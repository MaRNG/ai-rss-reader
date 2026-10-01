<?php

declare(strict_types=1);

namespace App\UI\Front\Presenter\Article;

use App\Model\Database\Repository\ArticleRepository;
use App\UI\Front\Presenter\BaseFrontPresenter;
use Doctrine\ORM\EntityManagerInterface;


class ArticlePresenter extends BaseFrontPresenter
{
	use Action\DetailAction;

	protected string $railActive = 'news';


	public function __construct(
		private readonly ArticleRepository $articleRepository,
		private readonly EntityManagerInterface $em,
	) {
		parent::__construct();
	}
}
