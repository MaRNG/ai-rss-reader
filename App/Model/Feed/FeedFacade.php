<?php

declare(strict_types=1);

namespace App\Model\Feed;

use App\Model\Database\Entity\Feed;
use App\Model\Database\Repository\FeedRepository;
use App\Model\Image\ImageStorage;
use Doctrine\ORM\EntityManagerInterface;


/**
 * Přidání, úprava a smazání feedu – sdílí administrace i CLI (feeds:add).
 */
final class FeedFacade
{
	public function __construct(
		private readonly FeedRepository $feeds,
		private readonly FeedFetcher $fetcher,
		private readonly ImageStorage $imageStorage,
		private readonly EntityManagerInterface $em,
	) {
	}


	/**
	 * Ověří, že URL je platný feed, uloží ho a rovnou stáhne články.
	 * @throws FeedException
	 */
	public function add(string $url, ?string $title = null, bool $fetchFullText = false): Feed
	{
		$url = trim($url);
		if (!preg_match('~^https?://~i', $url)) {
			throw new FeedException('Adresa feedu musí začínat http:// nebo https://.');
		}
		if ($this->feeds->findByUrl($url)) {
			throw new FeedException('Tento feed už je přidaný.');
		}

		$parsed = $this->fetcher->probe($url);
		$title = trim((string) $title) ?: ($parsed->title ?? parse_url($url, PHP_URL_HOST) ?: $url);

		$feed = new Feed($url, mb_substr($title, 0, 255));
		$feed->setSiteUrl($parsed->siteUrl);
		$feed->setFetchFullText($fetchFullText);
		$this->em->persist($feed);
		$this->em->flush();

		$this->fetcher->fetch($feed);
		return $feed;
	}


	/** @throws FeedException */
	public function update(Feed $feed, string $url, string $title, bool $fetchFullText, bool $active): void
	{
		$url = trim($url);
		if (!preg_match('~^https?://~i', $url)) {
			throw new FeedException('Adresa feedu musí začínat http:// nebo https://.');
		}
		$other = $this->feeds->findByUrl($url);
		if ($other && $other->getId() !== $feed->getId()) {
			throw new FeedException('Feed s touto adresou už existuje.');
		}
		if ($url !== $feed->getUrl()) {
			$feed->setUrl($url);
			$feed->setCacheHeaders(null, null);
		}
		$feed->setTitle(mb_substr(trim($title) ?: $feed->getTitle(), 0, 255));
		$feed->setFetchFullText($fetchFullText);
		$feed->setActive($active);
		$this->em->flush();
	}


	/** Smaže feed i s články, obrázky (kaskádou v DB) a složkou www/files/<feed_id>/ */
	public function delete(Feed $feed): void
	{
		$id = $feed->getId();
		$this->em->remove($feed);
		$this->em->flush();
		$this->imageStorage->deleteFeed($id);
	}
}
