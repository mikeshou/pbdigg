<?php
/**
 * @version $Id: admincp.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

//error_reporting(E_ALL);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

function_exists('date_default_timezone_set') && date_default_timezone_set('UTC');

$loadTime = explode(' ', microtime());
$loadTime = $loadTime[0] + $loadTime[1];
$timestamp = time();

$_PBENV = $option_message = $cp_message = $_siteFounder = array();
$pb_k = $b = $h = '';

$_PBENV['PHP_SELF'] = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
$_PBENV['REQUEST_URI'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_PBENV['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
$_PBENV['REAL_PATH'] = dirname(__FILE__);
$_PBENV['HTTP_HOST'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('HTTP_HOST');
$_DS = DIRECTORY_SEPARATOR;
$h = rawurlencode($_PBENV['HTTP_HOST']);

define('IN_PBDIGG', TRUE);
define('IN_ADMIN', TRUE);
define('PBDIGG_ROOT', str_replace('\\', '/', substr($_PBENV['REAL_PATH'], 0, strrpos($_PBENV['REAL_PATH'], $_DS) + 1)));
define('PBDIGG_CP', str_replace('\\', '/', $_PBENV['REAL_PATH'].'/'));
define('PBDIGG_CROOT', PBDIGG_ROOT.'data/cache/');

require_once PBDIGG_ROOT.'include/version.inc.php';
require_once PBDIGG_ROOT.'include/admin.func.php';
require_once PBDIGG_ROOT.'include/global.func.php';

if (!get_magic_quotes_gpc())
{
	addS($_GET);
	addS($_POST);
	addS($_COOKIE);
}

foreach(array('_GET','_POST') as $_gp)
{
	foreach($$_gp as $_k => $_v)
	{
		($_k{0} == '_' || $_k == 'GLOBALS') && exit;
		$$_k = $_v;
	}
}

$tpl_dir = 'admin';

if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
{
	$currentIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
elseif (isset($_SERVER['HTTP_CLIENT_IP']))
{
	$currentIP = $_SERVER['HTTP_CLIENT_IP'];
}
else
{
	$currentIP = $_SERVER['REMOTE_ADDR'];
}
$_PBENV['PB_IP'] = preg_match('~[\d\.]{7,15}~', $currentIP, $match) ? $match[0] : 'unknow';

require_once PBDIGG_ROOT.'data/cache/cache_config.php';
$rewrite_ext = array('html','htm','shtml','php','asp','jsp','cgi');
$pb_rewriteext = $rewrite_ext[$pb_rewriteext];
$pb_cpovertime = $pb_cpovertime >= 60 ? (int)$pb_cpovertime : 900;

obStart();

if (!$pb_siteurl)
{
	//获取当前pb访问地址
	$sHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? (int)$_SERVER['SERVER_NAME'] : (int)getenv('SERVER_NAME'));
	$sPort = isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : (int)getenv('SERVER_PORT');
	$sSecure = (isset($_SERVER['HTTPS']) || $sPort == 433) ? 1 : 0;
	$sDir = trim(dirname($_PBENV['PHP_SELF']));
	$sDir = substr($sDir, 0, strrpos($sDir, '/') + 1);
	$pb_siteurl = 'http'.(isset($_SERVER['HTTPS']) || $sPort == 433 ? 's' : '').'://'.preg_replace('~[\\\\/]{2,}~i', '/', $sHost.($sPort && (!$sSecure && $sPort != 80 || $sSecure && $sPort != 433) && strpos($sHost, ':') === FALSE ? ':'.$sPort : '').$sDir);
}
$_PBENV['PB_URL'] = HConvert($pb_siteurl);

//$_attdir = (!$pb_attdir && strpos($pb_attdir, '..') === FALSE) ? $pb_attdir : 'attachments';
$_attdir = 'attachments';
define('PBDIGG_ATTACHMENT', PBDIGG_ROOT.$_attdir.'/');

require_once PBDIGG_ROOT.'data/sql.inc.php';

header('Content-Type: text/html; charset='.$db_charset);

require_once PBDIGG_ROOT.'include/'.$pb_datatype.'.class.php';
$DB = new MySQL($db_host, $db_username, $db_password, $db_name, $db_pconnect);
unset($_ENV, $_REQUEST, $HTTP_ENV_VARS, $HTTP_POST_VARS, $HTTP_GET_VARS, $HTTP_POST_FILES, $HTTP_COOKIE_VARS, $db_password);
empty($_siteFounder) && exit('Access Denied!');

require_once PBDIGG_ROOT.'include/Cache.class.php';
$Cache = new Cache();

require_once PBDIGG_ROOT.'include/ptemplate.func.php';
require_once PBDIGG_ROOT.'include/validate.func.php';
$cp_message = loadLang('cp');
$option_message = loadLang('option');

if ($pb_ucenable)
{
	require_once PBDIGG_ROOT.'include/uc.inc.php';
	require_once PBDIGG_ROOT.'include/uc.class.php';
}

file_exists(PBDIGG_ROOT . 'install/install.php') && !file_exists(PBDIGG_ROOT . 'install/install.lock') && exit('Install File Exists!');

$editorjs = $menu = $log_description = $_categories = $_grouplevel = $checkSubmit = '';

require_once PBDIGG_CROOT.'cache_categories.php';
require_once PBDIGG_CROOT.'cache_grouplevel.php';
require_once PBDIGG_CP.'menu.inc.php';

!accessIPControl($_PBENV['PB_IP']) && showMsg('admin_accessip_denied', 'admincp.php');

if ($action == 'logout')
{
	sCookie('pb_adminsid', '', -1);
	$DB->db_exec("DELETE FROM {$db_prefix}adminonlines WHERE sid = '$pb_adminsid' OR ($timestamp - lastactivity > 600)", 0);
	redirect('admin_logout_success', 'admincp.php');
}

if ($action == 'login')
{
	require_once PBDIGG_ROOT.'include/adminlog.class.php';
	$adminLog = new adminLog;
	$adminLog->adminLogAnalyse();
	if ($pb_adminsafecode)
	{
		$_adminsafecode = NULL;
		require_once PBDIGG_ROOT.'data/safe.inc.php';
		if (empty($_adminsafecode) || ($adminsafecode != $_adminsafecode))
		{
			login();
		}
	}
	if (empty ($admin_user) || empty ($admin_pw))
	{
		login();
	}
	if (($pb_gdcheck & 16) && !ckgdcode($checkcode))
	{
		redirect('admin_checkcode_error', 'admincp.php');
	}

	$username = $password = $customer = $customercode = $pb_adminsid = '';
	$username = $admin_user;
	$password = pbNewPW(md5(stripslashes($admin_pw)));
	$customer = checkLogin((string)$username, $password);
	if ($customer)
	{
		$customercode = PEncode(serialize($customer), $pb_sitehash);
		$pb_adminsid = getAdminHash($customercode);
		$DB->db_exec("DELETE FROM {$db_prefix}adminonlines WHERE sid = '$pb_adminsid' OR ($timestamp - lastactivity > $pb_cpovertime)");
		$DB->db_exec("INSERT INTO {$db_prefix}adminonlines (sid, username, loginfo, logdate, logip, lastactivity, super) VALUES ('$pb_adminsid', '".addslashes($customer['username'])."', '".addslashes($customercode)."', $timestamp, '".$_PBENV['PB_IP']."', $timestamp, ".(defined('SUPERMANAGER') ? 1 : 0).")");
		sCookie('pb_adminsid', PEncode($pb_adminsid."\t".$customer['uid']."\t".pbNewPW($customer['password'])."\t".$timestamp, $pb_sitehash));
		if (defined('SUPERMANAGER'))
		{
			sCookie('pb_auth', PEncode($customer['uid']."\t".pbNewPW($customer['password']), $pb_sitehash));
		}
		adminlog('', 1, 1);
		redirect('admin_login_success', 'admincp.php');
	}
	else
	{
		$logdata = array('name'=>stripslashes($username),'ip'=>$_PBENV['PB_IP'],'time'=>$timestamp,'referer'=>$_SERVER['HTTP_REFERER']);
		$adminLog->writeAdminLog($logdata);
		adminlog(htmlspecialchars("username: $admin_user password: $admin_pw"), 0, 1);
		login();
	}
}

$customer = $pb_adminsid = $pb_adminuid = $pb_adminpw = $pb_admintime = $adminright = $loginSuccess = '';
list($pb_adminsid, $pb_adminuid, $pb_adminpw, $pb_admintime) = explode("\t", PDecode(gCookie('pb_adminsid'), $pb_sitehash));

if ((strlen($pb_adminsid) == 32) && is_numeric($pb_adminuid) && (strlen($pb_adminpw) == 32) && is_numeric($pb_admintime))
{
	$pb_adminsid = addslashes($pb_adminsid);
	$pb_adminuid = (int)$pb_adminuid;
	$pb_adminpw = addslashes($pb_adminpw);
	$pb_admintime = (int)$pb_admintime;
	$rs = $DB->fetch_one("SELECT sid, loginfo, logdate, logip, lastactivity, super FROM {$db_prefix}adminonlines WHERE sid = '$pb_adminsid' AND logip = '".$_PBENV['PB_IP']."' AND (lastactivity >= $timestamp - $pb_cpovertime)");
	if ($rs)
	{
		$customer = unserialize(PDecode($rs['loginfo'], $pb_sitehash));
		pbNewPW($customer['password']) != $pb_adminpw && login();
		$rs['super'] && define('SUPERMANAGER', 1);
		$customer['groupname'] = $_grouplevel[$customer['groupid']]['grouptitle'];
		$adminright = unserialize($customer['adminright']);
		$DB->db_exec("UPDATE {$db_prefix}adminonlines SET lastactivity = '$timestamp' WHERE sid = '$pb_adminsid' AND logip = '".$_PBENV['PB_IP']."' LIMIT 1");
		$DB->db_exec("DELETE FROM {$db_prefix}adminonlines WHERE lastactivity < $timestamp - $pb_cpovertime");
		$loginSuccess = TRUE;
	}
	elseif ($timestamp <= $pb_cpovertime + $pb_admintime)
	{
		$customer = checkLogin((int)$pb_adminuid, $pb_adminpw);
		if ($customer)
		{
			$adminright = unserialize($customer['adminright']);
			$customercode = PEncode(serialize($customer), $pb_sitehash);
			$pb_adminsid = getAdminHash($customercode);
			$DB->db_exec("DELETE FROM {$db_prefix}adminonlines WHERE sid = '$pb_adminsid' OR ($timestamp - lastactivity > $pb_cpovertime)");
			$DB->db_exec("INSERT INTO {$db_prefix}adminonlines (sid, username, loginfo, logdate, logip, lastactivity, super) VALUES ('$pb_adminsid', '".addslashes($customer['username'])."', '".addslashes($customercode)."', $timestamp, '".$_PBENV['PB_IP']."', $timestamp, ".(defined('SUPERMANAGER') ? 1 : 0).")");
			sCookie('pb_adminsid', PEncode($pb_adminsid."\t".$customer['uid']."\t".pbNewPW($customer['password'])."\t".$timestamp, $pb_sitehash));
			$loginSuccess = TRUE;
		}
	}
}

!$loginSuccess && login();

!defined('SUPERMANAGER') && define('SUPERMANAGER', 0);

$basename = $_PBENV['PHP_SELF'].'?action='.$action.'&job='.$job;
$activeJobMenu = isset($_leftmenu[$action]['item'][$job]) ? $_leftmenu[$action]['item'][$job] : (isset($_leftmenu[$action]['func'][$job]) ? $_leftmenu[$action]['func'][$job] : '');

adminlog($log_description);

if (!$action)
{
	$leftmenu = '';

	foreach ($_navmenu as $k => $v)
	{
		$leftmenu .= "'{$k}': {";
		foreach ($v as $vv)
		{
			if (isset($_leftmenu[$vv]) && ($vv == 'quick' || $adminright[$vv]))
			{
				$leftmenu .= "'".$_leftmenu[$vv]['name']."':[";
				foreach ($_leftmenu[$vv]['item'] as $kkk => $vvv)
				{
					$leftmenu .= $vv == 'quick' ? "'$kkk','$vvv'," : "'$vvv','$vv&job=$kkk',";
				}
				$leftmenu = substr($leftmenu, 0, -1)."],";
			}
		}
		$leftmenu = ($leftmenu{strlen($leftmenu) - 1} == ',' ? substr($leftmenu, 0, -1) : $leftmenu)."},";
	}
	$leftmenu && $leftmenu = substr($leftmenu, 0, -1);
	require_once pt_fetch('admincp');
}
elseif (file_exists($action.'.inc.php') && ($action == 'main' || $adminright[$action] && isset($job)))
{
	require_once $action.'.inc.php';
}
else
{
	showMsg('admin_undefined_action', 'admincp.php?action=main');
}

!defined('PB_PAGE') && define('PB_PAGE', 'admincp');

require_once pt_fetch(PB_PAGE);

PBOutPut();

?>
