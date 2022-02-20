<?php

namespace App\Models;

use Psr\Container\ContainerInterface;

class Users extends BaseModel {
	//Constructor
	public function __construct(ContainerInterface $ci) {
		parent::__construct($ci);
	}

	public function list($page) {
		$offset = 10 * max(0, ($page-1));
		$query = "SELECT * FROM users LIMIT {$offset},10";
		$users = $this->db->getAll($query);
		$results = [];
		foreach ($users as $user) {
			$results[] = [
				'user_id' => $user['user_id'],
				'nickname' => $user['nickname'],
				'status' => $user['status']
			];
		}
		return $results;
	}

	public function create($data) {
		$query = "INSERT INTO users (nickname, password, email, status) VALUES (?, ?, ?, 'I am using ChatApp!!')";
		return $this->db->execParam($query, [$data['nickname'], $data['password'], $data['email']]);
	}

	public function login($nickname, $password) {
		$user = $this->db->getOne("SELECT * FROM users WHERE nickname='{$nickname}' AND password='{$password}'");
		if ($user) {
			$hash = md5(uniqid(rand(), true));
			$this->db->execParam("INSERT INTO auth_keys (user_id, auth_key) VALUES (?,?) ON DUPLICATE KEY UPDATE auth_key=?", [$user['user_id'],$hash,$hash]);
			return $hash;
		} else return false;
	}

	public function getAuthUserId($authKey) {
		$auth = $this->db->getOne("SELECT * FROM auth_keys WHERE auth_key='{$authKey}'");
		if ($auth) {
			return $auth['user_id'];
		} else return false;
	}

	public function setStatus($userID, $status) {
		$query = "UPDATE users SET status=? WHERE user_id=?";
		return $this->db->execParam($query, [$status, $userID]);
	}

	public function getUserByID($userID) {
		return $this->db->getOne("SELECT * FROM users WHERE user_id='{$userID}'");
	}

	public function getUserByEmail($email) {
		return $this->db->getOne("SELECT * FROM users WHERE email='{$email}'");
	}

	public function getUserByNickname($nickname) {
		return $this->db->getOne("SELECT * FROM users WHERE nickname='{$nickname}'");
	}

}