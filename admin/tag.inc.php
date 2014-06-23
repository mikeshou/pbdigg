<?php
/**
 * @version $Id: tag.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

(!defined('IN_ADMIN') || !isset($_PBENV['PHP_SELF']) || !preg_match('/[\/\\\\]admincp\.php$/i', $_PBENV['PHP_SELF'])) && exit('Access Denied');

define('PB_PAGE', 'tag');

require_once (PBDIGG_ROOT.'include/tag.func.php');

if ($job == 'add')
{
	if (isPost())
	{
		$name = checkTag($name);
		intConvert(array('status','system'));

		$rs = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}tags WHERE tagname = '$name'");
		if ($rs['num'])
		{
			showMsg('tag_exist');
		}
		else
		{
			$DB->db_exec("INSERT INTO {$db_prefix}tags (tagname, ifopen, ifsys, tagpic) VALUES ('$name', $status, $system, '')");
			$tagid = $DB->db_insert_id();
			require_once PBDIGG_ROOT.'include/Upload.class.php';
			$Upload = new Upload();
			if ($Upload->getFiles())
			{
				$picname = (isset($picname) && preg_match('~^[a-z0-9\_]+?$~i', $picname)) ? $picname : $tagid;
				$picdata = $Upload->moveFile('topic', $picname, array('jpg', 'jpeg', 'gif', 'png', 'bmp'));
				$picdata && $DB->db_exec("UPDATE {$db_prefix}tags SET tagpic = '".addslashes(HConvert($picdata[0][1].'.'.$picdata[0][2]).'|'.(int)$picdata[0][7][1].'|'.(int)$picdata[0][7][2])."' WHERE tagid = '$tagid'");
			}
		}

		$Cache->tags();
		redirect('tag_add_success', 'admincp.php?action=tag&job=edit');
	}
	$tagstatus_1 = $tagsystem_0 = 'checked="checked"';
}
elseif ($job == 'edit')
{
	$sql = '';

	if ($tagid)
	{
		$tagid = implode(',', array_map('intval', array_unique(explode(',', trim($tagid)))));
	}
	$tagid && $sql .= ' WHERE tagid IN ('.$tagid.')';
	$rs = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}tags".$sql);
	$recordNum = (int)$rs['num'];
	$limit = sqlLimit($page);

	$asc = isset($asc) && in_array($asc, array('desc','asc')) ? $asc : 'asc';
	$newasc = $asc == 'asc' ? 'desc' : 'asc';
	$orderby = (isset($orderby) && in_array($orderby, array('ifopen','usenum'))) ? $orderby : 'ifopen';

	$multLink = cpmultLink($page, $recordNum, 'admincp.php?action=tag&job=edit&tagid='.$tagid.'&recordNum='.$recordNum.'&orderby='.$orderby.'&asc='.$asc.'&');

	$query = $DB->db_query("SELECT * FROM {$db_prefix}tags".$sql." ORDER BY $orderby $asc $limit");
	$tag = array();
	while ($rs = $DB->fetch_all($query))
	{
		if ($rs['ifopen'])
		{
			$rs['tagstatus'] = $cp_message['open'];
			$rs['opptagstatus'] = $cp_message['close'];
		}
		else
		{
			$rs['tagstatus'] = '<span class="r">'.$cp_message['close'].'</span>';
			$rs['opptagstatus'] = $cp_message['open'];
		}
		$rs['ifsys'] && $rs['tagname'] = '<strong>'.$rs['tagname'].'</strong>';
		$rs['tagpic'] && $rs['tagname'] .= '&nbsp;<img src="../templates/admin/images/img.gif" align="absmiddle" />';
		$tag[] = $rs;
	}
	$checkSubmit = 'onsubmit="return checkDel();"';
}
elseif ($job == 'search')
{
	if (isPost())
	{
		$sql = $tagid = '';
		$ifsys && $sql .= ' ifsys = 1 AND';
		if ($tagname)
		{
			$tagname = array_unique(explode(',', trim($tagname)));
			if (!empty($tagname))
			{
				$sql .= ' (';
				foreach ($tagname as $v)
				{
					$v = str_replace(array('_','%','*'), array('\\_','\\%','%'), preg_replace('~\*{2,}~i', '*', $v));
					$sql .= "tagname LIKE '".$v."' OR ";
				}
				$sql = substr($sql, 0, -4).') AND';
			}
		}
		$sql && $sql = ' WHERE'.substr($sql, 0, -4);
		if ($sql)
		{
			$query = $DB->db_query("SELECT tagid FROM {$db_prefix}tags".$sql);
			while ($rs = $DB->fetch_all($query))
			{
				$tagid .= $tagid ? ','.$rs['tagid'] : $rs['tagid'];
			}
		}
		redirect('redirect_search_result', 'admincp.php?action=tag&job=edit&tagid='.$tagid);
	}
}
elseif ($job == 'status')
{
	//change tag status
	intConvert(array('tagid','status'));
	!$tagid && showMsg('admin_illegal_parameter');
	$db_status = $status ? 0 : 1;

	$DB->db_exec("UPDATE {$db_prefix}tags SET ifopen = '$db_status' WHERE tagid = '$tagid'");
	$Cache->tags();
	redirect('tag_update_success', 'admincp.php?action=tag&job=edit');
}
elseif ($job == 'del')
{
	if (isPost())
	{
		positiveInteger($tagid);
		$rt = 'tag_del_failed';
		if (delTag($tagid))
		{
			$Cache->tags();
			$rt = 'tag_del_success';
		}
		redirect($rt, 'admincp.php?action=tag&job=edit');
	}
}
elseif ($job == 'mod')
{
	intConvert(array('tagid'));
	
	$tagdata = $DB->fetch_one("SELECT tagid, tagname, usenum, ifopen, ifsys, tagpic FROM {$db_prefix}tags WHERE tagid = '$tagid'");
	!$tagdata && showMsg('tag_not_exist');

	if (isPost())
	{
		$sql = $tagpic = $picdata = $topicupdate = $tagpicsql = '';
		intConvert(array('status','system'));
		if ($name != $tagdata['tagname'])
		{
			$rs = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}tags WHERE tagname = '$name' AND tagid <> '$tagid'");
			$rs && showMsg('tag_exist');
			$sql .= "tagname = '".checkTag($name)."',";
		}

		$sql .= "ifopen='$status',ifsys='$system',";

		$tagdata['tagpic'] && $tagpic = explode('|', $tagdata['tagpic']);
		$del = isset($del) ? true : false;
		if ($del)
		{
			PDel(PBDIGG_ATTACHMENT.'topic/'.$tagpic[0]);
			$tagpicsql = "tagpic = '',";;
		}

		$picname = (isset($picname) && preg_match('~^[a-z0-9\_]+?$~i', $picname)) ? $picname : $tagdata['tagid'];
		$picname != substr($tagpic[0], 0, strpos($tagpic[0], '.')) && $topicupdate = true;

		require_once PBDIGG_ROOT.'include/Upload.class.php';
		$Upload = new Upload();
		if ($Upload->getFiles() && ($picdata = $Upload->moveFile('topic', $picname)))
		{
			$picdata = addslashes(HConvert($picdata[0][1].'.'.$picdata[0][2]).'|'.(int)$picdata[0][7][1].'|'.(int)$picdata[0][7][2]);
			$tagpicsql = "tagpic = '$picdata',";
		}
		elseif (!$del && $topicupdate && $tagpic && (@copy(PBDIGG_ATTACHMENT.'topic/'.$tagpic[0], PBDIGG_ATTACHMENT.'topic/'.$picname.'.'.Fext($tagpic[0])) || PWriteFile(PBDIGG_ATTACHMENT.'topic/'.$picname.'.'.Fext($tagpic[0]), PReadFile(PBDIGG_ATTACHMENT.'topic/'.$tagpic[0]), 'wb')))
		{
			//原主题图片需保留
			$tagpicsql = "tagpic = '".$picname.".".Fext($tagpic[0])."|".$tagpic[1]."|".$tagpic[2]."',";
		}
		$sql .= $tagpicsql;

//		$picdata = $Upload->moveFile('topic', $picname);
//		if ($picdata)
//		{
//			$picdata = addslashes(HConvert($picdata[0][1].'.'.$picdata[0][2]).'|'.(int)$picdata[0][7][1].'|'.(int)$picdata[0][7][2]);
//			$sql .= "tagpic = '$picdata',";
//		}
//		elseif (!$del && $picname && $tagpic && !file_exists(PBDIGG_ATTACHMENT.'topic/'.$picname.'.'.Fext($tagpic[0])) && (@copy(PBDIGG_ATTACHMENT.'topic/'.$tagpic[0], PBDIGG_ATTACHMENT.'topic/'.$picname.'.'.Fext($tagpic[0])) || PWriteFile(PBDIGG_ATTACHMENT.'topic/'.$picname.'.'.Fext($tagpic[0]), PReadFile(PBDIGG_ATTACHMENT.'topic/'.$tagpic[0]), 'wb')))
//		{
//			//原主题图片需保留
//			$sql .= "tagpic = '".$picname.".".Fext($tagpic[0])."|".$tagpic[1]."|".$tagpic[2]."',";
//		}

		$DB->db_exec("UPDATE {$db_prefix}tags SET ".substr($sql, 0, -1)." WHERE tagid = '$tagid'");

		$Cache->tags();
		redirect('tag_mod_success','admincp.php?action=tag&job=mod&tagid='.$tagid);
	}
	else
	{
		@extract($tagdata);
		radioChecked('tagstatus_', $ifopen);
		radioChecked('tagsystem_', $ifsys);
		if ($tagpic)
		{
			$tmp = explode('|', $tagpic);
			$tagpicname = htmlspecialchars(substr($tmp[0], 0, strpos($tmp[0], '.')));
			$tagpicext = Fext($tmp[0]);
			$tagpic = '<img src="../'.$_attdir.'/topic/'.$tmp[0].'" class="thumbtagpic" onload="if (this.width>100)this.width=\'100\';if(this.height>100)this.height=\'100\'" id="thumbtagpic" />';
		}
		$basename .= '&tagid='.$tagid;
	}
}
elseif ($job == 'tidy' && $process == 'on')
{
	intConvert(array('start', 'count'));
	!$count && $count = 500;
	$end = $start + $count;

	$query = $DB->db_query("SELECT tagid FROM {$db_prefix}tags WHERE tagid >= $start AND tagid < $end");
	if (!$DB->db_num($query))
	{
		$Cache->tags();
		redirect('tag_tidy_success', 'admincp.php?action=tag&job=tidy');
	}
	while ($rs = $DB->fetch_all($query))
	{
		$tagcache = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}tagcache WHERE tagid = ".$rs['tagid']);
		$tagcache['num'] ? $DB->db_exec("UPDATE {$db_prefix}tags SET usenum = ".(int)$tagcache['num']." WHERE tagid = ".$rs['tagid']) : $DB->db_exec("DELETE FROM {$db_prefix}tags WHERE tagid = ".$rs['tagid']);
	}
	redirect('tag_tidy_process', 'admincp.php?action=tag&job=tidy&process=on&start='.$end.'&count='.$count);
}

?>
