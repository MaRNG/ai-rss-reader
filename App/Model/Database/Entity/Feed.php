<?php

declare(strict_types=1);

namespace App\Model\Database\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: 'feed')]
class Feed
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column(length: 2048)]
	private string $url;

	/** sha256 z URL; unikátní index (na VARCHAR(2048) v utf8mb4 se nevejde) */
	#[ORM\Column(type: 'string', length: 64, unique: true, options: ['fixed' => true])]
	private string $urlHash;

	#[ORM\Column(length: 2048, nullable: true)]
	private ?string $siteUrl = null;

	#[ORM\Column(length: 255)]
	private string $title;

	#[ORM\Column(length: 255, nullable: true)]
	private ?string $etag = null;

	#[ORM\Column(length: 255, nullable: true)]
	private ?string $lastModified = null;

	#[ORM\Column(nullable: true)]
	private ?DateTimeImmutable $lastFetchedAt = null;

	#[ORM\Column(type: 'text', nullable: true)]
	private ?string $lastError = null;

	#[ORM\Column]
	private int $errorCount = 0;

	#[ORM\Column]
	private bool $fetchFullText = false;

	#[ORM\Column]
	private bool $active = true;

	#[ORM\Column]
	private DateTimeImmutable $createdAt;


	public function __construct(string $url, string $title)
	{
		$this->setUrl($url);
		$this->title = $title;
		$this->createdAt = new DateTimeImmutable;
	}


	public function getId(): int
	{
		return $this->id ?? throw new \LogicException('Feed is not persisted.');
	}


	public function getUrl(): string
	{
		return $this->url;
	}


	public function setUrl(string $url): void
	{
		$this->url = $url;
		$this->urlHash = self::hashUrl($url);
	}


	public static function hashUrl(string $url): string
	{
		return hash('sha256', $url);
	}


	public function getSiteUrl(): ?string
	{
		return $this->siteUrl;
	}


	public function setSiteUrl(?string $siteUrl): void
	{
		$this->siteUrl = $siteUrl;
	}


	/** Doména webu pro zobrazení a favikonu */
	public function getHost(): string
	{
		$host = parse_url($this->siteUrl ?? $this->url, PHP_URL_HOST);
		return is_string($host) ? preg_replace('~^www\.~', '', $host) ?? $host : '';
	}


	public function getTitle(): string
	{
		return $this->title;
	}


	public function setTitle(string $title): void
	{
		$this->title = $title;
	}


	public function getEtag(): ?string
	{
		return $this->etag;
	}


	public function getLastModified(): ?string
	{
		return $this->lastModified;
	}


	public function setCacheHeaders(?string $etag, ?string $lastModified): void
	{
		$this->etag = $etag;
		$this->lastModified = $lastModified;
	}


	public function getLastFetchedAt(): ?DateTimeImmutable
	{
		return $this->lastFetchedAt;
	}


	public function getLastError(): ?string
	{
		return $this->lastError;
	}


	public function getErrorCount(): int
	{
		return $this->errorCount;
	}


	public function markFetched(): void
	{
		$this->lastFetchedAt = new DateTimeImmutable;
		$this->lastError = null;
		$this->errorCount = 0;
	}


	/** Vrací true, pokud byl feed kvůli opakovaným chybám deaktivován */
	public function markFailed(string $error, int $maxErrors): bool
	{
		$this->lastFetchedAt = new DateTimeImmutable;
		$this->lastError = $error;
		$this->errorCount++;
		if ($this->errorCount >= $maxErrors && $this->active) {
			$this->active = false;
			return true;
		}
		return false;
	}


	public function isFetchFullText(): bool
	{
		return $this->fetchFullText;
	}


	public function setFetchFullText(bool $fetchFullText): void
	{
		$this->fetchFullText = $fetchFullText;
	}


	public function isActive(): bool
	{
		return $this->active;
	}


	public function setActive(bool $active): void
	{
		$this->active = $active;
		if ($active) {
			$this->errorCount = 0;
		}
	}


	public function getCreatedAt(): DateTimeImmutable
	{
		return $this->createdAt;
	}
}
