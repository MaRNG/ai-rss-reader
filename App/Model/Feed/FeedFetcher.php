<?php

declare(strict_types=1);

namespace App\Model\Feed;

use App\Model\Database\Entity\Article;
use App\Model\Database\Entity\Feed;
use App\Model\Database\Repository\ArticleRepository;
use App\Model\Http\HttpClient;
use App\Model\Http\HttpException;
use App\Model\Image\ImageExtractor;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;


/**
 * Stáhne feed (podmíněný GET), uloží nové články a založí jejich obrázky ke stažení.
 */
final class FeedFetcher
{
	private const int MaxFeedBytes = 10_000_000;


	public function __construct(
		private readonly HttpClient $http,
		private readonly FeedParser $parser,
		private readonly HtmlSanitizer $sanitizer,
		private readonly ContentExtractor $contentExtractor,
		private readonly ImageExtractor $imageExtractor,
		private readonly ArticleRepository $articles,
		private readonly EntityManagerInterface $em,
		private readonly int $maxErrors,
	) {
	}


	/**
	 * Ověří URL a vrátí rozparsovaný feed (pro přidání nového feedu).
	 * @throws FeedException
	 */
	public function probe(string $url): ParsedFeed
	{
		try {
			$response = $this->http->get($url, ['Accept' => self::accept()], self::MaxFeedBytes);
		} catch (HttpException $e) {
			throw new FeedException($e->getMessage(), 0, $e);
		}
		return $this->parser->parse($response->body, $response->effectiveUrl);
	}


	public function fetch(Feed $feed, bool $force = false): FetchResult
	{
		try {
			$headers = ['Accept' => self::accept()];
			if (!$force && $feed->getEtag()) {
				$headers['If-None-Match'] = $feed->getEtag();
			}
			if (!$force && $feed->getLastModified()) {
				$headers['If-Modified-Since'] = $feed->getLastModified();
			}

			$response = $this->http->get($feed->getUrl(), $headers, self::MaxFeedBytes);
			if ($response->isNotModified()) {
				$feed->markFetched();
				$this->em->flush();
				return new FetchResult(notModified: true);
			}

			$parsed = $this->parser->parse($response->body, $response->effectiveUrl);
			$new = $this->storeItems($feed, $parsed);

			if (!$feed->getSiteUrl() && $parsed->siteUrl) {
				$feed->setSiteUrl($parsed->siteUrl);
			}
			$feed->setCacheHeaders($response->getHeader('etag'), $response->getHeader('last-modified'));
			$feed->markFetched();
			$this->em->flush();
			return new FetchResult(newArticles: $new);

		} catch (HttpException | FeedException $e) {
			$this->em->clear();
			$feed = $this->em->find(Feed::class, $feed->getId()) ?? throw $e;
			$deactivated = $feed->markFailed($e->getMessage(), $this->maxErrors);
			$this->em->flush();
			return new FetchResult(error: $e->getMessage(), deactivated: $deactivated);
		}
	}


	private function storeItems(Feed $feed, ParsedFeed $parsed): int
	{
		$hashes = [];
		foreach ($parsed->items as $item) {
			$hashes[self::guidHash($item->guid)] = $item;
		}
		$existing = $this->articles->findExistingGuidHashes($feed, array_keys($hashes));
		$now = new DateTimeImmutable;
		$count = 0;

		foreach ($hashes as $hash => $item) {
			if (isset($existing[$hash])) {
				continue;
			}
			$article = new Article($feed, $hash, $item->url, mb_substr($item->title, 0, 512));
			$article->setAuthor($item->author !== null ? mb_substr($item->author, 0, 255) : null);
			// Bez data nebo s datem v budoucnosti se použije čas stažení
			$article->setPublishedAt($item->publishedAt && $item->publishedAt <= $now ? $item->publishedAt : $now);

			$summarySource = $item->summaryHtml ?? $item->contentHtml ?? '';
			$article->setSummary($summarySource !== '' ? $this->sanitizer->excerpt($summarySource) : null);

			$html = $item->contentHtml;
			$mainImage = $item->imageUrl;
			if ($feed->isFetchFullText()) {
				$full = $this->contentExtractor->extract($item->url);
				if ($full) {
					$html = $full['html'];
					$mainImage ??= $full['image'];
				} else {
					$article->setFullTextFailed(true);
				}
			}
			if ($html !== null && $html !== '') {
				$clean = $this->sanitizer->sanitize($html, $item->url);
				$article->setContent($clean, $this->sanitizer->toText($clean));
			}

			$this->em->persist($article);
			$this->imageExtractor->extract($article, $mainImage);
			$count++;
		}
		return $count;
	}


	private static function guidHash(string $guid): string
	{
		return hash('sha256', $guid);
	}


	private static function accept(): string
	{
		return 'application/rss+xml, application/atom+xml, application/xml;q=0.9, text/xml;q=0.8, */*;q=0.5';
	}
}
