<?php

declare(strict_types=1);

use DI\Container;
use Monolog\Logger;

/*
 * App Settings
 * Configuration settings are defined here
 */
return function (Container $container) {
	$container->set('settings', function() {
		return [
			'name' => 'Chatapp!',
			'displayErrorDetails' => true,
			'logErrorDetails' => true,
			'logErrors' => true,
			'logger' => [
				'name' => 'slim-app',
				'path' => __DIR__ . '/../logs/app.log',
				'level' => Logger::DEBUG,
			],
			'connection' => [
				'host' => 'localhost',
				'dbname' => 'chatapp',
				'dbuser' => 'root',
				'dbpass' => 'root',
			]
		];
	});
};
