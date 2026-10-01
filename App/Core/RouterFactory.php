<?php

declare(strict_types=1);

namespace App\Core;

use Nette\Application\Routers\RouteList;
use Nette\StaticClass;


final class RouterFactory
{
	use StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;

		$router->withModule('Admin')
			->addRoute('admin/<presenter>/<action>[/<id \d+>]', 'Dashboard:default');

		$router->withModule('Front')
			->addRoute('news', 'News:default')
			->addRoute('feeds', 'Feed:default')
			->addRoute('feeds/<id \d+>', 'Feed:detail')
			->addRoute('article/<id \d+>', 'Article:detail')
			->addRoute('stats', 'Stats:default')
			->addRoute('', 'Home:default');

		return $router;
	}
}
