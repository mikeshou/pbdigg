<?php
/**
 * @version $Id: archive.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_PAGE', 'archive');
require_once './include/common.inc.php';

$pb_seotitle = $common_message['archive_title'];

$day = intval($_GET['day']);
if ($day && strlen($day) == 8)
{
	$setyear = substr($day, 0, 4);
	($setyear >= 2038 || $setyear <= 1970) && exit('Access Denied!');
	$setmonth = substr($day, 4, 2);
	$setday = substr($day, -2);
}
else
{
	list($setyear, $setmonth, $setday)  = explode('-', date('Y-m-d', $timestamp));
}

$prevmonth = $nextmonth = $currentmonth = $calendar = '';

list($prevmonth, $nextmonth, $currentmonth, $calendar) = calendar($setyear, $setmonth, $setday);

$query = $DB->db_query("SELECT tid, subject FROM {$db_prefix}threads WHERE ifcheck = '1' AND postdate >= '".mktime(0,0,0,$setmonth,$setday,$setyear)."' AND postdate < '".mktime(23,59,59,$setmonth,$setday,$setyear)."'");
$articles = array();
while($rs = $DB->fetch_all($query))
{
	$articles[] = $rs;
}

list ($start_yeaer, $start_month, $start_day) = explode('-', $_sitestat['buildtime']);
$start_yeaer < 2007 && $start_yeaer = 2007;
($start_month < 1 || $start_month > 12) && $start_month = 1;

list($now_yaer, $now_month) = explode('-', date('Y-n', $timestamp));
$archives = '';
$new = true;
for ($now_yaer; $now_yaer >= $start_yeaer; $now_yaer --)
{
	$archives .= "<ul>\n";
	$i = $new ? $now_month : 12;
	$j = $now_yaer == $start_yeaer ? $start_month : 1;
	while ($i >= $j)
	{
		$m = $i < 10 ? '0'.$i : $i;
		$archives .= '<li><a href="'.$_PBENV['PB_URL'].'archive.php?day='.$now_yaer.$m.'01">'.$now_yaer.$common_message['year'].$m.$common_message['month'].'</a></li>';
		$i--;
	}
	$archives .= "</ul>\n";
	$new = false;
}

function calendar($y, $m, $d)
{
	global $DB, $db_prefix, $timestamp, $common_message, $_PBENV;

	if ($m == 1)
	{
		$lastyear = $y-1;
		$lastmonth = 12;
		$nextmonth = $m+1;
		$nextyear = $y;
	}
	elseif ($m == 12)
	{
		$lastyear = $y;
		$lastmonth = $m - 1;
		$nextyear = $y + 1;
		$nextmonth = 1;
	}
	else
	{
		$lastmonth = $m - 1;
		$nextmonth = $m + 1;
		$lastyear = $nextyear = $y;
	}
	if ($nextmonth < 10) $nextmonth = '0'.$nextmonth;
	if ($lastmonth < 10) $lastmonth = '0'.$lastmonth;

	$weekday = date('w', mktime(0,0,0,$m,1,$y));
	$totalday = date('t', mktime(0,0,0,$m,1,$y));

	$start = strtotime($y.'-'.$m.'-1');
	if ($m == 12)
	{
		$endyear  = $y + 1;
		$endmonth = 1;
	}
	else
	{
		$endyear  = $y;
		$endmonth = $m + 1;
	}
	$end = strtotime($endyear.'-'.$endmonth.'-1');

	$query = $DB->db_query("SELECT postdate FROM {$db_prefix}threads WHERE ifcheck = '1' AND postdate >= '$start' AND postdate < '$end'");
	$datelines = $articles = array();
	while($rs = $DB->fetch_all($query))
	{
		$datelines[date('Ymj', $rs['postdate'])]++;
	}
	
	$br = 0;
	$html = "<tr>\n";
	for ($i = 1; $i <= $weekday; $i++)
	{
		$html .= "<td class=\"day\"></td>\n";
		$br++;
	}

	for($i = 1; $i <= $totalday; $i ++)
	{
		$td = ($y.$m.$i <= date('Ymj', $timestamp) && array_key_exists($y.$m.$i, $datelines)) ? '<a title="'.$y.$common_message['year'].$m.$common_message['month'].$i.$common_message['day'].$common_message['publish'].$datelines[$y.$m.$i].'" href="'.$_PBENV['PB_URL'].'archive.php?day='.$y.$m.($i < 10 ? '0'.$i : $i).'">'.$i.'</a>' : $i;
		$html .= "<td class=\"".($d == $i ? 'today' : 'day')."\">".$td."</td>\n";
		if (++$br >= 7)
		{
			$html .= "</tr>\n<tr>\n";
			$br = 0;
		}
	}
	if ($br != 0)
	{
		for ($i = $br; $i < 7; $i ++)
		{
			 $html .= "<td class=\"day\"></td>\n";
		}
	}
	$html .= "</tr>\n";
	return array($lastyear.$lastmonth.'01', $nextyear.$nextmonth.'01', $y.$common_message['year'].$m.$common_message['month'], $html);
}

require_once pt_fetch('archive');

PBOutPut();
?>
