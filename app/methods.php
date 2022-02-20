<?php

declare(strict_types=1);

use DI\Container;

/*
 * Allowed API methods
 */
return function (Container $container) {
	$container->set('methods', function() {
		return [
			'/help' => [
				'type' => 'get',
				'params' => [
				],
			],
			'/auth/register' => [
				'type' => 'post',
				'params' => [
					'email', 'nickname', 'password'
				],
			],
			'/auth/getAuthKey' => [
				'type' => 'post',
				'params' => [
					'nickname', 'password'
				],
			],
			'/actions/setStatus' => [
				'type' => 'put',
				'params' => [
					'auth', 'type', '?id', 'status'
				],
			],
			'/actions/getNewMessages' => [
				'type' => 'get',
				'params' => [
					'auth'
				],
			],
			'/actions/displayMessages/{type}/{id}/{page}' => [
				'type' => 'get',
				'params' => [
					'auth', 'type', 'id', 'page'
				],
			],
			'/actions/listUsers/{page}' => [
				'type' => 'get',
				'params' => [
					'auth'
				],
			],
			'/actions/listGroups/{page}' => [
				'type' => 'get',
				'params' => [
					'auth'
				],
			],
			'/actions/sendMessage' => [
				'type' => 'post',
				'params' => [
					'auth', 'type', 'id', 'message'
				],
			],
			'/actions/editMessage' => [
				'type' => 'put',
				'params' => [
					'auth', 'message_id', 'message'
				],
			],
			'/actions/deleteMessage' => [
				'type' => 'delete',
				'params' => [
					'auth', 'message_id'
				],
			],
			'/actions/createGroup' => [
				'type' => 'post',
				'params' => [
					'auth', 'name'
				],
			],
			'/actions/setOwner' => [
				'type' => 'put',
				'params' => [
					'auth', 'group_id', 'user_id'
				],
			],
			'/actions/addUserToGroup' => [
				'type' => 'post',
				'params' => [
					'auth', 'group_id', 'user_id'
				],
			],
			'/actions/leaveGroup' => [
				'type' => 'post',
				'params' => [
					'auth', 'group_id'
				],
			],
			'/actions/deleteGroup' => [
				'type' => 'delete',
				'params' => [
					'auth', 'group_id'
				],
			],
		];
	});
};
