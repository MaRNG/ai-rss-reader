<?php

declare(strict_types=1);

namespace App\UI\Front\Presenter\Article\Action;

use App\Model\Database\Entity\Article;
use App\UI\Front\Presenter\Article\ArticlePresenter;


/**
 * Čtení článku v aplikaci s proklikem na zdroj. Přihlášenému se článek označí jako přečtený.
 * @phpstan-require-extends ArticlePresenter
 */
trait DetailAction
{
	private Article $article;


	public function actionDetail(int $id): void
	{
		$article = $this->articleRepository->find($id);
		if (!$article || !$article->getFeed()->isActive()) {
			$this->error('Článek neexistuje.');
		}
		$this->article = $article;
		$this->activeFeedId = $article->getFeed()->getId();

		if ($this->getUser()->isLoggedIn() && !$article->isRead() && !$this->isSignalReceiver($this)) {
			$article->markRead();
			$this->em->flush();
		}
	}


	public function renderDetail(int $id): void
	{
		$this->template->article = $this->article;
		$this->template->neighbours = $this->articleRepository->findNeighbours($this->article);
	}
}
