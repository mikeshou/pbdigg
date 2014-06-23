<?php
/**
 * @version $Id: adminlog.class.php v1.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 * 
 * 后台尝试登陆次数控制
 * 
 * 10分钟内超过20次输入错误用户名或者密码，拒绝访问半小时
 * 
 */

class adminLog
{
	var $_adminlog = array();

	var $_timelimit = 600;
	
	var $_wrontnum = 20;
	
	var $_denytime = 1800;
	
	var $_cachefile = '';
	
	var $_lockfile = '';
	
	function adminLog()
	{
		$this->__construct();
	}

	function __construct()
	{
		$_adminlog = array();
		$this->_cachefile = PBDIGG_ROOT.'data/cache/cache_adminlog.php';
		$this->_lockfile = PBDIGG_ROOT.'data/cache/login_lock.tmp';
		if (!@include_once $this->_cachefile)
		{
			writeSafeContent($this->_cachefile, '$_adminlog = array();', 'wb');
		}
		$this->_adminlog = $_adminlog;
	}

	function adminLogAnalyse()
	{
		if (file_exists($this->_lockfile))
		{
			if (($lefttime = ($this->_denytime - ($GLOBALS['timestamp'] - filemtime($this->_lockfile)))) > 0)
			{
				$this->loginexit($lefttime);
			}
			else
			{
				PDel($this->_lockfile);
				$this->_adminlog = array();
			}
		}
		$startime = $GLOBALS['timestamp'] - $this->_timelimit;
		$tryloginnum = $unsetnum = 0;
		foreach ($this->_adminlog as $k => $v)
		{
			if ($k >= $startime)
			{
				$tryloginnum++;
			}
			else
			{
				unset($this->_adminlog[$k]);
				$unsetnum++;
			}
		}
		if ($tryloginnum >= $this->_wrontnum)
		{
			PWriteFile($this->_lockfile,'','wb');
			$this->loginexit($this->_denytime);
		}
		$unsetnum && writeSafeContent($this->_cachefile, '$_adminlog = '.pb_var_export($this->_adminlog).';', 'wb');
	}
	function writeAdminLog($logdata)
	{
		while (count($this->_adminlog) >= $this->_wrontnum)
		{
			array_pop($this->_adminlog);
		}
		$this->_adminlog[$logdata['time']] = array('name'=>$logdata['name'],'ip'=>$logdata['ip'],'time'=>$logdata['time'],'referer'=>$logdata['referer']);
		writeSafeContent($this->_cachefile, '$_adminlog = '.pb_var_export($this->_adminlog).';', 'wb');
	}
	function loginexit($lefttime)
	{
		$msg = getSingleLang('admin', 'admin_login_locked');
		eval("\$msg = \"".addcslashes($msg, '\\"')."\";");
		exit($msg);
	}
}

?>