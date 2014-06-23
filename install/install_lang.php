<?php

!defined('PBDIGG_INSTALL') && exit ('Access Denied');

$dirarray = array (
	array('admin/dbak', '默认数据库备份目录'),
	array('attachments', '附件目录'),
	array('attachments/temp', '临时上传目录'),
	array('attachments/commend', '推荐图片目录'),
	array('attachments/topic', '主题图片目录'),
	array('cache', '静态缓存目录'),
	array('compile', '模板编译目录'),
	array('data/sql.inc.php', '数据库配置文件'),
	array('data/cache', '系统缓存目录'),
	array('log/enter.php', '后台非法登录日志文件'),
	array('log/db', '数据库错误日志目录'),
	array('log/robots', '蜘蛛爬行日志目录'),
	array('images/cate', '分类图片目录'),
	array('images/avatars', '用户头像目录'),
	array('include/uc.inc.php', 'UCenter配置文件'),
	array('install', '安装目录')
);

$i_message = array(
	'install_title' => 'PBDigg安装脚本',
	'install_wizard' => '安装向导',
	'install_tips' => '提示信息',
	'unlimited' => '不限',
	'support' => '支持',
	'unsupport' => '不支持',
	'yes' => '是',
	'no' => '否',
	'open' => '开启',
	'close' => '关闭',
	'explain' => '说明',
	'install_prev' => '上一步',
	'install_next' => '下一步',
	'install_error' => '安装程序发生错误',
	'install_warning' => '这个安装程序仅仅用在你首次安装PBDigg。如果你已经在使用 PBDigg 或者要更新到一个新版本，请不要运行这个安装程序。',
	'install_license_title' => '第一步：PBDigg 用户许可协议',
	'install_license' => 'PBDigg是由浙江诸暨锐锋电脑科技独立开发的内容管理系统，基于PHP脚本和MySQL数据库。本程序是免费和源码开放的， 任何人都可以从互联网上免费下载，并可以在不违反本协议规定的前提下进行使用而无需缴纳程序使用费。
官方网址： www.pbdigg.com 交流论坛： bbs.pbdigg.com

为了使你正确并合法的使用本软件，请你在使用前务必阅读清楚下面的协议条款：

一、本授权协议适用且仅适用于PBDigg任何版本，PBDigg官方拥有对本授权协议的最终解释权和修改权。

二、协议许可的权利和限制
1、您可以在完全遵守本最终用户授权协议的基础上，将本软件应用于非商业用途，而不必支付软件版权授权费用，但我们也不承诺对个人用户提供任何形式的技术支持。
2、您可以在协议规定的约束和限制范围内修改PBDigg源代码或界面风格以适应您的网站要求。
3、您拥有使用本软件构建的网站全部内容所有权，并独立承担与这些内容的相关法律义务。
4、未经商业授权，不得将本软件用于商业用途(企业网站或以盈利为目的经营性网站)，否则我们将保留追究的权力。

三、免责声明
1、本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的。
2、用户出于自愿而使用本软件，您必须了解使用本软件的风险，任何情况下，程序的质量风险和性能风险完全由您承担。有可能证实该程序存在漏洞，您需要估算与承担所有必需服务，恢复，修正，甚至崩溃所产生的代价！在尚未购买产品技术服务之前，我们不承诺对免费用户提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任。
3、请务必仔细阅读本授权协议，在您同意授权协议的全部条件后，即可继续PBDigg的安装。即：您一旦开始安装PBDigg，即被视为完全同意本授权协议的全部内容，如果出现纠纷，我们将根据相关法律和协议条款追究责任。

版权所有 (C) 2007-2009，PBDigg.com 保留所有权利。',
	'install_agree' => '我已看过并同意用户许可协议',
	'install_check_env' => '第二步：运行环境检测',
	'install_env_tips' => '1、MySQL 未开启将导致系统无法运行。<br /><br />2、allow_url_fopen 未开启将导致远程图片保存功能无法应用。<br /><br />3、safe_mode 开启将导致系统无法运行。<br /><br />4、GD  未开启将导致与图片相关的大多数功能无法使用。<br /><br />5、商业版需PHP 5 和 Zend Optimizer 3.3以上版本支持。<br />',
	'install_check_env_item' => '项目',
	'install_check_env_needed' => 'PBDigg需要配置',
	'install_check_env_current' => '当前服务器配置',
	'install_check_env_status' => '检测状态',
	'install_check_env_item' => '项目',
	'install_check_env_item' => '项目',
	'install_check_env_item' => '项目',
	'php_os' => '操作系统',
	'php_version' => 'PHP版本',
	'mysql' => 'MySQL数据库扩展',
	'safe_mode' => 'safe_mode 安全模式',
	'allow_url_fopen' => 'allow_url_fopen',
	'gd' => 'GD 库',
	'install_check_dirmod' => '第三步：目录和文件权限检测',
	'install_dirmod_tips' => '1、将下面目录或者文件权限设为0777，如果是目录，则需要把权限应用于子目录与文件。<br />',
	'install_check_dirmod_item' => '目录/文件名称',
	'zend_version' => 'Zend Optimizer',
	'file_upload' => '附件上传',
	'install_setting' => '第四步：数据库资料与管理员账号设置',
	'install_mysql' => '数据库服务器参数',
	'install_mysql_host' => '数据库服务器',
	'install_mysql_host_intro' => '一般为：localhost',
	'install_mysql_port' => '数据库服务器端口',
	'install_mysql_port_intro' => '一般为：3306',
	'install_mysql_username' => '数据库用户名',
	'install_mysql_password' => '数据库密码',
	'install_mysql_name' => '数据库名',
	'install_mysql_prefix' => '表名前缀',
	'install_mysql_prefix_intro' => '如无特殊需要，请不要修改',
	'founder' => '超级管理员资料',
	'install_founder_name' => '账号',
	'install_founder_password' => '密码',
	'install_founder_rpassword' => '重复密码',
	'install_founder_email' => '电子邮件',
	'site' => '网站设置',
	'install_site_url' => '网站网址',
	'install_site_dir' => 'PBDigg 安装目录',
	'install_site_init' => '内置初始数据',
	'install_site_url_intro' => '请以“/”结尾',
	'install_site_dir_intro' => '如果是安装在根目录则为“/”',
	'install_site_init_intro' => '将安装初始化网站数据，新手请勾选',

	'install_mysql_host_empty' => '数据库服务器不能为空',
	'install_mysql_username_empty' => '数据库用户名不能为空',
	'install_mysql_name_empty' => '数据库名不能为空',
	'install_founder_name_empty' => '超级管理员用户名不能为空',
	'install_founder_password_length' => '超级管理员密码必须大于6位',
	'install_founder_rpassword_error' => '两次输入管理员密码不同',
	'install_founder_email_empty' => '超级管理员Email不能为空',

	'mysql_invalid_configure' => '数据库配置信息不完整',
	'database_errno_2003' => '无法连接数据库，请检查数据库是否启动，数据库服务器地址是否正确',
	'database_errno_2013' => '无法连接数据库，请检查数据库是否启动，数据库服务器地址是否正确',
	'database_errno_1045' => '无法连接数据库，请检查数据库用户名或者密码是否正确',
	'database_errno_1044' => '无法创建新的数据库，请检查数据库名称填写是否正确',
	'database_errno_1064' => 'SQL执行错误，请检查数据库名称填写是否正确',
	'database_errno' => '程序在执行数据库操作时发生了一个未知错误，安装过程无法继续进行。',
	'mysql_invalid_prefix' => '数据表前缀包含点字符(".")，请返回修改。',
	'founder_invalid_configure' => '超级管理员信息不完整',
	'founder_invalid_password' => '密码长度必须大于6位',
	'founder_invalid_rpassword' => '两次输入的密码不一致',
	'founder_invalid_email' => '电子邮件格式不正确',
	'founder_invalid_username' => '用户名包含非法字符',
	'founder_invalid_password' => '密码包含非法字符',
	
	'install_configure_confirm' => '第五步：账户信息确认',
	'configure_read_failed' => '配置文件读取错误，请检查/data/sql.inc.php属性是否为0777',
	'configure_temp_read_failed' => '临时文件读取错误，请检查/install 目录属性是否为0777',
	
	'mysql_version_402' => '您的 MYSQL 版本低于 4.0.2，安装无法继续进行！',
	'pbdigg_rebuild' => '数据库中已经安装过 PBDigg，继续安装会清空原有数据！',
	'install_founder_confirm' => '请确认您的账户和密码',
	'install_import_data' => '点击下一步开始导入数据',
	
	'install_db_action' => '第六步：完成网站安装',
	'install_db_import' => '数据导入',

	'create_table' => '创建表',
	'create_founder_success' => '超级管理员帐户创建成功',
	'create_init_success' => '初始化网站数据成功',
	'create_cache_success' => '创建缓存成功',
	'install_success' => '安装成功',
	'install_success_intro' => '<p>安装程序已经顺利执行完毕，请尽快删除整个 install 目录，以免被他人恶意利用。</p><p>感谢您使用 PBDigg 程序.</p><p><a href="../admin/admincp.php">请点击这里进入后台进行参数设置</a></p>',
	'install_lock' => 'PBDIGG 安装程序已锁定。如要重新安装，请删除本目录的 install.lock 文件！',
	'install_dbFile_error' => '数据库配置文件无法读取，请检查/install/install.sql是否存在。',

);

?>