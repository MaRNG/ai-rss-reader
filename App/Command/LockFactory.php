<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Lock\LockFactory as SymfonyLockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\FlockStore;


/**
 * Zámky pro dlouhé commandy, aby se dva běhy cronu nepřekrývaly.
 */
final class LockFactory
{
	private readonly SymfonyLockFactory $factory;


	public function __construct(string $lockDir)
	{
		if (!is_dir($lockDir)) {
			@mkdir($lockDir, 0o777, true);
		}
		$this->factory = new SymfonyLockFactory(new FlockStore($lockDir));
	}


	public function create(string $name): LockInterface
	{
		return $this->factory->createLock('siftly-' . $name, null);
	}
}
