<?php

namespace App\Models;

use App\Helpers\DB;
use Psr\Container\ContainerInterface;

class BaseModel {
	protected $container;
	protected $db;

	//Constructor
	public function __construct(ContainerInterface $ci) {
		$this->container = $ci;
		$this->db = new DB($ci);
	}
}