<?php

declare(strict_types=1);

namespace App\Model\Http;


final class HttpResponse
{
	/** @param array<string, string> $headers hlavičky s klíči malými písmeny */
	public function __construct(
		public readonly int $status,
		public readonly string $body,
		public readonly array $headers,
		public readonly string $effectiveUrl,
	) {
	}


	public function getHeader(string $name): ?string
	{
		return $this->headers[strtolower($name)] ?? null;
	}


	public function isNotModified(): bool
	{
		return $this->status === 304;
	}
}
