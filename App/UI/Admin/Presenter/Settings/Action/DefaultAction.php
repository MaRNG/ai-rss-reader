<?php

declare(strict_types=1);

namespace App\UI\Admin\Presenter\Settings\Action;

use App\UI\Admin\Presenter\Settings\SettingsPresenter;
use Nette\Application\UI\Form;


/** @phpstan-require-extends SettingsPresenter */
trait DefaultAction
{
	protected function createComponentSettingsForm(): Form
	{
		return $this->settingsFormFactory->create(function (): void {
			$this->flashMessage('Nastavení uloženo.', 'success');
			$this->redirect('this');
		});
	}
}
