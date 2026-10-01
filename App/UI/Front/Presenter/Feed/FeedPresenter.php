<?php

declare(strict_types=1);

namespace App\UI\Front\Presenter\Feed;

use App\Model\Database\Repository\ArticleRepository;
use App\Model\Database\Repository\FeedRepository;
use App\UI\Front\Presenter\BaseFrontPresenter;


class FeedPresenter extends BaseFrontPresenter
{
	use Action\DefaultAction;
	use Action\DetailAction;

	public const int PerPage = 30;

	protected string $railActive = 'feeds';


	public function __construct(
		private readonly FeedRepository $feedRepository,
		private readonly ArticleRepository $articleRepository,
	) {
		parent::__construct();
	}
}
