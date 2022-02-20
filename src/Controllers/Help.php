<?php

namespace App\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\{
	ServerRequestInterface as Request,
	ResponseInterface as Response
};

class Help extends BaseController {
	//Constructor
	public function __construct(ContainerInterface $ci) {
		parent::__construct($ci);
	}

	public function index (Request $request, Response $response, array $args) {
		return $response
			->withStatus(200)
			->withHeader('content-type', 'application/json')
			->withJson([
				"result" => "success",
				"message" => "Please login to get your auth key and use it in your calls.",
				"allowedMethods" => $this->container->get('methods')
			]);
	}


}