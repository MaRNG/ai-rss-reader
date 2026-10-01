<?php

declare(strict_types=1);

namespace App\UI\Admin\Presenter\Feed\Action;

use App\Model\Database\Entity\Feed;
use App\UI\Admin\Presenter\Feed\FeedPresenter;
use Nette\Application\Attributes\Requires;


/**
 * Seznam všech feedů (i neaktivních), stáhnout teď, smazat.
 * @phpstan-require-extends FeedPresenter
 */
trait DefaultAction
{
	public function renderDefault(): void
	{
		$this->template->feeds = $this->feedRepository->findAll();
	}


	#[Requires(methods: 'POST', sameOrigin: true)]
	public function handleFetch(int $id): void
	{
		$feed = $this->getFeed($id);
		$result = $this->feedFetcher->fetch($feed, force: true);
		if ($result->error) {
			$this->flashMessage("{$feed->getTitle()}: {$result->error}", 'error');
		} else {
			$this->flashMessage(sprintf('%s: staženo, %d nových článků. Obrázky stáhne cron (images:download).', $feed->getTitle(), $result->newArticles), 'success');
		}
		$this->redirect('this');
	}


	#[Requires(methods: 'POST', sameOrigin: true)]
	public function handleDelete(int $id): void
	{
		$feed = $this->getFeed($id);
		$title = $feed->getTitle();
		$this->feedFacade->delete($feed);
		$this->flashMessage("Feed $title byl smazán i s články a obrázky.", 'success');
		$this->redirect('default');
	}


	private function getFeed(int $id): Feed
	{
		return $this->feedRepository->find($id) ?? $this->error('Feed neexistuje.');
	}
}
