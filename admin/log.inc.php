<?php
/**
 * @version $Id: log.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

(!defined('IN_ADMIN') || !isset($_PBENV['PHP_SELF']) || !preg_match('/[\/\\\\]admincp\.php$/i', $_PBENV['PHP_SELF'])) && exit('Access Denied');

define('PB_PAGE', 'log');

$multLink = $recordNum = '';
$logs = array();
$limit = sqlLimit($page);

if ($job == 'admin')
{
	if (isPost())
	{
		if (SUPERMANAGER)
		{
			$DB->db_query("DELETE FROM {$db_prefix}adminlogs WHERE islog = 0 AND logdate < ($timestamp - 259200)");
			redirect('log_del_success', $basename);
		}
		else
		{
			redirect('admin_nopermission', $basename);
		}
	}

	$rs = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}adminlogs WHERE islog = 0");
	$recordNum = (int)$rs['num'];
	$multLink = cpmultLink($page, $recordNum, 'admincp.php?action=log&job=admin&');
	$SQL = "SELECT l.*, m.username FROM {$db_prefix}adminlogs l 
			LEFT JOIN {$db_prefix}members m 
			ON l.uid = m.uid 
			WHERE l.islog = 0 
			ORDER BY l.logdate DESC $limit";
	$query = $DB->db_query($SQL);
	while ($rs = $DB->fetch_all($query))
	{
		$rs['logdate'] = gdate($rs['logdate'], 'Y-m-d H:i');
		$rs['description'] = $rs['description'] ? $rs['description'] : '&nbsp;';
		$logs[] =  $rs;
	}
}
elseif ($job == 'login')
{
	if (isPost())
	{
		if (SUPERMANAGER)
		{
			$DB->db_query("DELETE FROM {$db_prefix}adminlogs WHERE islog = 1 AND logdate < ($timestamp - 259200)");
			redirect('log_del_success', $basename);
		}
		else
		{
			redirect('admin_nopermission', $basename);
		}
	}
	
	$rs = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}adminlogs WHERE islog = 1");
	$recordNum = (int)$rs['num'];
	$multLink = cpmultLink($page, $recordNum, 'admincp.php?action=log&job=login&');
	$SQL = "SELECT l.*, m.username FROM {$db_prefix}adminlogs l 
			LEFT JOIN {$db_prefix}members m 
			ON l.uid = m.uid 
			WHERE l.islog = 1 
			ORDER BY l.logdate DESC $limit";
	$query = $DB->db_query($SQL);
	while ($rs = $DB->fetch_all($query))
	{
		$rs['logdate'] = gdate($rs['logdate'], 'Y-m-d H:i');
		$rs['username'] = $rs['username'] ? $rs['username'] : '&nbsp;';
		$rs['result'] = $rs['result'] ? '<span class="g">'.$cp_message['login_success'].'</span>' : '<span class="r">'.$cp_message['login_failed'].'</span>';
		$rs['description'] = $rs['result'].$rs['description'];
		$logs[] = $rs;
	}
}
elseif ($job == 'common')
{
	if (isPost())
	{
		if (SUPERMANAGER)
		{
			$DB->db_query("TRUNCATE TABLE {$db_prefix}commonlogs");
			redirect('log_del_success', $basename);
		}
		else
		{
			redirect('admin_nopermission', $basename);
		}
	}
	else
	{
		$rs = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}commonlogs");
		$recordNum = (int)$rs['num'];
		$multLink = cpmultLink($page, $recordNum, 'admincp.php?action=log&job=login&');
		$SQL = "SELECT l.*, m.username FROM {$db_prefix}commonlogs l 
				LEFT JOIN {$db_prefix}members m 
				ON l.uid = m.uid 
				ORDER BY l.logdate DESC $limit";
		$query = $DB->db_query($SQL);
		while ($rs = $DB->fetch_all($query))
		{
			$rs['logdate'] = gdate($rs['logdate'], 'Y-m-d H:i');
			$rs['username'] = $rs['username'] ? $rs['username'] : '&nbsp;';
			$logs[] = $rs;
		}
	}
}

$checkSubmit = 'onsubmit="return checkDel();"';

?>
