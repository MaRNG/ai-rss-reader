<?php

declare(strict_types=1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;


final class SettingRepository
{
	public function __construct(
		private readonly EntityManagerInterface $em,
	) {
	}


	public function get(string $name, ?string $default = null): ?string
	{
		return $this->em->find(Setting::class, $name)?->getValue() ?? $default;
	}


	public function set(string $name, string $value): void
	{
		$setting = $this->em->find(Setting::class, $name);
		if ($setting) {
			$setting->setValue($value);
		} else {
			$this->em->persist(new Setting($name, $value));
		}
		$this->em->flush();
	}
}
