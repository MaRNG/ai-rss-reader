<?php

declare(strict_types=1);

namespace App\UI\Front\Presenter\Error4xx;

use App\UI\Front\Presenter\BaseFrontPresenter;
use Nette\Application\Attributes\Requires;
use Nette\Application\BadRequestException;


#[Requires(forward: true)]
final class Error4xxPresenter extends BaseFrontPresenter
{
	public function renderDefault(BadRequestException $exception): void
	{
		$code = $exception->getCode();
		$this->template->httpCode = $code;
		$this->template->message = match ($code) {
			403 => 'K této stránce nemáte přístup.',
			404 => 'Tahle stránka neexistuje. Možná byl článek smazán, nebo je odkaz špatně.',
			405 => 'Tento požadavek není povolený.',
			default => 'Požadavek se nepodařilo zpracovat.',
		};
	}
}
