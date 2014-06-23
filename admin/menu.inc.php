<?php
/**
 * @version $Id: menu.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

!defined('IN_PBDIGG') && exit ('Access Denied');

$_navmenu = array(
	'index' => array('quick'),
	'system' => array('setting'),
	'cate' => array('cate', 'tag'),
	'member' => array('member', 'group', 'message'),
	'content' => array('check', 'batch', 'special'),
	'template' => array('tpl'),
	'plugin' => array('module', 'plugin'),
	'tools' => array('tool', 'database', 'announcement', 'link'),
	'log' => array('log'),
);

$_leftmenu = &$option_message['leftmenu'];
?>
