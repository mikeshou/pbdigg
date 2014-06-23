<?php
/**
 * @version $Id: user.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_PAGE', 'user');
require_once './include/common.inc.php';
require_once './include/ubb.func.php';

$member_message = loadLang('user');
$common_message += $member_message;

$uid = (int)$uid;
$rssurl = 'rss.php?action=user&amp;uid='.$uid;

$userdata = $article = array();

$userdata = $DB->fetch_one("SELECT m.uid,m.username,m.email,m.adminid,m.groupid,m.publicemail,m.gender,m.regip,m.regdate,m.realgroup,m.postnum,m.commentnum,m.diggnum,m.burynum,m.currency,m.lastip,m.lastvisit,m.lastpost,m.lastcomment,m.lastupload,m.uploadnum,m.newmsg,m.friendnum,m.collectionnum,m.visitnum,m.ucuid,m.avatar,mx.qq,mx.msn,mx.site,mx.location,mx.birthday,mx.signature,mx.showsign,mx.ctsig  
										FROM {$db_prefix}members m 
										LEFT JOIN {$db_prefix}memberexp mx 
										USING (uid) 
										WHERE m.uid = '$uid'");

!$userdata && showMsg('user_space_noexist');

if (($pb_showsign & 1) && $userdata['showsign'] && $userdata['signature'])
{
	$userdata['ctsig'] && $userdata['signature'] = signUBB($userdata['signature']);
	list($pb_signh, $pb_signw) = explode("\t", $pb_signsize);
	$userdata['signature'] = '<div id="signature" style="'.($pb_signh ? 'max-height:'.$pb_signh.'px;maxHeight:'.$pb_signh.'px;' : '').($pb_signw ? 'max-width:'.$pb_signw.'px;maxWidth:'.$pb_signw.'px;' : '').'overflow:hidden;">'.preg_replace('~(?:\r\n|\n\r|\r|\n)~', '<br />', $userdata['signature']).'</div>';
}
else
{
	$userdata['signature'] = '';
}

switch ($userdata['gender'])
{
	case '0':
		$userdata['gender_name'] = $common_message['male'];
		break;
	case '1':
		$userdata['gender_name'] = $common_message['female'];
		break;
	default:
		$userdata['gender_name'] = $common_message['secrecy'];
		break;
}
$userdata['avatar'] = userFace($userdata['avatar'], $userdata['ucuid']);
$userdata['uurl'] = userSpace($userdata['uid'], $userdata['ucuid']);
!$userdata['publicemail'] && $userdata['email'] = $common_message['secrecy'];
$userdata['lastvisit'] = gdate($userdata['lastvisit'], 'Y-m-d');

$query = $DB->db_query("SELECT t.tid, t.cid, t.author, t.uid, t.subject, t.contentlink, t.linkhost, t.topicimg, t.postdate, t.digg, t.bury, t.views, t.comments, t.keywords, t.titlecolor, t.titlestyle, t.summary, t.realurl 
		FROM {$db_prefix}threads t WHERE t.uid = '$uid'ORDER BY t.postdate DESC LIMIT 10");

while ($rs = $DB->fetch_all($query))
{
	$style = '';
	$rs['titlecolor'] && $style .= 'color:#'.$rs['titlecolor'].';';
	if ($rs['titlestyle'])
	{
		/*b/em/u*/
		($rs['titlestyle'] & 1) && $style .= 'font-weight:bold;';
		($rs['titlestyle'] & 2) && $style .= 'font-style:italic;';
		($rs['titlestyle'] & 4) && $style .= 'text-decoration:underline;';
	}
	$rs['altsubject'] = $rs['subject'];
	$rs['subject'] = PBSubstr($rs['subject'], $pb_indextitle);
	$style && $rs['subject'] = '<span style="'.$style.'">'.$rs['subject'].'</span>';
	$rs['summary'] = str_replace("\n", '<br />', traceHtml(cutSpilthHtml(PBSubstr($rs['summary'], $pb_indexcontent))));

	$rs['curl'] = 'category.php?cid='.$rs['cid'];
	$rs['turl'] = 'show.php?tid='.$rs['tid'];
	$rs['contentlink'] = $rs['contentlink'] ? (strtolower(substr($rs['contentlink'], 0, 7)) == 'http://' ? $rs['contentlink'] : 'http://'.$rs['contentlink']) : '';
	$rs['titleurl'] = $pb_titlelink && $rs['contentlink'] ? $rs['contentlink'] : $rs['turl'];

	$rs['cate'] = $_categories[$rs['cid']]['name'];
	$rs['postdate'] = formatPostTime($rs['postdate']);

	$tmp_tags = $tmp_keywords = $topicimg = $topicimgh = $topicimgw = $addstyle = '';
	if ($rs['keywords'])
	{
		$tmp_keywords = explode(',', $rs['keywords']);
		foreach ($tmp_keywords as $v)
		{
			$v = HConvert($v);
			$tmp_tags .= '<a href="'.$pb_sitedir.'index.php?tag='.rawurlencode($v).'">'.$v.'</a> ';
		}
		$rs['tags'] = $tmp_tags;
	}
	else
	{
		$rs['tags'] = $common_message['null'];
	}
	if ($rs['topicimg'])
	{
		list($topicimg, $topicimgh, $topicimgw) = explode('|', $rs['topicimg']);
		(!$topicimgh || $topicimgh > $pb_topicstyleh) && $addstyle .= 'height:'.$pb_topicstyleh.'px;';
		(!$topicimgw || $topicimgw > $pb_topicstylew) && $addstyle .= 'width:'.$pb_topicstylew.'px;';
		$rs['topicimg'] = '<a href="'.$rs['turl'].'" class="topicimg"><img src="'.$pb_sitedir.$_attdir.'/topic/'.$topicimg.'" alt="'.$rs['altsubject'].'" style="'.$addstyle.'" /></a>';
	}
	!$rs['linkhost'] && $rs['linkhost'] = $common_message['origianl'];
	$article[] = $rs;
}


require_once pt_fetch('user');

PBOutPut();

?>