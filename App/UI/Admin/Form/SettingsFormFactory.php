<?php

declare(strict_types=1);

namespace App\UI\Admin\Form;

use App\Model\Database\Entity\Setting;
use App\Model\Database\Repository\SettingRepository;
use Nette\Application\UI\Form;


final class SettingsFormFactory
{
	public function __construct(
		private readonly SettingRepository $settings,
	) {
	}


	public function create(callable $onSuccess): Form
	{
		$form = new Form;
		$form->addTextArea('interestProfile', 'Profil zájmů', null, 8)
			->setMaxLength(5000)
			->setHtmlAttribute('placeholder', 'Např.: PHP, Nette, AI, self-hosting, bezpečnost. Nezajímá mě krypto ani mobilní hry.')
			->setOption('description', 'Volný text o tom, co vás zajímá a co ne. Ve fázi 2 podle něj Claude vybírá články do ranního přehledu.');
		$form->setDefaults(['interestProfile' => $this->settings->get(Setting::InterestProfile, '')]);
		$form->addSubmit('send', 'Uložit');

		$form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess): void {
			$this->settings->set(Setting::InterestProfile, trim($data->interestProfile));
			$onSuccess();
		};
		return $form;
	}
}
