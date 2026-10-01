<?php

declare(strict_types=1);

namespace App\UI\Front\Presenter\Stats\Action;

use App\UI\Front\Presenter\Stats\StatsPresenter;
use DateTimeImmutable;


/**
 * Statistiky: počty článků po feedech a denní přírůstek (podle fetched_at).
 * @phpstan-require-extends StatsPresenter
 */
trait DefaultAction
{
	public function renderDefault(int $days = 30): void
	{
		if (!in_array($days, StatsPresenter::Periods, true)) {
			$days = 30;
		}
		$now = new DateTimeImmutable;
		$daily = $this->statsRepository->dailyCounts($now, $days);

		$this->template->days = $days;
		$this->template->periods = StatsPresenter::Periods;
		$this->template->perFeed = $this->statsRepository->countsPerFeed($now);
		$this->template->daily = $daily;
		$this->template->periodTotal = array_sum($daily['totals']);
	}
}
