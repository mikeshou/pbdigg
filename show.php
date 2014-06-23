<?php
/**
 * @version $Id: show.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_PAGE', 'show');
require_once './include/common.inc.php';
require_once PBDIGG_ROOT.'include/ubb.func.php';
@include_once PBDIGG_CROOT.'cache_specialtpl.php';
@include_once PBDIGG_CROOT.'cache_grouplevel.php';

!$tid && showMsg('illegal_request');
!$allowread && showMsg('show_read_denied');

$comments = $article = array();
$commonM = $superM = 0;
$commentMultLink = '';

$article = $DB->fetch_one("SELECT t.tid,t.cid,t.author,t.uid,t.subject,t.contentlink,t.linkhost,t.topicimg,t.ifcheck,t.ifshield,t.iflock,t.topped,t.postdate,t.postip,t.digg,t.diggdate,t.bury,t.burydate,t.views,t.comments,t.commentdate,t.keywords,t.pbrank,t.commend,t.commendpic,t.first,t.module,t.realurl,t.ishtml,t.summary,t.titlecolor,t.titlestyle,
						m.uid,m.username,m.email,m.adminid,m.groupid,m.publicemail,m.gender,m.regdate,m.realgroup,m.postnum,m.commentnum,m.diggnum,m.burynum,m.currency,m.lastvisit,m.lastpost,m.lastcomment,m.uploadnum,m.friendnum,m.collectionnum,m.visitnum,m.ucuid,m.avatar,
						mx.qq,mx.msn,mx.site,mx.location,mx.birthday,mx.signature,mx.showsign,mx.ctsig 
						FROM {$db_prefix}threads t 
						LEFT JOIN {$db_prefix}members m 
						ON t.uid = m.uid 
						LEFT JOIN {$db_prefix}memberexp mx 
						ON m.uid = mx.uid 
						WHERE t.tid = '$tid'");
!$article && showMsg('show_illegal_tid');

$currentCateData = &$_categories[$article['cid']];
!$currentCateData && showMsg('illegal_data');
!($allowsort | $currentCateData['status']) && showMsg('show_cate_no_permission');

!($article['ifcheck'] | ($article['uid'] && $article['uid'] == $customer['uid']) | $allowcheckatc) && showMsg('show_article_unchecked', $_PBENV['PB_URL']);

$currentModuleData = $module->getSingleModuleData($article['module']);
!$currentModuleData && showMsg('illegal_data');
!$currentModuleData[$currentModuleData['identifier'].'_status'] && showMsg('show_module_closed');
$moduleid = $article['module'];

/*** 权限控制 ***/
if ($article['adminid'] && ($article['adminid'] < $customer['adminid']))
{
	$alloweditatc = $allowdelatc = $allowcheckatc = $allowlockatc = $allowmoveatc = $allowcopyatc = $allowtopatc = $allowshield = $allowcommend = '';
}
if ($logStatus && ($customer['uid'] == $article['uid']))
{
	$alloweditatc = $allowdelatc = 1;
	$commonM = 1;
}
$allowip = $superM = $customer['adminid'];
$allowcomment = $allowcomment && ($pb_ifcomment || SUPERMANAGER);

$cid = $article['cid'];
$article['cate'] = $currentCateData['name'];

$article['postdate'] = formatPostTime($article['postdate']);
!$article['author'] && $article['author'] = $common_message['anonymity'];
//会员签名
if (($pb_showsign & 1) && $article['showsign'] && $article['signature'])
{
	$article['ctsig'] && $article['signature'] = signUBB($article['signature']);
	list($pb_signh, $pb_signw) = explode("\t", $pb_signsize);
	$article['signature'] = '<div id="signature" style="'.($pb_signh ? 'max-height:'.$pb_signh.'px;maxHeight:'.$pb_signh.'px;' : '').($pb_signw ? 'max-width:'.$pb_signw.'px;maxWidth:'.$pb_signw.'px;' : '').'overflow:hidden;">'.preg_replace('~(?:\r\n|\n\r|\r|\n)~', '<br />', $article['signature']).'</div>';
}
else
{
	$article['signature'] = '';
}

switch ($article['gender'])
{
	case '0':
		$article['gender_name'] = $common_message['male'];
		break;
	case '1':
		$article['gender_name'] = $common_message['female'];
		break;
	default:
		$article['gender_name'] = $common_message['secrecy'];
		break;
}
$article['groupid'] == -1 && $article['groupid'] = (int)$article['realgroup'];
$article['grouptitle'] = $_grouplevel[$article['groupid']]['grouptitle'];
!$article['publicemail'] && $article['email'] = $common_message['secrecy'];
$article['avatar'] = userFace($article['avatar'], $article['ucuid']);
$article['uurl'] = userSpace($article['uid'], $article['ucuid']);
$article['turl'] = 'show.php?tid='.$tid;
$article['curl'] = 'category.php?cid='.$article['cid'];
$style = '';
$article['titlecolor'] && $style .= 'color:#'.$article['titlecolor'].';';
if ($article['titlestyle'])
{
	/*b/em/u*/
	($article['titlestyle'] & 1) && $style .= 'font-weight:bold;';
	($article['titlestyle'] & 2) && $style .= 'font-style:italic;';
	($article['titlestyle'] & 4) && $style .= 'text-decoration:underline;';
}
$article['altsubject'] = $article['subject'];
$style && $article['subject'] = '<span style="'.$style.'">'.$article['subject'].'</span>';

if ($article['topicimg'])
{
	list($pb_topicstyleh, $pb_topicstylew) = explode("\t", $pb_topicstylesize);
	list($topicimg, $topicimgh, $topicimgw,) = explode('|', $article['topicimg']);
	$addstyle = '';
	(!$topicimgh || $topicimgh > $pb_topicstyleh) && $addstyle .= 'height:'.$pb_topicstyleh.'px;';
	(!$topicimgw || $topicimgw > $pb_topicstylew) && $addstyle .= 'width:'.$pb_topicstylew.'px;';
	$article['topicimg'] = '<a href="'.$article['turl'].'" class="topicimg"><img src="'.$pb_sitedir.$_attdir.'/topic/'.$topicimg.'" alt="'.$article['altsubject'].'" style="'.$addstyle.'" /></a>';
}

$article['digged'] = strpos(gCookie('pb_tdigged'), ','.$tid.',') === FALSE ? 0 : 1;
$article['buryed'] = strpos(gCookie('pb_tburied'), ','.$tid.',') === FALSE ? 0 : 1;

!$article['contentlink'] && $article['contentlink'] = $article['turl'];
!$article['linkhost'] && $article['linkhost'] = $common_message['origianl'];

if ($article['keywords'])
{
	$tmp_tags = '';
	$article['keywords'] = explode(',', $article['keywords']);
	foreach ($article['keywords'] as $v)
	{
		$v = HConvert($v);
		$tmp_tags .= '<a href="'.$pb_sitedir.'index.php?tag='.rawurlencode($v).'">'.$v.'</a> ';
	}
	$article['tags'] = $tmp_tags;
}
else
{
	$article['tags'] = $common_message['null'];
}

$currentModuleObj = $module->getModuleObject($article['module']);
$currentModuleObj->show($tid, $article);

$pb_seotitle = $article['altsubject'].'_'.$article['cate'];
$pb_seokeywords = $article['keywords'] ? implode(',', $article['keywords']) : $article['altsubject'];
$pb_seodescription = preg_replace('~(?:\r\n|\n\r|\r|\n)~', '', strip_tags(str_replace('"', '&quot;', $article['summary'])));
$navLink = $menu->nav($cid).$common_message['nav_separator'].$article['subject'];
$rssurl = $_PBENV['PB_URL'].'rss.php?action=cate&amp;cid='.$cid;

//comment
if ($article['comments'])
{
	//评论采用ajax分页
	$conditionsql = $fieldsql = '';
	$i = 1;
	$pagesize = (int)$pb_cperpage;
	$commentMultLink = commentMultLink($p, $article['comments'], 'showcomment', $pagesize);

	!$allowcheckatc && $conditionsql .= ' AND c.ifcheck = 1';
	if ($pb_showsign & 2)
	{
		list($pb_signh, $pb_signw) = explode("\t", $pb_signsize);
		$fieldsql .= ',mx.signature,mx.showsign,mx.ctsig ';
	}
	list($ordersql, $bysql) = explode("\t", $pb_corder);
	
	$query = $DB->db_query("SELECT c.rid,c.cid,c.tid,c.author,c.uid,c.content,c.ifcheck,c.ifshield,c.ifconvert,c.postdate,c.postip,c.digg,c.diggdate,c.bury,c.burydate,
							m.username,m.email,m.adminid,m.groupid,m.publicemail,m.gender,m.regdate,m.realgroup,m.postnum,m.commentnum,m.diggnum,m.burynum,m.currency,m.lastvisit,m.lastpost,m.lastcomment,m.uploadnum,m.friendnum,m.collectionnum,m.visitnum,m.ucuid,m.avatar,
							mx.qq,mx.msn,mx.site,mx.location,mx.birthday {$fieldsql}
							FROM {$db_prefix}comments c 
							LEFT JOIN {$db_prefix}members m 
							ON c.uid = m.uid 
							LEFT JOIN {$db_prefix}memberexp mx 
							ON m.uid = mx.uid 
							WHERE c.tid = '$tid' {$conditionsql}
							ORDER BY c.{$ordersql} {$bysql} 
							LIMIT 0, $pagesize");
	while ($comment = $DB->fetch_all($query))
	{
		$comment['postdate'] = formatPostTime($comment['postdate']);
		!$comment['author'] && $comment['author'] = $common_message['anonymity'];
		
		//会员签名
		if (($pb_showsign & 2) && $comment['showsign'] && $comment['signature'])
		{
			$comment['ctsig'] && $comment['signature'] = signUBB($comment['signature']);
			
			$comment['signature'] = '<div id="signature_"'.$i.' style="'.($pb_signh ? 'max-height:'.$pb_signh.'px;maxHeight:'.$pb_signh.'px;' : '').($pb_signw ? 'max-width:'.$pb_signw.'px;maxWidth:'.$pb_signw.'px;' : '').'overflow:hidden;">'.preg_replace('~(?:\r\n|\n\r|\r|\n)~', '<br />', $comment['signature']).'</div>';
		}
		else
		{
			$comment['signature'] = '';
		}

		switch ($comment['gender'])
		{
			case '0':
				$comment['gender_name'] = $common_message['male'];
				break;
			case '1':
				$comment['gender_name'] = $common_message['female'];
				break;
			default:
				$comment['gender_name'] = $common_message['secrecy'];
				break;
		}
		$comment['avatar'] = userFace($comment['avatar'], $comment['uid']);
		$comment['uurl'] = userSpace($comment['uid'], $comment['ucuid']);

		$comment['digged'] = strpos(gCookie('pb_rdigged'), ','.$comment['rid'].',') === FALSE ? 0 : 1;
		$comment['buryed'] = strpos(gCookie('pb_rburied'), ','.$comment['rid'].',') === FALSE ? 0 : 1;

		$contentShield = $shieldNotice = '';
		if ($comment['ifshield'] || $comment['groupid'] == 3 && $pb_autoshield)
		{
			if ($customer['adminid'])
			{
				$shieldNotice = articleShield('show_admin_shield');
			}
			else
			{
				$comment['content'] = articleShield($comment['groupid'] == 3 ? 'show_auto_shield' : 'show_content_shield');
				$contentShield = true;
			}
		}
		if (!$contentShield)
		{
			$comment['content'] = $shieldNotice.$comment['content'];
			$comment['ifconvert'] && $comment['content'] = conentUBB($comment['content'], 'c');
			$comment['content'] = preg_replace('~(?:\r\n|\n\r|\r|\n)~i', '<br />', $comment['content']);
		}
		$comment['alloweditatc'] = $comment['allowdelatc'] = $comment['allowcheckatc'] = $comment['allowshield'] = '';
		//管理权限
		if ($customer['adminid'])
		{
			$comment['alloweditatc'] = $comment['allowdelatc'] = $comment['allowcheckatc'] = $comment['allowshield'] = 1;
		}
		if ($logStatus && ($customer['uid'] == $comment['uid']))
		{
			$comment['alloweditatc'] = $comment['allowdelatc'] = 1;
		}

		$comments[$i++] = $comment;
	}
}

list($pb_commentlenmin, $pb_commentlenmax) = explode("\t", $pb_commentlen);

$pbrank = pbrank($article['pbrank'], 0, 0, 1, 0, $article['postdate']);

$DB->db_exec("UPDATE {$db_prefix}threads SET views = views + 1, pbrank = '$pbrank' WHERE tid = '$tid'");

unset($comment);

require_once pt_fetch('show_'.$currentModuleData['identifier']);

PBOutPut();

?>
