<?php

declare(strict_types=1);

namespace App\UI\Front\Presenter;

use App\Model\Database\Repository\FeedRepository;
use App\UI\Shared\BasePresenter;
use App\UI\Shared\Trait\ArticleFeedbackSignals;
use DateTimeImmutable;
use Nette\DI\Attributes\Inject;


abstract class BaseFrontPresenter extends BasePresenter
{
	use ArticleFeedbackSignals;

	#[Inject]
	public FeedRepository $sidebarFeeds;

	/** Feed zvýrazněný v sidebaru (null = Všechny feedy) */
	protected ?int $activeFeedId = null;

	protected string $railActive = '';


	protected function beforeRender(): void
	{
		parent::beforeRender();
		$feeds = $this->sidebarFeeds->findActiveWithCounts(new DateTimeImmutable('-24 hours'));
		$this->template->sidebarFeeds = $feeds;
		$this->template->sidebarTotal = array_sum(array_column($feeds, 'recent'));
		$this->template->activeFeedId = $this->activeFeedId;
		$this->template->railActive = $this->railActive;
		$this->template->loggedIn = $this->getUser()->isLoggedIn();
	}
}
