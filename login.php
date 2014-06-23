<?php
/**
 * @version $Id: login.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_PAGE', 'login');
require_once './include/common.inc.php';

$login_message = loadLang('login');
$common_message += $login_message;

require_once PBDIGG_ROOT.'include/member.class.php';
require_once PBDIGG_ROOT.'include/mail.inc.php';

!$action && $action = 'login';

$forward = isset($forward) ? HConvert($forward) : forward();

$Member = new Member();

switch ($action)
{
	case 'login':
		$pb_passport && $pb_passporttype == 'client' && showMsg('passport_login');
		if (checkPost())
		{
			$Member->Action('in', array($username, $password, $persistent));
			redirect($groupid == 6 ? 'member_active_account' : 'member_login_success', $forward);
		}
		$pb_seotitle = $login_message['login_title'];
		$loginFormURL = $_PBENV['PB_URL'].'login.php?action=login&amp;forward='.rawurlencode($forward).'&amp;verify='.$verifyhash;
		break;

	case 'logout':
		$pb_passport && $pb_passporttype == 'client' && showMsg('passport_logout');
		(!$pb_passport && (!$customer['uid'] || !checkurlHash($verify))) && showMsg('illegal_request');
		$Member->Action('out');
		redirect('member_logout_success', $forward, 20);
		break;
	
	default:
		break;
}

require_once pt_fetch('login');

PBOutPut();

?>
