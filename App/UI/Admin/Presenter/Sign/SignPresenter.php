<?php

declare(strict_types=1);

namespace App\UI\Admin\Presenter\Sign;

use App\UI\Admin\Form\SignInFormFactory;
use App\UI\Shared\BasePresenter;
use Nette\Application\Attributes\Persistent;


class SignPresenter extends BasePresenter
{
	use Action\InAction;
	use Action\OutAction;

	#[Persistent]
	public string $backlink = '';


	public function __construct(
		private readonly SignInFormFactory $signInFormFactory,
	) {
		parent::__construct();
	}
}
