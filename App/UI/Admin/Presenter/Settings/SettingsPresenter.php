<?php

declare(strict_types=1);

namespace App\UI\Admin\Presenter\Settings;

use App\UI\Admin\Form\SettingsFormFactory;
use App\UI\Admin\Presenter\BaseAdminPresenter;


class SettingsPresenter extends BaseAdminPresenter
{
	use Action\DefaultAction;

	protected string $adminActive = 'settings';


	public function __construct(
		private readonly SettingsFormFactory $settingsFormFactory,
	) {
		parent::__construct();
	}
}
