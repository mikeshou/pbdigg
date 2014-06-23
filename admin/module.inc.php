<?php
/**
 * @version $Id: module.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

(!defined('IN_ADMIN') || !isset($_PBENV['PHP_SELF']) || !preg_match('/[\/\\\\]admincp\.php$/i', $_PBENV['PHP_SELF'])) && exit('Access Denied');

define('PB_PAGE', 'module');

require_once PBDIGG_ROOT.'include/module.class.php';
$module = new module();

switch ($job)
{
	case 'add':
		if (isPost())
		{
			charConvert(array('moduleDir'));
			$moduleInfo = $module->addModule($moduleDir, $step);
		}
		break;

	case 'edit':
		$moduledata = $module->listModule();
		break;
	
	case 'status':
		intConvert(array('mid'));
		$moduledata = $module->changeModuleStatus($mid);
		redirect('module_status_success', 'admincp.php?action=module&job=edit');
		break;
	
	case 'del':
		intConvert(array('mid'));
		if (isPost())
		{
			$module->delModule($mid);
			redirect('module_del_success', 'admincp.php?action=module&job=edit');
		}
		break;

	case 'mod':
	case 'help':
		intConvert(array('mid'));
		$moduledata = $module->getSingleModuleData($mid);
		(!$moduledata || preg_replace('~[a-z0-9_]~i', '', $moduledata['identifier'])) && showMsg('module_data_noexist');
		$identifier = $moduledata['identifier'];
		require_once PBDIGG_ROOT.'module/'.$identifier.'/admin/admin.php';
		break;	
}

?>