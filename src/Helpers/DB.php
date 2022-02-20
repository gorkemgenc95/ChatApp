<?php

namespace App\Helpers;

use PDO;

Class DB {
	private $_container;

	public function __construct($container) {
		$this->_container = $container;
	}

	private function asArray($obj) {
		return json_decode(json_encode($obj), true);
	}

	public function getOne($query) {
		$conn = $this->_container->get('connection');
		$stmt = $conn->query($query);
		return $this->asArray($stmt->fetch(PDO::FETCH_OBJ));
	}

	public function getAll($query) {
		$conn = $this->_container->get('connection');
		$stmt = $conn->query($query);
		return $this->asArray($stmt->fetchAll(PDO::FETCH_OBJ));
	}

	public function execParam($query, $data = []) {
		$conn = $this->_container->get('connection');
		$prepare = $conn->prepare($query);
		$prepare->execute($data);
		return $conn->lastInsertId() > 0 ? $conn->lastInsertId() : true;
	}

}