<?php

class friend
{
	var $DB = NULL;

	var $db_prefix = '';

	var $UC = NULL;

	var $action = '';

	var $uid = '0';

	var $ucenable = '0';

	function __construct()
	{
		global $DB, $db_prefix, $pb_ucenable, $uc_friend, $m_uid, $ucuid;

		$this->DB = $DB;
		$this->db_prefix = $db_prefix;
		$this->ucenable = $pb_ucenable && $uc_friend;
		if ($this->ucenable)
		{
			require_once PBDIGG_ROOT.'include/uc_client/client.php';
			$this->uid = $ucuid;
		}
		else
		{
			$this->uid = $m_uid;
		}
	}
	function friend()
	{
		$this->__construct();
	}

	function Action($action, $data = '', $extra = '')
	{
		$this->action = $action;
		$method = $this->ucenable ? 'uc_'.$action : $action;
		if (method_exists($this, $method))
		{
			return $this->$method($data, $extra);
		}
		else
		{
			showMsg('illegal_request');
		}
	}

	/**
	 * 添加好友
	 */
	function add()
	{
		global $uid, $timestamp, $member_message;

		$uid = (int)$uid;

		!$this->DB->fetch_first("SELECT COUNT(*) num FROM {$this->db_prefix}members WHERE uid = '$uid'") && showMsg('friend_user_nonexistence');

		$rs = $this->DB->fetch_one("SELECT fid, status FROM {$this->db_prefix}friends WHERE fuid = '{$this->uid}' AND uid = '$uid'");
		$status = $rs ? 1 : 0;

		$this->DB->db_exec("REPLACE INTO {$this->db_prefix}friends (uid, fuid, status, createdate) VALUES ('{$this->uid}', '$uid', $status, '$timestamp')");

		if ($this->DB->db_affected_rows() == 2)
		{
			$status && $this->DB->db_exec("UPDATE {$this->db_prefix}friends SET status = 1 WHERE fid = '".$rs['fid']."' LIMIT 1");
			$this->DB->db_exec("UPDATE {$this->db_prefix}members SET friendnum = friendnum - 1 WHERE ucuid = '{$this->uid}' LIMIT 1");
			$sendMsg[$uid] = array($this->uid, $member_message['friend_add_msg_title'], $member_message['friend_add_msg_content']);
			require_once PBDIGG_ROOT.'include/msg.class.php';
			$msg = new msg();
			$msg->sendMsg($sendMsg, FALSE, 1);
		}
		redirect('friend_update_succeed', 'member.php?type=my&action=friend');
	}
	function uc_add()
	{
		global $uid;
		if ($num = uc_friend_add($this->uid, $uid))
		{
			$this->DB->db_exec("UPDATE {$this->db_prefix}members SET friendnum = friendnum + 1 WHERE ucuid = '{$this->uid}' LIMIT 1");
		}
		redirect(($num ? 'friend_update_succeed' : 'friend_update_failed'), 'member.php?type=my&action=friend');
	}
	/**
	 * 删除好友
	 */
	function delete()
	{
		if (checkPostHash($GLOBALS['verify']))
		{
			global $uid;
			$frienddata = $this->DB->fetch_one("SELECT status FROM {$this->db_prefix}friends WHERE uid = '{$this->uid}' AND fuid = '$uid'");
			if ($frienddata)
			{
				$this->DB->db_exec("DELETE FROM {$this->db_prefix}friends WHERE uid = '{$this->uid}' AND fuid = '$uid' LIMIT 1");
				$frienddata['status'] && $this->DB->db_exec("UPDATE {$this->db_prefix}friends SET status = 0 WHERE uid = '$uid' AND fuid = '{$this->uid}' LIMIT 1");
				$this->DB->db_exec("UPDATE {$this->db_prefix}members SET friendnum = friendnum - 1 WHERE uid = '{$this->uid}' LIMIT 1");
				redirect('friend_update_succeed', 'member.php?type=my&action=friend');
			}
		}
		location('member.php?type=my&action=friend');
	}
	function uc_delete()
	{
		if (checkPostHash($GLOBALS['verify']))
		{
			global $uid;
			if (uc_friend_delete($this->uid, array($uid)))
			{
				$this->DB->db_exec("UPDATE {$this->db_prefix}members SET friendnum = friendnum - 1 WHERE ucuid = '{$this->uid}' LIMIT 1");
				redirect('friend_update_succeed', 'member.php?type=my&action=friend');	
			}
		}
		location('member.php?type=my&action=friend');
	}

	/**
	 * 我的好友
	 */
	function my()
	{
		$limit = $this->multLink(intval($this->DB->fetch_first("SELECT COUNT(*) num FROM {$this->db_prefix}friends WHERE uid = '{$this->uid}'")));
		$query = $this->DB->db_query("SELECT f.fuid, m.username, m.ucuid, m.avatar 
										FROM {$this->db_prefix}friends f 
										LEFT JOIN {$this->db_prefix}members m 
										ON f.fuid = m.uid 
										WHERE f.uid = '{$this->uid}' 
										ORDER BY f.fid DESC 
										$limit");
		$friends = array();
		while ($rs = $this->DB->fetch_all($query))
		{
			$rs['avatar'] = userFace($rs['avatar'], $rs['fuid']);
			$rs['fuurl'] = userSpace($rs['fuid'], $rs['ucuid']);
			$friends[] = $rs;
		}
		return $friends;
	}
	function uc_my()
	{
		global $p;
		$ucfriends = uc_friend_ls($this->uid, $p, 30, 99999);
		$friends = array();
		foreach ($ucfriends as $v)
		{
			$v['avatar'] = userFace('', $v['friendid']);
			$v['fuurl'] = userSpace($this->getUserID($v['friendid']), $v['friendid']);
			$v['fuid'] = $v['friendid']; 
			$friends[] = $v;
		}
		$this->multLink(count($ucfriends));
		return $friends;
	}

	function multLink($recordNum)
	{
		global $pb_msg, $p, $multLink;

		$pagesize = (int)$pb_msg;
		$multLink = multLink($p, $recordNum, 'member.php?type=msg&amp;action='.$this->action.'&amp;', $pagesize);
		return sqlLimit($p, $pagesize);
	}
	
	function getUserID($ucuid)
	{
		return intval($this->DB->fetch_first("SELECT uid FROM {$this->db_prefix}members WHERE ucuid = '$ucuid'"));
	}
}
?>