<?php

declare(strict_types=1);

namespace App\UI\Admin\Presenter;

use App\UI\Shared\BasePresenter;


abstract class BaseAdminPresenter extends BasePresenter
{
	protected string $adminActive = '';


	protected function startup(): void
	{
		parent::startup();
		if (!$this->getUser()->isLoggedIn()) {
			if ($this->getUser()->getLogoutReason() === \Nette\Security\UserStorage::LOGOUT_INACTIVITY) {
				$this->flashMessage('Byli jste odhlášeni kvůli neaktivitě. Přihlaste se prosím znovu.', 'info');
			}
			$this->redirect(':Admin:Sign:in', ['backlink' => $this->storeRequest()]);
		}
	}


	protected function beforeRender(): void
	{
		parent::beforeRender();
		$this->template->railActive = 'admin';
		$this->template->adminActive = $this->adminActive;
	}
}
