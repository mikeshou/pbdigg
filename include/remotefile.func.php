<?php
/**
 * @version $Id: remotefile.func.php v2.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

!defined('IN_PBDIGG') && exit('Access Denied!');

if($pb_watertype && !isset($GLOBALS['WM']))
{
	require_once PBDIGG_ROOT.'include/Watermark.class.php';
	$GLOBALS['WM'] = new Watermark();
}

function get_remote_files($text)
{
	if (!@ini_get('allow_url_fopen')) return $text;
	$remotefileurls = remote_file_url(stripslashes($text));
    $urls = save_remote_files($remotefileurls);
	return str_replace($urls[0], $urls[1], $text);
}

function remote_file_url($text)
{
	global $_PBENV;
	$pb_domain = preg_match("~^(http:\/\/)?([^\/]+)~i", $_PBENV['PB_URL'], $matches) ? strtolower($matches[2]) : '';
	if (!preg_match_all('~<img.+?src=(?:["|\']?)((?:https?|ftp):\/\/(?:[-_a-z0-9\.\~!$&\'\(\)*+,;=:@|/]+|%[\dA-F]{2})+\.(?:gif|jpg|jpeg|bmp|png))(?:["|\']?)[^>]*?>~i', $text, $matches))
	{
		return array();
	}
	$remotefileurls = array();
	foreach ($matches[1] as $match)
	{
		if (preg_match('~^http:\/\/'.preg_quote($pb_domain,'~').'.*?~i', $match)) continue;
		$remotefileurls[] = $match;
	}
	return array_unique($remotefileurls);
}

function fcopy($url, $file)
{
	$trynum = 0;
	$flag = false;
	while (!$flag && $trynum < 3)
	{
		if (@copy($url, $file))
		{
			$flag = true;
		}
		elseif ($content = @file_get_contents($url))
		{
			PWriteFile($file, $content, 'wb');
			$flag = true;
		}
		elseif ($fp = @fopen($url, 'rb'))
		{
			$content = '';
			while (!feof($fp))
			{
				$content .= fread($fp, 8192);
			}
			fclose($fp);
			PWriteFile($file, $content, 'wb');
			$flag = true;
		}
		$trynum++;
	}
	return $flag;
}

function save_remote_files($urls)
{
	global $WM, $timestamp, $pb_watertype, $_attdir, $pb_sitedir;
	static $stack = array();
	$oldpath = $newpath = array();
	$uploaddir = getUploadDir();
	foreach($urls as $key => $url)
	{
		if (!array_key_exists($url, $stack))
		{
			if(strpos($url, '://') === FALSE) continue;
			srand ((double) microtime() * 1000000);
			$filename = md5($url.rand(100, 999).$timestamp).'.'.Fext($url);
			$newfile = PBDIGG_ATTACHMENT.$uploaddir.'/'.$filename;
			$newurl = $pb_sitedir.$_attdir.'/'.$uploaddir.'/'.$filename;
			if(fcopy($url, $newfile) && file_exists($newfile))
			{
				if (!isImg($newfile))
				{
					PDel($newfile);
					continue;
				}
				$pb_watertype && $WM->setImg($newfile);
				@chmod($newfile, 0644);
				$oldpath[] = $url;
				$newpath[] = $newurl;
				$stack[$url] = $filename;
			}
		}
	}
	return array($oldpath, $newpath);
}
?>