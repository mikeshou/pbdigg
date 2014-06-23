<?php
/**
 * @version $Id: attachments.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_PAGE', 'attachments');
require_once './include/common.inc.php';

$aid = (int)$aid;
!$aid && showMsg('illegal_request');

if ($pb_attoutlink)
{
	$url = parse_url($_PBENV['PB_URL']);
	$host = $url['host'];
	if ($host && !preg_match('~[\d\.]{7,15}~i', $host))
	{
		$domain = 'com,net,org,gov,info,mobi,la,biz,jp,cn,tv,cc,nu,us,cn,ac,bj,sh,tj,cq,he,sx,nm,ln,jl,hl,js,zj,ah,fj,jx,sd,ha,hb,hn,gd,gx,hi,sc,gz,yn,xz,sn,gs,qh,nx,xj,tw,hk,mo';
		$realurl = array();
		$urlparse = explode('.', $host);
		$paramindex = count($urlparse) - 1;
		if ($paramindex)
		{
			while ($paramindex > 0)
			{
				$realurl[] = $urlparse[$paramindex];
				if (strpos($domain, $urlparse[$paramindex]) === FALSE)
				{
					break;
				}
				$paramindex--;
			}
		}
		else
		{
			$realurl = $urlparse;
		}
		$host = implode('.', array_reverse($realurl));
	}
	isset($_SERVER['HTTP_REFERER']) && strpos(strtolower($_SERVER['HTTP_REFERER']), $host) === FALSE && attachmentError('deny');
}

$attdata = $DB->fetch_one("SELECT * FROM {$db_prefix}attachments WHERE aid = '$aid'");

if ($attdata)
{
	$DB->db_exec("UPDATE {$db_prefix}attachments SET downloads = downloads + 1 WHERE aid = '$aid'");
	$attachmentpath = PBDIGG_ATTACHMENT.'/'.$attdata['filepath'];

	if (file_exists($attachmentpath) && is_readable($attachmentpath))
	{
		set_time_limit(300);
		ob_end_clean();
		$disposition = isImg($attachmentpath) ? 'inline' : 'attachment';
		$db_charset == 'utf-8' && $attdata['filename'] = convert_encoding('utf-8', 'gbk', $attdata['filename']);
		strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE && $attdata['filename'] = urlencode($attdata['filename']);
		header('Cache-control: max-age=31536000');
		header('Expires: '.gmdate('D, d M Y H:i:s', $timestamp + 31536000).' GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', $attdata['uploaddate']).' GMT');
		header('Content-type: '.$attdata['filetype']);
		header('Content-Encoding: none');
		header('Content-Disposition: '.$disposition.'; filename='.$attdata['filename']);
		header('Content-Length: '.filesize($attachmentpath));
		header('Content-Transfer-Encoding: binary');
		@readfile($attachmentpath);
		exit;
	}
	else
	{
		attachmentError('noexist');
	}
}
else
{
	showMsg('attachment_norecord');
}

function attachmentError($type)
{
	$type = in_array($type, array('deny', 'noexist')) ? $type : 'deny';
	header('Content-type: image/png');
	header('Content-Length: ' . strlen(PBDIGG_ROOT.'images/common/attachment_'.$type.'.png'));
	@readfile(PBDIGG_ROOT.'images/common/attachment_'.$type.'.png');
	exit;
}

function isImg($imgpath)
{
	return (strpos($imgpath, '..') !== FALSE || !file_exists($imgpath) || !in_array(Fext($imgpath), array('jpg','jpeg','bmp','gif','png')) || (function_exists('getimagesize') && !@getimagesize($imgpath))) ? false : true;
}

?>
