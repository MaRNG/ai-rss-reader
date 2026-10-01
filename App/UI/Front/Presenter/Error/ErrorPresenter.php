<?php

declare(strict_types=1);

namespace App\UI\Front\Presenter\Error;

use Nette;
use Nette\Application\Responses;
use Nette\Http;
use Tracy\ILogger;


/**
 * Chyby 5xx: zaloguje a zobrazí statickou stránku (bez DB, ta může být příčinou).
 */
final class ErrorPresenter implements Nette\Application\IPresenter
{
	use Nette\SmartObject;

	public function __construct(
		private readonly ILogger $logger,
	) {
	}


	public function run(Nette\Application\Request $request): Nette\Application\Response
	{
		$exception = $request->getParameter('exception');
		$this->logger->log($exception, ILogger::EXCEPTION);

		return new Responses\CallbackResponse(function (Http\IRequest $httpRequest, Http\IResponse $httpResponse): void {
			if (preg_match('#^text/html(?:;|$)#', (string) $httpResponse->getHeader('Content-Type'))) {
				require __DIR__ . '/500.phtml';
			}
		});
	}
}
