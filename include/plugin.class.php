<?php
/**
 * @version $Id: plugin.class.php v1.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

class plugin
{
	/**
	 * 数据库实例
	 */
	var $DB;
	/**
	 * 数据库表前缀
	 */
	var $db_prefix;
	
	function plugin()
    {
    	$this->_construct();
    }
    function __construct()
    {
    	global $DB, $db_prefix;
    	$this->DB = $DB;
    	$this->db_prefix = $db_prefix;
    }

    function addPlugin($pluginDir, $step = '')
    {
    	global $db_charset, $timestamp, $Cache;

    	if (!$pluginDir || preg_replace('~[a-z0-9_]~i', '', $pluginDir)) showMsg('plugin_illegal_path');
		if ($this->DB->fetch_one("SELECT * FROM {$this->db_prefix}plugins WHERE pmark = '".addslashes($pluginDir)."'")) showMsg('plugin_exist');

		!is_dir(PBDIGG_ROOT.'plugins/'.$pluginDir) &&  showMsg('plugin_path_noexist');
		!file_exists(PBDIGG_ROOT.'plugins/'.$pluginDir.'/plugin.xml') && showMsg('plugin_installfile_noexist');
		
		require_once PBDIGG_ROOT.'include/xml.func.php';
		$pluginInfo = xml_to_array(file_get_contents(PBDIGG_ROOT.'plugins/'.$pluginDir.'/plugin.xml'));

		$pluginInfo['pname'] = convert_encoding('utf-8', $db_charset,$pluginInfo['pname']);
		htmlspecialchars($pluginInfo['pname']) != $pluginInfo['pname'] && showMsg('plugin_illegal_name');
		$pluginDir != $pluginInfo['pmark'] && showMsg('plugin_illegal_identifier');
		!preg_match('~^[a-z0-9\.\s]+$~i', $pluginInfo['version']) && showMsg('plugin_illegal_version');
		$pluginInfo['description'] = convert_encoding('utf-8', $db_charset,$pluginInfo['description']);
//		preg_replace('~[a-z0-9_|]~i', '', $pluginInfo['dbtables']) && showMsg('plugin_illegal_tables');
		$pluginInfo['withstage'] =  $pluginInfo['withstage'] ? 1 : 0;
		(preg_replace('~[a-z0-9_|=,]~i', '', $pluginInfo['filterhook']) || preg_replace('~[a-z0-9_|=,]~i', '', $pluginInfo['actionhook'])) && showMsg('plugin_illegal_hook');
		$pluginInfo['copyright'] = htmlspecialchars(convert_encoding('utf-8', $db_charset,$pluginInfo['copyright']));

		if ($step == 'install')
		{
			!isset($_POST['agree']) && showMsg('plugin_agree_copyright');
			if (file_exists(PBDIGG_ROOT.'plugins/'.$pluginDir.'/install/install.sql') && ($sqldata = file_get_contents(PBDIGG_ROOT.'plugins/'.$pluginDir.'/install/install.sql')))
			{
				installSQL($sqldata);
			}
			addS($pluginInfo);
			$this->DB->db_exec("INSERT INTO {$this->db_prefix}plugins (pid,status,pname,pmark,version,description,actionhook,filterhook,withstage,copyright) VALUES (NULL,1,'".$pluginInfo['pname']."','".$pluginInfo['pmark']."','".$pluginInfo['version']."','".$pluginInfo['description']."','".$pluginInfo['actionhook']."','".$pluginInfo['filterhook']."','".$pluginInfo['withstage']."','".$pluginInfo['copyright']."')");
			$Cache->plugins();
			redirect('plugin_add_success', 'admincp.php?action=plugin&job=edit');
		}
		return $pluginInfo;
    }

	function delPlugin($pid)
	{
		global $Cache;
		$plugins = $this->DB->fetch_one("SELECT * FROM {$this->db_prefix}plugins WHERE pid = '$pid'");
		!$plugins && showMsg('plugin_data_noexist');
		if (file_exists(PBDIGG_ROOT.'plugins/'.$plugins['pmark'].'/install/uninstall.sql') && ($sqldata = file_get_contents(PBDIGG_ROOT.'plugins/'.$plugins['pmark'].'/install/uninstall.sql')))
		{
			installSQL($sqldata);
		}
//		if ($plugins['dbtables'])
//		{
//			$tables = explode('|', $plugins['dbtables']);
//			foreach ($tables as $table)
//			{
//				if ($table && preg_match('~^pb_plugin_[a-z\d]+$~i', $table))
//				{
//					$this->db_prefix != 'pb_' && $table = preg_replace('~^pb_~i', $this->db_prefix, $table);
//					$this->DB->db_exec("DROP TABLE IF EXISTS `$table`", FALSE);
//				}
//			}
//		}
		$this->DB->db_exec("DELETE FROM {$this->db_prefix}plugins WHERE pid = '$pid' LIMIT 1");
		$Cache->plugins();
	}

    function changePluginStatus($pid)
    {
    	global $Cache;
    	$this->DB->db_exec("UPDATE {$this->db_prefix}plugins SET status = status ^ 1 WHERE pid = ".$pid);
    	$Cache->plugins();
    }

    function listPlugin()
    {
    	global $cp_message;
    	$plugindata = array();
    	$query = $this->DB->db_query("SELECT pid, status, pname, pmark, version, description FROM {$this->db_prefix}plugins");

    	while ($rs = $this->DB->fetch_all($query))
    	{
    		$rs['status'] = $rs['status'] ? $cp_message['close'] : '<span class="r">'.$cp_message['open'].'</span>';
    		$plugindata[] = $rs;
    	}
    	return $plugindata;
    }
}
?>