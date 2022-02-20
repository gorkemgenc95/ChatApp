<?php

declare(strict_types=1);

use DI\Container;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Set Container on app
$container = setupContainer();
AppFactory::setContainer($container);

// Create App
$app = AppFactory::create();

$middleware = require __DIR__ . '/../app/middleware.php';
$middleware($app);

$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

// Run App
$app->run();

function setupContainer() {
	$container = new Container();

	$settings = require __DIR__ . '/../app/settings.php';
	$settings($container);

	$methods = require __DIR__ . '/../app/methods.php';
	$methods($container);

	$connection = require __DIR__ . '/../app/connection.php';
	$connection($container);

	$logger = require __DIR__ . '/../app/logger.php';
	$logger($container);

	return $container;
}
