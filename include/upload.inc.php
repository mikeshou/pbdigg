<?php
/**
 * @version $Id: upload.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_PAGE', 'upload');
require_once './common.inc.php';

if (!$allowaupload || !$pb_allowupload || !$timesession || !checkPostHash($verify) || !in_array($type, array('attachment', 'commend')))
{
	uploaderror(1);
}

$timesession = (int)$timesession;

switch ($action)
{
	case 'upload':
		require_once PBDIGG_ROOT.'include/validate.func.php';
		require_once PBDIGG_ROOT.'include/Upload.class.php';
		$exts = $attachment = $newattachment = array();
		$attachment_num = $size = $attachnum = 0;
		$ifatta = false;

		$mid = isset($mid) ? intval($mid) : 1;//默认为文章模型
		if ($module->checkModuleID($mid))
		{
			$currentModuleObj = $module->getModuleObject($mid);
			$exts = $currentModuleObj->getUploadExts($type);
			$size = $currentModuleObj->getUploadSize();
			$attachnum = $currentModuleObj->getUploadNums($type);
		}
		else
		{
			uploaderror(2);
		}

		$fdata = $DB->fetch_one("SELECT * FROM {$db_prefix}fsession WHERE uid = '".$customer['uid']."' AND timesession = '$timesession'");
		if ($fdata)
		{
			if ($fdata['attachment'])
			{
				$attachment = unserialize($fdata['attachment']);
				$attachment_num = count($attachment);
				if ($attachment_num >= $attachnum) uploaderror(3);
			}
			$ifatta = true;
		}

		$Upload = new Upload();
		$newattachment = $Upload->moveFile($type, '', $exts, $size);
		!$newattachment && uploaderror(4);

		foreach ($newattachment as $file)
		{
			$attachment[++$attachment_num] = array(addslashes(HConvert(convert_encoding('UTF-8', $db_charset, $file[8]))), $file[4], $file[3], $file[1], $file[2], $file[5], $file[6]);

		}
		if ($ifatta)
		{
			$DB->db_exec("UPDATE {$db_prefix}fsession SET attachment = '".addslashes(serialize($attachment))."' WHERE uid = '".$customer['uid']."' AND timesession = '$timesession'");
		}
		else
		{
			$DB->db_exec("INSERT INTO {$db_prefix}fsession (uid,attachment,timesession) VALUES ('".$customer['uid']."','".addslashes(serialize($attachment))."','$timesession')");
			clearFsession();
		}

		echo $attachment_num.','.$type;
		exit;
		break;
		
	case 'show':
		$id = (int)$id;
		$attachment = $DB->fetch_one("SELECT * FROM {$db_prefix}fsession WHERE uid = '".$customer['uid']."' AND timesession = '$timesession'");
		!$attachment && uploaderror();
		$attachment = unserialize($attachment['attachment']);
		!array_key_exists($id, $attachment) && uploaderror();
		$ca = &$attachment[$id];
		header('Pragma:no-cache');
		header('Cache-control:no-cache');
		if ($ca[6])
		{
			$img = PBDIGG_ATTACHMENT.'temp/'.$ca[3].'.'.$ca[4];
			$imginfo = @getimagesize($img);
			if (!$imginfo) uploaderror();
			header('Content-type: '.$ca[1]);
			readfile($img);
		}
		else
		{
			header('Content-type: image/gif');
			readfile(PBDIGG_ROOT.'images/common/icon.gif');
		}
		break;

	case 'del':
		$id = (int)$id;
		$attachment = $DB->fetch_one("SELECT * FROM {$db_prefix}fsession WHERE uid = '".$customer['uid']."' AND timesession = '$timesession'");
		!$attachment && uploaderror();
		$attachment = unserialize($attachment['attachment']);
		!array_key_exists($id, $attachment) && uploaderror();
		PDel(PBDIGG_ATTACHMENT.'temp/'.$attachment[$id][3].'.'.$attachment[$id][4]);
		$attachment[$id][5] && $attachment[$id][6] && PDel(PBDIGG_ATTACHMENT.'temp/thumb_'.$attachment[$id][3].'.'.$attachment[$id][4]);
		unset($attachment[$id]);
		$attachment = empty($attachment) ? '' : serialize($attachment);
		$DB->db_exec("UPDATE {$db_prefix}fsession SET attachment = '".addslashes($attachment)."' WHERE uid = '".$customer['uid']."' AND timesession = '$timesession'");
		break;
	
	case 'load':
		$attachment = $DB->fetch_one("SELECT * FROM {$db_prefix}fsession WHERE uid = '".$customer['uid']."' AND timesession = '$timesession'");
		if ($attachment && $attachment['attachment']) echo implode(',', array_keys(unserialize($attachment['attachment'])));
		exit;
		break;
	default:
		uploaderror();
	break;
}


function uploaderror($id = 0)
{
	global $action;

	if ($action == 'show')
	{
		header('Content-type: image/gif');
		readfile(PBDIGG_ROOT.'images/common/noimg.gif');
	}
	else
	{
		file_put_contents(PBDIGG_ROOT.'log/xxx.txt', $id);
		header('HTTP/1.1 500 Internal Server Error');
	}
	exit;
}

function clearFsession()
{
	global $DB, $db_prefix, $timestamp;
	$query = $DB->db_query("SELECT attachment FROM {$db_prefix}fsession WHERE timesession < $timestamp - 1800");
	while ($rs = $DB->fetch_all($query))
	{
		if ($rs['attachment'])
		{
			$a = unserialize($rs['attachment']);
			foreach ($a as $k => $v)
			{
				PDel(PBDIGG_ATTACHMENT.'temp/'.$v[3].'.'.$v[4]);
				if ($v[5] && $v[6])
				{
					PDel(PBDIGG_ATTACHMENT.'temp/thumb_'.$v[3].'.'.$v[4]);
				}
			}
		}
	}
	$DB->db_exec("DELETE FROM {$db_prefix}fsession WHERE timesession < $timestamp - 1800");
}
?>