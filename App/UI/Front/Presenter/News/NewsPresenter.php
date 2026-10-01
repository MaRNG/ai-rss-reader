<?php

declare(strict_types=1);

namespace App\UI\Front\Presenter\News;

use App\Model\Database\Repository\ArticleRepository;
use App\UI\Front\Presenter\BaseFrontPresenter;


class NewsPresenter extends BaseFrontPresenter
{
	use Action\DefaultAction;

	public const int PerPage = 30;

	protected string $railActive = 'news';


	public function __construct(
		private readonly ArticleRepository $articleRepository,
	) {
		parent::__construct();
	}
}
