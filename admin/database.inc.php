<?php
/**
 * @version $Id: database.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

(!defined('IN_ADMIN') || !isset($_PBENV['PHP_SELF']) || !preg_match('/[\/\\\\]admincp\.php$/i', $_PBENV['PHP_SELF'])) && exit('Access Denied');

define('PB_PAGE', 'database');

require_once (PBDIGG_ROOT.'include/table.inc.php');

if ($job == 'status')
{
	$database = $result = array();
	if($db_prefix != 'pb_')
	{
		foreach($pbtables as $key => $value)
		{
			$pbtables[$key] = str_replace('pb_', $db_prefix, $value);
		}
	}
	$database['version'] = $DB->db_version();
	$query = $DB->db_query('SHOW STATUS');
	while ($rs = $DB->fetch_all($query))
	{
		if ((strpos(strtolower($rs['Variable_name']), 'uptime')) !== FALSE)
		{
			$database['runtime'] = timespanFormat($rs['Value']);
			break;
		}
	}
	$query = $DB->db_query('SHOW TABLE STATUS');
	$pbtable = $othertable = $result = array();
	$pbtablenum = $pbrownum = $pbtotalsize = $othertablenum = $otherrownum = $othertotalsize = 0;
	while ($rs = $DB->fetch_all($query))
	{
		if (in_array($rs['Name'], $pbtables))
		{
			$pbrownum += $rs['Rows'];
			$pbtotalsize += ($rs['Data_length'] + $rs['Index_length'] + $rs['Data_free']);
			$rs['Type'] = isset($rs['Type']) ? $rs['Type'] : $rs['Engine'];
			$pbtablenum++;
			$pbtable[] = array($rs['Name'], $rs['Type'], $rs['Rows'], getRealSize($rs['Data_length']), getRealSize($rs['Data_free']));
		}
		else
		{
			$otherrownum += $rs['Rows'];
			$othertotalsize += ($rs['Data_length'] + $rs['Index_length'] + $rs['Data_free']);
			$rs['Type'] = isset($rs['Type']) ? $rs['Type'] : $rs['Engine'];
			$othertablenum++;
			$othertable[] = array($rs['Name'], $rs['Type'], $rs['Rows'], getRealSize($rs['Data_length']), getRealSize($rs['Data_free']));
		}
	}
	$pbtotalsize = getRealSize($pbtotalsize);
	$othertotalsize = getRealSize($othertotalsize);
}
elseif ($job == 'export')
{
	if ($ispost == 'on')
	{
		@set_time_limit(0);
		intConvert(array('volume','tableid','start','sizelimit'));

		!$filename && $filename = $db_prefix.gdate($timestamp, 'YmdHis').'_'.random(8);
		!$tablename && showMsg('database_chose_table');

		$volume++;
		$sqlfilename = "dbak/{$filename}_{$volume}.sql";

		if ($volume == 1)
		{
			$DB->db_query("TRUNCATE TABLE {$db_prefix}scaches");
			$tablename = implode('|', $tablename);
		}
		$sqldump = '';
		!$sizelimit && $sizelimit = ini_bytes('upload_max_filesize') < 2097152 ? ini_bytes('upload_max_filesize') : 2097152;
		$tables = explode('|', $tablename);
		$currentTable = $tables[$tableid];

		$sqldump = sqldumptable($tableid, $start);

		if(trim($sqldump))
		{
			$sqldump = "# PBDigg Database Backup File Vol.$volume\n# Version: ".$_PBENV['VERSION']."\n# Time: ".gdate($timestamp)."\n# PBDigg: http://www.pbdigg.com\n\n/*---------- SQL Dump Start ----------*/\n\n\n".$sqldump;
			if (PWriteFile($sqlfilename, $sqldump, 'wb'))
			{
				redirect('database_bak_process', 'admincp.php?action=database&job=export&ispost=on&filename='.rawurlencode($filename).'&sizelimit='.$sizelimit.'&volume='.$volume.'&tableid='.$tableid.'&start='.$start.'&tablename='.rawurlencode($tablename), 1);
			}
			else
			{
				showMsg('database_bak_failed', $basename);
			}
		}
		else
		{
			redirect('database_bak_success', $basename);
		}
	}
	$query = $DB->db_query('SHOW TABLE STATUS');
	$pbtable = $othertable = array();
	while ($rs = $DB->fetch_all($query))
	{
		if (in_array(str_replace($db_prefix, 'pb_', $rs['Name']), $pbtables))
		{
			$pbtable[] = array($rs['Name'], $rs['Rows'], getRealSize($rs['Data_length']));
		}
		else
		{
			$othertable[] = array($rs['Name'], $rs['Rows'], getRealSize($rs['Data_length']));
		}
	}
}
elseif ($job == 'import')
{
	if ($ispost == 'on')
	{
		@set_time_limit(0);
		intConvert(array('volume'));
		if (isPost())
		{
			!preg_match('~^[0-9a-z_]+\.sql$~i', $sqlfile) && showMsg('database_illegal_sqlfile');
			$filedetail = explode('_', $sqlfile);
			$filedetail[0].'_' != $db_prefix && showMsg('database_prefix_notsame');
			$backuptime = $filedetail[1];
			$identifier = $filedetail[2];
			$volume = 1;
		}
		$sqlfile = $db_prefix.$backuptime.'_'.$identifier.'_'.$volume.'.sql';
		$file = PBDIGG_CP.'dbak/'.$sqlfile;
		$sqldump = '';
		if (file_exists($file) && ($fp = fopen($file, 'rb')))
		{
			$bakinfo = fread($fp, 180);
			$detail = explode("\n", substr($bakinfo, 0, strpos($bakinfo, "\n\n\n")));
			trim(substr($detail[1], strrpos($detail[1],' '))) != $_PBENV['VERSION'] && showMsg('database_version_error');
			rewind($fp);
			$sqldump .= fread($fp, filesize($file));
			fclose($fp);
		}
		elseif ($process)
		{
			$Cache->config();
			redirect('database_import_success', 'admincp.php?action=tool&job=cache');
		}
		else
		{
			showMsg('database_file_noexist');
		}
		$sqlquery = splitsql($sqldump);
		unset($sqldump);
		foreach($sqlquery as $sql)
		{
			if($sql)
			{
				if(preg_match('~^CREATE~', $sql))
				{
					$extra = substr($sql, strrpos($sql,')') + 1);
					$tabletype = substr($extra, strpos($extra,'=') + 1);
					$tabletype = substr($tabletype, 0, (strpos($tabletype, ' ') ? strpos($tabletype, ' ') : strlen($tabletype)));
					$sql = str_replace($extra, '', $sql);
					strpos($db_charset, '-') !== FALSE && $db_charset = str_replace('-', '', $db_charset);
					$extra = $DB->db_version() > '4.1' ? ($db_charset ? "ENGINE=$tabletype DEFAULT CHARSET=$db_charset;" : "ENGINE=$tabletype;") : "TYPE=$tabletype;";
					$sql .= $extra;
				}
				$DB->db_exec($sql, FALSE);
			}
		}
		$nextvolume = $volume + 1;
		redirect('database_import_process', 'admincp.php?action=database&job=import&ispost=on&process=yes&backuptime='.$backuptime.'&identifier='.$identifier.'&volume='.$nextvolume, 1);
	}
	//list bak file
	$backupfile = $bk = array();
	$handle = opendir(PBDIGG_CP.'dbak');
	$filenum = 0;
	while (($file = readdir($handle)) !== FALSE)
	{
		if (preg_match('~^'.$db_prefix."\d{14}_[0-9a-z]{8}_(\d+)\.sql$~i", $file, $m))
		{
			$fp = @fopen(PBDIGG_CP.'dbak/'.$file, 'rb');
			$info = fread($fp, 180);
			fclose($fp);
			$detail = explode("\n", substr($info, 0, strpos($info, "\n\n\n")));
			$backupfile[]= array('filename'=>$file,'version'=>trim(substr($detail[1], strrpos($detail[1],' '))),'date'=>substr($detail[2], 8),'volume'=>$m[1]);
		}
	}
	$checkSubmit = 'onsubmit="return checkDel();"';
}
elseif ($job == 'optimize')
{
	if (isPost())
	{
		@set_time_limit(0);
		if($db_prefix != 'pb_')
		{
			foreach($pbtables as $key => $value)
			{
				$pbtables[$key] = str_replace('pb_', $db_prefix, $value);
			}
		}
		$optDB = implode(',', $pbtables);
		if (isset($repair))
		{
			$DB->db_query("REPAIR TABLE $optDB EXTENDED");
		}
		if (isset($optimize))
		{
			$DB->db_query("OPTIMIZE TABLE $optDB");
		}
		redirect('database_optimize_success', $basename);
	}
}
elseif ($job == 'checksqlfile')
{
	!preg_match('~^[0-9a-z_]+\.sql$~i', $sqlfile) && showMsg('database_illegal_sqlfile');

	$filedetail = explode('_', $sqlfile);
	$filedetail[0].'_' != $db_prefix && showMsg('database_prefix_notsame');
	$volume = (int)$filedetail[3];

	$sqlfilepath = PBDIGG_CP.'dbak/'.$sqlfile;
	if (file_exists($sqlfilepath) && ($fp = @fopen($sqlfilepath, 'rb')))
	{
		$info = fread($fp, 180);
		$detail = explode("\n", substr($info, 0, strpos($info, "\n\n\n")));
		fclose($fp);
		substr($detail[0], strrpos($detail[0], '.') + 1) != $volume && showMsg('admin_illegal_parameter');
		trim(substr($detail[1], strrpos($detail[1],' '))) != $_PBENV['VERSION'] && showMsg('database_version_error');
	}
	else
	{
		showMsg('database_file_noexist');
	}
}
elseif ($job == 'del' && isPost())
{
	(!$sqlfile || !is_array($sqlfile)) && showMsg('admin_illegal_parameter');
	foreach($sqlfile as $value)
	{
		if (preg_match('~^[a-z0-9_]+\.sql$~i', $value))
		{
			PDel(PBDIGG_CP."dbak/$value");
		}
	}
	redirect('database_delfile_success', 'admincp.php?action=database&job=import');
}

function sqldumptable(&$tableid, &$start, $limit = 300)
{
	global $DB, $sizelimit, $tables;

	//数据余量
	$sizelimit -= 300;
	$tabledump = '';
	for($count = count($tables); $tableid < $count && strlen($tabledump) < $sizelimit; $tableid++)
	{
		$table = $tables[$tableid];
		if (!$start)
		{
			//数据表结构
			$tabledump .= "DROP TABLE IF EXISTS `$table`;\n";
			$query = $DB->db_query("SHOW CREATE TABLE $table");
			$create = $DB->fetch_row($query);
			$tmpcreate = explode("\n", str_replace("\r","\n", $create[1]));
			$newcreate = '';
			foreach ($tmpcreate as $v)
			{
				$newcreate .= trim($v);
			}
			$tabledump .= $newcreate.";\n";
		}
		$columns = $DB->fetch_one("SHOW COLUMNS FROM $table");
		while (strlen($tabledump) < $sizelimit)
		{
//			$query = $DB->db_query($columns['Extra'] == 'auto_increment' ? "SELECT * FROM $table WHERE ".$columns['Field']." >= $start AND ".$columns['Field']." < ".($start + $limit) : "SELECT * FROM $table LIMIT $start, $limit");
			$query = $DB->db_query("SELECT * FROM $table LIMIT $start, $limit");
			if (!$DB->db_num($query))
			{
				//单表导出完毕
				$start = 0;
				break;
			}
			$realrows = 0;
			$numfields = $DB->db_field_num($query);
			while ($row = $DB->fetch_row($query))
			{
				$realrows++;
				$comma = '';
				$tabledump .= "INSERT INTO `$table` VALUES (";
				for ($i = 0; $i < $numfields; $i++)
				{
					$tabledump .= $comma.'\''.mysql_escape_string($row[$i]).'\'';
					$comma = ',';
				}
				$tabledump .= ");\n";
				if (strlen($tabledump) >= $sizelimit)
				{
					//数据超过设定大小
					$start += $realrows;
					break 3;
				}
			}
			$start += $limit;
		}
	}
	return $tabledump;
}

function splitsql($sql)
{
	$return = array();
	$sql = str_replace("\r", "\n", $sql);
	$sql = explode(";\n", trim(substr($sql, strpos($sql, "\n\n\n"))));
	foreach($sql as $query)
	{
		$return[] = trim($query);
	}
	return $return;
}

?>
