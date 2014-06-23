<?php
/**
 * @version $Id: activate.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_PAGE', 'activate');
require_once './include/common.inc.php';

$activate_message = loadLang('activate');
$common_message += $activate_message;

!$code && showMsg('illegal_request');

$code = explode('|', PDecode($code, $pb_sitehash));
$uid = (int)$code[0];
$regtime = (int)$code[1];
!$DB->fetch_first("SELECT COUNT(*) num FROM {$db_prefix}members WHERE uid = '$uid' AND regdate = '$regtime' AND groupid = 6") && showMsg('activate_invalid');

$DB->db_exec("UPDATE {$db_prefix}members SET groupid = -1 WHERE uid = '$uid' AND groupid = 6 LIMIT 1");

redirect('activate_succeed', $_PBENV['PB_URL']);

?>
