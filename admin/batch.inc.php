<?php
/**
 * @version $Id: batch.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

(!defined('IN_ADMIN') || !isset($_PBENV['PHP_SELF']) || !preg_match('/[\/\\\\]admincp\.php$/i', $_PBENV['PHP_SELF'])) && exit('Access Denied');

define('PB_PAGE', 'batch');

require_once PBDIGG_ROOT.'include/article.func.php';
require_once PBDIGG_ROOT.'include/search.class.php';
$search = new search($job);

if ($job == 'article')
{
	switch ($type)
	{
		case 'search':
			//search article
			$article = $search->exportResults(array(
				'cid' => $cid,
				'authors' => $authors,
				'tags' => $tags,
				'searchdate' => array('postdate' => array($postdatemore, $postdateless)),
				'searchscope' => array('views' => array($viewsmore, $viewsless),'comments' => array($commentsmore, $commentsless),'digg' => array($diggmore, $diggless)),
				'linkhost' => $linkhost,
				'postip' => $postip,
			));
			$recordNum = $search->getResultNum();
			$multLink = cpmultLink($page, $recordNum, 'admincp.php?action=batch&job=article&type=search'.$search->getMult().'&', $pagesize);
			
			break;
		case 'del':
			!$tids && !is_array($tids) && showMsg('admin_illegal_parameter');
			delArticle($tids);
			redirect('batch_del_success', $basename);
			break;
		default:
			$_cateOption = '';
			$option = '<select name="cid"><option value="0" selected="selected"></option>';
			cateOption($_categories, $_cateOption);
			$option .= $_cateOption.'</select>';
			break;
	}
}
elseif ($job == 'comment')
{
	switch ($type)
	{
		case 'search':
			//search comment
			$comment = $search->exportResults(array(
				'cid' => $cid,
				'authors' => $authors,
				'searchdate' => array('postdate' => array($postdatemore, $postdateless)),
				'searchscope' => array('digg', $diggmore, $diggless),
				'postip' => $postip,
			));
			$recordNum = $search->getResultNum();
			$multLink = cpmultLink($page, $recordNum, 'admincp.php?action=batch&job=comment&type=search'.$search->getMult().'&', $pagesize);
			break;
		case 'del':
			!$rids && !is_array($rids) && showMsg('admin_illegal_parameter');
			delComment($rids);
			redirect('batch_del_success', $basename);
			break;
		default:
			$_cateOption = '';
			$option = '<select name="cid"><option value="0" selected="selected"></option>';
			cateOption($_categories, $_cateOption);
			$option .= $_cateOption.'</select>';
			break;
	}
}
elseif ($job == 'attachment')
{
	switch ($type)
	{
		//search attachmet
		case 'search':
			$attachment = $search->exportResults(array(
				'cid' => $cid,
				'authors' => $author,
				'searchdate' => array('uploaddate' => array($uploaddatemore, $uploaddateless)),
				'searchscope' => array('filesize' => array($filesizemore, $filesizeless),'tid' => array($tidmore, $tidless)),
				'isimg' => $isimg,
			));
			$recordNum = $search->getResultNum();
			$multLink = cpmultLink($page, $recordNum, 'admincp.php?action=batch&job=attachment&type=search'.$search->getMult().'&', $pagesize);
			break;
		case 'del':
			require_once PBDIGG_ROOT.'include/attachment.inc.php';
			(!$aids || !is_array($aids)) && showMsg('admin_illegal_parameter');
			$aids = array_map('intval', $aids);
			delAttachment('aid IN ('.implode(',', $aids).')');
			redirect('batch_del_success', $basename);
			break;
		default:
			$_cateOption = '';
			$option = '<select name="cid"><option value="0" selected="selected"></option>';
			cateOption($_categories, $_cateOption);
			$option .= $_cateOption.'</select>';
			break;
	}
}


?>
