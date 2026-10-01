<?php

declare(strict_types=1);

namespace App\UI\Admin\Presenter\Feed;

use App\Model\Database\Repository\FeedRepository;
use App\Model\Feed\FeedFacade;
use App\Model\Feed\FeedFetcher;
use App\UI\Admin\Form\FeedFormFactory;
use App\UI\Admin\Presenter\BaseAdminPresenter;


class FeedPresenter extends BaseAdminPresenter
{
	use Action\DefaultAction;
	use Action\AddAction;
	use Action\EditAction;

	protected string $adminActive = 'feeds';


	public function __construct(
		private readonly FeedRepository $feedRepository,
		private readonly FeedFacade $feedFacade,
		private readonly FeedFetcher $feedFetcher,
		private readonly FeedFormFactory $feedFormFactory,
	) {
		parent::__construct();
	}
}
