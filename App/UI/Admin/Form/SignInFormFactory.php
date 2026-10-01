<?php

declare(strict_types=1);

namespace App\UI\Admin\Form;

use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Security\User;


final class SignInFormFactory
{
	public function __construct(
		private readonly User $user,
	) {
	}


	public function create(callable $onSuccess): Form
	{
		$form = new Form;
		$form->addEmail('email', 'E-mail')
			->setRequired('Zadejte e-mail.')
			->setHtmlAttribute('autocomplete', 'username')
			->setHtmlAttribute('autofocus');
		$form->addPassword('password', 'Heslo')
			->setRequired('Zadejte heslo.')
			->setHtmlAttribute('autocomplete', 'current-password');
		$form->addSubmit('send', 'Přihlásit');

		$form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess): void {
			try {
				$this->user->login($data->email, $data->password);
			} catch (AuthenticationException $e) {
				$form->addError($e->getMessage());
				return;
			}
			$onSuccess();
		};
		return $form;
	}
}
