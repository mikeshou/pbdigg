<?php
/**
 * @version $Id: index.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_PAGE', 'index');
require_once './include/common.inc.php';
@include_once PBDIGG_CROOT.'cache_specialtpl.php';

$urladd = $multLink = $sql = $pageadd = '';
$ifrewrite = $popularon = TRUE;
$rssurl = $_PBENV['PB_URL'].'rss.php?action=index';
$customer['tdigged'] = gCookie('pb_tdigged');
$customer['tburied'] = gCookie('pb_tburied');

//view upcoming
if (isset($action))
{
	$pb_torder = 'postdate';
	$urladd .= 'action=upcoming&amp;';
	$ifrewrite = $popularon = FALSE;
}
//view by time
if (isset($viewday) && is_numeric($viewday))
{
	$postdate = $timestamp - ((int)$viewday * 86400);
	$ckyear = gdate($postdate, 'Y');
	($ckyear <= 1970 || $ckyear >= 2038) &&	showMsg('index_illegal_time_param');
	$sql .= " AND t.postdate >= '$postdate'";
	$urladd .= 'viewday='.$viewday.'&amp;';
	$ifrewrite = FALSE;
}
//view by tag
if (isset($tag) && preg_match('~^[-_\x7f-\xff\w\s]+$~i', $tag) && strlen($tag) <= 20) 
{
	$tag = $originaltag = HConvert($tag);
	$query = $DB->db_query("SELECT tc.tid 
			FROM {$db_prefix}tagcache tc, {$db_prefix}tags t 
			WHERE tc.tagid = t.tagid 
			AND t.tagname = '$tag' LIMIT 500");
	$tidstr = '';
	while ($rs = $DB->fetch_all($query))
	{
		$tidstr .= ($tidstr ? ',' : '') . (int)$rs['tid'];
	}
	if ($tidstr)
	{
		$sql .= ' AND t.tid IN ('.$tidstr.')';
		$pb_seotitle = $pb_seokeywords = $pb_seodescription = $tag;
		$tag = rawurlencode($tag);
		$rssurl = $_PBENV['PB_URL'].'rss.php?action=tag&amp;tagname='.$tag;
		$urladd .= 'tag='.$tag.'&amp;';
		$ifrewrite = FALSE;
		$DB->db_exec("UPDATE {$db_prefix}tags SET hits = hits + 1 WHERE tagname = '$tag'");
	}
	else
	{
		showMsg('index_tag_records_empty');
	}
	$pageadd = '_tag';
}

!$allowlockatc && $sql .= ' AND t.ifcheck = 1';

$recordNum = intval($DB->fetch_first("SELECT COUNT(*) num FROM {$db_prefix}threads t WHERE 1 $sql"));

$pb_tday && $popularon && (!$p || $p == 1) && $pb_torder != 'pbrank' && $ifrewrite && $sql .= ' AND t.postdate > '.($timestamp - $pb_tday);

$pagesize = (int)$pb_aperpage;
$limit = sqlLimit($p, $pagesize);
$multLink = multLink($p, $recordNum, 'index.php?'.$urladd, $pagesize);

$article = array();

list($pb_topicstyleh, $pb_topicstylew) = explode("\t", $pb_topicstylesize);

$query = $DB->db_query("SELECT t.tid, t.cid, t.author, t.uid, t.subject, t.contentlink, t.linkhost, t.topicimg, t.postdate, t.digg, t.bury, t.views, t.comments, t.keywords, t.titlecolor, t.titlestyle, t.summary, t.realurl, m.avatar, m.ucuid 
		FROM {$db_prefix}threads t 
		LEFT JOIN {$db_prefix}members m 
		USING (uid) 
		WHERE 1 $sql ORDER BY t.$pb_torder DESC $limit");

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
//	$rs['summary'] = str_replace("\n", '<br />', traceHtml(cutSpilthHtml(str_replace(array('&lt;', '&gt;'), array('<', '>'), PBSubstr($rs['summary'], $pb_indexcontent)))));
	$rs['summary'] = str_replace("\n", '<br />', traceHtml(cutSpilthHtml(PBSubstr($rs['summary'], $pb_indexcontent))));

	$rs['avatar'] = userFace($rs['avatar'], $rs['ucuid']);
	$rs['uurl'] = userSpace($rs['uid'], $rs['ucuid']);
	$rs['curl'] = 'category.php?cid='.$rs['cid'];
	$rs['turl'] = 'show.php?tid='.$rs['tid'];
	$rs['contentlink'] = $rs['contentlink'] ? (strtolower(substr($rs['contentlink'], 0, 7)) == 'http://' ? $rs['contentlink'] : 'http://'.$rs['contentlink']) : '';
	$rs['titleurl'] = $pb_titlelink && $rs['contentlink'] ? $rs['contentlink'] : $rs['turl'];
	
	$rs['cate'] = $_categories[$rs['cid']]['name'];
	$rs['postdate'] = formatPostTime($rs['postdate']);
	!$rs['author'] && $rs['author'] = $common_message['anonymity'];

	$rs['digged'] = strpos($customer['tdigged'], ','.$rs['tid'].',') === FALSE ? 0 : 1;
	$rs['buryed'] = strpos($customer['tburied'], ','.$rs['tid'].',') === FALSE ? 0 : 1;

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

//announcements
$announcements = $_txt_link = $_pic_link = array();

include_once PBDIGG_CROOT.'cache_links.php';

foreach ($_announcements as $k => $v)
{
	(!$v['enddate'] || $v['enddate'] > $timestamp) && $announcements[] = $v;
}
$announcementsNum = count($announcements);

if ($customer['uid'] && $customer['lastvisit'] < $timestamp - 300)
{
	$DB->db_exec("UPDATE {$db_prefix}members SET lastvisit = '$timestamp', lastip = '".$_PBENV['PB_IP']."' WHERE uid = '".$customer['uid']."'");
}

unset($tmp_tags, $tmp_keywords, $topicimg, $topicimgh, $topicimgw, $addstyle, $_announcements);

require_once pt_fetch('index'.$pageadd);

PBOutPut();

?>