<?php

declare(strict_types=1);

namespace App\UI\Admin\Presenter\User\Action;

use App\Model\Security\UserException;
use App\UI\Admin\Presenter\User\UserPresenter;
use Nette\Application\Attributes\Requires;


/** @phpstan-require-extends UserPresenter */
trait DefaultAction
{
	public function renderDefault(): void
	{
		$this->template->users = $this->userRepository->findAll();
		$this->template->currentUserId = (int) $this->getUser()->getId();
	}


	#[Requires(methods: 'POST', sameOrigin: true)]
	public function handleDelete(int $id): void
	{
		$user = $this->userRepository->find($id) ?? $this->error('Uživatel neexistuje.');
		try {
			$this->userFacade->delete($user, (int) $this->getUser()->getId());
			$this->flashMessage("Uživatel {$user->getEmail()} smazán.", 'success');
		} catch (UserException $e) {
			$this->flashMessage($e->getMessage(), 'error');
		}
		$this->redirect('default');
	}
}
