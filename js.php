<?php
/**
 * @version $Id: js.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_CSCRIPT', 'js');
require_once('include/common.inc.php');

ob_end_clean();
obStart();

if (!$pb_jstransfer)
{
	exit('document.write("'.$common_message['js_transfer_closed'].'")');
}
if ($pb_jsurl && !in_array($_SERVER['HTTP_HOST'], explode('|', $pb_jsurl)))
{
	exit('document.write("'.$common_message['js_transfer_denied'].'")');
}

$output = '';

switch ($action)
{
	case 'cate':
		$cids  = explode('_', $cids);
		foreach ($cids as $v)
		{
			if (isset($_categories[$v]))
			{
				$output .= '<li><a href="'.$_PBENV['PB_URL'].'category.php?cid='.$v.'" target="_blank">'.$_categories[$v]['name'].'</a></li>';
			}
		}
		break;

	case 'notice':
		$num = is_numeric($num) ? $num : 10;
		$length = is_numeric($length) ? $length : 30;
		foreach ($_announcements as $k => $v)
		{
			if (!$v['enddate'] || $v['enddate'] < $timestamp)
			{
				$output .= '<li><a href="'.$_PBENV['PB_URL'].'announcement.php#'.$rs['aid'].'" target="_blank">'.PBSubstr($v['subject'], $length).'</a></li>';
			}
		}
		break;
	
	case 'article':
		$cachefile = PBDIGG_CROOT.'js_article_'.md5($action.$num.$length.$orderby.$cids.$nocids.$commend.$postdate.$author.$comments.$views.$digg.$bury.$cname).'tmp';
		if ($timestamp - @filemtime($cachefile) >= $pb_jstime)
		{
			$num = is_numeric($num) ? $num : 10;
			$length = is_numeric($length) ? $length : 35;
			!in_array($orderby, array('postdate','comments','views','digg','bury')) && $orderby = 'postdate';
			$cidout = $sql = '';
			foreach ($_categories as $k => $v)
			{
				if (!$v['status'])
				{
					$cidout .= ($cidout ? ',' : '').$cidout;
				}
			}
			$cidout && $sql .= ' AND cid NOT IN('.$cidout.')';
			if ($cids)
			{
				$sql .= ' AND cid IN ('.str_replace('_', ',', $cids).')';
			}
			if ($nocids)
			{
				$sql .= ' AND cid NOT IN ('.str_replace('_', ',', $nocids).')';
			}
			$commend && $sql .= ' AND commend = 1';
			$query = $DB->db_query("SELECT tid, cid, uid, author, subject, postdate, digg, bury, views, comments FROM {$db_prefix}threads ORDER BY $orderby DESC LIMIT $num");
			while ($rs = $DB->fetch_all($query))
			{
				$rs['subject'] = PBSubstr($rs['subject'], $length);
				$output .= '<li><a href="'.$_PBENV['PB_URL'].'show.php?tid='.$rs['tid'].'" target="_blank">'.$rs['subject'].'</a>';
				$postdate && $output .= '('.gdate($rs['postdate'], 'Y-m-d H:i').')';
				$author && $output .= '(<a href="'.$_PBENV['PB_URL'].'user.php?uid='.$rs['uid'].'" target="_blank">'.$rs['author'].'</a>)';
				$comments && $output .= '('.$common_message['js_total_comments'].':'.$rs['comments'].')';
				$views && $output .= '('.$common_message['js_total_views'].':'.$rs['views'].')';
				$digg && $output .= '('.$common_message['js_total_digg'].':'.$rs['digg'].')';
				$bury && $output .= '('.$common_message['js_total_bury'].':'.$rs['bury'].')';
				$cname && $output .= '(<a href="'.$_PBENV['PB_URL'].'category.php?cid='.$rs['uid'].'" target="_blank">'.$_categories[$rs['cid']]['name'].'</a>)';
				$output .= '</li>';
			}
			PWriteFile($cachefile, $output, 'wb');
		}
		else
		{
			@readfile($cachefile);
			exit;
		}
		break;

	case 'member':
		$cachefile = PBDIGG_CROOT.'js_member_'.md5($action.$num.$orderby).'tmp';
		if ($timestamp - @filemtime($cachefile) >= $pb_jstime)
		{
			$num = is_numeric($num) ? $num : 10;
			!in_array($orderby, array('regdate','postnum','commentnum','diggnum','burynum','currency')) && $orderby = 'postnum';
			$query = $DB->db_query("SELECT uid, username, ucuid FROM {$db_prefix}members ORDER BY $orderby LIMIT $num");
			while ($rs = $DB->fetch_all($query))
			{
				$output .= '<li><a href="'.userSpace($rs['uid'], $rs['ucuid']).'" target="_blank">'.$rs['username'].'</a></li>';
			}
			PWriteFile($cachefile, $output, 'wb');
		}
		else
		{
			@readfile($cachefile);
			exit;
		}
		break;

	case 'info':
		$output .= $common_message['js_total_member'].':'.$_sitestat['membernum'].'<br />';
		$output .= $common_message['js_total_threads'].':'.$_sitestat['artnum'].'<br />';
		$output .= $common_message['js_total_comments'].':'.$_sitestat['comnum'].'<br />';
		$output .= $common_message['js_new_member'].':'.$_sitestat['newmember'].'<br />';
		break;

	default:
		exit('document.write("'.$common_message['js_illegal_param'].'")');
}

echo 'document.write("'.str_replace('"','\"',$output).'");';
?>