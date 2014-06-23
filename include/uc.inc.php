<?php

!defined('IN_PBDIGG') && exit('Access Denied!');

define('UC_CONNECT', 'mysql');						// mysql 是直接连接的数据库, 为了效率, 建议采用 mysql

define('UC_DBHOST', '');				// UCenter 数据库主机
define('UC_DBUSER', '');						// UCenter 数据库用户名
define('UC_DBPW', '');								// UCenter 数据库密码
define('UC_DBNAME', '');						// UCenter 数据库名称
define('UC_DBCHARSET', 'gbk');						// UCenter 数据库字符集
define('UC_DBTABLEPRE', '``.');				// UCenter 数据库表前缀
define('UC_DBCONNECT', 0);

//通信相关
define('UC_KEY', '');			// 与 UCenter 的通信密钥, 要与 UCenter 保持一致
define('UC_API', '');		// UCenter 的 URL 地址, 在调用头像时依赖此常量
define('UC_CHARSET', 'gbk');						// UCenter 的字符集
define('UC_IP', '');								// UCenter 的 IP, 当 UC_CONNECT 为非 mysql 方式时, 并且当前应用服务器解析域名有问题时, 请设置此值
define('UC_APPID', 0);								// 当前应用的 ID

$uc_id = '0';
$uc_avatar = '0';
$uc_msg = '1';
$uc_friend = '1';
$uc_url = '';
$uc_space = '0';
$uc_spaceurl = '';
?>