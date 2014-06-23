<?php
/**
 * @version $Id: article.func.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

function delArticle($tids)
{
	global $DB, $db_prefix, $pb_creditdb, $Cache;

	if (!$tids) return false;
	!is_array($tids) && $tids = (array)$tids;
	$moduledata = $commentuid = $commentuid = array();
	$stids = implode(',', $tids);
	$creditdb = explode("\t", $pb_creditdb);
	$scurrency = intval($creditdb[2]);//精华待定
	$ccurrency = intval($creditdb[3]);
	require_once PBDIGG_ROOT.'include/attachment.func.php';
	require_once PBDIGG_ROOT.'include/module.class.php';
	$module = new module();
	
	$query = $DB->db_query("SELECT uid, cid, tid, module, topicimg FROM {$db_prefix}threads WHERE tid IN ($stids)");
	while ($article = $DB->fetch_all($query))
	{
		$uid = (int)$article['uid'];
		$cid = (int)$article['cid'];
		$tid = (int)$article['tid'];
		$moduleid = (int)$article['module'];

		$DB->db_exec("UPDATE {$db_prefix}members SET postnum = postnum - 1, currency = currency - $scurrency WHERE uid = '$uid'");

		$query = $DB->db_query("SELECT uid FROM {$db_prefix}comments WHERE tid = '$tid'");
		$cnum = intval($DB->db_num($query));
		while ($rs = $DB->fetch_all($query))
		{
			$rs['uid'] && $commentuid[$rs['uid']]++;
		}
		$DB->db_exec("UPDATE {$db_prefix}categories SET cnum = cnum - $cnum, tnum = tnum - 1 WHERE cid = '$cid'");

		/**** tag ***/
		$query = $DB->db_query("SELECT tagid FROM {$db_prefix}tagcache WHERE tid = '$tid'");
		$tagid = '';
		while ($rs = $DB->fetch_all($query))
		{
			$tagid .= ($tagid ? ',' : '').$rs['tagid'];
		}
		if ($tagid)
		{
			$DB->db_exec("UPDATE {$db_prefix}tags SET usenum = usenum - 1 WHERE tagid IN ($tagid)");
			$DB->db_exec("DELETE FROM {$db_prefix}tagcache WHERE tid = '$tid'");
		}
		if ($article['topicimg'])
		{
			list($tpath, $th, $tw, $ttype) = explode('|', $article['topicimg']);
			$ttype == '0' && PDel(PBDIGG_ATTACHMENT.'topic/'.$tpath);
		}
		$currentModuleData = $module->getModuleObject($moduleid);
		$currentModuleData && $currentModuleData->del($tid);
	}
	if ($commentuid)
	{
		foreach ($commentuid as $key => $value)
		{
			$DB->db_exec("UPDATE {$db_prefix}members SET commentnum = commentnum - $value, currency = currency - ".($value * $ccurrency)." WHERE uid = '$key'");
		}
	}
	$DB->db_exec("DELETE FROM {$db_prefix}threads WHERE tid IN ($stids)");
	$tnum = (int)$DB->db_affected_rows();
	$DB->db_exec("DELETE FROM {$db_prefix}tdata WHERE tid IN ($stids)");
	$DB->db_exec("DELETE FROM {$db_prefix}comments WHERE tid IN ($stids)");
	$cnum = (int)$DB->db_affected_rows();
	$DB->db_exec("DELETE FROM {$db_prefix}cdata WHERE tid IN ($stids)");
	delAttachment("tid IN ($stids)");
	$DB->db_exec("UPDATE {$db_prefix}sitestat SET artnum = artnum - $tnum, comnum = comnum - $cnum WHERE id = 1");
	$Cache->tplvar();
}

function delComment($rids)
{
	global $DB, $db_prefix, $pb_creditdb, $Cache;
	if (!$rids) return false;
	!is_array($rids) && $rids = (array)$rids;
	$srids = implode(',', $rids);
	$creditdb = explode("\t", $pb_creditdb);
	$currency = (int)$creditdb[3];
	$uids = $tids = $cids = array();
	$query = $DB->db_query("SELECT uid, cid, tid FROM {$db_prefix}comments WHERE rid IN ($srids)");
	while ($comment = $DB->fetch_all($query))
	{
		$comment['uid'] && $uids[$comment['uid']]++;
		$tids[$comment['tid']]++;
		$cids[$comment['cid']]++;
	}
	foreach ($uids as $k => $v)
	{
		$DB->db_exec("UPDATE {$db_prefix}members SET commentnum = commentnum - 1, currency = currency - ".($v * $currency)." WHERE uid = '$k'");
	}
	foreach ($tids as $k => $v)
	{
		$DB->db_exec("UPDATE {$db_prefix}threads SET comments = comments - $v WHERE tid = '$k'");
	}
	foreach ($cids as $k => $v)
	{
		$DB->db_exec("UPDATE {$db_prefix}categories SET cnum = cnum - $v WHERE cid = '$k'");
	}

	$DB->db_exec("DELETE FROM {$db_prefix}comments WHERE rid IN ($srids)");
	$cnum = (int)$DB->db_affected_rows();
	$DB->db_exec("DELETE FROM {$db_prefix}cdata WHERE rid IN ($srids)");
	$DB->db_exec("UPDATE {$db_prefix}sitestat SET comnum = comnum - $cnum WHERE id = 1");
	$Cache->tplvar();
}

?>
