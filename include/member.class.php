<?php
/**
 * @version $Id: member.class.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

!defined('IN_PBDIGG') && exit('Access Denied!');

if ($pb_ucenable)
{
	require_once PBDIGG_ROOT.'include/uc.class.php';
}

class Member
{
	/**
	 * 数据引用
	 */
	var $DB;
	
	var $db_prefix;
	
	var $UC;
	
	/**
	 * 用户信息
	 */
	var $customer;
	
	/**
	 * 用户ID
	 */
	var $uids = array();
	
	/**
	 * 操作类型
	 */
	var $action = NULL;

	var $newuser = array();

	function __construct()
	{
		global $DB, $customer, $db_prefix, $pb_ucenable, $UC;
		$this->DB = $DB;
		$this->db_prefix = $db_prefix;
		$this->customer = $customer;
		if ($pb_ucenable && !is_object($UC))
		{
			$this->UC = $UC = new uc();
		}
	}

	function Member()
	{
		$this->__construct();
	}
	function setUID($uid)
	{
		$this->uids = $this->ckuid($uid);
	}
	function setNewUser($newuser)
	{
		$this->newuser = $newuser;
	}
	function getNewUser()
	{
		return $this->newuser;
	}
	function ckuid($uids)
	{
		!is_array($uids) && $uids = (array)$uids;
		foreach ($uids as $k => $uid)
		{
			if ($uid <= 0)
			{
				unset($uids[$k]);
			}
			else
			{
				$uids[$k] = (int)$uid;
			}
		}
		if (!count($uids)) showMsg('member_uid_empty');
		return $uids;
	}
	function Action($action, $data = '', $extra = '')
	{
		$this->action = $action;
		switch ($this->action)
		{
			case 'add':
				$this->add($data);
				break;
			case 'del':
				$this->del($data);
				break;
			case 'mod':
				$this->mod($data);
				break;
			case 'check':
				$this->check($data, $extra);
				break;
			case 'password':
				$this->password($data);
				break;
			case 'in':
				$this->in($data);
				break;
			case 'out':
				$this->out();
				break;
			default:
				showMsg('illegal_request');
				break;
		}
	}

	/**
	 * 添加用户
	 * 
	 * @param Array $member array('username'=>'用户名', 'password'=>'密码', 'email'=>'电子邮件', 'groupid'=>'等级', 'ignore'=>'忽略用户名检查', 'currency'=>'积分',, 'ucRegister'=>'是否uc注册', 'exp'=>array())
	 */
	function add($member)
	{
		global $timestamp, $forward, $_grouplevel, $pb_ucenable, $pb_passport, $_PBENV, $Cache, $reg_minname, $reg_maxname, $reg_bannames, $reg_credit, $pb_passporttype, $reg_emailactive, $reg_sendemail, $pb_sitehash, $register_message, $reg_allowsameip;
		
		if (!isset($reg_minname))
		{
			require_once PBDIGG_CROOT.'cache_reg.php';
			require_once PBDIGG_ROOT.'include/validate.func.php';
		}
		$username = $password = $rpassword = $email = $currency = $groupid = $ignore = $exp = $ucRegister = $ismd5password = '';
		$uc_uid = $adminid = 0;
		@extract($member, EXTR_OVERWRITE);
		unset($member);
		(!$username || !$password || !$email || !$groupid) && showMsg('member_field_notfull');
		$name_length = strlen($username);
		($name_length < $reg_minname || $name_length > $reg_maxname) && showMsg('member_name_lengtherror');
		//用户名
		!preg_match('~^[_0-9a-z\x7f-\xff]+$~i', $username) && showMsg('member_illegal_name');
		//密码
		preg_match('~[\x00-\x20]~i', $password) && showMsg('member_illegal_password');

		$reg_bannames && !$ignore && (strpos($reg_bannames, $username) !== FALSE) && showMsg('member_banned_name');
		if ($ismd5password)
		{
			$md5password = $password;
		}
		else
		{
			$password != $rpassword && showMsg('member_rpassword_error');
			if (strlen($password) < 6) showMsg('member_password_less_six');
			$md5password = md5($password);
		}

		!$email || !isEMAIL($email) && $email = substr(md5($timestamp.random(5)), 0, 8).'@pbdigg.net';
		$currency = is_int($currency) ? $currency : (int)$reg_credit;

		if (!isset($_grouplevel[$groupid]) || $groupid == '7' || $groupid == '5')
		{
			$groupid = '-1';
		}
		if ($groupid == '1' || $groupid == '2')
		{
			!SUPERMANAGER && showMsg('member_add_nopermission');
			$adminid = $groupid;
		}
//		$realgroup = $groupid == '-1' ? '7' : $groupid;
		$realgroup = '7';
		$rs = $this->DB->fetch_one("SELECT username, email FROM {$this->db_prefix}members WHERE username = '$username' OR email = '$email'");
		if ($rs)
		{
			addslashes($rs['username']) == $username && showMsg('member_username_exist');
			addslashes($rs['email']) == $email && showMsg('member_email_exist');
		}

		if ($pb_ucenable && !$pb_passport)
		{
			if ($ucRegister)
			{
				$ucUserData = $this->UC->get_user($username);
				!$ucUserData && showMsg('uc_member_not_exist');
				$uc_uid = (int)$ucUserData[0];
				unset($ucUserData);
			}
			else
			{
				$uc_uid = $this->UC->add_user($username, $password, $email);
			}
		}

		$i_field = $v_field = '';
		if ($exp && is_array($exp))
		{
			foreach ($exp as $k => $v)
			{
				if (!preg_replace('~[a-z0-9_]~i', '', $k))
				{
					$i_field .= ','.$k;
					$v_field .= ','.sqlEscape($v);
				}
			}
		}
		$this->DB->db_exec("INSERT INTO {$this->db_prefix}members (uid, username, password, email, adminid, groupid, regip, regdate, realgroup, currency, lastip, lastvisit, ucuid) VALUES (NULL, '$username', '$md5password', '$email', '$adminid', '$groupid', '".$_PBENV['PB_IP']."', '$timestamp', '$realgroup', '$currency', '".$_PBENV['PB_IP']."', $timestamp, '$uc_uid')");
		$autoid = $this->DB->db_insert_id();
		$this->DB->db_exec("INSERT INTO {$this->db_prefix}memberexp (uid $i_field) VALUES ('$autoid' $v_field)");
		$this->DB->db_exec("UPDATE {$this->db_prefix}sitestat SET newmember = '$username', membernum = membernum + 1 WHERE id = 1");
		$Cache->config();

		$this->setNewUser(array($autoid,$username,$password,$md5password,$email,$uc_uid));

//		require PBDIGG_CROOT.'cache_config.php';
//		require_once PBDIGG_ROOT.'include/mail.inc.php';

		//返回
		if ($ucRegister || defined('IN_PASSPORT') || defined('IN_ADMIN')) return TRUE;

		$reg_allowsameip && PWriteFile(PBDIGG_CROOT.'ip_cache.php', (file_exists(PBDIGG_CROOT.'ip_cache.php') ? '' : "<?php exit;?>\n\n").$timestamp.','.$_PBENV['PB_IP']."\n", 'ab');

		if ($reg_emailactive)
		{
			//send active email
			$activecode = $autoid.'|'.$timestamp.'|'.random(8);
			$activecode = rawurlencode(PEncode($activecode, $pb_sitehash));
			$mail_content = str_replace(array('{!--username--}','{!--activecode--}'), array($username,$activecode), $register_message['register_active_body']);
			PMail($email, $register_message['register_active_subject'], $mail_content);
		}

		if ($reg_sendemail)
		{
			//send welcome email
			global $pb_sitename, $pb_adminmail, $reg_emailcontent;
			$mail_content = str_replace(array('{!--username--}','{!--sitename--}','{!--password--}','{!--siteurl--}','{!--adminmail--}'), array($username,$pb_sitename,$password,$_PBENV['PB_URL'], $pb_adminmail), $reg_emailcontent);
			PMail($email, $register_message['register_welcome_subject'], '<pre>'.$mail_content.'</pre>');
		}

		sCookie('pb_auth', PEncode($autoid."\t".pbNewPW($md5password), $pb_sitehash), 0);

		//passport
		if ($pb_passport && !$pb_ucenable && $pb_passporttype == 'server')
		{
			global $pb_pclienturl, $pb_pserverapi, $pb_passportkey;
			$key = '';
			$userdata = array('uid'=>intval($autoid),'username'=>stripslashes($username),'password'=>$password,'email'=>$email,'currency'=>$currency,'timestamp'=>$timestamp,'cookie'=>0);
			reset($userdata);
			while (list ($k, $v) = each($userdata))
			{
				$key .= $k.'='.rawurlencode($v).'&';
			}
			$key = PEncode($key, $pb_passportkey);
			$jumpurl = $pb_pclienturl.$pb_pserverapi;
			$hash = md5('pbdigglogin'.$key.$forward.$pb_passportkey);
			location($jumpurl.urlconj($jumpurl).'server=pbdigg&action=login&forward='.rawurlencode($forward).'&key='.rawurlencode($key).'&hash='.$hash);
		}
	}

	/**
	 * 编辑用户
	 * 
	 * @param Array $member array('uid'=>'用户ID', 'username'=>'用户名', 'password'=>'密码', 'rpassword'=>'重复密码',  'ismd5password'=>'是否MD5密码', 
	 * 'email'=>'电子邮件', 'publicemail'=>'是否公开电子邮件', 'groupid'=>'等级', 'gender'=>'性别', 'currency'=>'积分', 'regip'=>'注册IP', 'regdate'=>'注册日期', 'ignore'=>'忽略用户名检查', 'exp'=>'其他字段参数'))
	 */
	function mod($member)
	{
		global $_grouplevel, $pb_ucenable, $pb_passport, $pb_passporttype, $customer, $reg_minname, $reg_maxname, $reg_bannames;

		$username = $password = $email = $uid = $ignoreusername = $self = $realname = $namechanged = $ismd5password = $ignore = '';
		$sql = $expsql = array();

		@extract($member, EXTR_OVERWRITE);
		unset($member);
		$rs = $this->DB->fetch_one("SELECT username, email, password, adminid FROM {$this->db_prefix}members WHERE uid = '$uid'");
		if (!$rs) showMsg('member_not_exist');
		$realname = $rs['username'];
		($customer['uid'] == $uid) && $self = 1;

		in_array($rs['adminid'], array('1', '2')) && !defined('IN_PASSPORT') && !SUPERMANAGER && !$self && showMsg('member_mod_nopermission');

		if (!$username)
		{
			$username = $realname;
			$ignoreusername = 1;
		}
		//通行证为客服端不允许修改用户名、密码、电子邮件
		if (!$pb_passport || $pb_passporttype != 'client' || defined('IN_PASSPORT'))
		{
			//用户名
			if (!$ignoreusername)
			{
				$name_length = strlen($username);
				if ($name_length < $reg_minname || $name_length > $reg_maxname)
				{
					showMsg('member_name_lengtherror');
				}
				!preg_match('~^[_0-9a-z\x7f-\xff]+$~i', $username) && showMsg('member_illegal_name');
				if ($reg_bannames && !$ignore && (strpos($reg_bannames, $username) !== FALSE))
				{
					showMsg('member_banned_name');
				}
				$rs = $this->DB->fetch_one("SELECT COUNT(*) num FROM {$this->db_prefix}members WHERE username = '$username'");
				if (!$rs['num'])
				{
					$sql['username'] = $username;
					$namechanged = true;
				}
			}
			//密码
			if ((defined('IN_ADMIN') || defined('IN_PASSPORT')) && $password)
			{
				if (!$ismd5password)
				{
					$password != $rpassword && showMsg('member_rpassword_error');
					if (strlen($password) < 6) showMsg('member_password_less_six');
					preg_match('~[\x00-\x20]~i', $password) && showMsg('member_illegal_password');
				}
				$sql['password'] = $ismd5password ? $password : md5($password);
			}
			//电子邮件
			if ($email != $rs['email'] && isEMAIL($email))
			{
				if (!defined('IN_ADMIN') && !defined('IN_PASSPORT') && md5($emailpassword) != $rs['password'])
				{
					showMsg('member_oldpassword_error');
				}
				$rs = $this->DB->fetch_one("SELECT COUNT(*) num FROM {$this->db_prefix}members WHERE email = '$email' AND uid <> '$uid'");
				!$rs['num'] && $sql['email'] = $email;
			}
			isset($publicemail) && $sql['publicemail'] = $publicemail ? 1 : 0;
		}

		if (defined('IN_ADMIN'))
		{
			$adminid = 0;
			if (!isset($_grouplevel[$groupid]) || $groupid == '7' || $groupid == '5')
			{
				$groupid = -1;
			}
			if ($groupid == '1' || $groupid == '2')
			{
				!SUPERMANAGER && showMsg('member_mod_nopermission');
				$adminid = $groupid;
			}
			$sql['groupid'] = $groupid;
			$sql['adminid'] = $adminid;
			$regip && preg_match('~^[\d\.]{7,15}$~', $regip) && $sql['regip'] = $regip;
			if ($regdate = pStrToTime($regdate)) $sql['regdate'] = $regdate;
		}
		isset($currency) && is_int($currency) && $sql['currency'] = $currency;
		isset($gender) && is_int($gender) && $sql['gender'] = $gender;
		isset($qq) && $expsql['qq'] = $qq && checkQQ($qq) ? $qq : '';
		isset($msn) && $expsql['msn'] = $msn && checkMSN($msn) ? $msn : '';
		isset($site) && $expsql['site'] = $site && checkURL($site) ? $site : '';
		isset($location) && $expsql['location'] = $location && checkLocation($location) ? $location : '';
		isset($birthday) && preg_match('~^[0-9]{4}-[0-9]{2}-[0-9]{2}~i', $birthday) && $expsql['birthday'] = $birthday;
		if (isset($signature))
		{
			$expsql['signature'] = HConvert($signature);
			$expsql['ctsig'] = $expsql['signature'] == signUBB($expsql['signature']) ? 0 : 1;
		}
		isset($showsign) && $expsql['showsign'] = $showsign ? 1: 0;

		if ($sql)
		{
			$strsql = '';
			foreach ($sql as $k => $v)
			{
				if (!preg_replace('~[a-z0-9_]~i', '', $k))
				{
					$strsql .= ($strsql ? ',' : '')."$k = ".sqlEscape($v);
				}
			}
			$strsql && $this->DB->db_exec("UPDATE {$this->db_prefix}members SET $strsql WHERE uid = '$uid'"); 
		}

		if ($expsql)
		{
			$strsql = '';
			foreach ($expsql as $k => $v)
			{
				if (!preg_replace('~[a-z0-9_]~i', '', $k))
				{
					$strsql .= ($strsql ? ',' : '')."$k = ".sqlEscape($v);
				}
			}
			$strsql && $this->DB->db_exec("UPDATE {$this->db_prefix}memberexp SET $strsql WHERE uid = '$uid'"); 
		}

		if ($pb_ucenable && !$pb_passport)
		{
			$this->UC->mod_user($realname, $password, $email);
		}

		if ($namechanged)
		{
			$this->DB->db_exec("UPDATE {$this->db_prefix}threads SET author = '$username' WHERE uid = '$uid'");
			$this->DB->db_exec("UPDATE {$this->db_prefix}comments SET author = '$username' WHERE uid = '$uid'");
		}
	}
	/**
	 * 删除会员
	 */
	function del($uids)
	{
		global $customer, $pb_ucenable, $Cache;
		@set_time_limit(300);
		@ignore_user_abort(TRUE);
		$uids = $this->ckuid($uids);
		$suid = implode(',', $uids);
		if (defined('IN_UCAPI'))
		{
			$uids = array();
			$query = $this->DB->db_query("SELECT uid FROM {$this->db_prefix}members WHERE ucuid IN ($suid)");
			while ($rs = $this->DB->fetch_all($query))
			{
				$uids[] = $rs['uid'];
			}
			$suid = implode(',', $uids);
		}
		foreach ($uids as $value)
		{
			$rs = $this->DB->fetch_one("SELECT groupid, ucuid FROM {$this->db_prefix}members WHERE uid = '$value'");
			if (($rs['groupid'] == '1' && !SUPERMANAGER) || ($value == $customer['uid'])) showMsg('member_del_failed');
			$pb_ucenable && !defined('IN_UCAPI') && $this->UC->delete_user($rs['ucuid']);
		}
		$this->DB->db_exec("DELETE FROM {$this->db_prefix}members WHERE uid IN ($suid)");
		$this->DB->db_exec("DELETE FROM {$this->db_prefix}memberexp WHERE uid IN ($suid)");
		$this->DB->db_exec("DELETE FROM {$this->db_prefix}tdata WHERE uid IN ($suid)");
		$this->DB->db_exec("DELETE FROM {$this->db_prefix}cdata WHERE uid IN ($suid)");
		$this->DB->db_exec("DELETE FROM {$this->db_prefix}friends WHERE uid IN ($suid) OR fuid IN ($suid)");
		$this->DB->db_exec("DELETE FROM {$this->db_prefix}message WHERE (tuid IN ($suid) AND type = 'r') OR (fuid IN ($suid) AND type = 's')");
		$this->DB->db_exec("UPDATE {$this->db_prefix}threads SET author = '', uid = '0' WHERE uid IN ($suid)");
		$rs = $this->DB->fetch_one("SELECT COUNT(*) num FROM {$this->db_prefix}members");
		$membernum = (int)$rs['num'];
		$newmember = $this->DB->fetch_one("SELECT username FROM {$this->db_prefix}members ORDER BY uid DESC LIMIT 1");
		$this->DB->db_exec("UPDATE {$this->db_prefix}sitestat SET newmember = '$newmember', membernum = '$membernum' WHERE id = 1");
		$Cache->config();
	}

	/**
	 * 激活用户
	 */
	function check($uids, $pass = TRUE)
	{
		$suid = implode(',', array_map('intval', $uids));
		$suid && $this->DB->db_exec("UPDATE {$this->db_prefix}members SET groupid = '".($pass ? -1 : 6)."' WHERE uid IN ($suid)");
	}
	/**
	 * 修改密码
	 * @param Array $data Array(用户ID或者用户名, 密码, 重复密码, 原密码, 是否校验原密码)
	 */
	function password($data)
	{
		global $pb_passport, $pb_passporttype, $pb_ucenable;
		list ($uid, $password, $rpassword, $oldpassword, $auth) = $data;
		//通行证为客服端不允许修改用户名、密码、电子邮件
		if ($pb_passport && $pb_passporttype == 'client')
		{
			showMsg('member_passport_mod_invalid');
		}
		$password != $rpassword && showMsg('member_rpassword_error');
		if (strlen($password) < 6) showMsg('member_password_less_six');
		preg_match('~[\x00-\x20]~i', $password) && showMsg('member_illegal_password');
		$sql = is_int($uid) ? "uid='$uid'" : "username='".addslashes(stripslashes($uid))."'";
		$rs = $this->DB->fetch_one("SELECT username, password FROM {$this->db_prefix}members WHERE $sql");
		!$rs && showMsg('member_not_exist');
		if ($auth && (!$oldpassword || $rs['password'] != md5($oldpassword)))
		{
			showMsg('member_oldpassword_error');
		}

		$this->DB->db_exec("UPDATE {$this->db_prefix}members SET password = '".md5($password)."' WHERE $sql LIMIT 1");
		if ($pb_ucenable && !$pb_passport)
		{
			$this->UC->mod_user($rs['username'], $password, '');
		}
		sCookie('pb_auth', '', -1);
	}

	/**
	 * 退出登陆
	 */
	function out()
	{
		global $pb_ucenable, $pb_passport, $pb_passporttype, $forward;
		$uid = $this->ckuid($this->customer['uid']);
		clearcookie();
//		$this->DB->db_exec("DELETE FROM {$this->db_prefix}onlines WHERE uid = '".$uid[0]."' LIMIT 1", FALSE);
		unset($GLOBALS['customer']);
		$forward = isset($forward) ? $forward : forward();

		if ($pb_ucenable && !$pb_passport)
		{
			$this->UC->logout();
		}
		elseif ($pb_passport && $pb_passporttype == 'server')
		{
			global $timestamp, $pb_pclienturl, $pb_pserverapi, $pb_passportkey;
			$key = '';
			$auth = $this->DB->fetch_one("SELECT uid, username, password, email, groupid, currency FROM {$this->db_prefix}members WHERE uid = '$uid'");
			@extract($auth);
			$userdata = array('uid'=>intval($uid),'username'=>stripslashes($username),'password'=>$password,'email'=>$email,'currency'=>$currency,'timestamp'=>$timestamp,'cookie'=>-1);
			reset($userdata);
			while (list ($k, $v) = each($userdata))
			{
				$key .= $k.'='.rawurlencode($v).'&';
			}
			$key = PEncode($key, $pb_passportkey);
			$jumpurl = $pb_pclienturl.$pb_pserverapi;
			$hash = md5('pbdigglogout'.$key.$forward.$pb_passportkey);
			location($jumpurl.urlconj($jumpurl).'server=pbdigg&action=logout&forward='.rawurlencode($forward).'&key='.rawurlencode($key).'&hash='.$hash);
		}
	}
	/**
	 * 用户登陆
	 * @param Array $data Array(用户名, 密码, cookie时间)
	 */
	function in($data)
	{
		global $pb_ucenable, $pb_passport, $pb_passporttype, $timestamp, $_PBENV, $pb_gdcheck, $captcha, $forward;

		list ($username, $password, $persistent) = $data;
		if (!$username || !$password) showMsg('member_lack_userdata');
		!preg_match('~^[_0-9a-z\x7f-\xff]+$~i', $username) && showMsg('member_illegal_name');
		preg_match('~[\x00-\x20]~i', $password) && showMsg('member_illegal_password');
		($pb_gdcheck & 2) && !ckgdcode($captcha) && showMsg('checkcode_error');

		$failedlogin = TRUE;
		
		$email = $currency = '';

		$md5password = md5($password);
		$cookietime = $persistent ? 1 : 0;

		if ($pb_ucenable && !$pb_passport)
		{
			$uc_return = $this->UC->login($username, $password);
			if ($uc_return)
			{
				$ucuid = (int)$uc_return[0];
				$rs = $this->DB->fetch_one("SELECT uid, groupid, password FROM {$this->db_prefix}members WHERE ucuid = '$ucuid'");
				if ($rs)
				{
					$uid = (int)$rs['uid'];
					$groupid = (int)$rs['groupid'];
					if ($md5password != $rs['password'])
					{
						$this->DB->db_exec("UPDATE {$this->db_prefix}members SET password = '$md5password' WHERE uid = '$uid' LIMIT 1");
					}
					$GLOBALS['ucHeader'] = $this->UC->synlogin($ucuid);
				}
				else
				{
					$rs = $this->DB->fetch_one("SELECT COUNT(*) num FROM {$this->db_prefix}members WHERE username = '$username' OR email = '".addslashes($uc_return[3])."'");
					$rs['num'] && showMsg('uc_name_exist');
					$newMember = array (
						'username'=>$username,
						'password'=>$password,
						'rpassword'=>$password,
						'email'=>$uc_return[3],
						'groupid'=>7,
						'ignore'=>0,
						'ucRegister'=>true,
						'exp'=>array('ctsig'=>0)
					);
					$this->add($newMember);
					$member = $this->getNewUser();
					$GLOBALS['ucHeader'] = $this->UC->synlogin($member[5]);
					$uid = (int)$member[0];
					$groupid = '7';
				}
				$failedlogin = FALSE;
			}
		}
		else
		{
			$auth = $this->DB->fetch_one("SELECT uid, password, email, groupid, currency FROM {$this->db_prefix}members WHERE username = '$username'");
			if ($auth && ($auth['password'] == $md5password))
			{
				@extract($auth);
				$failedlogin = FALSE;
			}
		}
		if ($failedlogin)
		{
			PWriteFile(PBDIGG_ROOT.'log/enter.php', '<?php exit(\'Access Denied!\'); ?>'.$timestamp."\t".htmlspecialchars(stripslashes($username))."\t".htmlspecialchars(stripslashes($password))."\t".$_PBENV['PB_IP']."\n");
			showMsg('member_login_failed');
		}
		else
		{
			$this->DB->db_exec("UPDATE {$this->db_prefix}members SET visitnum = visitnum + 1 WHERE uid = '$uid' LIMIT 1");
			sCookie('pb_auth', PEncode($uid."\t".pbNewPW($md5password), $GLOBALS['pb_sitehash']), $cookietime);
			$forward = isset($forward) ? $forward : ($groupid == '6' ? $_PBENV['PB_URL'].'member.php' : forward());
			if ($pb_passport && !$pb_ucenable &&  $pb_passporttype == 'server')
			{
				global $pb_pclienturl, $pb_pserverapi, $pb_passportkey;
				$key = '';
				$userdata = array('uid'=>intval($uid),'username'=>stripslashes($username),'password'=>$password,'email'=>$email,'currency'=>$currency,'timestamp'=>$timestamp,'cookie'=>$cookietime);
				reset($userdata);
				while (list ($k, $v) = each($userdata))
				{
					$key .= $k.'='.rawurlencode($v).'&';
				}
				$key = PEncode($key, $pb_passportkey);
				$jumpurl = $pb_pclienturl.$pb_pserverapi;
				$hash = md5('pbdigglogin'.$key.$forward.$pb_passportkey);
				location($jumpurl.urlconj($jumpurl).'server=pbdigg&action=login&forward='.rawurlencode($forward).'&key='.rawurlencode($key).'&hash='.$hash);
			}
			$groupid == -1 && $groupid = 7;
			@include PBDIGG_CROOT.'cache_usergroup_'.$groupid.'.php';
			if (!$allowvisit)
			{
				clearcookie();
				showMsg('member_visit_denied');
			}
		}
	}
}
?>