<?php
/**
 * 
 * 模板内容调用类
$_tplvatable = array(

	'tpl_ccc1cdad' => array(
		'tranttype' => '调用类型：'article', 'comment', 'member', 'html', 'sql'',
		'cachetime' => '缓存周期，如果不限制则调用系统缓存时间',
		'lastmodify' => '上次缓存更新时间',
		'tplmark' => '模板标识',
		'trantnum' => '调用数量',
		'trantattribute' => '条件属性',
		'cotentlimit' => '内容限制',
		'fields' => '字段',
		'specialfields' => '特殊字段',
		'titlelimit' => '标题限制',
		'timeformat' => 'Y-m-d H:i:s',
		'trantorder' => '排序字段',
		'trantby' => 'DESC/ASC',
		'tplcontent' => '模板内容：<li><a href="{!--turl--}">{!--subject--}</a></li>',
		'querysql' => 'SQL语句',
	)
);
 */
 
class transfer
{
	/**
	 * 刷新缓存次数
	 */
	var $_refreshCacheNum = 0;

	/**
	 * 缓存模板内容
	 */
	var $_cacheTplContent = '';
	
	/**
	 * 模板调用标识列表
	 */
	var $_tplVarTable = array();

	/**
	 * 当前模板缓存
	 */
	var $_currentTplVar = array();
	
	/**
	 * 数据库实例
	 */
	var $DB = null;
	
	/**
	 * 模型实例
	 */
	var $module = null;
	
	/**
	 * 模块实例缓存
	 */
	var $_moduleObjects = array();

	function transfer()
	{
		$this->__contruct();
	}

	function __contruct()
	{
		include_once PBDIGG_CROOT.'cache_tplvar.php';
		$this->_tplVarTable = $_tplvartable;
		$this->DB = $GLOBALS['DB'];
		$this->module = $GLOBALS['module'];
		$modules = $this->module->getModuleMenu();
		foreach ($modules as $k => $v)
		{
			$this->_moduleObjects[$v[0]] = array($k);
		}
	}

	function getTplVar($var)
	{
		global $timestamp, $cid;
		$cid = (int)$cid;
		list ($type, $identifier) = explode('_', $var);
		$newvar = ($type == 'self') ? $type.'_'.$identifier.'_'.$cid : $var;

		if (isset($this->_cacheTplContent[$newvar])) return $this->_cacheTplContent[$newvar];

		if ($type == 'self' && !isset($this->_tplVarTable[$newvar]))
		{
			$this->_tplVarTable[$newvar] = $this->_tplVarTable[$var];
			if ($this->_tplVarTable[$newvar]['querysql'])
			{
				$this->_tplVarTable[$newvar]['querysql'] = str_replace('{#cid#}', $cid, $this->_tplVarTable[$newvar]['querysql']);
			}
		}

		$this->_currentTplVar = $tplVar = &$this->_tplVarTable[$newvar];
		if (!$tplVar) return $GLOBALS['common_message']['tpl_var_empty'];
		$tplVar['cachefile'] = PBDIGG_CROOT.'%%'.sprintf('%08X', crc32($newvar)).'%%.html';

		if ($tplVar['tranttype'] == 'html' || !$tplVar['replacefields'])
		{
			$this->_cacheTplContent[$newvar] = $tplVar['tplcontent'];
		}
		elseif (!$tplVar['cachetime'])
		{
			$this->_cacheTplContent[$newvar] = $this->tplcontent($tplVar);
		}
		elseif (($timestamp - $tplVar['cachetime'] <= $tplVar['lastmodify']) && file_exists($tplVar['cachefile']))
		{
			$this->_cacheTplContent[$newvar] = PReadFile($tplVar['cachefile']);
		}
		else
		{
			$this->_cacheTplContent[$newvar] = $this->tplcontent($tplVar);
			PWriteFile($tplVar['cachefile'], $this->_cacheTplContent[$newvar], 'wb');
			$tplVar['lastmodify'] = $timestamp;
			$this->buildTplVarTable();
		}

//		if (($tplVar['tranttype'] == 'html' || ($tplVar['lastmodify'] >= $timestamp - $tplVar['cachetime']) || $this->_refreshCacheNum > 2) && file_exists($tplVar['cachefile']))
//		{
//			$this->_cacheTplContent[$newvar] = PReadFile($tplVar['cachefile']);
//		}
//		else
//		{
//			$this->_cacheTplContent[$newvar] = ($tplVar['specialfields'] && $tplVar['tranttype'] != 'html') ? $this->tplcontent($tplVar) : $tplVar['tplcontent'];
//			PWriteFile($tplVar['cachefile'], $this->_cacheTplContent[$newvar], 'wb');
//			$tplVar['lastmodify'] = $timestamp;
//			$this->buildTplVarTable();
//		}
		return $this->_cacheTplContent[$newvar];
	}

	//特殊字段处理函数
	function turl(&$var, $prefix)
	{
		global $pb_rewrite, $pb_sitedir;
		return $var['t.realurl'] ? $var['t.realurl'] : ($pb_rewrite ? rewriteThread($var['t.tid']) : $pb_sitedir.'show.php?tid='.$var['t.tid']);
	}

	function curl(&$var, $prefix)
	{
		global $pb_rewrite, $_categories;
		return $pb_rewrite ? rewriteCate($var[$prefix.'cid']) : 'category.php?cid='.$var[$prefix.'cid'];
	}

	function uurl(&$var, $prefix)
	{
		return userSpace($var['m.uid'], $var['m.ucuid']);
	}

	function postdate(&$var, $prefix)
	{
		return $this->_currentTplVar['timeformat'] == '-1' ? formatPostTime($var[$prefix.'postdate']) : gdate($var[$prefix.'postdate'], $this->_currentTplVar['timeformat']);
	}
	
	function avatar(&$var, $prefix)
	{
		return userFace($var['m.avatar'], $var['m.ucuid']);
	}

	function cname(&$var, $prefix)
	{
		global $_categories;
		return isset($_categories[$var[$prefix.'cid']]) ? $_categories[$var[$prefix.'cid']]['name'] : '';
	}

	function topicimg(&$var, $prefix)
	{
		$topicimg = '';
		if ($var['t.topicimg'])
		{
			global $pb_sitedir, $_attdir;
			list($topicimg,,) = explode('|', $var['t.topicimg']);
			$topicimg = $pb_sitedir.$_attdir.'/topic/'.$topicimg;
		}
		return $topicimg;
	}

	function summary(&$var, $prefix)
	{
		return str_replace("\n", '<br />', $this->_currentTplVar['cotentlimit'] ? traceHtml(cutSpilthHtml(PBSubstr($var['t.summary'], $this->_currentTplVar['cotentlimit']))) : $var['t.summary']);
	}

	function subject(&$var, $prefix)
	{
		$style = $subject = '';
		$var['t.titlecolor'] && $style .= 'color:#'.$var['t.titlecolor'].';';
		if ($var['t.titlestyle'])
		{
			/*b/em/u*/
			($var['t.titlestyle'] & 1) && $style .= 'font-weight:bold;';
			($var['t.titlestyle'] & 2) && $style .= 'font-style:italic;';
			($var['t.titlestyle'] & 4) && $style .= 'text-decoration:underline;';
		}
		$subject = $this->_currentTplVar['titlelimit'] ? PBSubstr($var['t.subject'], $this->_currentTplVar['titlelimit']) : $var['t.subject'];
		$style && $subject = '<span style="'.$style.'">'.$subject.'</span>';
		return $subject;
	}

	function altsubject(&$var, $prefix)
	{
		return $var['t.subject'];
	}

	function content(&$var, $prefix)
	{
		if (!function_exists('emCode'))
		{
			require_once PBDIGG_ROOT.'include/ubb.func.php';
		}
		$content = preg_replace(array('~\[(flash|media)[^]]*\].+?\[/\\1\]~is', '~\[em:(\d+)\]~ies', '~\[quote(?:=([^]]*))?\]\s*(.+?)\s*\[/quote\]~eis'), array('', "emCode('\\1')", "qouteCode('\\1','\\2')"), $var['r.content']);
		return traceHtml(cutSpilthHtml($this->_currentTplVar['cotentlimit'] ? PBSubstr($content, $this->_currentTplVar['cotentlimit']) : $content));
	}

	function regdate(&$var, $prefix)
	{
		return $this->_currentTplVar['timeformat'] == '-1' ? formatPostTime($var['m.regdate']) : gdate($var['m.regdate'], $this->_currentTplVar['timeformat']);
	}

	function gender(&$var, $prefix)
	{
		global $common_message;
		return $var['m.gender'] ? ($var['m.gender'] == '1' ? $common_message['female'] : $common_message['secrecy']) : $common_message['male'];
	}

	function moduleSpecialfield($identifier, $table, $field, &$var)
	{
		if (!isset($this->_moduleObjects[$identifier][1]))
		{
			$this->_moduleObjects[$identifier][1] = $this->module->getModuleObject($this->_moduleObjects[$identifier][0]);
		}
		$this->_moduleObjects[$identifier][1]->transfer($field, $table, $var);
	}
	
	function tplcontent(&$tplVar)
	{
		global $timestamp;
		$content = '';
		$tplVar['querysql'] = str_replace(array('{timestamp}'), array($timestamp), $tplVar['querysql']);
		$query = $this->DB->db_query($tplVar['querysql']);
		$replacefields = $tplVar['replacefields'] ? explode(',', $tplVar['replacefields']) : '';
		$specialfields = $tplVar['specialfields'] ? explode(',', $tplVar['specialfields']) : '';

		$search = array();
		foreach ($replacefields as $replacefield)
		{
			$search[] = '{!--'.$replacefield.'--}';
		}

		while ($rs = $this->DB->fetch_all($query))
		{
			if ($specialfields)
			{
				foreach ($specialfields as $specialfield)
				{
					list($prefix, $suffix) = explode('.', $specialfield);
					if (in_array($prefix, array('t','c','r','m','mx')))
					{
						$rs[$specialfield] = $this->$suffix($rs, $prefix.'.');
					}
					else
					{
						$table = $prefix;
						if (($pos = strpos($prefix, '_')) !== FALSE)
						{
							$prefix = substr($prefix, 0, $pos);
						}
						$this->moduleSpecialfield($prefix, $table, $suffix, $rs);
					}
				}
			}
			$replace = array();
			foreach ($replacefields as $replacefield)
			{
				$replace[] = $rs[$replacefield];
			}
			$content .= str_replace($search, $replace, $tplVar['tplcontent']);
		}
		return $content;
	}

	function buildTplVarTable()
	{
		$tplvartable = "<?php\r\n!defined('IN_PBDIGG') && exit('Access Denied!');\r\n\$_tplvartable = ".pb_var_export($this->_tplVarTable).";\r\n?>";
		PWriteFile(PBDIGG_CROOT.'cache_tplvar.php', $tplvartable, 'wb');
	}
}
	
?>