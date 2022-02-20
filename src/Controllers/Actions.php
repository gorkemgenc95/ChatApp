<?php

namespace App\Controllers;

use App\Models\Groups;
use App\Models\Messages;
use App\Models\Users;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\{
	ServerRequestInterface as Request,
	ResponseInterface as Response
};

class Actions extends BaseController {
	//Constructor
	public function __construct(ContainerInterface $ci) {
		parent::__construct($ci);
	}

	public function getNewMessages (Request $request, Response $response, array $args) {
		$userID = $this->getAuthUser($request);
		if ($userID === false) {
			return $this->authFailed($response);
		}
		$messagesModel = new Messages($this->container);
		$newMessages = $messagesModel->getNew($userID);
		return $response
			->withStatus(200)
			->withHeader('content-type', 'application/json')
			->withJson([
				"result" => "success",
				"message" => "New messages requested",
				"data" => $newMessages
			]);
	}

	public function displayMessages (Request $request, Response $response, array $args) {
		$userID = $this->getAuthUser($request);
		if ($userID === false) {
			return $this->authFailed($response);
		}
		$messagesModel = new Messages($this->container);
		$groupsModel = new Groups($this->container);
		$type = $args['type'];
		$id = $args['id'];
		$page = $args['page'];

		if (!($type === 'user' || $type === 'group')) {
			$this->addLog([$userID, 'bad type', $type]);
			return $response
				->withStatus(400)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "error",
					"message" => "Type must be either group or user",
				]);
		}
		if ($type === 'group' && !$groupsModel->inGroup($id, $userID)) {
			$this->addLog([$userID, 'not in group', $type]);
			return $response
				->withStatus(400)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "error",
					"message" => "You are not in this group",
				]);
		}
		$messages = $messagesModel->display($userID, $type, $id, $page);
		return $response
			->withStatus(200)
			->withHeader('content-type', 'application/json')
			->withJson([
				"result" => "success",
				"message" => "Messages requested",
				"data" => $messages
			]);
	}

	public function setStatus (Request $request, Response $response, array $args) {
		$userID = $this->getAuthUser($request);
		if ($userID === false) {
			return $this->authFailed($response);
		}
		$type = $request->getParam('type');
		$status = $request->getParam('status');

		if ($type === 'user') {
			$model = new Users($this->container);
			$id = $userID;
		} else if ($type === 'group') {
			$model = new Groups($this->container);
			$id = $request->getParam('id');
			if (!$model->isOwner($userID, $id)) {
				$this->addLog([$userID, 'not group owner', $id]);
				return $response
					->withStatus(400)
					->withHeader('content-type', 'application/json')
					->withJson([
						"result" => "error",
						"message" => "You are not the owner of this group",
					]);
			}
		} else {
			$this->addLog([$userID, 'bad type', $type]);
			return $response
				->withStatus(400)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "error",
					"message" => "Type must be either group or user",
				]);
		}
		if ($model->setStatus($id, $status)) {
			return $response
				->withStatus(200)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "success",
					"message" => "Status set",
				]);
		} else {
			$this->addLog([$userID, 'status not set', $status]);
			return $response
				->withStatus(500)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "error",
					"message" => "Status could not be set",
				]);
		}
	}

	public function setOwner (Request $request, Response $response, array $args) {
		$userID = $this->getAuthUser($request);
		if ($userID === false) {
			return $this->authFailed($response);
		}
		$groupsModel = new Groups($this->container);
		$groupID = $request->getParam('group_id');
		$newOwnerID = $request->getParam('user_id');

		if ($groupsModel->isOwner($userID, $groupID) && $groupsModel->inGroup($groupID, $newOwnerID)) {
			if ($groupsModel->setOwner($groupID, $newOwnerID)) {
				return $response
					->withStatus(200)
					->withHeader('content-type', 'application/json')
					->withJson([
						"result" => "success",
						"message" => "New group owner set",
					]);
			} else {
				$this->addLog([$userID, 'group owner not set', [$groupID, $newOwnerID]]);
				return $response
					->withStatus(500)
					->withHeader('content-type', 'application/json')
					->withJson([
						"result" => "error",
						"message" => "New group owner could not be set",
					]);
			}
		} else {
			$this->addLog([$userID, 'not group owner', $groupID]);
			return $response
				->withStatus(400)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "error",
					"message" => "You are not the owner of the group or the new candidate is not in the group",
				]);
		}
	}

	public function addUserToGroup (Request $request, Response $response, array $args) {
		$userID = $this->getAuthUser($request);
		if ($userID === false) {
			return $this->authFailed($response);
		}
		$groupsModel = new Groups($this->container);
		$groupID = $request->getParam('group_id');
		$newUserID = $request->getParam('user_id');

		if ($groupsModel->isOwner($userID, $groupID)) {
			if ($groupsModel->addUser($groupID, $newUserID)) {
				return $response
					->withStatus(200)
					->withHeader('content-type', 'application/json')
					->withJson([
						"result" => "success",
						"message" => "New user added",
					]);
			} else {
				$this->addLog([$userID, 'user not added', [$groupID, $newUserID]]);
				return $response
					->withStatus(500)
					->withHeader('content-type', 'application/json')
					->withJson([
						"result" => "error",
						"message" => "New user could not be added",
					]);
			}
		} else {
			$this->addLog([$userID, 'not group owner', $groupID]);
			return $response
				->withStatus(400)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "error",
					"message" => "You are not the owner of the group",
				]);
		}
	}

	public function createGroup (Request $request, Response $response, array $args) {
		$userID = $this->getAuthUser($request);
		if ($userID === false) {
			return $this->authFailed($response);
		}
		$groupsModel = new Groups($this->container);
		$groupName = $request->getParam('name');
		$groupID = $groupsModel->create($groupName, $userID);
		if ($groupID) {
			if (!$groupsModel->addUser($groupID, $userID)) {
				$this->addLog([$userID, 'New user could not be added', [$groupID, $userID]]);
				return $response
					->withStatus(500)
					->withHeader('content-type', 'application/json')
					->withJson([
						"result" => "error",
						"message" => "",
					]);
			}
			return $response
				->withStatus(200)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "success",
					"message" => "Group created",
				]);
		} else {
			$this->addLog([$userID, 'group not created', $groupName]);
			return $response
				->withStatus(500)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "error",
					"message" => "Group could not be created",
				]);
		}
	}

	public function deleteGroup (Request $request, Response $response, array $args) {
		$userID = $this->getAuthUser($request);
		if ($userID === false) {
			return $this->authFailed($response);
		}
		$groupsModel = new Groups($this->container);
		$groupID = $request->getParam('group_id');

		if ($groupsModel->isOwner($userID, $groupID)) {
			if ($groupsModel->delete($groupID)) {
				return $response
					->withStatus(200)
					->withHeader('content-type', 'application/json')
					->withJson([
						"result" => "success",
						"message" => "Group deleted",
					]);
			} else {
				$this->addLog([$userID, 'group not deleted', $groupID]);
				return $response
					->withStatus(500)
					->withHeader('content-type', 'application/json')
					->withJson([
						"result" => "error",
						"message" => "Group could not be deleted",
					]);
			}
		} else {
			$this->addLog([$userID, 'not group owner', $groupID]);
			return $response
				->withStatus(400)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "error",
					"message" => "You are not the owner of the group",
				]);
		}
	}

	public function leaveGroup (Request $request, Response $response, array $args) {
		$userID = $this->getAuthUser($request);
		if ($userID === false) {
			return $this->authFailed($response);
		}
		$groupsModel = new Groups($this->container);
		$groupID = $request->getParam('group_id');

		if ($groupsModel->isOwner($userID, $groupID)) {
			return $response
				->withStatus(400)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "error",
					"message" => "Please promote another member to owner before you leave",
				]);
		} else {
			if ($groupsModel->removeUser($groupID, $userID)) {
				return $response
					->withStatus(200)
					->withHeader('content-type', 'application/json')
					->withJson([
						"result" => "success",
						"message" => "You left the group",
					]);
			} else {
				$this->addLog([$userID, 'leaving group failed', $groupID]);
				return $response
					->withStatus(500)
					->withHeader('content-type', 'application/json')
					->withJson([
						"result" => "error",
						"message" => "You could not leave the group",
					]);
			}
		}
	}

	public function listUsers (Request $request, Response $response, array $args) {
		$userID = $this->getAuthUser($request);
		if ($userID === false) {
			return $this->authFailed($response);
		}
		$usersModel = new Users($this->container);
		$page = $args['page'];
		$users = $usersModel->list($page);
		return $response
			->withStatus(200)
			->withHeader('content-type', 'application/json')
			->withJson([
				"result" => "success",
				"message" => "Users requested",
				"data" => $users
			]);
	}

	public function listGroups (Request $request, Response $response, array $args) {
		$userID = $this->getAuthUser($request);
		if ($userID === false) {
			return $this->authFailed($response);
		}
		$groupsModel = new Groups($this->container);
		$page = $args['page'];
		$groups = $groupsModel->list($userID, $page);
		return $response
			->withStatus(200)
			->withHeader('content-type', 'application/json')
			->withJson([
				"result" => "success",
				"message" => "Groups requested",
				"data" => $groups
			]);
	}

	public function sendMessage (Request $request, Response $response, array $args) {
		$userID = $this->getAuthUser($request);
		if ($userID === false) {
			return $this->authFailed($response);
		}
		$messagesModel = new Messages($this->container);
		$usersModel = new Users($this->container);
		$groupsModel = new Groups($this->container);
		$type = $request->getParam('type');
		$message = $request->getParam('message');
		$receiverID = $request->getParam('id');
		if (!($type === 'user' || $type === 'group')) {
			$this->addLog([$userID, 'bad type', $type]);
			return $response
				->withStatus(400)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "error",
					"message" => "Type must be either group or user",
				]);
		}
		if ($type === 'user' && !$usersModel->getUserByID($receiverID)) {
			$this->addLog([$userID, 'user not found', $type]);
			return $response
				->withStatus(400)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "error",
					"message" => "There is not such user",
				]);
		}
		if ($type === 'group' && !$groupsModel->inGroup($receiverID, $userID)) {
			$this->addLog([$userID, 'not in group', $type]);
			return $response
				->withStatus(400)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "error",
					"message" => "You are not in this group",
				]);
		}
		if ($messagesModel->send($userID, $receiverID, $type, $message, time())) {
			return $response
				->withStatus(200)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "success",
					"message" => "Message sent",
				]);
		} else {
			$this->addLog([$userID, 'message not sent', $message]);
			return $response
				->withStatus(500)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "error",
					"message" => "Message could not be sent",
				]);
		}
	}

	public function editMessage (Request $request, Response $response, array $args) {
		$userID = $this->getAuthUser($request);
		if ($userID === false) {
			return $this->authFailed($response);
		}
		$messagesModel = new Messages($this->container);
		$messageID = $request->getParam('message_id');
		$message = $request->getParam('message');

		if (!$messagesModel->isOwner($userID, $messageID)) {
			$this->addLog([$userID, 'message not owned', $messageID]);
			return $response
				->withStatus(400)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "success",
					"message" => "This message belongs to someone else",
				]);
		}
		if ($messagesModel->edit($messageID, $message)) {
			return $response
				->withStatus(200)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "success",
					"message" => "Message edited",
				]);
		} else {
			$this->addLog([$userID, 'message not edited', [$messageID, $message]]);
			return $response
				->withStatus(500)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "error",
					"message" => "Message could not be edited",
				]);
		}
	}

	public function deleteMessage (Request $request, Response $response, array $args) {
		$userID = $this->getAuthUser($request);
		if ($userID === false) {
			return $this->authFailed($response);
		}
		$messagesModel = new Messages($this->container);
		$messageID = $request->getParam('message_id');

		if (!$messagesModel->isOwner($userID, $messageID)) {
			$this->addLog([$userID, 'message not owned', $messageID]);
			return $response
				->withStatus(400)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "success",
					"message" => "This message belongs to someone else",
				]);
		}
		if ($messagesModel->delete($messageID)) {
			return $response
				->withStatus(200)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "success",
					"message" => "Message deleted",
				]);
		} else {
			$this->addLog([$userID, 'message not deleted', $messageID]);
			return $response
				->withStatus(500)
				->withHeader('content-type', 'application/json')
				->withJson([
					"result" => "error",
					"message" => "Message could not be deleted",
				]);
		}
	}
}