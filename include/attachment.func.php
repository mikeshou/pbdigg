<?php
/**
 * @version $Id: attachment.func.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

/**
 * 删除附件
 * 
 * @param Array $condition 附件查询条件 array('字段'=>值)
 */
function delAttachment($condition = 0)
{
	global $DB, $db_prefix;
	$query = $DB->db_query("SELECT filepath, thumb FROM {$db_prefix}attachments WHERE {$condition}");
	while ($attachment = $DB->fetch_all($query))
	{
		PDel(PBDIGG_ATTACHMENT.$attachment['filepath']);
		if ($attachment['thumb'])
		{
			PDel(PBDIGG_ATTACHMENT.substr($attachment['filepath'], 0, strrpos($attachment['filepath'], '/')).'/thumb_'.substr(strrchr($attachment['filepath'], '/'), 1));
		}
	}
	$DB->db_exec("DELETE FROM {$db_prefix}attachments WHERE {$condition}");
}

/**
 * 修改附件所属分类
 */
function updateAttFID($ncid, $ocid)
{
	global $DB, $db_prefix;
	$sql = "UPDATE {$db_prefix}attachments SET cid = '".intval($ncid)."' WHERE cid = '".intval($ocid)."'";
	return $DB->db_exec($sql);
}

/**
 * 附件类型图片
 */
function attachtype($type)
{
	global $pb_sitedir;
	if (preg_match('~^torrent$~', $type))
	{
		$attachicon = 'torrent';
	}
	elseif (preg_match('~^pdf$~', $type))
	{
		$attachicon = 'pdf';
	}
	elseif (preg_match('~^(jpg|jpeg|gif|png|bmp)$~', $type))
	{
		$attachicon = 'img';
	}
	elseif (preg_match('~^(swf|fla|swi)$~', $type))
	{
		$attachicon = 'flash';
	}
	elseif (preg_match('~^(wav|mid|mp3|m3u|wma|asf|asx|vqf|mpg|mpeg|avi|wmv)$~', $type))
	{
		$attachicon = 'wmv';
	}
	elseif (preg_match('~^(ra|rmvb|rm|rv)$~', $type))
	{
		$attachicon = 'rm';
	}
	elseif (preg_match('~^(txt|rtf|wri|chm)$~', $type))
	{
		$attachicon = 'txt';
	}
	elseif (preg_match('~^doc$~', $type))
	{
		$attachicon = 'doc';
	}
	elseif (preg_match('~^xls$~', $type))
	{
		$attachicon = 'xls';
	}
	elseif (preg_match('~^(rar|zip|arj|arc|cab|lzh|lha|tar|gz)$~', $type))
	{
		$attachicon = 'zip';
	}
	else
	{
		$attachicon = 'unknow';
	}
	return '<img src="'.$pb_sitedir.'images/file/'.$attachicon.'.gif" border="0" class="attachicon" alt="'.$attachicon.'" />&nbsp;';
}
function attachment($content)
{
	return preg_replace('~\[attachment=(\d+?)\]~ie', "repatta('\\1')", $content);
}
function repatta($id)
{
	global $ainfo, $onload, $pb_sitedir, $pb_attoutput, $pb_outputmaxsize, $_attdir;

	if (!isset($ainfo[$id])) return '';
	$atta = &$ainfo[$id];
	$extension = strtolower(Fext($atta['filename']));
	$isimg = ($atta['isimg'] && in_array($extension, array('jpg', 'jpeg', 'gif', 'png', 'bmp'))) ? true : false;
	$url = $pb_sitedir.(($isimg || $pb_attoutput || ($pb_outputmaxsize && $atta['filesize'] > $pb_outputmaxsize)) ? $_attdir.'/'.$atta['filepath'] : 'attachments.php?aid='.$atta['aid']);
	unset($ainfo[$id]);
	return $isimg ? '<a href="'.$url.'" title="'.htmlspecialchars($atta['filename']).'" rel="group" class="thickbox attachimg"><img src="'.$url.'"'.$onload.' class="previewimg" /></a>' : '<span class="attachother">'.attachtype($extension).' <a href="'.$url.'" target="_blank" title="'.$atta['filename'].'">'.$atta['filename'].'</a></span>';
}
?>
