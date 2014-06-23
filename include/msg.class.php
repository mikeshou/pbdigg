<?php

class msg
{
	var $DB = NULL;

	var $db_prefix = '';

	var $action = '';

	var $uid = '0';

	var $ucenable = '0';

	function __construct()
	{
		global $DB, $db_prefix, $pb_ucenable, $uc_msg, $m_uid, $ucuid;

		$this->DB = $DB;
		$this->db_prefix = $db_prefix;
		$this->ucenable = $pb_ucenable && $uc_msg;
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
	function msg()
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
	 * 收件箱
	 */
	function receivebox()
	{
		global $member_message, $enableDel;

		$limit = $this->multLink(intval($this->DB->fetch_first("SELECT COUNT(*) num FROM {$this->db_prefix}message WHERE tuid = '{$this->uid}' AND type = 'r'")));
		$query = $this->DB->db_query("SELECT ms.mid, ms.fuid, ms.title, ms.postdate, ms.ifread, ms.ifsys, m.username fauthor, m.ucuid 
					FROM {$this->db_prefix}message ms 
					LEFT JOIN {$this->db_prefix}members m 
					ON ms.fuid = m.uid 
					WHERE ms.tuid = '{$this->uid}' 
					AND ms.type = 'r' 
					ORDER BY ms.postdate DESC 
					$limit");
		$msgs = array();
		while ($rs = $this->DB->fetch_all($query))
		{
			$rs['ifread'] = $rs['ifread'] ? $member_message['msg_readed'] : $member_message['msg_unreaded'];
			$rs['postdate'] = gdate($rs['postdate'], 'Y-m-d H:i');
			$rs['title'] = PBSubstr($rs['title'], 30);
			$rs['fuurl'] = userSpace($rs['fuid'], $rs['ucuid']);
			if ($rs['ifsys'])
			{
				$rs['fauthor'] = $rs['addresser'] = $member_message['msg_sys'];
			}
			else
			{
				$rs['addresser'] = '<a href="'.$rs['fuurl'].'" target="_blank">'.$rs['fauthor'].'</a>';
			}
			$msgs[] = $rs;
		}
		$enableDel = 1;
		return $msgs;
	}
	function uc_receivebox()
	{
		global $pb_msg, $p, $member_message, $filter, $enableDel;
		$ucmsgs = uc_pm_list($this->uid, $p, $pb_msg, 'inbox', $filter, 30);
		$msgs = array();
		if ($ucmsgs['count'])
		{
			foreach ($ucmsgs['data'] as $v)
			{
				$v['mid'] = $v['pmid'];
				if (!$v['msgtoid'] || !$v['msgfromid'])
				{
					$v['fauthor'] = $v['addresser'] = $member_message['msg_sys'];
				}
				else
				{
					$v['fauthor'] = $v['related'] ? $v['msgfrom'] : HConvert($this->DB->fetch_first("SELECT username FROM {$this->db_prefix}members WHERE ucuid = '".$v['msgfromid']."'"));
					$v['fuurl'] = userSpace($this->getUserID($v['msgfromid']), $v['msgfromid']);
					$v['addresser'] = '<a href="'.$v['fuurl'].'" target="_blank">'.$v['fauthor'].'</a>';
				}
				$v['ifread'] = ($v['new'] && $filter != 'announcepm') ? $member_message['msg_unreaded'] : $member_message['msg_readed'];
				$v['postdate'] = gdate($v['dateline'], 'Y-m-d H:i');
				$v['title'] = PBSubstr($v['subject'], 30);
				$msgs[] = $v;
			}
		}
		$enableDel = $filter == 'announcepm' ? 0 : 1;
		$this->multLink($ucmsgs['count']);
		return $msgs;
	}
	/**
	 * 发件箱
	 */
	function sendbox()
	{
		global $member_message;

		$limit = $this->multLink(intval($this->DB->fetch_first("SELECT COUNT(*) num FROM {$this->db_prefix}message WHERE fuid = '{$this->uid}' AND type = 's'")));
		$query = $this->DB->db_query("SELECT ms.mid, ms.tuid, ms.title, ms.postdate, m.username tauthor, m.ucuid 
					FROM {$this->db_prefix}message ms 
					LEFT JOIN {$this->db_prefix}members m 
					ON ms.tuid = m.uid 
					WHERE ms.fuid = '{$this->uid}' 
					AND ms.type = 's' 
					ORDER BY ms.postdate DESC 
					$limit");
		$msgs = array();
		while ($rs = $this->DB->fetch_all($query))
		{
			$rs['postdate'] = gdate($rs['postdate'], 'Y-m-d H:i');
			$rs['title'] = PBSubstr($rs['title'], 30);
			$rs['tuurl'] = userSpace($rs['tuid'], $rs['ucuid']);
			$rs['addressee'] = '<a href="'.$rs['tuurl'].'" target="_blank">'.$rs['tauthor'].'</a>';
			$msgs[] = $rs;
		}
		return $msgs;
	}
//	function uc_sendbox()
//	{
//		return;
//		global $pb_msg, $p, $member_message;
//		$ucmsgs = uc_pm_list($this->uid, $p, $pb_msg, 'outbox', 'privatepm', 30);
//		$msgs = array();
//		if ($ucmsgs['count'])
//		{
//			foreach ($ucmsgs['data'] as $v)
//			{
//				$v['mid'] = $v['pmid'];
//				$v['tauthor'] = $v['msgfrom'];
//				$v['postdate'] = gdate($v['dateline'], 'Y-m-d H:i');
//				$v['title'] = PBSubstr($v['subject'], 30);
//				$v['tuurl'] = userSpace($this->getUserID($v['msgtoid']), $v['msgtoid']);
//				$msgs[] = $v;
//			}
//		}
//		$this->multLink($ucmsgs['count']);
//		return $msgs;
//	}

	/**
	 * 阅读短消息
	 */
	function read()
	{
		global $mid, $member_message;

		$mid = (int)$mid;
		$msg = $this->DB->fetch_one("SELECT ms.mid, ms.fuid, ms.title, ms.content, ms.postdate, ms.ifsys, ms.ifread, m.username, m.ucuid  
				FROM {$this->db_prefix}message ms 
				LEFT JOIN {$this->db_prefix}members m 
				ON ms.fuid = m.uid 
				WHERE ms.mid = '$mid' 
				AND ((ms.tuid = '{$this->uid}' AND ms.type = 'r') OR (ms.fuid = '{$this->uid}' AND ms.type = 's'))");
		!$msg && showMsg('msg_nonexistence');

		$msg['postdate'] = gdate($msg['postdate'], 'Y-m-d H:i');
		$msg['addressee'] = $msg['ifsys'] ? $member_message['msg_sys'] : '<a href="'.userSpace($msg['fuid'], $msg['ucuid']).'" target="_blank">'.$msg['username'].'</a>';
		$msg['content'] = preg_replace('~\[quote(=(?:[^]]*))?\]\s*(.+?)\s*\[/quote\]~eis', "\$this->qouteCode('\\2')", $msg['content']);
		
		if (!$msg['ifread'])
		{
			$this->DB->db_exec("UPDATE {$this->db_prefix}message SET ifread = 1 WHERE mid = '$mid' AND tuid = '{$this->uid}' AND type = 'r'");
			//new msg
			$this->updateNewMsg();
		}
		return $msg;
	}
	function uc_read()
	{
		global $mid, $member_message;
		$msg = array();
		$ucmsgs = uc_pm_viewnode($this->uid, 0, $mid);

		$msg['mid'] = $ucmsgs['pmid'];
		$msg['title'] = $ucmsgs['subject'];
		$msg['content'] = preg_replace('~\[quote(=(?:[^]]*))?\]\s*(.+?)\s*\[/quote\]~eis', "\$this->qouteCode('\\2')", $ucmsgs['message']);
		$msg['postdate'] = gdate($ucmsgs['dateline'], 'Y-m-d H:i');
		
		if (!$ucmsgs['msgtoid'] || !$ucmsgs['msgfromid'])
		{
			$msg['fauthor'] = $msg['addressee'] = $member_message['msg_sys'];
		}
		else
		{
			$msg['fauthor'] = $ucmsgs['related'] ? $ucmsgs['msgfrom'] : HConvert($this->DB->fetch_first("SELECT username FROM {$this->db_prefix}members WHERE ucuid = '".$ucmsgs['msgfromid']."'"));
			$msg['fuurl'] = userSpace($this->getUserID($ucmsgs['msgfromid']), $ucmsgs['msgfromid']);
			$msg['addressee'] = '<a href="'.$msg['fuurl'].'" target="_blank">'.$msg['fauthor'].'</a>';
		}
		if ($ucmsgs['new'])
		{
			uc_pm_readstatus($this->uid, array($ucmsgs['msgfromid']), array($ucmsgs['pmid']));
			$this->uc_updateNewMsg();
		}
		return $msg;
	}

	/**
	 * 写短消息
	 */
	function write()
	{
		global $allowmsg, $member_message;

		!$allowmsg && showMsg('msg_send_disable');
		if (checkPost())
		{
			$addressee = trim($_POST['addressee']);
			$title = trim($_POST['title']);
			$content = trim($_POST['content']);
			$savemsg = isset($_POST['savemsg']) ? TRUE : FALSE;
			
			if (!$addressee || !$title || !$content)
			{
				showMsg('msg_send_invalid');
			}

			$title = checkMsg($title);
			$content = checkMsg($content, 'content');

			$addresseeUID = $this->DB->fetch_first("SELECT uid FROM {$this->db_prefix}members WHERE username = '$addressee' LIMIT 1");
			!$addresseeUID && showMsg('msg_send_nonexistence');

			$addresseeUID == $this->uid && showMsg('msg_send_self_ignore');

			$sendMsg[$addresseeUID] = array($this->uid, addslashes(HConvert(stripslashes($title))), addslashes(HConvert(stripslashes($content))));

			$this->sendMsg($sendMsg, $savemsg);
			showMsg('msg_send_succeed', 'member.php?type=msg&action=write');
		}
		else
		{
			global $mid, $tuid;
			$msg = array('title'=>'', 'addressee'=>'', 'content'=>'');
			$mid = (int)$mid;
			$tuid = (int)$tuid;
			if ($mid)
			{
				$msg = $this->DB->fetch_one("SELECT ms.title, m.username AS addressee, ms.content 
						FROM {$this->db_prefix}message ms 
						LEFT JOIN {$this->db_prefix}members m 
						ON ms.fuid = m.uid 
						WHERE ms.mid = '$mid' 
						AND ms.type = 'r' 
						AND ms.tuid = '{$this->uid}'");
				$msg['title'] = strpos($msg['title'], 'Re:') === FALSE ? 'Re:'.$msg['title'] : $msg['title'];
				$msg['content'] = '[quote]'.PBSubstr($msg['content'], 50)."[/quote]\r\n";
			}
			elseif ($tuid)
			{
				$msg['addressee'] = $this->DB->fetch_first("SELECT username AS addressee FROM {$this->db_prefix}members WHERE uid = '$tuid'");
			}
			return $msg;
		}
	}
	function uc_write()
	{
		global $allowmsg, $member_message;

		!$allowmsg && showMsg('msg_send_disable');
		if (checkPost())
		{
			$addressee = trim($_POST['addressee']);
			$title = trim($_POST['title']);
			$content = trim($_POST['content']);

			if (!$addressee || !$title || !$content)
			{
				showMsg('msg_send_invalid');
			}

			$title = checkMsg($title);
			$content = checkMsg($content, 'content');

			$addresseeUID = $this->DB->fetch_first("SELECT ucuid FROM {$this->db_prefix}members WHERE username = '$addressee' LIMIT 1");
			!$addresseeUID && showMsg('msg_send_nonexistence');

			$addresseeUID == $this->uid && showMsg('msg_send_self_ignore');

			$sendMsg[$addresseeUID] = array($this->uid, addslashes(HConvert(stripslashes($title))), addslashes(HConvert(stripslashes($content))));

			$this->sendMsg($sendMsg);
			showMsg('msg_send_succeed', 'member.php?type=msg&action=write');
		}
		else
		{
			global $mid, $tuid;
			$msg = array('title'=>'', 'addressee'=>'', 'content'=>'');
			$mid = (int)$mid;
			$tuid = (int)$tuid;
			if ($mid && ($msg = uc_pm_viewnode($this->uid, 0, $mid)) && $msg['msgtoid'] == $this->uid && $msg['msgfromid'])
			{
				$msg['title'] = strpos($msg['subject'], 'Re:') === FALSE ? 'Re:'.$msg['subject'] : $msg['subject'];
				$msg['content'] = '[quote]'.PBSubstr(strip_tags($msg['message']), 50)."[/quote]\r\n";
				$msg['addressee'] = $msg['related'] ? $msg['msgfrom'] : HConvert($this->DB->fetch_first("SELECT username FROM {$this->db_prefix}members WHERE ucuid = '".$msg['msgfromid']."'"));
			}
			elseif ($tuid)
			{
				$msg['addressee'] = $this->DB->fetch_first("SELECT username AS addressee FROM {$this->db_prefix}members WHERE ucuid = '$tuid'");
			}
			return $msg;
		}
	}

	/**
	 * 清理信箱
	 */
	function drop()
	{
		if (checkPost())
		{
			global $inbox, $outbox;

			isset($outbox) && $this->DB->db_exec("DELETE FROM {$this->db_prefix}message WHERE fuid = '{$this->uid}' AND type = 's'");
			if (isset($inbox))
			{
				$this->DB->db_exec("DELETE FROM {$this->db_prefix}message WHERE tuid = '{$this->uid}' AND type = 'r'");
				$this->DB->db_exec("UPDATE {$this->db_prefix}members SET newmsg = 0 WHERE uid = '{$this->uid}");
			}
			redirect('msg_delete_succeed', 'member.php?type=msg&action=drop');
		}
	}

//	function uc_drop()
//	{
//		if (checkPost())
//		{
//			
//		}
//	}

	function del()
	{
		if (checkPostHash($GLOBALS['verify']))
		{
			global $mid;

			if ($mid && is_array($mid))
			{
				$mids = '';
				foreach ($mid as $m)
				{
					$mids .= ($mids ? ',' : '').(int)$m;
				}
				if ($mids)
				{
					$this->DB->db_exec("DELETE FROM {$this->db_prefix}message WHERE mid IN ($mids) AND ((fuid = '{$this->uid}' AND type = 's') OR (tuid = '{$this->uid}' AND type = 'r'))");
					$this->updateNewMsg();
				}
			}
			redirect('msg_delete_succeed', 'member.php?type=msg&action='.(in_array($GLOBALS['forward'], array('receivebox', 'sendbox')) ? $GLOBALS['forward'] : 'receivebox'));
		}
	}

	function uc_del()
	{
		if (checkPostHash($GLOBALS['verify']))
		{
			global $mid;
			if ($mid && is_array($mid) && uc_pm_delete($this->uid, 'inbox', array_map('intval', $mid)))
			{
				$this->uc_updateNewMsg();
			}
			redirect('msg_delete_succeed', 'member.php?type=msg&action=receivebox&filter='.$GLOBALS['filter']);
		}
	}
	function updateNewMsg()
	{
		$newmsg = intval($this->DB->fetch_first("SELECT COUNT(*) num FROM {$this->db_prefix}message WHERE tuid = '{$this->uid}' AND type = 'r' AND ifread = 0"));
		$this->DB->db_exec("UPDATE {$this->db_prefix}members SET newmsg = '$newmsg' WHERE uid = '{$this->uid}'");
	}
	function uc_updateNewMsg()
	{
		$this->DB->db_exec("UPDATE {$this->db_prefix}members SET newmsg = '".intval(uc_pm_checknew($this->uid))."' WHERE ucuid = '{$this->uid}' LIMIT 1");
	}
	/**
	 * $var = array($k[tuid] => array('0'=>fuid,'1'=>title,'2'=>content));
	 */
	function sendMsg($var, $save = FALSE, $sys = 0)
	{
		global $timestamp;
		static $groups = array();

		if ($this->ucenable)
		{
			$addressee = '';
			foreach ($var as $k => $v)
			{
				!$sys && (uc_pm_send($v[0], $k, $v[1], $v[2]) <= 0) && showMsg('msg_send_invalid');
				$addressee .= (int)$k.',';
			}
			$addressee && $this->DB->db_exec("UPDATE {$this->db_prefix}members SET newmsg = newmsg + 1 WHERE ucuid IN (".substr($addressee, 0, -1).")");
		}
		else
		{
			$insertInbox = $insertOutbox = $addressee = '';
			foreach ($var as $k => $v)
			{
				$info = $this->DB->fetch_one("SELECT m.groupid, m.realgroup FROM {$this->db_prefix}members m WHERE m.uid = '$k'");
				if ($info)
				{
					$totalMsg = intval($this->DB->fetch_first("SELECT COUNT(*) total FROM {$this->db_prefix}message WHERE tuid = '$k' AND type = 'r'"));
					$groupid = (int)$info['groupid'];
					$groupid == -1 && $groupid = (int)$info['realgroup'];
					if (!isset($groups[$groupid]))
					{
						require PBDIGG_CROOT.'cache_usergroup_'.$groupid.'.php';
						$groups[$groupid] = (int)$msgmax;
					}
					if (!$sys && ($totalMsg >= $groups[$groupid])) showMsg('msg_addressee_overflow');
					
					$insertInbox .= "({$v[0]}, {$k}, '{$v[1]}', '{$v[2]}', 'r', $timestamp, 0, $sys),";
					$save && !$sys && $insertOutbox .= "({$v[0]}, {$k}, '{$v[1]}', '{$v[2]}', 's', $timestamp, 1, $sys),";
					$addressee .= (int)$k.',';
				}
			}
			$insertInbox && $this->DB->db_exec("INSERT INTO {$this->db_prefix}message (fuid, tuid, title, content, type, postdate, ifread, ifsys) VALUES ".substr($insertInbox, 0, -1));
			$insertOutbox && $this->DB->db_exec("INSERT INTO {$this->db_prefix}message (fuid, tuid, title, content, type, postdate, ifread, ifsys) VALUES ".substr($insertOutbox, 0, -1));
			$addressee && $this->DB->db_exec("UPDATE {$this->db_prefix}members SET newmsg = newmsg + 1 WHERE uid IN (".substr($addressee, 0, -1).")");
		}
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

	function qouteCode($content)
	{
		return '<div class="quote"><div class="quote-title">'.getSingleLang('common', 'ubb_quote').' </div><blockquote>' . str_replace('\\"', '"', $content) . '</blockquote></div>';
	}
}
?>