<?php

declare(strict_types=1);

namespace App\UI\Shared;

use Nette\Bridges\ApplicationLatte\Template;
use Nette\Security\IIdentity;


class LayoutTemplate extends Template
{
	public ?IIdentity $identity;
	public string $railActive = '';
	/** @var list<\stdClass> */
	public array $flashes = [];
}
