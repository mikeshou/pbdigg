<?php
/**
 * 客户端
 * action=xxx&server=xxx&key=xxx&hash=xxx
 * 
 * $passportMember = array('username'=>'xxx','password'=>'原始密码','timestamp'=>'发起请求时间戳','email'=>'电子邮件','currency'=>'积分','cookie'=>'cookie保存信息','forward'=>'返回地址');
 * 
 * 
 */

define('PB_PAGE', 'passport');
define('IN_PASSPORT', TRUE);
require_once './include/common.inc.php';
require_once PBDIGG_CROOT.'cache_reg.php';
require_once PBDIGG_ROOT.'include/member.class.php';
require_once PBDIGG_ROOT.'include/validate.func.php';

(!$pb_passport || $pb_passporttype != 'client') && showMsg('passprot_illegal_request');

(!preg_match('~^[_a-z0-9]+$~i', $server) || $action != 'login' && $action != 'logout') && showMsg('passprot_lack_params');

$passportMember = array();

require_once PBDIGG_ROOT.'api/passport.'.$server.'.php';

$passprot = new passport($passportMember);

$passprot->$action();

class passport
{
	var $_user = array();
	
	var $_memberFields = array('username', 'password', 'email', 'currency');

	var $DB = null;
	
	var $Member = null;

	var $db_prefix = '';

	function passport($user)
	{
		$this->__construct($user);
	}	
	function __construct($user)
	{
		global $DB, $db_prefix;
		$this->_user = $this->checkData($user);
		$this->DB = $DB;
		$this->db_prefix = $db_prefix;
		$this->Member = new Member();
	}

	function checkData($user)
	{
		global $timestamp;

		if (!$user['username'] || !$user['password'] || !$user['timestamp'] || !is_numeric($user['timestamp'])) showMsg('passprot_lack_params');
		if ($timestamp - $user['timestamp'] > 3600) showMsg('passprot_expired_error');
		!is_numeric($user['cookie']) && $user['cookie'] = 0;
		if (!isEMAIL($user['email'])) unset($user['email']);
		$user['currency'] = (int)$user['currency'];
		if ($user['username'] != htmlspecialchars($user['username'])) showMsg('passprot_illegal_username');

		foreach ($user as $k => $v)
		{
			if (!in_array($k, array('username', 'password', 'email', 'currency', 'cookie', 'forward'))) unset($user[$k]);
		}
		return $user;
	}

	function memberFileds()
	{
		$fileds = array();
		foreach ($this->_memberFields as $v)
		{
			array_key_exists($v, $this->_user) && $fileds[] = $v;
		}
		return $fileds;
	}

	function login()
	{
		$fileds = $this->memberFileds();
		reS($this->_user);
		$member = $this->DB->fetch_one("SELECT uid,".implode(',', $fileds)." FROM {$this->db_prefix}members WHERE username = '".$this->_user['username']."'");

		if ($member)
		{
			$uid = (int)$member['uid'];
			$password = $member['password'];
			foreach ($fileds as $v)
			{
				if ($member[$v] != $this->_user[$v])
				{
					$this->Member->Action('mod', array_merge($this->_user, array('uid'=>$uid,'ismd5password'=>1,'ignore'=>1)));
					break;
				}
			}
		}
		else
		{
			$newMember = array (
				'username'=>$this->_user['username'],
				'password'=>$this->_user['password'],
				'ismd5password'=>1,
				'email'=>$this->_user['email'],
				'currency'=>(int)$this->_user['currency'],
				'groupid'=>7,
				'ignore'=>1,
			);

			$this->Member->Action('add', $newMember);
			$member = $this->Member->getNewUser();
			$uid = (int)$member[0];
			$password = $member[3];
		}
		sCookie('pb_auth', PEncode($uid."\t".pbNewPW($password), $GLOBALS['pb_sitehash']), $this->_user['cookie']);
		$this->redirect();
	}
	
	function logout()
	{
		$this->Member->Action('out');
		$this->redirect();
	}

	function redirect()
	{
		$jumpurl = $this->_user['forward'] ? $this->_user['forward'] : $GLOBALS['_PBENV']['PB_URL'];
		location($jumpurl);
	}
}

?>