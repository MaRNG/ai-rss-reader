<?php

declare(strict_types=1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Feed;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;


final class FeedRepository
{
	public function __construct(
		private readonly EntityManagerInterface $em,
	) {
	}


	public function find(int $id): ?Feed
	{
		return $this->em->find(Feed::class, $id);
	}


	public function findByUrl(string $url): ?Feed
	{
		return $this->em->getRepository(Feed::class)->findOneBy(['urlHash' => Feed::hashUrl($url)]);
	}


	/** @return list<Feed> */
	public function findAll(): array
	{
		return $this->em->getRepository(Feed::class)->findBy([], ['title' => 'ASC']);
	}


	/** @return list<Feed> */
	public function findActive(): array
	{
		return $this->em->getRepository(Feed::class)->findBy(['active' => true], ['title' => 'ASC']);
	}


	/**
	 * Aktivní feedy s počty článků pro sidebar a přehled feedů.
	 * @return list<array{feed: Feed, total: int, recent: int, lastDate: ?DateTimeImmutable}>
	 */
	public function findActiveWithCounts(DateTimeImmutable $recentSince): array
	{
		$rows = $this->em->getConnection()->fetchAllAssociative(
			'SELECT f.id,
				COUNT(a.id) AS total,
				COALESCE(SUM(a.fetched_at >= ?), 0) AS recent,
				MAX(a.published_at) AS last_date
			FROM feed f
			LEFT JOIN article a ON a.feed_id = f.id
			WHERE f.active = 1
			GROUP BY f.id',
			[$recentSince->format('Y-m-d H:i:s')],
		);
		$counts = array_column($rows, null, 'id');

		$result = [];
		foreach ($this->findActive() as $feed) {
			$row = $counts[$feed->getId()] ?? null;
			$result[] = [
				'feed' => $feed,
				'total' => (int) ($row['total'] ?? 0),
				'recent' => (int) ($row['recent'] ?? 0),
				'lastDate' => isset($row['last_date']) ? new DateTimeImmutable($row['last_date']) : null,
			];
		}
		return $result;
	}
}
