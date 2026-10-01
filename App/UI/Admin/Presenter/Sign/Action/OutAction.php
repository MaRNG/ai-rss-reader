<?php

declare(strict_types=1);

namespace App\UI\Admin\Presenter\Sign\Action;

use App\UI\Admin\Presenter\Sign\SignPresenter;


/** @phpstan-require-extends SignPresenter */
trait OutAction
{
	public function actionOut(): void
	{
		$this->getUser()->logout(true);
		$this->flashMessage('Byli jste odhlášeni.', 'info');
		$this->redirect(':Front:Home:');
	}
}
