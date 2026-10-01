<?php

declare(strict_types=1);

namespace App\Model\Http;


/**
 * Jednoduchý HTTP klient nad cURL pro feedy, plné texty a obrázky.
 * Přesměrování sleduje ručně, aby šel každý krok zkontrolovat (jen http/https, žádné lokální adresy).
 */
final class HttpClient
{
	private const int MaxRedirects = 5;


	public function __construct(
		private readonly string $userAgent,
		private readonly int $timeout,
	) {
	}


	/**
	 * @param array<string, string> $headers
	 * @param int|null $maxBytes limit velikosti těla, při překročení HttpException::permanent
	 */
	public function get(string $url, array $headers = [], ?int $maxBytes = null, ?int $timeout = null): HttpResponse
	{
		for ($i = 0; $i <= self::MaxRedirects; $i++) {
			$response = $this->request($url, $headers, $maxBytes, $timeout ?? $this->timeout);
			$location = $response->getHeader('location');
			if (!in_array($response->status, [301, 302, 303, 307, 308], true) || $location === null) {
				return $response;
			}
			$url = self::resolveUrl($url, $location);
		}
		throw new HttpException("Too many redirects for $url");
	}


	/** @param array<string, string> $headers */
	private function request(string $url, array $headers, ?int $maxBytes, int $timeout): HttpResponse
	{
		[$host, $port, $ip] = UrlGuard::resolve($url);

		$responseHeaders = [];
		$body = '';
		$curl = curl_init($url);
		$headerLines = [];
		foreach ($headers as $name => $value) {
			$headerLines[] = "$name: $value";
		}

		curl_setopt_array($curl, [
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
			// Připnout ověřenou IP, aby ji DNS nemohlo mezi kontrolou a požadavkem změnit
			CURLOPT_RESOLVE => ["$host:$port:$ip"],
			CURLOPT_CONNECTTIMEOUT => min(10, $timeout),
			CURLOPT_TIMEOUT => $timeout,
			CURLOPT_USERAGENT => $this->userAgent,
			CURLOPT_HTTPHEADER => $headerLines,
			CURLOPT_ENCODING => '',
			CURLOPT_HEADERFUNCTION => function ($ch, string $line) use (&$responseHeaders): int {
				if (str_contains($line, ':')) {
					[$name, $value] = explode(':', $line, 2);
					$responseHeaders[strtolower(trim($name))] = trim($value);
				}
				return strlen($line);
			},
			CURLOPT_WRITEFUNCTION => function ($ch, string $chunk) use (&$body, $maxBytes): int {
				$body .= $chunk;
				if ($maxBytes !== null && strlen($body) > $maxBytes) {
					return 0; // přeruší přenos
				}
				return strlen($chunk);
			},
		]);

		$ok = curl_exec($curl);
		$status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
		$error = curl_error($curl);

		if ($maxBytes !== null && strlen($body) > $maxBytes) {
			throw HttpException::permanent("Response from $url exceeds $maxBytes bytes");
		}
		if ($ok === false) {
			throw new HttpException("Request to $url failed: $error");
		}
		if ($status >= 400) {
			throw new HttpException("Request to $url returned HTTP $status");
		}

		return new HttpResponse($status, $body, $responseHeaders, $url);
	}


	public static function resolveUrl(string $base, string $relative): string
	{
		if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $relative)) {
			return $relative;
		}
		$parts = parse_url($base);
		$scheme = $parts['scheme'] ?? 'https';
		$authority = $parts['host'] ?? '';
		if (isset($parts['port'])) {
			$authority .= ':' . $parts['port'];
		}
		if (str_starts_with($relative, '//')) {
			return "$scheme:$relative";
		}
		if (str_starts_with($relative, '/')) {
			return "$scheme://$authority$relative";
		}
		$path = $parts['path'] ?? '/';
		$dir = substr($path, 0, (int) strrpos($path, '/') + 1);
		return "$scheme://$authority$dir$relative";
	}
}
