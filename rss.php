<?php
/**
 * @version $Id: rss.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_PAGE', 'rss');
require_once './include/common.inc.php';

!in_array($action, array('index', 'cate', 'user', 'tag')) && $action == 'index';

$sql = $pagetitle = '';
if ($action == 'cate')
{
	$cid = intval($cid);
	if (!isset($_categories[$cid]) || !$_categories[$cid]['status']) exit('Access Denied!');
	$sql .= " AND cid = '$cid'";
	$pagetitle = $_categories[$cid]['name'];
}
elseif ($action == 'tag' && isset($tagname))
{
	!preg_match('~^[-_\x7f-\xff\w\s]+$~i', $tagname) && exit('Access Denied!');
	$tid = '';
	$query = $DB->db_query("SELECT tc.tid 
			FROM {$db_prefix}tagcache tc, {$db_prefix}tags t 
			WHERE tc.tagid = t.tagid 
			AND t.tagname = '$tagname'");
	while ($result = $DB->fetch_all($query))
	{
		$tid .= ($tid ? ',' : '').$result['tid'];
	}
	$tid && $sql .= " AND tid IN ($tid)";
	$pagetitle = HConvert($tagname);
}
elseif ($action == 'user' && is_numeric($uid))
{
	$uid = intval($uid);
	$sql .= " AND uid = '$uid'";
	$rs = $DB->fetch_one("SELECT username FROM {$db_prefix}members WHERE uid = '$uid'");
	$pagetitle = HConvert($rs['username']);
}
header("Content-Type: application/xml");
$rssTitle = "<?xml version=\"1.0\" encoding=\"$db_charset\"?>\n" .
			"<rss version=\"2.0\">\n" .
			"<channel>\n" .
			"<title>$pb_sitename</title>\n" .
			"<link>$pb_siteurl</link>\n" .
			"<copyright>Copyright (C) $pb_sitename </copyright>\n" .
			"<generator>PBDIGG Version ".$_PBENV['VERSION']." Build ".$_PBENV['RELEASE']."</generator>\n" .
			"<lastBuildDate>".gdate($timestamp, 'r')."</lastBuildDate>\n";
			"<description>".$pagetitle.$common_message['rss_title']."</description>\n";
			"<ttl>".$pb_cachetime."</ttl>\n";
echo $rssTitle;

$query = $DB->db_query("SELECT tid, cid, author, uid, subject, summary, postdate, realurl, ishtml 
		FROM {$db_prefix}threads 
		WHERE ifcheck = 1 AND ifshield = 0 $sql 
		ORDER BY postdate DESC 
		LIMIT 0, $pb_aperpage");
$i = 0;
while ($rs = $DB->fetch_all($query))
{
	$rs['summary'] = str_replace("\n", '<br />', traceHtml(cutSpilthHtml(PBSubstr($rs['summary'], $pb_indexcontent))));
	$rs['turl'] =  $_PBENV['PB_URL'].'show.php?tid='.$rs['tid'];
	$rs['postdate'] = gmdate('r', $rs['postdate']);
	echo "<item id=\"".($i++)."\">\r\n<title><![CDATA[".$rs['subject']."]]></title>\r\n<link>".$rs['turl']."</link>\r\n<description><![CDATA[\n".$rs['summary']."\n]]></description>\r\n<pubDate>".$rs['postdate']."</pubDate>\r\n</item>\r\n";
}
echo "</channel>\r\n</rss>";
exit;
?>