<?php
/**
 * @version $Id: tpl.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

(!defined('IN_ADMIN') || !isset($_PBENV['PHP_SELF']) || !preg_match('/[\/\\\\]admincp\.php$/i', $_PBENV['PHP_SELF'])) && exit('Access Denied');

define('PB_PAGE', 'tpl');

switch ($job)
{
	case 'view':
		require_once PBDIGG_ROOT.'include/PDirectory.class.php';
		!$directory && $directory = '';
		$directory && !preg_match('~^[/a-z0-9_]+$~i', $directory) && showMsg('admin_illegal_request');
		$DIR = PDirectory::getInstance();
		$DIR->setRoot(PBDIGG_ROOT.'templates');
		$DIR->setCDir($directory);
		$icon = array(
			'dir' => array('dir'),
			'html' => array('html', 'htm', 'shtml', 'tpl'),
			'php' => array('php', 'php3'),
			'img' => array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'psd'),
			'doc' => array('doc'),
			'xls' => array('xls'),
			'txt' => array('txt'),
			'js' => array('js'),
			'xml' => array('xml'),
			'css' => array('css'),
			'rm' => array('rm', 'rmvb'),
			'wmv' => array('wmv', 'wma', 'avi'),
			'zip' => array('zip', 'rar', 'gz', 'tar')
		);
		$DIR->setIcon(PBDIGG_ROOT.'images/file/', $icon, 'gif');
		$DIR->listDir();
		$DIR->upDir();

		$dirs = $DIR->getDirs();
		$files = $DIR->getFiles();
		$updir = $DIR->getUpDir();
		break;

	case 'del':

		!$directory && $directory = '';
		if (($directory && !preg_match('~^[/a-z0-9_]+$~i', $directory)) || !preg_match('~^[a-z0-9_\x7f-\xff]+\.(html|htm|gif|png|bmp|jpg|jpeg|css|js)$~i', $filename)) showMsg('tpl_file_operate_refuse');
		if (isPost())
		{
			PDel(PBDIGG_ROOT.'templates'.$directory.'/'.$filename);
			redirect('tpl_file_del_success', 'admincp.php?action=tpl&job=view&directory='.$directory);
		}
		break;

	case 'deldir':

		!$directory && $directory = '';
		if ($directory && !preg_match('~^[/a-z0-9_]+$~i', $directory)) showMsg('tpl_file_operate_refuse');
		if (isPost())
		{
			emptyDir(PBDIGG_ROOT.'templates'.$directory);
			redirect('tpl_dir_del_success', 'admincp.php?action=tpl&job=view');
		}
		break;

	case 'rename':
		!$directory && $directory = '';
		!$filename && $filename = '';
		if (($directory && !preg_match('~^[/a-z0-9_]+$~i', $directory)) || ($filename && !preg_match('~^[a-z0-9_\x7f-\xff]+\.(html|htm|gif|png|bmp|jpg|jpeg|css|js)$~i', $filename))) showMsg('tpl_file_operate_refuse');
		$filepath = PBDIGG_ROOT.'templates'.$directory.'/'.$filename;

		if (isPost())
		{
			if ($filename)
			{
				$reg = '~^[a-z0-9_\x7f-\xff]+\.(html|htm|gif|png|bmp|jpg|jpeg|css|js)$~i';
			}
			else
			{
				$reg = '~^[/a-z0-9_]+$~i';
				$directory = substr($directory, 0, strrpos($directory, '/'));
			}

			!preg_match($reg, $newfilename) && showMsg('tpl_file_operate_refuse');
			$newfilepath = PBDIGG_ROOT.'templates'.$directory.'/'.$newfilename;

			if (@rename($filepath, $newfilepath))
			{
				redirect('tpl_rename_success', 'admincp.php?action=tpl&job=view&directory='.$directory);
			}
			else
			{
				showMsg('tpl_rename_failed');
			}
		}
		else
		{
			if (is_dir($filepath))
			{
				$pos = strrpos($directory, '/');
				$oldfilename = substr($directory, $pos + 1);
				$olddirectory = substr($directory, 0, $pos);
			}
			elseif (is_file($filepath))
			{
				$oldfilename = $filename;
				$olddirectory = $directory;
			}
			else
			{
				showMsg('tpl_file_noexist');
			}
		}
		break;

	case 'edit':
		!$directory && $directory = '';
		if (($directory && !preg_match('~^[/a-z0-9_]+$~i', $directory)) || !preg_match('~^[a-z0-9_\x7f-\xff]+\.(html|htm|css|js)$~i', $filename)) showMsg('tpl_file_operate_refuse');
		$filepath = PBDIGG_ROOT.'templates'.$directory.'/'.$filename;
		!file_exists($filepath) && showMsg('tpl_file_noexist');
//		$template = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;', '&#', '<', '>'), array('&amp;amp;', '&amp;quot;', '&amp;lt;', '&amp;gt;', '&amp;#', '&lt;', '&gt;'), file_get_contents($filepath));
		if (isPost())
		{
			if (PWriteFile($filepath, str_replace(array('\\"', '\\\'', '\\\\', '&lt;', '&gt;'), array('"', '\'', '\\', '<', '>'), preg_replace('~<\?php(?:\r\n?|[ \n\t]).*?\?>~s','', $template)), 'wb'))
			{
				redirect('tpl_mod_success', 'admincp.php?action=tpl&job=view&directory='.$directory);
			}
			else
			{
				showMsg('tpl_mod_failed');
			}
		}
		$template = str_replace(array('<', '>'), array('&lt;', '&gt;'), preg_replace(array('~<\?php(?:\r\n?|[ \n\t]).*?\?>~s', '~&(#[0-9]+|[a-z]+);~is'), array('', '&amp;\\1;'), file_get_contents($filepath)));
		break;

	case 'addfile':
		!$directory && $directory = '';
		if ($directory && !preg_match('~^[/a-z0-9_]+$~i', $directory)) showMsg('tpl_file_operate_refuse');

		if (isPost())
		{
			!preg_match('~^[a-z0-9_\x7f-\xff]+\.(html|htm|css|js)$~i', $filename) && showMsg('tpl_file_operate_refuse');
			$filepath = PBDIGG_ROOT.'templates'.$directory.'/'.$filename;
			file_exists($filepath) && showMsg('tpl_file_exist');
			if (PWriteFile($filepath, str_replace(array('\\"', '\\\'', '\\\\'), array('"', '\'', '\\'), preg_replace('~<\?php(?:\r\n?|[ \n\t]).*?\?>~s','', $template)), 'wb'))
			{
				redirect('tpl_file_add_success', 'admincp.php?action=tpl&job=view&directory='.$directory);
			}
			else
			{
				showMsg('tpl_file_add_failed');
			}
		}
		break;

	case 'adddir':
		!$directory && $directory = '';
		if ($directory && !preg_match('~^[/a-z0-9_]+$~i', $directory)) showMsg('admin_illegal_request');
		$dirpath = PBDIGG_ROOT.'templates'.$directory;

		!pwritable($dirpath) && showMsg('tpl_write_nopermission');

		if (isPost())
		{
			!preg_match('~^[a-z0-9_\x7f-\xff]+$~i', $dirname) && showMsg('tpl_illegal_dirname');
			is_dir($dirpath.'/'.$dirname) && showMsg('tpl_dir_exist');
			if (@mkdir($dirpath.'/'.$dirname, 0666))
			{
				redirect('tpl_dir_make_success', 'admincp.php?action=tpl&job=view&directory='.$directory);
			}
			else
			{
				showMsg('tpl_dir_make_failed');
			}
		}
		break;
	
	case 'guide':
		//模板调用

		require_once PBDIGG_ROOT.'include/module.class.php';
		$module = new module();

		$tplcids = $tplmids = $guide = $trantattribute = array();
		$guide['cachetime'] = 600;
		$guide['trantnum'] = 10;
		foreach ($_categories as $k => $v)
		{
			$tplcids[$k] = $v['name'];
		}
		if ($type == 'article' || $type == 'sql')
		{
			$expandFields = $expandOrders = $expandSpecialFields = '';
			$expandSpecialFieldData = array();//sql
			$moduleMenu = $module->getModuleMenu();
			$moduleConfig = $module->getModuleConfig();
			foreach ($moduleMenu as $k => $v)
			{
				$tplmids[$k] = $v[1];
//					require_once PBDIGG_ROOT.'module/'.$v[0].'/'.$v[0].'.config.php';
//					if (!empty($moduleTpl['order']))
//					{
//						$expandFields .= '<br /><fieldset><legend><strong><span class="g">'.$v[1].$cp_message['tpl_module_var_title'].'</span></strong></legend><table style="width:100%"><tr>';
//						$i = 1;
//						foreach ($moduleTpl['fields'] as $kk => $vv)
//						{
//							$expandFields .= '<td>'.$vv.': <input class="input" type="text" value="&#123;!--'.$kk.'--&#125;"></td>';
//							!($i++ % 3) && $expandFields .= '</tr><tr>';
//						}
//						$expandFields .= str_pad('<td>&nbsp;</td>', $i % 3).'</tr></table></fieldset>';
//					}
				$cmoduleConfig = $moduleConfig[$k];
				if (!empty($cmoduleConfig['fields']))
				{
					$expandFields .= '<br /><fieldset><legend><strong><span class="g">'.$v[1].$cp_message['tpl_module_var_title'].'</span></strong></legend><table style="width:100%"><tr>';
					$i = 1;
					foreach ($cmoduleConfig['fields'] as $kk => $vv)
					{
						$expandFields .= '<td>'.$vv[0].': <input class="input" type="text" value="&#123;!--'.$kk.'--&#125;"></td>';
						!($i++ % 3) && $expandFields .= '</tr><tr>';
					}
					$expandFields .= str_repeat('<td>&nbsp;</td>', $i % 3).'</tr></table></fieldset>';
				}
				
				if ($type == 'sql' && !empty($cmoduleConfig['specialFields']))
				{
					$expandSpecialFieldData = array_merge($expandSpecialFieldData, $cmoduleConfig['specialFields']);
					$expandSpecialFields .= '<br /><fieldset><legend><strong><span class="g">'.$v[1].$cp_message['tpl_module_specialfileds_title'].'</span></strong></legend><table style="width:100%"><tr>';
					$i = 1;
					foreach ($cmoduleConfig['specialFields'] as $vvv)
					{
						isset($cmoduleConfig['fields'][$vvv]) && $expandSpecialFields .= '<td>'.$cmoduleConfig['fields'][$vvv][0].'&#123;!--'.$vvv.'--&#125;: <input size="45" class="input" type="text" value="'.implode(',', $cmoduleConfig['fields'][$vvv][1]).'"></td>';
						!($i++ % 2) && $expandSpecialFields .= '</tr><tr>';
					}
					$expandSpecialFields .= str_repeat('<td>&nbsp;</td>', $i % 2).'</tr></table></fieldset>';
				}
			}
		}
		switch ($type)
		{
			case 'article':

				if (isPost())
				{
					intConvert(array('cachetime','self','topicimg','topped','commend','commendpic','first','postdate_opt','postdate','trantnum','titlelimit','cotentlimit'));
					charConvert(array('tplname','orderby','order','timeformat'));
					!preg_match('~^[a-z0-9_\x7f-\xff]+$~i', $tplname) && showMsg('tpl_illegal_tplname');
					$cachetime < 0 && $cachetime = 600;
					($self || !isset($cids) || !is_array($cids)) && $cids = array();
					$self && !empty($cids) && showMsg('tpl_selfcate_conflict');
					$newcids = $newmids = '';
					$newcids = implode(',', array_map('intval', $cids));
					if (isset($mids))
					{
						!is_array($mids) && showMsg('tpl_mids_data_error');
						foreach ($mids as $v)
						{
							array_key_exists($v, $moduleMenu) && $newmids .= (int)$v.',';
						}
						$newmids && $newmids = substr($newmids, 0, -1);
					}
					!in_array($order, array('digg','diggdate','bury','burydate','views','postdate','commentdate','comments','pbrank')) && showMsg('tpl_order_error');
					$orderby = $orderby == 'asc' ? 'asc' : 'desc';
					$trantnum < 0 && $trantnum = 10;
					$titlelimit < 0 && $titlelimit = 0;
					$cotentlimit < 0 && $cotentlimit = 0;
					$timeformat != -1 && !preg_match('~^(?:Y-)?m-d(?: H:i:s)?$~i', $timeformat) && showMsg('tpl_illegal_timeformat');
					!$template && showMsg('tpl_empty_template');

					$trantattribute['cid'] = array(getInCode($newcids), 'IN', '('.$newcids.')', $newcids);
					$trantattribute['module'] = array(getInCode($newmids), 'IN', '('.$newmids.')', $newmids);
					$trantattribute['topicimg'] = array(1, getOpeCode($topicimg, true), "\'\'", $topicimg);
					$trantattribute['topped'] = array(1, getOpeCode($topped), $topped);
					$trantattribute['commend'] = array(1, getOpeCode($commend), $commend);
					$trantattribute['commendpic'] = array(1, getOpeCode($commendpic, true), "\'\'", $commendpic);
					$trantattribute['first'] = array(1, getOpeCode($first), $first);
					$trantattribute['postdate_opt'] = array(0, $postdate_opt);
					$trantattribute['postdate'] = array($postdate_opt ? 1 : 0, getZoneCode($postdate_opt), '{timestamp} - '.$postdate);
					$trantattribute['ifcheck'] = array(1, '=', 1);
					$trantattribute['self'] = array(0, $self);
//					var_dump($trantattribute);exit;

					list($querysql, $fields, $specialFields, $replaceFields) = compileSQL('t', $template, $trantattribute, $order, $orderby, $trantnum);

					stripS($trantattribute);
					$trantattribute = addslashes(serialize($trantattribute));
					if ($do == 'edit')
					{
						intConvert(array('tplid'));
						$tplmarksql = $self && substr($tplmark, 0, 4) == 'self' || !$self && substr($tplmark, 0, 3) == 'tpl' ? '' : "tplmark = '".tplMark($tplname, $self)."',";
						$DB->db_exec("UPDATE {$db_prefix}templates SET $tplmarksql tplname = '$tplname',cachetime = '$cachetime',trantattribute = '$trantattribute',trantorder = '$order',trantby = '$orderby',trantnum = '$trantnum',`fields` = '$fields',specialfields = '$specialFields',replacefields = '$replaceFields',cotentlimit = '$cotentlimit',titlelimit = '$titlelimit',timeformat = '$timeformat',tplcontent = '$template',querysql = '$querysql' WHERE tplid = '$tplid'");
						$Cache->tplvar();
						redirect('tpl_guide_edit_success', 'admincp.php?action=tpl&job=list');
					}
					else
					{
						$tplmark = tplMark($tplname, $self);
						$DB->db_exec("INSERT INTO {$db_prefix}templates (tplid,tplname,tplmark,cachetime,trantattribute,trantorder,trantby,trantnum,`fields`,specialfields,replacefields,cotentlimit,titlelimit,timeformat,tranttype,tplcontent,querysql) VALUES (NULL,'$tplname','$tplmark','$cachetime','$trantattribute','$order','$orderby','$trantnum','$fields','$specialFields','$replaceFields','$cotentlimit','$titlelimit','$timeformat','$type','$template','$querysql')");
						$Cache->tplvar();
						redirect('tpl_guide_make_success', 'admincp.php?action=tpl&job=guide');
					}
				}
				elseif ($do == 'edit')
				{
					intConvert(array('tplid'));
					$guide = $DB->fetch_one("SELECT * FROM {$db_prefix}templates WHERE tplid = '$tplid'");
					!$guide && showMsg('tpl_trant_noexist');
					$trantattribute = unserialize($guide['trantattribute']);
					$postdate_opt = html_select($option_message['compare'], 'postdate_opt', end($trantattribute['postdate_opt']));
					$postdate = html_select($option_message['tday'], 'postdate', substr(end($trantattribute['postdate']), 14));
					$orderby = html_select($option_message['orderby'], 'orderby', $guide['trantby']);
					$timeformat = html_radio($option_message['timeformat'], 'timeformat', $guide['timeformat']);
					$trantattribute['self_'.end($trantattribute['self'])] = $trantattribute['topicimg_'.end($trantattribute['topicimg'])] = $trantattribute['topped_'.end($trantattribute['topped'])] = $trantattribute['commend_'.end($trantattribute['commend'])] = $trantattribute['commendpic_'.end($trantattribute['commendpic'])] = $trantattribute['first_'.end($trantattribute['first'])] = $guide['order_'.$guide['trantorder']] = 'checked="checked"';
					$tplcids = html_checkbox($tplcids, 'cids[]', explode(',', end($trantattribute['cid'])));
					$tplmids = html_checkbox($tplmids, 'mids[]', explode(',', end($trantattribute['module'])));
					$display_cid = end($trantattribute['self']) ? 'none' : 'block';
				}
				else
				{
					$trantattribute['self_0'] = $trantattribute['topicimg_3'] = $trantattribute['topped_3'] = $trantattribute['commend_3'] = $trantattribute['commendpic_3'] = $trantattribute['first_3'] = $guide['order_digg'] = 'checked="checked"';
					$postdate_opt = html_select($option_message['compare'], 'postdate_opt');
					$postdate = html_select($option_message['tday'], 'postdate');
					$orderby = html_select($option_message['orderby'], 'orderby');
					$timeformat = html_radio($option_message['timeformat'], 'timeformat', 'Y-m-d H:i:s');
					$tplcids = html_checkbox($tplcids, 'cids[]');
					$tplmids = html_checkbox($tplmids, 'mids[]');
					$display_cid = 'block';
				}
				break;
			
			case 'comment':
				if (isPost())
				{
					intConvert(array('cachetime','self','postdate_opt','postdate','trantnum','titlelimit','cotentlimit'));
					charConvert(array('tplname','orderby','order','timeformat'));
					!preg_match('~^[a-z0-9_\x7f-\xff]+$~i', $tplname) && showMsg('tpl_illegal_tplname');
					$cachetime < 0 && $cachetime = 600;
					($self || !isset($cids) || !is_array($cids)) && $cids = array();
					$self && !empty($cids) && showMsg('tpl_selfcate_conflict');
					$newcids = $newmids = '';
					$newcids = implode(',', array_map('intval', $cids));
//					if (isset($mids))
//					{
//						!is_array($mids) && showMsg('tpl_mids_data_error');
//						foreach ($mids as $v)
//						{
//							array_key_exists($v, $moduleMenu) && $newmids .= (int)$v.',';
//						}
//						$newmids && $newmids = substr($newmids, 0, -1);
//					}
					!in_array($order, array('digg','diggdate','bury','burydate','views','postdate')) && showMsg('tpl_order_error');
					$orderby = $orderby == 'asc' ? 'asc' : 'desc';
					$trantnum < 0 && $trantnum = 10;
					$titlelimit < 0 && $titlelimit = 0;
					$cotentlimit < 0 && $cotentlimit = 0;
					$timeformat != -1 && !preg_match('~^(?:Y-)?m-d(?: H:i:s)?$~i', $timeformat) && showMsg('tpl_illegal_timeformat');
					!$template && showMsg('tpl_empty_template');

					$trantattribute['cid'] = array(getInCode($newcids), 'IN', '('.$newcids.')', $newcids);
//					$trantattribute['module'] = array(getInCode($newmids), 'IN', '('.$newmids.')', $newmids);
					$trantattribute['postdate_opt'] = array(0, $postdate_opt);
					$trantattribute['postdate'] = array($postdate_opt ? 1 : 0, getZoneCode($postdate_opt), '{timestamp} - '.$postdate);
					$trantattribute['ifcheck'] = array(1, '=', 1);
					$trantattribute['self'] = array(0, $self);
		
					list($querysql, $fields, $specialFields, $replaceFields) = compileSQL('r', $template, $trantattribute, $order, $orderby, $trantnum);

					stripS($trantattribute);
					$trantattribute = addslashes(serialize($trantattribute));

					if ($do == 'edit')
					{
						intConvert(array('tplid'));
						$tplmarksql = $self && substr($tplmark, 0, 4) == 'self' || !$self && substr($tplmark, 0, 3) == 'tpl' ? '' : "tplmark = '".tplMark($tplname, $self)."',";
						$DB->db_exec("UPDATE {$db_prefix}templates SET $tplmarksql tplname = '$tplname',cachetime = '$cachetime',trantattribute = '$trantattribute',trantorder = '$order',trantby = '$orderby',trantnum = '$trantnum',`fields` = '$fields',specialfields = '$specialFields',replacefields='$replaceFields',cotentlimit = '$cotentlimit',titlelimit = '$titlelimit',timeformat = '$timeformat',tplcontent = '$template',querysql = '$querysql' WHERE tplid = '$tplid'");
						$Cache->tplvar();
						redirect('tpl_guide_edit_success', 'admincp.php?action=tpl&job=list');
					}
					else
					{
						$tplmark = tplMark($tplname, $self);
						$DB->db_exec("INSERT INTO {$db_prefix}templates (tplid,tplname,tplmark,cachetime,trantattribute,trantorder,trantby,trantnum,`fields`,specialfields,replacefields,cotentlimit,titlelimit,timeformat,tranttype,tplcontent,querysql) VALUES (NULL,'$tplname','$tplmark','$cachetime','$trantattribute','$order','$orderby','$trantnum','$fields','$specialFields','$replaceFields','$cotentlimit','$titlelimit','$timeformat','$type','$template','$querysql')");
						$Cache->tplvar();
						redirect('tpl_guide_make_success', 'admincp.php?action=tpl&job=guide');
					}
				}
				elseif ($do == 'edit')
				{
					intConvert(array('tplid'));
					$guide = $DB->fetch_one("SELECT * FROM {$db_prefix}templates WHERE tplid = '$tplid'");
					!$guide && showMsg('tpl_trant_noexist');
					$trantattribute = unserialize($guide['trantattribute']);
					$postdate_opt = html_select($option_message['compare'], 'postdate_opt', end($trantattribute['postdate_opt']));
					$postdate = html_select($option_message['tday'], 'postdate', substr(end($trantattribute['postdate']), 14));
					$orderby = html_select($option_message['orderby'], 'orderby', $guide['trantby']);
					$timeformat = html_radio($option_message['timeformat'], 'timeformat', $guide['timeformat']);
					$trantattribute['self_'.end($trantattribute['self'])] = $guide['order_'.$guide['trantorder']] = 'checked="checked"';
					$tplcids = html_checkbox($tplcids, 'cids[]', explode(',', end($trantattribute['cid'])));
//					$tplmids = html_checkbox($tplmids, 'mids[]', explode(',', end($trantattribute['module'])));
					$display_cid = end($trantattribute['self']) ? 'none' : 'block';
				}
				else
				{
					$trantattribute['self_0'] = $guide['order_digg'] = 'checked="checked"';
					$postdate_opt = html_select($option_message['compare'], 'postdate_opt');
					$postdate = html_select($option_message['tday'], 'postdate');
					$orderby = html_select($option_message['orderby'], 'orderby');
					$timeformat = html_radio($option_message['timeformat'], 'timeformat', 'Y-m-d H:i:s');
					$tplcids = html_checkbox($tplcids, 'cids[]');
//					$tplmids = html_checkbox($tplmids, 'mids[]');
					$display_cid = 'block';
				}
				break;
			
			case 'member':
				if (isPost())
				{
					intConvert(array('cachetime','regdate_opt','regdate','trantnum'));
					charConvert(array('tplname','orderby','order'));
					!preg_match('~^[a-z0-9_\x7f-\xff]+$~i', $tplname) && showMsg('tpl_illegal_tplname');
					$cachetime < 0 && $cachetime = 600;
					!in_array($order, array('currency','postnum','commentnum','diggnum','burynum','uploadnum','friendnum','collectionnum','visitnum')) && showMsg('tpl_order_error');
					$orderby = $orderby == 'asc' ? 'asc' : 'desc';
					$trantnum < 0 && $trantnum = 10;
					$timeformat != -1 && !preg_match('~^(?:Y-)?m-d(?: H:i:s)?$~i', $timeformat) && showMsg('tpl_illegal_timeformat');
					!$template && showMsg('tpl_empty_template');

					$trantattribute['gender'] = array(1, getOpeCode($gender), $gender);
					$trantattribute['regdate_opt'] = array(0, $regdate_opt);
					$trantattribute['regdate'] = array($regdate_opt ? 1 : 0, getZoneCode($regdate_opt), $timestamp - $regdate);

					list($querysql, $fields, $specialFields, $replaceFields) = compileSQL('m', $template, $trantattribute, $order, $orderby, $trantnum);

					stripS($trantattribute);
					$trantattribute = addslashes(serialize($trantattribute));

					if ($do == 'edit')
					{
						intConvert(array('tplid'));
						$DB->db_exec("UPDATE {$db_prefix}templates SET tplname = '$tplname',cachetime = '$cachetime',trantattribute = '$trantattribute',trantorder = '$order',trantby = '$orderby',trantnum = '$trantnum',`fields` = '$fields',specialfields = '$specialFields',replacefields='$replaceFields',timeformat='$timeformat',tplcontent = '$template',querysql = '$querysql' WHERE tplid = '$tplid'");
						$Cache->tplvar();
						redirect('tpl_guide_edit_success', 'admincp.php?action=tpl&job=list');
					}
					else
					{
						$tplmark = tplMark($tplname, 0);
						$DB->db_exec("INSERT INTO {$db_prefix}templates (tplid,tplname,tplmark,cachetime,trantattribute,trantorder,trantby,trantnum,`fields`,specialfields,replacefields,timeformat,tranttype,tplcontent,querysql) VALUES (NULL,'$tplname','$tplmark','$cachetime','$trantattribute','$order','$orderby','$trantnum','$fields','$specialFields','$replaceFields','$timeformat','$type','$template','$querysql')");
						$Cache->tplvar();
						redirect('tpl_guide_make_success', 'admincp.php?action=tpl&job=guide');
					}
				}
				elseif ($do == 'edit')
				{
					intConvert(array('tplid'));
					$guide = $DB->fetch_one("SELECT * FROM {$db_prefix}templates WHERE tplid = '$tplid'");
					!$guide && showMsg('tpl_trant_noexist');
					$trantattribute = unserialize($guide['trantattribute']);
					$postdate_opt = html_select($option_message['compare'], 'regdate_opt', end($trantattribute['regdate_opt']));
					$postdate = html_select($option_message['tday'], 'regdate', end($trantattribute['regdate']));
					$orderby = html_select($option_message['orderby'], 'orderby', $guide['trantby']);
					$trantattribute['gender_'.end($trantattribute['gender'])] = $guide['order_'.$guide['trantorder']] = 'checked="checked"';
					$timeformat = html_radio($option_message['timeformat'], 'timeformat', $guide['timeformat']);
				}
				else
				{
					$trantattribute['gender_3'] = $guide['order_currency'] = 'checked="checked"';
					$postdate_opt = html_select($option_message['compare'], 'regdate_opt');
					$postdate = html_select($option_message['tday'], 'regdate');
					$orderby = html_select($option_message['orderby'], 'orderby');
					$timeformat = html_radio($option_message['timeformat'], 'timeformat', 'Y-m-d H:i:s');
				}
				break;
			
			case 'html':
				if (isPost())
				{
					charConvert(array('tplname'));
					!preg_match('~^[a-z0-9_\x7f-\xff]+$~i', $tplname) && showMsg('tpl_illegal_tplname');
					if ($do == 'edit')
					{
						intConvert(array('tplid'));
						$DB->db_exec("UPDATE {$db_prefix}templates SET tplname = '$tplname', tplcontent = '$template' WHERE tplid = '$tplid'");
						$Cache->tplvar();
						redirect('tpl_guide_edit_success', 'admincp.php?action=tpl&job=list');
					}
					else
					{
						$tplmark = tplMark($tplname, 0);
						$DB->db_exec("INSERT INTO {$db_prefix}templates (tplid,tplname,tplmark,cachetime,trantattribute,trantorder,trantby,trantnum,`fields`,specialfields,cotentlimit,titlelimit,timeformat,tranttype,tplcontent,querysql) VALUES (NULL,'$tplname','$tplmark','','','','','','','','','','','$type','$template','')");
						$Cache->tplvar();
						redirect('tpl_guide_make_success', 'admincp.php?action=tpl&job=guide');
					}
				}
				elseif ($do == 'edit')
				{
					intConvert(array('tplid'));
					$guide = $DB->fetch_one("SELECT * FROM {$db_prefix}templates WHERE tplid = '$tplid'");
					!$guide && showMsg('tpl_trant_noexist');
				}
				break;

			case 'sql':
				if (isPost())
				{
					charConvert(array('tplname'));
					!preg_match('~^[a-z0-9_\x7f-\xff]+$~i', $tplname) && showMsg('tpl_illegal_tplname');
					$timeformat != -1 && !preg_match('~^(?:Y-)?m-d(?: H:i:s)?$~i', $timeformat) && showMsg('tpl_illegal_timeformat');
					if (!$querysql || !preg_match('~^select[_`a-z0-9\.\s,]+from[_`\-a-z0-9\.\s():|&=,<>!\*/%\~]+~i', $querysql)) showMsg('tpl_sql_format_error');
					preg_match('~(delete|drop|truncate|load data|replace|update|insert)~i', $querysql) && showMsg('tpl_sql_dangerous_words');

					$specialFields = $replaceFields = array();
					//内置特殊字段
					$builtInSpecialFieldData = array('turl','curl','uurl','postdate','avatar','cname','topicimg','summary','subject','altsubject','content','regdate','gender','signature');
					preg_match_all('~{!--([_0-9a-z\.]+?)--}~i', $template, $tplMatchFields, PREG_SET_ORDER);

					foreach ($tplMatchFields as $v)
					{
						$replaceFields[] = $v[1];
						list($prefix, $suffix) = explode('.', $v[1]);			
						(in_array($prefix, array('t','m','mx','r','c')) && in_array($suffix, $builtInSpecialFieldData) || in_array($v[1], $expandSpecialFieldData)) && !in_array($v[1], $specialFields) && $specialFields[] = $v[1];
					}
					$replaceFields = implode(',', $replaceFields);
					$specialFields = implode(',', $specialFields);
					if ($do == 'edit')
					{
						intConvert(array('tplid'));
						$DB->db_exec("UPDATE {$db_prefix}templates SET tplname = '$tplname', specialfields = '$specialFields', replaceFields = '$replaceFields', timeformat = '$timeformat', tplcontent = '$template', querysql = '$querysql' WHERE tplid = '$tplid'");
						$Cache->tplvar();
						redirect('tpl_guide_edit_success', 'admincp.php?action=tpl&job=list');
					}
					else
					{
						$tplmark = tplMark($tplname, 0);
						$DB->db_exec("INSERT INTO {$db_prefix}templates (tplid,tplname,tplmark,cachetime,trantattribute,trantorder,trantby,trantnum,`fields`,specialfields,replaceFields,cotentlimit,titlelimit,timeformat,tranttype,tplcontent,querysql) VALUES (NULL,'$tplname','$tplmark','','','','','','','$specialFields','$replaceFields','','','$timeformat','$type','$template','$querysql')");
						$Cache->tplvar();
						redirect('tpl_guide_make_success', 'admincp.php?action=tpl&job=guide');
					}
				}
				elseif ($do == 'edit')
				{
					intConvert(array('tplid'));
					$guide = $DB->fetch_one("SELECT * FROM {$db_prefix}templates WHERE tplid = '$tplid'");
					!$guide && showMsg('tpl_trant_noexist');
					$timeformat = html_radio($option_message['timeformat'], 'timeformat', $guide['timeformat']);
				}
				else
				{
					$timeformat = html_radio($option_message['timeformat'], 'timeformat', 'Y-m-d H:i:s');
				}
				break;

			default:
				break;
		}
		break;

	case 'list':
		$tpllist = array();
		$query = $DB->db_query("SELECT tplid, tplname, tplmark, tranttype FROM {$db_prefix}templates");
		while ($rs = $DB->fetch_all($query))
		{
			$rs['texttranttype'] = $cp_message['tpl_'.$rs['tranttype']];
			$tpllist[] = $rs;
		}
		break;

	case 'deltpl':
		!is_array($tplids) && $tplids = settype($tplids, 'array');
		$tplids = array_filter(array_map('intval', $tplids));
		!$tplids && showMsg('tpl_chose_tplid');
		$DB->db_exec("DELETE FROM {$db_prefix}templates WHERE tplid IN (".implode(',', $tplids).")");
		$Cache->tplvar();
		redirect('tpl_del_success', 'admincp.php?action=tpl&job=list');
		break;

	case 'special':
		if ($do == 'edit')
		{
			intConvert(array('tplid'));
			$specialtpl = $DB->fetch_one("SELECT * FROM {$db_prefix}specialtpl WHERE tplid = '$tplid'");
			!$specialtpl && showMsg('tpl_special_noexist');
			if (isPost())
			{
				$DB->db_exec("UPDATE {$db_prefix}specialtpl SET template = '$template' WHERE tplid = '$tplid'");
				$Cache->specialtpl();
				redirect('tpl_special_mod_success', 'admincp.php?action=tpl&job=special');
			}
		}
		else
		{
			$specialtpl = array();
			$query = $DB->db_query("SELECT * FROM {$db_prefix}specialtpl");
			while ($rs = $DB->fetch_all($query))
			{
				$specialtpl[] = $rs;
			}
		}
		break;
	default:
		break;
}

?>
