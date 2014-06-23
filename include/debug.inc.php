<?php
/**
 * @version $Id: mysql.class.php v2.1 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

!defined('IN_PBDIGG') && exit('Access Denied!');

switch ($this->db_error_no())
{
	case '1005':
		$mysql_error = '创建表失败';
		break;
	case '1006':
		$mysql_error = '创建数据库失败';
		break;
	case '1007':
	case '1008':
		$mysql_error = '数据库不存在，删除数据库失败';
		break;
	case '1016':
		$mysql_error = '无法打开数据文件，尝试使用 phpmyadmin 进行修复';
		break;
	case '1017':
		$mysql_error = '服务器非法关机，导致数据库文件损坏';
		break;
	case '1020':
		$mysql_error = '记录已被其他用户修改，当前无法使用';
		break;
	case '1021':
		$mysql_error = '关键字重复，更改记录失败';
		break;
	case '1037':
		$mysql_error = '系统内存不足，请重启数据库或重启服务器';
		break;
	case '1040':
		$mysql_error = '数据库已到达最大连接数，请在服务器空闲的时候运行转换程序';
		break;
	case '1041':
		$mysql_error = '系统内存不足，请在服务器空闲的时候运行转换程序';
		break;
	case '1042':
		$mysql_error = '无效的主机名，请正确配置数据库参数';
		break;
	case '1043':
		$mysql_error = '无效连接，请正确配置数据库参数';
		break;
	case '1044':
		$mysql_error = '当前用户没有访问数据库的权限，请检查数据库用户名或者密码是否正确';
		break;
	case '1045':
		$mysql_error = '无法连接数据库，请检查数据库用户名或者密码是否正确';
		break;
	case '1046':
		$mysql_error = '数据表不存在，请检查源程序和转换程序是否对应';
		break;
	case '1048':
		$mysql_error = '字段不能为空';
		break;
	case '1049':
		$mysql_error = '数据库不存在，请检查数据库名填写是否正确';
		break;
	case '1050':
		$mysql_error = '数据表已存在';
		break;
	case '1051':
		$mysql_error = '数据表不存在';
		break;
	case '1054':
		$mysql_error = '数据库字段不存在';
		break;
	case '1060':
	case '1062':
		$mysql_error = '字段值重复，入库失败';
		break;
	case '1064':
		$mysql_error = 'SQL执行发生错误：1.数据超长或类型不匹配；2.数据库记录重复';
		break;
	case '1065':
		$mysql_error = '无效的SQL语句，SQL语句为空';
		break;
	case '1081':
		$mysql_error = '不能建立Socket连接';
		break;
	case '1114':
		$mysql_error = '数据表已满，不能容纳任何记录';
		break;
	case '1115':
		$mysql_error = '设置的字符集在 MySQL 并没有支持';
		break;
	case '1129':
		$mysql_error = '数据库出现异常，请重启数据库';
		break;
	case '1130':
		$mysql_error = '连接数据库失败，没有连接数据库的权限';
		break;
	case '1133':
		$mysql_error = '数据库用户不存在';
		break;
	case '1135':
		$mysql_error = '1、服务器系统内存溢出；2、环境系统损坏或系统损坏。请联系空间商解决';
		break;
	case '1141':
		$mysql_error = '当前用户无权访问数据库';
		break;
	case '1142':
		$mysql_error = '当前用户无权访问数据表';
		break;
	case '1143':
		$mysql_error = '当前用户无权访问数据表中的字段';
		break;
	case '1146':
		$mysql_error = '数据表不存在';
		break;
	case '1149':
		$mysql_error = 'SQL语句语法错误';
		break;
	case '1158':
	case '1159':
	case '1160':
	case '1161':
		$mysql_error = '网络错误，请检查网络连接状况';
		break;
	case '1169':
		$mysql_error = '字段值重复，更新记录失败';
		break;
	case '1177':
		$mysql_error = '打开数据表失败';
		break;
	case '1227':
		$mysql_error = '权限不足，您无权进行此操作';
		break;
	case '1267':
		$mysql_error = '不合法的混合字符集';
		break;
	case '2002':
		$mysql_error = '服务器端口不对，请咨询空间商正确的端口';
		break;
	case '2003':
	case '2013':
		$mysql_error = '无法连接数据库，请检查数据库是否启动，数据库服务器地址是否正确';
		break;
	case '2005':
		$mysql_error = '数据库服务器不存在';
		break;
	case '10048':
		$mysql_error = '开启防刷新,严禁刷新太快，建议在my.ini文件中修改最大连接数';
		break;
	case '10055':
		$mysql_error = '没有缓存空间可利用';
		break;
	case '10061':
		$mysql_error = '数据库服务器未启动成功，可能是 my.ini 出错导致';
		break;
	default:
		$mysql_error = '未定义SQL执行错误，请向我们<a href="http://bbs.pbdigg.com" target="_blank">反馈</a>这个BUG，谢谢您的合作。';
		break;
}

$message = "<html>\n<head>\n";
$message .= "<meta content=\"text/html; charset=$db_charset\" http-equiv=\"Content-Type\">\n";
$message .= "</head>\n";
$message .= "<body>\n";
$message .= "<p style=\"font-family: Verdana, Tahoma; font-size: 11px; background: #fff;\">\n";
$message .= "<strong>PBDigg Info：</strong>" . $mysql_error . "\n<br />";
if ($pb_debug)
{
	$message .= "<strong>Time：</strong>" . date('Y-m-d @ H:i', $timestamp) . "\n<br />";
	$message .= "<strong>Script：</strong>http://" . $_SERVER['HTTP_HOST'] . $_PBENV['REQUEST_URI'] . "\n<br />";
	if ($sql)
	{
		$message .= "<strong>SQL：</strong><code>" . $sql . "</code>\n<br />";
	}
	$message .= "<strong>MySQL Error Description：</strong>" . str_replace($GLOBALS['db_host'], 'mysql_server_host', $this->db_error_msg()) . "\n<br />";
	$message .= "<strong>MySQL Error Number：</strong>" . $this->db_error_no() . "\n<br />";
}
$message .= "</p>\n";
$message .= "</body>\n</html>";
echo $message;
exit;

?>