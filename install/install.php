<?php
/**
 * @version $Id: install.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

function add_S(&$array)
{
	if (is_array($array))
	{
		foreach ($array as $key => $value)
		{
			 add_S($array[$key]);
		}
	}
	elseif (is_string($array))
	{
		$array = addslashes($array);
	}
}
function result($result = 1, $output = 1)
{
	if($result)
	{
		$text = '<span class="green">√</span>';
		if(!$output)
		{
			return $text;
		}
		echo $text;
	}
	else
	{
		$text = '<span class="red">×</span>';
		if(!$output)
		{
			return $text;
		}
		echo $text;
	}
}
function runquery($sql)
{
	global $newdb_charset, $db_prefix, $tablenum, $i_message;
	$sql = str_replace("\r", "\n", str_replace('`pb_', '`'.$db_prefix, $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query)
	{
		$queries = explode("\n", trim($query));
		$sq = "";
		foreach($queries as $query)
		{
			$sq .= $query;
		}
		$ret[$num] = $sq;
		$num ++;
	}
	unset($sql);
	foreach($ret as $query)
	{
		$query = trim($query);
		if($query) {
			if(substr($query, 0, 12) == 'CREATE TABLE')
			{
				$name = preg_replace("/CREATE TABLE `([a-z0-9_]+)` .*/is", "\\1", $query);
				echo '<p>'.$i_message['create_table'].' '.$name.' ... <span class="blue">OK</span></p>';
				@mysql_query(createtable($query, $newdb_charset));
				$tablenum ++;
			}
			else
			{
				@mysql_query($query);
			}
		}
	}
}
function createtable($sql, $newdb_charset)
{
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array("MYISAM", "HEAP")) ? $type : "MYISAM";
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
		(mysql_get_server_info() > "4.1" ? " ENGINE=$type DEFAULT CHARSET=$newdb_charset" : " TYPE=$type");
}
function get_ip()
{
	if (isset ($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
		$onlineip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	elseif (isset ($_SERVER['HTTP_CLIENT_IP']))
	{
		$onlineip = $_SERVER['HTTP_CLIENT_IP'];
	}
	else
	{
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}
	$onlineip = preg_match('/[\d\.]{7,15}/', addslashes($onlineip), $onlineipmatches);
	return $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
}
function pb_writable($var)
{
	$writeable = FALSE;
	$var = PBDIGG_ROOT.$var;
	if (is_file($var))
	{
		if (($fp = @fopen($var, 'w')) && @chmod($var, 0777) && (@fwrite($fp, 'pbdigg')))
		{
			@fclose($fp);
			$fp = @fopen($var, 'w');
			@fclose($fp);
			$writeable = TRUE;
		}
	}
	else
	{
		if (is_dir($var) || @mkdir($var, 0777))
		{
			$var .= '/temp.php';
			if (($fp = @fopen($var, 'w')) && @chmod($var, 0777) && (fwrite($fp, 'pbdigg')))
			{
				@fclose($fp);
				@unlink($var);
				$writeable = TRUE;
			}
		}
	}
	return $writeable;
}

function pb_random($length, $isNum = FALSE)
{
	$random = '';
	$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$num = '0123456789';
	if ($isNum)
	{
		$sequece = 'num';
	}
	else
	{
		$sequece = 'str';
	}
	$max = strlen($$sequece) - 1;
	for ($i = 0; $i < $length; $i++)
	{
		$random .= ${$sequece}{mt_rand(0, $max)};
	}
	return $random;
}

error_reporting(E_ERROR | E_WARNING | E_PARSE);

@set_magic_quotes_runtime(0);
define('PBDIGG_INSTALL', TRUE);
define('IN_PBDIGG', TRUE);
define('PBDIGG_ROOT', str_replace('\\', '/', substr(dirname(__FILE__), 0, -7)));
$PHP_SELF = addslashes(htmlspecialchars($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']));
$timestamp = time();
$ip = get_ip();
$_PBVERSION = '3.0';
$configfile = PBDIGG_ROOT.'data/sql.inc.php';
$installfile = PBDIGG_ROOT.'install/install.sql';
$tempfile = PBDIGG_ROOT.'install/temp.php';
$initfile = PBDIGG_ROOT.'install/init.sql';
$db_charset = 'utf-8';
$biz = false;

@header('Content-Type: text/html; charset='.$db_charset);

require_once PBDIGG_ROOT.'install/install_lang.php';

if (file_exists('install.lock'))
{
	exit($i_message['install_lock']);
}
if (!is_readable($installfile))
{
	exit($i_message['install_dbFile_error']);
}

if (!get_magic_quotes_gpc())
{
	add_S($_POST);
	add_S($_GET);
}
@extract($_POST);
@extract($_GET);

$quit = false;
$msg = $alert = $link = $sql = $allownext = '';

?>
<html>
<head>
<title><?php echo $i_message['install_title']; ?></title>
<meta http-equiv=Content-Type content="text/html; charset=<?php echo $db_charset;?>">
<link href="style.css" rel="stylesheet" type="text/css" />
<body>
<div id='content'>
<div id='pageheader'>
	<div id="logo"><img src="images/pbdigg.gif" width="260" height="80" border="0" alt="PBDIGG" /></div>
	<div id="version" class="rightheader">Version <?php echo $_PBVERSION; ?></div>
</div>
<div id='innercontent'>
	<h1>PBDigg <?php echo $_PBVERSION, ' ', $i_message['install_wizard']; ?></h1>
<?php
if (!$v)
{
?>
<div class="botBorder">
	<h2><?php echo $i_message['install_license_title'];?></h2>
</div>
<div class="botBorder">
	<p><span class="red"><strong><?php echo $i_message['install_tips'];?></strong></span><br /><br />
<?php echo $i_message['install_warning'];?>
	</p>
</div>
<div>
	<p>
		<textarea class="textarea" readonly="readonly" cols="50"><?php echo $i_message['install_license'];?></textarea>
	</p>
</div>
<p class="center">
	<input onclick="location.href='install.php?v=1'" type="button" class="submit" value="<?php echo $i_message['install_agree'];?>" />
</p>
<?php
}
elseif ($v == '1')
{
?>
<div class="botBorder">
	<h2><?php echo $i_message['install_check_env'];?></h2>
</div>
<div class="botBorder">
	<p><span class="red"><strong><?php echo $i_message['install_tips'];?></strong></span><br /><br />
<?php echo $i_message['install_env_tips'];?>
	</p>
</div>
<div>
<table cellpadding="3" cellspacing="1" class="t_table">
<tr> 
	<th width="25%"><strong><?php echo $i_message['install_check_env_item'];?></strong></th>
	<th width="30%"><strong><?php echo $i_message['install_check_env_needed'];?></strong></th>
	<th width="30%"><strong><?php echo $i_message['install_check_env_current'];?></strong></th>
	<th width="15%"><strong><?php echo $i_message['install_check_env_status'];?></strong></th>
</tr>
<tr> 
	<td><?php echo $i_message['php_os'];?></td>
	<td><?php echo $i_message['unlimited'];?></td>
	<td><?php echo PHP_OS;?></td>
	<td><?php echo result(1, 1);?></td>
</tr>
<tr> 
	<td><?php echo $i_message['php_version'];?></td>
	<td>4.3.3 +</td>
	<td><?php echo PHP_VERSION;?></td>
	<td>
<?php
if (PHP_VERSION < '4.3.0' || ($biz && PHP_VERSION < '5.0.0'))
{
	result(0, 1);
	$quit = TRUE;
}
else
{
	result(1, 1);
}
?>
	</td>
</tr>
<tr> 
	<td><?php echo $i_message['mysql'];?></td>
	<td><?php echo $i_message['open'];?></td>
	<td>
<?php
if (function_exists('mysql_connect'))
{
	echo $i_message['open'];
	$result = result(1, 0);
}
else
{
	echo '<span class="red">'.$i_message['close'].'</span>';
	$result = result(0, 0);
	$quit = TRUE;
}
?>
	</td>
	<td><?php echo $result;?></td>
</tr>
<tr> 
	<td><?php echo $i_message['allow_url_fopen'];?></td>
	<td><?php echo $i_message['open'];?></td>
	<td>
<?php
if (@ini_get('allow_url_fopen'))
{
	echo $i_message['open'];
	$result = result(1, 0);
}
else
{
	echo '<span class="red">'.$i_message['close'].'</span>';
	$result = result(0, 0);
}
?>
	</td>
	<td><?php echo $result;?></td>
</tr>
<tr> 
	<td><?php echo $i_message['safe_mode'];?></td>
	<td><?php echo $i_message['close'];?></td>
	<td>
<?php
if (@ini_get('safe_mode'))
{
	
	echo '<span class="red">'.$i_message['open'].'</span>';
	$result = result(0, 0);
	$quit = TRUE;
}
else
{
	echo $i_message['close'];
	$result = result(1, 0);
}
?>
	</td>
	<td><?php echo $result;?></td>
</tr>
<tr> 
	<td><?php echo $i_message['gd'];?></td>
	<td><?php echo $i_message['open'];?></td>
	<td>
<?php
if (function_exists('gd_info'))
{
	$gd = gd_info();
	echo $gd['GD Version'];
	$result = result(1, 0);
}
else
{
	echo '<span class="red">'.$i_message['close'].'</span>';
	$result = result(0, 0);
}
?>
	</td>
	<td><?php echo $result;?></td>
</tr>
<tr> 
	<td><?php echo $i_message['file_upload'];?></td>
	<td><?php echo $i_message['open'];?></td>
	<td>
<?php
if (@ini_get('file_uploads'))
{
	echo $i_message['open'],'/', @ini_get('upload_max_filesize');
	$result = result(1, 0);
}
else
{
	echo '<span class="red">'.$i_message['close'].'</span>';
	$result = result(0, 0);
}
?>
	</td>
	<td><?php echo $result;?></td>
</tr>
<tr> 
	<td><?php echo $i_message['zend_version'];?></td>
	<td><?php echo $i_message['open'];?></td>
	<td>
<?php
if (!extension_loaded('zend optimizer') || zend_optimizer_version() < '3.3.0')
{
	echo '<span class="red">'.$i_message['close'].'</span>';
	$result = result(0, 0);
	$biz && $quit = TRUE;
}
else
{
	echo zend_optimizer_version();
	$result = result(1, 0);
}
?>
	</td>
	<td><?php echo $result;?></td>
</tr>
</table>
</div>
<form action="install.php?v=2" method="post">
<p class="center">
	<input type="button" class="submit" name="prev" value="<?php echo $i_message['install_prev'];?>" onclick="history.go(-1)">&nbsp;
	<input type="submit" class="submit" name="next" value="<?php echo $i_message['install_next'];?>" <?php if($quit) echo "disabled=\"disabled\"";?> />
</p>
</form>
<?php
}
elseif ($v == '2')
{
?>
<div class="botBorder">
	<h2><?php echo $i_message['install_check_dirmod'];?></h2>
</div>
<div class="botBorder">
	<p><span class="red"><strong><?php echo $i_message['install_tips'];?></strong></span><br /><br />
<?php echo $i_message['install_dirmod_tips'];?>
	</p>
</div>
<div>
<table cellpadding="3" cellspacing="1" class="t_table">
<tr> 
	<th width="25%"><strong><?php echo $i_message['install_check_dirmod_item'];?></strong></th>
	<th width="*"><strong><?php echo $i_message['explain'];?></strong></th>
	<th width="25%"><strong><?php echo $i_message['install_check_env_status'];?></strong></th>
</tr>
<?php
foreach ($dirarray as $v)
{
	echo '<tr><td>'.$v[0].'</td>'; 
	echo '<td>'.$v[1].'</td>'; 
	echo '<td>';
	if (pb_writable($dir))
	{
		result(1, 1);
	}
	else
	{
		result(0, 1);
		$quit = TRUE;
	}
	echo '</td></tr>'; 
}
?>
</table>
</div>
<p class="center">
<form method="post" action='install.php?v=3'>
	<input type="button" class="submit" name="prev" value="<?php echo $i_message['install_prev'];?>" onclick="history.go(-1)">&nbsp;
	<input type="submit" class="submit" name="next" value="<?php echo $i_message['install_next'];?>" <?php if($quit) echo "disabled=\"disabled\"";?>>
</form>
</p>
<?php
}
elseif ($v == '3')
{
?>
<div class="botBorder">
	<h2><?php echo $i_message['install_setting'];?></h2>
</div>

<form method="post" action="install.php?v=4" id="install" onsubmit="return check();">
<div class="shade">
<div class="settingHead"><?php echo $i_message['install_mysql'];?></div>

<h5><?php echo $i_message['install_mysql_host'];?></h5>
<p><input type="text" name="db_host" value="localhost" size="40" class='input' /> <?php echo $i_message['install_mysql_host_intro'];?></p>

<h5><?php echo $i_message['install_mysql_port'];?></h5>
<p><input type="text" name="db_port" value="3306" size="40" class='input' /> <?php echo $i_message['install_mysql_port_intro'];?></p>

<h5><?php echo $i_message['install_mysql_username'];?></h5>
<p><input type="text" name="db_username" value="root" size="40" class='input' /></p>

<h5><?php echo $i_message['install_mysql_password'];?></h5>
<p><input type="text" name="db_password" value="" size="40" class='input' /></p>

<h5><?php echo $i_message['install_mysql_name'];?></h5>
<p><input type="text" name="db_name" value="" size="40" class='input' /></p>

<h5><?php echo $i_message['install_mysql_prefix'];?></h5>
<p><input type="text" name="db_prefix" value="pb_" size="40" class='input' /> <?php echo $i_message['install_mysql_prefix_intro'];?></p>
</div>

<div class="shade">
<div class="settingHead"><?php echo $i_message['founder'];?></div>

<h5><?php echo $i_message['install_founder_name'];?></h5>
<p><input type="text" name="username" value="admin" size="40" class='input' /></p>

<h5><?php echo $i_message['install_founder_password'];?></h5>
<p><input type="text" name="password" value="" size="40" class='input' /></p>

<h5><?php echo $i_message['install_founder_rpassword'];?></h5>
<p><input type="text" name="rpassword" value="" size="40" class='input' /></p>

<h5><?php echo $i_message['install_founder_email'];?></h5>
<p><input type="text" name="email" value="admin@admin.com" size="40" class='input' /></p>
</div>

<div class="shade">
<div class="settingHead"><?php echo $i_message['site'];?></div>

<h5><?php echo $i_message['install_site_url'];?></h5>
<?php

$sHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? (int)$_SERVER['SERVER_NAME'] : (int)getenv('SERVER_NAME'));
$TEMP_PHP_SELF = dirname(preg_replace('~[/]{2,}~i', '/', str_replace('\\', '/', $_SERVER['PHP_SELF'])));
$sDir = substr($TEMP_PHP_SELF, 0, strrpos($TEMP_PHP_SELF, '/'));
$pb_siteurl = 'http://'.$sHost.$sDir.'/';
!$sDir && $sDir = '/';

?>
<p><input type="text" name="site_url" value="<?php echo $pb_siteurl;?>" size="40" class='input' /> <?php echo $i_message['install_site_url_intro'];?></p>
<h5><?php echo $i_message['install_site_dir'];?></h5>
<p><input type="text" name="site_dir" value="<?php echo $sDir;?>" size="40" class='input' /> <?php echo $i_message['install_site_dir_intro'];?></p>
<h5><?php echo $i_message['install_site_init'];?></h5>
<p><input type="checkbox" name="site_init" value="1" class="checkbox" /> <?php echo $i_message['install_site_init_intro'];?></p>
</div>
<div class="center">
	<input type="button" class="submit" name="prev" value="<?php echo $i_message['install_prev'];?>" onclick="history.go(-1)">&nbsp;
	<input type="submit" class="submit" name="next" value="<?php echo $i_message['install_next'];?>">
</div>
</form>
<script type="text/javascript" language="javascript">
function check()
{
	var obj = document.getElementById('install');
	if (!obj.db_host.value)
	{
		alert('<?php echo $i_message['install_mysql_host_empty'];?>');
		obj.db_host.focus();
		return false;
	}
	else if (!obj.db_username.value)
	{
		alert('<?php echo $i_message['install_mysql_username_empty'];?>');
		obj.db_username.focus();
		return false;
	}
	else if (!obj.db_name.value)
	{
		alert('<?php echo $i_message['install_mysql_name_empty'];?>');
		obj.db_name.focus();
		return false;
	}
	else if (!obj.username.value)
	{
		alert('<?php echo $i_message['install_founder_name_empty'];?>');
		obj.username.focus();
		return false;
	}
	else if (obj.password.value.length < 6)
	{
		alert('<?php echo $i_message['install_founder_password_length'];?>');
		obj.password.focus();
		return false;
	}
	else if (obj.password.value != obj.rpassword.value)
	{
		alert('<?php echo $i_message['install_founder_rpassword_error'];?>');
		obj.rpassword.focus();
		return false;
	}
	else if (!obj.email.value)
	{
		alert('<?php echo $i_message['install_founder_email_empty'];?>');
		obj.email.focus();
		return false;
	}
	return true;
}
</script>
<?php
}
elseif ($v == '4')
{
?>
<div class="botBorder">
	<h2><?php echo $i_message['install_configure_confirm'];?></h2>
</div>
<?php
	if(empty($db_host) || empty($db_username) || empty($db_name) || empty($db_prefix))
	{
		$msg .= '<li>'.$i_message['mysql_invalid_configure'].'<li>';
		$quit = TRUE;
	}
	if ($db_port && $db_port != '3306' && is_numeric($db_port)) $db_host .= ':'.$db_port;
	if (!@mysql_connect($db_host, $db_username, $db_password))
	{
		$msg .= '<li>'.(isset($i_message['database_errno_'.mysql_errno()]) ? $i_message['database_errno_'.mysql_errno()] : $i_message['database_errno']).'</li>';
		$quit = TRUE;
	}
	if(strpos($db_prefix, '.') !== FALSE)
	{
		$msg .= '<li>'.$i_message['mysql_invalid_prefix'].'</li>';
		$quit = TRUE;
	}
	if (empty($username) || empty($password) || empty($rpassword) || empty($email))
	{
		$msg .= '<li>'.$i_message['founder_invalid_configure'].'</li>';
		$quit = TRUE;
	}
	if (strlen($password) < 6)
	{
		$msg .= '<li>'.$i_message['founder_invalid_password'].'</li>';
		$quit = TRUE;
	}
	if ($password != $rpassword)
	{
		$msg .= '<li>'.$i_message['founder_invalid_rpassword'].'</li>';
		$quit = TRUE;
	}
	if (!preg_match('~^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,3}$~i', $email))
	{
		$msg .= '<li>'.$i_message['founder_invalid_email'].'</li>';
		$quit = TRUE;
	}
	if (!preg_match('~^[_0-9a-z\x7f-\xff]+$~i', $username))
	{
		$msg .= '<li>'.$i_message['founder_invalid_username'].'</li>';
		$quit = TRUE;
	}
	if (preg_match('~[\x00-\x20]~i', $password))
	{
		$msg .= '<li>'.$i_message['founder_invalid_password'].'</li>';
		$quit = TRUE;
	}
	$fp = @fopen($configfile, 'wb');
	if (!$fp || !@chmod($configfile, 0777))
	{
		$quit = TRUE;
		$msg .= '<li>'.$i_message['configure_read_failed'].'</li>';
	}
	else
	{
		$configfilecontent = <<<EOT
<?php

!defined('IN_PBDIGG') && exit ('Access Denied');

/** PBDigg SQL CONFIG FILE! **/

//数据库服务器，一般为localhost
\$db_host = '{$db_host}';

//数据库名称
\$db_name = '{$db_name}';

//数据库用户名
\$db_username = '{$db_username}';

//数据库密码
\$db_password = '{$db_password}';

//数据库持久连接 0=关闭, 1=打开
\$db_pconnect = '0';

//表前缀
\$db_prefix = '{$db_prefix}';

//数据库字符集, 可选 'gbk', 'big5', 'utf-8'
\$db_charset = '{$db_charset}';

//数据库类型
\$pb_datatype = 'MySQL';

//是否将错误写入调试文件 0=否, 1=是
\$pb_logdebug = '1';

//是否启用debug模式，TRUE将显示完整错误信息，FALSE将显示提示性错误信息
\$pb_debug = TRUE;

//网站创始人 UID, 多个创始人，之间使用半角逗号 “,” 分隔，例如“array('1','2','3')”。请务必设置一名管理员为创始人，否则将出现无法登陆后台的错误。
\$_siteFounder = array('1');

?>
EOT;
		@fwrite($fp, trim($configfilecontent));
		@fclose($fp);

		$link_identifier = @mysql_connect($db_host, $db_username, $db_password);
		$sqlv = @mysql_get_server_info($link_identifier);
		if($sqlv < '4.0.2')
		{
			$msg .= '<li>'.$i_message['mysql_version_402'].'</li>';
			$quit = TRUE;
		}
		else
		{
			if($sqlv > '4.1')
			{
				$newdb_charset = (strpos($db_charset, '-') === FALSE) ? $db_charset : str_replace('-', '', $db_charset);
				mysql_query("CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET $newdb_charset");
			}
			else
			{
				mysql_query("CREATE DATABASE IF NOT EXISTS `$db_name`");
			}
			if (mysql_errno())
			{
				$msg .= '<li>'.(isset($i_message['database_errno_'.mysql_errno()]) ? $i_message['database_errno_'.mysql_errno()] : $i_message['database_errno']).'</li>';
				$quit = TRUE;
			}
			else
			{
				mysql_select_db($db_name);
			}
			$query = @mysql_query("SELECT * FROM {$db_prefix}configs");
			if($query)
			{
				$msg .= '<li>'.$i_message['pbdigg_rebuild'].'</li>';
				$alert = ' onclick="return confirm(\''.$i_message['pbdigg_rebuild'].'\');"';
			}
			@mysql_close();
		}
	}
	$fp = @fopen($tempfile, 'wb');
	if ($fp && @chmod($tempfile, 0777))
	{
		$md5password = md5($password);
		$username = addcslashes($username, '\'\\');
		$email = addcslashes($email, '\'\\');
		$site_url = addcslashes($site_url, '\'\\');
		if ($site_dir && $site_dir{strlen($site_dir)-1} != '/')
		{
			$site_dir .= '/';
		}
		$site_dir = addcslashes($site_dir, '\'\\');
		$site_init = isset($site_init) ? 1 : 0;
		$temp = <<<EOT
<?php
!defined('IN_PBDIGG') && exit ('Access Denied');
\$username = '{$username}';
\$password = '{$md5password}';
\$email = '{$email}';
\$site_url = '{$site_url}';
\$site_dir = '{$site_dir}';
\$site_init = '{$site_init}';
?>
EOT;
		fwrite($fp, trim($temp));
		@fclose($fp);
	}
	else
	{
		$quit = TRUE;
		$msg .= '<li>'.$i_message['configure_temp_read_failed'].'</li>';
	}
	if ($quit)
	{
?>
<div class="error"><?php echo $i_message['install_error'];?></div><ul>
<?php
		echo $msg, '</ul>';
	}
	else
	{
?>
<div class="botBorder">
	<p><?php echo $i_message['install_founder_name'], ' ', $username;?></p>
	<p><?php echo $i_message['install_founder_password'], ' ', $password;?></p>
	<p><?php echo $i_message['install_import_data'];?></p>
</div>
<?php
	}
?>
<div class="center">
<form method="post" action="install.php?v=5">
	<input type="button" class="submit" name="prev" value="<?php echo $i_message['install_prev'];?>" onclick="history.go(-1)">&nbsp;
	<input type="submit" class="submit" name="next" value="<?php echo $i_message['install_next'];?>" <?php if($quit){echo "disabled=\"disabled\"";}echo $alert;?>>
</form>
</div>
<?php
}
elseif ($v == '5')
{
?>
<div class="botBorder">
	<h2><?php echo $i_message['install_db_action'];?></h2>
</div>
<div class="botBorder">
<h4><?php echo $i_message['install_db_import'];?></h4>
<?php
	@require_once($configfile);
	mysql_connect($db_host, $db_username, $db_password);
	if (mysql_get_server_info() > '5.0')
	{
		mysql_query("SET sql_mode = ''");
	}
	$newdb_charset = (strpos($db_charset, '-') === FALSE) ? $db_charset : str_replace('-', '', $db_charset);
	mysql_query("SET character_set_connection=$newdb_charset, character_set_results=$newdb_charset, character_set_client=binary");
	mysql_select_db($db_name);
	$tablenum = 0;
	$fp = @fopen($installfile, 'rb');
	$sql = fread($fp, filesize($installfile));
	fclose($fp);
	runquery($sql);
	@require_once($tempfile);
	mysql_query("INSERT INTO {$db_prefix}members (`uid`, `username`, `password`, `email`, `adminid`, `groupid`, `publicemail`, `gender`, `regip`, `regdate`, `realgroup`, `postnum`, `commentnum`, `diggnum`, `burynum`, `currency`, `lastip`, `lastvisit`, `lastpost`, `lastcomment`, `lastupload`, `lastsearch`, `uploadnum`, `newmsg`, `friendnum`, `collectionnum`, `visitnum`, `ucuid`, `avatar`) VALUES (1, '".addslashes($username)."', '".addslashes($password)."', '".addslashes($email)."', 1, 1, 1, 1, '$ip', '$timestamp', 7, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '')");
	mysql_query("INSERT INTO {$db_prefix}memberexp (`uid`, `qq`, `msn`, `site`, `location`, `birthday`, `signature`, `showsign`, `ctsig`) VALUES (1, '', '', '', '', '', '', 0, 0)");
	mysql_query("UPDATE {$db_prefix}configs SET text = '".addslashes($site_url)."' WHERE title = 'pb_siteurl'");
	mysql_query("UPDATE {$db_prefix}configs SET text = '".addslashes($site_dir)."' WHERE title = 'pb_sitedir'");
	mysql_query("UPDATE {$db_prefix}configs SET text = '".pb_random(10)."' WHERE title = 'pb_sitehash'");
	mysql_query("UPDATE {$db_prefix}configs SET text = '".md5(pb_random(32))."' WHERE title = 'pb_k'");
	mysql_query("UPDATE {$db_prefix}sitestat SET newmember = '".addslashes($username)."', buildtime = '".date('Y-m-d')."' WHERE id = 1");
?>
<p><?php echo $i_message['create_founder_success'];?></p>
<?php
if ($site_init && file_exists($initfile))
{
	$fp = @fopen($initfile, 'rb');
	$sql = fread($fp, filesize($initfile));
	fclose($fp);
	runquery($sql);
?>
<p><?php echo $i_message['create_init_success'];?></p>
<?php
}
	require_once PBDIGG_ROOT.'include/global.func.php';
	require_once PBDIGG_ROOT.'include/Cache.class.php';
	require_once PBDIGG_ROOT.'include/MySQL.class.php';
	$DB = new MySQL($db_host, $db_username, $db_password, $db_name, $db_pconnect);
	$Cache = new Cache();
	$Cache->coreCache();
	fopen('install.lock', 'w');
	unlink($tempfile);
	PWriteFile(PBDIGG_ROOT.'data/cache/cache_words.php', "<?php\r\n\$words_banned = array();\$words_replace = array();\$words_links = array();\r\n?>", 'wb');
?>
<p><?php echo $i_message['create_cache_success'];?></p>
</div>
<div class="botBorder">
<h4><?php echo $i_message['install_success'];?></h4>
<?php echo $i_message['install_success_intro'];?>
</div>
<?php
}
?>
</div>
<div class='copyright'>PBDigg Version <?php echo $_PBVERSION?> &#169; copyright 2007 - 2009 PBDigg.com All Rights Reserved</div>
</div>
</body>
</html>