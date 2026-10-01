<?php

declare(strict_types=1);

namespace App\Model\Feed;

use DateTimeImmutable;
use SimplePie\Item;
use SimplePie\SimplePie;


/**
 * Parsování RSS/Atom přes SimplePie z již stažených dat (stahování řeší HttpClient kvůli ETag).
 */
final class FeedParser
{
	public function parse(string $data, string $feedUrl): ParsedFeed
	{
		$pie = new SimplePie;
		$pie->enable_cache(false);
		$pie->set_raw_data($data);
		$pie->set_feed_url($feedUrl);
		$pie->enable_order_by_date(false);
		$pie->strip_htmltags(false);
		$pie->strip_attributes([]);
		if (!$pie->init()) {
			$error = $pie->error();
			throw new FeedException('Feed nelze načíst: ' . (is_array($error) ? implode('; ', $error) : (string) $error));
		}

		$items = [];
		foreach ($pie->get_items() as $item) {
			$parsed = $this->parseItem($item);
			if ($parsed) {
				$items[] = $parsed;
			}
		}

		return new ParsedFeed(
			title: self::clean($pie->get_title()),
			siteUrl: $pie->get_link() ?: null,
			items: $items,
		);
	}


	private function parseItem(Item $item): ?ParsedItem
	{
		$url = $item->get_permalink();
		$title = self::clean($item->get_title());
		if (!$url || !preg_match('~^https?://~i', $url)) {
			return null;
		}

		$guid = $item->get_id() ?: $url;
		$timestamp = $item->get_date('U');
		$publishedAt = is_numeric($timestamp) ? (new DateTimeImmutable)->setTimestamp((int) $timestamp) : null;

		$description = $item->get_description(true) ?: null;
		$content = $item->get_content(true) ?: null;

		return new ParsedItem(
			guid: (string) $guid,
			url: $url,
			title: $title ?? $url,
			author: self::clean($item->get_author()?->get_name()),
			summaryHtml: $description,
			contentHtml: $content ?? $description,
			publishedAt: $publishedAt,
			imageUrl: $this->findImage($item),
		);
	}


	private function findImage(Item $item): ?string
	{
		$thumbnail = $item->get_thumbnail();
		if (is_array($thumbnail) && !empty($thumbnail['url'])) {
			return $thumbnail['url'];
		}
		foreach ($item->get_enclosures() ?? [] as $enclosure) {
			$type = (string) $enclosure->get_type();
			$link = $enclosure->get_link();
			if ($link && (str_starts_with($type, 'image/') || $enclosure->get_medium() === 'image')) {
				return $link;
			}
			foreach ($enclosure->get_thumbnails() ?? [] as $thumb) {
				if ($thumb) {
					return $thumb;
				}
			}
		}
		return null;
	}


	private static function clean(?string $text): ?string
	{
		if ($text === null) {
			return null;
		}
		$text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$text = trim(preg_replace('~\s+~u', ' ', $text) ?? $text);
		return $text !== '' ? $text : null;
	}
}
