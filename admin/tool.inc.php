<?php
/**
 * @version $Id: tool.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

(!defined('IN_ADMIN') || !isset($_PBENV['PHP_SELF']) || !preg_match('/[\/\\\\]admincp\.php$/i', $_PBENV['PHP_SELF'])) && exit('Access Denied');

define('PB_PAGE', 'tool');

if ($job == 'cache')
{
	if (isPost())
	{
		if ($config)
		{
			$Cache->config();
			$Cache->reg();
			$Cache->uc();
		}
		if ($cate)
		{
			$Cache->categories();
			$Cache->singlecate();
		}
		if ($article || $comment || $member)
		{
			$Cache->tplvar();
		}
		if ($tag)
		{
			$Cache->tags();
		}
		if ($link)
		{
			$Cache->flink();
		}
		if ($announce)
		{
			$Cache->announce();
		}
		if ($stat)
		{
			$Cache->grouplevel();
			$Cache->userGroupCache();
			$Cache->adminGroupCache();
			$Cache->plugins();
		}
		redirect('cache_refresh_success', $basename);
	}
}
elseif ($job == 'recount')
{
	switch ($type)
	{
		case 'article':
			intConvert(array('start', 'count'));
			!$count && $count = 500;
			$end = $start + $count;
			$query = $DB->db_query("SELECT tid FROM {$db_prefix}threads WHERE tid >= $start AND tid < $end");
			if (!$DB->db_num($query))
			{
				redirect('recount_success', $basename);
			}
			while ($rs = $DB->fetch_all($query))
			{
				$tidcache = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}comments WHERE tid = ".$rs['tid']);
				$DB->db_exec("UPDATE {$db_prefix}threads SET comments = ".(int)$tidcache['num']." WHERE tid = ".$rs['tid']);
			}
			redirect('recount_process', 'admincp.php?action=tool&job=recount&type=article&start='.$end.'&count='.$count);
			break;
			
		case 'member':
			intConvert(array('start', 'count'));
			!$count && $count = 500;
			$end = $start + $count;
			$query = $DB->db_query("SELECT uid FROM {$db_prefix}members WHERE uid >= $start AND uid < $end");
			if (!$DB->db_num($query))
			{
				redirect('recount_success', $basename);
			}
			while ($rs = $DB->fetch_all($query))
			{
				$t = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}threads WHERE uid = ".$rs['uid']);
				$c = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}comments WHERE uid = ".$rs['uid']);
				$a = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}attachments  WHERE uid = ".$rs['uid']);
				$DB->db_exec("UPDATE {$db_prefix}members SET postnum = '".(int)$t['num']."', commentnum = '".(int)$c['num']."', uploadnum = '".(int)$a['num']."' WHERE uid = ".$rs['uid']);
			}
			redirect('recount_process', 'admincp.php?action=tool&job=recount&type=member&start='.$end.'&count='.$count);
			break;
		
		case 'cate':
			$query = $DB->db_query("SELECT cid, COUNT(*) num FROM {$db_prefix}threads GROUP BY cid");
			while ($a = $DB->fetch_all($query))
			{
				$recount[$a['cid']][0] = (int)$a['num'];
			}
			$query = $DB->db_query("SELECT cid, COUNT(*) num FROM {$db_prefix}comments GROUP BY cid");
			while ($c = $DB->fetch_all($query))
			{
				$recount[$c['cid']][1] = (int)$c['num'];
			}
			$tnum = $cnum = 0;
			foreach ($recount as $k => $v)
			{
				$tnum += $v[0];
				$cnum += $v[1];
				$DB->db_query("UPDATE {$db_prefix}categories SET tnum = '".$v[0]."', cnum = '".$v[1]."' WHERE cid = '$k'");
			}
			$pnum = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}categories");
			$mnum = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}members");
			$mname = $DB->fetch_one("SELECT username FROM {$db_prefix}members ORDER BY uid DESC LIMIT 1");
			$DB->db_query("UPDATE {$db_prefix}sitestat SET newmember = '".addslashes($mname['username'])."', membernum = '".(int)$mnum['num']."', catenum = '".(int)$pnum['num']."', artnum = '$tnum', comnum = '$cnum' WHERE id = 1");
			redirect('recount_success', $basename);
			break;
	}
}
elseif ($job == 'filepermission')
{
	$systemdirarray = array (
		basename(PBDIGG_CP).'/dbak',
		$_attdir,
		'cache',
		'compile',
		'data/cache',
		'images',
		'log',
	);
	$systemdir = dirPermission($systemdirarray);
}

?>
