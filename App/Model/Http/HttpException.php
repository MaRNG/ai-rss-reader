<?php

declare(strict_types=1);

namespace App\Model\Http;


class HttpException extends \RuntimeException
{
	/** Chyba, kterou nemá smysl opakovat (zakázaná adresa, příliš velký soubor…) */
	public bool $permanent = false;


	public static function permanent(string $message): self
	{
		$e = new self($message);
		$e->permanent = true;
		return $e;
	}
}
