<?php
/**
 * @version $Id: check.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

(!defined('IN_ADMIN') || !isset($_PBENV['PHP_SELF']) || !preg_match('/[\/\\\\]admincp\.php$/i', $_PBENV['PHP_SELF'])) && exit('Access Denied');

define('PB_PAGE', 'check');
require_once (PBDIGG_ROOT.'include/article.func.php');

if ($job == 'article')
{
	if (isPost() && $tids && is_array($tids))
	{
		if ($checktype == 'pass')
		{
			positiveInteger($tids);
			$tids = implode(',', $tids);
			$tids && $DB->db_exec("UPDATE {$db_prefix}threads SET ifcheck = 1 WHERE tid IN ($tids)");
			redirect('check_pass_success', $basename);
		}
		elseif ($checktype == 'del')
		{
			delArticle($tids);
			redirect('check_del_success', $basename);
		}
	}
	else
	{
		//list uncheck article
		require_once PBDIGG_ROOT.'include/module.class.php';
		$module = new module();
		$moduledata = $module->getModuleMenu();
		$rs = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}threads WHERE ifcheck = 0");
		$recordNum = (int)$rs['num'];
		$limit = sqlLimit($page);
		$multLink = cpmultLink($page, $recordNum, 'admincp.php?action=check&job=article&');

		$query = $DB->db_query("SELECT tid, cid, subject, author, postdate, module FROM {$db_prefix}threads WHERE ifcheck = 0 $limit");
		$article = array();
		while ($rs = $DB->fetch_all($query))
		{
			!$rs['author'] && $rs['author'] = $cp_message['anonymity'];
			$rs['postdate'] = gdate($rs['postdate'], 'Y-m-d H:i');
			$rs['modulename'] = $moduledata[$rs['module']][1];
			$article[] = $rs;
		}
	}
}
elseif ($job == 'comment')
{
	if (isPost() && $rids && is_array($rids))
	{
		if ($checktype == 'pass')
		{
			positiveInteger($rids);
			$rids = implode(',', $rids);
			$DB->db_query("UPDATE {$db_prefix}comments SET ifcheck = 1 WHERE rid IN ($rids)");
			redirect('check_pass_success', $basename);
		}
		elseif ($checktype == 'del')
		{
			delComment($rids);
			redirect('check_del_success', $basename);
		}
	}
	else
	{
		//list uncheck comments
		$rs = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}comments WHERE ifcheck = 0");
		$recordNum = (int)$rs['num'];
		$limit = sqlLimit($page);
		$multLink = cpmultLink($page, $recordNum, 'admincp.php?action=check&job=comment&');
		$query = $DB->db_query("SELECT rid, tid, content, author, postdate FROM {$db_prefix}comments WHERE ifcheck = 0 $limit");
		$comment = array();
		while ($rs = $DB->fetch_all($query))
		{
			!$rs['author'] && $rs['author'] = $cp_message['anonymity'];
			$rs['postdate'] = gdate($rs['postdate'], 'Y-m-d H:i');
			$rs['content'] = PBSubstr($rs['content'], 35);
			$comment[] = $rs;
		}
	}
}

?>
