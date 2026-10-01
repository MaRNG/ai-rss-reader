<?php

declare(strict_types=1);

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: 'setting')]
class Setting
{
	public const string InterestProfile = 'interest_profile';
	public const string DigestTopN = 'digest_top_n';
	public const string DigestLanguage = 'digest_language';

	#[ORM\Id]
	#[ORM\Column(length: 64)]
	private string $name;

	#[ORM\Column(type: 'text')]
	private string $value;


	public function __construct(string $name, string $value)
	{
		$this->name = $name;
		$this->value = $value;
	}


	public function getName(): string
	{
		return $this->name;
	}


	public function getValue(): string
	{
		return $this->value;
	}


	public function setValue(string $value): void
	{
		$this->value = $value;
	}
}
