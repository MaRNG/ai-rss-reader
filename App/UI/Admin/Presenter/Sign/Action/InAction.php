<?php

declare(strict_types=1);

namespace App\UI\Admin\Presenter\Sign\Action;

use App\UI\Admin\Presenter\Sign\SignPresenter;
use Nette\Application\UI\Form;


/** @phpstan-require-extends SignPresenter */
trait InAction
{
	public function actionIn(): void
	{
		if ($this->getUser()->isLoggedIn()) {
			$this->redirect(':Admin:Dashboard:');
		}
	}


	protected function createComponentSignInForm(): Form
	{
		return $this->signInFormFactory->create(function (): void {
			$this->restoreRequest($this->backlink);
			$this->redirect(':Admin:Dashboard:');
		});
	}
}
