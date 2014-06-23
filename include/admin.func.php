<?php
/**
 * @version $Id: admin.func.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

!defined('IN_PBDIGG') && exit('Access Denied!');

/**
 * 后台登陆验证
 * 
 * @return Boolean 验证通过返回用户信息
 */
function checkLogin($username, $password)
{
	global $DB, $db_prefix, $_siteFounder;
	if (!$username || !$password) return FALSE;
	$customer = $DB->fetch_one("SELECT m.uid, m.username, m.password, m.adminid, m.groupid, a.* FROM {$db_prefix}members m INNER JOIN {$db_prefix}admingroups a USING (adminid) WHERE m.".(is_int($username) ? "uid = '$username'" : "username = '$username'"));
	if (!$customer || ($customer['adminid'] == '0') || ($customer['allowadmincp'] != '1') || (pbNewPW($customer['password']) != $password)) return FALSE;
	in_array($customer['uid'], $_siteFounder) && define('SUPERMANAGER', 1);
	return $customer;
}


/**
 * 后台操作日志
 */
function adminlog($description = '', $result = 0, $islog = 0)
{
	global $DB, $db_prefix, $timestamp, $_PBENV, $customer;
	$DB->db_exec("INSERT INTO {$db_prefix}adminlogs (uid, action, description, logdate, logip, result, islog) VALUES(".intval($customer['uid']).", '".addslashes('<span class="b">GET:</span><br />'.addslashes(htmlspecialchars(stripslashes($_SERVER['QUERY_STRING']))).'<br /><span class="g">POST:</span><br />'.addslashes(htmlspecialchars(postToGet($_POST))))."', '".addslashes(htmlspecialchars($description))."', '$timestamp', '".$_PBENV['PB_IP']."', $result, $islog)");
}

function suggestKey($len = 16)
{
	$key = 'abcdefhijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWYXZ~!@$^*<>()+-';
	$i = 0;
	$suggestKey = '';
	while ($i ++ < $len)
	{
		$suggestKey .= $key{rand(0, 73)};
	}
	return $suggestKey;
}

function getAdminHash($str)
{
	return md5($GLOBALS['pb_sitehash'].$str.$GLOBALS['_PBENV']['PB_IP'].$_SERVER['HTTP_USER_AGENT']);
}

function login()
{
	global $pb_gdcheck,$pb_adminsafecode,$db_charset,$_PBENV;
	define('PB_PAGE', 'login');
	sCookie('pb_adminsid','');
	require_once pt_fetch('login');
	PBOutPut();
}

function activeMenu()
{
	global $action, $job, $_leftmenu;

	$activeMenu = isset($_leftmenu[$action]['item'][$job]) ? $_leftmenu[$action]['item'][$job] : (isset($_leftmenu[$action]['func'][$job]) ? $_leftmenu[$action]['func'][$job] : '');
}

function stripWhite($string)
{
	return str_replace(array("\t", "\n", "\r", ' '), array('', '', '', '&nbsp;'), $string);
}

function siteStyle($style = '', $name = 'config[pb_style]', $unselected = FALSE)
{
	$option = "<select name=\"".$name."\" id=\"style\">\n";
	$unselected && $option .= "<option value=\"\"></option>\n";
	preg_replace('~[_0-9a-z]~i', '', $style) && $style = 'pbdigg';

	$path = PBDIGG_ROOT.'templates';
	if ($resource = @ opendir($path))
	{
		while (($file = readdir($resource)) !== false)
		{
			if (preg_match('~^[_a-z0-9]+?$~i', $file) && $file != 'admin')
			{
				$option .= "<option value=\"{$file}\"".($style == $file ? ' selected="selected"' : '').">{$file}</option>\r\n";
			}
		}
	}
	else
	{
		exit('Cant\'t Open Templates Directory!');
	}
	$option .= "</select>\n";
	return $option;
}

function siteLang($lang)
{
	$option = '<select name="config[pb_lang]" id="lang">';

	$path = PBDIGG_ROOT.'languages/';
	if ($resource = @ opendir($path))
	{
		while (($file = readdir($resource)) !== false)
		{
			preg_match('~^[_0-9a-z\-]+$~i', $file) && is_dir($path.$file) && $option .= "<option value=\"{$file}\"".($file == $lang ? ' selected="selected"' : '').">{$file}</option>";
		}
	}
	$option .= '</select>';
	return $option;
}

function exchange(&$var1, &$var2)
{
	if ($var1 > $var2)
	{
		$tmp = $var1;
		$var1 = $var2;
		$var2 = $tmp;
	}
}

/**
 * convert shorthand notation into bytes
 */
function ini_bytes($val)
{
    $val = trim(@ini_get($val));
    $last = strtolower($val{strlen($val)-1});
    switch($last)
    {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

function positiveInteger(&$var)
{
	if (is_array($var))
	{
		foreach ($var as $key => $value)
		{
			 positiveInteger($var[$key]);
		}
	}
	elseif ($var < 0)
	{
		$var = 0;
	}
	else
	{
		$var = (int)$var;
	}
}
/**
 * 分类管理表格
 * 
 * @param Array $cate 分类数组
 * @param String $table 分类管理表格
 * @param Int $startID 分类开始ID，如从顶级分类开始则为0
 * @param Int $index 选择分类ID
 * @param Int $level 分类层次
 */
function cateTable($cate, &$table, $startID = 0, $level = 0)
{
	global $cp_message;
	$lang[0] = '<span class="r">'.$cp_message['close'].'</font>';
	$lang[1] = $cp_message['open'];
	$status[0] = $cp_message['open'];
	$status[1] = $cp_message['close'];
	foreach ($cate as $key => $value)
	{
		if ($value['cup'] == $startID)
		{
			$table .= '<tr>';
			$table .= '<td>'.$value['cid'].'</td>';
			$table .= '<td style="text-align:left;text-indent:'.$level.'em">|-&nbsp;'.$value['name'].'</td>';
			$table .= '<td>'.$lang[$value['status']].'</td>';
			$table .= '<td><input class="input" type="text" name="cate['.$value['cid'].']" value="'.$value['displayorder'].'" size="5" /></td>';
			$table .= '<td><a href="admincp.php?action=cate&job=status&status='.$value['status'].'&cid='.$value['cid'].'"><img src="../templates/admin/images/status_'.$value['status'].'.gif" alt="'.$status[$value['status']].'" /></span>&nbsp;&nbsp;<a href="admincp.php?action=cate&job=del&cid='.$value['cid'].'"><img src="../templates/admin/images/del.gif" alt="'.$cp_message['del'].'" /></a>&nbsp;&nbsp;<a href="admincp.php?action=cate&job=mod&cid='.$value['cid'].'"><img src="../templates/admin/images/edit.gif" alt="'.$cp_message['edit'].'" /></a></td>';
			$table .= '</tr>';
			cateTable($cate, $table, $value['cid'], $level + 1);
		}
	}
}

function accessIPControl($IP)
{
	global $pb_adminipallow;
	$ipallow = unserialize($pb_adminipallow);
	if (!$ipallow)
	{
		return TRUE;
	}
	$ipallow = explode("\n", $ipallow);
	foreach ($ipallow as $value)
	{
		$pattern = "/^{$value}[\d\.]*?$/i";
		if (preg_match($pattern, $IP))
		{
			return TRUE;
		}
	}
	return FALSE;
}

function timespanFormat($seconds)
{
	$days = floor($seconds / 86400);
	if ($days > 0)
	{
		$seconds -= $days * 86400;
	}
	$hours = floor($seconds / 3600);
	if ($days > 0 || $hours > 0)
	{
		$seconds -= $hours * 3600;
	}
	$minutes = floor($seconds / 60);
	if ($days > 0 || $hours > 0 || $minutes > 0)
	{
		$seconds -= $minutes * 60;
	}
	return sprintf($GLOBALS['cp_message']['database_timeformat'], (string) $days, (string) $hours, (string) $minutes, (string) $seconds);
}

function getRealSize($size)
{
	if ($size < 1024)
	{
		return $size.' Byte';
	}
	if ($size < 1048576)
	{
		return round($size / 1024, 2).' KB';
	}
	if ($size < 1073741824)
	{
		return round($size / 1048576, 2).' MB';
	}
	if ($size < 1099511627776)
	{
		return round($size / 1073741824, 2).' GB';
	}
}

function checkWritable($var)
{
	$writeable = FALSE;
	if (is_dir($var) || @mkdir($var, 0777))
	{
		$var .= '/temp.php';
		if (($fp = @fopen($var, 'w')) && fwrite($fp, 'pbdigg'))
		{
			fclose($fp);
			@unlink($var);
			$writeable = TRUE;
		}
	}
	return $writeable;	
}
function dirPermission($perm)
{
	$rperm = array();
	if ($perm && is_array($perm))
	{
		foreach ($perm as $value)
		{
			$path = PBDIGG_ROOT.$value;
			$subpath = array();
			if ($dh = opendir($path))
			{
				while (($file = readdir($dh)) !== FALSE)
				{
					$file != '..' && $file != '.' && is_dir($path.'/'.$file) && $subpath[] = $value.'/'.$file;
				}
				closedir($dh);
			}
			$subpath && $rperm = array_merge($rperm, dirPermission($subpath));
			$rperm[$value] = checkWritable($path) ? '<span class="g">Pass</span>' : '<span class="r">Failed</span>';
		}
	}
	return $rperm;
}

function cpmultLink($currentPage, $totalRecords, $url, $pageSize = 30)
{
	if ($totalRecords <= $pageSize) return '';
	$pagenum = ceil($totalRecords / $pageSize);
	$currentPage = ($currentPage < 1) ? 1 : ($currentPage > $pagenum ? $pagenum : (int)$currentPage);
	$pages = '<div class="pages"><a href="'.$url.'page=1" style="font-weight:bold">&laquo;</a>';
	$flag = 0;
	for ($i = $currentPage - 3; $i <= $currentPage - 1; $i++)
	{
		if ($i < 1) continue;
		$pages .= '<a href="'.$url.'page='.$i.'">'.$i.'</a>';
	}
	$pages .= "<strong> $currentPage </strong>";
	if($currentPage < $pagenum)
	{
		for ($i =  $currentPage + 1; $i <= $pagenum; $i++)
		{
			$pages .= '<a href="'.$url.'page='.$i.'">'.$i.'</a>';
			$flag ++;
			if ($flag == 4) break;
		}
		if ($pagenum > $currentPage + 4) $pages .= '<a href="'.$url.'page='.$pagenum.'" style="font-weight:bold">&raquo;</a>';
	}
	return $pages;
}

function pwritable($var, $del = FALSE)
{
	$writeable = $mkdir = '';
	if(!is_dir($var))
	{
		@mkdir($var, 0777);
		$mkdir = 1;
	}
	if (is_dir($var))
	{
		$var = rtrim($var,'/').'/index.html';
		if (($fp = @fopen($var, 'wb')) && (fwrite($fp, "\n")))
		{
			fclose($fp);
			$del && @unlink($var) && $mkdir && PDelDir($var);
			$writeable = TRUE;
		}
	}
	return $writeable;
}

function postToGet($input, $pre = '', $quote = '')
{
	$output = '';

	foreach ($input as $k => $v)
	{
		if (is_array($v))
		{
			$output .= postToGet($v, $pre.($quote ? '['.$k.']' : $k), true);
		}
		else
		{
			$output .= $pre.($quote ? '['.$k.']' : $k).'='.stripslashes($v).'&';
		}
	}
	return $output;
}

function installSQL($sql)
{
	global $db_charset, $DB, $db_prefix;
	$queriesarray = explode(";\n", trim(str_replace("\r", "\n", $sql)));
	unset($sql);
	foreach($queriesarray as $query)
	{
		$queries = explode("\n", trim($query));
		$sql = '';
		foreach($queries as $query)
		{
			$query{0} != '#' && $sql .= $query;
		}
		if($sql)
		{
			if(preg_match('~^CREATE~', $sql))
			{
				$extra = substr($sql, strrpos($sql,')') + 1);
				$tabletype = substr($extra, strpos($extra,'=') + 1);
				$tabletype = substr($tabletype, 0, (strpos($tabletype, ' ') ? strpos($tabletype, ' ') : strlen($tabletype)));
				$tabletype{strlen($tabletype)-1} == ';' && $tabletype = substr($tabletype, 0, -1);
				$sql = str_replace($extra, '', $sql);
				strpos($db_charset, '-') !== FALSE && $db_charset = str_replace('-', '', $db_charset);
				$extra = $DB->db_version() > '4.1' ? ($db_charset ? "ENGINE=$tabletype DEFAULT CHARSET=$db_charset;" : "ENGINE=$tabletype;") : "TYPE=$tabletype;";
				$sql .= $extra;
			}
			$db_prefix != 'pb_' && $sql = preg_replace('~^(INSERT|CREATE|DROP|DELETE)(.+?)(pb_)(.+)~ies', "replacePrefix('\\1','\\2','\\3','\\4')", $sql);
			$DB->db_exec($sql, FALSE);
		}
	}
}
function replacePrefix($action, $front, $prefix, $end)
{
	return $action.$front.str_replace($prefix, $GLOBALS['db_prefix'], $prefix).str_replace('\\"', '"', $end);
}
function getOpeCode($var, $ifempty = false)
{
	return  $var == '3' ? '' : ($ifempty ? ($var ? '<>' : '=') : '=');
}
function getZoneCode($var)
{
	return $var == 1 ? '<' : '>';
}
function getInCode($var)
{
	return $var ? 1 : 0;
}
function tplMark($name, $pre)
{
	global $DB, $db_prefix;
	while (true)
	{
		$tplmark = ($pre ? 'self_' : 'tpl_').substr(md5($name.random(8)), 0, 8);
		$rs = $DB->fetch_one("SELECT COUNT(*) num FROM {$db_prefix}templates WHERE tplmark = '$tplmark'");
		if (!$rs['num']) break;
	}
	return $tplmark;
}
function compileFieldsData($type, &$expandSpecialFieldData, &$tablesRelation)
{
	switch ($type)
	{
		case 't':
			$fieldsData = array(
				't.tid' => 't.tid',
				't.subject' => array('t.subject','t.titlecolor','t.titlestyle'),
				't.turl' => array('t.tid','t.realurl'),
				't.summary' => 't.summary',
				't.topicimg' => array('t.tid', 't.topicimg'),
				't.digg' => 't.digg',
				't.bury' => 't.bury',
				't.comments' => 't.comments',
				't.views' => 't.views',
				't.postdate' => 't.postdate',
				't.author' => 't.author',
				't.avatar' => array('m.avatar','m.ucuid'),
				't.uurl' => array('m.uid','m.ucuid'),
				't.altsubject' => 't.subject',
				't.commendpic' => 't.commendpic',
				't.contentlink' => 't.contentlink',
				't.cid' => 't.cid',
				't.curl' => 't.cid',
				't.cname' => 't.cid',
			);
			//模块接口
			global $module;
			$moduleConfig = $module->getModuleConfig();
			foreach ($moduleConfig as $k => $v)
			{
				if (!empty($v['fields']))
				{
					foreach ($v['fields'] as $kk => $vv)
					{
						$fieldsData[$kk] = $vv[1];
					}
				}
				if (!empty($v['specialFields']))
				{
					$expandSpecialFieldData = array_merge($expandSpecialFieldData, $v['specialFields']);
				}
				if (!empty($v['tablesRelation']))
				{
					$tablesRelation = array_merge($tablesRelation, $v['tablesRelation']);
				}
			}
			break;
		case 'r':
			$fieldsData = array(
				'r.rid' => 'r.rid',
				'r.subject' => array('t.subject','t.titlecolor','t.titlestyle'),
				'r.altsubject' => 't.subject',
				'r.turl' => array('t.tid','t.realurl'),
				'r.content' => 'r.content',
				'r.postdate' => 'r.postdate',
				'r.digg' => 'r.digg',
				'r.bury' => 'r.bury',
				'r.author' => 'r.author',
				'r.avatar' => array('m.avatar','m.ucuid'),
				'r.uurl' => array('m.uid','m.ucuid'),
				'r.cid' => 'r.cid',
				'r.curl' => 'r.cid',
				'r.cname' => 'r.cid',
			);
			break;
		case 'm':
			$fieldsData = array(
				'm.uid' => 'm.uid',
				'm.username' => 'm.username',
				'm.uurl' => array('m.uid','m.ucuid'),
				'm.postnum' => 'm.postnum',
				'm.commentnum' => 'm.commentnum',
				'm.diggnum' => 'm.diggnum',
				'm.burynum' => 'm.burynum',
				'm.uploadnum' => 'm.uploadnum',
				'm.friendnum' => 'm.friendnum',
				'm.collectionnum' => 'm.collectionnum',
				'm.visitnum' => 'm.visitnum',
				'm.ucuid' => 'm.ucuid',
				'm.gender' => 'm.gender',
				'm.avatar' => array('m.avatar','m.ucuid'),
				'mx.qq' => 'mx.qq',
				'mx.msn' => 'mx.msn',
				'mx.site' => 'mx.site',
				'mx.location' => 'mx.location',
				'mx.birthday' => 'mx.birthday',
				'mx.signature' => array('mx.signature','mx.showsign','mx.ctsig'),
			);
			break;
	}
	return $fieldsData;
}
function compileSQL($type = 't', $template, $trantattribute, $order, $orderby, $trantnum)
{
	global $db_prefix;

	$realtables = array('t'=>'threads','r'=>'comments','m'=>'members');
	if (!array_key_exists($type, $realtables)) return;
	$table = $realtables[$type];

	preg_match_all('~{!--([_0-9a-z\.]+?)--}~i', $template, $tplMatchFields, PREG_SET_ORDER);

	//内置特殊字段
	$builtInSpecialFieldData = array('turl','curl','uurl','postdate','avatar','cname','topicimg','summary','subject','altsubject','content','regdate','gender','signature');
	//扩展特殊字段
	$expandSpecialFieldData = array();

	$tablesRelation = array(
		't' => array('threads','tid'),
		'm' => array('members','uid'),
		'mx' => array('memberexp','uid'),
		'c' => array('categories','cid'),
		'r' => array('comments','rid'),
	);

	$fieldsData = compileFieldsData($type, $expandSpecialFieldData, $tablesRelation);
	
	$tables = $fields = $specialFields = $replaceFields = array();

	foreach ($tplMatchFields as $v)
	{
		if (array_key_exists($v[1], $fieldsData))
		{
			!in_array($v[1], $replaceFields) && $replaceFields[] = $v[1];//替换字段
			$cf = $fieldsData[$v[1]];
			if (is_array($cf))
			{
				foreach ($cf as $sv)
				{
					$svct = substr($sv, 0, strpos($sv, '.'));
					$svct != $type && !in_array($svct, $tables) && $tables[] = $svct;
					!in_array($sv, $fields) && $fields[] = $sv;
				}
			}
			else
			{
				$ct = substr($cf, 0, strpos($cf, '.'));
				$ct != $type && !in_array($ct, $tables) && $tables[] = $ct;
				!in_array($fieldsData[$v[1]], $fields) && $fields[] = $fieldsData[$v[1]];
			}
			list($prefix, $suffix) = explode('.', $v[1]);			
			(in_array($prefix, array('t','m','mx','r','c')) && in_array($suffix, $builtInSpecialFieldData) || in_array($v[1], $expandSpecialFieldData)) && !in_array($v[1], $specialFields) && $specialFields[] = $v[1];
		}
	}
	$outFields = '';
	foreach ($fields as $v)
	{
		$outFields .= ($outFields ? ',' : '').$v.' AS `'.$v.'`';
	}
	$sqltable = $sqlcondition = $sqlorder = $querysql = '';
	if ($outFields)
	{
		//关联表
		$pretype = $type;
		foreach ($tables as $t)
		{
			isset($tablesRelation[$t]) && $sqltable .= ' LEFT JOIN `'.$db_prefix.$tablesRelation[$t][0].'` '.$t.' ON '.$pretype.'.'.$tablesRelation[$t][1].' = '.$t.'.'.$tablesRelation[$t][1].' ';
			$pretype = $t;
		}
		//条件
		foreach ($trantattribute as $k => $v)
		{
			$v[0] && $v[$v[0]] && $sqlcondition .= $type.'.'.$k.' '.$v[1].$v[++$v[0]].' AND ';
		}

		$sqlcondition && $sqlcondition = substr($sqlcondition, 0, strrpos(trim($sqlcondition), ' '));

		//排序
		$orderby &&	$sqlorder = ' ORDER BY '.$type.'.'.$order.' '.$orderby;
		$sqlorder .= $trantnum ? ' LIMIT '.$trantnum : '';
		$querysql = "SELECT $outFields FROM `{$db_prefix}{$table}` $type $sqltable ".($sqlcondition ? " WHERE ".($trantattribute['self'][1] ? $type.'.cid = {#cid#} AND ' : '')." $sqlcondition " : '').$sqlorder;
	}

	return array($querysql, implode(',', $fields), implode(',', $specialFields), implode(',', $replaceFields));
}
?>
