<?php

declare(strict_types=1);

namespace App\Model\Feed;

use DateTimeImmutable;


final class ParsedItem
{
	public function __construct(
		public readonly string $guid,
		public readonly string $url,
		public readonly string $title,
		public readonly ?string $author,
		public readonly ?string $summaryHtml,
		public readonly ?string $contentHtml,
		public readonly ?DateTimeImmutable $publishedAt,
		public readonly ?string $imageUrl,
	) {
	}
}
