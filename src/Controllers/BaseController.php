<?php

namespace App\Controllers;

use App\Models\Users;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\{
	ServerRequestInterface as Request,
	ResponseInterface as Response
};

class BaseController {
	protected $container;

	//Constructor
	public function __construct(ContainerInterface $ci) {
		$this->container = $ci;
	}

	public function addLog($data) {
		$this->container->get(LoggerInterface::class)->debug('log', ['data' => $data]);
	}

	public function getAuthUser(Request $request) {
		$userModel = new Users($this->container);
		$auth = $request->getParam('auth');
		return $userModel->getAuthUserId($auth);
	}

	public function authFailed(Response $response) {
		return $response
			->withStatus(401)
			->withHeader('content-type', 'application/json')
			->withJson([
				"result" => "error",
				"message" => "Please check your auth token.",
			]);
	}
}