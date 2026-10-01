<?php

declare(strict_types=1);

namespace App\UI\Admin\Presenter\Dashboard;

use App\UI\Admin\Presenter\BaseAdminPresenter;


/** Rozcestník administrace – zatím rovnou správa feedů */
final class DashboardPresenter extends BaseAdminPresenter
{
	public function actionDefault(): void
	{
		$this->redirect(':Admin:Feed:');
	}
}
