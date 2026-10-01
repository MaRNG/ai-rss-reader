<?php

declare(strict_types=1);

namespace App\Model\Database\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: 'article')]
#[ORM\UniqueConstraint(name: 'uniq_article_feed_guid', columns: ['feed_id', 'guid_hash'])]
#[ORM\Index(name: 'idx_article_fetched', columns: ['fetched_at'])]
#[ORM\Index(name: 'idx_article_published', columns: ['published_at'])]
#[ORM\Index(name: 'idx_article_feed_published', columns: ['feed_id', 'published_at'])]
#[ORM\Index(name: 'idx_article_feed_fetched', columns: ['feed_id', 'fetched_at'])]
#[ORM\Index(name: 'ft_article_search', columns: ['title', 'content_text'], flags: ['fulltext'])]
class Article
{
	public const int FeedbackNone = 0;
	public const int FeedbackUp = 1;
	public const int FeedbackDown = -1;

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\ManyToOne(targetEntity: Feed::class)]
	#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
	private Feed $feed;

	#[ORM\Column(type: 'string', length: 64, options: ['fixed' => true])]
	private string $guidHash;

	#[ORM\Column(length: 2048)]
	private string $url;

	#[ORM\Column(length: 512)]
	private string $title;

	#[ORM\Column(length: 255, nullable: true)]
	private ?string $author = null;

	#[ORM\Column(type: 'text', nullable: true)]
	private ?string $summary = null;

	#[ORM\Column(type: 'text', length: 16777215, nullable: true)]
	private ?string $contentHtml = null;

	#[ORM\Column(type: 'text', length: 16777215, nullable: true)]
	private ?string $contentText = null;

	/** Plný text se nepodařilo stáhnout, zobrazuje se perex */
	#[ORM\Column]
	private bool $fullTextFailed = false;

	#[ORM\Column(nullable: true)]
	private ?DateTimeImmutable $publishedAt = null;

	#[ORM\Column]
	private DateTimeImmutable $fetchedAt;

	#[ORM\Column(nullable: true)]
	private ?DateTimeImmutable $readAt = null;

	#[ORM\Column]
	private bool $starred = false;

	#[ORM\Column(type: 'smallint')]
	private int $feedback = self::FeedbackNone;

	#[ORM\ManyToOne(targetEntity: Image::class)]
	#[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
	private ?Image $mainImage = null;

	/** @var Collection<int, Image> */
	#[ORM\ManyToMany(targetEntity: Image::class)]
	#[ORM\JoinTable(name: 'article_image')]
	#[ORM\JoinColumn(onDelete: 'CASCADE')]
	#[ORM\InverseJoinColumn(onDelete: 'CASCADE')]
	private Collection $images;


	public function __construct(Feed $feed, string $guidHash, string $url, string $title)
	{
		$this->feed = $feed;
		$this->guidHash = $guidHash;
		$this->url = $url;
		$this->title = $title;
		$this->fetchedAt = new DateTimeImmutable;
		$this->images = new ArrayCollection;
	}


	public function getId(): int
	{
		return $this->id ?? throw new \LogicException('Article is not persisted.');
	}


	public function getFeed(): Feed
	{
		return $this->feed;
	}


	public function getGuidHash(): string
	{
		return $this->guidHash;
	}


	public function getUrl(): string
	{
		return $this->url;
	}


	public function getTitle(): string
	{
		return $this->title;
	}


	public function getAuthor(): ?string
	{
		return $this->author;
	}


	public function setAuthor(?string $author): void
	{
		$this->author = $author;
	}


	public function getSummary(): ?string
	{
		return $this->summary;
	}


	public function setSummary(?string $summary): void
	{
		$this->summary = $summary;
	}


	public function getContentHtml(): ?string
	{
		return $this->contentHtml;
	}


	public function getContentText(): ?string
	{
		return $this->contentText;
	}


	public function setContent(?string $html, ?string $text): void
	{
		$this->contentHtml = $html;
		$this->contentText = $text;
	}


	public function isFullTextFailed(): bool
	{
		return $this->fullTextFailed;
	}


	public function setFullTextFailed(bool $failed): void
	{
		$this->fullTextFailed = $failed;
	}


	public function getPublishedAt(): ?DateTimeImmutable
	{
		return $this->publishedAt;
	}


	public function setPublishedAt(?DateTimeImmutable $publishedAt): void
	{
		$this->publishedAt = $publishedAt;
	}


	/** Datum pro řazení a zobrazení: publikace, jinak stažení */
	public function getDate(): DateTimeImmutable
	{
		return $this->publishedAt ?? $this->fetchedAt;
	}


	public function getFetchedAt(): DateTimeImmutable
	{
		return $this->fetchedAt;
	}


	public function getReadAt(): ?DateTimeImmutable
	{
		return $this->readAt;
	}


	public function isRead(): bool
	{
		return $this->readAt !== null;
	}


	public function markRead(): void
	{
		$this->readAt ??= new DateTimeImmutable;
	}


	public function markUnread(): void
	{
		$this->readAt = null;
	}


	public function isStarred(): bool
	{
		return $this->starred;
	}


	public function setStarred(bool $starred): void
	{
		$this->starred = $starred;
	}


	public function getFeedback(): int
	{
		return $this->feedback;
	}


	public function setFeedback(int $feedback): void
	{
		if (!in_array($feedback, [self::FeedbackNone, self::FeedbackUp, self::FeedbackDown], true)) {
			throw new \InvalidArgumentException("Invalid feedback value $feedback.");
		}
		$this->feedback = $feedback;
	}


	public function getMainImage(): ?Image
	{
		return $this->mainImage;
	}


	public function setMainImage(?Image $image): void
	{
		$this->mainImage = $image;
	}


	/** @return Collection<int, Image> */
	public function getImages(): Collection
	{
		return $this->images;
	}


	public function addImage(Image $image): void
	{
		if (!$this->images->contains($image)) {
			$this->images->add($image);
		}
	}
}
