<?php
/**
 * @version $Id: cate.func.php v2.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

/**
 * 删除分类
 * 
 * @param Int $cid 分类ID
 */
function delCate($cid)
{
	global $DB, $db_prefix, $_categories, $Cache, $module;
	$cid = (int)$cid;
	$rs = $DB->fetch_one("SELECT * FROM {$db_prefix}categories WHERE cid = '$cid'");
	if (!$rs)
	{
		//cate not exist
		return 'cate_not_exist';
	}
	$cup = $rs['cup'];
	$rs = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}categories WHERE cup = '$cid'");
	if ($rs['num'])
	{
		//if exists sub cate
		return 'cate_sub_exist';
	}
	//del cate
	require_once PBDIGG_ROOT.'include/attachment.func.php';
	delAttachment("cid = $cid");
	if ($_categories[$cid]['icon'])
	{
		PDel(PBDIGG_ROOT.'images/cate/'.$_categories[$cid]['icon']);
	}
	$module->delArticleByCid($cid);
	$DB->db_exec("DELETE FROM {$db_prefix}categories WHERE cid = '$cid'");
	$DB->db_exec("DELETE FROM {$db_prefix}threads WHERE cid = '$cid'");
	$DB->db_exec("DELETE FROM {$db_prefix}comments WHERE cid = '$cid'");
	if ($cup)
	{
		$childnum = $DB->fetch_first("SELECT COUNT(*) num FROM {$db_prefix}categories WHERE cup = '$cup'");
		$DB->db_exec("UPDATE {$db_prefix}categories SET withchild = ".($childnum ? 1 : 0)." WHERE cid = '$cup'");
	}
	$DB->db_exec("UPDATE {$db_prefix}sitestat SET catenum = catenum - 1 WHERE id = 1");

	PDel(PBDIGG_ROOT.'data/cache/cache_singlecate_'.$cid.'.php');
	PDel(PBDIGG_ROOT.'data/cache/cache_cate_option.php');
	PDel(PBDIGG_ROOT.'data/cache/cache_cate_table.php');

	$Cache->config();
	$Cache->categories();
}

/**
 * 合并分类
 * 
 * @param Int $scid 源分类ID
 * @param Int $dcid 目标分类ID
 */
function mergeCate($scid, $dcid)
{
	global $DB, $db_prefix, $Cache;
	$rs = $DB->fetch_one("SELECT tnum, cnum FROM {$db_prefix}categories WHERE cid = $scid");
	$tnum = (int)$rs['tnum'];
	$cnum = (int)$rs['cnum'];

	$DB->db_exec("UPDATE {$db_prefix}threads SET cid = $dcid WHERE cid = $scid");
	$DB->db_exec("UPDATE {$db_prefix}comments SET cid = $dcid WHERE cid = $scid");
	$DB->db_exec("UPDATE {$db_prefix}attachments SET cid = $dcid WHERE cid = $scid");
	$DB->db_exec("UPDATE {$db_prefix}categories SET tnum = tnum + $tnum, cnum = cnum + $cnum WHERE cid = $dcid");
	$DB->db_exec("DELETE FROM {$db_prefix}categories WHERE cid = $scid");
	$DB->db_exec("UPDATE {$db_prefix}sitestat SET catenum = catenum - 1 WHERE id = 1");

	PDel(PBDIGG_ROOT.'data/cache/cache_singlecate_'.$scid.'.php');
	PDel(PBDIGG_ROOT.'data/cache/cache_cate_option.php');
	PDel(PBDIGG_ROOT.'data/cache/cache_cate_table.php');

	$Cache->config();
	$Cache->categories();
}
?>
