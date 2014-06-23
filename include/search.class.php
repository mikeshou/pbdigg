<?php
/**
 * @version $Id: search.class.php v1.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

class search
{
	/**
	 * 搜索类型
	 */
	var $_searchtype;
	
	/**
	 * 搜索条件语句
	 */
	var $_searchsql = '';
	
	/**
	 * 搜索基本表
	 */
	var $_basetable;
	
	/**
	 * 返回限定
	 */
	var $_limit = '';
	
	/**
	 * 搜索hash
	 */
	var $_cacheHash = '';
	/**
	 * 记录总数
	 */
	var $_resultNum;
	
	/**
	 * 分页参数
	 */
	var $_mult = '';
	
	/**
	 * 搜索匹配正则
	 */
	var $_pattern = array('%','_','*');
	var $_replace = array('\%','\_','%');
	
	/**
	 * 数据库实例
	 */
	var $DB = null;
	
	var $db_prefix = '';
	
	/**
	 * 是否缓存搜索
	 */
	var $_ifcache;
	
	/**
	 * 缓存周期
	 */
	var $_cacheTime = 600;

    function search($type, $cache = false)
    {
    	$this->__construct($type, $cache);
    }

    function __construct($type, $cache = false)
    {
    	if (in_array($type, array('article', 'comment', 'attachment', 'author')))
    	{
    		global $page, $pagesize, $DB, $db_prefix;
    		$this->DB = $DB;
    		$this->db_prefix = $db_prefix;
    		$this->_searchtype = $type;
    		$this->_ifcache = $cache ? true : false;
    		$this->_limit = sqlLimit($page, $pagesize);
			switch ($this->_searchtype)
    		{
    			case 'article':
    				$this->_basetable = 't';
    				break;
    			case 'comment':
    				$this->_basetable = 'c';
    				break;
    			case 'attachment':
    				$this->_basetable = 'a';
    				break;
    			case 'author':
    				$this->_basetable = 'm';
    				break;
    		}
    	}
    }

	function getMult()
	{
		return $this->_mult;
	}

	function getResultNum()
	{
		return intval($this->_resultNum);
	}

    function exportResults($condition)
    {
    	global $searchhash;
    	if (!is_array($condition)) return;
		$cached = false;
    	if ($this->_ifcache && isset($searchhash) && preg_match('~^[a-z0-9]{32}$~', $searchhash))
    	{
    		$cached = $this->getCache($searchhash);
    	}
    	if (!$cached)
    	{
    		foreach ($condition as $k => $v)
	    	{
	    		$this->$k($v);
	    	}
	    	$this->_cacheHash = md5(md5($this->_searchsql).$this->_searchtype);
	    	$this->_ifcache && $cached = $this->getCache($this->_cacheHash);
    	}
    	$this->_searchsql && $this->_searchsql = ' WHERE '.substr($this->_searchsql, 4);

    	return $this->{$this->_searchtype}($cached);
    }
    
    function getCache($searchhash)
    {
    	global $timestamp;
    	$cacheData = $this->DB->fetch_one("SELECT num, ids, exptime FROM {$this->db_prefix}scaches WHERE hash = '$searchhash' AND exptime > '$timestamp'");

		if ($cacheData && $cacheData['ids'])
		{
			$this->_resultNum = (int)$cacheData['num'];
			$newids = '';
			$ids = explode(',', $cacheData['ids']);
			foreach ($ids as $v)
			{
				$newids .= (int)$v.',';
			}
			$newids && $newids = substr($newids, 0, -1);

			switch ($this->_searchtype)
    		{
    			case 'article':
    				$this->_searchsql = " AND t.tid IN ($newids)";
    				break;
    			case 'comment':
    				$this->_searchsql = " AND c.rid IN ($newids)";
    				break;
    			case 'attachment':
    				$this->_searchsql = " AND a.aid IN ($newids)";
    				break;
    			case 'author':
    				$this->_searchsql = " AND m.uid IN ($newids)";
    				break;
    		}

    		$newids != $cacheData['ids'] && $this->DB->db_exec("UPDATE {$this->db_prefix}scaches SET ids = '".addslashes($newids)."' WHERE hash = '$searchhash' AND exptime = '".$cacheData['exptime']."'");
    		$this->_mult = '&searchhash='.$searchhash;
    		return true;
		}
	}

	function buildCache($ids)
	{
		global $timestamp;

		$this->DB->db_exec("DELETE FROM {$this->db_prefix}scaches WHERE exptime <= '$timestamp'");
		$this->DB->db_exec("INSERT INTO {$this->db_prefix}scaches (hash,keywords,num,ids,searchip,searchtime,exptime) VALUES ('".$this->_cacheHash."','','".$this->_resultNum."','$ids','','','".($timestamp + $this->_cacheTime)."')");
		$this->_mult = '&searchhash='.$this->_cacheHash;
	}

	function article($cached)
	{
		$anonymity = getSingleLang('common', 'common_anonymity');
		if ($this->_ifcache && !$cached)
		{
			$query = $this->DB->db_query("SELECT t.tid FROM {$this->db_prefix}threads t ".$this->_searchsql);
			$ids = '';
			$num = 0;
			while ($rs = $this->DB->fetch_all($query))
			{
				$ids .= (int)$rs['tid'].',';
				$num++;
			}
			if ($ids)
			{
				$this->_resultNum = (int)$num;
				$ids = substr($ids, 0, -1);
				$this->buildCache($ids);
				$this->_searchsql = " WHERE t.tid IN ($ids)";
				unset($ids, $num);
			}
		}
		if (!isset($this->_resultNum))
		{
			$rs = $this->DB->fetch_one("SELECT COUNT(*) num FROM {$this->db_prefix}threads t ".$this->_searchsql);
			$this->_resultNum = (int)$rs['num'];
		}
		$query = $this->DB->db_query("SELECT t.tid, t.subject, t.author, t.postdate, t.postip FROM {$this->db_prefix}threads t ".$this->_searchsql.$this->_limit);

		$article = array();
		while ($rs = $this->DB->fetch_all($query))
		{
			$rs['postdate'] = gdate($rs['postdate'], 'Y-m-d H:i');
			!$rs['author'] && $rs['author'] = $anonymity;
			$article[] = $rs;
		}
		return $article;
	}
	
	function comment($cached)
	{
		$anonymity = getSingleLang('common', 'common_anonymity');
		if ($this->_ifcache && !$cached)
		{
			$query = $this->DB->db_query("SELECT c.rid FROM {$this->db_prefix}comments c ".$this->_searchsql);
			$ids = '';
			$num = 0;
			while ($rs = $this->DB->fetch_all($query))
			{
				$ids .= (int)$rs['rid'].',';
				$num++;
			}
			if ($ids)
			{
				$this->_resultNum = (int)$num;
				$ids = substr($ids, 0, -1);
				$this->buildCache($ids);
				$this->_searchsql = " WHERE c.rid IN ($ids)";
				unset($ids, $num);
			}
		}
		if (!isset($this->_resultNum))
		{
			$rs = $this->DB->fetch_one("SELECT COUNT(*) num FROM {$this->db_prefix}comments c ".$this->_searchsql);
			$this->_resultNum = (int)$rs['num'];
		}
		$query = $this->DB->db_query("SELECT c.rid, c.content, c.author, c.postdate, c.postip FROM {$this->db_prefix}comments c ".$this->_searchsql.$this->_limit);

		$comment = array();
		while ($rs = $this->DB->fetch_all($query))
		{
			$rs['postdate'] = gdate($rs['postdate'], 'Y-m-d H:i');
			$rs['content'] = PBSubStr($rs['content'], 50);
			!$rs['author'] && $rs['author'] = $anonymity;
			$comment[] = $rs;
		}
		return $comment;
	}
	
//	function author()
//	{
//		$this->_sql = "SELECT * FROM {$this->db_prefix}threads t";
//	}
	
	function attachment($cached)
	{
		$anonymity = getSingleLang('common', 'common_anonymity');

		if ($this->_ifcache && !$cached)
		{
			$query = $this->DB->db_query("SELECT a.aid FROM {$this->db_prefix}attachments a ".$this->_searchsql);
			$ids = '';
			$num = 0;
			while ($rs = $this->DB->fetch_all($query))
			{
				$ids .= (int)$rs['aid'].',';
				$num++;
			}
			if ($ids)
			{
				$this->_resultNum = (int)$num;
				$ids = substr($ids, 0, -1);
				$this->buildCache($ids);
				$this->_searchsql = " WHERE a.aid IN ($ids)";
				unset($ids, $num);
			}
		}

		if (!isset($this->_resultNum))
		{
			$rs = $this->DB->fetch_one("SELECT COUNT(*) num FROM {$this->db_prefix}attachments a ".$this->_searchsql);
			$this->_resultNum = (int)$rs['num'];
		}
		$query = $this->DB->db_query("SELECT a.aid, a.tid, a.uid, a.filename, a.filesize, a.uploaddate, a.downloads FROM {$this->db_prefix}attachments a ".$this->_searchsql.$this->_limit);

		$attachment = array();
		while ($rs = $this->DB->fetch_all($query))
		{
			$rs['uploaddate'] = gdate($rs['uploaddate'], 'Y-m-d H:i');
			$rs['filename'] = htmlspecialchars($rs['filename']);
			$rs['filesize'] = getRealSize($rs['filesize']);
			$attachment[] = $rs;
		}
		return $attachment;
	}
	
    function cid($cid)
    {
    	!is_array($cid) && $cid = explode(',', $cid);
    	$newcid = '';
    	foreach ($cid as $v)
    	{
    		$v && is_numeric($v) && $newcid .= $newcid ? ',' : '' . (int)$v;
    	}
    	if ($newcid)
    	{
	    	$this->_searchsql .= " AND ".$this->_basetable.".cid IN ($newcid)";
	    	$this->_mult .= '&cid='.$newcid;
    	}
    }

    function mid($mid)
    {
    	global $module;
    	!is_array($mid) && $mid = explode(',', $mid);
    	$newmid = '';
    	if (!is_object($module))
    	{
			require_once PBDIGG_ROOT.'include/module.class.php';
			$module = new module();
    	}
    	foreach ($mid as $v)
    	{
    		$v && is_numeric($v) && in_array($mid, $module->getModuleId()) && $newmid .= $newmid ? ',' : '' . (int)$v;
    	}
    	if ($newmid)
    	{
	    	$this->_searchsql .= " AND ".$this->_basetable.".module IN ($newmid)";
	    	$this->_mult .= '&mid='.$newmid;
    	}
    }

    function uid($uid)
    {
    	!is_array($uid) && $uid = explode(',', $uid);
    	$newuid = '';
    	foreach ($uid as $v)
    	{
    		$v && is_numeric($v) && $newuid .= $newuid ? ',' : '' . (int)$v;
    	}
    	if ($newuid)
    	{
	    	$this->_searchsql .= " AND ".$this->_basetable.".uid IN ($newuid)";
	    	$this->_mult .= '&uid='.$newuid;
    	}
    }

    function authors($author)
    {
		$author = strip_tags(trim($author));
		$_author = explode(',', $author);
		$authorCondition = '';
		foreach ($_author as $value)
		{
			if (trim($value) && (strlen($value) <= 20))
			{
				$authorCondition .= " OR username LIKE '".str_replace($this->_pattern, $this->_replace, preg_replace('~\*{2,}~i', '*', $value))."'";
			}
		}
		if ($authorCondition)
		{
			$query = $this->DB->db_query("SELECT uid FROM {$this->db_prefix}members WHERE ".substr($authorCondition, 3));
			$uids = '';
			while ($rs = $this->DB->fetch_all($query))
			{
				$uids .= ",".(int)$rs['uid'];
			}
			$this->_searchsql .= $uids ? ' AND '.$this->_basetable.'.uid IN ('.substr($uids, 1).')' : ' AND 0';
			$this->_mult .= '&authors='.rawurlencode($author);
		}
    }
    
    function tags($tags)
    {
		$tags = strip_tags(trim($tags));
		$_tags = explode(',', $tags);
		$tagCondition = '';
		foreach ($_tags as $value)
		{
			if (trim($value) && (strlen($value) <= 30))
			{
				$tagCondition .= " OR tg.tagname LIKE '".str_replace($this->_pattern, $this->_replace, preg_replace('/\*{2,}/i', '*', $value))."'";
			}
		}
		if ($tagCondition)
		{
			$query = $this->DB->db_query("SELECT tc.tid FROM {$this->db_prefix}tagcache tc INNER JOIN {$this->db_prefix}tags tg USING (tagid) WHERE ".substr($tagCondition, 3));
			$tids = '';
			while ($rs = $this->DB->fetch_all($query))
			{
				$tids .= ",".(int)$rs['tid'];
			}
			$this->_searchsql .= $tids ? ' AND '.$this->_basetable.'.tid IN ('.substr($tids, 1).')' : ' AND 0';
			$this->_mult .= '&tags='.rawurlencode($tags);
		}
    }
    /**
     * @param string $datefield 时间字段
     */
    function searchdate($params)
    {
		foreach ($params as $k => $v)
		{
			if (preg_replace('~[a-z]~i', '', $k)) return;
			list ($more, $less) = $v;

			if ($more && preg_match('~^\d{4}-\d{1,2}-\d{1,2}$~i', $more))
			{
				$this->_searchsql .= " AND {$this->_basetable}.$k >= ".pStrToTime($more.' 0:0:0');
				$this->_mult .= '&'.$k.'more='.$more;
			}
			if ($less && preg_match('~^\d{4}-\d{1,2}-\d{1,2}$~i', $less))
			{
				$this->_searchsql .= " AND {$this->_basetable}.$k < ".pStrToTime($less.' 0:0:0');
				$this->_mult .= '&'.$k.'less='.$less;
			}
		}
    }

    function searchscope($params)
    {
		foreach ($params as $k => $v)
		{
			if (preg_replace('~[a-z]~i', '', $k)) return;
			list ($more, $less) = $v;
			if (is_numeric($more) && $more != -1)
			{
				$more = (int)$more;
				$this->_searchsql .= " AND {$this->_basetable}.$k >= $more";
				$this->_mult .= '&'.$k.'more='.$more;
			}
			if (is_numeric($less) && $less != -1)
			{
				$less = (int)$less;
				$this->_searchsql .= " AND {$this->_basetable}.$k < $less";
				$this->_mult .= '&'.$k.'less='.$less;
			}
		}
    }

    function postip($ip)
    {
    	if ($ip && preg_match('~^[0-9\*\.]+$~i', $ip))
		{
			$this->_searchsql .= " AND ".$this->_basetable.".postip ".((strpos($ip, '*') === FALSE) ? (" = '$ip'") : " LIKE '".str_replace('*', '%', preg_replace('~\*{2,}~i', '*', $ip))."'");
			$this->_mult .= '&postip='.rawurlencode($ip);
		}
    }

    function isimg($bool)
    {
    	if ($bool)
    	{
    		$this->_searchsql .= ' AND '.$this->_basetable.'.isimg = 1';
			$this->_mult .= '&isimg=1';
    	}
    }
    
    function linkhost($linkhost)
    {
    	if ($linkhost && preg_match('~^[-_a-z0-9\.\~!\$&\'\(\)\*\+,;=:@\|/]+$~i', $linkhost))
    	{
    		$this->_searchsql .= " AND ".$this->_basetable.".linkhost ".(strpos($linkhost, '*') === FALSE ? " = '$linkhost'" : " LIKE '".str_replace('*', '%', preg_replace('~\*{2,}~i', '*', $linkhost))."'");
			$this->_mult .= '&linkhost='.rawurlencode($linkhost);;
    	}
    }
}
?>