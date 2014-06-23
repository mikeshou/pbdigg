<?php
/**
 * @version $Id: uc.class.php v2.1 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

!defined('IN_PBDIGG') && exit('Access Denied!');

require_once PBDIGG_ROOT.'include/uc_client/client.php';

class uc
{
	var $DB = NULL;

	var $db_prefix = '';

	function __construct()
	{
		global $DB, $db_prefix;
		$this->DB = $DB;
		$this->db_prefix = $db_prefix;
	}

	function uc()
    {
    	$this->__construct();
    }

    function add_user($username, $password, $email)
    {
		$uc_uid = uc_user_register($username, $password, $email);
		switch ($uc_uid)
		{
			case -1:
			case -2:
				$message = 'member_illegal_name';
				break;
			case -3:
				$message = 'member_username_exist';
				break;
			case -4:
				$message = 'member_email_format_error';
				break;
			case -5:
				$message = 'member_email_noallowed';
				break;
			case -6:
				$message = 'member_email_exist';
				break;
			default:
				$message = '';
				break;
		}
		$message && showMsg($message);
		$GLOBALS['ucHeader'] = uc_user_synlogin($uc_uid);
		return $uc_uid;
    }
    function delete_user($username)
    {
    	return uc_user_delete($username);
    }
    function mod_user($username, $password, $email)
    {
    	$uc_status = uc_user_edit($username, '', $password, $email, 1);
		switch ($uc_status)
		{
			case -4:
				$message = 'member_email_format_error';
				break;
			case -5:
				$message = 'member_email_noallowed';
				break;
			case -6:
				$message = 'member_email_exist';
				break;
			default:
				$message = '';
				break;
		}
		$message && showMsg($message);
		return $uc_status;
    }
    function get_avatar_upload($uid, $type = 'virtual', $html = TRUE)
    {
    	return uc_avatar($uid, $type, $html);
    }
    function get_user($auth)
    {
    	$isid = is_int($auth) ? 1 : 0;
    	$userdata = uc_get_user($auth, $isid);
    	return $userdata[0] ? $userdata : false;
    }
    function logout()
    {
    	$GLOBALS['ucHeader'] = uc_user_synlogout();
    }
    function login($username, $password)
    {
    	$userdata = uc_user_login($username, $password);
		switch ($userdata[0])
		{
			case '-1':
				$message = 'member_not_exist';
				break;
			
			case '-2':
				$message = 'member_login_failed';
				break;
				
			case '-3':
				$message = 'member_safeqa_error';
				break;
			
			default:
				$message = '';
				break;				
		}
		$message && showMsg($message);
		return $userdata;
    }
    function synlogin($uid)
    {
    	return uc_user_synlogin($uid);
    }
    function merge_user($start = 0, $merge = 0, $percount = 0, $pbuc = 1)
    {
		global $db_name;
		$start = (int)$start;
		$merge = (int)$merge;
		$percount = (int)$percount;
		$UCDB = new MySQL(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME);
		$query = $this->DB->db_query("SELECT * FROM `{$db_name}`.{$this->db_prefix}members LIMIT $start, $percount");
		if (!$this->DB->db_num($query)) showMsg('uc_import_success','admincp.php?action=plugin&job=mod&pmark=ucmerge');
		while($data = $this->DB->fetch_all($query))
		{
			$salt = rand(100000, 999999);
			$password = md5($data['password'].$salt);
			$username = addslashes($data['username']);

			$ucuser = $UCDB->fetch_one("SELECT uid, email FROM ".UC_DBTABLEPRE."members WHERE username = '$username'");
			if($ucuser)
			{
				if ($pbuc)
				{
					$UCDB->db_exec("UPDATE ".UC_DBTABLEPRE."members SET password = '$password', email = '".addslashes($data['email'])."', salt = '$salt', regip = '".addslashes($data['regip'])."', regdate = '".addslashes($data['regdate'])."' WHERE uid = '".(int)$ucuser['uid']."' LIMIT 1");
					$this->DB->db_exec("UPDATE `{$db_name}`.{$this->db_prefix}members SET ucuid = '".(int)$ucuser['uid']."' WHERE uid = '".(int)$data['uid']."' LIMIT 1");
				}
				else
				{
					$this->DB->db_exec("UPDATE `{$db_name}`.{$this->db_prefix}members SET email = '".addslashes($ucuser['email'])."', ucuid = '".(int)$ucuser['uid']."' WHERE uid = '".(int)$data['uid']."' LIMIT 1");
				}
				$merge++;
			}
			else
			{
				$UCDB->db_exec("INSERT INTO ".UC_DBTABLEPRE."members (uid,username,password,email,myid,myidkey,regip,regdate,lastloginip,lastlogintime,salt,secques) VALUES (NULL,'$username','$password','".addslashes($data['email'])."','','','".addslashes($data['regip'])."','".addslashes($data['regdate'])."',0,0,'$salt','')");
				$this->DB->db_exec("UPDATE `{$db_name}`.{$this->db_prefix}members SET ucuid = ".(int)$UCDB->db_insert_id()." WHERE uid = '".(int)$data['uid']."' LIMIT 1");
			}
		}
		return array('start'=>$start + $percount, 'merge'=>$merge);
    }
}
?>