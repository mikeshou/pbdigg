<?php
/**
 * @version $Id: Directory.class.php v2.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

class PDirectory
{
	/**
	 * 根目录
	 */
	var $_root = '';
	
	/**
	 * 当前目录
	 */
	var $_cdir = '';

	/**
	 * 上级目录
	 */
	var $_updir = '';
	
	/**
	 * ICON图标目录
	 */
	var $_icondir = '';
	
	/**
	 * 文件ICON图标
	 */
	var $_icon = array();
	
	/**
	 * ICON图标后缀名
	 */
	var $_iconext = 'gif';
	
	/**
	 * 目录信息
	 * 
	 * @array('name'=>'dirname', 'icon'=>'diricon', 'url'=>'dirurl')
	 */
	var $_dirs = array();
	
	/**
	 * 文件信息
	 * 
	 * @array('name'=>'filename', 'icon'=>'fileicon', 'size'=>'filesize', 'mtime'=>'filemtime', 'url'=>'dirurl')
	 */
	var $_files = array();
	
	/**
	 * 文件类型
	 */
	var $_ext = array();
	
	/**
	 * 构造函数
	 */
    function PDirectory()
    {
    }
    
	/**
     * 取得类的一个实例
     *
     * @return object
     */
    function & getInstance()
    {
        static $instance;
        if (empty($instance))
        {
            $instance = new PDirectory;
        }
        return $instance;
    }
    
    /**
     * 设置根目录
     * 
	 * @param String $var '/目录名称'
     */
    function setRoot($var)
    {
    	$this->_root = $var;
    }
    
    /**
     * 设置当前目录
	 * @param String $var '/目录名称/二级目录'
     */
    function setCDir($var)
    {
    	$this->_cdir = $var;
    }
    
    /**
     * 返回当前目录
     */
    function getCDir()
    {
    	return $this->_cdir;
    }
    
    /**
     * 返回目录列表
     */
    function getDirs()
    {
    	return $this->_dirs;
    }
    
    /**
     * 返回文件列表
     */
    function getFiles()
    {
    	return $this->_files;
    }
    
    /**
     * 上级目录链接
     */
    function upDir()
    {
    	//up dir
		if ($this->_cdir)
		{
			$this->_updir = substr($this->_cdir, 0, strrpos($this->_cdir, '/'));
		}
    }
    /**
     * 返回上级目录链接
     */
    function getUpDir()
    {
		return $this->_updir;
    }
    /**
     * 设置文件对应ICON图标
     * 
     * @param String $iconDir ICON图片目录，以"/"结尾
     * @param Array $icon 关联数组：array('html'=>array('html', 'htm', 'tpl'), 'php'=>array('php', 'php3'))
     * @param String $ext ICON图片后缀名，如：gif
     */
    function setIcon($iconDir, $icon, $ext)
    {
    	$this->_icondir = $iconDir;
    	$this->_icon = $icon;
    	$this->_iconext = $ext;
    }
    /**
     * 返回文件ICON
     */
    function getIcon($fileext)
    {
    	if ($this->_icon)
    	{
    		foreach ($this->_icon as $key => $value)
    		{
    			foreach ($value as $ext)
    			{
    				if ($fileext == $ext)
    				{
    					$icon = $key.'.'.$this->_iconext;
    					$iconfile = $this->_icondir.$icon;
    					if (file_exists($iconfile))
    					{
    						return $icon;
    					}
    					else
    					{
    						break 2;
    					}
    				}
    			}
    		}
    	}
    	return 'unknow.'.$this->_iconext;
    }
    /**
     * 设置合法后缀名
     * 
     * @param Array $var 后缀名数组：array('html', 'php', 'tpl')
     */
    function setExt($var)
    {
    	$this->_ext = $var;
    }
    /**
     * 遍历目录
     */
    function listDir()
    {
    	$openDir = str_replace(array('\\\\','\\'), array('\\','/'), $this->_root.$this->_cdir);
    	if (is_dir($openDir))
    	{
    		if ($rs = @opendir($openDir))
    		{
    			while (($file = readdir($rs)) !== FALSE)
    			{
    				if ($file != '.' && $file != '..')
    				{
    					$dfile = $openDir.'/'.$file;
    					if (is_dir($dfile))
    					{
    						//dir
    						$this->_dirs[] = array($file, $this->getIcon('dir'), '&directory='.$this->_cdir.'/'.$file);
    					}
    					else
    					{
    						//file
    						$ext = substr($file, strpos($file, '.') + 1);
    						if (!$this->_ext || in_array($ext, $this->_ext))
    						{
    							$this->_files[] = array($file, $this->getIcon($ext), getRealSize(filesize($dfile)), gdate(filemtime($dfile), 'y-n-j H:i'), '&directory='.$this->_cdir.'&filename='.urlencode($file));
    						}
    					}
    				}
    			}
    		}
    	}
    }
}
?>