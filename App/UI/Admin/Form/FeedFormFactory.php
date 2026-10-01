<?php

declare(strict_types=1);

namespace App\UI\Admin\Form;

use App\Model\Database\Entity\Feed;
use App\Model\Feed\FeedException;
use App\Model\Feed\FeedFacade;
use Nette\Application\UI\Form;


final class FeedFormFactory
{
	public function __construct(
		private readonly FeedFacade $feeds,
	) {
	}


	/** @param callable(Feed): void $onSuccess */
	public function create(?Feed $feed, callable $onSuccess): Form
	{
		$form = new Form;
		$form->addText('url', 'Adresa feedu (RSS/Atom)')
			->setHtmlType('url')
			->setRequired('Zadejte adresu feedu.')
			->addRule($form::URL, 'Zadejte platnou adresu.')
			->setMaxLength(2048)
			->setHtmlAttribute('placeholder', 'https://www.example.cz/rss');
		$form->addText('title', 'Název')
			->setMaxLength(255)
			->setRequired($feed !== null ? 'Zadejte název.' : false)
			->setOption('description', $feed ? null : 'Nepovinné – bez vyplnění se převezme z feedu.');
		$form->addCheckbox('fetchFullText', 'Stahovat plný text článků')
			->setOption('description', 'Pro feedy, které posílají jen perex. Stažení je pomalejší.');

		if ($feed) {
			$form->addCheckbox('active', 'Aktivní');
			$form->setDefaults([
				'url' => $feed->getUrl(),
				'title' => $feed->getTitle(),
				'fetchFullText' => $feed->isFetchFullText(),
				'active' => $feed->isActive(),
			]);
		}
		$form->addSubmit('send', $feed ? 'Uložit' : 'Přidat feed');

		$form->onSuccess[] = function (Form $form, \stdClass $data) use ($feed, $onSuccess): void {
			try {
				if ($feed) {
					$this->feeds->update($feed, $data->url, $data->title, $data->fetchFullText, $data->active);
				} else {
					$feed = $this->feeds->add($data->url, $data->title, $data->fetchFullText);
				}
			} catch (FeedException $e) {
				$form->addError($e->getMessage());
				return;
			}
			$onSuccess($feed);
		};
		return $form;
	}
}
