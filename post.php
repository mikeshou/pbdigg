<?php
/**
 * @version $Id: post.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_PAGE', 'post');
require_once './include/common.inc.php';
require_once PBDIGG_ROOT.'include/validate.func.php';
require_once PBDIGG_ROOT.'include/ubb.func.php';
@include_once PBDIGG_CROOT.'cache_specialtpl.php';

$post_message = loadLang('post');
$common_message += $post_message;

!$pb_ifpost && !SUPERMANAGER && showMsg('post_thread_closed');

!$allowpost && redirect('post_newthread_nopermission', $customer['groupid'] == '6' ? 'member.php' : 'login.php?forward=post.php');

!in_array($action, array('add', 'edit')) && $action = 'add';

if ($pb_reposttime)
{
	$pb_lastpost = $customer['uid'] ?  $customer['lastpost'] : gCookie('pb_lastpost');
	$action != 'edit' && !$customer['adminid'] && $pb_lastpost && ($timestamp - $pb_lastpost < $pb_reposttime) && showMsg('post_flood_ctrl');
}

list($title_min, $title_max) = explode("\t", $pb_titlelen);
list($content_min, $content_max) = explode("\t", $pb_contentlen);
$uploadattachment = $allowaupload && $pb_allowupload;
$uploadtopicimg = $uploadattachment && $pb_uploadtopicimg;
$rssurl = '';
			
//header('Cache-control: private, must-revalidate');
$currentCateData = $currentModuleData = $currentModuleObj = $postcontent = $moduleIdentifier = '';
$moduleid = (int)$moduleid;

if ($cid && isset($_categories[$cid]))
{
	$currentCateData = &$_categories[$cid];
	!$allowsort && !$currentCateData['status'] && showMsg('post_cate_newthread_nopermission');
	!$logStatus && !$currentCateData['anonymity'] && showMsg('post_cate_contribute_nopermission');
	if (count($currentCateData['ttype']) == 1) $moduleid = $currentCateData['ttype'][0];
	if ($currentModuleData = checkModuleID($moduleid))
	{
		$post = array();
		$currentModuleObj = $module->getModuleObject($moduleid);
		!$currentModuleObj && showMsg('illegal_request');
		$moduleIdentifier = $currentModuleData['identifier'];
		$currentModuleObj->$action($post, $tid);
	}
	else
	{
		$moduleList = array();
		$modules = $module->getSingleModuleData();
		foreach ($modules as $k => $v)
		{
			in_array($k, $currentCateData['ttype']) && $v[$v['identifier'].'_status'] && $moduleList[] = $v;
		}
		empty($moduleList) && showMsg('post_module_error');
	}
}
else
{
	$cid = $moduleid = '';
	$chosecate = '';
	unlimitBlockMenu($chosecate);
}


function unlimitBlockMenu(&$menu, $start = 0)
{
	$child = getChild($start);
	$menu .= '<ul>';
	foreach ($child as $k => $v)
	{
		$menu .= '<li><a href="post.php?cid='.$k.'">'.$v['name'].'</a>';
		if (getChild($k))
		{
			unlimitBlockMenu($menu, $k);
		}
		$menu .= '</li>';
	}
	$menu .= '</ul>';
}
function getChild($cid)
{
	global $_categories;
	$child = array();
	foreach($_categories as $k => $v)
	{
		$v['cup'] == $cid && $child[$k] = $v;
	}
	return $child;
}
function checkModuleID($moduleid)
{
	global $module, $currentCateData, $currentModuleData;
	if ($moduleid && $module->checkModuleID($moduleid) && in_array($moduleid, $currentCateData['ttype']))
	{
		$currentModuleData = $module->getSingleModuleData($moduleid);
		if ($currentModuleData[$currentModuleData['identifier'].'_status']) return $currentModuleData;
	}
}

$pb_seotitle = $post_message['post_'.$action.'_title'];

require_once pt_fetch('post');

PBOutPut();

?>
