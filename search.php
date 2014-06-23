<?php
/**
 * @version $Id: search.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_PAGE', 'search');
require_once './include/common.inc.php';

if (!$allowbsearch)
{
	showMsg('search_nopermission');
}

$searchresult = array();

if (isset($searchid) && preg_match('~^[0-9a-z]{32}$~', $searchid))
{
	$rs = $DB->fetch_one("SELECT keywords, num, ids FROM {$db_prefix}scaches WHERE hash = '$searchid'");
	if ($rs && $rs['ids'])
	{
		$recordNum = (int)$rs['num'];
		$pagesize = (int)$pb_aperpage;
		$limit = sqlLimit($p, $pagesize);
		$multLink = multLink($p, $recordNum, $pb_sitedir.'search.php?searchid='.$searchid.'&amp;', $pagesize);
		$pb_seotitle = HConvert($rs['keywords']);
		$query = $DB->db_query("SELECT tid, cid, author, uid, subject, summary, postdate FROM {$db_prefix}threads t WHERE tid IN (".$rs['ids'].") $limit");
		while ($rs = $DB->fetch_all($query))
		{
			$rs['postdate'] = gdate($rs['postdate'], 'Y-m-d H:i');
			$rs['cname'] = $_categories[$rs['cid']]['name'];
			$rs['summary'] = str_replace("\n", '<br />', traceHtml(cutSpilthHtml(PBSubstr($rs['summary'], $pb_indexcontent))));
			$searchresult[] = $rs;
		}
	}
	else
	{
		showMsg('search_nocontent');
	}
}
elseif (isset($pb_s) && checkPostHash($verify))
{
	if (isset($_SERVER['HTTP_REFERER']))
	{
		$rhost = parse_url($_SERVER['HTTP_REFERER']);
		$rhost['host'] .= isset($rhost['port']) ? (':'.$rhost['port']) : '';
		if($rhost['host'] != $_SERVER['HTTP_HOST']) exit('Access Denied');
	}
	$lastsearch = $logStatus ? $customer['lastsearch'] : gCookie('pb_lastsearch');
	$searchmax = $searchmax > $pb_maxsearchctrl ? $searchmax : $pb_maxsearchctrl;
	if ($timestamp - $lastsearch < $searchmax) showMsg('search_flood_limit');
	$pb_s = str_replace(array('_', '%'), '', trim($_POST['pb_s']));
	strlen($pb_s) < 2 && showMsg('search_keywords_invalid');
	$pb_seotitle = HConvert($pb_s);
	$hash = md5($pb_s);
	if(preg_match("/(\s)/i", $pb_s) && !preg_match("/(\|)/i", $pb_s))
	{
		$andor = ' AND ';
		$cut = -5;
		$pb_s = preg_replace("/( )/is", "+", $pb_s);
	}
	else
	{
		$andor = ' OR ';
		$cut = -4;
		$pb_s = preg_replace("/(\|)/is", "+", $pb_s);
	}
	$keywords = array_filter(explode('+', $pb_s));
	(!isset($searchitem) || !in_array($searchitem, array(1,2,3,4))) && $searchitem = 1;
	foreach ($keywords as $t)
	{
		$t = trim($t);
		if($t)
		{
			$sql .= '(';
			switch ($searchitem)
			{
				case '2':
					$sql .= "keywords LIKE '%".$t."%'";
					break;

				case '3':
					$sql .= "author LIKE '%".$t."%'";
					break;

				case '4':
					if (!$allowasearch) showMsg('search_fullsearch_nopermission');
					$sql .= "summary LIKE '%".$t."%'";
					break;

				default:
					$sql .= "subject LIKE '%".$t."%'";
					break;
			}
			$sql .= ')';
			$sql .= $andor;
		}
	}
	$sql = substr($sql, 0 , $cut);
	
	$rs = $DB->fetch_one("SELECT hash, ids, num FROM {$db_prefix}scaches WHERE hash = '$hash' AND exptime >= '$timestamp'");
	if ($rs)
	{
		redirect('search_to_result', 'search.php?searchid='.$rs['hash']);
	}
	else
	{
		$query = $DB->db_query("SELECT tid FROM {$db_prefix}threads WHERE ifcheck = 1 AND $sql LIMIT ".intval($pb_maxresult));
		$tids = '';
		$recordNum = (int)$DB->db_num($query);
		while ($rs = $DB->fetch_all($query))
		{
			$tids .= ($tids ? ',' : '').$rs['tid'];
		}
		if ($tids)
		{
			$DB->db_exec("DELETE FROM {$db_prefix}scaches WHERE exptime < '$timestamp'");
			$DB->db_exec("INSERT INTO {$db_prefix}scaches (hash, keywords, num, ids, searchip, searchtime, exptime) VALUES ('$hash', '$pb_s', '$recordNum', '$tids', '".$_PBENV['PB_IP']."', '$timestamp', ".($timestamp + 3600).")");
			if ($logStatus)
			{
				$DB->db_exec("UPDATE {$db_prefix}members SET lastsearch = '$timestamp' WHERE uid = '".$customer['uid']."'");
			}
			sCookie('pb_lastsearch', $timestamp);
			redirect('search_to_result', 'search.php?searchid='.$hash);
		}
		else
		{
			showMsg('search_nocontent');
		}
	}
}
else
{
	$pb_seotitle = $common_message['search_title'];
}

require_once pt_fetch('search');

PBOutPut();

?>
