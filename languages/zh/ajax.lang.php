<?php
/**
 * @version $Id: ajax.lang.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

$ajax_message = array(
	'ajax_action_disabled' => '错误：请求数据或者用户权限错误。',
	'ajax_action_success' => '操作成功',
	'ajax_dbaction_repeat' => '请勿重复操作',
	'ajax_comment_action_error' => '评论不存在或者您无操作权限',
	'ajax_comment_edit_success' => '评论编辑成功',
	'ajax_comment_del_success' => '评论删除成功',
	'ajax_comment_check_success' => '评论审核成功',
	'ajax_article_action_error' => '文章不存在或者您无操作权限',
	'ajax_same_cid' => '相同分类文章无法复制或者移动操作',
	'ajax_module_nonexistence' => '模型无法匹配，操作失败',

	'ajax_del_article' => '删除文章',
	'ajax_copy_article' => '复制文章',
	'ajax_move_article' => '移动文章',
	'ajax_check_article' => '审核文章',
	'ajax_lock_article' => '锁定文章',
	'ajax_shield_article' => '屏蔽文章',
	'ajax_top_article' => '置顶文章',
	'ajax_commend_article' => '推荐文章',
	'ajax_first_article' => '头条文章',
	'ajax_del_comment' => '删除评论',
	'ajax_check_comment' => '审核评论',
	'ajax_shield_comment' => '屏蔽评论',
	'ajax_del_attachment' => '删除附件',

	'ajax_check_action' => '审核',
	'ajax_uncheck_action' => '取消审核',
	'ajax_shield_action' => '屏蔽',
	'ajax_unshield_action' => '取消屏蔽',
	'ajax_lock_action' => '锁定',
	'ajax_unlock_action' => '取消锁定',
	'ajax_top_action' => '置顶',
	'ajax_untop_action' => '取消置顶',
	'ajax_first_action' => '头条',
	'ajax_unfirst_action' => '取消头条',
	'ajax_commend_action' => '推荐',
	'ajax_uncommend_action' => '取消推荐',

	'ajax_username_toolong' => "对不起，您的用户名超过 {$GLOBALS['reg_maxname']} 个字符，请返回输入一个较短的用户名。",
	'ajax_username_tooshort' => "对不起，您输入的用户名小于 {$GLOBALS['reg_minname']} 个字符, 请返回输入一个较长的用户名。",

	'ajax_comment_nopermission' => '您所在分组无发表评论的权限',
	'ajax_comment_success' => '评论成功',
	'ajax_comment_ready_check' => '评论发布成功，请等待管理员审核。',
	'ajax_flood_ctrl' => "对不起，您两次发表间隔少于 {$GLOBALS['pb_reposttime']} 秒，请不要灌水！",

);

?>