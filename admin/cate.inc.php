<?php
/**
 * @version $Id: cate.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

(!defined('IN_ADMIN') || !isset($_PBENV['PHP_SELF']) || !preg_match('/[\/\\\\]admincp\.php$/i', $_PBENV['PHP_SELF'])) && exit('Access Denied');

define('PB_PAGE', 'cate');

require_once PBDIGG_ROOT.'include/module.class.php';
$module = new module();

require_once PBDIGG_ROOT.'include/cate.func.php';

$cid = (int)$cid;

if ($job == 'add')
{
	if (isPost())
	{
		$uploadicon = $ttype = array();
		$icon = $depth = $withchild = '';

		$displayorder = $cate['displayorder'] > 0 ? (int)$cate['displayorder'] : 0;
		$status = $cate['status'] ? 1 : 0;
		$anonymity = $cate['anonymity'] ? 1 : 0;
		$listtype = $cate['listtype'] ? 1 : 0;
		$listnum = (int)$cate['listnum'];

		$cup = (int)$cate['cup'];
		if ($cup)
		{
			$dbcup = $DB->fetch_one("SELECT cid, depth FROM {$db_prefix}categories WHERE cid = '$cup'");
			!$dbcup && showMsg('cate_cup_noexists');
			$depth = (int)$dbcup['depth'] + 1;
			$withchild = $cup;
		}

		!isset($cate['ttype']) && showMsg('cate_chose_ttype');
		$moduleArray = $module->getModuleMenu();
		foreach ($cate['ttype'] as $v)
		{
			isset($moduleArray[$v]) && $ttype[] = (int)$v;
		}
		$listtype && count($ttype) > 1 && showMsg('cate_list_type');
		$ttype = $ttype ? implode("\t", $ttype) : '';

		//cate name don't support html code
		$name = HConvert(trim($cate['name']));
		!checkCateName($name) && showMsg('cate_illegal_catename');

		$dir = strtolower($cate['dir']);
		if ($dir !== '' && $dir != $pb_chtmldir)
		{
			(preg_replace('~[_a-z0-9]~i', '', $dir) || strlen($dir) > 30 || in_array($dir, $option_message['systemdir'])) && showMsg('cate_illegal_dir');
			$DB->fetch_first("SELECT COUNT(*) num FROM {$db_prefix}categories WHERE dir = '$dir'") && showMsg('cate_dir_exists');
		}
		else
		{
			$dir = '';
		}
		
		$style = $cate['style'];
		(preg_replace('~[_a-z0-9]~i', '', $style) || !is_dir(PBDIGG_ROOT.'templates/'.$style)) && showMsg('cate_illegal_style');

		$icon = ($cate['icon'] && isImg(PBDIGG_ROOT.'images/cate/'.$cate['icon'])) ? HConvert($cate['icon']) : '';

		$cover = $cate['cover'];
		$cover && (preg_replace('~[_a-z0-9]~i', '', $cover) || strlen($cover) > 30 || !file_exists(PBDIGG_ROOT.'templates/'.$style.'/'.$cover.'.html') || !file_exists(PBDIGG_ROOT.'templates/'.$pb_style.'/'.$cover.'.html')) && showMsg('cate_illegal_cover');

		$template = $cate['template'];
		$template && (preg_replace('~[_a-z0-9]~i', '', $template) || strlen($template) > 30 || !file_exists(PBDIGG_ROOT.'templates/'.$style.'/'.$template.'.html') || !file_exists(PBDIGG_ROOT.'templates/'.$pb_style.'/'.$template.'.html')) && showMsg('cate_illegal_template');

		//filter dangerous code
		$keywords = addslashes(strip_tags(str_replace('"', '&quot;', stripslashes(trim($cate['keywords'])))));
		$description = addslashes(strip_tags(str_replace('"', '&quot;', stripslashes(trim($cate['description'])))));

		$cateNum = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}categories WHERE cup = '$cup' AND name = '$name'");
		$cateNum['num'] && showMsg('cate_same_exists');
		$DB->db_exec("INSERT INTO {$db_prefix}categories (cid, cup, depth, withchild, icon, name, dir, style, keywords, description, status, anonymity, tnum, cnum, displayorder, ttype, cover, template, listtype, listnum) VALUES (NULL, '$cup', '$depth', '0', '$icon', '$name', '$dir', '$style', '$keywords', '$description', '$status', '$anonymity', 0, 0, '$displayorder', '$ttype', '$cover', '$template', '$listtype', '$listnum')");
		$cid = $DB->db_insert_id();
		$withchild && $DB->db_exec("UPDATE {$db_prefix}categories SET withchild = 1 WHERE cid = '$withchild'");

		$Cache->categories();
		$Cache->singlecate($cid);

		PDel(PBDIGG_ROOT.'data/cache/cache_cate_option.php');
		PDel(PBDIGG_ROOT.'data/cache/cache_cate_table.php');
		redirect('cate_add_success', $basename);
	}
	else
	{
		$option = '<select name="cate[cup]"><option value="0" selected="selected">'.$cp_message['cate_topcate'].'</option>';
		$_cateOption = $ttype = '';
		if (@include_once PBDIGG_ROOT.'data/cache/cache_cate_option.php')
		{
			$option .= $_cateOption;
		}
		else
		{
			cateOption($_categories, $_cateOption);
			$option .= $_cateOption;
			$Cache->writeCache('cate_option', '$_cateOption = \''.addcslashes($_cateOption, '\\\'').'\'');
		}
		$option .= '</select>';
		
		$cateStyle = siteStyle('', 'cate[style]', TRUE);

		$cate = array();
		$cate['status_1'] = $cate['anonymity_1'] = $cate['listtype_0'] ='checked="checked"';
		//模型绑定
		$moduleArray = $module->getModuleMenu();
		foreach($moduleArray as $k => $v)
		{
			$ttype .= '<input type="checkbox" value="'.$k.'" name="cate[ttype][]" checked="checked" />'.($v[2] ? $v[1] : '<span class="r">'.$v[1].'</span>').'&nbsp;';
		}
	}
}
elseif ($job == 'edit')
{
	if (isPost())
	{
		//update cate display order
		positiveInteger($cate);
		foreach ($cate as $key => $value)
		{
			$DB->db_query("UPDATE {$db_prefix}categories SET displayorder = '$value' WHERE cid = '$key'");
		}
		$Cache->categories();
		PDel(PBDIGG_ROOT.'data/cache/cache_cate_option.php');
		PDel(PBDIGG_ROOT.'data/cache/cache_cate_table.php');
		redirect('cate_update_success', $basename);
	}
	$_cateTable = '';
	if (!@include_once(PBDIGG_ROOT.'data/cache/cache_cate_table.php'))
	{
		cateTable($_categories, $_cateTable, 0, 0);
		$Cache->writeCache('cate_table', '$_cateTable = \''.addcslashes($_cateTable, '\\\'').'\'');
	}
}
elseif ($job == 'del')
{
	//del cate
	if (isPost())
	{
		if ($rt = delCate($cid)) showMsg($rt, 'admincp.php?action=cate&job=edit');
		redirect('cate_del_success', 'admincp.php?action=cate&job=edit');
	}
}
elseif ($job == 'status')
{
	//open or close cate
	$status = (int)$status;

	if ($cid <= 0) showMsg('admin_illegal_parameter', 'admincp.php?action=cate&job=edit');
	if ($status)
	{
		$db_status = 0;
		$subCate = array();
		subCate($_categories, $subCate, $startID = $cid);
		$cid .= ','.implode(',', $subCate);
		if ($cid{strlen($cid)-1} == ',')
		{
			$cid = substr($cid, 0 , -1);
		}
	}
	else
	{
		$db_status = 1;
	}
	$DB->db_exec("UPDATE {$db_prefix}categories SET status = $db_status WHERE cid IN ($cid)");

	$Cache->categories();

	PDel(PBDIGG_ROOT.'data/cache/cache_cate_table.php');

	redirect('cate_update_success', 'admincp.php?action=cate&job=edit');
}
elseif ($job == 'mod')
{
	//change cate attribute

	$oldcate = array();

	$oldcate = $DB->fetch_one("SELECT * FROM {$db_prefix}categories WHERE cid = '$cid'");
	!$oldcate && showMsg('cate_illegal_cid', 'admincp.php?action=cate&job=edit');

	if (isPost())
	{
		$icon = $depthdiff = $sql = '';
		$ttype = array();
		$depth = 0;

		$sql .= ($cate['displayorder'] > 0 && $oldcate['displayorder'] != $cate['displayorder']) ? ',displayorder='.(int)$cate['displayorder'] : '';
		$sql .= ($cate['status'] ^ $oldcate['status']) ? ',status='.($cate['status'] ? 1 : 0) : '';
		$sql .= ($cate['anonymity'] ^ $oldcate['anonymity']) ? ',anonymity='.($cate['anonymity'] ? 1 : 0) : '';
		$sql .= ($cate['listtype'] ^ $oldcate['listtype']) ? ',listtype='.($cate['listtype'] ? 1 : 0) : '';
		$cate['listnum'] != $oldcate['listnum'] && $sql .= ',listnum='.intval($cate['listnum']);

		!isset($cate['ttype']) && showMsg('cate_chose_ttype');
		$moduleArray = $module->getModuleMenu();
		foreach ($cate['ttype'] as $v)
		{
			isset($moduleArray[$v]) && $ttype[] = (int)$v;
		}
		$cate['listtype'] && count($ttype) > 1 && showMsg('cate_list_type');
		$ttype = $ttype ? implode("\t", $ttype) : '';

		$sql .= ($ttype != $oldcate['ttype']) ? ",ttype='$ttype'" : '';

		//cate name don't support html code
		$name = HConvert(trim($cate['name']));
		!checkCateName($name) && showMsg('cate_illegal_catename');
		stripslashes($name) != $oldcate['name'] && $sql .= ",name='$name'";

		$cup = (int)$cate['cup'];

		$DB->fetch_one("SELECT cid FROM {$db_prefix}categories WHERE cup = '$cup' AND name = '$name' AND cid <> '$cid'") && showMsg('cate_same_exists');
		
		if ($cup != $oldcate['cup'])
		{
			if ($cup)
			{
				$dbcup = $DB->fetch_one("SELECT cid,depth FROM {$db_prefix}categories WHERE cid = '$cup'");
				!$dbcup && showMsg('cate_cup_noexists');
				$depth = (int)$dbcup['depth'] + 1;
				$DB->db_exec("UPDATE {$db_prefix}categories SET withchild = 1 WHERE cid = '$cup'");
			}
			//depth start from 0
			$sql .= ',cup='.$cup.',depth='.$depth;
			$depthdiff = (int)$depth - $oldcate['depth'];
			//更新是否存在子分类
			if ($oldcate['cup'])
			{
				$num = $DB->fetch_first("SELECT COUNT(*) AS num FROM {$db_prefix}categories WHERE cup = '".$oldcate['cup']."' AND cid <> '$cid'");
				!$num && $DB->db_exec("UPDATE {$db_prefix}categories SET withchild = 0 WHERE cid = '".$oldcate['cup']."'");
			}
		}

		$dir = strtolower($cate['dir']);
		if ($dir != $oldcate['dir'])
		{
			(preg_replace('~[_a-z0-9]~i', '', $dir) || strlen($dir) > 30 || in_array($dir, $option_message['systemdir'])) && showMsg('cate_illegal_dir');
			($dir !== '') && $DB->fetch_first("SELECT COUNT(*) num FROM {$db_prefix}categories WHERE dir = '$dir' AND cid <> '$cid'") && showMsg('cate_dir_exists');
			$sql .= ",dir='$dir'";
		}

		$style = $cate['style'];
		if ($style != $oldcate['style'])
		{
			(preg_replace('~[_a-z0-9]~i', '', $style) || !is_dir(PBDIGG_ROOT.'templates/'.$style)) && showMsg('cate_illegal_style');
			$sql .= ",style='$style'";
		}

		$icon = $cate['icon'];
		if ($icon != $oldcate['icon'])
		{
			$sql .= ",icon='".(($icon && isImg(PBDIGG_ROOT.'images/cate/'.$icon)) ? HConvert($icon) : '')."'";
		}

		$cover = $cate['cover'];
		if ($cover != $oldcate['cover'] && !preg_replace('~[_a-z0-9]~i', '', $cover) && strlen($cover) <= 30 && (file_exists(PBDIGG_ROOT.'templates/'.$style.'/'.$cover.'.html') || file_exists(PBDIGG_ROOT.'templates/'.$pb_style.'/'.$cover.'.html')))
		{
			$sql .= ",cover='$cover'";
		}

		$template = $cate['template'];
		if ($template != $oldcate['template'] && !preg_replace('~[_a-z0-9]~i', '', $template) && strlen($template) <= 30 && (file_exists(PBDIGG_ROOT.'templates/'.$style.'/'.$template.'.html') || file_exists(PBDIGG_ROOT.'templates/'.$pb_style.'/'.$template.'.html')))
		{
			$sql .= ",template='$template'";
		}

		//filter dangerous code
		stripslashes($cate['keywords']) != $oldcate['keywords'] && $sql .= ",keywords='".addslashes(strip_tags(str_replace('"', '&quot;', stripslashes(trim($cate['keywords'])))))."'";
		stripslashes($cate['description']) != $oldcate['description'] && $sql .= ",description='".addslashes(strip_tags(str_replace('"', '&quot;', stripslashes(trim($cate['description'])))))."'";

		$cateNum = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}categories WHERE cup = '$cup' AND name = '$name' AND cid <> '$cid'");
		$cateNum['num'] && showMsg('cate_same_exists');
		$sql && $DB->db_exec("UPDATE {$db_prefix}categories SET ".substr($sql, 1)." WHERE cid = '$cid'");

		$ccatedate = $_categories[$cid]['subcate'];
		$depthdiff && !empty($ccatedate) && $DB->db_exec("UPDATE {$db_prefix}categories SET depth = depth + ".$depthdiff." WHERE cid IN (".implode(',', $ccatedate).")");

		$Cache->categories();
		$Cache->singlecate($cid);

		PDel(PBDIGG_ROOT.'data/cache/cache_cate_option.php');
		PDel(PBDIGG_ROOT.'data/cache/cache_cate_table.php');
		redirect('cate_update_success', 'admincp.php?action=cate&job=edit');
	}
	else
	{
		$cate = &$oldcate;
		$option = '<select name="cate[cup]"><option value="0">'.$cp_message['cate_topcate'].'</option>';
		$cate_option = $ttype = '';
		cateOption($_categories, $cate_option, 0, $_categories[$cid]['cup']);
		$option .= $cate_option.'</select>';

		$cate['status_'.$cate['status']] = $cate['anonymity_'.$cate['anonymity']] = $cate['listtype_'.$cate['listtype']] = 'checked="checked"';

		//模型绑定
		$cate['ttype'] = explode("\t", $cate['ttype']);
		$moduleArray = $module->getModuleMenu();
		foreach($moduleArray as $k => $v)
		{
			$ttype .= '<input type="checkbox" value="'.$k.'" name="cate[ttype][]" '.(in_array($k, $cate['ttype']) ? 'checked="checked"' : '').' />'.($v[2] ? $v[1] : '<span class="r">'.$v[1].'</span>').'&nbsp;';
		}
		$cateStyle = siteStyle($cate['style'], 'cate[style]', TRUE);
		$basename .= '&cid='.$cid;
	}
}
elseif ($job == 'merge')
{
	if (isPost())
	{
		$scid = (int)$source;
		$dcid = (int)$destination;
		$scid == $dcid && showMsg('cate_merge_same');
		//if source cate has sub cate
		$rs = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}categories WHERE cup = '$scid'");
		$rs['num'] && showMsg('cate_sub_exist');
		mergeCate($scid, $dcid);

		redirect('cate_merge_success', $actionurl);
	}
	else
	{
		$source = '<select name="source">';
		$destination = '<select name="destination">';
		$option = '';
		if (@include_once PBDIGG_ROOT.'data/cache/cache_cate_option.php')
		{
			$option .= $_cateOption;
		}
		else
		{
			cateOption($_categories, $_cateOption);
			$option .= $_cateOption;
			$Cache->writeCache('cate_option', '$_cateOption = \''.addcslashes($_cateOption, '\\\'').'\'');
		}
		$source .= $option.'</select>';
		$destination .= $option.'</select>';
	}
}
else
{
	redirect('admin_illegal_parameter', 'admincp.php?action=main');
}


?>
