<?php

declare(strict_types=1);

namespace App\Model\Feed;


final class ParsedFeed
{
	/** @param list<ParsedItem> $items */
	public function __construct(
		public readonly ?string $title,
		public readonly ?string $siteUrl,
		public readonly array $items,
	) {
	}
}
