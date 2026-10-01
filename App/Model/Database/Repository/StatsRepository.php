<?php

declare(strict_types=1);

namespace App\Model\Database\Repository;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;


/**
 * Agregace pro /stats. Přírůstek se počítá podle fetched_at (kdy článek Siftly stáhl).
 */
final class StatsRepository
{
	public function __construct(
		private readonly EntityManagerInterface $em,
	) {
	}


	/** @return list<array{id: int, title: string, total: int, last7: int, last30: int}> */
	public function countsPerFeed(DateTimeImmutable $now): array
	{
		$rows = $this->em->getConnection()->fetchAllAssociative(
			'SELECT f.id, f.title,
				COUNT(a.id) AS total,
				COALESCE(SUM(a.fetched_at >= ?), 0) AS last7,
				COALESCE(SUM(a.fetched_at >= ?), 0) AS last30
			FROM feed f
			LEFT JOIN article a ON a.feed_id = f.id
			WHERE f.active = 1
			GROUP BY f.id, f.title
			ORDER BY total DESC, f.title',
			[
				$now->modify('-7 days')->format('Y-m-d H:i:s'),
				$now->modify('-30 days')->format('Y-m-d H:i:s'),
			],
		);
		return array_map(fn(array $r) => [
			'id' => (int) $r['id'],
			'title' => (string) $r['title'],
			'total' => (int) $r['total'],
			'last7' => (int) $r['last7'],
			'last30' => (int) $r['last30'],
		], $rows);
	}


	/**
	 * Denní přírůstek po feedech za posledních $days dní (včetně dneška), dny bez článků jako 0.
	 * @return array{days: list<string>, feeds: list<array{id: int, title: string, counts: list<int>}>, totals: list<int>}
	 */
	public function dailyCounts(DateTimeImmutable $today, int $days): array
	{
		$from = $today->setTime(0, 0)->modify('-' . ($days - 1) . ' days');
		$rows = $this->em->getConnection()->fetchAllAssociative(
			'SELECT DATE(a.fetched_at) AS day, f.id, f.title, COUNT(*) AS cnt
			FROM article a
			JOIN feed f ON f.id = a.feed_id
			WHERE a.fetched_at >= ? AND f.active = 1
			GROUP BY day, f.id, f.title',
			[$from->format('Y-m-d H:i:s')],
		);

		$dayKeys = [];
		for ($i = 0; $i < $days; $i++) {
			$dayKeys[] = $from->modify("+$i days")->format('Y-m-d');
		}
		$index = array_flip($dayKeys);

		$feeds = [];
		$totals = array_fill(0, $days, 0);
		foreach ($rows as $row) {
			$id = (int) $row['id'];
			$feeds[$id] ??= ['id' => $id, 'title' => (string) $row['title'], 'counts' => array_fill(0, $days, 0)];
			$i = $index[$row['day']] ?? null;
			if ($i !== null) {
				$feeds[$id]['counts'][$i] += (int) $row['cnt'];
				$totals[$i] += (int) $row['cnt'];
			}
		}
		usort($feeds, fn($a, $b) => array_sum($b['counts']) <=> array_sum($a['counts']));

		return ['days' => $dayKeys, 'feeds' => $feeds, 'totals' => $totals];
	}
}
