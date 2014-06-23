<?php
/**
 * @version $Id: member.lang.php v2.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

$member_message = array(
	'illegal_request' => '错误的请求参数',
	'member_title' => '控制面板',
	'profile_basic_title' => '个人资料',
	'profile_password_title' => '密码设置',
	'profile_avatar_title' => '头像设置',
	'profile_avatar_failed' => '头像修改失败，参数错误或者是没有权限。',
	'profile_upload_avatar_nopermission' => '修改头像成功',
	'profile_mod_success' => '个人资料编辑成功！',

	'msg_title' => '短信息',
	'msg_readed' => '已读',
	'msg_unreaded' => '<span style="color:#f00">未读</span>',
	'msg_sys' => '系统短信',
	'msg_nonexistence' => '短消息不存在或已被删除',
	'msg_send_disable' => '您没有发短消息的权限',
	'msg_send_nonexistence' => '收件人不存在或存在重复',
	'msg_send_self_ignore' => '您不能给自己发消息',
	'msg_addressee_overflow' => '短消息发送失败，收件人信箱已满',
	'msg_send_succeed' => '短消息发送成功',
	'msg_delete_succeed' => '指定消息删除成功',
	'msg_send_invalid' => '短消息发送失败',
	
	
	'collection_title' => '我的收藏夹',
	'collection_update_succeed' => '收藏夹更新成功',
	'collection_thread_nonexistence' => '指定的收藏主题不存在',
	'collection_exists' => '您已经收藏过这个主题',
	'collection_is_full' => '您的收藏夹已满，请在继续操作前删除一些不用的收藏',
	'collection_del_success' => '删除收藏成功',

	'friend_title' => '我的好友',
	'friend_user_nonexistence' => '指定用户不存在',
	'friend_add_invalid' => '用户已存在于您的好友列表中',
	'friend_update_succeed' => '好友列表成功更新',
	'friend_update_failed' => '好友列表成功失败，可能是用户拒绝加您为好友引起的',
	'friend_add_msg_title' => "{$GLOBALS['customer']['username']}添加您为好友",
	'friend_add_msg_content' => "您可以<a href=\"{$GLOBALS['customer']['uurl']}\" target=\"_blank\">点击这里</a>查看他(她)的个人资料，或者<a href=\"{$GLOBALS['pb_sitedir']}member.php?type=my&amp;action=friend&amp;job=add&amp;uid={$GLOBALS['customer']['safeuid']}\">把他(她)加为您的好友。</a>",
	
	'activate_illegal' => '您所用的 ID 不存在或您不是等待验证会员。',

);
?>