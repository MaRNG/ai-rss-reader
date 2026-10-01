<?php

declare(strict_types=1);

namespace App\UI\Shared\Trait;

use App\Model\Database\Entity\Article;
use App\Model\Database\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Application\Attributes\Requires;
use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;


/**
 * Signály hvězdička / přečteno / 👍👎 pro výpisy i detail článku. Jen pro přihlášené, jen POST.
 * @phpstan-require-extends Presenter
 */
trait ArticleFeedbackSignals
{
	#[Inject]
	public ArticleRepository $feedbackArticles;

	#[Inject]
	public EntityManagerInterface $feedbackEm;


	#[Requires(methods: 'POST', sameOrigin: true)]
	public function handleStar(int $articleId, bool $starred): void
	{
		$article = $this->getArticleForFeedback($articleId);
		$article->setStarred($starred);
		$this->finishFeedback(['starred' => $article->isStarred()]);
	}


	#[Requires(methods: 'POST', sameOrigin: true)]
	public function handleRead(int $articleId, bool $read): void
	{
		$article = $this->getArticleForFeedback($articleId);
		$read ? $article->markRead() : $article->markUnread();
		$this->finishFeedback(['read' => $article->isRead()]);
	}


	#[Requires(methods: 'POST', sameOrigin: true)]
	public function handleFeedback(int $articleId, int $value): void
	{
		$article = $this->getArticleForFeedback($articleId);
		$article->setFeedback($value === $article->getFeedback() ? Article::FeedbackNone : $value);
		$this->finishFeedback(['feedback' => $article->getFeedback()]);
	}


	private function getArticleForFeedback(int $id): Article
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->error('Přihlaste se.', 403);
		}
		return $this->feedbackArticles->find($id) ?? $this->error('Článek neexistuje.');
	}


	/** @param array<string, mixed> $payload */
	private function finishFeedback(array $payload): void
	{
		$this->feedbackEm->flush();
		if ($this->isAjax()) {
			foreach ($payload as $key => $value) {
				$this->payload->$key = $value;
			}
			$this->sendPayload();
		}
		$this->redirect('this');
	}
}
