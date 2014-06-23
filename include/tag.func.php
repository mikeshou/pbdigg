<?php
/**
 * @version $Id: tag.func.php v2.1 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

/**
 * 删除标签
 * 
 * @param Array $tagid 标签ID数组
 * @return Boolean 是否正确删除
 */
function delTag($tagid)
{
	global $DB, $db_prefix;
	if (empty($tagid) || !is_array($tagid))
	{
		return FALSE;
	}
	$tagid = implode(',', $tagid);
	$DB->db_exec("DELETE FROM {$db_prefix}tagcache WHERE tagid IN ($tagid)");
	$DB->db_exec("DELETE FROM {$db_prefix}tags WHERE tagid IN ($tagid)");
	return TRUE;
}

?>