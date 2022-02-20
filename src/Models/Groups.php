<?php

namespace App\Models;

use Psr\Container\ContainerInterface;

class Groups extends BaseModel {
	//Constructor
	public function __construct(ContainerInterface $ci) {
		parent::__construct($ci);
	}

	public function list($userID, $page) {
		$offset = 10 * max(0, ($page-1));
		$query = "SELECT * FROM groups g
    		LEFT JOIN users u ON (u.user_id=g.admin)
    		LIMIT {$offset},10";
		$groups = $this->db->getAll($query);

		$query = "SELECT * FROM user_groups WHERE user_id='{$userID}'";
		$user_groups = $this->db->getAll($query);
		$joined_groups = [];
		foreach ($user_groups as $user_group) {
			$joined_groups[] = $user_group['group_id'];
		}

		$results = [];
		foreach ($groups as $group) {
			$results[] = [
				'group_id' => $group['group_id'],
				'name' => $group['name'],
				'owner' => [
					'id' => $group['admin'],
					'nickname' => $group['nickname']
				],
				'status' => $group['status'],
				'joined' => in_array($group['group_id'],$joined_groups)
			];
		}
		return $results;
	}

	public function create($groupName, $userID) {
		$query = "INSERT INTO groups (name, admin, status) VALUES (?, ?, 'Hello everyone...')";
		return $this->db->execParam($query, [$groupName, $userID]);
	}

	public function setStatus($groupID, $status) {
		$query = "UPDATE groups SET status=? WHERE group_id=?";
		return $this->db->execParam($query, [$status, $groupID]);
	}

	public function isOwner($userID, $groupID) {
		return $this->db->getOne("SELECT * FROM groups WHERE admin='{$userID}' AND group_id='{$groupID}'");
	}

	public function inGroup($groupID, $userID)
	{
		return $this->db->getOne("SELECT * FROM user_groups WHERE user_id='{$userID}' AND group_id='{$groupID}'");
	}

	public function setOwner($groupID, $newOwnerID) {
		$query = "UPDATE groups SET admin=? WHERE group_id=?";
		return $this->db->execParam($query, [$newOwnerID, $groupID]);
	}

	public function addUser($groupID, $userID) {
		$query = "INSERT IGNORE INTO user_groups (user_id,group_id) VALUES (?,?)";
		return $this->db->execParam($query, [$userID, $groupID]);
	}

	public function removeUser($groupID, $userID) {
		$query = "DELETE FROM user_groups WHERE user_id=? AND group_id=?";
		return $this->db->execParam($query, [$userID, $groupID]);
	}

	public function delete($groupID) {
		$query = "DELETE FROM groups WHERE group_id=?";
		$res1 = $this->db->execParam($query, [$groupID]);
		$query = "DELETE FROM user_groups WHERE group_id=?";
		$res2 = $this->db->execParam($query, [$groupID]);
		return ($res1&&$res2);
	}

}