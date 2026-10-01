<?php

declare(strict_types=1);

namespace App\Model\Http;


/**
 * Ochrana proti SSRF: povolí jen http/https na veřejné adresy.
 */
final class UrlGuard
{
	/**
	 * @return array{string, int, string} host, port, ověřená IP adresa
	 * @throws HttpException
	 */
	public static function resolve(string $url): array
	{
		$parts = parse_url($url);
		$scheme = strtolower($parts['scheme'] ?? '');
		$host = $parts['host'] ?? '';
		if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
			throw HttpException::permanent("URL '$url' is not an http(s) URL");
		}
		$port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
		$host = trim($host, '[]');

		$ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : self::lookup($host);
		if ($ips === []) {
			throw new HttpException("Cannot resolve host '$host'");
		}
		foreach ($ips as $ip) {
			if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
				throw HttpException::permanent("Host '$host' resolves to a non-public address");
			}
		}
		$ip = $ips[0];
		return [$host, $port, str_contains($ip, ':') ? "[$ip]" : $ip];
	}


	/** @return list<string> */
	private static function lookup(string $host): array
	{
		$ips = [];
		foreach (@dns_get_record($host, DNS_A | DNS_AAAA) ?: [] as $record) {
			$ips[] = $record['ip'] ?? $record['ipv6'] ?? null;
		}
		$ips = array_values(array_filter($ips));
		return $ips ?: array_values(array_filter((array) @gethostbynamel($host)));
	}
}
