<?php
/**
 * @version $Id: passport.pw.php v2.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

!defined('IN_PBDIGG') && exit('Access Denied!');

(!$userdb || !$verify || !isset($forward)) && showMsg('illegal_request');

md5($action.$userdb.$forward.$pb_passportkey) != $verify && showMsg('passport_check_error');

parse_str(StrCode($userdb, 'DECODE'), $passportMember);

/*
 * phpwind积分参数说明：
 * 
 * $passportMember['rvrc']：威望
 * $passportMember['money']：金钱
 * $passportMember['credit']：贡献
 * $passportMember['currency']：综合积分
 * 
 * 如果您需要将phpwind论坛的威望同步到pbdigg的积分字段，请将下行 $passportMember['currency'] 修改为 $passportMember['money']
 * 同理其他积分参数
 * 
 */
$passportMember['currency'] = (int)$passportMember['currency'];
$passportMember['timestamp'] = (int)$passportMember['time'];
$passportMember['cookie'] = $passportMember['cktime'] == 'F' ? 1 : (int)$passportMember['cktime'];
$passportMember['forward'] = $forward ? (strtolower(substr($forward, 0, 7)) == 'http://' ? $forward : $pb_pclienturl.$forward) : '';

/**
 * PHPWIND加解密函数
 */
function StrCode($string,$action='ENCODE')
{
	$action != 'ENCODE' && $string = base64_decode($string);
	$code = '';
	$key  = substr(md5($_SERVER['HTTP_USER_AGENT'].$GLOBALS['pb_passportkey']),8,18);
	$keylen = strlen($key); $strlen = strlen($string);
	for ($i=0;$i<$strlen;$i++) {
		$k		= $i % $keylen;
		$code  .= $string[$i] ^ $key[$k];
	}
	return ($action!='DECODE' ? base64_encode($code) : $code);
}

?>