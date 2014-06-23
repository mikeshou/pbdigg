<?php
/**
 * @version $Id: register.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_PAGE', 'register');
require_once './include/common.inc.php';
require_once PBDIGG_CROOT.'cache_reg.php';
require_once PBDIGG_CROOT.'cache_grouplevel.php';

$register_message = loadLang('register');
$common_message += $register_message;

require_once PBDIGG_ROOT.'include/member.class.php';
require_once PBDIGG_ROOT.'include/validate.func.php';
require_once PBDIGG_ROOT.'include/mail.inc.php';

$pb_passport && $pb_passporttype == 'client' && showMsg('passport_register');

!$forward && $forward = forward();

!$reg_status && showMsg($reg_closereason, $forward);

$customer['groupid'] != '5' && showMsg('register_already', $forward);

$ipcache = '';

if ($reg_allowsameip)
{
	$ipcachefile = PBDIGG_CROOT.'ip_cache.php';
	if (file_exists($ipcachefile) && ($ipcache = PReadFile($ipcachefile)))
	{
		$iplog = explode("\n", rtrim(substr($ipcache, 15)));
		$restore = $ifflood = '';
		foreach ($iplog as $v)
		{
			$lastregtime = substr($v, 0, 10);
			if ($timestamp < ($lastregtime + 3600 * $reg_allowsameip))
			{
				$restore .= $v."\n";
				!$ifflood && strpos($v, $_PBENV['PB_IP']) && $ifflood = TRUE;
			}
		}
		$restore ? PWriteFile($ipcachefile, "<?php exit;?>\n\n".$restore, 'wb') : PDel($ipcachefile);
		$ifflood && showMsg('register_flood_ctrl');
	}
}

if (isPost())
{
//	$registerData = array('username'=>'用户名', 'password'=>'密码', 'email'=>'电子邮件', 'groupid'=>'等级', 'ignore'=>'忽略用户名检查', 'currency'=>'积分', 'exp'=>array());
//	$Member->add($registerData);
//	
	
//	if ($pb_gdcheck['login'] && !ckgdcode($captcha))
//	{
//		showMsg($u_message['checkcode_error'], 'register.php');
//	}
//	if ($reg_invite)
//	{
//		!$invite && showMsg('请输入邀请码', 'register.php');
//		$rt = $DB->db_fetch_one_array("SELECT COUNT(*) num FROM {$db_prefix}invitecode WHERE invitecode = '".$_POST['invite']."' AND (endtime >= {$timestamp} OR endtime = -1) ");
//		if ($rt['num'])
//		{
//			$DB->db_query("DELETE FROM {$db_prefix}invitecode WHERE invitecode = '".$_POST['invite']."' OR (endtime <> -1 AND endtime <= $timestamp)", 0);
//		}
//		else
//		{
//			showMsg('您输入的邀请码不正确，请重新输入。', 'register.php');
//		}
//	}

	($pb_gdcheck & 1) && !ckgdcode($captcha) && showMsg('checkcode_error');
	$pb_regqa && !ckqa($answer) && showMsg('checkqa_error');
	!isset($privacy) && showMsg('register_privacy_needed');

	$reg_groupid = $reg_emailactive ? '6' : '-1';

	$Member = new Member();
	$Member->Action('add', array('username'=>trim($_POST['username']), 'password'=>trim($_POST['password']), 'rpassword' => trim($_POST['rpassword']), 'email'=>trim($_POST['email']), 'groupid'=>$reg_groupid, 'exp'=>array('signature'=>'','ctsig'=>0)));
	redirect(($reg_emailactive ? 'register_email_active' : 'register_success'), $forward);
}
else
{
	$pb_seotitle = $register_message['register_title'];
	$registerFormURL = $_PBENV['PB_URL'].'register.php?forward='.$forward.'&amp;verify='.urlHash('forward='.$forward.'&amp;');
}

require_once pt_fetch('register');

PBOutPut();

?>
