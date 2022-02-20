<?php

namespace App\Models;

use Psr\Container\ContainerInterface;

class Messages extends BaseModel {
	//Constructor
	public function __construct(ContainerInterface $ci) {
		parent::__construct($ci);
	}

	public function getNew($userID) {
		$user = $this->db->getOne("SELECT check_ts FROM users WHERE user_id='{$userID}'");
		if (empty($user['check_ts'])) {
			$user['check_ts'] = 0;
		}
		$this->setLastRead($userID, time());

		$pmQuery = "SELECT * FROM messages m 
			LEFT JOIN users u ON (m.sender=u.user_id)
			WHERE m.msg_type='user' AND m.timestamp>={$user['check_ts']} AND m.receiver='{$userID}'";
		$personalMessages = $this->db->getAll($pmQuery);

		$gmQuery = "SELECT * FROM messages m
			LEFT JOIN users u ON (m.sender=u.user_id)
			WHERE m.msg_type='group' AND m.timestamp>={$user['check_ts']} AND m.receiver IN (SELECT ug.group_id FROM user_groups ug WHERE ug.user_id='{$userID}')";
		$groupMessages = $this->db->getAll($gmQuery);

		$newMessages = [];
		foreach ($personalMessages as $message) {
			$newMessages[] = [
				'date' => date(DATE_RFC1036, $message['timestamp']),
				'from' => $message['nickname'],
				'message' => base64_decode($message['msg_text']),
				'type' => 'personal',
			];
		}
		foreach ($groupMessages as $message) {
			$newMessages[] = [
				'date' => date(DATE_RFC1036, $message['timestamp']),
				'from' => $message['nickname'],
				'message' => base64_decode($message['msg_text']),
				'type' => 'group',
			];
		}
		return $newMessages;
	}

	private function setLastRead($userID, int $time) {
		return $this->db->execParam("UPDATE users SET check_ts=? WHERE user_id=?", [$time, $userID]);
	}

	public function send($sender, $receiver, $type, $message, $ts) {
		$message = base64_encode($message);
		return $this->db->execParam("INSERT INTO messages (sender,receiver,msg_type,msg_text,timestamp) VALUES (?,?,?,?,?)", [$sender, $receiver, $type, $message, $ts]);
	}

	public function display($userID, $type, $id, $page) {
		$offset = 10 * max(0, ($page-1));
		if ($type === 'user') {
			$query = "SELECT * FROM messages m 
			LEFT JOIN users u ON (m.sender=u.user_id)
			WHERE m.msg_type='user' AND (m.receiver='{$userID}' AND m.sender='{$id}') OR 
			      (m.receiver='{$id}' AND m.sender='{$userID}')
			LIMIT {$offset},10";
		} else {
			$query = "SELECT * FROM messages m
			LEFT JOIN users u ON (m.sender=u.user_id)
			WHERE m.msg_type='group' AND m.receiver='{$id}'
			LIMIT {$offset},10";
		}
		$messages = $this->db->getAll($query);
		$results = [];
		foreach ($messages as $message) {
			$results[] = [
				'msg_id' => $message['msg_id'],
				'date' => date(DATE_RFC1036, $message['timestamp']),
				'from' => $message['nickname'],
				'message' => base64_decode($message['msg_text']),
			];
		}
		return $results;
	}

	public function edit($messageID, $message) {
		$message = base64_encode($message);
		$query = "UPDATE messages SET msg_text=? WHERE msg_id=?";
		return $this->db->execParam($query, [$message, $messageID]);
	}

	public function delete($messageID) {
		$query = "DELETE FROM messages WHERE msg_id=?";
		return $this->db->execParam($query, [$messageID]);
	}

	public function isOwner($userID, $messageID) {
		return $this->db->getOne("SELECT * FROM messages WHERE msg_id='{$messageID}' AND sender='{$userID}'");
	}

}