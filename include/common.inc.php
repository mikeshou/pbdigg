<?php
/**
 * @version $Id: common.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 * Minimum Requirement: PHP 4.3.3
 */

//error_reporting(E_ALL);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

function_exists('date_default_timezone_set') && @date_default_timezone_set('Etc/GMT+0');

$loadTime = explode(' ', microtime());
$loadTime = $loadTime[0] + $loadTime[1];
$timestamp = time();

define('IN_PBDIGG', TRUE);
define('PBDIGG_ROOT', str_replace('\\', '/', substr((dirname(__FILE__)), 0, -7)));
!defined('PB_PAGE') && define('PB_PAGE', 'PB_PAGE');
define('PBDIGG_CROOT', PBDIGG_ROOT.'data/cache/');
$_attdir = 'attachments';//自定义附件目录，3.0请勿更改
define('PBDIGG_ATTACHMENT', PBDIGG_ROOT.$_attdir.'/');
$_PBENV = array();
$_PBENV['PHP_SELF'] = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
$_PBENV['REQUEST_URI'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_PBENV['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
$_DS = DIRECTORY_SEPARATOR;

require_once PBDIGG_ROOT.'include/version.inc.php';
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

require_once PBDIGG_CROOT.'cache_config.php';

$tpl_dir = $pb_style;//当前模板
$pb_timecorrect && $timestamp += $pb_timecorrect;

if (!$pb_siteurl)
{
	//获取当前pb访问地址
	$sHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? (int)$_SERVER['SERVER_NAME'] : (int)getenv('SERVER_NAME'));
	$sPort = isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : (int)getenv('SERVER_PORT');
	$sPort == '80' && $sPort = '';
	$sSecure = (isset($_SERVER['HTTPS']) || $sPort == 433) ? 1 : 0;
	$sDir = trim(dirname($_PBENV['PHP_SELF']));
	$pb_siteurl = 'http'.(isset($_SERVER['HTTPS']) || $sPort == 433 ? 's' : '').'://'.preg_replace('~[\\\\/]{2,}~i', '/', $sHost.($sPort && (!$sSecure && $sPort != 80 || $sSecure && $sPort != 433) && strpos($sHost, ':') === FALSE ? ':'.$sPort : '').$sDir.'/');
}

$_PBENV['PB_URL'] = HConvert($pb_siteurl);

if (isset($_SERVER['HTTP_CLIENT_IP']))
{
	$currentIP = $_SERVER['HTTP_CLIENT_IP'];
}
elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
{
	$currentIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else
{
	$currentIP = $_SERVER['REMOTE_ADDR'];
}
$_PBENV['PB_IP'] = preg_match('~^[\d\.]{7,15}$~', $currentIP, $match) ? $match[0] : 'unknow';
unset($match);

if ($_DS == '/' && $pb_loadavg)
{
	if((function_exists('sys_getloadavg') && $loadavg = sys_getloadavg()) || ($loadavg = explode(' ', @file_get_contents('/proc/loadavg'))))
	{
		(($loadavg[0] > $pb_loadavg) || (!$_COOKIE && !$_SERVER['HTTP_USER_AGENT'])) && exit('Service Unavailable!');
	}
}

$userAgent = userAgent();
$pb_robots && $userAgent[2] && logRobots($userAgent[1]);

obStart();

//变量初始化
$_categories = $_siteFounder = array();

$logStatus = $ucHeader = $jsmore = $cssmore = $gtype = $grouptitle = $uplower = $uphigher = $allowvisit = $allowsort = $allowread = $allowpost = $allowcomment 
= $allowdigg = $allowbury = $allowbsearch = $allowasearch = $searchmax = $allowreport = $allowhtml = $allowmsg 
= $msgmax = $allowfavors = $favorsmax = $allowavatar = $allowaupload = $uploadmax = $uploadtype = $allowadmincp = $alloweditatc = $allowdelatc = $allowcheckatc = $allowlockatc = $allowmoveatc = $allowcopyatc = $allowtopatc = $allowcommend = $allowshield = $allowedip = $allowtitlestyle = '';

require_once PBDIGG_ROOT.'data/sql.inc.php';

PB_PAGE != 'AJAX' && PB_PAGE != 'WAP' && header('Content-Type: text/html; charset='.$db_charset);

$common_message = loadLang('common');

require_once PBDIGG_ROOT.'include/ptemplate.func.php';

if ($pb_lastvisit = gCookie('pb_lastvisit'))
{
	list($lastVisit, $lastPath) = explode("\t", $pb_lastvisit);
	$pb_refreshtime && ($timestamp - $lastVisit < $pb_refreshtime) && ($lastPath == $_PBENV['REQUEST_URI']) && showMsg('refresh_limit');
}

ipControl($_PBENV['PB_IP']);

require_once PBDIGG_ROOT.'include/MySQL.class.php';
$DB = new MySQL($db_host, $db_username, $db_password, $db_name, $db_pconnect);
unset($db_password);

$customer = array('uid'=>0,'username'=>$common_message['anonymity'],'adminid'=>0,'groupid'=>5,'realgroup'=>0,'currency'=>0,'ucuid'=>0,'avatar'=>'');
//$pb_auth = gCookie('pb_auth');
$pb_auth = gCookie('pb_auth') ? gCookie('pb_auth') : (isset($pb_auth) ? $pb_auth : '');
if ($pb_auth)
{
	list($p_uid, $p_pw) = explode("\t", PDecode($pb_auth, $pb_sitehash));
	if (is_numeric($p_uid) && strlen($p_pw) == 32)
	{
		$customer = $DB->fetch_one("SELECT m.uid, m.username, m.password, m.email, m.adminid, m.groupid, m.publicemail, m.gender, m.regip, m.regdate, m.realgroup, m.postnum, m.commentnum, m.diggnum, m.burynum, m.currency, m.lastip, m.lastvisit, m.lastpost, m.lastcomment, m.lastupload, m.lastsearch, m.uploadnum, m.newmsg, m.friendnum, m.collectionnum, m.visitnum, m.ucuid, m.avatar, mx.qq, mx.msn, mx.site, mx.location, mx.birthday, mx.signature, mx.showsign, mx.ctsig FROM {$db_prefix}members m LEFT JOIN {$db_prefix}memberexp mx USING (uid) WHERE m.uid = ".(int)$p_uid);
		if (!$customer || pbNewPW($customer['password']) != $p_pw)
		{
			sCookie('pb_auth', '', -1);
			unset($customer, $pb_auth);
			showMsg('auth_error');
		}
		$customer['uid'] = $customer['safeuid'] = (int)$customer['uid'];
		$customer['groupid'] = (int)$customer['groupid'];
		$customer['adminid'] = (int)$customer['adminid'];
		$customer['newmsg'] && $customer['newmsg'] = '<span class="newmsg">'.$customer['newmsg'].'</span>';
		$customer['groupid'] == -1 && $customer['groupid'] = (int)$customer['realgroup'];
		$logStatus = 1;
	}
}

if ($pb_ucenable)
{
	require_once PBDIGG_ROOT.'include/uc.inc.php';
	$customer['safeuid'] = (int)$customer['ucuid'];
}

require_once PBDIGG_ROOT.'data/cache/cache_usergroup_'.$customer['groupid'].'.php';

if ($customer['adminid'] && ($customer['adminid'] == 1 || $customer['adminid'] == 2))
{
	require_once PBDIGG_CROOT.'cache_admingroup_'.$customer['adminid'].'.php';
	if (in_array($customer['uid'], $_siteFounder))
	{
		define('SUPERMANAGER', TRUE);
		$alloweditatc = $allowdelatc = $allowcheckatc = $allowlockatc = $allowmoveatc = $allowcopyatc = $allowtopatc = $allowcommend = $allowshield = $allowedip = $allowtitlestyle = 1;	
	}
}

!defined('SUPERMANAGER') && define('SUPERMANAGER', FALSE);

if (!$pb_ifopen && !SUPERMANAGER)
{
	clearcookie();
	showMsg($pb_whyclosed);
}
if (!$allowvisit && PB_PAGE != 'login')
{
	clearcookie();
	showMsg('member_visit_denied', 'login.php');
}

$tid = (int)$tid;
$cid = (int)$cid;
$p = (int)$p;

if ($pb_passport && $pb_passporttype == 'client')
{
	$loginurl = $pb_pclienturl.$pb_pclientlogin;
	$logouturl = $pb_pclienturl.$pb_pclientlogout;
	$regurl = $pb_pclienturl.$pb_pclientregister;
}
else
{
	$loginurl = $_PBENV['PB_URL'].'login.php';
	$logouturl = $_PBENV['PB_URL'].'login.php?action=logout&amp;verify='.urlHash('action=logout');
	$regurl = $_PBENV['PB_URL'].'register.php';
}

$rewrite_ext = array('html','htm','shtml','php','asp','jsp','cgi');
$pb_rewriteext = $rewrite_ext[$pb_rewriteext];
$diggbury = $pb_ifdigg || $pb_ifbury;

$customer['avatar'] = userFace($customer['avatar'], $customer['ucuid']);
$customer['uurl'] = userSpace($customer['uid'], $customer['ucuid']);

//$style= gCookie('style') ? gCookie('style') : (isset($_GET['style']) ? $_GET['style'] : (isset($_POST['style']) ? $_POST['style'] : $pb_style));
$styleid= isset($_GET['styleid']) ? $_GET['styleid'] : (gCookie('styleid') ? gCookie('styleid') : '');
if ($styleid && !preg_replace('~[-_a-z0-9]~i', '', $styleid) && 'admin' != strtolower($styleid) && @is_dir(PBDIGG_ROOT.'templates/'.$styleid))
{
	$tpl_dir = $pb_style = $styleid;
	sCookie('styleid', $styleid, 1);
}

require_once PBDIGG_CROOT.'cache_categories.php';
require_once PBDIGG_ROOT.'include/menu.class.php';
require_once PBDIGG_ROOT.'include/module.class.php';
require_once PBDIGG_ROOT.'include/transfer.class.php';
require_once PBDIGG_ROOT.'include/Cache.class.php';

$menu = new menu();
$module = new module();
$transfer = new transfer();
$Cache = new Cache();

sCookie('pb_lastvisit', $timestamp."\t".$_PBENV['REQUEST_URI']);
$verifyhash = getUserHash($customer['uid']);

//action_do('common');
//filter_do('common');

?>
