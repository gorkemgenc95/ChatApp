<?php

namespace App\Helpers;

use Respect\Validation\Validator as Respect;

class Validator extends Respect {
	public static function validateEmail($email) {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return false;
		}
		return true;
	}
}