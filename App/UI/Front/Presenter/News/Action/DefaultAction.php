<?php

declare(strict_types=1);

namespace App\UI\Front\Presenter\News\Action;

use App\UI\Front\Presenter\ArticleGrouping;
use App\UI\Front\Presenter\News\NewsPresenter;
use DateTimeImmutable;
use Nette\Utils\Paginator;


/**
 * Novinky: promíchané články ze všech feedů od nejnovějších, po dnech.
 * @phpstan-require-extends NewsPresenter
 */
trait DefaultAction
{
	public function renderDefault(int $page = 1): void
	{
		$paginator = new Paginator;
		$paginator->setItemsPerPage(NewsPresenter::PerPage);
		$paginator->setPage($page);

		$articles = $this->articleRepository->findPage(null, $paginator);
		$this->template->days = ArticleGrouping::byDay($articles);
		$this->template->paginator = $paginator;
		$this->template->newCount = $this->articleRepository->count(null, new DateTimeImmutable('-24 hours'));
	}
}
