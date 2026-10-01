<?php

declare(strict_types=1);

namespace App\UI\Admin\Form;

use App\Model\Database\Entity\User;
use App\Model\Security\UserException;
use App\Model\Security\UserFacade;
use Nette\Application\UI\Form;


final class UserFormFactory
{
	public function __construct(
		private readonly UserFacade $users,
		private readonly \Nette\Security\User $currentUser,
	) {
	}


	/** @param callable(User): void $onSuccess */
	public function create(?User $user, callable $onSuccess): Form
	{
		$form = new Form;
		$form->addText('name', 'Jméno')
			->setMaxLength(255)
			->setRequired('Zadejte jméno.');
		$form->addEmail('email', 'E-mail')
			->setMaxLength(255)
			->setRequired('Zadejte e-mail.')
			->setOption('description', 'Slouží jako přihlašovací jméno.');
		$password = $form->addPassword('password', $user ? 'Nové heslo' : 'Heslo')
			->setHtmlAttribute('autocomplete', 'new-password')
			->setOption('description', ($user ? 'Nechte prázdné, pokud heslo neměníte. ' : '') . 'Alespoň ' . UserFacade::MinPasswordLength . ' znaků.');
		if ($user) {
			$password->addCondition($form::Filled)->addRule($form::MinLength, null, UserFacade::MinPasswordLength);
			$form->addCheckbox('active', 'Aktivní (může se přihlásit)');
			$form->setDefaults(['name' => $user->getName(), 'email' => $user->getEmail(), 'active' => $user->isActive()]);
		} else {
			$password->setRequired('Zadejte heslo.')->addRule($form::MinLength, null, UserFacade::MinPasswordLength);
		}
		$form->addSubmit('send', $user ? 'Uložit' : 'Vytvořit uživatele');

		$form->onSuccess[] = function (Form $form, \stdClass $data) use ($user, $onSuccess): void {
			try {
				if ($user) {
					$this->users->update($user, $data->email, $data->name, $data->active, $data->password, $this->currentUserId());
				} else {
					$user = $this->users->create($data->email, $data->name, $data->password);
				}
			} catch (UserException $e) {
				$form->addError($e->getMessage());
				return;
			}
			$onSuccess($user);
		};
		return $form;
	}


	private function currentUserId(): ?int
	{
		$id = $this->currentUser->getId();
		return $id !== null ? (int) $id : null;
	}
}
