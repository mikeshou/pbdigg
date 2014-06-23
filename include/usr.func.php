<?php
/**
 * @version $Id: usr.func.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

################## 用户自定义函数 ######################
/**
 * 幻灯片主题
 * @param string $cid 主题分类，多给分类id之间用半角逗号分隔，留空为全部主题
 * @param int $num 调用数量
 * @param int $width 播放器宽度
 * @param int $height 播放器高度
 * @param int $autoPlayTime 图片切换时间
 * 
 * 用户可以自行修改播放器代码实现各种特效播放
 */
function powerpoint($cid = '', $num = 5, $width = 290, $height = 230, $autoPlayTime = 5)
{
	global $_PBENV, $_attdir, $pb_rewrite, $pb_cachetime, $timestamp, $DB, $db_prefix;

	$filehash = md5($cid.$num.$width.$width.$height.$autoPlayTime);
	$cachefile = PBDIGG_CROOT.'cache_powerpoint_'.$filehash.'.xml';

	echo '<object data="'.$_PBENV['PB_URL'].'js/slideplayer/slideplayer.swf?xml='.$_PBENV['PB_URL'].'data/cache/cache_powerpoint_'.$filehash.'.xml" type="application/x-shockwave-flash" width="'.$width.'" height="'.$height.'" id="powerpoint"><param name="wmode" value="opaque" /><param name="movie" value="'.$_PBENV['PB_URL'].'js/slideplayer/slideplayer.swf?xml='.$_PBENV['PB_URL'].'data/cache/cache_powerpoint_'.$filehash.'.xml" /></object>';

	if (!file_exists($cachefile) || ($timestamp - filemtime($cachefile) >= $pb_cachetime))
	{
		$sql = '';
		if ($cid)
		{
			$cid = explode(',', $cid);
			$tmp_cid = '';
			foreach ($cid as $v)
			{
				$tmp_cid .= isset($_cagetories[$cid]) ? ($tmp_cid ? ',' : '').$v : '';
			}
			$tmp_cid && $sql .= " AND cid IN ($tmp_cid)";
		}
		$num = (int)$num;
		$num <= 0 && $num = 5;
		$query = $DB->db_query("SELECT tid, cid, subject, commendpic, realurl FROM {$db_prefix}threads WHERE commend = 2 AND ifcheck = 1 $sql ORDER BY postdate DESC LIMIT $num");
	
		//播放器代码构造开始
		$content = '<?xml version="1.0" encoding="utf-8"?><data><channel>';
		$channel = '';

		while ($rs = $DB->fetch_all($query))
		{
			$link = $pb_rewrite ? rewriteThread($rs['tid']) : ($_PBENV['PB_URL'].'show.php?tid='.$rs['tid']);
			$image = $_PBENV['PB_URL'].$_attdir.'/commend/'.$rs['commendpic'];
			$channel .= '<item><link>'.$link.'</link><image>'.$image.'</image><title>'.addcslashes($rs['subject'], '\\\"\'').'</title></item>';
		}
		!$channel && $channel = '<item><link>http://fqa.pbdigg.com/views.php?q=powerpoint</link><image>'.$_PBENV['PB_URL'].'images/common/no_powerpoint.gif</image><title>赶紧去主题页里使用头条推送功能</title></item>';
		global $db_charset;
		$db_charset != 'utf-8' && $channel = convert_encoding($db_charset, 'UTF-8', $channel);
		$content .= $channel.'</channel><config><roundCorner>0</roundCorner><autoPlayTime>'.$autoPlayTime.'</autoPlayTime><isHeightQuality>false</isHeightQuality><blendMode>normal</blendMode><transDuration>1</transDuration><windowOpen>_blank</windowOpen><btnSetMargin>auto 5 5 auto</btnSetMargin><btnDistance>20</btnDistance><titleBgColor>0xff6600</titleBgColor><titleTextColor>0xffffff</titleTextColor><titleBgAlpha>.75</titleBgAlpha><titleMoveDuration>1</titleMoveDuration><btnAlpha>.7</btnAlpha><btnTextColor>0xffffff</btnTextColor><btnDefaultColor>0x1B3433</btnDefaultColor><btnHoverColor>0xff9900</btnHoverColor><btnFocusColor>0xff6600</btnFocusColor><changImageMode>click</changImageMode><isShowBtn>true</isShowBtn><isShowTitle>true</isShowTitle><scaleMode>noBorder</scaleMode><transform>blur</transform><isShowAbout>false</isShowAbout></config></data>';

		//播放器代码构造结束
		PWriteFile($cachefile, $content, 'wb');
	}
}
/**
 * 热门标签
 * @param int $num 调用数量
 * 
 * 模板调用方法{#func hottags(调用数量)#}
 */
function hottags($num = 0)
{
	global $timestamp, $pb_cachetime, $Cache, $_hottagstpl;

	$output = '';
	$_hottags = array();
	$cachefile = PBDIGG_CROOT.'cache_hottags.php';
	(!is_int($num) || $num < 0) && $num = 10;
	if (($timestamp - filemtime($cachefile) >= $pb_cachetime) || !file_exists($cachefile))
	{
		$Cache->hottags($num);
	}
	include $cachefile;
	foreach ($_hottags as $v)
	{
		$output .= str_replace(array('{!--tagid--}', '{!--tagname--}', '{!--encodetagname--}', '{!--usenum--}', '{!--color--}'), array($v['tagid'], $v['tagname'], $v['encodetagname'], $v['usenum'], $v['color']), $_hottagstpl['template']);
	}
	echo $output;
}
/**
 * 系统标签
 * @param int $num 调用数量
 * 
 * 模板调用方法{#func hottags(调用数量)#}
 */
function systags($num = 0)
{
	global $timestamp, $pb_cachetime, $Cache, $_systagstpl;

	$output = '';
	$_systags = array();
	$cachefile = PBDIGG_CROOT.'cache_systags.php';
	(!is_int($num) || $num < 0) && $num = 10;
	if (($timestamp - filemtime($cachefile) >= $pb_cachetime) || !file_exists($cachefile))
	{
		$Cache->hottags($num);
	}
	include $cachefile;
	foreach ($_systags as $v)
	{
		$output .= str_replace(array('{!--tagid--}', '{!--tagname--}', '{!--encodetagname--}', '{!--usenum--}', '{!--color--}'), array($v['tagid'], $v['tagname'], $v['encodetagname'], $v['usenum'], $v['color']), $_systagstpl['template']); 
	}
	echo $output;
}
/**
 * 头部菜单
 * 仅限1-2级分类
 * @param int $num 调用数量
 */
function headerMenu($num = 5)
{
	echo $GLOBALS['menu']->headerMenu($num);
}
/**
 * 块状菜单
 * 仅限1-2级分类
 */
function blockMenu()
{
	echo $GLOBALS['menu']->blockMenu();
}
/**
 * 树菜单
 * 无限级分类
 */
function treeMenu($cid = 0)
{
	global $_PBENV;
	$menu = <<<EOT
<style type="text/css">
.treemenu {font-family: sans-serif;margin:0.5em;}
.directory {font-size: 9pt; font-weight: bold;}
.directory h3 {margin: 0px; margin-top: 1em; font-size: 11pt;}
.directory > h3 {margin-top: 0;}
.directory p {margin: 0px; white-space: nowrap;}
.directory div {display: none; margin: 0px;}
.directory img {vertical-align: -30%;}
</style>
<script type="text/javascript">
var imgbase = '{$_PBENV['PB_URL']}images/common/';
function toggleFolder(id, imageNode) 
{
	var folder = document.getElementById(id);
	var l = imageNode.src.length;
	if (imageNode.src.substring(l-25,l) == "treemenu_folderclosed.png" || imageNode.src.substring(l-23,l) == "treemenu_folderopen.png")
	{
		imageNode = imageNode.previousSibling;
		l = imageNode.src.length;
	}
	if (folder == null) 
	{
	}
	else if (folder.style.display == "block") 
	{
		if (imageNode != null) 
		{
			imageNode.nextSibling.src = imgbase + "treemenu_folderclosed.png";
			if (imageNode.src.substring(l-18,l) == "treemenu_mnode.png")
			{
				imageNode.src = imgbase + "treemenu_pnode.png";
			}
		}
		folder.style.display = "none";
	}
	else
	{
		if (imageNode != null) 
		{
			imageNode.nextSibling.src = imgbase + "treemenu_folderopen.png";
			if (imageNode.src.substring(l-18,l) == "treemenu_pnode.png")
			{
				imageNode.src = imgbase + "treemenu_mnode.png";
			}
		}
		folder.style.display = "block";
	}
}      
</script>
<div class="treemenu">
<div class="directory">
EOT;
	
	$menu .= $GLOBALS['menu']->treeMenu((int)$cid).'</div></div>';
	echo $menu;
}
/**
 * 栏目导航
 */
function nav()
{
	global $cid;
	echo $GLOBALS['menu']->nav($cid);
}
/**
 * 上下文导航
 */
function prevnext()
{
	global $tid, $common_message, $DB, $db_prefix, $pb_sitedir, $_prevnexttpl;

	$prev = $DB->fetch_one("SELECT subject, tid FROM {$db_prefix}threads WHERE tid < $tid ORDER BY tid DESC LIMIT 1");
	$next = $DB->fetch_one("SELECT subject, tid FROM {$db_prefix}threads WHERE tid > $tid ORDER BY tid ASC LIMIT 1");

	echo str_replace(array('{!--prev--}', '{!--next--}'), array(($prev ? '<a href="show.php?tid='.$prev['tid'].'">'.$prev['subject'].'</a>' : $common_message['show_noarticle']), ($next ? '<a href="show.php?tid='.$next['tid'].'">'.$next['subject'].'</a>' : $common_message['show_noarticle'])), $_prevnexttpl['template']);
}
/**
 * 相关文章
 * @param int $num 调用文章数量
 * @param int $length 标题长度，留空为不限
 * @param string $timeformat 时间格式
 */
function linkarticle($num = 10, $length = 0, $timeformat = 'Y-m-d H:i:s')
{
	global $tid, $moduleid, $common_message, $DB, $db_prefix, $pb_sitedir, $_linkarticletpl;

	$articlelink = $tids = '';
	$length = (int)$length;
	$num = (is_int($num) && $num > 0) ? intval($num) : 10;
	$query = $DB->db_query("SELECT tc1.tid 
							FROM {$db_prefix}tagcache tc1
							LEFT JOIN {$db_prefix}tagcache tc2 ON tc1.tagid = tc2.tagid
							WHERE tc2.tid = '$tid' LIMIT 100");

	while ($rs = $DB->fetch_all($query))
	{
		$tid != $rs['tid'] && $tids .= ($tids ? ',' : '') .$rs['tid'];
	}
	if ($tids)
	{
		$query = $DB->db_query("SELECT tid, cid, subject, digg, bury, postdate, realurl 
								FROM {$db_prefix}threads 
								WHERE tid IN ($tids) AND module = '$moduleid' 
								LIMIT 0, $num");
		while ($rs = $DB->fetch_all($query))
		{
			$altsubject = $rs['subject'];
			$subject = $length ? PBSubstr($rs['subject'], $length) : $rs['subject'];
			$turl = 'show.php?tid='.$rs['tid'];
			$postdate = gdate($rs['postdate'], $timeformat);
			$articlelink .= str_replace(array('{!--subject--}', '{!--turl--}', '{!--digg--}', '{!--bury--}', '{!--altsubject--}', '{!--postdate--}'), array($subject, $turl, $rs['digg'], $rs['bury'], $altsubject, $postdate), $_linkarticletpl['template']);
		}
	}

	echo $articlelink ? $articlelink : $common_message['show_nolinkarticle'];
}

function digger($num = 10)
{
	global $tid, $DB, $db_prefix, $common_message;
	
	$num = (is_int($num) && $num > 0) ? intval($num) : 10;
	$query = $DB->db_query("SELECT m.uid, m.username, m.avatar, m.ucuid 
					FROM {$db_prefix}members m 
					LEFT JOIN {$db_prefix}tdata t 
					USING (uid) WHERE t.tid = '$tid' AND t.type = 'digg' LIMIT 0, $num");
	$articleattention = '';
	while ($rs = $DB->fetch_all($query))
	{
		$articleattention .= '<div><span><img src="'.userFace($rs['avatar'], $rs['ucuid']).'" alt="" /></span><span><a href="'.userSpace($rs['uid'], $rs['ucuid']).'">'.$rs['username'].'</a></span></div>';
	}
	echo $articleattention ? $articleattention : $common_message['show_noannony'];
	return;
}
?>