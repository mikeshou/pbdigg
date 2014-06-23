<?php
/**
 * @version $Id: ubb.func.php v2.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

!defined('IN_PBDIGG') && exit('Access Denied!');

global $_emote;
$_emote = array();
@include_once PBDIGG_CROOT.'cache_emotion.php';

function signUBB($str)
{
	$ubbtype = explode("\t", $GLOBALS['pb_signubbtype']);
	$search = $replace = array();
	if (in_array('b', $ubbtype))
	{
		$search[] = '[b]';
		$search[] = '[/b]';
		$replace[] = '<strong>';
		$replace[] = '</strong>';
	}
	if (in_array('i', $ubbtype))
	{
		$search[] = '[i]';
		$search[] = '[/i]';
		$replace[] = '<em>';
		$replace[] = '</em>';
	}
	if (in_array('u', $ubbtype))
	{
		$search[] = '[u]';
		$search[] = '[/u]';
		$replace[] = '<u>';
		$replace[] = '</u>';
	}
	$str = str_replace($search, $replace, $str);

	$reg_search = array('~\[align=(left|center|right|justify)\](.*?)\[/align]~is');
	$reg_replace = array('<p style="text-align:\\1">\\2</p>');
	if (in_array('font', $ubbtype))
	{
		$reg_search[] = '~\[font=([^\[\<]+?)\](.*?)\[/font\]~is';
		$reg_replace[] = '<span style="font-family:\\1">\\2</span>';
	}
	if (in_array('size', $ubbtype))
	{
		$reg_search[] = '~\[size=(\d+(\.\d+)?(px|em|ex|pt|pc|in|mm|cm|%|)?)\](.*?)\[/size\]~ies';
		$reg_replace[] = "fontsize('\\1', '\\4')";
	}
	if (in_array('color', $ubbtype))
	{
		$reg_search[] = '~\[color=([^\[\<]+?)\](.*?)\[/color\]~is';
		$reg_replace[] = '<span style="color:\\1">\\2</span>';
	}
	$str = preg_replace($reg_search, $reg_replace, $str);
	//link
	if ((strpos($str, '[url=') !== FALSE) && (strpos($str, '[/url]') !== FALSE) && in_array('url', $ubbtype))
	{
		$str = preg_replace('~\[url=(https?|ftp|gopher|news|telnet|rtsp|mms|callto|ed2k){1}://([^\["\']+?)\](.+?)\[/url\]~is', '<a href="\\1://\\2" target="_blank">\\3</a>', $str);
	}
	//email
	if ((strpos($str, '[email=') !== FALSE) && (strpos($str, '[/email]') !== FALSE) && in_array('email', $ubbtype))
	{
		$str = preg_replace('~\[email=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\](.+?)\[/email\]~is', '<a href="mailto:\\1@\\2">\\3</a>', $str);
	}
	//imgage
	if ((strpos($str, '[img]') !== FALSE) && (strpos($str, '[/img]') !== FALSE) && in_array('img', $ubbtype))
	{
		$str = preg_replace('~\[img\]\s*([^\[\<\r\n]+?)\s*\[/img\]~ies', "imgCode('\\1')", $str);
	}
	//quote
	if ((strpos($str, '[quote') !== FALSE) && (strpos($str, '[/quote]') !== FALSE) && in_array('quote', $ubbtype))
	{
		$str = preg_replace('~\[quote(=(?:[^]]*))?\]\s*(.+?)\s*\[/quote\]~eis', "qouteCode('\\2')", $str);
	}

	return $str;
}

/**
 * 表情
 */
function emCode($id)
{
	global $_emote, $pb_sitedir;
	return (isset($_emote[$id]) ? ('<img src="'.$pb_sitedir.'images/emoticons/'.$_emote[$id][0].'" alt="'.$_emote[$id][1].'" class="emote" />') : '');
}

/**
 * 文字大小
 */
function fontsize($size, $content)
{
	return  is_numeric($size) ? '<font size="'.$size.'">'.str_replace('\\"', '"', $content).'</font>' : '<span style="font-size:'.$size.'">'.str_replace('\\"', '"', $content).'</span>';
}

/**
 * 图片自动显示
 */
function imgCode($var)
{
	global $pb_signimgsize, $_PBENV;
	list($pb_signimgh, $pb_signimgw) = explode("\t", $pb_signimgsize);
	$onload = '';
	strtolower(substr($var, 0, 7)) != 'http://' && $var = $_PBENV['PB_URL'].$var;
	if ($pb_signimgh || $pb_signimgw)
	{
		$onload = ' onload="';
		$pb_signimgw && $onload .= "if(this.width>'".$pb_signimgw."')this.width='".$pb_signimgw."';";
		$pb_signimgh && $onload .= "if(this.height>'".$pb_signimgh."')this.height='".$pb_signimgh."';";
		$onload .= '"';
	}
	return "<a href=\"$var\" target=\"_blank\"><img src=\"$var\" alt=\"\" $onload /></a>";;
}

/**
 * 引用
 */
function qouteCode($author, $content)
{
	return '<div class="quote"><div class="quote-title">'.($author ? (getSingleLang('common', 'ubb_quote').': '.HConvert($author)) : '').' </div><blockquote>' . HConvert(str_replace('\\"', '"', $content)) . '</blockquote></div>';
}

/**
 * 内容UBB代码转换
 */
function conentUBB($content, $type = 't', $test = false)
{
	$ubbtype = explode("\t", $GLOBALS['pb_'.$type.'ubbtype']);
	//media
	if ((strpos($content, '[media') !== FALSE) && (strpos($content, '[/media]') !== FALSE) && (in_array('media', $ubbtype) || $test))
	{
		$content = preg_replace('~\[media(=(\d*?),(\d*?))?\]\s*([^\[\<\r\n]+?)\s*\[/media\]~eis', "mediaCode('\\2', '\\3', '\\4')", $content);
	}
	//flash
	if ((strpos($content, '[flash') !== FALSE) && (strpos($content, '[/flash]') !== FALSE) && (in_array('flash', $ubbtype) || $test))
	{
		$content = preg_replace('~\[flash(=(\d*?),(\d*?))?\]\s*([^\[\<\r\n]+?)\s*\[/flash\]~eis', "flashCode('\\2', '\\3', '\\4')", $content);
	}
	//quote
	if ((strpos($content, '[quote') !== FALSE) && (strpos($content, '[/quote]') !== FALSE))
	{
		$content = preg_replace('~\[quote(?:=([^]]*))?\]\s*(.+?)\s*\[/quote\]~eis', "qouteCode('\\1','\\2')", $content);
	}
	//emote
	if (strpos($content, '[em') !== FALSE)
	{
		$content = preg_replace('~\[em:(\d+)\]~ies', "emCode('\\1')", $content);
	}
	return $content;
}

/**
 * 视频媒体
 */
function mediaCode($height, $width, $mediaurl)
{
	global $pb_mplayersize, $pb_mautoplay;
	list($pb_mplayerh, $pb_mplayerw) = explode("\t", $pb_mplayersize);
	$exticon = strtolower(Fext($mediaurl));
	$pb_mplayerh = ($height > $pb_mplayerh || !$height) ? $pb_mplayerh : $height;
	$pb_mplayerw = ($width > $pb_mplayerw || !$width) ? $pb_mplayerh : $width;
	if (preg_match('~^(wav|asf|asx|vqf|mpg|mpeg|avi|wmv)$~i', $exticon))
	{
		return '<div><object id="MediaPlayer" width="'.$pb_mplayerw.'" height="'.$pb_mplayerh.'" classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,4,7,1112" align="baseline" border="0" standby="Loading Microsoft Windows Media Player components..." type="application/x-oleobject"><param name="URL" value="'.$mediaurl.'"><param name="autoStart" value="'.$pb_mautoplay.'"><param name="invokeURLs" value="false"><param name="playCount" value="100"><param name="defaultFrame" value="datawindow"><embed src="'.$mediaurl.'" align="baseline" border="0" width="'.$pb_mplayerw.'" height="'.$pb_mplayerh.'"	type="application/x-mplayer2" pluginspage="" name="MediaPlayer" showcontrols="1" showpositioncontrols="0" showaudiocontrols="1" showtracker="1" showdisplay="0"	showstatusbar="1" autosize="0" showgotobar="0" showcaptioning="0" autostart="'.$pb_mautoplay.'" autorewind="0" animationatstart="0" transparentatstart="0" allowscan="1" enablecontextmenu="1" clicktoplay="0" defaultframe="datawindow" invokeurls="0"></embed></object></div>';
	}
	elseif (preg_match('~^(mid|mp3|m3u|wma)$~i', $exticon))
	{
		return '<div><object id="MediaPlayer1" width="350" height="68" classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,4,7,1112" align="baseline" border="0" standby="Loading Microsoft Windows Media Player components..." type="application/x-oleobject"> <param name="URL" value="'.$mediaurl.'"> <param name="autoStart" value="'.($pb_mautoplay ? 'true' : 'false').'"> <param name="invokeURLs" value="false"> <param name="playCount" value="100"> <param name="defaultFrame" value="datawindow"> <embed src="'.$mediaurl.'" align="baseline" border="0" width="350" height="68" type="application/x-mplayer2" pluginspage="" name="MediaPlayer1" showcontrols="1" showpositioncontrols="0" showaudiocontrols="1" showtracker="1" showdisplay="0" showstatusbar="1" autosize="0" showgotobar="0" showcaptioning="0" autostart="'.$pb_mautoplay.'" autorewind="0" animationatstart="0" transparentatstart="0" allowscan="1" enablecontextmenu="1" clicktoplay="0" defaultframe="datawindow" invokeurls="0"></embed></object></div>';
	}
	elseif ('ra' == $exticon)
	{
		return '<embed id="RealPlayer1" autogotourl=false type="audio/x-pn-realaudio-plugin" src="'.$mediaurl.'" controls="ControlPanel,StatusBar" width=350 height=68 border=0 autostart='.($pb_mautoplay ? 'true' : 'false').' loop=true></embed>';
	}
	elseif (preg_match('~^(rmvb|rm|rv)$~i', $exticon))
	{
		return '<embed id="RealPlayer" autogotourl="false" type="audio/x-pn-realaudio-plugin" src="'.$mediaurl.'" controls="ControlPanel,StatusBar" width="'.$pb_mplayerw.'" height="'.$pb_mplayerh.'" border="0" autostart="'.($pb_mautoplay ? 'true' : 'false').'" loop="true"></embed>';
	}
	return $mediaurl;
}
/**
 * Flash播放
 */
function flashCode($height, $width, $flashurl)
{
	global $pb_mplayersize, $pb_fautoplay, $_PBENV;
	list($pb_mplayerh, $pb_mplayerw) = explode("\t", $pb_mplayersize);
	$pb_mplayerh = ($height > $pb_mplayerh || !$height) ? $pb_mplayerh : $height;
	$pb_mplayerw = ($width > $pb_mplayerw || !$width) ? $pb_mplayerh : $width;

	if (($parseurl = parse_url($flashurl)) && (strpos($parseurl['host'], 'youtube') !== FALSE) && preg_match('~^http://([^/]+)/watch\?v=([a-z0-9_]+).*~is', $flashurl, $m))
	{
		$flashurl = 'http://'.$m[1].'/watch?v='.$m[2].'&fs=1';
	}
	if ($pb_fautoplay)
	{
		if (preg_match('~^http://www\.tudou\.com/v/[-a-z0-9_]+$~i', $flashurl))
		{
			$param = substr($flashurl, strrpos($flashurl, '/') + 1);
			return '<object width="'.$pb_mplayerw.'" height="'.$pb_mplayerh.'"><param name="movie" value="'.$param.'"></param><param name="allowScriptAccess" value="always"></param><param name="wmode" value="transparent"></param><embed src="'.$flashurl.'" type="application/x-shockwave-flash" width="'.$pb_mplayerw.'" height="'.$pb_mplayerh.'" allowFullScreen="true" wmode="transparent" allowScriptAccess="always"></embed></object>';	
		}
		return '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="'.$pb_mplayerw.'" height="'.$pb_mplayerh.'"><param name="movie" value="'.$flashurl.'" /><param name="quality" value="high" /><embed src="'.$flashurl.'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="'.$pb_mplayerw.'" height="'.$pb_mplayerh.'"></embed></object>';
	}
	else
	{
		return '<img src="'.$_PBENV['PB_URL'].'images/file/flash.gif" border="0" class="attachicon" alt="flash" />&nbsp;<a href="'.$flashurl.'" target="_blank">'.$flashurl.'</a><br />';		
	}
}

?>
