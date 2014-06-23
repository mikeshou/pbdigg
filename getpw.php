<?php
/**
 * @version $Id: getpw.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_PAGE', 'getpw');
require_once './include/common.inc.php';

$getpw_message = loadLang('getpw');
$common_message += $getpw_message;

!$pb_getpw && showMsg('getpw_closed');

if ($action == 'send' && checkPostHash($verify))
{
	$rs = $DB->fetch_one("SELECT uid, adminid, password FROM {$db_prefix}members WHERE username = '$username' AND email = '$email'");
	!$rs && showMsg('getpw_account_notmatch');
	in_array($rs['adminid'], array('1', '2')) && showMsg('getpw_account_invalid');
	$timestamp - gCookie('pb_getpw') < 180 && showMsg('getpw_time_limit');
	sCookie('pb_getpw', $timestamp, 3600);
	//send active email
	require_once PBDIGG_ROOT.'include/mail.inc.php';
	$confirmcode = rawurlencode(PEncode($rs['uid'].'|'.$timestamp.'|'.$rs['password'], $pb_sitehash));
	PMail($email, $getpw_message['getpw_email_subject'], str_replace(array('{!--username--}','{!--confirmcode--}'), array($username,$confirmcode), $getpw_message['getpw_email_body']));
	showMsg('getpw_email_success', $_PBENV['PB_URL']);
}
elseif ($action == 'get' && isset($confirm))
{
	$confirmcode = explode('|', PDecode($confirm, $pb_sitehash));
	if ((count($confirmcode) != 3) || !is_numeric($confirmcode[0]) || !is_numeric($confirmcode[1]) && ($timestamp - $confirmcode[1] > 86400) || (strlen($confirmcode[2]) != 32))
	{
		showMsg('getpw_confirm_illegal', 'getpw.php');
	}
	$gpw_uid = (int)$confirmcode[0];
	$gpw_pw = addslashes($confirmcode[2]);
	$rs = $DB->fetch_one("SELECT adminid FROM {$db_prefix}members WHERE uid = '$gpw_uid' AND password = '$gpw_pw'");
	!$rs && showMsg('getpw_user_nonexistence');
	in_array($rs['adminid'], array('1', '2')) && showMsg('getpw_account_invalid');

	if (checkPostHash($verify))
	{
		require_once PBDIGG_ROOT.'include/member.class.php';
		$Member = new Member();
		$Member->Action('password', array($gpw_uid, $password, $rpassword, '', FALSE));
		sCookie('pb_getpw', '', -1);
		redirect('getpw_succeed', 'login.php');
	}
}

$pb_seotitle = $getpw_message['getpw_title'];

require_once pt_fetch('getpw');

PBOutPut();

?>
