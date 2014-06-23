<?php
/**
 * @version $Id: Upload.class.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

class Upload
{
	/**
	 * 上传文件
	 */
	var $_files = array();
	/**
	 * 上传文件数量
	 */
	var $_count = 0;
	
	var $_flash = false;

	/**
	 * 构造函数
	 */
	function Upload($flash = false)
	{
		if (is_array($_FILES))
		{
			foreach ($_FILES as $field => $struct)
			{
				if (isset ($struct['error']) && ($struct['error'] === UPLOAD_ERR_OK) && ($struct['tmp_name'] != 'none') && (is_uploaded_file($struct['tmp_name']) || is_uploaded_file(str_replace('\\\\', '\\', $struct['tmp_name']))))
				{
					$struct['ext'] = Fext($struct['name']);
					$this->_files[$field] = $struct;
				}
			}
			$this->_count = count($this->_files);
		}
		$this->_flash = (boolean)$flash;
	}
	/**
	 * 返回文件对象
	 */
	function getFiles()
	{
		return $this->_files;
	}
	/**
	 * 取得上传文件数量
	 */
	function getCount()
	{
		return $this->_count;
	}

	/**
	 * 返回上传文件属性
	 */
	function getAttribut($item, $att)
	{
		return isset($this->_files[$item][$att]) ? $this->_files[$item][$att] : NULL;
	}
	/**
	 * 设置上传文件属性
	 */
	function setAttribut($item, $att, $value)
	{
		if (isset($this->_files[$item]))
		{
			$this->_files[$item][$att] = $value;
		}
	}
	/**
	 * 返回浏览器提供的文件类型
	 */
	function getMimeType($fileext)
	{
		switch ($fileext)
		{
			case 'pdf' :
				$mimetype = 'application/pdf';
				break;
			case 'rar' :
			case 'zip' :
				$mimetype = 'application/zip';
				break;
			case 'doc' :
				$mimetype = 'application/msword';
				break;
			case 'xls' :
				$mimetype = 'application/vnd.ms-excel';
				break;
			case 'ppt' :
				$mimetype = 'application/vnd.ms-powerpoint';
				break;
			case 'gif' :
				$mimetype = 'image/gif';
				break;
			case 'png' :
				$mimetype = 'image/png';
				break;
			case 'jpeg' :
			case 'jpg' :
				$mimetype = 'image/jpeg';
				break;
			case 'wav' :
				$mimetype = 'audio/x-wav';
				break;
			case 'mpeg' :
			case 'mpg' :
			case 'mpe' :
				$mimetype = 'video/x-mpeg';
				break;
			case 'mov' :
				$mimetype = 'video/quicktime';
				break;
			case 'avi' :
				$mimetype = 'video/x-msvideo';
				break;
			case 'txt' :
				$mimetype = 'text/plain';
				break;
			default :
				$mimetype = 'application/octet-stream';
		}
		return $mimetype;
	}
	/**
	 * 返回上传是否为图片
	 */
	function isImg($item)
	{
		return strpos(strtolower($item['type']), 'image') === FALSE ? 0 : 1;
	}
	/**
	 * 生产唯一文件名
	 */
	function makeFileName($item)
	{
		return preg_replace("~(php|phtml|php3|php4|jsp|exe|dll|asp|cer|asa|shtml|shtm|aspx|asax|cgi|fcgi|pl)~i", "_\\1_", md5($item['name'] . $item['size'] . $item['tmp_name'] . time()));
	}
	/**
	 * 检查上传文件类型和大小是否符合要求
	 * 
	 * @param $type Array 上传文件类型数组
	 * @param Int $maxSize 文件最大尺寸，单位字节
	 */
	function checkFile($item, $type = array(), $maxSize = 0)
	{
		if (is_array($type))
		{
			$ck = FALSE;
			foreach ($type as $ext)
			{
				if ($item['ext'] == strtolower($ext))
				{
					$ck = TRUE;
					break;
				}
			}
			if (!$ck)
			{
				$this->error('attachment_ext_notallowed');
			}
		}
		if ((int)$item['size'] < 1 || ($maxSize && $item['size'] > $maxSize))
		{
			$this->error('attachment_size_invalid');
		}
	}
	
	function error($tip)
	{
		if ($this->_flash)
		{
			uploaderror();
		}
		else
		{
			showMsg($tip);
		}
	}
	
	/**
	 * 移动文件
	 * 
	 * @param String $type 附件类型
	 * @param String $filename 自定义文件名称
	 */
	function moveFile($type = 'attachment', $filename = '', $allowedExt = array(), $maxSize = 0)
	{
		global $pb_attachdir, $pb_contentthumb, $pb_contentthumbsize, $pb_topicthumb, $pb_topicthumbsize, $pb_watertype, $timestamp;
		static $WM;

		$imgext  = array('jpg', 'jpeg', 'gif', 'png', 'bmp');
		$_movedfile = $_imginfo = array();
		($allowedExt || $maxSize) && $check = true;

		switch ($type)
		{
			case 'attachment':
//				$_attpath = PBDIGG_ATTACHMENT.($pb_attachdir == 2 && $GLOBALS['cid']) ? $GLOBALS['cid'] : gdate($timestamp, ($pb_attachdir == 1) ? 'ymd' : 'ym').'/';
				$_attpath = PBDIGG_ATTACHMENT.'temp/';
				break;
			case 'avatar':
				$_attpath = PBDIGG_ROOT.'images/avatars/'.str_pad(substr($GLOBALS['m_uid'], -2),2,'0',STR_PAD_LEFT).'/';
				break;
			case 'topic':
				$_attpath = PBDIGG_ATTACHMENT.'topic/'.(PB_PAGE == 'tag' ? '' : gdate($timestamp, 'ym').'/');
				break;
			case 'commend':
				$_attpath = PBDIGG_ATTACHMENT.'temp/';
				break;
			case 'cate':
				$_attpath = PBDIGG_ROOT.'images/cate/';
				break;
			default:
				$_attpath = PBDIGG_ATTACHMENT.'temp/';
				break;
		}
		if (!is_dir($_attpath))
		{
			if (!@mkdir($_attpath, 0777) || !@fclose(@fopen($_attpath.'index.html', 'w'))) $this->error('attachment_mkdir_failed');
		}
		foreach ($this->_files as $k => $v)
		{
			$check && $this->checkFile($v, $allowedExt, $maxSize);
			$moved = $_imginfo = '';
			!$filename && $filename = $this->makeFileName($v);
//			if ($type == 'topic' && !$GLOBALS['topicupdate'] && file_exists($_attpath.$filename.'.'.$v['ext']))
//			{
//				$filename .= '_'.$GLOBALS['timestamp'];
//			}
			
			$real_path = $_attpath.$filename.'.'.$v['ext'];
			if (move_uploaded_file($v['tmp_name'], $real_path) || @copy($v['tmp_name'], $real_path) || PWriteFile($real_path, PReadFile($v['tmp_name']), 'wb'))
			{
				$moved = true;
				PDel($v['tmp_name']);
			}
//			($this->isImg($v) && in_array($v['ext'], $imgext)) 
			$isimg = isImg($real_path) ? 1 : 0;
			if ($moved)
			{
				@chmod($real_path, 0644);
				if ($isimg && function_exists('getimagesize') && !($_imginfo = getimagesize($real_path)))
				{
					PDel($real_path);
					$this->error('attachment_illegal_image');
				}
				if ($isimg)
				{
					if ($type == 'attachment' && $pb_contentthumb)
					{
						list ($_height, $_width) = explode("\t", $pb_contentthumbsize);
						$_imginfo = thumb($real_path, $_height, $_width);
						$_thumb = 1;
					}
					elseif ($type == 'topic' && $pb_topicthumb)
					{
						list ($_height, $_width) = explode("\t", $pb_topicthumbsize);
						$_imginfo = thumb($real_path, $_height, $_width, '');
						$_thumb = 1;
					}
					if ($type == 'attachment' && $pb_watertype)
					{
						require_once PBDIGG_ROOT.'include/Watermark.class.php';
						!isset($WM) && $WM = new Watermark();
						$WM->setImg($real_path);
						$_thumb && $WM->setImg($_imginfo[0]);
					}
				}
				$_movedfile[] = array($real_path, $filename, $v['ext'], $v['size'], $this->getMimeType($v['ext']), (isset($_thumb) ? 1 : 0), $isimg, $_imginfo, HConvert($v['name']));
			}
			else
			{
				$this->error('attachment_save_error');
			}
		}
//		var_dump($_movedfile);
		return $_movedfile;
	}
}
?>
