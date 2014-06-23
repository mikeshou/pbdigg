<?php
/**
 * @version $Id: link.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

(!defined('IN_ADMIN') || !isset($_PBENV['PHP_SELF']) || !preg_match('/[\/\\\\]admincp\.php$/i', $_PBENV['PHP_SELF'])) && exit('Access Denied');

define('PB_PAGE', 'announcement');

if ($job == 'add')
{
	if (isPost())
	{
		intConvert(array('cid','displayorder'));
		charConvert(array('url','enddate'));

		safeConvert($subject);
		safeConvert($content);

		(!$subject || (!$content && !$url)) && showMsg('announcement_content_empty');	

		$url && checkURL($url);

		if (!$enddate || !preg_match('~^\d{4}-\d{1,2}-\d{1,2}$~', $enddate))
		{
			$enddate = 0;
		}
		else
		{
			$enddate = pStrToTime($enddate);
		}

		$DB->db_exec("INSERT INTO {$db_prefix}announcements (cid,author,subject,url,content,postdate,enddate,displayorder) VALUES ('$cid','".addslashes($customer['username'])."','$subject','$url','$content','$timestamp','$enddate','$displayorder')");
		$cid ? $Cache->singlecate($cid) : $Cache->config();
		redirect('announcement_add_success', $basename);
	}
	$option = '<select name="cid"><option value="0" selected="selected">'.$cp_message['all_cate'].'</option>';
	$_cateOption = '';
	if (@include(PBDIGG_ROOT.'data/cache/cache_cate_option.php'))
	{
		$option .= $_cateOption;
	}
	else
	{
		cateOption($_categories, $_cateOption);
		$Cache->writeCache('cate_option', 'return $_cateOption = \''.addcslashes($_cateOption, '\\\'').'\'');
		$option .= $_cateOption;
	}
	$option .= '</select>';
	$editor = getEditor(array(array('id'=>'content','type'=>'Basic','content'=>'','width'=>400,'height'=>200)));
}
elseif ($job == 'edit')
{
	if (isPost() && $aids)
	{
		//删除公告
		!is_array($aids) && $aids = settype($aids, 'array');
		positiveInteger($aids);

		$aids && $DB->db_exec("DELETE FROM {$db_prefix}announcements WHERE aid IN (".implode(',', $aids).")");
		$Cache->announce();
		redirect('announcement_del_success', $basename);
	}
	$query = $DB->db_query("SELECT aid, cid, subject, enddate, displayorder FROM {$db_prefix}announcements ORDER BY postdate DESC");
	$announcements = array();

	while ($rs = $DB->fetch_all($query))
	{
		if ($rs['enddate'])
		{
			if ($rs['enddate'] < $timestamp)
			{
				$rs['enddate'] = '<span class="r">'.$cp_message['announcements_date_expire'].'</span>';
			}
			else
			{
				$rs['enddate'] = gdate($rs['enddate'], 'Y-m-d');
			}
		}
		else
		{
			$rs['enddate'] = $cp_message['announcements_date_unlimited'];
		}
		$rs['subject'] = PBSubstr($rs['subject'], 50);
		$rs['cate'] = $rs['cid'] ? $_categories[$rs['cid']]['name'] : $cp_message['announcements_all_cate'];
		$announcements[] = $rs;
	}
	$checkSubmit = 'onsubmit="return checkDel();"';
}
elseif ($job == 'mod')
{
	intConvert(array('aid'));
	$announcements = $DB->fetch_one("SELECT * FROM {$db_prefix}announcements WHERE aid = '$aid'");
	!$announcements && showMsg('admin_illegal_parameter');

	if (isPost())
	{
		intConvert(array('cid','displayorder'));
		charConvert(array('url','enddate'));

		safeConvert($subject);
		safeConvert($content);

		(!$subject || (!$content && !$url)) && showMsg('announcement_content_empty');

		substr($url, 0, 4) != 'http' && $url = 'http://'.$url;
		$url && checkURL($url);

		if (!$enddate || !preg_match('~^\d{4}-\d{1,2}-\d{1,2}$~', $enddate))
		{
			$enddate = 0;
		}
		else
		{
			$enddate = pStrToTime($enddate);
		}

		$DB->db_query("UPDATE {$db_prefix}announcements SET cid = '$cid', subject = '$subject', url = '$url', content = '$content', enddate = '$enddate', displayorder = '$displayorder' WHERE aid = '$aid'");
		$Cache->announce();
		redirect('announcement_mod_success', 'admincp.php?action=announcement&job=edit');
	}
	$announcements['enddate'] = $announcements['enddate'] ? gdate($announcements['enddate'], 'Y-m-d') : '';
	$option = '<select name="cid"><option value="0">'.$cp_message['all_cate'].'</option>';
	$cate_option = '';
	cateOption($_categories, $cate_option, 0, $announcements['cid']);
	$option .= $cate_option;
	$option .= '</select>';
	$editor = getEditor(array(array('id'=>'content','type'=>'Basic','content'=>$announcements['content'],'width'=>400,'height'=>200)));
}

?>
