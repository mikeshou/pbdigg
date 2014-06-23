<?php
/**
 * @version $Id: global.func.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

!defined('IN_PBDIGG') && exit('Access Denied!');

function addS(&$array)
{
	if (is_array($array))
	{
		foreach ($array as $key => $value)
		{
			addS($array[$key]);
		}
	}
	elseif (is_string($array))
	{
		$array = addslashes($array);
	}
}

function stripS(&$array)
{
   	if (is_array($array))
	{
		foreach ($array as $key => $value)
		{
			stripS($array[$key]);
		}
	}
	elseif (is_string($array))
	{
		$array = stripslashes($array);
	}
}

function reS(&$array)
{
	if (is_array($array))
	{
		foreach ($array as $key => $value)
		{
			reS($array[$key]);
		}
	}
	elseif (is_string($array))
	{
		$array = addslashes(stripslashes($array));
	}
}

function gdate($time = '', $format = '')
{
	global $pb_timezone,$pb_timeformat,$pb_dateformat;
	!$time && $time = time();
	if (!$format)
	{
		//24 OR 12 time format
		$hour = $pb_timeformat ? 'H:i' : 'h:i A';
		$format = $pb_dateformat . ' ' . $hour;
	}
	return gmdate($format, ($time + $pb_timezone * 3600));
}
function loadPluginLang($mark, $cp = FALSE)
{
	global $pb_lang;
	$plugin_message = '';
	$filename = $mark;
	if ($cp) $filename .= '_cp';
	$langPath = PBDIGG_ROOT.'plugins/'.$mark.'/languages/'.$pb_lang.'/'.$filename.'.lang.php';
	if (!file_exists($langPath))
	{
		$langPath = PBDIGG_ROOT.'plugins/'.$mark.'/languages/zh/'.$filename.'.lang.php';
		if (!file_exists($langPath))
		{
			exit('Load Language File Failed!');
		}
	}
	require $langPath;
	return $plugin_message;
}
function &loadLang($type)
{
	global $pb_lang;
	static $lang = array();
	if (isset($lang[$type])) return $lang[$type];
	$langPath = PBDIGG_ROOT.'languages/'.$pb_lang.'/'.$type.'.lang.php';
	if (!file_exists($langPath))
	{
		$langPath = PBDIGG_ROOT.'languages/zh/'.$type.'.lang.php';
		if (!file_exists($langPath))
		{
			exit('Load Language File Failed!');
		}
	}
	require $langPath;
	$lang[$type] = ${$type.'_message'};
	return $lang[$type];
}

function getSingleLang($type, $key)
{
	$lang = &loadLang($type);
	$SingleLang = isset($lang[$key]) ? $lang[$key] : '';
	unset($lang);
	return $SingleLang;
}
function gCookie($var)
{
	global $pb_ckpre;
	$ckpre = $pb_ckpre ? substr(md5($pb_ckpre), 8, 6).'_' : '';
	return isset($_COOKIE[$ckpre.$var]) ? $_COOKIE[$ckpre.$var] : '';
}

/**
 * 设置COOKIE信息
 * 
 * @param $name cookie名称
 * @param $value cookie值
 * @param $expire 过期时间：-1：删除，0：即时，1：永久
 * @return Boolean 设置cookie是否成功
 */
function sCookie($name, $value, $expire = 0, $httponly = true)
{
	global $pb_ckpath, $pb_ckdomain, $pb_ckpre, $timestamp;
	switch ($expire)
	{
		case 0:
			$expire = 0;
			break;
		case 1:
			$expire = $timestamp + 31536000;
			break;
		case -1:
			$expire = $timestamp - 31536000;
			break;
		default:
			$expire += $timestamp;
			break;
	}
	!$pb_ckpath && $pb_ckpath = '/';
	$secure = ($_SERVER['SERVER_PORT'] == '443') ? 1 : 0;
	$ckpre = $pb_ckpre ? substr(md5($pb_ckpre), 8, 6).'_' : '';
	if (PHP_VERSION >= '5.2.0')
	{
		return setcookie($ckpre.$name, $value, $expire, $pb_ckpath, $pb_ckdomain, $secure, ($httponly ? 1 : 0));	
	}
	else
	{
		return setcookie($ckpre.$name, $value, $expire, $pb_ckpath, $pb_ckdomain, $secure);
	}
}

/**
 * 清除cookies
 */
function clearcookie()
{
	sCookie('pb_sid', '', -1);
	sCookie('pb_auth', '', -1);
	sCookie('pb_lastvisit', '', -1);
	sCookie('pb_tdigged', '', -1);
	sCookie('pb_tburied', '', -1);
	sCookie('pb_rdigged', '', -1);
	sCookie('pb_rburied', '', -1);
}

function getIP()
{
	$isagent = TRUE;
	if (isset ($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
		$currentIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	elseif (isset ($_SERVER['HTTP_CLIENT_IP']))
	{
		$currentIP = $_SERVER['HTTP_CLIENT_IP'];
	}
	else
	{
		$currentIP = $_SERVER['REMOTE_ADDR'];
		$isagent = FALSE;
	}
	return array((preg_match('~[\d\.]{7,15}~', $currentIP, $match) ? $match[0] : 'unknow'), $isagent);
}
function redirect($msg, $url, $time = 2)
{
	showMsg($msg, $url, $time * 1000, 1);
}

function showMsg($msg, $url = '', $time = 0, $redirect = 0)
{
	@extract($GLOBALS, EXTR_SKIP);
	define('MSG', TRUE);
	ob_end_clean();
	obStart();
	if (defined('IN_ADMIN'))
	{
		$admin_msg = loadLang('admin') + loadLang('common');
		if (defined('IN_PLUGIN')) $admin_msg += loadPluginLang(IN_PLUGIN, TRUE);
		if (defined('IN_MODULE')) $admin_msg += loadLang(IN_MODULE);
		isset($admin_msg[$msg]) && eval("\$msg = \"".addcslashes($admin_msg[$msg], '"')."\";");
	}
	else
	{
		isset($common_message[$msg]) && eval("\$msg = \"".addcslashes($common_message[$msg], '"')."\";");
		$pb_seotitle = strip_tags($msg);
	}

	if (PB_PAGE == 'ajax') return $msg;
	require_once pt_fetch('msg');
	PBOutPut();
}
function PEncode($txt, $key)
{
	srand((double)microtime() * 1000000);
	$encrypt_key = md5(rand(0, 32000));
	$ctr = 0;
	$tmp = '';
	for($i = 0;$i < strlen($txt); $i++) 
	{
		$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		$tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
	}
	return base64_encode(PKey($tmp, $key));
}

function PDecode($txt, $key) 
{
	$txt = PKey(base64_decode($txt), $key);
	$tmp = '';
	for ($i = 0;$i < strlen($txt); $i++)
	{
		$md5 = $txt[$i];
		$tmp .= $txt[++$i] ^ $md5;
	}
	return $tmp;
}

function PKey($txt, $encrypt_key)
{
	$encrypt_key = md5($encrypt_key);
	$ctr = 0;
	$tmp = '';
	for($i = 0; $i < strlen($txt); $i++)
	{
		$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		$tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
	}
	return $tmp;
}

function spendTime()
{
	global $loadTime;
	$mtime = explode(' ', microtime());
	return number_format(($mtime[1] + $mtime[0] - $loadTime), 6);
}

function writeSafeContent($filename, $content, $mod)
{
	PWriteFile($filename, "<?php\r\n!defined('IN_PBDIGG') && exit('Access Denied!');\r\n".$content."\r\n?>", $mod);
}

function PWriteFile($filename, $content, $mode = 'ab', $chmod = 1)
{
	strpos($filename, '..') !== FALSE && exit('Access Denied!');

	$fp = @fopen($filename, $mode);
	if ($fp)
	{
		flock($fp, LOCK_EX);
		fwrite($fp, $content);
		fclose($fp);
		$chmod && @chmod($filename, 0666);
		return TRUE;
	}
	return FALSE;
}

function PReadFile($filename, $mode = 'rb')
{
	strpos($filename, '..') !== FALSE && exit('Access Denied!');
	if ($fp = @ fopen($filename, $mode))
	{
		flock($fp, LOCK_SH);
		$filedata = @ fread($fp, filesize($filename));
		fclose($fp);
	}
	return $filedata;
}
function PCopy($source, $dest)
{
	return @copy($source, $dest) || PWriteFile($dest, PReadFile($source), 'wb');
}
function PMove($source, $dest)
{
	if (@copy($source, $dest) || PWriteFile($dest, PReadFile($source), 'wb'))
	{
		PDel($source);
		return true;
	}
}
function PDel($var)
{
	return strpos($var, '..') === FALSE && is_file($var) && @unlink($var) ? TRUE : FALSE;
}

function PDelDir($var)
{
	return strpos($var, '..') === FALSE && is_dir($var) && @rmdir($var) ? TRUE : FALSE;
}

function emptyDir($dir)
{
	$newdir = '';
	if (is_dir($dir))
	{
		if ($dh = opendir($dir))
		{
	        while (($file = readdir($dh)) !== FALSE)
	        {
	            if ($file != '..' && $file != '.')
	            {
	            	$newdir = $dir.'/'.$file;
	            	if (is_dir($newdir))
	            	{
	            		emptyDir($newdir, FALSE);
	            	}
	            	else
	            	{
	            		@unlink($dir.'/'.$file);
	            	}
	            }
	        }
	        closedir($dh);
    	}
    	@rmdir($dir);
	}
}

function PListFile($var, $type = array())
{
	if (is_dir($var))
	{
		$rs = @opendir($var);
		while (($file = readdir($rs)) !== FALSE)
		{
			if ($file != '..' && $file != '.')
			{
				if ($type && in_array(Fext($file), $type))
				$rt[] = $file;
			}
		}
		return $rt;
	}
	return FALSE;
}

function Pmkdir($var, $basedir, $force = FALSE)
{
	if (strpos($var, '..') !== FALSE || strpos($basedir, '..') !== FALSE)
	{
		exit('Access Denied!');
	}
	if (!is_dir($basedir.$var))
	{
		$var = preg_replace('/\/{2,}/', '/', str_replace('\\', '/', $var));
		$basedir = preg_replace('/\/{2,}/', '/', str_replace('\\', '/', $basedir));
		$temp = explode('/',$var);
		$dirnum = count($temp);
		$cur_dir = $basedir.'/';
		for($i = 0; $i < $dirnum; $i++)
		{
			$cur_dir .= $temp[$i].'/';
			if (!is_dir($cur_dir))
			{
				if (!mkdir($cur_dir, 0777) && $force)
				{
					showMsg('attachment_mkdir_failed');
				}
			} 
		}
	}
	return TRUE;
}

function random($length, $isNum = FALSE)
{
	$random = '';
	$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$num = '0123456789';
	if ($isNum)
	{
		$sequece = 'num';
	}
	else
	{
		$sequece = 'str';
	}
	$max = strlen($$sequece) - 1;
	for ($i = 0; $i < $length; $i++)
	{
		$random .= ${$sequece}{mt_rand(0, $max)};
	}
	return $random;
}

function getUserHash($uid)
{
	return substr(md5(intval($uid).$GLOBALS['pb_sitehash'].ipFragment($GLOBALS['_PBENV']['PB_IP'])), 8, 8);
}

function HConvert($var)
{
	if (is_array($var))
	{
		foreach ($var as $key => $value)
		{
			$var[$key] = HConvert($value);
		}
	}
	else
	{
		do
		{
			$clean = $var;
			$var = preg_replace('~&(?!(#[0-9]+|[a-z]+);)~is', '&amp;', $var);
			$var = preg_replace(array('~%0[0-8]~','~%1[124-9]~','~%2[0-9]~','~%3[0-1]~','~[\x00-\x08\x0b\x0c\x0e-\x1f]~'), '', $var);
		}
		while ($clean != $var);
		
		$var = str_replace(array('"', '\'', '<', '>', "\t", "\r"), array('&quot;', '&#39;', '&lt;', '&gt;', '&nbsp;&nbsp;', ''), $var);
	}
	return $var;
}

/**
 * HTML危险代码过滤
 * 
 * @param String $string 过滤字符
 */
function safeConvert(&$string)
{
	require_once PBDIGG_ROOT.'include/safehtml.class.php';
	$safehtml = & new HTML_Safe();
	$string = $safehtml->parse($string);
}

/**
 * 字符截取函数
 * 
 * @param String $text 内容
 * @param Integer $limit 截取长度
 * @param String $add 更多标记
 */
function PBSubstr($text, $limit, $add = '&#8230;')
{
	global $db_charset;
	$strlen = strlen($text);
	$db_charset = strtolower($db_charset);
	if($strlen <= $limit) return $text;
//	$text = str_replace(array('&nbsp;', '&quot;', '&#039;'), array(' ', '"', "'"), $text);
	$rtext = '';
	if ($db_charset == 'utf-8')
	{
		$n = $tn = $noc = 0;
		while ($n < $strlen)
		{
			$t = ord($text{$n});
			if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126))
			{
				$tn = 1; $n++; $noc++;
			}
			elseif (194 <= $t && $t <= 223)
			{
				$tn = 2; $n += 2; $noc += 2;
			}
			elseif (224 <= $t && $t < 239)
			{
				$tn = 3; $n += 3; $noc += 2;
			}
			elseif (240 <= $t && $t <= 247)
			{
				$tn = 4; $n += 4; $noc += 2;
			}
			elseif (248 <= $t && $t <= 251)
			{
				$tn = 5; $n += 5; $noc += 2;
			}
			elseif ($t == 252 || $t == 253)
			{
				$tn = 6; $n += 6; $noc += 2;
			}
			else
			{
				$n++;
			}
			if($noc >= $limit) break;
		}
		if($noc > $limit) $n -= $tn;
		$rtext = substr($text, 0, $n);
	}
	else
	{
		$addlen = strlen($add);
		$limit -= $addlen - 1;
		for ($i = 0; $i < $limit; $i++)
		{
			$rtext .= ord($text[$i]) > 127 ? $text[$i] . $text[++$i] : $text[$i];
		}
	}
//	$rtext = str_replace(array('"', "'"), array('&quot;', '&#039;'), $rtext);
	return $rtext.$add;
}

/**
 * Convert character encoding
 * 
 * @param String $from_encoding 原始编码
 * @param String $to_encoding 目标编码
 * @param String $str 编码字符串
 */
function convert_encoding($from_encoding, $to_encoding, $str)
{
	if(empty($str) || (strtolower($from_encoding) == strtolower($to_encoding))) return $str;
//	$from_encoding = str_replace('gbk', 'gb2312', $from_encoding);
//	$to_encoding = str_replace('gbk', 'gb2312', $to_encoding);

	if(function_exists('mb_convert_encoding'))
	{
		return mb_convert_encoding($str, $to_encoding, $from_encoding);
	}
	elseif (function_exists('iconv'))
	{
		return iconv($from_encoding, $to_encoding.'//IGNORE', $str);
	}
	else
	{
		global $CHS;
		if (!is_object($CHS))
		{
			require_once PBDIGG_ROOT.'include/Chinese.class.php';
			$CHS = new Chinese($from_encoding, $to_encoding);
		}
		return $CHS->Convert($str);
	}
}

function ipControl($ip)
{
	global $pb_ipallow, $pb_ipdeny;
	$ipExist = FALSE;
	if ($pb_ipallow)
	{
		$allowip = explode("\n", unserialize($pb_ipallow));
		foreach ($allowip as $value)
		{
			if (preg_match("~^{$value}[\d\.]*~i", $ip))
			{
				$ipExist = TRUE;
				break;
			}
		}
		if (!$ipExist)
		{
			showMsg('ip_limit');
		}
	}
	if ($pb_ipdeny)
	{
		$ipdeny = explode("\n", unserialize($pb_ipdeny));
		foreach ($ipdeny as $value)
		{
			preg_match("~^{$value}[\d\.]*~i", $ip) && showMsg('ip_limit');
		}
	}
}

function parCate($cate, &$parCate, $cid = 0)
{
	if (!$cate[$cid]['cup']) return;
	foreach ($cate as $key => $value)
	{
		if ($value['cid'] == $cate[$cid]['cup'])
		{
			$parCate[] = $value['cid'];
			parCate($cate, $parCate, $value['cid']);
		}
	}
}
function subCate($cate, &$subCate, $cid = 0)
{
	foreach ($cate as $key => $value)
	{
		if ($value['cup'] == $cid)
		{
			$subCate[] = $value['cid'];
			subCate($cate, $subCate, $value['cid']);
		}
	}
}

function str_parcate($cate, &$str_parcate, $startID = 0)
{
	if (!$cate[$startID]['cup']) return;
	foreach ($cate as $key => $value)
	{
		if ($value['cid'] == $cate[$startID]['cup'])
		{
			$str_parcate .= $value['cid'].',';
			str_parcate($cate, $str_parcate, $value['cid']);
		}
	}
}
function str_subcate($cate, &$str_subcate, $startID = 0)
{
	foreach ($cate as $key => $value)
	{
		if ($value['cup'] == $startID)
		{
			$str_subcate .= $value['cid'].',';
			str_subcate($cate, $str_subcate, $value['cid']);
		}
	}
}

function cateOption($cate, &$option, $startID = 0, $index = 0, $level = 0)
{
	foreach ($cate as $key => $value)
	{
		if ($value['cup'] == $startID)
		{
			$option .= '<option value="'.$value['cid'].'"';
			$value['cid'] == $index && $option .= ' selected="select" style="background:#ffffde"';
			$option .= '>'.str_repeat('&nbsp;', $level).'| - '.htmlspecialchars($value['name']).'</option>';
			cateOption($cate, $option, $value['cid'], $index, $level + 1);
		}
	}
}

/**
 * URL重写
 * 
 * @param String $str
 */
function rewrite(&$str)
{
	if (!$str) return;
	$str = preg_replace("~<a href\=\"index.php(\?p=([0-9]{1,}))?\"([^\>]*)\>~e", "'<a href=\"'.rewriteIndex('\\2').'\"'.stripslashes('\\3').'>'", $str);
	$str = preg_replace("~<a href\=\"category.php\?cid=([0-9]{1,})(&amp;p=([0-9]{1,}))?\"([^\>]*)\>~e", "'<a href=\"'.rewriteCate('\\1', '\\3').'\"'.stripslashes('\\4').'>'", $str);
	$str = preg_replace("~<a href\=\"show.php\?tid=([0-9]{1,})(&amp;p=([0-9]{1,}))?\"([^\>]*)\>~e", "'<a href=\"'.rewriteThread('\\1', '\\3').'\"'.stripslashes('\\4').'>'", $str);
	$str = preg_replace("~<a href\=\"user.php\?uid=([0-9]{1,})(&amp;p=([0-9]{1,}))?\"([^\>]*)\>~e", "'<a href=\"'.rewriteUser('\\1', '\\3').'\"'.stripslashes('\\4').'>'", $str);
}
function rewriteIndex($page)
{
	global $pb_rewriteext, $pb_extenable, $pb_sitedir;
	return $page > 1 ? $pb_sitedir.'index'.($pb_extenable ? '_'.$page.'.'.$pb_rewriteext : $page) : $pb_sitedir;
}
function rewriteCate($cid, $page = 0)
{
	global $pb_rewriteext, $pb_extenable, $pb_sitedir, $_categories, $pb_chtmldir;
	return $pb_sitedir.($_categories[$cid]['dir'] ? $_categories[$cid]['dir'] : $pb_chtmldir.'/'.$cid).($page > 1 ? ('/'.$page.($pb_extenable ? '.'.$pb_rewriteext : '')) : '');
}
function rewriteThread($tid, $page = 0)
{
	global $pb_rewriteext, $pb_extenable, $pb_sitedir, $pb_shtmldir;
	return $pb_sitedir.$pb_shtmldir.'/'.$tid.($page > 1 ? ($pb_extenable ? '_'.$page.'.'.$pb_rewriteext : '/'.$page) : ($pb_extenable ? '.'.$pb_rewriteext : ''));
}
function rewriteUser($uid, $page = 0)
{
	global $pb_rewriteext, $pb_extenable, $pb_sitedir;
	if (!$uid) return '#';
	return $pb_sitedir.'user/'.$uid.($page > 1 ? ($pb_extenable ? '_'.$page.'.'.$pb_rewriteext : '/'.$page) : ($pb_extenable ? '.'.$pb_rewriteext : ''));
}

/**
 * 将数组转换为可通过 url 传递的字符串连接
 *
 * @param array $args
 * @return string
 */
function gURL($args)
{
    $str = $amp = '';
    foreach ($args as $key => $value)
    {
        if (is_array($value))
        {
            $str .= $amp . gURL($value);
        }
        else
        {
            $str .= $amp . rawurlencode($key) . '=' . rawurlencode($value);
        }
        $amp = '&amp;';
    }
    return $str;
}

/**
 * 返回GET OR POST变量值
 */
function GP($value, $method = 'G')
{
	if($method == 'G' && isset($_GET[$value]))
	{
		return $_GET[$value];
	}
	return $_POST[$value];
}

function formatPostTime($seconds)
{
	global $common_message, $pb_dformat, $timestamp;
	if ($pb_dformat)
	{
		$timestr = gdate($seconds);
	}
	else
	{
		$seconds = $timestamp - $seconds;
		$days = $hours = $timestr = '';
		$days = floor($seconds / 86400);
		if ($days > 0)
		{
			$seconds -= $days * 86400;
			$timestr .= $days.$common_message['format_post_day'].' ';
		}
		$hours = floor($seconds / 3600);
		if ($hours > 0)
		{
			$seconds -= $hours * 3600;
			$timestr .= $hours.$common_message['format_post_hour'].' ';
		}
		$timestr .= floor($seconds / 60).$common_message['format_post_minutebefore'];
	}
	return $timestr;
}

function Fext($filename)
{
	return strtolower(trim(substr(strrchr($filename, '.'), 1)));
}

function radioChecked($prefix, $index = 0, $number = 2)
{
	for ($i = 0; $i < $number; $i++)
	{
		$GLOBALS[$prefix.$i] = $i == $index ? 'checked="checked"' : '';
	}
}

function html_radio($params, $name, $index = '')
{
	$radio = '';
    foreach ($params as $key => $value)
    {
        $radio .= '<input type="radio" name="'.$name.'" value="'.htmlspecialchars($key).'"';
        $index == $key && $radio .= ' checked="checked"';
        $radio .= ' />&nbsp;'.htmlspecialchars($value).'&nbsp;&nbsp;';
    }
   return $radio;
}

function html_select($params, $name, $index = '', $extra = '')
{
    $select = "<select name=\"{$name}\" {$extra}>";
    foreach ($params as $key => $value)
    {
        $select .= '<option value="'.htmlspecialchars($key).'"';
        $index == $key && $select .= ' selected="selected"';
        $select .= '>'.htmlspecialchars($value)."</option>";
    }
   $select .= "</select>";
   return $select;
}

function html_checkbox($params, $name, $index = array(), $newline = 0, $extra = '')
{
	$checkbox = '';
	$i = 1;
	foreach ($params as $key => $value)
	{
		$checkbox .= '<input type="checkbox" name="'.$name.'" value="'.$key.'"';
		if (in_array($key, $index))
		{
			$checkbox .= ' checked="checked"';
		}
		$checkbox .= $extra.' />'.htmlspecialchars($value).'&nbsp;';
		if ($newline && ($i % $newline == 0))
		{
			$checkbox .= '<br />';
		}
		$i ++;
	}
	return $checkbox;
	
}

function multLink($currentPage, $totalRecords, $url, $pageSize = 10)
{
	$lang_prev = getSingleLang('common', 'prev');
	$lang_next = getSingleLang('common', 'next');
	if ($totalRecords <= $pageSize) return '';
	$mult = '';
	$totalPages = ceil($totalRecords / $pageSize);
	$mult .= '<div class="pages"><div class="nextprev">';
	$currentPage < 1 && $currentPage = 1;
	if ($currentPage > 1)
	{
		$mult .= '<a href="'.$url.'p='.($currentPage - 1).'">'.$lang_prev.'</a>';
	}
	else
	{
		$mult .= '<span class="nextprev">'.$lang_prev.'</span>';	
	}
	if ($totalPages < 13)
	{
		for ($counter = 1; $counter <= $totalPages; $counter++)
		{
			if ($counter == $currentPage)
			{
				$mult .= '<span class="current">'.$counter.'</span>';	
			}
			else
			{
				$mult .= '<a href="'.$url.'p='.$counter.'">'.$counter.'</a>';
			}
		}
	}
	elseif ($totalPages > 11)
	{
		if($currentPage < 7)		
		{
			for ($counter = 1; $counter < 10; $counter++)
			{
				if ($counter == $currentPage)
				{
					$mult .= '<span class="current">'.$counter.'</span>';
				}
				else
				{
					$mult .= '<a href="'.$url.'p='.$counter.'">'.$counter.'</a>';
				}	
			}
			$mult .= '<span>&#8230;</span><a href="'.$url.'p='.($totalPages-1).'">'.($totalPages-1).'</a><a href="'.$url.'p='.$totalPages.'">'.$totalPages.'</a>';	
		}
		elseif($totalPages - 6 > $currentPage && $currentPage > 6)
		{
			$mult .= '<a href="'.$url.'p=1">1</a><a href="'.$url.'p=2">2</a><span>&#8230;</span>';
			for ($counter = $currentPage - 3; $counter <= $currentPage + 3; $counter++)
			{
				if ($counter == $currentPage)
				{
					$mult .= '<span class="current">'.$counter.'</span>';	
				}
				else
				{
					$mult .= '<a href="'.$url.'p='.$counter.'">'.$counter.'</a>';
				}					
			}
			$mult .= '<span>&#8230;</span><a href="'.$url.'p='.($totalPages-1).'">'.($totalPages-1).'</a><a href="'.$url.'p='.$totalPages.'">'.$totalPages.'</a>';		
		}
		else
		{
			$mult .= '<a href="'.$url.'p=1">1</a><a href="'.$url.'p=2">2</a><span>&#8230;</span>';
			for ($counter = $totalPages - 8; $counter <= $totalPages; $counter++)
			{
				if ($counter == $currentPage)
				{
					$mult .= '<span class="current">'.$counter.'</span>';	
				}
				else
				{
					$mult .= '<a href="'.$url.'p='.$counter.'">'.$counter.'</a>';
				}
			}
		}
	}
	if ($currentPage < $counter - 1)
	{
		$mult .= '<a href="'.$url.'p='.($currentPage + 1).'" class="nextprev">'.$lang_next.'</a>';
	}
	else
	{
		$mult .= '<span class="nextprev">'.$lang_next.'</span>';
	}
	$mult .= '</div></div>';
	return $mult;
}
function commentMultLink($currentPage, $totalRecords, $func, $pageSize = 10)
{
	$lang_prev = getSingleLang('common', 'prev');
	$lang_next = getSingleLang('common', 'next');
	if ($totalRecords <= $pageSize) return '';
	$mult = '';
	$totalPages = ceil($totalRecords / $pageSize);
	$mult .= '<div class="pages"><div class="nextprev">';
	$currentPage < 1 && $currentPage = 1;
	if ($currentPage > 1)
	{
		$mult .= '<a href="javascript:void();" onclick="'.$func.'('.($currentPage - 1).');">'.$lang_prev.'</a>';
	}
	else
	{
		$mult .= '<span class="nextprev">'.$lang_prev.'</span>';	
	}
	if ($totalPages < 13)
	{
		for ($counter = 1; $counter <= $totalPages; $counter++)
		{
			if ($counter == $currentPage)
			{
				$mult .= '<span class="current">'.$counter.'</span>';	
			}
			else
			{
				$mult .= '<a href="javascript:void();" onclick="'.$func.'('.$counter.')">'.$counter.'</a>';
			}
		}
	}
	elseif ($totalPages > 11)
	{
		if($currentPage < 7)		
		{
			for ($counter = 1; $counter < 10; $counter++)
			{
				if ($counter == $currentPage)
				{
					$mult .= '<span class="current">'.$counter.'</span>';
				}
				else
				{
					$mult .= '<a href="javascript:void();" onclick="'.$func.'('.$counter.')">'.$counter.'</a>';
				}	
			}
			$mult .= '<span>&#8230;</span><a href="javascript:void();" onclick="'.$func.'('.($totalPages-1).')">'.($totalPages-1).'</a><a href="javascript:void();" onclick="'.$func.'('.$totalPages.')">'.$totalPages.'</a>';	
		}
		elseif($totalPages - 6 > $currentPage && $currentPage > 6)
		{
			$mult .= '<a href="javascript:void();" onclick="'.$func.'(1)">1</a><a href="javascript:void();" onclick="'.$func.'(2)">2</a><span>&#8230;</span>';
			for ($counter = $currentPage - 3; $counter <= $currentPage + 3; $counter++)
			{
				if ($counter == $currentPage)
				{
					$mult .= '<span class="current">'.$counter.'</span>';	
				}
				else
				{
					$mult .= '<a href="javascript:void();" onclick="'.$func.'('.$counter.')">'.$counter.'</a>';
				}					
			}
			$mult .= '<span>&#8230;</span><a href="javascript:void();" onclick="'.$func.'('.($totalPages-1).')">'.($totalPages-1).'</a><a href="javascript:void();" onclick="'.$func.'('.$totalPages.')">'.$totalPages.'</a>';		
		}
		else
		{
			$mult .= '<a href="javascript:void();" onclick="'.$func.'(1)">1</a><a href="javascript:void();" onclick="'.$func.'(2)">2</a><span>&#8230;</span>';
			for ($counter = $totalPages - 8; $counter <= $totalPages; $counter++)
			{
				if ($counter == $currentPage)
				{
					$mult .= '<span class="current">'.$counter.'</span>';	
				}
				else
				{
					$mult .= '<a href="javascript:void();" onclick="'.$func.'('.$counter.')">'.$counter.'</a>';
				}
			}
		}
	}
	if ($currentPage < $counter - 1)
	{
		$mult .= '<a href="javascript:void();" onclick="'.$func.'('.($currentPage + 1).')" class="nextprev">'.$lang_next.'</a>';
	}
	else
	{
		$mult .= '<span class="nextprev">'.$lang_next.'</span>';
	}
	$mult .= '</div></div>';
	return $mult;
}
function is_Page($page)
{
	return !empty($page) && preg_match ('/^([0-9]+)$/', $page);
}

function thumb($img, $height, $width, $save_prefix = 'thumb_', $del = false)
{
	if (empty($img) || !gdEnable() || !isImg($img)) return $img;
	$imginfo = @getimagesize($img);
	switch($imginfo[2])
	{
		case 1:
			$tmp_img = @imagecreatefromgif($img);
			break;
		case 2:
			$tmp_img = imagecreatefromjpeg($img);
			break;
		case 3:
			$tmp_img = imagecreatefrompng($img);
			break;
		default:
			$tmp_img = imagecreatefromstring($img);
			break;
	}
	if ($save_prefix)
	{
		$imgpath = substr($img, 0, strrpos($img, '/'));
		$filename = substr($img, strrpos($img, '/')+1);
		$savepath = $imgpath.'/'.$save_prefix.$filename;
	}
	else
	{
		$savepath = $img;
	}
	if(($height >= $imginfo[1] || !$height) && ($width >= $imginfo[0] || !$width))
	{
		if ($save_prefix)
		{
			@copy($img, $savepath) || PWriteFile($savepath, PReadFile($img), 'wb');
			$del && PDel($img);
		}
		return array($savepath, floor($imginfo[1]), floor($imginfo[0]));
	}
	$realscale = $imginfo[1] / $imginfo[0];
	if ($realscale <= 1)
	{
		$width = ($width > $imginfo[0] || !$width) ? $imginfo[0] : $width;
		$height = ($height > $imginfo[1] || !$height) ? $imginfo[1] : $width*$realscale;
	}
	else
	{
		$height = ($height > $imginfo[1] || !$height) ? $imginfo[1] : $height;
		$width = ($width > $imginfo[0] || !$width) ? $imginfo[0] : $height / $realscale;
	}
	$width = floor($width);
	$height = floor($height);
    $dst_image = imagecreatetruecolor($width, $height);
    imagecopyresampled($dst_image, $tmp_img, 0, 0, 0, 0, $width, $height, $imginfo[0], $imginfo[1]);
     switch($imginfo[2])
	{
        case '1':
        	imagegif($dst_image, $savepath);
        	break;
        case '2':
        	imagejpeg($dst_image, $savepath);
        	break;
        case '3':
        	imagepng($dst_image, $savepath);
        	break;
        default :
            imagejpeg($dst_image, $savepath);
            break;
    }
    $save_prefix && $del && PDel($img);
	return array($savepath, $height, $width);
}

/**
 * 编辑器获取函数
 * @param 2维数组 $var array(array('id'=>'xxx','type'='simple','content'='','width'=>'','height'=>''))
 * @return array array[0]:编辑器头部js	array[1]['传递指定id']:编辑器文本框	
 */
function getEditor($var)
{
	global $_PBENV, $editorjs;
	$editor = array();
	$editorjs = '<script type="text/javascript" charset="utf-8" src="'.$_PBENV['PB_URL'].'js/xheditor/xheditor.js"></script>';
	foreach ($var as $v)
	{
		$editor[$v['id']] = '<textarea cols="80" rows="10" id="'.$v['id'].'" name="'.$v['id'].'" style="width: '.(isset($v['width']) && is_int($v['width']) ? $v['width'] : 550).'px;height:'.(isset($v['height']) && is_int($v['height']) ? $v['height'] : 300).'px;">'.rteSafe($v['content']).'</textarea>';
	}
	return $editor;
}

/**
 * returns safe code for preloading in the Editor
 */
function rteSafe($strText)
{
//	$strText = str_replace(chr(145), chr(39), $strText);
//	$strText = str_replace(chr(146), chr(39), $strText);
//	$strText = str_replace("'", "&#39;", $strText);
//	$strText = str_replace(chr(147), chr(34), $strText);
//	$strText = str_replace(chr(148), chr(34), $strText);
//	$strText = str_replace(chr(10), " ", $strText);
//	$strText = str_replace(chr(13), " ", $strText);
	$strText = str_replace('<', '&lt;', $strText);
	$strText = str_replace('>', '&gt;', $strText);
	return $strText;
}

/**
 * ubb代码转html
 */
function ubb_html($str)
{
	$search = array('[strike]', '[/strike]', '[br]','[u]', '[/u]', '[i]', '[/i]', '[b]', '[/b]', '[code]', '[/code]', '[pre]', '[/pre]', '[acronym]', '[/acronym]', '[address]', '[/address]', '[div]', '[/div]', '[p]', '[/p]', '[span]', '[/span]', '[ul]', '[ol]', '[li]', '[/ul]', '[/ol]', '[/li]', '[tr]', '[/tr]', '[td]', '[/td]', '[table]', '[/table]');
	$replace = array('<strike>', '</strike>', '<br />', '<u>', '</u>', '<i>', '</i>', '<b>', '</b>', '<code>', '</code>', '<pre>', '</pre>', '<acronym>', '</acronym>', '<address>', '</address>', '<div>', '</div>', '<p>', '</p>', '<span>', '</span>', '<ul>', '<ol>', '<li>', '</ul>', '</ol>', '</li>', '<tr>', '</tr>', '<td>', '</td>', '<table>', '</table>');
	$str = str_replace($search, $replace, $str);
	$reg_search = array("#\[align=([^\[\<]+?)\](.*?)\[/align]#is",
						"/\[table=(\d{1,4}(px|em|ex|pt|pc|in|mm|cm|%|)?)(,([\(\)%,#\w ]+)?)?\]/ies",
						"#\[td=(\d{1,2}),(\d{1,2})(,(\d{1,3}(px|em|ex|pt|pc|in|mm|cm|%|)?))?\]#is",
						"#\[font=([^\[\<]+?)\](.*?)\[/font\]#is",
						"#\[size=(\d+(\.\d+)?(px|em|ex|pt|pc|in|mm|cm|%|)?)\](.*?)\[/size\]#ies",
						"#\[color=([^\[\<]+?)\](.*?)\[/color\]#is");
	$reg_replace = array("<p style=\"text-align:\\1\">\\2</p>",
						"table_code('\\1','\\4')",
						"<td colspan=\"\\1\" rowspan=\"\\2\" width=\"\\4\">",
						"<span style=\"font-family:\\1\">\\2</span>",
						"font_size('\\1', '\\4', 'content')",
						"<span style=\"color:\\1\">\\2</span>");
	$str = preg_replace($reg_search, $reg_replace, $str);
	if ((strpos($str, '[url=') !== FALSE) && (strpos($str, '[/url]') !== FALSE))
	{
		$str = preg_replace("#\[url=(https?|ftp|gopher|news|telnet|rtsp|mms|callto|ed2k){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]#is", "<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $str);
	}
	if ((strpos($str, '[email=') !== FALSE) && (strpos($str, '[/email]') !== FALSE))
	{
		$str = preg_replace("#\[email=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\](.+?)\[\/email\]#is", "<a href=\"mailto:\\1@\\2\">\\3</a>", $str);
	}
	if ((strpos($str, '[img]') !== FALSE) && (strpos($str, '[/img]') !== FALSE))
	{
		$str = preg_replace("#\[img\]\s*([^\[\<\r\n]+?)\s*\[/img\]#is", "<img src=\"\\1\" />", $str);
	}
	return $str;
}

function table_code($width, $bgcolor)
{
	if (substr($width, -1) == '%')
	{
		$width = (substr($width, 0, -1) < 98) ? (int)$width.'%' : '98%';
	}
	else
	{
		$width = (int)$width;
		$width = ($width <= 560) ? $width : '98%';
	}
	return '<table'.($width ? ' width="'.$width.'" ' : ' ').'align="center" class="t_table"'.($bgcolor ? ' bgcolor="'.$bgcolor.'" ' : '').'>';
}

function font_size($size, $content, $type)
{
	global $pb_tubbsize;
	if (is_numeric($size))
	{
		if ($type == 'content' || $type == 'comment')
		{
			if ($size > $pb_tubbsize || $size < 1) $size = 3;
		}
		else
		{
			$size = 3;
		}
		return '<font size="'.$size.'">'.$content.'</font>';
	}
	else
	{
		return '<span style="font-size:'.$size.'">'.$content.'</span>';
	}
}

/**
 * 返回附件目录
 */
function getUploadDir()
{
	global $pb_attachdir, $timestamp;
	$path = PBDIGG_ATTACHMENT;
	if ($pb_attachdir == '2' && $GLOBALS['cid'])
	{
		$dir = $GLOBALS['cid'];
	}
	elseif ($pb_attachdir == '0')
	{
		$dir = gdate($timestamp, 'ymd');
	}
	else
	{
		$dir = gdate($timestamp, 'ym');
	}
	if (!is_dir($path . $dir))
	{
		if (!mkdir($path . $dir, 0777)) showMsg('attachment_mkdir_failed');
	}
	return $dir;
}

/**
 * 清除文件缓存
 */
function clearcache($tplmark, $cacheid, $show = FALSE)
{
	global $TPL, $pb_visitcache, $cache_dir, $dir;
	if ($pb_visitcache)
	{
		$TPL->caching = TRUE;
		if ($show)
		{
			$showdir = '';
			$cacheid = (string)$cacheid;
			$tidlen = strlen($cacheid);
			$ctid = 0;
			while ($ctid < $tidlen)
			{
				$showdir .= '/'.$cacheid{$ctid};
				$ctid++;
			}
			$TPL->cache_dir = $cache_dir.$dir.$showdir;
		}
		$TPL->clear_cache($tplmark.'.html', $cacheid);
	}
}

function ckgdcode($postcode)
{
	$ckcode = gCookie('pb_ckcode');
	if (!$postcode || !$ckcode) return FALSE;
	list($t, $n) = explode("\t", $ckcode);
	return md5($postcode.$t) == $n;
}

function ckqa($answer)
{
	return $answer == $GLOBALS['pb_checkanswer'];
}

function getTagStyle()
{
    //自定义字体大小
    $sizearray = array('8','9','10','11','12','20');
    $key = array_rand($sizearray);
    return 'font-size:'.$sizearray[$key].'pt;color:#'.dechex(rand(0,255)).dechex(rand(0,196)).dechex(rand(0,255));
}

function getPostVerify($pre, $actionurl, $end)
{
	$params = '';
	if (($pos = strpos($actionurl, '?')) !== FALSE)
	{
		$queryString = substr($actionurl, $pos + 1);
		if ($queryString)
		{
			$params = explode('&', str_replace('&amp;', '&', $queryString));
			foreach ($params as $k => $v)
			{
				if (substr($v, 0, 6) == 'verify')
				{
					unset($params[$k]);
				}
			}
			$params = implode('&amp;', str_replace(array('"', "'", '<', '>'), array('%22', '%27', '%3C', '%3E'), $params)).'&amp;';
		}
		$actionurl = substr($actionurl, 0, $pos);
	}
	return '<form'.stripslashes($pre).'action="'.$actionurl.'?'.$params.'verify='.urlHash($params).'&amp;"'.stripslashes($end).'>';
}
function urlHash($querystring)
{
	global $db_hash, $customer, $_PBENV;
	$urlhash = '';
	if ($querystring)
	{
		$querystring = str_replace('&amp;', '&', $querystring);
		$querystring{strlen($querystring)-1} == '&' && $querystring = substr($querystring, 0, -1);
		$querystring = explode('&', str_replace('=','&',$querystring));
		foreach ($querystring as $p)
		{
			$urlhash .= $p;
		}
		$urlhash = md5($urlhash);
	}
	return substr(md5($urlhash.$db_hash.ipFragment($_PBENV['PB_IP']).$customer['uid']),8, 8);
}
function checkurlHash($verify)
{
	global $db_hash, $customer, $_PBENV;
	$urlhash = '';
	if ($_GET)
	{
		foreach ($_GET as $k => $v)
		{
			$k != 'verify' && $urlhash .= $k.$v;
		}
		$urlhash = md5($urlhash);
	}
	return substr(md5($urlhash.$db_hash.ipFragment($_PBENV['PB_IP']).$customer['uid']),8, 8) == $verify;
}

function checkPostHash($verify)
{
	return $verify == $GLOBALS['verifyhash'];
}

function intConvert($params)
{
	is_array($params) && settype($params, 'array');
	foreach ($params as $v)
	{
		$GLOBALS[$v] = isset($_GET[$v]) ? (int)$_GET[$v] : (isset($_POST[$v]) ? (int)$_POST[$v] : 0);
	}
}
function charConvert($params, $trim = true)
{
	is_array($params) && settype($params, 'array');
	foreach ($params as $v)
	{
		$GLOBALS[$v] = isset($_GET[$v]) ? HConvert($_GET[$v]) : (isset($_POST[$v]) ? HConvert($_POST[$v]) : '');
		$trim && $GLOBALS[$v] = trim($GLOBALS[$v]);
	}
}
function sqlLimit($page, $pagesize = 30)
{
	$page = (int)$page;
	$page <= 0 && $page = 1;
	$pagesize = (int)$pagesize;
	$pagesize <= 0 && $pagesize = 10;
	$GLOBALS['page'] = $page;
	$GLOBALS['pagesize'] = $pagesize;
	return ' LIMIT '.($page - 1) * $pagesize.', '.$pagesize;
}
/**
 * @param numeric/string $var 字符串或者数值
 * @return string 转义后的字符串
 */
function sqlEscape($var)
{
	return " '".(preg_match('~^-?[0-9]+\.?$~', $var) ? (is_int($var) ? (int)$var : (float)$var) : addslashes(stripslashes($var)))."' ";
}
function obStart()
{
	static $gzEnable;
	!isset($gzEnable) && $gzEnable = $GLOBALS['pb_gzip'] && (strpos(strtolower($_SERVER['HTTP_ACCEPT_ENCODING']), 'gzip') !== FALSE) && function_exists('ob_gzhandler') && !@ini_get('zlib.output_compression') && @ini_get('output_handler') != 'ob_gzhandler';
	$gzEnable ? ob_start('ob_gzhandler') : ob_start();
}
function pStrToTime($string)
{
	$string = strtotime($string);
	if ($string === FALSE || $string == -1)	return 0;
	return function_exists('date_default_timezone_set') ? $string - 3600 * $GLOBALS['pb_timezone'] : $string;
}

function userAgent()
{
	$os = $browser = $robot = '';
	$agent = strtolower(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : getenv('HTTP_USER_AGENT'));

	if (preg_match('~bot|crawl|spider|slurp|sohu-search|lycos|robozilla~i', $agent))
	{
		$robot = 1;
		if (strpos($agent, 'googlebot')!==  FALSE)
		{
			$browser = 'Googlebot';
		}
		elseif (strpos($agent, 'msnbot') !== FALSE)
		{
			$browser = 'MSNbot';
		}
		elseif (strpos($agent, 'slurp') !== FALSE)
		{
			$browser = 'Yahoobot';
		}
		elseif (strpos($agent,'baiduspider') !==  FALSE)
		{
			$browser = 'Baiduspider';
		}
		elseif (strpos($agent,'yodaobot') !==  FALSE)
		{
			$browser = 'YodaoBot';
		}
		elseif (strpos($agent,'soso') !==  FALSE)
		{
			$browser = 'SosoImageSpider';
		}
		elseif (strpos($agent,'sohu-search') !==  FALSE)
		{
			$browser = 'Sohubot';
		}
		elseif (strpos($agent, 'lycos') !== FALSE)
		{
			$browser = 'Lycos';
		}
		elseif (strpos($agent, 'robozilla')!==  FALSE)
		{
			$browser = 'Robozilla';
		}
		elseif (strpos($agent, 'sogou')!==  FALSE)
		{
			$browser = 'Sogou';
		}
		else
		{
			$browser = 'unknow';
		}
	}
	elseif (strpos($agent, 'msie') !== FALSE)
	{
		$tmp = explode(';', $agent);
		$browser = trim($tmp[1]);
		$os = trim($tmp[2]);
	}
	elseif (strpos($agent, 'chrome') !== FALSE)
	{
		$tmp = explode(';', $agent);
		$os = trim($tmp[2]);
		preg_match('~chrome/[\d\.]+~i', $tmp[3], $m);
		$browser = $m[0];
	}
	elseif (strpos($agent, 'opera') !== FALSE)
	{
		$tmp = explode('(', $agent);
		if (strpos($tmp[0], 'opera') === FALSE)
		{
			$tmp_one = explode(')', $tmp[1]);
			$browser = trim($tmp_one[1]);
		}
		else
		{
			$browser = trim($tmp[0]);
		}
		$tmp_two = explode(';', $tmp[1]);
		$os = strpos($tmp_two[0], 'windows') === FALSE ? trim($tmp_two[1]) : trim($tmp_two[0]);
	}
	elseif (strpos($agent, 'safari') !== FALSE)
	{
		$tmp = explode('(', $agent);
		$tmp_one = explode(';', $tmp[1]);
		$os = trim(substr($tmp_one[2], 0, strpos($tmp_one[2], ')')));
		$tmp_two = explode(' ', $tmp[2]);
		$browser = trim(array_pop($tmp_two));
	}
	elseif (strpos($agent, 'gecko') !== FALSE)
	{
		$tmp = explode(')', $agent);
		$tmp_one = explode(';', $tmp[0]);
		$tmp_two = explode(' ', $tmp[1]);
		$browser = trim($tmp_two[2]);
		$os = trim($tmp_one[2]);
	}

	return array($os, $browser, $robot);
}

function logRobots($robot)
{
	global $pb_sitehash, $timestamp, $_PBENV;
	PWriteFile(PBDIGG_ROOT.'log/robots/'.substr(md5($pb_sitehash), 6, 6).'_'.gdate($timestamp, 'Ymd').'.txt', $robot."\t".gdate($timestamp, 'Y-m-d H:i:s')."\t".$_PBENV['PB_IP']."\t".$_PBENV['REQUEST_URI']."\n");
}

function pbNewPW($password)
{
	return md5(ipFragment($GLOBALS['_PBENV']['PB_IP']).$GLOBALS['pb_sitehash'].$password);
//	return md5($_SERVER['HTTP_USER_AGENT'].ipFragment($GLOBALS['_PBENV']['PB_IP']).$GLOBALS['pb_sitehash'].$password);
}

function ipFragment($ip)
{
	list($ip1,$ip2,) = explode('.', $ip);
	return $ip1.$ip2;
}

function isPost()
{
	return ($_SERVER['REQUEST_METHOD'] == 'POST' && checkurlHash($GLOBALS['verify']) && (empty($_SERVER['HTTP_REFERER']) || preg_replace("~https?:\/\/([^\:\/]+).*~i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("~([^\:]+).*~", "\\1", $_SERVER['HTTP_HOST']))) ? 1 : 0;
}
function checkPost()
{
	return ($_SERVER['REQUEST_METHOD'] == 'POST' && checkPostHash($GLOBALS['verify']) && (empty($_SERVER['HTTP_REFERER']) || preg_replace("~https?:\/\/([^\:\/]+).*~i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("~([^\:]+).*~", "\\1", $_SERVER['HTTP_HOST']))) ? 1 : 0;
}
function pb_var_export($input)
{
	$output = '';
	if (is_array($input))
	{
		$output .= "array(\r\n";
		foreach ($input as $key => $value)
		{
			$output .= "\t".pb_var_export($key).' => '.pb_var_export($value).",\r\n";
		}
		$output .= ')';
	}
	elseif (is_string($input))
	{
		$output .= "'".str_replace(array('\\','\''),array('\\\\','\\\''),$input)."'";
	}
	elseif (is_int($input) || is_double($input))
	{
		$output .= "'".(string)$input."'";
	}
	elseif (is_bool($input))
	{
		$output .= $input ? 'TRUE' : 'FALSE';
	}
	else
	{
		$output .= 'NULL';
	}
	return $output;
}
function pstrlen($text)
{
	global $db_charset;
	if (function_exists('mb_strlen'))
	{
		return mb_strlen($text, $db_charset);
	}
	elseif (strtolower($db_charset) == 'utf-8')
	{
		return preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $text, $tnum);
	}
	else
	{
		for ($i = 0, $limit = strlen($text), $tnum = 0; $i < $limit; $i++)
		{
			ord($text[$i]) > 127 && ++$i;
			$tnum ++;
		}
		return $tnum;
	}
}

function location($url, $type = 's')
{
	$url = preg_match('~^https?://~i', $url) ? $url : $GLOBALS['_PBENV']['PB_URL'].$url;
	ob_end_clean();
	if ($type == 's')
	{
		header ('Location: '.$url);
		exit;
	}
	else
	{
		exit('<meta http-equiv="refresh" content="0;url='.$url.'">');
	}
}

function forward()
{
	global $_PBENV;
	if (isset($_SERVER['HTTP_REFERER']))
	{
		$parseurl = parse_url($_SERVER['HTTP_REFERER']);
		if (isset($parseurl['host']) && strpos($_PBENV['PB_URL'], $parseurl['host']) !== FALSE && strpos($parseurl['path'], 'login') === FALSE && strpos($parseurl['path'], 'register') === FALSE)
		{
			$forward = $_SERVER['HTTP_REFERER'];
		}
	}
	!isset($forward) && $forward = $_PBENV['PB_URL'];
	return HConvert($forward);
}

function urlconj($url)
{
	return strpos($url, '?') === FALSE ? '?' : ($url{strlen(str_replace('&amp;', '&', $url)) - 1} == '&' ? '' : '&');
}

/**
 * 过滤器函数
 * @param string $dotag 过滤器标识
 * @param $var 过滤内容
 * $_actions = array(
		'reg' => array(
			'myoutput1' => '2',
			'myoutput2' => '2',
			),
		),
	);
 */
function filter_do($dotag, $content = '')
{
	global $_filterhook;
	$args = func_get_args();
	unset($args[0]);

	if (isset($_filterhook['all']))
	{
		$content = call_all_filter($args);
		$args[1] = $content;
	}

	if (isset($_filterhook[$dotag]))
	{
		$currentFilter = &$_filterhook[$dotag];

		foreach ($currentFilter as $hookFunc => $hookArgs)
		{
			if (function_exists($hookFunc))
			{
				$content = call_user_func_array($hookFunc, array_slice($args, 0, (int)$hookArgs));
				$args[1] = $content;
			}
		}
		unset($GLOBALS['_filterhook'][$dotag]);
	}
	return $content;
}

function call_all_filter($args)
{
	global $_filterhook;
	$content = $args[1];
	foreach ($_filterhook['all'] as $hookFunc => $hookArgs)
	{
		function_exists($hookFunc) && $content = call_user_func_array($hookFunc, $args);
	}
	return $content;
}
/**
 * 动作钩子
 * @param string $dotag 过滤器标识
 * $_actionshook = array(
		'reg' => array(
			'函数名' => '传递参数个数',
			),
		),
	);
 */
function action_do($dotag)
{
	global $_actionhook;
	$args = func_get_args();
	unset($args[0]);

	if (isset($_actionhook['all']))
	{
		call_all_action($args);
	}

	if (isset($_actionhook[$dotag]))
	{
		$currentActions = &$_actionhook[$dotag];
		foreach ($currentActions as $hookFunc => $hookArgs)
		{
			function_exists($hookFunc) && call_user_func_array($hookFunc, array_slice($args, 0, (int)$hookArgs));
		}
	}

	unset($GLOBALS['_actionhook'][$dotag]);
}

function call_all_action($args)
{
	global $_actionhook;
	foreach ($_actionhook['all'] as $hookFunc => $hookArgs)
	{
		function_exists($hookFunc) && call_user_func_array($hookFunc, $args);
	}
}
function cutSpilthHtml($html)
{
	$len = strlen($html);
	while ($len >= 0)
	{
		$char = substr($html, --$len, 1);
		if ($char == '>') break;
		if ($char == '<')
		{
			$html = substr($html, 0, $len);
			break;
		}
	}
	return $html;
}
function traceHtml($html, $config = 'summary')
{
	//Copyright (c) 2007-2009 PBDigg.Com

	//空标记
	$emptyElements = array('area','base','basefont','br','col','frame','hr','img','input','isindex','link','meta','param');
	/*** 允许标记 用户可以自行定义 ***/
	//摘要
	$summaryElements = array ('a','abbr','acronym','address','b','big','blockquote','br','center','cite','code','dd','del','div','dl','dt','em','font','h1','h2','h3','h4','h5','h6','hr','i','ins','li','ol','p','pre','small','span','strike','strong','sub','sup','table','tbody','td','tfoot','th','thead','tr','tt','u','ul','img');
	//文章内容
	$contentElements = array('a','abbr','acronym','address','applet','area','b','base','basefont','bdo','big','blockquote','br','button','caption','center','cite','code','col','colgroup','dd','del','dfn','dir','div','dl','dt','em','fieldset','font','form','frame','frameset','h1','h2','h3','h4','h5','h6','hr','i','iframe','img','input','ins','isindex','kbd','label','legend','li','map','menu','noframes','noscript','object','ol','optgroup','option','p','param','pre','q','s','samp','script','select','small','span','strike','strong','sub','sup','table','tbody','td','textarea','tfoot','th','thead','title','tr','tt','u','ul','var');

	$allowedElements = &${$config.'Elements'};

	$html = preg_replace('~<!--(?:.*?)-->~is', '', trim($html));

	$htmlLength = strlen($html);
	$start = 0;
	$stack = $heap = array();
	
	while (true)
	{
		if ($start >= strlen($html)) break;

		$currentChar = $html{$start};

		if ($currentChar == '<')
		{
			$tmp_html = substr($html, $start + 1);
			$gtPosition = strpos($tmp_html, '>');
			$analyzeElement = ltrim(substr($tmp_html, 0, $gtPosition));

			$html_start = substr($html, 0, $start);
			$html_end = substr($html, $start + $gtPosition + 2);

			if ($analyzeElement{0} == '/')
			{
				/**闭合标记分析**/

				//标识
				$realElement = substr($analyzeElement, 1);
				//小写标识
				$realStandardElement = strtolower($realElement);
				//小写转换标识
				$replaceTag = $realElement == $realStandardElement ? FALSE : TRUE;
				//合法标记识别
				$allowedElementskey = array_search($realStandardElement, $allowedElements);
				//空标记识别
				$emptyElementskey = array_search($realStandardElement, $emptyElements);
				//入栈标记
				$stackkey = array_search($realStandardElement, $stack);

				if ($allowedElementskey === FALSE || $emptyElementskey !== FALSE)
				{
					//删除标记
					$html = $html_start . $html_end;
//					$htmlLength -= $gtPosition - 2;
					continue;
				}
				if ($stackkey === FALSE)
				{
					$heap[] = $realStandardElement;
					$start += $gtPosition + 2;
					continue;
				}
				if ($replaceTag)
				{
					//大小写替换处理
					$html = $html_start . "</$realStandardElement>" . $html_end;
				}
				//删除标签
				unset($stack[$stackkey]);
				$start += $gtPosition + 2;
			}
			else
			{
				$whiteSpacePosition = strpos($analyzeElement, ' ');
				$attributes = '';
				if ($whiteSpacePosition)
				{
					$realElement = substr($analyzeElement, 0, $whiteSpacePosition);
					$attributes = rtrim(substr($analyzeElement, $whiteSpacePosition));
				}
				elseif ($analyzeElement == 'br/' || $analyzeElement == 'hr/')
				{
					//修复
					$realElement = substr($analyzeElement, 0, -1);
				}
				else
				{
					$realElement = $analyzeElement;
				}

				//小写标识
				$realStandardElement = strtolower($realElement);
				//小写转换标识
				$replaceTag = $realElement == $realStandardElement ? FALSE : TRUE;
				//合法标记识别
				$allowedElementskey = array_search($realStandardElement, $allowedElements);

				if ($allowedElementskey === FALSE)
				{
					//删除标记
					if (($endKey = strpos($html_end, '</'.$realStandardElement.'>')) !== FALSE)
					{
						$html_end = substr($html_end, $endKey + strlen($realStandardElement) + 3);
					}
					$html = $html_start . $html_end;
					continue;
				}

				//空标记识别
				$emptyElementskey = array_search($realStandardElement, $emptyElements);
				if ($emptyElementskey !== FALSE && (!$attributes || $attributes{strlen($attributes) - 1} != '/'))
				{
					//空标记处理
					$attributes = $attributes.' /';
				}
				$html_middle = $realStandardElement.$attributes;
				$html = $html_start . "<$html_middle>" . $html_end;

				$emptyElementskey === FALSE && $stack[] = $realStandardElement;
				$start += strlen($html_middle) + 2;
			}
		}
		else
		{
			$start++;	
		}
	}
	if ($heap)
	{
		foreach ($heap as $n)
		{
			$html = '<'.$n.'>'.$html;
		}
	}
	if ($stack)
	{
		$stack = array_reverse($stack);
		foreach ($stack as $n)
		{
			$html .= '</'.$n.'>';
		}
	}
	return trim(preg_replace('~<([a-z]+?)><\/\\1>~i', '', str_replace('<a>', '', $html)));
}

function PBOutPut($cacheid = NULL, $compileid = NULL)
{
	global $pb_gzip, $DB;

	if (defined('IN_ADMIN'))
	{
		$output = preg_replace(array('~<!--\d{10}-->\n~','~<form([^>]*?)action="([^\"]+?)"([^>]*?)>~ie'),array('',"getPostVerify('\\1','\\2','\\3')"), ob_get_contents());
		ob_end_clean();
		!defined('MSG') && PB_PAGE != 'login' && PB_PAGE != 'admincp' && $output .= '<script type="text/javascript">document.getElementById("debug").innerHTML = "Processed in ' . spendTime() . ' second(s), ' . $DB->queries . ' queries '.($pb_gzip ? 'Gzip Enabled' : 'Gzip Disabled').'";</script>';
	}
	else
	{
		global $pb_exectime, $pb_rewrite, $pb_timezone, $timestamp, $_PBENV;

		$output = preg_replace('~<!--\d{10}-->\n~', '', ob_get_contents());
		ob_end_clean();
		$pb_rewrite && rewrite($output);
//		PB_PAGE != 'post' && $output .= '<script type="text/javascript" src="http://stat.pbdigg.com/stat.php?h='.base64_encode($GLOBALS['pb_sitehash']."\t".$_PBENV['VERSION']).'"></script>';
		$pb_exectime && $output .= '<script type="text/javascript">document.getElementById("debug").innerHTML = "GMT '.($pb_timezone >= 0 ? '+' : '').$pb_timezone.', '.gdate($timestamp, 'Y-m-d H:i').', Processed in ' . spendTime() . ' second(s), ' . ($DB ? $DB->queries : 0) . ' queries '.($pb_gzip ? 'Gzip Enabled' : 'Gzip Disabled').'";</script>';
	}
	obStart();
	echo $output;
	unset($output);
	exit;
}

function pb_tpl($tplid)
{
	global $transfer;
	return $transfer->getTplVar($tplid);
}
/**
 * @param $face 头像字符串
 * @param $uid 用户ID
 * @param $size 尺寸
 * @param $trueface 真人头像
 */
function userFace($face, $uid, $size = 'middle', $trueface = false)
{
	global $uc_avatar, $uc_url, $pb_avathight, $pb_avatwidth, $_PBENV;
	if ($uc_avatar)
	{
		$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
		$uid = abs(intval($uid));
		$uid = sprintf("%09d", $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		$typeadd = $trueface ? '_real' : '';
		$ucAvatarError = ' onerror="this.onerror=null;this.src=\''.$uc_url.'images/noavatar_'.$size.'.gif\'" ';
		$avatar = $uc_url.'data/avatar/'.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd.'_avatar_'.$size.'.jpg" onerror="this.onerror=null;this.src=\''.$uc_url.'images/noavatar_'.$size.'.gif\'';
	}
	elseif ($face)
	{
		list($faceurl, $type) = explode('|', $face);
		//1:upload 2:url 3:default
		$avatar = ($type == 2 && substr($faceurl,0,7) == 'http://') ? $faceurl : ($_PBENV['PB_URL'].'images/'.($type == 1 ? 'avatars' : 'portrait').'/'.$faceurl);
	}
	else
	{
		$avatar = $_PBENV['PB_URL'].'images/portrait/default.png';
	}
	return $avatar;
}

/**
 * 用户空间地址
 */
function userSpace($uid, $ucuid)
{
	global $uc_space, $uc_spaceurl, $pb_sitedir, $pb_rewrite;
	return ($uc_space && $uc_spaceurl && is_numeric($ucuid)) ? $uc_spaceurl.'space.php?uid='.$ucuid : ($pb_rewrite ? rewriteUser($uid) : $pb_sitedir.'user.php?uid='.$uid);
}

function contentCopyCtrl()
{
	$randstr = '';
	for ($i = 0; $i < 10; $i++)
	{
		$randstr .= chr(mt_rand(0, 126));
	}
	$randstr = str_replace('<', '&lt;', $randstr);
	return '<span style="display:none">'.$randstr.'</span>&nbsp;<br />';
}

function articleShield($lang)
{
	return '<div class="articleshield">'.getSingleLang('common', $lang).'</div>';
}

function pbrank($pbrank, $digg = 0, $bury = 0, $views = 0, $comments = 0, $postdate = 0)
{
	global $timestamp;

	$max = 16777215;
	$day = 86400;

	$septime = $timestamp - $postdate;
	$total = 0;
	$total += $digg * 5;
	$total += $bury * 3;
	$total += $views * 0.1;
	$total += $comments * 2;
	if ($septime <= $day)
	{
		$total *= 1.5;
	}
	elseif ($septime <= 2 * $day)
	{
		$total *= 0.7;
	}
	elseif ($septime <= 3 * $day)
	{
		$total *= 0.5;
	}
	else
	{
		$total = 0;
	}
	$total = ($total <= $max && $total >= 0) ? $total : $pbrank;

	return intval(floor($total));
}

function makeTopicImg($file)
{
	global $timestamp, $pb_topicthumbsize;
	$filename = substr($file, strrpos($file, '/') + 1);
	$topicimginfo = @getimagesize($file);
	$topicimgh = floor($topicimginfo[1]);
	$topicimgw = floor($topicimginfo[0]);
	$topicdir = gdate($timestamp, 'ym');
	if ((is_dir(PBDIGG_ATTACHMENT.'topic/'.$topicdir) || @mkdir(PBDIGG_ATTACHMENT.'topic/'.$topicdir)) && PCopy($file, PBDIGG_ATTACHMENT.'topic/'.$topicdir.'/'.$filename))
	{
		list ($_height, $_width) = explode("\t", $pb_topicthumbsize);
		$_imginfo = thumb(PBDIGG_ATTACHMENT.'topic/'.$topicdir.'/'.$filename, $_height, $_width, '');
		$topicimgh = $_imginfo[1];
		$topicimgw = $_imginfo[2];
		return $topicdir.'/'.$filename.'|'.$topicimgh.'|'.$topicimgw.'|0';
	}
	return '';			
}

function getSiteContent($url, $timeout = 10)
{
	$content = '';
	if (function_exists('curl_init'))
	{
		ob_start();
		$ch = curl_init($url);
		curl_setopt ($ch, CURLOPT_REFERER, $url);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_exec($ch);
		curl_close($ch);
		$content = ob_get_contents();
		ob_end_clean();
		return $content ? $content : false;
	}
	else
	{
		require_once PBDIGG_ROOT.'include/Snoopy.class.php';
		$snoopy = new Snoopy();
		$snoopy->read_timeout = $timeout;
		$snoopy->fetch($url);
		return $snoopy->error ? '' : $snoopy->results;
	}
}
function replace_tag($tag, $content)
{
	global $_tagkeywords;

	foreach ($_tagkeywords as $k => $v)
	{
		if ($v && strpos($content, $v) !== FALSE)
		{
			if (!preg_match('~&[a-z]*?'.preg_quote($v, '~').'[a-z]*?;~i', $content))
			{
				$content = preg_replace('~('.preg_quote($v, '~').')~ie', "replace_single_tag('\\1')", $content);
			}
		}
	}
	return $tag.str_replace('\\"', '"', $content);
}
function replace_single_tag($tag)
{
	global $_tagkeywords, $pb_sitedir;
	if (in_array($tag, $_tagkeywords))
	{
		$key = array_keys($_tagkeywords, $tag);
		if ($key) unset($_tagkeywords[$key[0]]);
		return "<a href=\"{$pb_sitedir}index.php?tag=".rawurlencode($tag)."\"><span style=\"text-decoration:underline;\">{$tag}</span></a>";
	}
	return $tag;
}

function ubb_parse($content)
{
	$content = preg_replace('~\[flash\s*(?:=\s*(\d+)\s*,\s*(\d+)\s*)?\]([\s\S]+?)\[\/flash\]~ie',"flash_parse('\\1','\\2','\\3')", $content);
	$content = preg_replace('~\[media\s*(?:=\s*(\d+)\s*,\s*(\d+)\s*)?\]([\s\S]+?)\[\/media\]~ie',"media_parse('\\1','\\2','\\3')", $content);
	return $content;
}
function ubb_deparse($content)
{
	$content = preg_replace('~<embed((?:\s+[^>]+)?(?:\s+type="application/x-shockwave-flash")[^>]*?)/?>~ie', "media_deparse('\\1')", $content);
	$content = preg_replace('~<embed((?:\s+[^>]+)?(?:\s+type="application/x-mplayer2")[^>]*?)/?>~ie', "media_deparse('\\1','media')", $content);
	return $content;
}
function flash_parse($h, $w, $url)
{
	(!$w || !is_numeric($w)) && $w = 550;
	(!$h || !is_numeric($h)) && $h = 400;
	return '<embed type="application/x-shockwave-flash" src="'.$url.'" wmode="opaque" quality="high" bgcolor="#ffffff" menu="false" play="false" loop="false" width="'.$w.'" height="'.$h.'"/>';
}
function media_parse($h, $w, $url)
{
	(!$w || !is_numeric($w)) && $w = 550;
	(!$h || !is_numeric($h)) && $h = 400;
	return '<embed type="application/x-mplayer2" src="'.$url.'" enablecontextmenu="false" autostart="false" width="'.$w.'" height="'.$h.'"/>';
}
function media_deparse($all, $tag = 'flash')
{
	$all = str_replace('\"', '"', $all);
	if (preg_match('~src\s*=["|\']([^"|\']+)["|\']~i', $all, $m))
	{
		$url = $m[1];
	}
	else
	{
		return '';
	}
	$w = preg_match('~width\s*=["|\'](\d+)["|\']~i', $all, $m) ? $m[1] : 550;
	$h = preg_match('~height\s*=["|\'](\d+)["|\']~i', $all, $m) ? $m[1] : 400;
	return "[{$tag}={$h},{$w}]{$url}[/{$tag}]";
}

require_once PBDIGG_ROOT.'include/usr.func.php';

?>
