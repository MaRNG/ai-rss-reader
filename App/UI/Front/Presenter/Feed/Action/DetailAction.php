<?php

declare(strict_types=1);

namespace App\UI\Front\Presenter\Feed\Action;

use App\Model\Database\Entity\Feed;
use App\UI\Front\Presenter\ArticleGrouping;
use App\UI\Front\Presenter\Feed\FeedPresenter;
use DateTimeImmutable;
use Nette\Utils\Paginator;


/**
 * Články jednoho feedu.
 * @phpstan-require-extends FeedPresenter
 */
trait DetailAction
{
	private Feed $feed;


	public function actionDetail(int $id): void
	{
		$feed = $this->feedRepository->find($id);
		if (!$feed || !$feed->isActive()) {
			$this->error('Feed neexistuje.');
		}
		$this->feed = $feed;
		$this->activeFeedId = $feed->getId();
	}


	public function renderDetail(int $id, int $page = 1): void
	{
		$paginator = new Paginator;
		$paginator->setItemsPerPage(FeedPresenter::PerPage);
		$paginator->setPage($page);

		$this->template->feed = $this->feed;
		$this->template->days = ArticleGrouping::byDay($this->articleRepository->findPage($this->feed, $paginator));
		$this->template->paginator = $paginator;
		$this->template->newCount = $this->articleRepository->count($this->feed, new DateTimeImmutable('-24 hours'));
	}
}
