<?php
/**
 * @version $Id: admincp.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

$option_message = array (
	'torder' => array (
		'diggdate' => 'Digg日期',
		'digg' => 'Digg数量',
		'postdate' => '发布日期',
		'commentdate' => '评论日期',
		'pbrank' => 'PBRank',
	),
	'corder' => array (
		'postdate' => '评论日期',
		'digg' => 'Digg数量',
		'diggdate' => 'Digg日期',
	),
	'orderby' => array (
		'asc' => '顺序',
		'desc' => '倒序',
	),
	'tday' => array (
		0 => '',
		86400 => '一天',
		259200 => '三天',
		604800 => '一星期',
		2592000 => '一个月',
		7776000 => '三个月',
	),
	'contentubb' => array (
		'flash' => '[flash]flash',
		'media' => '[media]多媒体',
	),
	'signubb' => array (
		'b' => '[b]粗体字',
		'i' => '[i]斜体字',
		'u' => '[u]下划线',
		'font' => '[font]字体',
		'color' => '[color]颜色',
		'size' => '[size]大小',
		'quote' => '[quote]引用',
		'url' => '[url]链接',
		'img' => '[img]图片',
		'email' => '[email]电邮',
	),
	'attachdir' => array (
		0 => '按天存放',
		1 => '按月存放',
		2 => '按栏目存放',
	),
	'watertype' => array (
		0 => '不启用',
		1 => '文字水印',
		2 => '图片水印',
	),
	'gdcheck' => array (
		1 => '注册',
		2 => '前台登录',
		4 => '发表主题',
		8 => '发表评论',
		16 => '后台登录',
	),
	'gdcodetype' => array (
		1 => '数字验证码',
		2 => '英文验证码',
		4 => '随机验证码',
	),
	/*
	 * 颜色特效代码。
	 * 请按照<option value="颜色代码" style="background:颜色代码;color:颜色代码">颜色代码</option>的格式添加。
	 * 代码格式：#ff0000（红色）、#0000ff（蓝色）、#00ff00（绿色）...
	 * 颜色代码查询：http://www.pbdigg.com/colortable.gif
	 */
	'title_color' => '<option value="ff0000" style="background:#ff0000;color:#ff0000">#ff0000</option><option value="0000ff" style="background:#0000ff;color:#0000ff">#0000ff</option><option value="00ff00" style="background:#00ff00;color:#00ff00">#00ff00</option>',
	'timezone' => array (
		'-12' => '(标准时-12:00) 日界线西',
		'-11' => '(标准时-11:00) 中途岛、萨摩亚群岛',
		'-10' => '(标准时-10:00) 夏威夷',
		'-9' => '(标准时-9:00) 阿拉斯加',
		'-8' => '(标准时-8:00) 太平洋时间(美国和加拿大)',
		'-7' => '(标准时-7:00) 山地时间(美国和加拿大)',
		'-6' => '(标准时-6:00) 中部时间(美国和加拿大)、墨西哥城',
		'-5' => '(标准时-5:00) 东部时间(美国和加拿大)、波哥大',
		'-4' => '(标准时-4:00) 大西洋时间(加拿大)、加拉加斯',
		'-3.5' => '(标准时-3:30) 纽芬兰',
		'-3' => '(标准时-3:00) 巴西、布宜诺斯艾利斯、乔治敦',
		'-2' => '(标准时-2:00) 中大西洋',
		'-1' => '(标准时-1:00) 亚速尔群岛、佛得角群岛',
		'0' => '(格林尼治标准时) 西欧时间、伦敦、卡萨布兰卡',
		'1' => '(标准时+1:00) 中欧时间、安哥拉、利比亚',
		'2' => '(标准时+2:00) 东欧时间、开罗，雅典',
		'3' => '(标准时+3:00) 巴格达、科威特、莫斯科',
		'3.5' => '(标准时+3:30) 德黑兰',
		'4' => '(标准时+4:00) 阿布扎比、马斯喀特、巴库',
		'4.5' => '(标准时+4:30) 喀布尔',
		'5' => '(标准时+5:00) 叶卡捷琳堡、伊斯兰堡、卡拉奇',
		'5.5' => '(标准时+5:30) 孟买、加尔各答、新德里',
		'6' => '(标准时+6:00) 阿拉木图、 达卡、新亚伯利亚',
		'7' => '(标准时+7:00) 曼谷、河内、雅加达',
		'8' => '(北京时间+8:00) 北京、重庆、香港、新加坡',
		'9' => '(标准时+9:00) 东京、汉城、大阪、雅库茨克',
		'9.5' => '(标准时+9:30) 阿德莱德、达尔文',
		'10' => '(标准时+10:00) 悉尼、关岛',
		'11' => '(标准时+11:00) 马加丹、索罗门群岛',
		'12' => '(标准时+12:00) 奥克兰、惠灵顿、堪察加半岛'
	),
	'compare' => array(
		'0' => '',
		'1' => '大于',
		'-1' => '小于',
	),
	'timeformat' => array(
		'Y-m-d H:i:s' => '2007-09-16 15:31:18',
		'Y-m-d' => '2007-09-16',
		'm-d' => '09-16',
		'-1' => '1小时20分30秒前',
	),
	'systemdir' => array(
		'admin','api','attachments','cache','compile','data','images','include','install','js','languages','log','module','plugins','templates','update'
	),
	'leftmenu' => array (
		'quick' => array(
			'name' => '快捷操作',
			'item' => array(
				'系统信息' => 'main',			
				'系统设置' => 'setting&job=basic',
				'栏目设置 ' => 'cate&job=edit'),
		),
		'setting' => array (
			'name' => '系统设置',
			'item' => array (
				'basic' => '基本设置',
				'core' => '功能设置',
				'view' => '界面显示',
				'atc' => '主题设置',
				'member' => '会员设置',
				'credit' => '积分设置',
				'attachment' => '附件设置',
				'safe' => '安全设置',
				'seo' => '搜索优化',
				'email' => '邮件设置',
				'transfer' => 'JS调用设置',
				'passport' => '通行证设置',
			)
		),
		'cate' => array (
			'name' => '分类管理',
			'item' => array (
				'add' => '添加分类',
				'edit' => '编辑分类',
				'merge' => '合并分类'
			),
			'func' => array(
				'mod' => '编辑分类',
				'del' => '删除分类',
			)
		),
		'tag' => array (
			'name' => '标签管理',
			'item' => array (
				'add' => '添加标签',
				'edit' => '标签管理',
				'search' => '标签搜索',
				'tidy' => '标签整理'
			)
		),
		'member' => array (
			'name' => '会员管理',
			'item' => array (
				'add' => '添加会员',
				'edit' => '管理会员',
				'check' => '会员审核',
				'tidy' => '会员统计'
			),
			'func' => array(
				'list' => '会员列表',
				'mod' => '编辑会员',
			)
		),
		'group' => array (
			'name' => '分组管理',
			'item' => array (
				'count' => '分组统计',
				'admin' => '管理组',
				'user' => '会员组'
			),
			'func' => array(
				'mod' => '权限编辑',
			)
		),
		'message' => array (
			'name' => '信息管理',
			'item' => array (
				'email' => '发送邮件',
				'msg' => '发送短信'
			)
		),
		'check' => array (
			'name' => '审核管理',
			'item' => array (
				'article' => '主题审核',
				'comment' => '评论审核'
			)
		),
		'batch' => array (
			'name' => '内容管理',
			'item' => array (
				'article' => '主题',
				'comment' => '评论',
				'attachment' => '附件'
			)
		),
		'special' => array (
			'name' => '专题管理',
			'item' => array (
				'add' => '添加主题',
				'article' => '主题列表',
				'comment' => '批量添加'
			)
		),
		'tpl' => array (
			'name' => '模板管理',
			'item' => array (
				'view' => '在线模板编辑',
				'guide' => '调用生成向导',
				'list' => '模板调用管理',
				'special' => '特殊模板管理',
			),
			'func' => array(
				'edit' => '模板编辑',
				'adddir' => '新建目录',
				'addfile' => '新建文件',
				'rename' => '重命名',
			)
		),
		'module' => array (
			'name' => '模块管理',
			'item' => array (
				'add' => '添加模块',
				'edit' => '管理模块'
			),
			'func' => array(
				'mod' => '模块设置',
				'del' => '删除模块',
				'help' => '模块帮助',
			)
		),
		'plugin' => array (
			'name' => '插件',
			'item' => array (
				'add' => '添加插件',
				'edit' => '插件管理',
			),
			'func' => array(
				'mod' => '插件设置',
				'del' => '卸载插件',
			)
		),
		'tool' => array (
			'name' => '系统工具',
			'item' => array (
				'cache' => '更新缓存',
				'recount' => '数据重建',
				'filepermission' => '文件权限检查'
			)
		),
		'database' => array (
			'name' => '数据库管理',
			'item' => array (
				'status' => '数据库状态',
				'export' => '数据备份',
				'import' => '数据恢复',
				'optimize' => '数据表优化'
			),
			'func' => array(
				'checksqlfile' => 'SQL文件检查',
			)
		),
		'announcement' => array (
			'name' => '网站公告',
			'item' => array (
				'add' => '添加公告',
				'edit' => '管理公告',
			),
			'func' => array(
				'mod' => '编辑公告'
			)
		),
		'link' => array (
			'name' => '友情链接',
			'item' => array (
				'add' => '添加链接',
				'edit' => '链接管理'
			),
			'func' => array(
				'mod' => '编辑链接'
			)
		),
		'log' => array (
			'name' => '日志管理',
			'item' => array (
				'admin' => '后台管理日志',
				'login' => '后台登录日志',
				'common' => '前台管理日志'
			)
		)
	)
)
?>
