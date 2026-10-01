<?php

declare(strict_types=1);

namespace App\UI\Admin\Presenter\Feed\Action;

use App\Model\Database\Entity\Feed;
use App\UI\Admin\Presenter\Feed\FeedPresenter;
use Nette\Application\UI\Form;


/** @phpstan-require-extends FeedPresenter */
trait AddAction
{
	protected function createComponentAddForm(): Form
	{
		return $this->feedFormFactory->create(null, function (Feed $feed): void {
			$this->flashMessage("Feed {$feed->getTitle()} přidán a stažen.", 'success');
			$this->redirect('default');
		});
	}
}
