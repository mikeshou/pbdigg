<?php
/**
 * @version $Id: plugin.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

(!defined('IN_ADMIN') || !isset($_PBENV['PHP_SELF']) || !preg_match('/[\/\\\\]admincp\.php$/i', $_PBENV['PHP_SELF'])) && exit('Access Denied');

define('PB_PAGE', 'plugin');

require_once PBDIGG_ROOT.'include/plugin.class.php';
$plugin = new plugin();

switch ($job)
{
	case 'add':
		if (isPost())
		{
			charConvert(array('pluginDir'));
			$pluginInfo = $plugin->addPlugin($pluginDir, $step);
		}
		break;

	case 'edit':
		$plugindata = $plugin->listPlugin();
		break;

	case 'status':
		intConvert(array('pid'));
		$plugin->changePluginStatus($pid);
		redirect('plugin_status_success', 'admincp.php?action=plugin&job=edit');
		break;

	case 'del':
		intConvert(array('pid'));
		if (isPost())
		{
			$plugin->delPlugin($pid);
			redirect('plugin_del_success', 'admincp.php?action=plugin&job=edit');
		}
		break;

	case 'mod':
		if (preg_match('~^[a-z0-9_]+$~i', $pmark) && file_exists(PBDIGG_ROOT.'plugins/'.$pmark.'/admin.php'))
		{
			require_once PBDIGG_ROOT.'plugins/'.$pmark.'/admin.php';
		}
		else
		{
			showMsg('plugin_no_cpset');
		}
		break;

	default:	
		break;
}

?>