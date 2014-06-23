<?php
/**
 * @version $Id: getpw.lang.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

$getpw_message = array(
	'getpw_title' => '密码取回',
	'getpw_closed' => '管理员关闭了密码取回功能，请返回。',
	'getpw_account_notmatch' => '用户名和 Email 地址不匹配，请返回修改。',
	'getpw_account_invalid' => '管理员不能使用取回密码功能，请返回。',
	'getpw_time_limit' => '您取回密码次数过于频繁，请在3分钟后再试。',
	'getpw_email_subject' => "取回您在{$GLOBALS['pb_sitename']}的密码",
	'getpw_email_body' => "<pre>亲爱的{!--username--}：\r\n\r\n您提交了取回密码的申请，您可以通过访问下面的链接地址进行密码修改。\r\n\r\n{$GLOBALS['_PBENV']['PB_URL']}getpw.php?action=get&confirm={!--confirmcode--}\r\n\r\n(这是一封自动产生的email，请勿回复。)\r\n----------------------------------------------------------------------\r\n本网站采用 PBDigg 架设，欢迎访问: http://www.pbdigg.com\r\n</pre>",
	'getpw_email_success' => '取回密码的方法发送到您的信箱中，请在24小时内修改您的密码！',
	'getpw_succeed' => '密码修改成功，请重新登录。',
	'getpw_confirm_illegal' => '密码重置安全验证校验失败，无法取回密码。',
	'getpw_user_nonexistence' => '用户不存在，无法取回密码。',
);

?>