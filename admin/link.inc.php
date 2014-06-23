<?php
/**
 * @version $Id: link.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

(!defined('IN_ADMIN') || !isset($_PBENV['PHP_SELF']) || !preg_match('/[\/\\\\]admincp\.php$/i', $_PBENV['PHP_SELF'])) && exit('Access Denied');

define('PB_PAGE', 'link');

if ($job == 'add')
{
	if (isPost())
	{
		intConvert(array('status','displayorder'));
		charConvert(array('sitename','siteurl','logo','description'));
		(!$sitename || !$siteurl) && showMsg('link_empty');
		substr($siteurl, 0, 4) != 'http' && $siteurl = 'http://'.$siteurl;
		checkURL($siteurl);
		$logo && checkURL($siteurl);

		$rs = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}links WHERE siteurl = '$siteurl'");
		$rs['num'] && showMsg('link_exist');
		$DB->db_exec("INSERT INTO {$db_prefix}links (displayorder,ifshow,sitename,siteurl,description,logo) VALUES ('$displayorder','$status','$sitename','$siteurl','$description','$logo')");
		$Cache->flink();
		redirect('link_add_success', $basename);
	}
	$status_1 = 'checked="checked"';
}
elseif ($job == 'edit')
{
	if (isPost() && $lids)
	{
		!is_array($lids) && $lids = settype($lids, 'array');
		positiveInteger($lids);
		$lids && $DB->db_exec("DELETE FROM {$db_prefix}links WHERE lid IN (".implode(',', $lids).")");
		$Cache->flink();
		redirect('link_del_success', $basename);
	}
	$links = array();
	$query = $DB->db_query("SELECT lid, displayorder, ifshow, sitename, siteurl FROM {$db_prefix}links");

	while ($rs = $DB->fetch_all($query))
	{
		$rs['ifshow'] = $rs['ifshow'] ? $cp_message['yes'] : '<span class="r">'.$cp_message['not'].'</span>';
		$links[] = $rs;
	}
	$checkSubmit = 'onsubmit="return checkDel();"';
}
elseif ($job == 'mod')
{
	intConvert(array('lid'));
	$link = $DB->fetch_one("SELECT * FROM {$db_prefix}links WHERE lid = '$lid'");
	!$link && showMsg('admin_illegal_parameter');
	if (isPost())
	{
		$sql = '';
		intConvert(array('status','displayorder'));
		charConvert(array('sitename','siteurl','logo','description'));
		(!$sitename || !$siteurl) && showMsg('link_empty');
		$sql .= "sitename = '$sitename',";
		substr($siteurl, 0, 4) != 'http' && $siteurl = 'http://'.$siteurl;
		checkURL($siteurl);
		$sql .= "siteurl = '$siteurl',";
		if ($logo)
		{
			checkURL($logo);
		}
		$sql .= "logo = '$logo',";
		$sql .= "description = '$description',";
		$sql .= "ifshow = '$status',";
		$sql .= "displayorder = '$displayorder'";
		$rs = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}links WHERE siteurl = '$siteurl' AND lid <> '$lid'");
		$rs['num'] && showMsg('link_exist');
		$DB->db_exec("UPDATE {$db_prefix}links SET $sql WHERE lid = '$lid'");
		$Cache->flink();
		redirect('link_mod_success', 'admincp.php?action=link&job=edit');
	}
	radioChecked('status_', $link['ifshow']);
}

?>
