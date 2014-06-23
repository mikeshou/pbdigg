<?php
/**
 * @version $Id: message.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

(!defined('IN_ADMIN') || !isset($_PBENV['PHP_SELF']) || !preg_match('/[\/\\\\]admincp\.php$/i', $_PBENV['PHP_SELF'])) && exit('Access Denied');

define('PB_PAGE', 'message');

if (($job == 'email' || $job == 'msg') && $ispost == 'on')
{
	intConvert(array('start','count'));
	charConvert(array('title'));

	!$title && showMsg('message_msg_title_empty');
	(!$count || $count < 0) && $count = 100;
	!is_array($groups) && $groups = explode(',', $groups);
	$groups = implode(',', array_map('intval', $groups));
	!$groups && showMsg('message_msg_addressee_empty');
	$contentCache = PBDIGG_ROOT.'data/cache/send_'.$job.'.tmp';
	if (!$start)
	{
		PWriteFile($contentCache, $content, 'wb');
	}
	else
	{
		$content = PReadFile($contentCache);
	}
	$content = stripslashes($content);
	safeConvert($content);
	!$content && showMsg('message_msg_content_empty');
	if ($job == 'email')
	{
		$query = $DB->db_query("SELECT uid, username, email FROM {$db_prefix}members WHERE groupid IN (".$groups.") LIMIT $start, $count");
		if ($DB->db_num($query))
		{
			require_once PBDIGG_ROOT.'include/mail.inc.php';
			$sendto = array();
			while ($rs = $DB->fetch_all($query))
			{
				$sendto[] = $rs['email'];
			}
			PMail($sendto, $title, $content);
			redirect('message_send_process', 'admincp.php?action=message&job=email&start='.($start + $count).'&count='.$count.'&groups='.$groups.'&title='.rawurlencode($title));
		}
		else
		{
			PDel($contentCache);
			redirect('message_send_success', $basename);
		}
	}
	else
	{
		if ($pb_ucenable && $uc_msg)
		{
			$sql = 'ucuid AS uid';
			$field = 'ucuid';
		}
		else
		{
			$sql = $field = 'uid';
		}

		$query = $DB->db_query("SELECT $sql, username FROM {$db_prefix}members WHERE groupid IN ($groups) LIMIT $start, $count");
		if ($DB->db_num($query))
		{
			$insert = $uids = '';
			$content = addslashes($content);
			while ($rs = $DB->fetch_all($query))
			{
				$insert .= "(0, ".(int)$rs['uid'].", '$title', '$content', 'r', '$timestamp', 0, 1),";
				$uids .= (int)$rs['uid'].',';
			}
			if ($pb_ucenable && $uc_msg)
			{
				uc_pm_send(0, substr($uids, 0, -1), $title, $content);
			}
			else
			{
				$DB->db_exec("INSERT INTO {$db_prefix}message (fuid, tuid, title, content, type, postdate, ifread, ifsys) VALUES ".substr($insert, 0, -1));				
			}
			$DB->db_exec("UPDATE {$db_prefix}members SET newmsg = newmsg + 1 WHERE $field IN (".substr($uids, 0, -1).")");
			$end = $start + $count;
			redirect('message_send_process', 'admincp.php?action=message&job=msg&start='.$end.'&count='.$count.'&groups='.$groups.'&title='.rawurlencode($title));
		}
		else
		{
			PDel($contentCache);
			redirect('message_send_success', $basename);
		}
	}
}

$mailto = '<table><tr>';
$_grouplevel['-1']['grouptitle'] = $cp_message['common_member'];
$i = 1;
foreach ($_grouplevel as $k => $v)
{
	if ($k == 5 || $v['gtype'] == 'member') continue;
	$mailto .= '<td><input type="checkbox" name="groups[]" value="'.$k.'" />'.$v['grouptitle'].'</td>';
	!($i++ % 4) && $mailto .= '</tr><tr>';
}

$mailto .= str_repeat('<td>&nbsp;</td>', $i % 4).'</tr></table>';

?>