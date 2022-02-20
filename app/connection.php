<?php

declare(strict_types=1);

use DI\Container;

/*
 * Create database connection
 */
return function (Container $container) {
	$container->set('connection', function() use ($container) {
		$connection = $container->get('settings')['connection'];
		try {
			$connection = new PDO("mysql:host={$connection['host']};dbname={$connection['dbname']}", $connection['dbuser'], $connection['dbpass']);
			$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			echo "Connection failed: " . $e->getMessage();
		}
		return $connection;
	});
};
