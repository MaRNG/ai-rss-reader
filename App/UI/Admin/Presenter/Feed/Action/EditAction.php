<?php

declare(strict_types=1);

namespace App\UI\Admin\Presenter\Feed\Action;

use App\Model\Database\Entity\Feed;
use App\UI\Admin\Presenter\Feed\FeedPresenter;
use Nette\Application\UI\Form;


/** @phpstan-require-extends FeedPresenter */
trait EditAction
{
	private ?Feed $editedFeed = null;


	public function actionEdit(int $id): void
	{
		$this->editedFeed = $this->getFeed($id);
	}


	public function renderEdit(int $id): void
	{
		$this->template->feed = $this->editedFeed;
	}


	protected function createComponentEditForm(): Form
	{
		$feed = $this->editedFeed ?? $this->error();
		return $this->feedFormFactory->create($feed, function (Feed $feed): void {
			$this->flashMessage("Feed {$feed->getTitle()} uložen.", 'success');
			$this->redirect('default');
		});
	}
}
