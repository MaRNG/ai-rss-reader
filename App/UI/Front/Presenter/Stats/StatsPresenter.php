<?php

declare(strict_types=1);

namespace App\UI\Front\Presenter\Stats;

use App\Model\Database\Repository\StatsRepository;
use App\UI\Front\Presenter\BaseFrontPresenter;


class StatsPresenter extends BaseFrontPresenter
{
	use Action\DefaultAction;

	public const array Periods = [7, 30, 90];

	protected string $railActive = 'stats';


	public function __construct(
		private readonly StatsRepository $statsRepository,
	) {
		parent::__construct();
	}
}
