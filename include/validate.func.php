<?php
/**
 * @version $Id: validate.func.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

$words_banned = $words_replace = $words_links = array();

@include_once PBDIGG_CROOT.'cache_words.php';

/**
 * Email 地址
 */
function isEMAIL($value)
{
    return preg_match('~^[a-z0-9]+([._\-\+]*[a-z0-9]+)*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+\.)+[a-z0-9]+$~i', $value);
}

/**
 * 时间日期
 */
function isDateTime($value)
{
    return preg_match('~^[yY][_-]?[mn][_-]?[dj]$~i', $value);
}

/**
 * 分页号
 */
function isPage($page)
{
	return !empty($page) && preg_match('~^\d+$~', $page);
}

/**
 * 域名
 */
function isDOMAIN($value)
{
    return preg_match('~^[\da-z][-a-z0-9\.]*[\da-z]$~is', $value);
}

/**
 * http地址校验
 * @param string $url 地址
 * @return boolean
 */

function isUrl($url)
{
	return preg_match('~^https?:\/\/(?:[-_a-z0-9\.\~!\$&\'\(\)\*\+,;=:@\|/]+|%[\dA-F]{2})+$~i', $url);
}

/**
 * 颜色
 */
function isColorCode($value)
{
	return preg_match('~^#[0-9a-f]{6}$~i', $value);
}

/**
 * 整数
 */
function isINT($value)
{
    return strlen(intval($value)) == strlen($value) && is_numeric($value);
}

/**
 * HASH
 */
function isHash($value)
{
	return preg_match('~^[0-9a-z]{10}$~i', $value);
}

function isImg($imgpath)
{
	return (strpos($imgpath, '..') !== FALSE || !file_exists($imgpath) || !in_array(Fext($imgpath), array('jpg','jpeg','bmp','gif','png')) || (function_exists('getimagesize') && !@getimagesize($imgpath))) ? false : true;
}

function isTag($tag)
{
	return preg_match('~^[-_\x7f-\xff\w\s]+$~i', $tag) && strlen($tag) <= 30;
}

function checkCateName($value)
{
	$len = strlen($value);
	return $len < 1 || $len > 50 ? 0 : 1;
}

function checkTag($tag)
{
	$tag = array_filter(array_unique(explode(',', str_replace("\xa3\xac", ',', HConvert(stripslashes($tag))))));
	if (count($tag) > 5)
	{
		showMsg('validate_tag_num_limit');
	}
	if ($tag)
	{
		foreach ($tag as $k => $v)
		{
			if (!$v || strlen($v) > 30)
			{
				unset($tag[$k]);
				continue;
			}
			isBadWords($v, true);
		}
	}
	return addslashes(implode(',',$tag));
}

function checkQQ($qq)
{
	return preg_match('~^[1-9][0-9]{4,8}$~i', $qq);
}

function checkMSN($msn)
{
	return preg_match('~^[_\.0-9a-z-]+@[\w\.]*?$~i', $msn);
}

/**
 * 检查URL链接
 */
function checkURL($url)
{
	global $words_links;
	!preg_match('~^(https?|ftp|gopher|news|telnet|mms|rtsp):\/\/~i', $url) && showMsg('validate_illegal_url');
	if ($words_links && ($urldata = parse_url($url)))
	{
		$host = $urldata['host'];
		foreach ($words_links as $l)
		{
			if (strpos($host, $l) !== FALSE)
			{
				showMsg('validate_banlinks_exist');
			}
		}
	}
	return TRUE;
}

/**
 * 检查头像链接
 */
function checkAvatar($url)
{
	return preg_match('~^https?:\/\/[^\s]+?\.(gif|jpeg|png|jpg)$~i', $url);
}

/**
 * 检查来自信息
 */
function checkLocation($location)
{
	if (strlen($location) > 30) showMsg('validate_location_length_limit');
	return isBadWords($location, true);
}

/**
 * 检查短信息
 */
function checkMsg($msg, $type = 'title')
{
	if (strlen($msg) > ($type == 'title' ? 80 : 1000)) showMsg('validate_msg_subject_limit');
	return isBadWords($msg);
}

/**
 * 检查标题
 */
function checkTitle($title)
{
	global $titlelenmin, $titlelenmax;
	list ($titlelenmin, $titlelenmax) = explode("\t", $GLOBALS['pb_titlelen']);
	
	if (strlen($title) > $titlelenmax || strlen($title) < $titlelenmin)
	{
		showMsg('validate_title_lengtherror');
	}
	return isBadWords($title, true);
}

/**
 * 检查主题内容
 */
function checkContent($content)
{
	global $contentlenmin, $contentlenmax;
	list ($contentlenmin, $contentlenmax) = explode("\t", $GLOBALS['pb_contentlen']);

	if (strlen($content) > $contentlenmax || strlen($content) < $contentlenmin)
	{
		showMsg('validate_content_lengtherror');
	}
	return isBadWords($content);
}

/**
 * 检查评论
 * 
 * @param String $comment 评论内容
 */
function checkComment($comment)
{
	global $commentlenmin, $commentlenmax, $words_banned, $words_replace;;
	list ($commentlenmin, $commentlenmax) = explode("\t", $GLOBALS['pb_commentlen']);

	if (strlen($comment) < $commentlenmin || strlen($comment) > $commentlenmax)
	{
		return showMsg('validate_comment_lengtherror');
	}
	$words_banned = array_merge($words_banned, $words_replace);
	foreach ($words_banned as $value)
	{
		if (strpos($comment, $value) !== FALSE)
		{
			return showMsg('validate_banwords_exist');
		}
	}
}

function isBadWords($content, $force = false)
{
	global $words_banned, $words_replace;
	$wordsbanned = $force ? array_merge($words_banned, $words_replace) : $words_banned;
	foreach ($wordsbanned as $value)
	{
		if (strpos($content, $value) !== FALSE)
		{
			showMsg('validate_banwords_exist');
		}
	}
	if (!$force)
	{
		foreach ($words_replace as $value)
		{
			if (strpos($content, $value) !== FALSE)
			{
				$content = str_replace($value, str_pad('*', pstrlen($value)), $content);
			}
		}
	}
	return $content;
}

function gdEnable()
{
	static $gdenable;
	if (is_bool($gdenable)) return $gdenable;
	$m = array();
	$gdinfo = gd_info();
	preg_match('~([0-9\.]+?)\s~', $gdinfo['GD Version'], $m);
	$gdenable = ($m[1] >= '2.0.28' && function_exists('imagecreatetruecolor')) ? TRUE : FALSE;
	return $gdenable;
}

?>
