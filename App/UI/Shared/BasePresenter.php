<?php

declare(strict_types=1);

namespace App\UI\Shared;

use Nette\Application\UI\Presenter;


abstract class BasePresenter extends Presenter
{
	protected function beforeRender(): void
	{
		$this->template->identity = $this->getUser()->isLoggedIn() ? $this->getUser()->getIdentity() : null;
	}
}
