<?php

declare(strict_types=1);

namespace App\UI\Admin\Presenter\User\Action;

use App\Model\Database\Entity\User;
use App\UI\Admin\Presenter\User\UserPresenter;
use Nette\Application\UI\Form;


/** @phpstan-require-extends UserPresenter */
trait EditAction
{
	private ?User $editedUser = null;


	public function actionEdit(int $id): void
	{
		$this->editedUser = $this->userRepository->find($id) ?? $this->error('Uživatel neexistuje.');
	}


	public function renderEdit(int $id): void
	{
		$this->template->editedUser = $this->editedUser;
	}


	protected function createComponentEditForm(): Form
	{
		$user = $this->editedUser ?? $this->error();
		return $this->userFormFactory->create($user, function (User $user): void {
			$this->flashMessage("Uživatel {$user->getEmail()} uložen.", 'success');
			$this->redirect('default');
		});
	}
}
