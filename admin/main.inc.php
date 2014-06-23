<?php
/**
 * @version $Id: main.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

(!defined('IN_ADMIN') || !isset($_PBENV['PHP_SELF']) || !preg_match('/[\/\\\\]admincp\.php$/i', $_PBENV['PHP_SELF'])) && exit('Access Denied');

define('PB_PAGE', 'main');

$serverSoft = $_SERVER['SERVER_SOFTWARE'];
$serverOS = PHP_OS;
$PHPVersion = PHP_VERSION;
$MySQLVersion = $DB->db_version();
$globalRegister = checkCfg('register_globals');
if (ini_get('file_uploads'))
{
	$uploadFile = ini_get('upload_max_filesize') . ' / ' .$cp_message['allow'];
}
else
{
	$uploadFile = '<span class="r">' . $cp_message['deny'] . '</span>';
}
$gdVersion = GDVersion();
$gdVersion = $gdVersion ? $gdVersion : '<span class="r">' . $cp_message['deny'] . '</span>';
$domain = $_SERVER['SERVER_NAME'];
$serverTime = gdate($timestamp, 'Y-m-d H:i:s');
$max_execution_time = ini_get('max_execution_time').' seconds';
$php_memory_limit = ini_get('memory_limit');
$current_memory = function_exists('memory_get_usage') ? getRealSize(memory_get_usage()) : $cp_message['unkonw'];

$onlines = array();
$query = $DB->db_query("SELECT username,lastactivity,logip,super,logdate FROM {$db_prefix}adminonlines");
while ($rs = $DB->fetch_all($query))
{
	$rs['super'] = $rs['super'] ? $cp_message['yes'] : '<span style="color:#f00">'.$cp_message['not'].'</span>';
	$rs['lastactivity'] = gdate($rs['lastactivity']);
	$rs['logdate'] = gdate($rs['logdate']);
	$onlines[] = $rs;
}

function checkCfg($str)
{
	global $cp_message;
	$fun = function_exists('get_cfg_var') ? 'get_cfg_var' : 'ini_get';
	$param = $fun($str);
	switch ($param)
	{
		case 1 :
			return $cp_message['open'];
			break;
		case 0 :
			return $cp_message['close'];
			break;
		default :
			return $param;
			break;
	}
}
function GDVersion()
{
	if (function_exists('gd_info'))
	{
		$gd = gd_info();
	}
	return $gd['GD Version'] ? $gd['GD Version'] : FALSE;	
}

?>
