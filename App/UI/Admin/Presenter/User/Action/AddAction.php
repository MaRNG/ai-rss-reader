<?php

declare(strict_types=1);

namespace App\UI\Admin\Presenter\User\Action;

use App\Model\Database\Entity\User;
use App\UI\Admin\Presenter\User\UserPresenter;
use Nette\Application\UI\Form;


/** @phpstan-require-extends UserPresenter */
trait AddAction
{
	protected function createComponentAddForm(): Form
	{
		return $this->userFormFactory->create(null, function (User $user): void {
			$this->flashMessage("Uživatel {$user->getEmail()} vytvořen.", 'success');
			$this->redirect('default');
		});
	}
}
