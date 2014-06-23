<?php
/**
 * @version $Id: member.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

(!defined('IN_ADMIN') || !isset($_PBENV['PHP_SELF']) || !preg_match('/[\/\\\\]admincp\.php$/i', $_PBENV['PHP_SELF'])) && exit('Access Denied');

define('PB_PAGE', 'member');

require_once PBDIGG_ROOT.'include/member.class.php';
$Member = new Member();

if ($job == 'add') 
{
	//add new member
	if (isPost())
	{
		$newMember = array (
			'username'=>trim($username),
			'password'=>trim($password),
			'rpassword'=>trim($password),
			'email'=>trim($email),
			'groupid'=>(int)$groupid,
			'ignore'=>'1',
			'exp'=>array('ctsig'=>0)
		);

		$Member->Action('add', $newMember);

		redirect('member_add_success', $basename);
	}
	else
	{
		$group_option = array();
		foreach ($_grouplevel as $key => $value)
		{
			$key != '5' && $group_option[$key] = $value['grouptitle'];
		}
		//default add common user
		$group_option = html_select($group_option, 'groupid', 7, 'id="groupid"');
	}
}
elseif ($job == 'edit')
{
	$group_option = array('0'=>'');
	foreach ($_grouplevel as $key => $value)
	{
		$group_option[$key] = $value['grouptitle'];
	}
	//default add common user
	$group_option = html_select($group_option, 'groupid', 0, 'id="groupid"');
}
elseif ($job == 'list')
{
	if ($ispost == 'on')
	{
		//search member
		$sql = '1';
		$uid && $sql .= " AND m.uid = ".(int)$uid;
		$pattern = array('%','_','*');
		$replace = array('\%','\_','%');
		$username = strip_tags(trim($username));
		if ($username && (strlen($username) < 20))
		{
			$username = str_replace($pattern, $replace, preg_replace('~\*{2,}~i', '', $username));
			$sql .= " AND m.username LIKE '$username'";
		}
		$email = trim($email);
		if ($email && (strlen($email) < 100))
		{
			$email = str_replace($pattern, $replace, preg_replace('~\*{2,}~i', '', $email));
			$sql .= " AND m.email ".(strpos($email, '%') === FALSE ? '=' : 'LIKE')." '$email'";
		}
		$groupid = (int)$groupid;
		if ($groupid)
		{
			$groupid = ($groupid == 7) ? -1 : $groupid;
			$sql .= " AND m.groupid = $groupid";
		}
		//register time
		$later = trim($later);
		if (preg_match('~^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})~i', $later))
		{
			$later = strtotime($later);
			$sql .= " AND m.regdate >= '$later'";
		}
		$earlier = trim($earlier);
		if (preg_match('~^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})~i', $earlier))
		{
			$earlier = strtotime($earlier.' 23:59:59');
			$sql .= " AND m.regdate < '$earlier'";
		}
		//last login date
		$logdatelater = trim($logdatelater);
		if (preg_match('~^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})~i', $logdatelater))
		{
			$logdatelater = strtotime($logdatelater);
			$sql .= " AND m.lastvisit >= '$logdatelater'";
		}
		$logdateearlier = trim($logdateearlier);
		if (preg_match('~^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})~i', $logdateearlier))
		{
			$logdateearlier = strtotime($logdateearlier.' 23:59:59');
			$sql .= " AND m.lastvisit < '$logdateearlier'";
		}
		if (is_numeric($articleless))
		{
			$sql .= " AND m.postnum < ".(int)$articleless;
		}
		if (is_numeric($articlemore))
		{
			$sql .= " AND m.postnum >= ".(int)$articlemore;
		}
		if (is_numeric($currencyless))
		{
			$sql .= " AND m.currency < ".(int)$currencyless;
		}
		if (is_numeric($currencymore))
		{
			$sql .= " AND m.currency >= ".(int)$currencymore;
		}
		
		if (!in_array($orderby, array('m.username','m.email','m.regdate','m.lastvisit','m.postnum','m.currency')))
		{
			$orderby = 'm.username';
		}
		$sql .= " ORDER BY $orderby";
		if (!in_array($ordertype, array('ASC','DESC')))
		{
			$ordertype = 'ASC';
		}
		$sql .= " $ordertype";
		$pagesize = (int)$pagesize;
		if (!$pagesize || ($pagesize < 0))
		{
			$pagesize = 30;
		}
		$rs = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}members m WHERE $sql");
		$recordNum = (int)$rs['num'];
		$limit = sqlLimit($page, $pagesize);

		$multLink = cpmultLink($page, $recordNum, 'admincp.php?action=member&job=list&ispost=on&username='.rawurlencode($username).'&email='.rawurlencode($email).'&groupid='.$groupid.'&later='.$later.'&earlier='.$earlier.'&logdateearlier='.$logdateearlier.'&logdatelater='.$logdatelater.'&articleless='.$articleless.'&articlemore='.$articlemore.'&currencyless='.$currencyless.'&currencymore='.$currencymore.'&orderby='.$orderby.'&ordertype='.$ordertype.'&pagesize='.$pagesize.'&', $pagesize);
		$SQL = "SELECT m.uid, m.username, m.email, m.groupid, m.regdate, m.lastvisit FROM {$db_prefix}members m WHERE $sql $limit";
	}
	elseif ($find)
	{
		//shortcut search
		$sql = '';
		switch ($find)
		{
			case 'newest':
				$numsql = "SELECT COUNT(*) num FROM {$db_prefix}members WHERE regdate > $timestamp - 259200";
				$sql = 'm.regdate > '.($timestamp - 259200);
				break;
			
			case 'currency':
				$numsql = "SELECT COUNT(*) num FROM {$db_prefix}members WHERE currency < 0";
				$sql = 'm.currency < 0';
				break;
			
			case 'article':
				$numsql = "SELECT COUNT(*) num FROM {$db_prefix}members WHERE postnum = 0";
				$sql = 'm.postnum = 0';
				break;
			
			default:
				$numsql = "SELECT COUNT(*) num FROM {$db_prefix}members";
				break;
		}
		$rs = $DB->fetch_one($numsql);
		$recordNum = (int)$rs['num'];
		$limit = sqlLimit($page);
		$multLink = cpmultLink($page, $recordNum, 'admincp.php?action=member&job=list&find='.$find.'&');

		$SQL = "SELECT m.uid, m.username, m.email, m.groupid, m.regdate, m.lastvisit 
				FROM {$db_prefix}members m ".($sql ? ' WHERE '.$sql : '').$limit;
	}
	else
	{
		showMsg('admin_illegal_parameter');
	}
	$query = $DB->db_query($SQL);
	$member = array();
	while ($rs = $DB->fetch_all($query))
	{
		$rs['group'] = $rs['groupid'] == '-1' ? $_grouplevel['7']['grouptitle'] : $_grouplevel[$rs['groupid']]['grouptitle'];
		$rs['regdate'] = gdate($rs['regdate'], 'y-m-d H:i');
		$rs['lastvisit'] = gdate($rs['lastvisit'], 'y-m-d H:i');
		$member[] = $rs;
	}
	$checkSubmit = 'onsubmit="return checkDel();"';
}
elseif ($job == 'del')
{
	if ($uids)
	{
		!is_array($uids) && $uids = settype($uids, 'array');
		$Member->Action('del', $uids);
		redirect('member_del_success', 'admincp.php?action=member&job=edit');
	}
}
elseif ($job == 'check')
{
	if (isset($checktype) && isPost())
	{
		//del or pass
		!$uids && !is_array($uids) && showMsg('admin_illegal_parameter');
		switch ($checktype)
		{
			case 'del':
			case 'check':
				$Member->Action($checktype, $uids, TRUE);
				redirect('member_'.$checktype.'_success', $basename);
				break;
			default:
				redirect('admin_illegal_parameter', $basename);
				break;
		}
	}
	//ready for check
	$rs = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}members m WHERE groupid = 6");
	$recordNum = (int)$rs['num'];
	$limit = sqlLimit($page);
	$multLink = cpmultLink($page, $recordNum, 'admincp.php?action=member&job=check&');

	$SQL = "SELECT uid, username, email, regdate FROM {$db_prefix}members WHERE groupid = 6 $limit";

	$query = $DB->db_query($SQL);
	$member = array();
	while ($rs = $DB->fetch_all($query))
	{
		$rs['regdate'] = gdate($rs['regdate'], 'Y-m-d H:i');
		$member[] = $rs;
	}
}
elseif ($job == 'mod')
{
	intConvert(array('uid'));
	$member = array();
	$SQL = "SELECT m.*, mx.* FROM {$db_prefix}members m LEFT JOIN {$db_prefix}memberexp mx USING (uid) WHERE m.uid = '$uid'";
	$member = $DB->fetch_one($SQL);
	!$member && showMsg('member_not_exist');
	if (isPost())
	{
		intConvert(array('groupid'));

		require_once PBDIGG_ROOT.'data/cache/cache_words.php';
		require_once PBDIGG_ROOT.'data/cache/cache_reg.php';
		require_once PBDIGG_ROOT.'include/ubb.func.php';

		$Member->Action('mod', array('uid'=>$uid, 'username'=>$username, 'password'=>$password, 'rpassword'=>$rpassword, 'email'=>$email, 'publicemail'=>isset($publicemail), 'groupid'=>(int)$groupid, 'gender'=>(int)$gender, 'currency'=>(int)$currency, 'regip'=>$regip, 'regdate'=>$regdate, 'ignore'=>'1', 'qq'=>$qq, 'msn'=>$msn,'site'=>$site,'location'=>$location,'birthday'=>$year.'-'.$month.'-'.$day,'signature'=>$signature,'showsign'=>isset($showsign)));
		redirect('member_mod_success', 'admincp.php?action=member&job=list&ispost=on&uid='.$uid);
	}

	radioChecked('gender_', $member['gender'], 4);

	$publicemail = $member['publicemail'] ? 'checked="checked"' : '';
	$showsign = $member['showsign'] ? 'checked="checked"' : '';
	$member['regdate'] = gdate($member['regdate'], 'Y-m-d H:i');

	list($year, $month, $day) = explode('-', $member['birthday']);
	require_once PBDIGG_ROOT.'include/DateTime.php';
	$year_option = html_select($_YEAR, 'year', $year, 'id="year"');
	$month_option = html_select($_MONTH, 'month', $month, 'id="month"');
	$day_option = html_select($_DAY, 'day', $day, 'id="day"');
	$group_option = array();
	foreach ($_grouplevel as $key => $value)
	{
		$key != '5' && $group_option[$key] = $value['grouptitle'];
	}
	//default add common user
	$groupid = ($member['groupid'] == -1) ? 7 : $member['groupid'];
	$group_option = html_select($group_option, 'groupid', $groupid, 'id="groupid"');
}
elseif ($job == 'tidy' && $process == 'on')
{
	intConvert(array('start', 'count'));
	!$count && $count = 500;
	$end = $start + $count;

	$query = $DB->db_query("SELECT uid FROM {$db_prefix}members WHERE uid >= $start AND uid < $end");
	if (!$DB->db_num($query))
	{
		redirect('member_tidy_success', 'admincp.php?action=member&job=tidy');
	}
	while ($rs = $DB->fetch_all($query))
	{
		//update user's article
		$num= $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}threads WHERE uid = ".$rs['uid']);
		$postnum = (int)$num['num'];
		$num = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}comments WHERE uid = ".$rs['uid']);
		$commentnum = (int)$num['num'];
		$num = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}attachments WHERE uid = ".$rs['uid']);
		$uploadnum = (int)$num['num'];
		$sql = '';
		$querytdata = $DB->db_query("SELECT type, COUNT(*) num FROM {$db_prefix}tdata WHERE uid = ".$rs['uid']." GROUP BY type");
		while ($trs = $DB->fetch_all($querytdata))
		{
			$sql .= ','.$trs['type']."num = '".(int)$trs['num']."'";
		}
		$DB->db_query("UPDATE {$db_prefix}members SET postnum = '$postnum', commentnum = '$commentnum', uploadnum = '$uploadnum' $sql WHERE uid = ".$rs['uid']);
	}
	redirect('member_tidy_process', 'admincp.php?action=member&job=tidy&process=on&start='.$end.'&count='.$count);
}

?>