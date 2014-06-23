<?php
/**
 * @version $Id: mail.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

!defined('IN_PBDIGG') && exit('Access Denied!');

require_once PBDIGG_ROOT.'include/phpmailer/class.phpmailer.php';

$mail = new PHPMailer();

if ($pb_mailtype)
{
	$mail->IsSMTP();
	$mail->Host = $pb_smtphost;
	strpos($pb_smtphost, 'smtp.gmail.com') !== FALSE && $mail->SMTPSecure = 'ssl';
	if ($pb_smtpauth)
	{
		$mail->SMTPAuth = TRUE;
	}
	$mail->Username = $pb_smtpuser;
	$mail->Password = $pb_smtppw;
	if ($pb_smtpport)
	{
		$mail->Port = $pb_smtpport;
	}
}
else
{
	$mail->IsMail();
}
$mail->From = $pb_smtpuser;
$mail->FromName = $pb_sitename;
$mail->CharSet = $db_charset;
$mail->Encoding = 'base64';
$mail->IsHTML(TRUE);
$mail->AltBody ='To view the message, please use an HTML compatible email viewer!';
$mail->SetLanguage($pb_lang, PBDIGG_ROOT.'include/phpmailer/language/');

function PMail($sendto, $subject, $body)
{
    global $mail;
	!is_array($sendto) && $sendto = (array)$sendto;
    foreach ($sendto as $k)
    {
    	$mail->AddAddress($k);
    }
	$mail->Subject = $subject;
	$mail->Body = $body;
	return $mail->Send() ? TRUE : $mail->ErrorInfo;
}
?>
