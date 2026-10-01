<?php

declare(strict_types=1);

namespace App\UI\Admin\Presenter\User;

use App\Model\Database\Repository\UserRepository;
use App\Model\Security\UserFacade;
use App\UI\Admin\Form\UserFormFactory;
use App\UI\Admin\Presenter\BaseAdminPresenter;


class UserPresenter extends BaseAdminPresenter
{
	use Action\DefaultAction;
	use Action\AddAction;
	use Action\EditAction;

	protected string $adminActive = 'users';


	public function __construct(
		private readonly UserRepository $userRepository,
		private readonly UserFacade $userFacade,
		private readonly UserFormFactory $userFormFactory,
	) {
		parent::__construct();
	}
}
