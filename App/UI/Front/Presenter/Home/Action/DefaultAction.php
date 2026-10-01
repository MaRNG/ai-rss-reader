<?php

declare(strict_types=1);

namespace App\UI\Front\Presenter\Home\Action;

use App\UI\Front\Presenter\Home\HomePresenter;


/**
 * Hlavní stránka: TOP 1 článek z každého feedu (ve fázi 1 nejnovější).
 * @phpstan-require-extends HomePresenter
 */
trait DefaultAction
{
	public function renderDefault(): void
	{
		$this->template->articles = $this->articleRepository->findLatestPerFeed();
	}
}
