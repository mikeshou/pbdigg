<?php
/**
 * @version $Id: cache.class.php v2.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

class Cache
{
	/**
	 * 数据库实例
	 */
	var $DB = null;
	/**
	 * 表前缀
	 */
	var $db_prefix = '';
	/**
	 * 缓存文件生存期
	 */
	var $lifeTime = 0;

	function __construct()
	{
		global $DB, $db_prefix, $pb_cachetime;
		$this->lifeTime = (int)$pb_cachetime;
		$this->DB = $DB;
		$this->db_prefix = $db_prefix;
	}

	function Cache()
	{
		$this->__construct();
	}

    function setLifeTime($lifeTime)
    {
    	$this->lifeTime = (int)$lifeTime;
    }

	function config()
	{
		$query = $this->DB->db_query("SELECT title, text FROM {$this->db_prefix}configs WHERE title LIKE 'pb_%'");
		$content = '';
		while ($rs = $this->DB->fetch_all($query))
		{
			$content .= '$'.$rs['title']." = '".addcslashes($rs['text'], '\'\\')."';\r\n";
		}
		//统计信息
		$query = $this->DB->db_query("SELECT newmember, membernum, catenum, artnum, comnum, buildtime FROM {$this->db_prefix}sitestat WHERE id = 1");
		$content .= "\r\n\$_sitestat = array(\r\n";
		while ($rs = $this->DB->fetch_all($query))
		{
			$content .= "\t'newmember' => '" . addcslashes(HConvert($rs['newmember']), '\'\\') . "',\r\n";
			$content .= "\t'membernum' => '" . $rs['membernum'] . "',\r\n";
			$content .= "\t'catenum' => '" . $rs['catenum'] . "',\r\n";
			$content .= "\t'artnum' => '" . $rs['artnum'] . "',\r\n";
			$content .= "\t'comnum' => '" . $rs['comnum'] . "',\r\n";
			$content .= "\t'buildtime' => '" . $rs['buildtime'] . "',\r\n";
		}
		$content .= ");\r\n";

		//首页公告
		$query = $this->DB->db_query("SELECT aid, cid, author, subject, url, content, postdate, enddate FROM {$this->db_prefix}announcements WHERE cid = '' ORDER BY displayorder");
		$_announcements = array();
		while ($rs = $this->DB->fetch_all($query))
		{
			$rs['postdate'] = gdate($rs['postdate'], 'Y-m-d H:i');
			$rs['link'] = $rs['url'] ? $rs['url'] : 'announcement.php?aid=' . $rs['aid'] . '#'.$rs['aid'];
			$rs['url'] = $rs['url'] ? ('<a href="'.$rs['url'].'" target="_blank" class="ancurl">' . $rs['url'] . '</a>') : '';
			$_announcements[] = $rs;
		}
		$this->writeCache('announcements', '$_announcements = '.pb_var_export($_announcements).';');

		$content .= '$_announcements = '.pb_var_export($_announcements).";\r\n";

		$this->DB->db_free($query);
		$this->writeCache('config', $content);
	}

	function reg()
	{
		$query = $this->DB->db_query("SELECT title, text FROM {$this->db_prefix}configs WHERE title LIKE 'reg_%'");
		$content = '';
		while ($rs = $this->DB->fetch_all($query))
		{
			$content .= '$'.$rs['title']." = '".addcslashes($rs['text'], '\'\\')."';\r\n";
		}
		$this->DB->db_free($query);
		$this->writeCache('reg', $content);
	}

	function uc($uc = '')
	{
		if (!$uc)
		{
			$query = $this->DB->db_query("SELECT title, text FROM {$this->db_prefix}configs WHERE title LIKE 'uc_%'");
			while ($rs = $this->DB->fetch_all($query))
			{
				$uc[$rs['title']] = $rs['text'];
			}
		}
		$content = '';
		foreach ($uc as $k => $v)
		{
			$content .= '$'.$k." = '".addcslashes($v, '\'\\')."';\r\n";
		}
		$query && $this->DB->db_free($query);
		$this->writeCache('uc', $content);
	}

	function categories()
	{
		$categories = array();
		$query = $this->DB->db_query("SELECT cid,cup,depth,withchild,icon,name,dir,style,status,anonymity,tnum,cnum,displayorder,ttype,cover,template,listtype,listnum FROM {$this->db_prefix}categories ORDER BY displayorder");
		
		while ($rs = $this->DB->fetch_all($query))
		{
			$rs['ttype'] && $rs['ttype'] = explode("\t", $rs['ttype']);
			$categories[$rs['cid']] = $rs;
		}
		$this->DB->db_free($query);
		foreach ($categories as $k => $v)
		{
			$parCate = $subCate = array();
			parCate($categories, $parCate, $k);
			subCate($categories, $subCate, $k);
			$categories[$k]['parcate'] = array_reverse($parCate);
			$categories[$k]['subcate'] = $subCate;
		}

		$this->writeCache('categories', "\$_categories = ".pb_var_export($categories).";");
	}

	function singlecate($cid = '')
	{
		$sql = $cid ? ' WHERE cid IN ('.$cid.')' : '';
		$query = $this->DB->db_query("SELECT cid, keywords, description FROM {$this->db_prefix}categories".$sql);
		while ($rs = $this->DB->fetch_all($query))
		{
			$content = "\$_singlecate = array(";
			$content .= "'keywords' => '" . addcslashes($rs['keywords'], '\'\\') . "',";
			$content .= "'description' => '" . addcslashes($rs['description'], '\'\\') . "',";
			$content .= ");\r\n";

			//分类公告
			$query2 = $this->DB->db_query("SELECT aid, cid, author, subject, url, content, postdate, enddate FROM {$this->db_prefix}announcements WHERE cid = '".$rs['cid']."' ORDER BY displayorder");
			$_announcements = array();
			while ($rs2 = $this->DB->fetch_all($query2))
			{
				$rs2['postdate'] = gdate($rs2['postdate'], 'Y-m-d H:i');
				$rs2['link'] = $rs2['url'] ? $rs2['url'] : 'announcement.php?aid=' . $rs2['aid'] . '#'.$rs2['aid'];
				$rs2['url'] = '<a href="'.$rs2['url'].'" target="_blank">' . $rs2['url'] . '</a>';
				$_announcements[] = $rs2;
			}
			$content .= '$_announcements_'.$rs['cid'].' = '.pb_var_export($_announcements).";\r\n";
			$this->writeCache('singlecate_'.$rs['cid'], $content);
			$this->DB->db_free($query2);
		}
		$this->DB->db_free($query);
	}

	function grouplevel()
	{
		$query = $this->DB->db_query("SELECT groupid, gtype, grouptitle FROM {$this->db_prefix}usergroups ORDER BY groupid");
		$content = "\$_grouplevel = array(\r\n";
		while ($rs = $this->DB->fetch_all($query))
		{
			$content .= "\t'" . $rs['groupid'] . "' => array(\r\n";
			$content .= "\t\t'groupid' => '" . $rs['groupid'] . "',\r\n";
			$content .= "\t\t'gtype' => '" . $rs['gtype'] . "',\r\n";
			$content .= "\t\t'grouptitle' => '" . addcslashes($rs['grouptitle'], '\\\'') . "'\r\n";
			$content .= "\t),\r\n";
		}
		$content .= ");";
		$this->writeCache('grouplevel', $content);
	}
	/**
	 * 用户组缓存
	 */
	function userGroupCache()
	{
		$query = $this->DB->db_query("SELECT * FROM {$this->db_prefix}usergroups");
		$content = '';
		while ($rs = $this->DB->fetch_all($query))
		{
			foreach ($rs as $k => $v)
			{
				$content .= '$'.$k." = '".$v."';\r\n";
			}
			$this->writeCache('usergroup_'.$rs['groupid'], $content);
			$content = '';
		}
	}
	/**
	 * 管理组缓存
	 */
	function adminGroupCache()
	{
		$query = $this->DB->db_query("SELECT * FROM {$this->db_prefix}admingroups");
		$content = '';
		while ($rs = $this->DB->fetch_all($query))
		{
			foreach ($rs as $k => $v)
			{
				$k !='adminright' && $content .= '$'.$k." = '".(int)$v."';\r\n";
			}
			$this->writeCache('admingroup_'.$rs['adminid'], $content);
			$content = '';
		}
	}
	/**
	 * 热门标签
	 */
	function hottags($num = 10)
	{
		global $DB, $db_prefix, $pb_tagcolor;
		$num = (int)$num;
		$query = $this->DB->db_query("SELECT tagid, tagname, usenum FROM {$this->db_prefix}tags WHERE ifopen = 1 ORDER BY usenum DESC LIMIT $num");
		$content = "return \$_hottags = array(\r\n";
		while ($result = $this->DB->fetch_all($query))
		{
			$content .= "\t'" . $result['tagid'] . "' => array(\r\n";
			$content .= "\t\t'tagid' => '" . $result['tagid'] . "',\r\n";
			$content .= "\t\t'tagname' => '" . addcslashes($result['tagname'], '\\\'') . "',\r\n";
			$content .= "\t\t'encodetagname' => '" . addcslashes(rawurlencode($result['tagname']), '\\\'') . "',\r\n";
			$content .= "\t\t'usenum' => '" . $result['usenum'] . "',\r\n";
			if ($pb_tagcolor)
			{
				$content .= "\t\t'color' => '" . addcslashes(getTagStyle(), '\\\'') . "',\r\n";
			}
			$content .= "\t),\r\n";
		}
		$content .= ");";
		$this->DB->db_free($query);
		$this->writeCache('hottags', $content);
	}
	/**
	 * 系统标签
	 */
	function systags($num = 10)
	{
		global $DB, $db_prefix, $pb_tagcolor;
		$num = (int)$num;
		$query = $this->DB->db_query("SELECT tagid, tagname, usenum, tagpic FROM {$this->db_prefix}tags FORCE INDEX ( usenum ) WHERE ifsys = 1 AND ifopen = 1 ORDER BY usenum DESC LIMIT $num");
		$content = "\$_systags = array(\r\n";
		while ($result = $this->DB->fetch_all($query))
		{
			$content .= "\t'" . $result['tagid'] . "' => array(\r\n";
			$content .= "\t\t'tagid' => '" . $result['tagid'] . "',\r\n";
			$content .= "\t\t'tagname' => '" . addcslashes($result['tagname'], '\\\'') . "',\r\n";
			$content .= "\t\t'encodetagname' => '" . addcslashes(rawurlencode($result['tagname']), '\\\'') . "',\r\n";
			$content .= "\t\t'tagpic' => '" . addcslashes($result['tagpic'], '\\\'') . "',\r\n";
			$content .= "\t\t'usenum' => '" . $result['usenum'] . "',\r\n";
			if ($pb_tagcolor)
			{
				$content .= "\t\t'color' => '" . addcslashes(getTagStyle(), '\\\'') . "',\r\n";
			}
			$content .= "\t),\r\n";
		}
		$content .= ");";
		$this->DB->db_free($query);
		$this->writeCache('systags', $content);
	}
	function tags()
	{
		$this->hottags();
		$this->systags();
	}
	/**
	 * 插件缓存
	 */
	function plugins()
	{
		$query = $this->DB->db_query("SELECT pid, pmark, actionhook, filterhook, withstage FROM {$this->db_prefix}plugins WHERE status = 1");
		$_actionhook = $_filterhook = $_plugins = array();
		while ($rs = $this->DB->fetch_all($query))
		{
			if ($rs['actionhook'])
			{
				$hook = explode('|', $rs['actionhook']);
				foreach ($hook as $h)
				{
					$h = explode(',', $h);
					if (count($h) <= 1) continue;
					$t = $h[0];
					$f = array_slice($h, 1);
					foreach ($f as $v)
					{
						if (!preg_match('~^[_a-z]{1}[_a-z0-9]*?=\d+$~i', $v)) continue;
						list($fn,$pa) = explode('=', $v);
						$_actionhook[$t][$fn] = (int)$pa;
					}
				}
			}

			if ($rs['filterhook'])
			{
				$hook = explode('|', $rs['filterhook']);
				foreach ($hook as $h)
				{
					$h = explode(',', $h);
					if (count($h) <= 1) continue;
					$t = $h[0];
					$f = array_slice($h, 1);
					foreach ($f as $v)
					{
						if (!preg_match('~^[_a-z]{1}[_a-z0-9]*?=\d+$~i', $v)) continue;
						list($fn,$pa) = explode('=', $v);
						$_filterhook[$t][$fn] = (int)$pa;
					}
				}
			}
			$rs['withstage'] && $_plugins[$rs['pid']] = $rs['pmark'];
		}
		$this->DB->db_free($query);
		$this->writeCache('plugins', "\$_actionhook = ".pb_var_export($_actionhook).";\r\n\$_filterhook = ".pb_var_export($_filterhook).";\r\n\$_plugins = ".pb_var_export($_plugins).";");
	}
	/**
	 * 友情链接
	 */
	function flink()
	{
		$query = $this->DB->db_query("SELECT * FROM {$this->db_prefix}links WHERE ifshow = 1 ORDER BY displayorder");
		$ck = FALSE;
		$pright = chr(80).chr(66).chr(68).chr(105).chr(103).chr(103);
		$purl = chr(104).chr(116).chr(116).chr(112).chr(58).chr(47).chr(47).chr(119).chr(119).chr(119).chr(46).chr(112).chr(98).chr(100).chr(105).chr(103).chr(103).chr(46).chr(110).chr(101).chr(116);

		$txt = "\$_txt_link = array(\r\n";
		$pic = "\$_pic_link = array(\r\n";
		while ($rs = $this->DB->fetch_all($query))
		{
			if (!$ck && (strpos(strtolower($rs['siteurl']), $purl) !== FALSE))
			{
				$ck = TRUE;
			}
			$content = "\t'" . $rs['lid'] . "' => array(\r\n";
			$content .= "\t\t'sitename' => '" . addcslashes($rs['sitename'], '\'\\') . "',\r\n";
			$content .= "\t\t'siteurl' => '" . addcslashes($rs['siteurl'], '\'\\') . "',\r\n";
			$content .= "\t\t'description' => '" . addcslashes($rs['description'], '\'\\') . "',\r\n";
			$content .= "\t\t'logo' => '" . addcslashes($rs['logo'], '\'\\') . "'),\r\n";
			if ($rs['logo'])
			{
				$pic .= $content;
			}
			else
			{
				$txt .= $content;
			}
		}
		if (!$ck)
		{
			$content = "\t'9999' => array(\r\n";
			$content .= "\t\t'sitename' => '$pright',\r\n";
			$content .= "\t\t'siteurl' => '$purl',\r\n";
			$content .= "\t\t'description' => '&#20013;&#25991;&#73;&#84;&#36164;&#35759;',\r\n";
			$content .= "\t\t'logo' => 'logo.gif'),\r\n";
			$pic .= $content;
		}
		$txt .= ");";
		$pic .= ");";
		$this->writeCache('links', $txt."\r\n\r\n".$pic);
	}
 	/** 
 	 * 缓存模板
	 */
	function tplvar()
	{
		$query = $this->DB->db_query("SELECT tplmark,cachetime,`fields`,specialfields,replacefields,cotentlimit,titlelimit,timeformat,tranttype,tplcontent,querysql FROM {$this->db_prefix}templates");
		$content = "\$_tplvartable = array(\r\n";
		while ($rs = $this->DB->fetch_all($query))
		{
			$content .= "'" . $rs['tplmark'] . "' => array(";
			$content .= "'cachetime' => '" . $rs['cachetime'] . "',";
			$content .= "'fields' => '" . addcslashes($rs['fields'], '\\\'') . "',";
			$content .= "'specialfields' => '" . addcslashes($rs['specialfields'], '\\\'') . "',";
			$content .= "'replacefields' => '" . addcslashes($rs['replacefields'], '\\\'') . "',";
			$content .= "'cotentlimit' => '" . $rs['cotentlimit'] . "',";
			$content .= "'titlelimit' => '" . $rs['titlelimit'] . "',";
			$content .= "'timeformat' => '" . $rs['timeformat'] . "',";
			$content .= "'tranttype' => '" . $rs['tranttype'] . "',";
			$content .= "'tplcontent' => '" . addcslashes($rs['tplcontent'], '\\\'') . "',";
			$content .= "'querysql' => '" . addcslashes($rs['querysql'], '\\\'') . "',";
			$content .= "),";
		}
		$content .= ");";
		$this->writeCache('tplvar', $content);
	}
 	/** 
 	 * 特殊模板缓存
	 */
	function specialtpl()
	{
		$query = $this->DB->db_query("SELECT * FROM {$this->db_prefix}specialtpl");
		$content = '';
		while ($rs = $this->DB->fetch_all($query))
		{
			$content .= "\$_".$rs['tplfunc']."tpl = array(\r\n";
			$content .= "'tplid' => '" . $rs['tplid'] . "',";
			$content .= "'tplname' => '" . addcslashes($rs['tplname'], '\\\'') . "',";
			$content .= "'tplfunc' => '" . $rs['tplfunc'] . "',";
			$content .= "'template' => '" . addcslashes($rs['template'], '\\\'') . "',";
			$content .= ");\r\n";
		}
		$this->writeCache('specialtpl', $content);
	}
	/**
	 * 网站公告
	 * 
	 * @version 1.0
	 */
	function announce()
	{
		$this->config();
		$this->singlecate();
	}
	/**
	 * 核心缓存
	 */
	function coreCache()
	{
		$this->config();
		$this->reg();
		$this->categories();
		$this->singlecate();
		$this->grouplevel();
		$this->userGroupCache();
		$this->adminGroupCache();
		$this->flink();
		$this->announce();
		$this->tplvar();
		$this->specialtpl();
		$this->tags();
	}
	/**
	 * 加载调用内容
	 * 
	 * @param String $cacheName 缓存文件名
	 * @param Int $cacheName 缓存类型
	 * @param Boolean $checkTime 是否检查缓存时间
	 * @param Int $force 是否必须加载
	 * @version 1.0
	 */
	function _loadTran($cacheName, $type, $checkTime = TRUE, $force = 0)
	{
		global $timestamp, $func_message;
		$cacheFile = PBDIGG_ROOT.'data/cache/cache_'.$cacheName.'.php';
		if (file_exists($cacheFile))
		{
			clearstatcache();
			if ($checkTime && ($timestamp - filemtime($cacheFile)) > $this->lifeTime)
			{
				$this->mktran($cacheName, $type);
			}
			$this->cacheContent = include_once($cacheFile);
		}
		elseif ($force)
		{
			$this->mktran($cacheName, $type);
			$this->cacheContent = include_once($cacheFile);
		}
		else
		{
			$this->cacheContent = sprintf($func_message['tran_cache_needed'], $cacheName);
		}
	}

	/**
	 * 加载缓存
	 * 
	 * @param String $cacheName 缓存文件名
	 * @param Boolean $checkTime 是否检查缓存时间
	 * @param int $force 是否必须加载
	 * @version 1.0
	 */
	function loadCache($cacheName, $checkTime = TRUE, $force = 0)
	{
		$cacheFile = PBDIGG_ROOT.'data/cache/cache_'.$cacheName.'.php';
		if (file_exists($cacheFile))
		{
			clearstatcache();
			if ($checkTime && (time() - filemtime($cacheFile)) > $this->lifeTime)
			{
				$this->$cacheName();
			}
			$this->cacheContent = include_once($cacheFile);
		}
		elseif ($force)
		{
			$this->$cacheName();
			$this->cacheContent = include_once($cacheFile);
		}
		else
		{
			$this->cacheContent = '';
		}
	}
	/**
	 * 缓存文件属性
	 * 
	 * @return boolean 是否可写
	 */
	function cacheMod($filename, $mod = 0666)
	{
		if (strpos($filename, '..') !== FALSE) return FALSE;
		$file = PBDIGG_ROOT.'data/cache/cache_'.$filename.'.php';
		return (is_writable($file) || chmod($file, $mod)) ? 1 : 0;
	}
	/**
	 * 更新缓存
	 * 
	 * @version 1.0
	 */
	function refreshCache()
	{
		if ($this->cacheList)
		{
			$funcName = array_filter(explode(',', $this->cacheList));
			foreach ($funcName as $value)
			{
				$this->$value();
			}
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * 缓存写入函数
	 * 
	 * @version 2.0
	 */
	function writeCache($fileName, $content)
	{
		$cacheFile = PBDIGG_ROOT . 'data/cache/cache_' . $fileName . '.php';
		$cacheContent = "<?php\r\n\r\n/** PBDigg Cache File, DO NOT Modify! **/\r\n\r\n";
		$cacheContent .= $content;
		$cacheContent .= "\r\n?>";
		return PWriteFile($cacheFile, $cacheContent, 'wb');
	}
}
?>
