<?php

declare(strict_types=1);

namespace App\Model\Feed;


final class FetchResult
{
	public function __construct(
		public readonly int $newArticles = 0,
		public readonly bool $notModified = false,
		public readonly ?string $error = null,
		public readonly bool $deactivated = false,
	) {
	}
}
