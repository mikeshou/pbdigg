<?php
/**
 * @version $Id: mysql.class.php v2.1 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

class MySQL
{
	var $dblink = null;
	var $resultType = MYSQL_ASSOC;
	var $queries = 0;

	function MySQL($db_host, $db_username, $db_password, $db_name, $db_pconnect = 0)
	{
		global $db_charset;

		$this->dblink = $db_pconnect ? @ mysql_pconnect($db_host, $db_username, $db_password) : @ mysql_connect($db_host, $db_username, $db_password);
		!$this->dblink && $this->db_halt();
		$db_version = mysql_get_server_info($this->dblink);
		if ($db_version > '4.1')
		{
			$realdb_charset = $db_charset = strtolower($db_charset);
			if ($db_charset && in_array($db_charset, array ('gbk', 'big5', 'utf-8')))
			{
				if (strpos($db_charset, '-') !== FALSE)
				{
					$realdb_charset = str_replace('-', '', $db_charset);
				}
				mysql_query("SET character_set_client = 'binary', character_set_connection = '".$realdb_charset."', character_set_results = '".$realdb_charset."'", $this->dblink);
			}
		}
		if ($db_version > '5.0.1')
		{
			mysql_query("SET sql_mode = ''", $this->dblink);
		}
		$db_name && !@mysql_select_db($db_name, $this->dblink) && $this->db_halt();
	}

	/**
	 * 执行查询语句
	 * 
	 * @return obj 资源
	 */
	function db_query($sql)
	{
		$query = mysql_query($sql, $this->dblink);
		$this->queries ++;
		!$query && $this->db_halt($sql);
		return $query;
	}
	/**
	 * 执行修改语句
	 * 
	 * @return int 影响行数
	 */
	function db_exec($sql, $lowp = TRUE)
	{
		if ($lowp)
		{
			$offset = strpos($sql, ' ');
			$sql = substr($sql, 0, $offset).' LOW_PRIORITY'.substr($sql, $offset);
		}
		$query = function_exists('mysql_unbuffered_query') ? mysql_unbuffered_query($sql, $this->dblink) : mysql_query($sql, $this->dblink);
		$this->queries ++;
		!$query && $this->db_halt($sql);
		return mysql_affected_rows($this->dblink);
	}

	/**
	 * 取得关联数据集
	 * 
	 * @param resource $query
	 */
	function fetch_all($query)
	{
		return mysql_fetch_array($query, $this->resultType);
	}

	/**
	 * 取得一行记录
	 * 
	 * @param string $sql
	 * @param array $data
	 */
	function fetch_one($sql)
	{
		return $this->fetch_all($this->db_query($sql));
	}
	
	/**
	 * 取得第一条记录
	 */
	function fetch_first($sql)
	{
		$tmp = &mysql_fetch_array($this->db_query($sql), MYSQL_NUM);
		return isset($tmp[0]) ? $tmp[0] : '';
	}
	/**
	 * 取得数字数据集
	 */
	function fetch_row($query)
	{
		return mysql_fetch_row($query);
	}
	/**
	 * 取得select语句查询结果的数目
	 */
	function db_num($query)
	{
		return mysql_num_rows($query);
	}

	/**
	 * 取得 INSERT，UPDATE，DELETE 语句查询结果的数目
	 */
	function db_affected_rows()
	{
		return mysql_affected_rows($this->dblink);
	}

	/**
	 * 取得结果集中字段的数目
	 */
	function db_field_num($query)
	{
		return mysql_num_fields($query);
	}

	/**
	 * 取得上一步 INSERT 操作产生的 ID
	 */
	function db_insert_id()
	{
		return mysql_insert_id($this->dblink);
	}

	/**
	 * 释放资源
	 */
	function db_free($query)
	{
		return mysql_free_result($query);
	}

	/**
	 * 关闭连接
	 */
	function db_close()
	{
		return mysql_close($this->dblink);
	}

	/**
	 * 数据库版本
	 */
	function db_version()
	{
		return mysql_get_server_info($this->dblink);
	}

	/**
	 * 返回 MySQL 操作产生的文本错误信息
	 */
	function db_error_msg()
	{
		return is_object($this->dblink) ? mysql_error($this->dblink) : mysql_error();
	}

	/**
	 * 返回 MySQL 操作中的错误信息的数字编码
	 */
	function db_error_no()
	{
		return is_object($this->dblink) ? mysql_errno($this->dblink) : mysql_errno();
	}

	/**
	 * 返回查询次数
	 */
	function getQueries()
	{
		return $this->queries;
	}
	
	function setResultType($type)
	{
		$this->resultType = in_array($type, array(MYSQL_ASSOC,MYSQL_NUM,MYSQL_BOTH)) ? $type : MYSQL_ASSOC;
	}
	/**
	 * 数据库错误提示显示页面
	 */
	function db_halt($sql = '')
	{
		global $pb_logdebug, $pb_debug, $db_charset, $timestamp, $_PBENV;
		$pb_logdebug && $this->logDebugFile($sql);
		require_once PBDIGG_ROOT.'include/debug.inc.php';
	}

	/**
	 * 记录数据库错误到调试文件
	 */
	function logDebugFile($sql = '')
	{
		global $timestamp, $_PBENV;
		$queryString = $suffix = $path = $content = '';
		$path = PBDIGG_ROOT.'log/db/DB_'.date('Ymd', $timestamp).'.php';
		$content .= "<?php exit('Access Denied');?>";
		$content .= "\r\nTime：".date('Y-m-d @ H:i', $timestamp);
		$content .= "\r\nScript：http://".$_SERVER['HTTP_HOST'].$_PBENV['REQUEST_URI'];
		$content .= "\r\nDescription：".$this->db_error_msg();
		$content .= "\r\nError Number：".$this->db_error_no();
		$content .= "\r\nError SQL:".$sql;
		$content .= "\r\n======== PBDigg End ========\r\n";
		PWriteFile($path, $content);
	}
}


?>