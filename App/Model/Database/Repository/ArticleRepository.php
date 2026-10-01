<?php

declare(strict_types=1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Article;
use App\Model\Database\Entity\Feed;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Nette\Utils\Paginator;


final class ArticleRepository
{
	public function __construct(
		private readonly EntityManagerInterface $em,
	) {
	}


	public function find(int $id): ?Article
	{
		return $this->em->find(Article::class, $id);
	}


	/**
	 * Které z daných hashů už ve feedu existují.
	 * @param list<string> $guidHashes
	 * @return array<string, true>
	 */
	public function findExistingGuidHashes(Feed $feed, array $guidHashes): array
	{
		if ($guidHashes === []) {
			return [];
		}
		$rows = $this->em->createQueryBuilder()
			->select('a.guidHash')
			->from(Article::class, 'a')
			->where('a.feed = :feed')
			->andWhere('a.guidHash IN (:hashes)')
			->setParameter('feed', $feed)
			->setParameter('hashes', $guidHashes)
			->getQuery()
			->getSingleColumnResult();
		return array_fill_keys($rows, true);
	}


	/**
	 * Nejnovější článek z každého aktivního feedu, seřazené od nejnovějšího.
	 * @return list<Article>
	 */
	public function findLatestPerFeed(): array
	{
		$ids = $this->em->getConnection()->fetchFirstColumn(
			'SELECT id FROM (
				SELECT a.id, a.published_at,
					ROW_NUMBER() OVER (PARTITION BY a.feed_id ORDER BY a.published_at DESC, a.id DESC) AS rn
				FROM article a
				JOIN feed f ON f.id = a.feed_id AND f.active = 1
			) t
			WHERE rn = 1
			ORDER BY published_at DESC, id DESC',
		);
		return $this->findByIdsOrdered(array_map('intval', $ids));
	}


	/** @return list<Article> */
	public function findPage(?Feed $feed, Paginator $paginator): array
	{
		$qb = $this->listQuery($feed);
		$paginator->setItemCount($this->count($feed));
		return $qb
			->setFirstResult($paginator->getOffset())
			->setMaxResults($paginator->getItemsPerPage())
			->getQuery()
			->getResult();
	}


	public function count(?Feed $feed, ?DateTimeImmutable $fetchedSince = null): int
	{
		$qb = $this->em->createQueryBuilder()
			->select('COUNT(a.id)')
			->from(Article::class, 'a')
			->join('a.feed', 'f')
			->where('f.active = true');
		if ($feed) {
			$qb->andWhere('a.feed = :feed')->setParameter('feed', $feed);
		}
		if ($fetchedSince) {
			$qb->andWhere('a.fetchedAt >= :since')->setParameter('since', $fetchedSince);
		}
		return (int) $qb->getQuery()->getSingleScalarResult();
	}


	/**
	 * Předchozí (starší) a další (novější) článek téhož feedu.
	 * @return array{previous: ?Article, next: ?Article}
	 */
	public function findNeighbours(Article $article): array
	{
		$base = fn(string $op, string $dir) => $this->em->createQueryBuilder()
			->select('a')
			->from(Article::class, 'a')
			->where('a.feed = :feed')
			->andWhere("(a.publishedAt $op :date OR (a.publishedAt = :date AND a.id $op :id))")
			->setParameter('feed', $article->getFeed())
			->setParameter('date', $article->getPublishedAt())
			->setParameter('id', $article->getId())
			->orderBy('a.publishedAt', $dir)
			->addOrderBy('a.id', $dir)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		return [
			'previous' => $base('<', 'DESC'),
			'next' => $base('>', 'ASC'),
		];
	}


	/**
	 * Smaže staré články kromě označených hvězdičkou.
	 * @return int počet smazaných
	 */
	public function deleteOlderThan(DateTimeImmutable $before): int
	{
		return $this->em->getConnection()->executeStatement(
			'DELETE FROM article WHERE fetched_at < ? AND starred = 0',
			[$before->format('Y-m-d H:i:s')],
		);
	}


	private function listQuery(?Feed $feed): QueryBuilder
	{
		$qb = $this->em->createQueryBuilder()
			->select('a', 'f', 'i')
			->from(Article::class, 'a')
			->join('a.feed', 'f')
			->leftJoin('a.mainImage', 'i')
			->where('f.active = true')
			->orderBy('a.publishedAt', 'DESC')
			->addOrderBy('a.id', 'DESC');
		if ($feed) {
			$qb->andWhere('a.feed = :feed')->setParameter('feed', $feed);
		}
		return $qb;
	}


	/**
	 * @param list<int> $ids
	 * @return list<Article>
	 */
	private function findByIdsOrdered(array $ids): array
	{
		if ($ids === []) {
			return [];
		}
		$articles = $this->em->createQueryBuilder()
			->select('a', 'f', 'i')
			->from(Article::class, 'a')
			->join('a.feed', 'f')
			->leftJoin('a.mainImage', 'i')
			->where('a.id IN (:ids)')
			->setParameter('ids', $ids)
			->getQuery()
			->getResult();
		$byId = [];
		foreach ($articles as $article) {
			$byId[$article->getId()] = $article;
		}
		return array_values(array_filter(array_map(fn(int $id) => $byId[$id] ?? null, $ids)));
	}
}
