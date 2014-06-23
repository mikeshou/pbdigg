<?php
/**
 * @version $Id: tag.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_PAGE', 'tag');
require_once './include/common.inc.php';

$tag_message = loadLang('tag');
$common_message += $tag_message;

$recordNum = intval($DB->fetch_first("SELECT COUNT(*) num FROM {$db_prefix}tags WHERE ifopen = 1"));
$pagesize = (int)$pb_tperpage;
$limit = sqlLimit($p, $pagesize);
$multLink = multLink($p, $recordNum, $pb_sitedir.'tag.php?', $pagesize);

$tags = array();

$query = $DB->db_query("SELECT tagname, usenum FROM {$db_prefix}tags WHERE ifopen = 1 $limit");
while ($rs = $DB->fetch_all($query))
{
	$tags[] = array(rawurlencode($rs['tagname']), $rs['tagname'], $rs['usenum'], getTagStyle());
}

$pb_seotitle = $tag_message['tag_title'];

require_once pt_fetch('tag');

PBOutPut();
?>
