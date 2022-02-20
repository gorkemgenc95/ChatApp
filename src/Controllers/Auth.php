<?php

namespace App\Controllers;

use App\Models\Users;
use App\Helpers\Validator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\{
	ServerRequestInterface as Request,
	ResponseInterface as Response
};

class Auth extends BaseController {
	//Constructor
	public function __construct(ContainerInterface $ci) {
		parent::__construct($ci);
	}

	public function getAuthKey (Request $request, Response $response, array $args) {
		$users_model = new Users($this->container);
		$nickname = $request->getParam('nickname');
		$password = $request->getParam('password');
		$hash = $users_model->login($nickname, md5($password));
		if ($hash) {
			return $response
				->withStatus(200)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "success",
					"message" => "Login successful. Use your auth key in your next API calls.",
					"data" => [
						"auth" => $hash
					]
				]);
		} else {
			return $response
				->withStatus(401)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "error",
					"message" => "Login failed"
				]);
		}

	}

	public function register (Request $request, Response $response, array $args) {
		$users_model = new Users($this->container);
		$email = $request->getParam('email');
		$nickname = $request->getParam('nickname');
		$password = $request->getParam('password');

		// Check email and nickname validity
		$valid = true;
		$err_msg = "";
		if (Validator::validateEmail($email) === false) {
			$valid = false;
			$err_msg = "Not a valid email: {$email}";
		} else if ($users_model->getUserByEmail($email)) {
			$valid = false;
			$err_msg = "There is already an account with this email: {$email}";
		} else if ($users_model->getUserByNickname($nickname)) {
			$valid = false;
			$err_msg = "There is already an account with this nickname: {$nickname}";
		}
		if ($valid) {
			$success = $users_model->create([
				"nickname" => $nickname,
				"password" => md5($password),
				"email"    => $email,
			]);
			if ($success) {
				return $response
					->withStatus(200)
					->withHeader('content-type', 'application/json')
					->withJson([
						"result" => "success",
						"message" => "Account created"
					]);
			} else {
				return $response
					->withStatus(500)
					->withHeader('content-type', 'application/json')
					->withJson([
						"result" => "error",
						"message" => "An error occurred creating account"
					]);
			}
		} else {
			return $response
				->withStatus(400)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "error",
					"message" => $err_msg
				]);
		}
	}
}