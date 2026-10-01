<?php

declare(strict_types=1);

namespace App;

use Nette;
use Nette\Bootstrap\Configurator;


final class Bootstrap
{
	private readonly Configurator $configurator;
	private readonly string $rootDir;


	public function __construct()
	{
		$this->rootDir = dirname(__DIR__);
		$this->configurator = new Configurator;
		$this->configurator->setTempDirectory($this->rootDir . '/var/temp');
	}


	public function bootWebApplication(): Nette\DI\Container
	{
		$this->initializeEnvironment();
		$this->setupContainer();
		return $this->configurator->createContainer();
	}


	public function bootConsoleApplication(): Nette\DI\Container
	{
		$this->configurator->setDebugMode(getenv('SIFTLY_DEBUG') === '1');
		$this->configurator->enableTracy($this->rootDir . '/var/log');
		$this->setupContainer();
		return $this->configurator->createContainer();
	}


	private function initializeEnvironment(): void
	{
		// Debug režim jen z lokálu; na serveru lze vynutit souborem var/debug
		$this->configurator->setDebugMode(is_file($this->rootDir . '/var/debug') ?: ['127.0.0.1', '::1']);
		$this->configurator->enableTracy($this->rootDir . '/var/log');
	}


	private function setupContainer(): void
	{
		// Mimo debug nelogovat deprecated hlášky knihoven (SimplePie na PHP 8.5 jich hlásí spoustu)
		if (!$this->configurator->isDebugMode()) {
			error_reporting(E_ALL & ~E_DEPRECATED);
		}

		$configDir = $this->rootDir . '/config';
		$this->configurator->addStaticParameters([
			'rootDir' => $this->rootDir,
			'wwwDir' => $this->rootDir . '/www',
			'varDir' => $this->rootDir . '/var',
		]);
		$this->configurator->addConfig($configDir . '/common.neon');
		$this->configurator->addConfig($configDir . '/services.neon');
		$this->configurator->addConfig($configDir . '/doctrine.neon');
		$this->configurator->addConfig($configDir . '/local.neon');
	}
}
