<?php

declare(strict_types=1);

namespace App\Model\Image;

use App\Model\Database\Entity\Article;
use App\Model\Database\Repository\ImageRepository;


/**
 * Najde obrázky v článku a založí pro ně záznamy Image ve stavu pending.
 * Hlavní obrázek: enclosure / media:content z feedu, jinak první obrázek v textu.
 */
final class ImageExtractor
{
	private const int MaxImagesPerArticle = 30;


	public function __construct(
		private readonly ImageRepository $images,
	) {
	}


	public function extract(Article $article, ?string $mainImageUrl): void
	{
		$urls = self::findImageUrls($article->getContentHtml() ?? '');
		$mainImageUrl = $mainImageUrl && self::isHttpUrl($mainImageUrl) ? $mainImageUrl : ($urls[0] ?? null);

		foreach (array_slice($urls, 0, self::MaxImagesPerArticle) as $url) {
			$article->addImage($this->images->findOrCreate($article->getFeed(), $url));
		}
		if ($mainImageUrl) {
			$image = $this->images->findOrCreate($article->getFeed(), $mainImageUrl);
			$article->addImage($image);
			$article->setMainImage($image);
		}
	}


	/** @return list<string> */
	public static function findImageUrls(string $html): array
	{
		preg_match_all('~<img\b[^>]*\bsrc="([^"]+)"~i', $html, $m);
		$urls = [];
		foreach ($m[1] as $src) {
			$url = html_entity_decode($src, ENT_QUOTES | ENT_HTML5, 'UTF-8');
			if (self::isHttpUrl($url)) {
				$urls[$url] = true;
			}
		}
		return array_keys($urls);
	}


	private static function isHttpUrl(string $url): bool
	{
		return (bool) preg_match('~^https?://~i', $url) && strlen($url) <= 2048;
	}
}
