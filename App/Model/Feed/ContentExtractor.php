<?php

declare(strict_types=1);

namespace App\Model\Feed;

use App\Model\Http\HttpClient;
use fivefilters\Readability\Readability;


/**
 * Stažení plného textu článku přes Readability (pro feedy, které posílají jen perex).
 */
final class ContentExtractor
{
	private const int MaxBytes = 5_000_000;


	public function __construct(
		private readonly HttpClient $http,
	) {
	}


	/**
	 * @return array{html: string, image: ?string}|null null, pokud se obsah nepodařilo získat
	 */
	public function extract(string $url): ?array
	{
		try {
			$response = $this->http->get($url, ['Accept' => 'text/html,application/xhtml+xml'], self::MaxBytes);
			$readability = new Readability(fixRelativeURLs: true, originalURL: $response->effectiveUrl);
			$article = $readability->parse($response->body);
		} catch (\Throwable) {
			return null;
		}

		if (!$article->hasContent()) {
			return null;
		}
		return ['html' => (string) $article->content, 'image' => $article->image];
	}
}
