<?php

declare(strict_types=1);

use Slim\App;

/*
 * Dynamic Routing
 * New methods should be entered to the methods.php config
 * Routing logic: /{className}/{method}/{?parameters}
 */
return function (App $app) {
	$app->get('/', '\App\Controllers\Help:index');
	$container = $app->getContainer();
	$methods = $container->get('methods');
	foreach ($methods as $pattern => $config) {
		$routing = explode("/", trim($pattern, '/'));
		$class = ucfirst($routing[0]);
		$method = $routing[1] ?? "index";
		$callable = "\App\Controllers\\".$class.":".$method;
		switch ($config['type']) {
			case "post":
				$app->post($pattern, $callable);
				break;
			case "get":
				$app->get($pattern, $callable);
				break;
			case "put":
				$app->put($pattern, $callable);
				break;
			case "delete":
				$app->delete($pattern, $callable);
				break;
		}
	}

};
