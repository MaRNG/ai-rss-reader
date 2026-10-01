<?php

declare(strict_types=1);

namespace App\UI\Front\Presenter\Home;

use App\Model\Database\Repository\ArticleRepository;
use App\UI\Front\Presenter\BaseFrontPresenter;


class HomePresenter extends BaseFrontPresenter
{
	use Action\DefaultAction;

	protected string $railActive = 'home';


	public function __construct(
		private readonly ArticleRepository $articleRepository,
	) {
		parent::__construct();
	}
}
