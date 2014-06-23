<?php
/**
 * @version $Id: register.lang.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

$register_message = array(
	'register_title' => '会员注册',
	'register_already' => '您已经是注册会员，请不要重复注册！',
	'register_flood_ctrl' => '同一IP在 {$reg_allowsameip}小时 内只能注册 1 次',
	'register_privacy_needed' => '您必须同意我们的隐私策略才能完成注册！',
	'register_success' => '感谢您注册{$pb_sitename}',
	'register_email_active' => '激活邮件已经成功发送到您的注册邮箱<br />请点击确认信中的链接地址，完成注册！',
	'register_welcome_subject' => "感谢您注册{$GLOBALS['pb_sitename']}",	
	'register_active_subject' => "您在{$GLOBALS['pb_sitename']}的激活码邮件",
	'register_active_body' => "<pre>\r\n{!--username--}，您好！\r\n\r\n欢迎您加入{$GLOBALS['pb_sitename']}。\r\n\r\n这是一封来自{$GLOBALS['pb_sitename']}的注册确认信，请访问下面的链接地址，激活账号完成注册：\r\n\r\n{$GLOBALS['_PBENV']['PB_URL']}activate.php?code={!--activecode--}\r\n\r\n(这是一封自动产生的email，请勿回复。)\r\n\r\n----------------------------------------------------------------------\r\n本网站采用 PBDigg 架设，欢迎访问: http://www.pbdigg.com\r\n</pre>",
);

?>
