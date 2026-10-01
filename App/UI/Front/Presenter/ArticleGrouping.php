<?php

declare(strict_types=1);

namespace App\UI\Front\Presenter;

use App\Model\Database\Entity\Article;
use DateTimeImmutable;


final class ArticleGrouping
{
	/**
	 * Seskupí (seřazené) články po dnech.
	 * @param list<Article> $articles
	 * @return list<array{date: DateTimeImmutable, articles: list<Article>}>
	 */
	public static function byDay(array $articles): array
	{
		$days = [];
		foreach ($articles as $article) {
			$key = $article->getDate()->format('Y-m-d');
			$days[$key] ??= ['date' => $article->getDate()->setTime(0, 0), 'articles' => []];
			$days[$key]['articles'][] = $article;
		}
		return array_values($days);
	}
}
