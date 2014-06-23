<?php
/**
 * @version $Id: common.lang.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

$common_message = array (
	/****** 公共 ******/
	'illegal_request' => '错误的请求参数',
	'action_nopermission' => '您所在的用户组无此操作权限',
	'refresh_limit' => '系统设定页面刷新时间间隔为 {$pb_refreshtime} 秒。',
	'ip_limit' => '您的IP被禁止访问，请与管理员联系。',
	'anonymity' => '匿名网友',
	'secrecy' => '保密',
	'null' => '无',
	'male' => '男',
	'female' => '女',
	'origianl' => '原创',
	'first_page' => '首页',
	'nav_separator' => ' >> ',
	'more_page' => '更多',
	'visit_deny' => '您所在的分组无权访问网站，请重新登陆',
	'checkcode_error' => '您输入的验证码不正确',
	'checkqa_error' => '您输入的验证问题答案不正确',
	'illegal_data' => '读取数据错误：您请求的链接无效，或者数据已被删除！',
	'auth_error' => '用户安全认证信息已修改，请重新登录！<br /><br />如果您无法退出，请点选 IE 工具 => 选项 然后手动清除COOKIE',
	'prev' => '上一页',
	'next' => '下一页',
	'upload' => '上传',
	'insert' => '插入',
	'delete' => '删除',
	'year' => '年',
	'month' => '月',
	'day' => '日',
	'publish' => '发表',
	'ubb_quote' => '引用',
	/****** func & class ******/

	'format_post_day' => '天',
	'format_post_hour' => '小时',
	'format_post_minutebefore' => '分钟前',

	'attachment_ext_notallowed' => '不支持上传此类扩展名的附件',
	'attachment_size_invalid' => '您上传的附件尺寸超过系统设定值，无法继续上传',
	'attachment_mkdir_failed' => '附件目录建立失败，可能是目录属性设置问题，请与管理员联系',
	'attachment_illegal_image' => '您上传的附件不是有效的图像文件，无法继续上传',
	'attachment_save_error' => '附件文件无法保存到服务器，可能是目录属性设置问题，请与管理员联系。',

	'msg_addressee_overflow' => '收件方信箱已满',

	'validate_title_lengtherror' => '标题长度不能超过 {$titlelenmax} 个字符，少于 {$titlelenmin} 个字符，请返回修改。',
	'validate_content_lengtherror' => '主题长度不能超过 {$contentlenmax} 个字符，少于 {$contentlenmin} 个字符，请返回修改。',
	'validate_comment_lengtherror' => '主题长度不能超过 {$commentlenmax} 个字符，少于 {$commentlenmin} 个字符，请返回修改。',
	'validate_banwords_exist' => '您填写的内容(如主题、评论、标签、签名、短消息等)包含不良内容而无法提交，请返回修改。',
	'validate_banlinks_exist' => '您填写的文章链接包含不良网站地址而无法提交，请返回修改。',
	'validate_tag_num_limit' => '单个主题标签数不能超过5个，请返回修改。',
	'validate_location_length_limit' => '“来自”信息不能超过30个字符，请返回修改。',
	'validate_illegal_url' => 'URL地址不符合规则，请返回修改。',
	'validate_msg_subject_limit' => '短信标题不得超过 80 字符，内容不得超过 1000 字符',
	
	'ubb_qoute' => '引用',

	/****** 通行证 ******/
	'passport_login' => '系统开启通行证功能，请到 <a href="{$loginurl}">通行证登录地址</a> 登录系统',
	'passport_logout' => '系统开启通行证功能，请到 <a href="{$logouturl}">通行证登录地址</a> 退出登录',
	'passport_register' => '系统开启通行证功能，请到 <a href="{$regurl}">通行证登录地址</a> 注册',
	'passprot_illegal_request' => '系统没有开启通行证功能',
	'passprot_lack_params' => '数据检验失败，缺少参数',
	'passport_check_error' => '数据安全检验失败',
	'passprot_expired_error' => '通行证请求超时',
	'passprot_illegal_username' => '用户名无效',

	/****** 模板调用 ******/
	'tpl_empty_content' => '调用内容不存在，可能是模板内的标识不正确引起的。<br /><a href="http://bbs.pbdigg.com/thread-2853-1-1.html" target="_blank">点击寻求帮助</a>',
	'tpl_var_empty' => '调用模板不存在，可能是模板内标签与后台不匹配引起的。<br /><a href="http://bbs.pbdigg.com/thread-2853-1-1.html" target="_blank">点击寻求帮助</a>',

	/****** member ******/
	'member_add_nopermission' => '无添加管理员用户的权限',
	'member_mod_nopermission' => '无修改管理员用户的权限',
	'member_lack_userdata' => '请输入用户名和密码',
	'member_uid_empty' => '用户ID为空',
	'member_field_notfull' => '用户信息不完整',
	'member_name_lengtherror' => '用户名长度不能超过 {$reg_maxname} 个字符，少于 {$reg_minname} 个字符',
	'member_illegal_name' => '用户名包含不符合规则的字符，请返回修改。',
	'member_illegal_password' => '密码包含不符合规则的字符，请返回修改。',
	'member_banned_name' => '用户名包含系统禁止注册词语',
	'member_rpassword_error' => '两次输入的密码不一致',
	'member_password_less_six' => '密码长度不能小于 6 个字符',
	'member_username_exist' => '用户名已经存在',
	'member_email_exist' => 'Email 地址已经被注册',
	'member_email_format_error' => 'Email 格式不正确',
	'member_email_noallowed' => 'Email 地址不允许注册',
	'member_not_exist' => '用户不存在',
	'member_del_failed' => '用户删除失败',
	'member_visit_denied' => '您的帐户被拒绝访问，请与管理员联系。',
	'member_active_account' => '登录成功，您的帐号处于待激活状态，现在将转入控制面板。',
	'member_login_failed' => '用户名或者密码错误，登录失败！',
	'member_login_success' => '恭喜您，登陆成功',
	'member_logout_success' => '您已经成功退出',
	'member_safeqa_error' => '安全问题错误',
	'member_passport_mod_invalid' => '站点开启通行证，请到服务器端修改用户名、密码和电子邮件信息。',
	'member_oldpassword_error' => '原密码不正确，您不能修改密码或 Email，请返回。',
	'member_password_mod_success' => '密码修改成功，请重新登录。',
	'uc_name_exist' => '登陆失败，请确认是否正确导入会员数据到UCenter数据库！',
	'uc_import_success' => '用户数据成功导入UCenter数据库',
	'uc_member_not_exist' => '用户不存在UCenter数据库，登陆失败！',

	/****** 首页 ******/
	'index_illegal_time_param' => '错误的时间参数',
	'index_tag_records_empty' => '标签或文章信息不存在',

	/****** 分类 ******/
	'cate_no_exist' => '指定的分类不存在',
	'cate_no_permission' => '您所在分组无访问指定分类的权限',
	
	/****** 内容 ******/
	'show_illegal_tid' => '指定的文章不存在',
	'show_cate_no_permission' => '您无权访问指定文章所在的分类',
	'show_article_unchecked' => '文章尚未审核通过，请返回！',
	'show_module_closed' => '{$currentModuleData[\'name\']}模块已经关闭，请返回！',
	'show_read_denied' => '用户组权限：您所属的用户组没有阅读文章的权限',
	'show_title_shield' => '主题被自动屏蔽！',
	'show_content_shield' => '您查看的主题已被管理员屏蔽！',
	'show_auto_shield' => '您查看的主题作者被禁言，主题被自动屏蔽！',
	'show_admin_shield' => '该主题已被屏蔽！以下为原始内容',
	'show_nolinkarticle' => '暂无相关文章',
	'show_noannony' => '最近还没有登录用户关注过这篇文章…',
	'show_noarticle' => '无文章',
	
	/****** privacy ******/
	'privacy_title' => '隐私权政策',
	
	/****** rss.php ******/
	'rss_title' => 'RSS订阅',
	
	/****** js.php ******/
	'js_title' => 'JS调用',
	'js_transfer_closed' => '内容调用未启用',
	'js_transfer_denied' => '调用被拒绝',
	'js_illegal_param' => '错误的调用参数',
	'js_total_member' => '会员总数',
	'js_total_threads' => '主题数',
	'js_total_comments' => '评论数',
	'js_new_member' => '最新会员',
	'js_total_views' => '点击数',
	'js_total_digg' => 'digg数',
	'js_total_bury' => 'bury数',

	/****** announcement.php ******/
	'announcement_title' => '网站公告',

	/****** attachment.php ******/
	'attachment_download_denied' => '您正在下载的文件来自{$pb_sitename}，请不要从外部链接下载本站的附件。',
	'attachment_noexist' => '访问的附件不存在或无法读取。',
	'attachment_norecord' => '附件记录不存在。',
	
	/****** search.php ******/
	'search_title' => '搜索',
	'search_nopermission' => '您所在的分组无搜索权限',
	'search_nocontent' => '无匹配内容',
	'search_keywords_invalid' => '指定的搜索关键字不能少于2个字符，请返回重新填写。',
	'search_fullsearch_nopermission' => '您所在的分组无全文搜索权限',
	'search_flood_limit' => '系统设定两次搜索间隔时间为{$searchmax}秒',
	'search_to_result' => '搜索成功，下面转入结果页...',
	
	/****** sitemap.php ******/
	'sitemap_title' => '网站地图',
	
	/****** archive.php ******/
	'archive_title' => '文章归档',
)
?>
