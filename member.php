<?php
/**
 * @version $Id: member.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_PAGE', 'member');
require_once './include/common.inc.php';

$member_message = loadLang('member');
$common_message += $member_message;

require_once PBDIGG_ROOT.'include/member.class.php';
require_once PBDIGG_ROOT.'include/validate.func.php';
@include_once PBDIGG_CROOT.'cache_words.php';

$m_uid = intval($customer['uid']);
$ucuid = intval($customer['ucuid']);

if (!$m_uid)
{
	header('Location: login.php');
	exit;
}

!isset($type) && $type = 'profile';
$actionurl = 'member.php?type='.$type.'&amp;action='.$action.'&amp;verify='.$verifyhash;

switch ($type)
{
	case 'profile':
		$Member = new Member();
		if ($action == 'basic')
		{
			//基本资料
			if (checkPost())
			{
				require_once PBDIGG_ROOT.'data/cache/cache_words.php';
				require_once PBDIGG_ROOT.'data/cache/cache_reg.php';
				require_once PBDIGG_ROOT.'include/ubb.func.php';
				$Member->Action('mod', array('uid'=>$m_uid, 'username'=>'', 'emailpassword'=>(isset($emailpassword) ? $emailpassword : ''), 'email'=>$email, 'publicemail'=>isset($publicemail), 'gender'=>(int)$gender, 'ignore'=>'0', 'qq'=>$qq, 'msn'=>$msn,'site'=>$site,'location'=>$location,'birthday'=>$year.'-'.$month.'-'.$day,'signature'=>$signature,'showsign'=>isset($showsign)));
				redirect('profile_mod_success', $actionurl);
			}
			else
			{
				radioChecked('gender_', $customer['gender'], 4);
				$publicemail = ($customer['publicemail']) ? 'checked="checked"' : '';
				$showsign = ($customer['showsign']) ? 'checked="checked"' : '';
				list($year, $month, $day) = explode('-', $customer['birthday']);
				require_once PBDIGG_ROOT.'include/DateTime.php';
				$year_option = html_select($_YEAR, 'year', $year, 'id="year"');
				$month_option = html_select($_MONTH, 'month', $month, 'id="month"');
				$day_option = html_select($_DAY, 'day', $day, 'id="day"');
				$pb_seotitle = $member_message['profile_basic_title'];
			}
		}
		elseif ($action == 'password')
		{
			//密码
			if (checkPost())
			{
				$Member->Action('password', array($m_uid, $newpw, $rnewpw, $oldpw, TRUE));
				redirect('member_password_mod_success', 'login.php');
			}
			$pb_seotitle = $member_message['profile_password_title'];
		}
		elseif ($action == 'avatar')
		{
			//头像
			$iflinkavatar = $allowavatar && $pb_selfavat;
			$ifuploadavatar = $iflinkavatar && $pb_avatupload;
			
			if (checkPost())
			{
				if ($_FILES['uploadavatar']['error'] === UPLOAD_ERR_OK)
				{
					//上传头像
					!$ifuploadavatar && showMsg('profile_avatar_failed');
					require_once PBDIGG_ROOT.'include/Upload.class.php';
					$Upload = new Upload();
					!$Upload->getFiles() && showMsg('profile_avatar_failed');

					$avatardata = $Upload->moveFile('avatar', $m_uid, array('jpg', 'jpeg', 'gif', 'png'), $pb_avatsize);
					$avatardir = str_pad(substr($m_uid, -2),2,'0',STR_PAD_LEFT);
					$DB->db_exec("UPDATE {$db_prefix}members SET avatar = '".addslashes($avatardir.'/'.$m_uid.'.'.$avatardata[0][2].'|1')."' WHERE uid = '$m_uid'");

					if (preg_match('~^'.preg_quote($_PBENV['PB_URL'].'images/avatars/'.$avatardir.'/'.$m_uid).'.('.implode('|', array_diff(array('jpg', 'jpeg', 'gif', 'png'), array($avatardata[0][2]))).')$~i', $customer['avatar'], $m) && file_exists(PBDIGG_ROOT.'images/avatars/'.$avatardir.'/'.$m_uid.'.'.$m[1]))
					{
						PDel(PBDIGG_ROOT.'images/avatars/'.$avatardir.'/'.$m_uid.'.'.$m[1]);
					}
				}
				elseif ($iflinkavatar && $linkavatar != 'http://')
				{
					//链接头像
					(!$iflinkavatar || !checkAvatar($linkavatar)) && showMsg('profile_avatar_failed');
					$DB->db_exec("UPDATE {$db_prefix}members SET avatar = '".$linkavatar."|2' WHERE uid = ".$m_uid);
					delUploadAvatar($m_uid);
					
				}
				elseif ($defaultavatar)
				{
					//默认头像
					$avatar_list = PListFile(PBDIGG_ROOT.'images/portrait', array('gif','jpg','jpeg','png'));
					(!in_array($defaultavatar, $avatar_list) || !$avatar_list) && showMsg('profile_avatar_failed');
					$DB->db_exec("UPDATE {$db_prefix}members SET avatar = '".$defaultavatar."|3' WHERE uid = '$m_uid'");
					delUploadAvatar($m_uid);
				}
				redirect('profile_mod_success', $actionurl);
			}
			else
			{
				$pb_seotitle = $member_message['profile_avatar_title'];
				if ($pb_ucenable)
				{
					$uploadHTML = $UC->get_avatar_upload($customer['ucuid']);
				}
				else
				{
					$defualtAvatars = PListFile(PBDIGG_ROOT.'images/portrait', array('gif','jpg','jpeg','png'));
					$uploadavatsize = floor($pb_avatsize / 1024);
				}
			}
		}
		else
		{
			$pb_seotitle = $member_message['member_title'];
			$customer['lastvisit'] = gdate($customer['lastvisit'], 'Y-m-d H:i');
			$activeNotice = $customer['groupid'] == '6' ? 1 : 0;
		}
		break;

	case 'msg':

		require_once PBDIGG_ROOT.'include/msg.class.php';
		$multLink = '';
		!in_array($action, array('receivebox','sendbox','write','del','drop')) && $action == 'receivebox';
		$msg = new msg();
		$msgdata = $msg->Action($action);
		$pb_seotitle = $member_message['msg_title'];
		if (!$pb_ucenable || !$uc_msg)
		{
			$receiveMsgNum = intval($DB->fetch_first("SELECT COUNT(*) num FROM {$db_prefix}message WHERE tuid = '$m_uid' AND type = 'r' AND ifsys = 0"));
			$msgper = $msgmax ? round($receiveMsgNum / $msgmax, 5) * 100 : 100;
			$msgper >= 90 && $msgper = '<font style="color:#f00">'.$msgper.'</font>';
		}
		break;

	case 'my':
		if ($action == 'collection')
		{
			if ($job == 'add' && checkPostHash($verify))
			{
				$thread = $DB->fetch_one("SELECT tid, cid FROM {$db_prefix}threads WHERE tid = '$tid'");
				!$thread && showMsg('collection_thread_nonexistence');
				$tids = ',';
				$num = 0;
				$query = $DB->db_query("SELECT tid FROM {$db_prefix}tdata WHERE uid = '$m_uid' AND type = 'collection'");
				while ($rs = $DB->fetch_all($query))
				{
					$tids .= $rs['tid'].',';
				}
				strpos($tids, ",$tid,") !== FALSE && showMsg('collection_exists');
				$DB->db_num($query) >= $favorsmax && showMsg('collection_is_full');
				$DB->db_exec("INSERT INTO {$db_prefix}tdata (cid, tid, uid, type) VALUES ('".$thread['cid']."', '".$thread['tid']."', '$m_uid', 'collection')");
				redirect('collection_update_succeed', $actionurl);
			}
			elseif ($job == 'del' && checkPost())
			{
				if ($tids && is_array($tids))
				{
					$tidstr = '';
					foreach ($tids as $m)
					{
						$tidstr .= ($tidstr ? ',' : '').(int)$m;
					}
					$tidstr && $DB->db_exec("DELETE FROM {$db_prefix}tdata WHERE tid IN ($tidstr) AND uid = '$m_uid' AND type = 'collection'");
					redirect('collection_update_succeed', $actionurl);
				}
				else
				{
					showMsg('illegal_request');
				}
			}
			else
			{
				$recordNum = intval($DB->fetch_first("SELECT COUNT(*) num FROM {$db_prefix}tdata WHERE uid = '$m_uid' AND type = 'collection'"));
				$pagesize = (int)$pb_aperpage;
				$limit = sqlLimit($p, $pagesize);
				$multLink = multLink($p, $recordNum, 'member.php?type=my&amp;action=collection&amp;', $pagesize);
				$query = $DB->db_query("SELECT td.tid, t.uid, t.subject, t.realurl, c.cid, c.name cate 
										FROM {$db_prefix}tdata td 
										LEFT JOIN {$db_prefix}threads t 
										ON td.tid = t.tid 
										LEFT JOIN {$db_prefix}categories c 
										ON td.cid = c.cid 
										WHERE td.uid = '$m_uid' 
										AND type = 'collection' $limit");
				$collections = array();
				while ($rs = $DB->fetch_all($query))
				{
					$rs['subject'] = PBSubstr($rs['subject'], $pb_indextitle);
					$rs['turl'] = 'show.php?tid='.$rs['tid'];
					$rs['curl'] = 'category.php?cid='.$rs['cid'];
					$collections[] = $rs;
				}
				$favorsper = $favorsmax ? round($recordNum / $favorsmax, 5) * 100 : 100;
				$favorsper >= 90 && $favorsper = '<font style="color:#f00">'.$favorsper.'</font>';
				$pb_seotitle = $member_message['collection_title'];
			}
		}
		elseif ($action == 'friend')
		{
			require_once PBDIGG_ROOT.'include/friend.class.php';
			$multLink = '';
			!in_array($job, array('add','delete','my')) && $job = 'my';
			$friend = new friend();
			$frienddata = $friend->Action($job);
			$pb_seotitle = $member_message['friend_title'];
		}
		break;
	case 'reactive':
		//重新激活账号
		if ($customer['groupid'] == '6')
		{
			require_once PBDIGG_ROOT.'include/mail.inc.php';
			$register_message = loadLang('register');
			$activecode = $customer['uid'].'|'.$timestamp.'|'.random(8);
			$activecode = rawurlencode(PEncode($activecode, $pb_sitehash));
			$mail_content = str_replace(array('{!--username--}','{!--activecode--}'), array($customer['username'],$activecode), $register_message['register_active_body']);
			PMail($customer['email'], $register_message['register_active_subject'], $mail_content);
			redirect('register_email_active', 'member.php');
		}
		else
		{
			redirect('activate_illegal', 'member.php');
		}
		break;
	default:
		showMsg('illegal_request');
		break;
}

function delUploadAvatar($uid)
{
	global $DB, $db_prefix;
	$avatardata = $DB->fetch_first("SELECT avatar FROM {$db_prefix}members WHERE uid = '$uid'");
	if ($avatardata)
	{
		list($dir, $type) = explode('|', $avatardata);
		$type == '1' && PDel(PBDIGG_ROOT.'images/avatars/'.$dir);
	}
	return TRUE;
}
require_once pt_fetch('member');

PBOutPut();
?>
