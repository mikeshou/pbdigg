<?php
/**
 * @version $Id: module.class.php v1.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

class module
{
	/**
	 * 数据库实例
	 */
	var $DB;
	/**
	 * 数据库表前缀
	 */
	var $db_prefix;
	
	/**
	 * 模型ID
	 */
	var $_moduleId = array();
	
	/**
	 * 模型缓存文件
	 */
	var $_moduleCacheFile = '';
	
	/**
	 * 模型参数设置内容
	 */
	var $_moduleSettings = '';

	/**
	 * 模型配置信息
	 */
	var $_moduleConfigs = array();

	/**
	 * 模型实例
	 */
	var $_moduleObject = array();
	
	function module()
    {
    	$this->__construct();
    }
    
    /**
     * 构造函数
     */
    function __construct()
    {
    	global $DB, $db_prefix;
    	$_module = array();
    	$this->DB = $DB;
    	$this->db_prefix = $db_prefix;
    	$this->_moduleCacheFile = PBDIGG_CROOT.'cache_module.php';
    	if (file_exists($this->_moduleCacheFile))
    	{
    		require_once $this->_moduleCacheFile;
    		$this->_moduleSettings = $_module;
    	}
    	else
    	{
    		$this->_moduleSettings = $this->moduleCache();
    	}
    }
	/**
	 * 获取模型菜单
	 */
    function getModuleMenu()
    {
    	$moduleMenu = array();
    	foreach ($this->_moduleSettings as $k => $v)
		{
			$moduleMenu[$v['mid']] = array($v['identifier'], $v['name'], $v[$v['identifier'].'_status']);
		}
		return $moduleMenu;
    }
	/**
	 * 获取模型配置信息
	 * 
	 * @param $moduleID Array 模型ID数组，array(1,2,3)
	 */
    function getModuleConfig($moduleID = array())
    {
    	if (empty($moduleID)) $moduleID = $this->getModuleId();
    	foreach ($moduleID as $v)
		{
			if (!isset($this->_moduleConfigs[$v]))
			{
				require_once PBDIGG_ROOT.'module/'.$this->_moduleSettings[$v]['identifier'].'/'.$this->_moduleSettings[$v]['identifier'].'.config.php';
				$this->_moduleConfigs[$v] = array('fields'=>$moduleTpl['fields'],'specialFields'=>$moduleTpl['specialFields'],'tablesRelation'=>$moduleTpl['tablesRelation']);
			}
		}
		return $this->_moduleConfigs;
    }
    
	/**
	 * 获取模型ID数组
	 */
    function getModuleId()
    {
    	if (empty($this->_moduleId))
    	{
    		foreach ($this->_moduleSettings as $k => $v)
			{
				$k && is_numeric($k) && $this->_moduleId[] = (int)$k;
			}
    	}
    	return $this->_moduleId;
    }
	/**
	 * 获取模型实例
	 * 
	 * @param int $moduleID 模型ID
	 * @param boolean $reload 是否重新生成实例
	 */

    function getModuleObject($moduleID, $reload = FALSE)
    {
    	if (!isset($this->_moduleSettings[$moduleID])) return NULL;
    	if (!isset($this->_moduleObject[$moduleID]) || $reload)
    	{
    		require PBDIGG_ROOT.'module/'.$this->_moduleSettings[$moduleID]['identifier'].'/'.$this->_moduleSettings[$moduleID]['identifier'].'.class.php';
    		$this->_moduleObject[$moduleID] = new $this->_moduleSettings[$moduleID]['identifier']();
    	}
    	return $this->_moduleObject[$moduleID];
    }
	/**
	 * 缓存模型配置信息
	 */
    function moduleCache()
    {
    	global $db_prefix;
    	$moduledata = array();
    	$query = $this->DB->db_query("SELECT mid, identifier, name FROM {$this->db_prefix}module");
		while ($rs = $this->DB->fetch_all($query))
		{
			foreach ($rs as $k => $v)
			{
				$moduledata[$rs['mid']][$k] = $v;
			}
		}
		foreach ($moduledata as $k => $v)
		{
			$query = $this->DB->db_query("SELECT * FROM {$this->db_prefix}modconfig WHERE mvar LIKE '".$v['identifier']."_%'");
			while ($rs = $this->DB->fetch_all($query))
			{
				$moduledata[$k][$rs['mvar']] = $rs['mtext'];
			}
		}
		$this->buildCacheFile('$_module = '.pb_var_export($moduledata).';');
		return $moduledata;
    }
	/**
	 * 添加模型
	 */
    function addModule($moduleDir, $step = '')
    {
    	global $db_charset, $timestamp;

    	if (!$moduleDir || preg_replace('~[a-z0-9_]~i', '', $moduleDir)) showMsg('module_illegal_path');
		if ($this->DB->fetch_one("SELECT * FROM {$this->db_prefix}module WHERE identifier = '".addslashes($moduleDir)."'")) showMsg('module_exist');

		!is_dir(PBDIGG_ROOT.'module/'.$moduleDir) &&  showMsg('module_path_noexist');
		!file_exists(PBDIGG_ROOT.'module/'.$moduleDir.'/module.xml') && showMsg('module_installfile_noexist');

		require_once PBDIGG_ROOT.'include/xml.func.php';
		$moduleInfo = xml_to_array(file_get_contents(PBDIGG_ROOT.'module/'.$moduleDir.'/module.xml'));
		$moduleInfo['name'] = convert_encoding('utf-8', $db_charset,$moduleInfo['name']);
		$moduleInfo['author'] = htmlspecialchars(convert_encoding('utf-8', $db_charset,$moduleInfo['author']));
		$moduleInfo['description'] = convert_encoding('utf-8', $db_charset,$moduleInfo['description']);
		$moduleInfo['copyright'] = htmlspecialchars(convert_encoding('utf-8', $db_charset,$moduleInfo['copyright']));
		htmlspecialchars($moduleInfo['name']) != $moduleInfo['name'] && showMsg('module_illegal_name');
		$moduleDir != $moduleInfo['identifier'] && showMsg('module_illegal_identifier');
		!preg_match('~^[a-z0-9\.\s]+$~i', $moduleInfo['version']) && showMsg('module_illegal_version');
		!pStrToTime($moduleInfo['publish']) && $moduleInfo['publish'] = gdate($timestamp);

		if ($step == 'install')
		{
			!isset($_POST['agree']) && showMsg('module_agree_copyright');
			if (file_exists(PBDIGG_ROOT.'module/'.$moduleDir.'/install/install.sql') && ($sqldata = file_get_contents(PBDIGG_ROOT.'module/'.$moduleDir.'/install/install.sql')))
			{
				installSQL($sqldata);
			}
			addS($moduleInfo);
			$this->DB->db_exec("INSERT INTO {$this->db_prefix}module (identifier,name,author,publish,version,copyright,description) VALUES ('".$moduleInfo['identifier']."','".$moduleInfo['name']."','".$moduleInfo['author']."','".pStrToTime($moduleInfo['publish'])."','".$moduleInfo['version']."','".$moduleInfo['copyright']."','".$moduleInfo['description']."')");
			//增加一条模块状态配置
			$this->DB->db_exec("REPLACE INTO {$this->db_prefix}modconfig (mvar, mtext) VALUES ('".$moduleInfo['identifier']."_status', '1')");
			$this->moduleCache();
			redirect('module_add_success', 'admincp.php?action=module&job=edit');
		}
		return $moduleInfo;
    }
	/**
	 * 删除模型
	 */
	function delModule($mid)
	{
		$moduledata = $this->DB->fetch_one("SELECT * FROM {$this->db_prefix}module WHERE mid = '$mid'");
		!$moduledata && showMsg('module_data_noexist');
		if (file_exists(PBDIGG_ROOT.'module/'.$moduledata['identifier'].'/install/uninstall.sql') && ($sqldata = file_get_contents(PBDIGG_ROOT.'module/'.$moduledata['identifier'].'/install/uninstall.sql')))
		{
			installSQL($sqldata);
		}
		$this->DB->db_exec("DELETE FROM {$this->db_prefix}module WHERE mid = '$mid' LIMIT 1");
		$this->DB->db_exec("DELETE FROM {$this->db_prefix}modconfig WHERE mvar LIKE '".addslashes($moduledata['identifier'])."_%'");
		$this->moduleCache();
	}
	/**
	 * 修改模型状态
	 */
    function changeModuleStatus($mid)
    {
    	if (!array_key_exists($mid, $this->_moduleSettings)) return FALSE;
    	$identifier = $this->_moduleSettings[$mid]['identifier'];
    	$this->DB->db_exec("UPDATE {$this->db_prefix}modconfig SET mtext = '".(intval($this->_moduleSettings[$mid][$identifier.'_status']) ^ 1)."' WHERE mvar = '".addslashes($identifier)."_status'");
    	$this->moduleCache();
    }
	/**
	 * 返回模型列表
	 */
    function listModule()
    {
    	global $cp_message;
    	$moduledata = array();
    	$query = $this->DB->db_query("SELECT mid,identifier,publish,name,version FROM {$this->db_prefix}module");

    	while ($rs = $this->DB->fetch_all($query))
    	{
    		$rs['publish'] = gdate($rs['publish'], 'Y-m-d');
    		$rs['status'] = $this->_moduleSettings[$rs['mid']][$rs['identifier'].'_status'] ? $cp_message['close'] : '<span class="r">'.$cp_message['open'].'</span>';
    		$moduledata[] = $rs;
    	}
    	return $moduledata;
    }
	/**
	 * 获取单个模型配置参数
	 */
    function getSingleModuleData($mid = 0)
    {
    	return isset($this->_moduleSettings[$mid]) ? $this->_moduleSettings[$mid] : $this->_moduleSettings;
    }
    
    function checkModuleID($mid)
    {
    	return isset($this->_moduleSettings[$mid]) ? true : false;
    }
    
	/**
	 * 模型缓存文件写入
	 */
	function buildCacheFile($content)
	{
		$content = "<?php\r\n!defined('IN_PBDIGG') && exit('Access Denied!');\r\n".$content."\r\n?>";
		PWriteFile($this->_moduleCacheFile, $content, 'wb');
	}

	/**
	 * 根据分类删除内容
	 * 
	 * @param array $cid 分类cid数组
	 */
	function delArticleByCid($cid)
	{
		global $_categories;
		if (empty($cid)) return;
		!is_array($cid) && $cid = (array)$cid;
		$modules = array();
		$cid = array_unique($cid);
		foreach ($cid as $v)
		{
			if (isset($_categories[$v]) && $_categories[$v]['ttype'])
			{
				foreach ($_categories[$v]['ttype'] as $vv)
				{
					$modules[$vv][] = $v;
				}
			}
		}
		foreach ($modules as $m => $a)
		{
			if ($moduleObject = $this->getModuleObject($m))
			{
				$moduleObject->delCateArticle($a);
			}
		}
	}

	/**
	 * 根据tid删除内容
	 * 
	 * @param array $var 模型 & TID 关联数组 array('moduleid1'=>array('tids'),'moduleid2'=>array('tids'))
	 */
	function delArticleByTid($var)
	{
		global $_categories;
		if (empty($var)) return;
		!is_array($var) && $var = (array)$var;

		foreach ($var as $m => $a)
		{
			if ($moduleObject = $this->getModuleObject($m))
			{
				$moduleObject->del($a);
			}
		}
	}
}
?>