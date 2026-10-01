<?php

declare(strict_types=1);

namespace App\Model\Database\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: 'image')]
#[ORM\UniqueConstraint(name: 'uniq_image_feed_hash', columns: ['feed_id', 'hash'])]
#[ORM\Index(name: 'idx_image_status', columns: ['status'])]
class Image
{
	public const string StatusPending = 'pending';
	public const string StatusDone = 'done';
	public const string StatusFailed = 'failed';

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\ManyToOne(targetEntity: Feed::class)]
	#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
	private Feed $feed;

	#[ORM\Column(length: 2048)]
	private string $sourceUrl;

	/** sha1 ze sourceUrl, zároveň název souboru */
	#[ORM\Column(type: 'string', length: 40, options: ['fixed' => true])]
	private string $hash;

	/** Relativně k www/files, např. 12/a/b/c/d/abcd….jpg */
	#[ORM\Column(length: 255, nullable: true)]
	private ?string $path = null;

	#[ORM\Column(length: 32, nullable: true)]
	private ?string $mimeType = null;

	#[ORM\Column(nullable: true)]
	private ?int $size = null;

	#[ORM\Column(nullable: true)]
	private ?int $width = null;

	#[ORM\Column(nullable: true)]
	private ?int $height = null;

	#[ORM\Column(length: 16)]
	private string $status = self::StatusPending;

	#[ORM\Column(type: 'smallint')]
	private int $attempts = 0;

	#[ORM\Column(nullable: true)]
	private ?DateTimeImmutable $downloadedAt = null;


	public function __construct(Feed $feed, string $sourceUrl)
	{
		$this->feed = $feed;
		$this->sourceUrl = $sourceUrl;
		$this->hash = self::hashUrl($sourceUrl);
	}


	public static function hashUrl(string $url): string
	{
		return sha1($url);
	}


	public function getId(): int
	{
		return $this->id ?? throw new \LogicException('Image is not persisted.');
	}


	public function getFeed(): Feed
	{
		return $this->feed;
	}


	public function getSourceUrl(): string
	{
		return $this->sourceUrl;
	}


	public function getHash(): string
	{
		return $this->hash;
	}


	public function getPath(): ?string
	{
		return $this->path;
	}


	public function getMimeType(): ?string
	{
		return $this->mimeType;
	}


	public function getWidth(): ?int
	{
		return $this->width;
	}


	public function getHeight(): ?int
	{
		return $this->height;
	}


	public function getStatus(): string
	{
		return $this->status;
	}


	public function isDone(): bool
	{
		return $this->status === self::StatusDone;
	}


	public function getAttempts(): int
	{
		return $this->attempts;
	}


	public function markDownloaded(string $path, string $mimeType, int $size, int $width, int $height): void
	{
		$this->path = $path;
		$this->mimeType = $mimeType;
		$this->size = $size;
		$this->width = $width;
		$this->height = $height;
		$this->status = self::StatusDone;
		$this->attempts++;
		$this->downloadedAt = new DateTimeImmutable;
	}


	public function markAttemptFailed(int $maxAttempts): void
	{
		$this->attempts++;
		if ($this->attempts >= $maxAttempts) {
			$this->status = self::StatusFailed;
		}
	}


	/** Neopravitelná chyba (nepovolený typ, příliš velký soubor, zakázaná adresa) */
	public function markFailed(): void
	{
		$this->attempts++;
		$this->status = self::StatusFailed;
	}
}
