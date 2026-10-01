<?php

declare(strict_types=1);

namespace App\UI\Front\Presenter\Feed\Action;

use App\UI\Front\Presenter\Feed\FeedPresenter;
use DateTimeImmutable;


/**
 * Přehled feedů s počty článků.
 * @phpstan-require-extends FeedPresenter
 */
trait DefaultAction
{
	public function renderDefault(): void
	{
		$this->template->feeds = $this->feedRepository->findActiveWithCounts(new DateTimeImmutable('-24 hours'));
	}
}
