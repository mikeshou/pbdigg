<?php
/**
 * @version $Id: plugin.ucmerge.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

!defined('IN_ADMIN') && exit('Access Denied!');

define('IN_PLUGIN', 'ucmerge');

$plugin_message = loadPluginLang(IN_PLUGIN, TRUE);

if (!$pb_ucenable)
{
	showMsg('ucmerge_status_error');
}

if ($process == 'on')
{
	$pbuc = isset($pbuc) ? '1' : '0';
	(!is_numeric($percount) || $percount < 0) && $percount = 500;
	$UC = new uc();
	$info = $UC->merge_user($start, $merge, $percount, $pbuc);
	redirect('ucmerge_process_ing', 'admincp.php?action=plugin&pmark=ucmerge&job=mod&process=on&start='.$info['start'].'&merge='.$info['merge'].'&percount='.$percount.'&pbuc='.$pbuc);
}

require_once pt_plugin_fetch('admin', 'ucmerge', 'admin');

PBOutPut();

?>