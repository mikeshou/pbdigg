<?php
/**
 * @version $Id: ptemplate.php v2.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

function pt_fetch($tpl_name)
{
	global $tpl_dir;

	(strpos($tpl_name, '..') !== FALSE || strpos($tpl_dir, '..') !== FALSE) && exit('Access Denied!');
	$current_tplfile = PBDIGG_ROOT.'templates/'.$tpl_dir.'/'.$tpl_name.'.html';
	$default_tplfile = PBDIGG_ROOT.'templates/pbdigg/'.$tpl_name.'.html';
	$_tpldir = file_exists($current_tplfile) ? $tpl_dir : (file_exists($default_tplfile) ? 'pbdigg' : '');
	!$_tpldir && exit('Template File '.$tpl_name.'.html Not Found!');
	$tpl_file = PBDIGG_ROOT.'templates/'.$_tpldir.'/'.$tpl_name.'.html';
	$compile_filename = '%%'.sprintf('%08X', crc32($_tpldir.$tpl_name)).'%%'.$tpl_name.'.php';
	$compile_file = PBDIGG_ROOT.'compile/'.$compile_filename;
	if (file_exists($compile_file))
	{
		$fp = @fopen($compile_file, 'rb');
		$tpl_mtime = fgets($fp);
		if (substr($tpl_mtime, 4, 10) >= filemtime($tpl_file))
		{
			return $compile_file;
		}
	}
	return pt_compile($tpl_name, $tpl_file, $compile_filename);
}

function pt_plugin_fetch($tpl_name, $mark, $style = 'default')
{
	(strpos($tpl_name, '..') !== FALSE || strpos($mark, '..') !== FALSE || strpos($style, '..') !== FALSE) && exit('Access Denied!');
	$tpl_file = PBDIGG_ROOT.'plugins/'.$mark.'/templates/'.$style.'/'.$tpl_name.'.html';
	!file_exists($tpl_file) && exit('Template File '.$tpl_name.'.html Not Found!');
	$compile_filename = '%%'.sprintf('%08X', crc32($tpl_name.$mark.$style)).'%%'.$tpl_name.'.php';
	$compile_file = PBDIGG_ROOT.'compile/'.$compile_filename;
	if (file_exists($compile_file))
	{
		$fp = @fopen($compile_file, 'rb');
		$tpl_mtime = fgets($fp);
		if (substr($tpl_mtime, 4, 10) >= filemtime($tpl_file))
		{
			return $compile_file;
		}
	}
	return pt_compile($tpl_name, $tpl_file, $compile_filename);
}

function pt_compile($tpl_name, $tpl_file, $compile_filename)
{
	global $timestamp;
	if (!$fp = @fopen($tpl_file, 'rb'))
	{
		exit('Can\'t Read Template File '.$tpl_name.'.html!');
	}
	$tpl = @fread($fp, filesize($tpl_file));
	fclose($fp);
	$tpl = preg_replace(array('~<[\?%]=?.*?[\?%]>~s','~<script\s+language\s*=\s*[\"\']?php[\"\']?\s*>.*?</script\s*>~s','~<\?php(?:\r\n?|[ \n\t]).*?\?>~s'), '', $tpl);

    preg_match_all('~{#include ([a-z0-9{\$}\_\./]+)#}~i', $tpl, $matches);
	$include_blocks = $matches[1];
	$tpl = preg_replace('~{#include ([a-z0-9\_\./]+)#}~i', '{#include#}', $tpl);
	
	preg_match_all('~{#includephp ([a-z0-9\_\./]+)#}~i', $tpl, $matches);
	$includephp_blocks = $matches[1];
	$tpl = preg_replace('~{#includephp ([a-z0-9\_\./]+)#}~i', '{#includephp#}', $tpl);


	preg_match_all('~{#([_\/\d\$a-z]+)?\x20?(.*?)?#}~is', $tpl, $template_tags, PREG_SET_ORDER);
    $text_blocks = preg_split('~{#(.*?)#}~s', $tpl);

    $compiled_tags = array();

    foreach ($template_tags as $k => $v)
    {
		switch ($v[1]{0})
		{
			case '$':
				$compiled_tags[] = '<?php echo '.$v[1].($v[2] ? preg_replace('~\[\'\$([a-z0-9_]+?)\'\]~is','[$\\1]',str_replace(array('[',']'),array('[\'','\']'),$v[2])) : '').';?>';
				break;
			case '/':
				$compiled_tags[] = '<?php } ?>';
				break;
			default:
				switch ($v[1])
				{
					case 'include':
	                	$compiled_tags[] = "<?php include(pt_fetch(".pt_include(array_shift($include_blocks))."));?>";
	                	break;
	                case 'includephp':
	                	$compiled_tags[] = "<?php include('".array_shift($includephp_blocks)."');?>";
	                	break;
					case 'foreach':
						$compiled_tags[] = "<?php foreach(".$v[2]."){?>";
						break;
					case 'if':
						$compiled_tags[] = "<?php if(".$v[2]."){?>";
						break;
					case 'else':
						$compiled_tags[] = "<?php }else{?>";
						break;
					case 'elseif':
						$compiled_tags[] = "<?php }elseif(".$v[2]."){?>";
						break;
					case 'func':
						$compiled_tags[] = "<?php ".pt_func($v[2]).";?>";
						break;
					case 'tpl':
						$compiled_tags[] = "<?php echo \$transfer->getTplVar('".$v[2]."');?>";
						break;
					default:
						$compile_blocks[] = '';
						break;
				}
				break;
		}
    }
    $compiled_content = '';
	for ($i = 0, $size = count($text_blocks); $i < $size; $i++)
	{
//		if ($compiled_tags[$i] == '')
//        {
//            $text_blocks[$i+1] = preg_replace('~^(\r\n|\r|\n|\r\n)~', '', $text_blocks[$i+1]);
//        }
        $compiled_content .= $text_blocks[$i] . (isset($compiled_tags[$i]) ? $compiled_tags[$i] : '');	
	}
	return pt_write(str_replace('?><?php', '', "<!--".$timestamp."-->\n<?php !defined('IN_PBDIGG') && exit('Access Denied!');?>".$compiled_content), $compile_filename);
}

function pt_include($str)
{
	if (preg_match_all('~{(\$[a-z0-9_]+)}~i', $str, $m))
	{         		
		$splite = preg_split('~{\$[a-z0-9_]+}~i', $str);
		$str = '';
		for ($i = 0, $size = count($splite); $i < $size; $i ++)
		{
			$str .= $splite[$i].(isset($m[1][$i]) ? ($splite[$i] ? "'." : '').$m[1][$i].($splite[$i+1] ? ".'" : '') : ($splite[$i] ? "'" : ''));
		}
		$str = ($str{0} == '$' ? '' : "'").$str;
	}
	else
	{
		$str = "'$str'";
	}
	return $str;
}

function pt_func($func)
{
	!preg_match('~^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\((?:(\'|\").*\\1|\d*|,|\s)*\)$~i', $func) && exit('Dangerous Function In Template File!');
	$pos = strpos($func, '(');
	$funcname = strtolower(trim(substr($func, 0, $pos)));
	$parameter = substr($func, $pos);
	$functionList = array('phpinfo','passthru','exec','system','chroot','scandir','chgrp','chown','shell_exec','proc_open','proc_get_status','error_log','ini_alter','ini_set','ini_restore','dl','pfsockopen','syslog','readlink','symlink','popen','stream_socket_server','putenv','call_user_func','call_user_func_array','call_user_method','call_user_method_array');
	if (!preg_match('~^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$~i', $funcname) || in_array($funcname, $functionList))
	{
		exit('Dangerous Function In Template File!');
	}
	return $funcname.$parameter;
}

function pt_write($data, $compile_filename)
{
	$filename = PBDIGG_ROOT.'compile/'.$compile_filename;
	if ($fp = @fopen($filename, 'wb'))
	{
		flock($fp, LOCK_EX);
		fwrite($fp, $data);
		fclose($fp);
		chmod($filename, 0666);
	}
	return $filename;
}
?>