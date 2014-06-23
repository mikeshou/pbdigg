<?php
/**
 * @version $Id: plugin.pbmap.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

!defined('IN_ADMIN') && exit('Access Denied!');

define('IN_PLUGIN', 'pbmap');

$plugin_message = loadPluginLang(IN_PLUGIN, TRUE);

require_once(PBDIGG_ROOT.'plugins/pbmap/include/pbmap.func.php');

if ($process == 'on')
{
	$url = '';
	$sql = ' WHERE ifcheck = 1';
	!isset($baidugo) && $baidugo = 1;
	!isset($pageid) && $pageid = '';
	!isset($ts) && $ts = $timestamp - 3600 * 24 * 300;//3 days 
	$ts = (int)$ts;
	$url .="ts=$ts&";
	$sql .= " AND postdate > '$ts'";
	isset($baidu) && $url .='baidu=1&';
	isset($google) && $url .='google=1&';
	$pernum = 1000;
	$start = (int)$start;
	$end = $start + $pernum;

	if (isset($mapcate) && $mapcate)
	{
		is_array($mapcate) && $mapcate = implode(',', $mapcate);
		$mapcate = explode(',', $mapcate);
		positiveInteger($mapcate);
		$mapcate = implode(',', $mapcate);
		$sql .= " AND cid IN ($mapcate)";
		$url .= "mapcate=$mapcate&";
	}
	if (!$start)
	{
		$info = $DB->fetch_one("SELECT count(tid) num FROM {$db_prefix}threads $sql");
		if ($google && $info['num'] > $pernum)
		{
			//创建索引
			$googleindex = '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
   			$num = ceil($info['num'] / $pernum);
   			$i = $pageid = 1;
   			while ($i <= $num)
   			{
   				$googleindex .= '<sitemap><loc>'.$_PBENV['PB_URL'].'sitemap'.$i++.'.xml</loc></sitemap>';
   			}
   			$googleindex .= '</sitemapindex>';
   			PWriteFile(PBDIGG_ROOT.'sitemap_index.xml', $googleindex, 'wb');
		}
		if ($baidu)
		{
			//创建baidu地图文件
			PWriteFile(PBDIGG_ROOT.'sitemap_baidu.xml', '<?xml version="1.0" encoding="UTF-8"?><document xmlns:bbs ="http://www.baidu.com/search/bbs_sitemap.xsd"><webSite>'.$_SERVER['SERVER_NAME'].'</webSite><webMaster>'.$pb_adminmail.'</webMaster><updatePeri>12</updatePeri><updatetime>'.gdate($timestamp, 'Y-m-d H:i').'</updatetime><version>PBDIGG'.$_PBENV['VERSION'].'</version>', 'wb');
		}
	}

	$query = $DB->db_query("SELECT tid, subject, postdate, keywords FROM {$db_prefix}threads $sql ORDER BY postdate DESC LIMIT $start, $pernum");

	if ($DB->db_num($query))
	{
		$baidumap = $googlemap = '';
		$baidu && $sitemap_baidu_filesize = filesize(PBDIGG_ROOT.'sitemap_baidu.xml');
		while ($rs = $DB->fetch_all($query))
		{
			if ($baidugo && $baidu)
			{
				$baidutemp = '<item><link>'.$_PBENV['PB_URL'].'show.php?tid='.$rs['tid'].'</link><title>'.$rs['subject'].'</title><pubDate>'.gdate($rs['postdate']).'</pubDate></item>';
				$sitemap_baidu_filesize += strlen($baidutemp);
				if ($sitemap_baidu_filesize < 10485760)
				{
					$baidumap .= $baidutemp;
				}
				else
				{
					$baidugo = 0;
					$url .='baidugo=0&';
				}
			}
			if ($google)
			{
				$googlemap .= '<url><loc>'.$_PBENV['PB_URL'].'show.php?tid='.$rs['tid'].'</loc><news:news><news:publication_date>'.mapdate($rs['postdate']).'</news:publication_date><news:keywords>'.$rs['keywords'].'</news:keywords></news:news></url>';
			}
		}
		if ($baidu && $baidumap)
		{
			$pb_rewrite && rewrite($baidumap);
			PWriteFile(PBDIGG_ROOT.'sitemap_baidu.xml', convert_encoding($db_charset, 'UTF-8', $baidumap));
		}
		if ($google)
		{
			$googlemap = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.google.com/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">'.$googlemap.'</urlset>';
			$pb_rewrite && rewrite($googlemap);
			PWriteFile(PBDIGG_ROOT.'sitemap'.$pageid.'.xml', convert_encoding($db_charset, 'UTF-8', $googlemap), 'wb');
		}
		$url .= "start=$end&";
		$pageid && $url .= 'pageid='.(++$pageid).'&';
		redirect('pbmap_mkprocess', 'admincp.php?action=plugin&pmark=pbmap&job=mod&process=on&'.$url);
	}
	if ($baidu)
	{
		PWriteFile(PBDIGG_ROOT.'sitemap_baidu.xml', '</document>');
	}
	showMsg('pbmap_mksuccess', 'admincp.php?action=plugin&pmark=pbmap&job=mod');
}
else
{
	$cate = array();
	foreach ($_categories as $k => $v)
	{
		$cate[$k] = $v['name'];
	}
	$mapcate = html_select($cate, 'mapcate[]', '', 'size="8" id="mapcate" multiple="multiple"');
}

require_once pt_plugin_fetch('admin', 'pbmap', 'admin');

PBOutPut();

?>