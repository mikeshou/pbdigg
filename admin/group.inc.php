<?php
/**
 * @version $Id: group.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

(!defined('IN_ADMIN') || !isset($_PBENV['PHP_SELF']) || !preg_match('/[\/\\\\]admincp\.php$/i', $_PBENV['PHP_SELF'])) && exit('Access Denied');

define('PB_PAGE', 'group');

if ($job == 'count')
{
	$groups = array();
	$query = $DB->db_query("SELECT COUNT(*) amount, groupid FROM {$db_prefix}members GROUP BY groupid ORDER BY groupid");
	while ($rs = $DB->fetch_all($query))
	{
		if ($rs['groupid'] == -1)
		{
			$rs['groupname'] = $_grouplevel['7']['grouptitle'];
			$rs['groupid'] = 7;
		}
		else
		{
			$rs['groupname'] = $_grouplevel[$rs['groupid']]['grouptitle'];
		}
		$groups[] = $rs;
	}
}
elseif ($job == 'admin')
{
	$groups = array();
	$query = $DB->db_query("SELECT a.adminid, g.groupid FROM {$db_prefix}admingroups a LEFT JOIN {$db_prefix}usergroups g USING (adminid)");
	while ($rs = $DB->fetch_all($query))
	{
		$rs['groupname'] = $_grouplevel[$rs['adminid']]['grouptitle'];
		$groups[] = $rs;
	}
}
elseif ($job == 'user')
{
	$systemgroup = $membergroup = $specialgroup = array();
	$query = $DB->db_query("SELECT groupid, grouptitle, gtype FROM {$db_prefix}usergroups");
	while ($rs = $DB->fetch_all($query))
	{
		$rs['groupname'] = $_grouplevel[$rs['groupid']]['grouptitle'];
		switch ($rs['gtype'])
		{
			case 'system':
				$systemgroup[] = $rs;
				break;
			case 'member':
				$membergroup[] = $rs;
				break;
			case 'special':
				$specialgroup[] = $rs;
				break;
		}
	}
}
elseif ($job == 'mod')
{
	switch ($type)
	{
		case 'admin':
		
			intConvert(array('adminid'));
			!SUPERMANAGER && showMsg('admin_nopermission');
			$rs = $DB->fetch_one("SELECT * FROM {$db_prefix}admingroups WHERE adminid = '$adminid'");
			!$rs && showMsg('admin_illegal_parameter');
			if (isPost())
			{
				$sql = $mright = '';
				$sql .= "allowadmincp = '".(isset($allowadmincp) ? 1 : 0)."',";
				$sql .= "alloweditatc = '".(isset($alloweditatc) ? 1 : 0)."',";
				$sql .= "allowdelatc = '".(isset($allowdelatc) ? 1 : 0)."',";
				$sql .= "allowcheckatc = '".(isset($allowcheckatc) ? 1 : 0)."',";
				$sql .= "allowlockatc = '".(isset($allowlockatc) ? 1 : 0)."',";
				$sql .= "allowmoveatc = '".(isset($allowmoveatc) ? 1 : 0)."',";
				$sql .= "allowcopyatc = '".(isset($allowcopyatc) ? 1 : 0)."',";
				$sql .= "allowtopatc = '".(isset($allowtopatc) ? 1 : 0)."',";
				$sql .= "allowcommend = '".(isset($allowcommend) ? 1 : 0)."',";
				$sql .= "allowshield = '".(isset($allowshield) ? 1 : 0)."',";
				$sql .= "allowtitlestyle = '".(isset($allowtitlestyle) ? 1 : 0)."',";
				
				$rights = array('setting','cate','tag','member','group','module','check','special','batch','message','plugin','tpl','database','tool','announcement','link','log');
				foreach ($rights as $v)
				{
					$mright[$v] = isset($admin_right[$v]) ? 1 : 0;
				}
				$sql .= "adminright = '".serialize($mright)."'";
				$DB->db_exec("UPDATE {$db_prefix}admingroups SET $sql WHERE adminid = '$adminid'");

				$Cache->adminGroupCache();
				redirect('group_mod_success', 'admincp.php?action=group&job=admin');
			}

			@extract($rs, EXTR_OVERWRITE);
			
			$allowadmincp = $allowadmincp ? 'checked="checked"' : '';
			$alloweditatc = $alloweditatc ? 'checked="checked"' : '';
			$allowdelatc = $allowdelatc ? 'checked="checked"' : '';
			$allowcheckatc = $allowcheckatc ? 'checked="checked"' : '';
			$allowlockatc = $allowlockatc ? 'checked="checked"' : '';
			$allowmoveatc = $allowmoveatc ? 'checked="checked"' : '';
			$allowcopyatc = $allowcopyatc ? 'checked="checked"' : '';
			$allowtopatc = $allowtopatc ? 'checked="checked"' : '';
			$allowcommend = $allowcommend ? 'checked="checked"' : '';
			$allowshield = $allowshield ? 'checked="checked"' : '';
			$allowtitlestyle = $allowtitlestyle ? 'checked="checked"' : '';
			$admin_right = array();
			$adminright = unserialize($adminright);
			foreach ($adminright as $k => $v)
			{
				$admin_right[$k] = $v ? 'checked="checked"' : '';
			}
			break;
			
		case 'user':
			intConvert(array('groupid'));
			$rs = $DB->fetch_one("SELECT * FROM {$db_prefix}usergroups WHERE groupid = '$groupid'");
			!$rs && showMsg('admin_illegal_parameter');
			if (isPost())
			{
				$sql = '';
				$sql .= "allowvisit = '".(isset($allowvisit) ? 1 : 0)."',";
				$sql .= "allowsort = '".(isset($allowsort) ? 1 : 0)."',";
				$sql .= "allowread = '".(isset($allowread) ? 1 : 0)."',";
				$sql .= "allowpost = '".(isset($allowpost) ? 1 : 0)."',";
				$sql .= "allowcomment = '".(isset($allowcomment) ? 1 : 0)."',";
				$sql .= "allowdigg = '".(isset($allowdigg) ? 1 : 0)."',";
				$sql .= "allowbury = '".(isset($allowbury) ? 1 : 0)."',";
				$sql .= "allowbsearch = '".(isset($allowbsearch) ? 1 : 0)."',";
				$sql .= "allowasearch = '".(isset($allowasearch) ? 1 : 0)."',";
				$sql .= "searchmax = '".(($searchmax > 0) ? (int)$searchmax : 0)."',";
				$sql .= "allowreport = '".(isset($allowreport) ? 1 : 0)."',";
				$sql .= "allowhtml = '".(isset($allowhtml) ? 1 : 0)."',";
				$sql .= "allowmsg = '".(isset($allowmsg) ? 1 : 0)."',";
				$sql .= "msgmax = '".(($msgmax > 0) ? (int)$msgmax : 0)."',";
				$sql .= "allowfavors = '".(isset($allowfavors) ? 1 : 0)."',";
				$sql .= "favorsmax = '".(($favorsmax > 0) ? (int)$favorsmax : 0)."',";
				$sql .= "allowavatar = '".(isset($allowavatar) ? 1 : 0)."',";
				$sql .= "allowaupload = '".(isset($allowaupload) ? 1 : 0)."',";
				$uploadmax = (int)$uploadmax * 1024;
				$uploadmax > ini_bytes('upload_max_filesize') && showMsg('group_uploadmax_error');
				$sql .= "uploadmax = '$uploadmax',";
				$newuploadtype = '';
				$uploadtype = strtolower(str_replace("\xa3\xac",',',trim($uploadtype)));
				$uploadtype = explode(',', $uploadtype);
				foreach ($uploadtype as $ext)
				{
					if (preg_match('~^[a-z\d]+$~', $ext) && !preg_match("~(?:php|phtml|php3|php4|jsp|exe|dll|asp|cer|asa|shtml|shtm|aspx|asax|cgi|fcgi|pl)~i", $ext))
					{
						$newuploadtype .= ($newuploadtype ? ',' : '') . $ext;
					}
				}
				$sql .= "uploadtype = '$newuploadtype',";
				$sql .= "allowurl = '".(isset($allowurl) ? 1 : 0)."',";
				$sql .= "allowtimestamp = '".(isset($allowtimestamp) ? 1 : 0)."',";
				$sql .= "allowinitstatus = '".(isset($allowinitstatus) ? 1 : 0)."',";
				$sql .= "inithit = '".(($inithit > 0) ? (int)$inithit : 0)."',";
				$sql .= "initdigg = '".(($initdigg > 0) ? (int)$initdigg : 0)."',";
				$sql .= "initbury = '".(($initbury > 0) ? (int)$initbury : 0)."',";
				$sql .= "allowad = '".(isset($allowad) ? 1 : 0)."'";

				$DB->db_exec("UPDATE {$db_prefix}usergroups SET $sql WHERE groupid = '$groupid'");
				$Cache->userGroupCache();
				redirect('group_mod_success', 'admincp.php?action=group&job=user');
			}
			else
			{
				@extract($rs, EXTR_OVERWRITE);
				foreach ($rs as $k => $v)
				{
					substr($k, 0, 5) == 'allow' && $$k = $v ? 'checked="checked"' : '';
				}
				$uploadmax = ceil($uploadmax / 1024);
			}
			break;
	}
}

?>
