<?php
/**
 * @version $Id: left.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

(!defined('IN_ADMIN') || !isset($_PBENV['PHP_SELF']) || !preg_match('/[\/\\\\]admincp\.php$/i', $_PBENV['PHP_SELF'])) && exit('Access Denied');

define('PB_PAGE', 'left');

//require_once PBDIGG_CP.'include/module.class.php';
//
//$module = new module();
//
//$menu['module']['item'] = $module->getModuleMenu();

$menudb = array();

foreach ($menu as $k => $v)
{
	if ($adminright[$k])
	{
		$menudb[$k] = $v;
	}
}

?>