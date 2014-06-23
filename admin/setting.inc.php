<?php
/**
 * @version $Id: setting.inc.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

(!defined('IN_ADMIN') || !isset($_PBENV['PHP_SELF']) || !preg_match('/[\/\\\\]admincp\.php$/i', $_PBENV['PHP_SELF'])) && exit('Access Denied');

define('PB_PAGE', 'setting');

if (isPost())
{
	$configs = array();
	!$Cache->cacheMod('config', 0777) && showMsg('setting_cachemod_failed');
	//basic setting
	if ($job == 'basic')
	{
		$configs['pb_sitename'] = trim($config['pb_sitename']);
		$configs['pb_siteurl'] = strtolower(trim($config['pb_siteurl']));
		$configs['pb_sitedir'] = str_replace('\\\\', '/', strtolower(trim($config['pb_sitedir'])));
		!preg_match('~^[a-z0-9\_\/]+$~is', $configs['pb_sitedir']) && showMsg('setting_sitedir_error');
		$configs['pb_sitedir'] != '/' && $configs['pb_sitedir'] = '/'.$configs['pb_sitedir'].'/';
		$configs['pb_siteurl'] && !preg_match('~^https?://[a-z0-9\-\_\/\.:]+/$~is', $configs['pb_siteurl']) && showMsg('setting_siteurl_error');
		$configs['pb_adminmail'] = trim($config['pb_adminmail']);
		$configs['pb_adminmail'] && !isEMAIL($configs['pb_adminmail']) && showMsg('setting_email_error');
		$configs['pb_icp'] = '<a href="http://www.miibeian.gov.cn" target="_blank">'.trim($config['pb_icp']).'</a>';
		$configs['pb_statistic'] = trim($config['pb_statistic']);
		$configs['pb_ifopen'] = $config['pb_ifopen'] ? 1 : 0;
		$configs['pb_whyclosed'] = $whyclosed;
	}
	//core setting
	elseif ($job == 'core')
	{
		$configs['pb_gzip'] = (int)$config['pb_gzip'];
		if (PHP_VERSION < '4.0.4' || !function_exists('ob_gzhandler') || @ini_get('zlib.output_compression') || @ini_get('output_handler') == 'ob_gzhandler')
		{
			$configs['pb_gzip'] = 0;
		}
		$configs['pb_rewrite'] = (int)$config['pb_rewrite'];
		$configs['pb_cachetime'] = (int)$config['pb_cachetime'];
		$configs['pb_getpw'] = (int)$config['pb_getpw'];
		$configs['pb_lang'] = in_array($config['pb_lang'], array('en', 'zh', 'tw')) && is_dir(PBDIGG_ROOT.'languages/'.$config['pb_lang']) ? $config['pb_lang'] : 'zh';
		if (!preg_match('~^[_0-9a-z]+?$~is', $config['pb_style']) || !is_dir(PBDIGG_ROOT.'templates/'.$config['pb_style']))
		{
			showMsg('setting_styledir_noexists');
		}
		elseif (!is_writable(PBDIGG_ROOT.'cache') || !is_writable(PBDIGG_ROOT.'compile'))
		{
			showMsg('setting_styledir_unwritable');
		}
		$configs['pb_style'] = $config['pb_style'];
		$configs['pb_timezone'] = array_key_exists($config['pb_timezone'], $option_message['timezone']) ? (int)$config['pb_timezone'] : 0;
		$configs['pb_timecorrect'] = (int)$config['pb_timecorrect'];
		$configs['pb_timeformat'] = (int)$config['pb_timeformat'];
		$configs['pb_dateformat'] = trim($config['pb_dateformat']);
		if ($configs['pb_dateformat'] && !isDateTime($configs['pb_dateformat']))
		{
			$configs['pb_dateformat'] = 'Y-m-d';
		}
		$configs['pb_refreshtime'] = $config['pb_refreshtime'] >= 0 ? (int)$config['pb_refreshtime'] : 3;
		$configs['pb_maxsearchctrl'] = $config['pb_maxsearchctrl'] >= 0 ? (int)$config['pb_maxsearchctrl'] : 3;
		$configs['pb_maxresult'] = $config['pb_maxresult'] > 0 ? (int)$config['pb_maxresult'] : 500;
		$configs['pb_online'] = $config['pb_online'] > 0 ? (int)$config['pb_online'] : 60;
		$configs['pb_exectime'] = (int)$config['pb_exectime'];
		$configs['pb_ckpre'] = (!$config['pb_ckpre'] || strlen($config['pb_ckpre']) < 6) ? suggestKey(6) : trim($config['pb_ckpre']);
		$configs['pb_ckpath'] = $config['pb_ckpath'];
		$configs['pb_ckpath'] != '/' && !preg_match('~^/[^\/:?*"<>|]+/$~is', $configs['pb_ckpath']) && $configs['pb_ckpath'] = '/';
		$configs['pb_ckdomain'] = $config['pb_ckdomain'];
		$configs['pb_ckdomain'] != '' && !preg_match('~^\.?[\da-z][-a-z0-9\.]*[\da-z]$~is', $configs['pb_ckdomain']) && $configs['pb_ckdomain'] = '';
		$configs['pb_cpovertime'] = $config['pb_cpovertime'] >= 60 ? (int)$config['pb_cpovertime'] : 900;
		$configs['pb_robots'] = (int)$config['pb_robots'];
	}
	//view setting
	elseif ($job == 'view')
	{
		$configs['pb_diggname'] = htmlspecialchars(trim($config['pb_diggname']));
		$configs['pb_buryname'] = htmlspecialchars(trim($config['pb_buryname']));
		$configs['pb_indextitle'] = $config['pb_indextitle'] > 0 ? (int)$config['pb_indextitle'] : 50;
		$configs['pb_indexcontent'] = $config['pb_indexcontent'] > 0 ? (int)$config['pb_indexcontent'] : 300;
		$configs['pb_aperpage'] = $config['pb_aperpage'] > 0 ? (int)$config['pb_aperpage'] : 10;
		$configs['pb_torder'] = array_key_exists($config['pb_torder'], $option_message['torder']) ? $config['pb_torder'] : 'diggdate';
		$configs['pb_tday'] = (int)$config['pb_tday'];
		$configs['pb_dformat'] = (int)$config['pb_dformat'];
		$configs['pb_titlelink'] = (int)$config['pb_titlelink'];
		$configs['pb_titleubb'] = isset($config['pb_titleubb']) ? array_sum($config['pb_titleubb']) : '0';
		$configs['pb_topicstylesize'] = (int)$config['pb_topicstyleh']."\t".(int)$config['pb_topicstylew'];
		$configs['pb_tubbtype'] = isset($config['pb_tubbtype']) ? implode("\t", $config['pb_tubbtype']) : '';
		$configs['pb_contentthumb'] = (int)$config['pb_contentthumb'];
		$configs['pb_topicthumb'] = (int)$config['pb_topicthumb'];
		$configs['pb_topicthumbsize'] = (int)$config['pb_topicthumbh']."\t".(int)$config['pb_topicthumbw'];
		$configs['pb_contentthumbsize'] = (int)$config['pb_contentthumbh']."\t".(int)$config['pb_contentthumbw'];
		$configs['pb_previewsize'] = (int)$config['pb_previewh']."\t".(int)$config['pb_previeww'];
		$configs['pb_mautoplay'] = (int)$config['pb_mautoplay'];
		$configs['pb_copyctrl'] = (int)$config['pb_copyctrl'];
		$configs['pb_taglink'] = (int)$config['pb_taglink'];
		$configs['pb_fautoplay'] = (int)$config['pb_fautoplay'];
		$configs['pb_mplayersize'] = (int)$config['pb_mplayerh']."\t".(int)$config['pb_mplayerw'];
		$configs['pb_cperpage'] = $config['pb_cperpage'] > 0 ? (int)$config['pb_cperpage'] : 15;
		$configs['pb_corder'] = (array_key_exists($config['pb_corder_one'], $option_message['corder']) ? $config['pb_corder_one'] : 'postdate')."\t".($config['pb_corder_two'] == 'asc' ? 'asc' : 'desc');
		$configs['pb_cubbtype'] = isset($config['pb_cubbtype']) ? implode("\t", $config['pb_cubbtype']) : '';
		$configs['pb_showsign'] = (int)$config['pb_showsign'][0] + (int)$config['pb_showsign'][1];
		$configs['pb_signubbtype'] = isset($config['pb_signubbtype']) ? implode("\t", $config['pb_signubbtype']) : '';
		$configs['pb_signsize'] = (int)$config['pb_signh']."\t".(int)$config['pb_signw'];
		$configs['pb_signimgsize'] = (int)$config['pb_signimgh']."\t".(int)$config['pb_signimgw'];
		$configs['pb_tperpage'] = (int)$config['pb_tperpage'];
		$configs['pb_tagcolor'] = (int)$config['pb_tagcolor'];
	}
	//atc setting
	elseif ($job == 'atc')
	{
		$configs['pb_ifpost'] = (int)$config['pb_ifpost'];
		$configs['pb_ifcomment'] = (int)$config['pb_ifcomment'];
		$configs['pb_tcheck'] = (int)$config['pb_tcheck'];
		$configs['pb_ccheck'] = (int)$config['pb_ccheck'];
		$configs['pb_trackback'] = (int)$config['pb_trackback'];
		$configs['pb_anonnews'] = (int)$config['pb_anonnews'];
		$configs['pb_autoshield'] = (int)$config['pb_autoshield'];
		$configs['pb_reeditlimit'] = $config['pb_reeditlimit'] > 0 ? (int)$config['pb_reeditlimit'] : 0;
		$configs['pb_tftopicimg'] = (int)$config['pb_tftopicimg'];
		$configs['pb_urlsaveimg'] = (int)$config['pb_urlsaveimg'];
		$configs['pb_reposttime'] = $config['pb_reposttime'] > 0 ? (int)$config['pb_reposttime'] : 0;
		$configs['pb_ifdigg'] = (int)$config['pb_ifdigg'];
		$configs['pb_ifbury'] = (int)$config['pb_ifbury'];
		$config['pb_titlelenmin'] = (int)$config['pb_titlelenmin'];
		$config['pb_titlelenmax'] = (int)$config['pb_titlelenmax'];
		exchange($config['pb_titlelenmin'], $config['pb_titlelenmax']);
		$configs['pb_titlelen'] = $config['pb_titlelenmin']."\t".$config['pb_titlelenmax'];
		$config['pb_contentlenmin'] = (int)$config['pb_contentlenmin'];
		$config['pb_contentlenmax'] = (int)$config['pb_contentlenmax'];
		exchange($config['pb_contentlenmin'], $config['pb_contentlenmax']);
		$configs['pb_contentlen'] = $config['pb_contentlenmin']."\t".$config['pb_contentlenmax'];
		$config['pb_commentlenmin'] = (int)$config['pb_commentlenmin'];
		$config['pb_commentlenmax'] = (int)$config['pb_commentlenmax'];
		exchange($config['pb_commentlenmin'], $config['pb_commentlenmax']);
		$configs['pb_commentlen'] = $config['pb_commentlenmin']."\t".$config['pb_commentlenmax'];

		$configs['words_banned'] = array_filter(explode(',', str_replace("\xa3\xac",',',stripslashes(trim($config['words_banned'])))));
		$configs['words_replace'] = array_filter(explode(',', str_replace("\xa3\xac",',',stripslashes(trim($config['words_replace'])))));
		$configs['words_links'] = array_filter(explode(',', str_replace("\xa3\xac",',',stripslashes(trim($config['words_links'])))));
		//缓存
		PWriteFile(PBDIGG_CROOT.'cache_words.php', "<?php\n\r\$words_banned = ".pb_var_export($configs['words_banned']).";\n\r\$words_replace = ".pb_var_export($configs['words_replace']).";\n\r\$words_links = ".pb_var_export($configs['words_links']).";\n\r?>", 'wb');
		$configs['words_banned'] = addslashes(implode(',', $configs['words_banned']));
		$configs['words_replace'] = addslashes(implode(',', $configs['words_replace']));
		$configs['words_links'] = addslashes(implode(',', $configs['words_links']));
	}
	//member setting
	elseif ($job == 'member')
	{
		$configs['pb_selfad'] = (int)$config['pb_selfad'];
		$configs['pb_selfavat'] = (int)$config['pb_selfavat'];
		$configs['pb_avatupload'] = (int)$config['pb_avatupload'];
		$configs['pb_avatsize'] = (int)$config['pb_avatsize'] * 1024;
		$configs['pb_avathight'] = (int)$config['pb_avathight'];
		$configs['pb_avatwidth'] = (int)$config['pb_avatwidth'];
		$configs['pb_msg'] = (int)$config['pb_msg'];
		$configs['pb_fav'] = (int)$config['pb_fav'];
		$configs['reg_status'] = (int)$config['reg_status'];
		$configs['reg_closereason'] = $config['reg_closereason'];
		safeConvert($configs['reg_closereason']);
//		$configs['reg_invite'] = (int)$config['reg_invite'];
		$configs['reg_emailactive'] = (int)$config['reg_emailactive'];
		$configs['reg_sendemail'] = (int)$config['reg_sendemail'];
		$configs['reg_emailcontent'] = trim($config['reg_emailcontent']);
		$configs['reg_agreement'] = $reg_agreement;
		safeConvert($configs['reg_agreement']);
		$configs['reg_allowsameip'] = (int)$config['reg_allowsameip'];
		$configs['reg_bannames'] = trim($config['reg_bannames']);
		$configs['reg_minname'] = (int)$config['reg_minname'];
		$configs['reg_maxname'] = (int)$config['reg_maxname'];
		exchange($configs['reg_minname'], $configs['reg_maxname']);
		$configs['reg_credit'] = (int)$config['reg_credit'];
		updateConfig($configs);
		$Cache->reg();
		$Cache->config();
		redirect('setting_success', $basename);
	}
	//credit setting
	elseif ($job == 'credit')
	{
		$configs['pb_customcredit'] = trim($config['pb_customcredit']);
		if (!$configs['pb_customcredit'])
		{
			showMsg('setting_customcredit_empty');
		}
		ksort($config['pb_creditdb']);
		$configs['pb_creditdb'] = implode("\t", array_map('intval', $config['pb_creditdb']));
	}
	//attachment setting
	elseif ($job == 'attachment')
	{
		$configs['pb_allowupload'] = (int)$config['pb_allowupload'];
		$configs['pb_uploadtopicimg'] = (int)$config['pb_uploadtopicimg'];
		$configs['pb_attoutlink'] = (int)$config['pb_attoutlink'];
		$configs['pb_attoutput'] = (int)$config['pb_attoutput'];
		$configs['pb_attachdir'] = (int)$config['pb_attachdir'];
		$sysUploadMax = ini_bytes('upload_max_filesize');
		$configs['pb_uploadmaxsize'] = (int)$config['pb_uploadmaxsize'] * 1024;
		$configs['pb_outputmaxsize'] = (int)$config['pb_outputmaxsize'] * 1024;
		if ($configs['pb_uploadmaxsize'] > $sysUploadMax)
		{
			showMsg('setting_uploadlimit');
		}
		$configs['pb_attachnum'] = (int)$config['pb_attachnum'];
		$configs['pb_uploadfiletype'] = strtolower(trim($config['pb_uploadfiletype']));
		if ($configs['pb_uploadfiletype'])
		{
			if (strpos($configs['pb_uploadfiletype'], "\xa3\xac") !== FALSE)
			{
				$configs['pb_uploadfiletype'] = str_replace("\xa3\xac", ',', $configs['pb_uploadfiletype']);
			}
			foreach ($configs['pb_uploadfiletype'] as $v)
			{
				(preg_replace('~[a-z0-9]~', '', $v) || in_array($v, array('php','phtml','php3','php4','jsp','exe','dll','asp','cer','asa','shtml','shtm','aspx','asax','cgi','fcgi','pl'))) && showMsg('setting_forbidden_ext');
			}
		}

		$configs['pb_watertype'] = (int)$config['pb_watertype'];
		$configs['pb_watertext'] = trim($config['pb_watertext']);
		$configs['pb_waterfont'] = trim($config['pb_waterfont']);
		if ($configs['pb_waterfont'] && !file_exists(PBDIGG_ROOT.'images/font/'.$configs['pb_waterfont']))
		{
			showMsg('setting_waterfont_noexists');
		}
		$configs['pb_waterfontsize'] = (int)$config['pb_waterfontsize'];
		$configs['pb_waterfontcolor'] = strtolower(trim($config['pb_waterfontcolor']));
		$configs['pb_waterfontcolor'] = preg_replace('~^rgb\((\d+),\s*(\d+),\s*(\d+)\)~ie', '"#".dechex(\\1).dechex(\\2).dechex(\\3)', $configs['pb_waterfontcolor']);
		if (!$configs['pb_waterfontcolor'] || !isColorCode($configs['pb_waterfontcolor']))
		{
			showMsg('setting_waterfontcolor_error');
		}
		$configs['pb_waterimg'] = strtolower(trim($config['pb_waterimg']));
		if (!$configs['pb_waterimg'])
		{
			$configs['pb_waterimg'] = 'mark.png';
		}
//		if (FExt($configs['pb_waterimg']) == 'gif')
//		{
//			showMsg('setting_waterimg_nosupport');
//		}
		if (!file_exists(PBDIGG_ROOT.'images/water/'.$configs['pb_waterimg']) || !isImg(PBDIGG_ROOT.'images/water/'.$configs['pb_waterimg']))
		{
			showMsg('setting_waterimg_noexists');
		}
		$configs['pb_watertransition'] = (int)$config['pb_watertransition'];
		($configs['pb_watertransition'] > 100 || $configs['pb_watertransition'] < 0) && $configs['pb_watertransition'] = 85;
		$configs['pb_waterquality'] = (int)$config['pb_waterquality'];
		($configs['pb_waterquality'] > 100 || $configs['pb_waterquality'] < 0) && $configs['pb_waterquality'] = 85;
		$configs['pb_waterminsize'] = (int)$config['pb_waterminh']."\t".(int)$config['pb_waterminw'];
		$configs['pb_waterposition'] = (int)$config['pb_waterposition'];
	}
	//safe setting
	elseif ($job == 'safe')
	{
		$configs['pb_sitehash'] = trim($config['pb_sitehash']);
		if (!$configs['pb_sitehash'] || !isHash($configs['pb_sitehash']))
		{
			showMsg('setting_sitehash_error');
		}
		$configs['pb_gdcheck'] = 0;
		$gdcheck = array('reg' => 1, 'login' => 2, 'post' => 4, 'comment' => 8, 'admin' => 16);
		$config['pb_gdcheck'] = (array)$config['pb_gdcheck'];
		foreach ($gdcheck as $k => $v)
		{
			if (in_array($v, $config['pb_gdcheck']))
			{
				$configs['pb_gdcheck'] += $v;
			}
		}
		$configs['pb_gdcodetype'] = (int)$config['pb_gdcodetype'];
		$configs['pb_gdheight'] = (int)$config['pb_gdheight'];
		($configs['pb_gdheight'] <= 0 || $configs['pb_gdheight'] > 60) && $configs['pb_gdheight'] = 60;
		$configs['pb_gdwidth'] = (int)$config['pb_gdwidth'];
		($configs['pb_gdwidth'] <= 0 || $configs['pb_gdwidth'] > 150) && $configs['pb_gdwidth'] = 150;
		$configs['pb_gdcodenum'] = (int)$config['pb_gdcodenum'];
		$configs['pb_gdcodenum'] <= 0 && $configs['pb_gdcodenum'] = 4;
	
		$configs['pb_checkquestion'] = trim($config['pb_answercheck'][0]);
		$configs['pb_checkanswer'] = trim($config['pb_answercheck'][1]);
		safeConvert($configs['pb_checkquestion']);
		safeConvert($configs['pb_checkanswer']);
		if ($configs['pb_checkquestion'] && $configs['pb_checkanswer'] === '')
		{
			showMsg('setting_checkanswer_need');
		}
		$configs['pb_regqa'] = (int)$config['pb_regqa'];
		$config['pb_ipallow'] = trim($config['pb_ipallow']);
		$config['pb_ipdeny'] = trim($config['pb_ipdeny']);
		$config['pb_adminipallow'] = trim($config['pb_adminipallow']);
		$configs['pb_ipallow'] = $config['pb_ipallow'] ? serialize(preg_replace('~(?:\r\n|\n\r|\r|\n)~i', "\n", $config['pb_ipallow'])) : '';
		$configs['pb_ipdeny'] = $config['pb_ipdeny'] ? serialize(preg_replace('~(?:\r\n|\n\r|\r|\n)~i', "\n", $config['pb_ipdeny'])) : '';
		$configs['pb_adminipallow'] = $config['pb_adminipallow'] ? serialize(preg_replace('~(?:\r\n|\n\r|\r|\n)~i', "\n", $config['pb_adminipallow'])) : '';
		$configs['pb_adminsafecode'] = (int)$config['pb_adminsafecode'];
		if ($configs['pb_adminsafecode'])
		{
			require_once PBDIGG_ROOT.'data/safe.inc.php';
			if (empty($_adminsafecode))
			{
				showMsg('setting_safecode_empty');
			}
		}
		$configs['pb_loadavg'] = (int)$config['pb_loadavg'];
	}
	//seo setting
	elseif ($job == 'seo')
	{
		$configs['pb_rewriteext'] = (int)$config['pb_rewriteext'];
		$configs['pb_extenable'] = (int)$config['pb_extenable'];
		$configs['pb_chtmldir'] = strtolower($config['pb_chtmldir']);
		!$configs['pb_chtmldir'] && $configs['pb_chtmldir'] = 'category';
		$configs['pb_shtmldir'] = strtolower($config['pb_shtmldir']);
		!$configs['pb_shtmldir'] && $configs['pb_shtmldir'] = 'show';
		preg_replace('~[_0-9a-z]~i', $configs['pb_chtmldir']) && showMsg('setting_illegal_chtmldir');
		$DB->fetch_first("SELECT COUNT(*) num FROM {$db_prefix}categories WHERE dir = '".$configs['pb_chtmldir']."'") && showMsg('setting_chtmldir_noperm');
		in_array($configs['pb_chtmldir'], $option_message['systemdir']) && showMsg('setting_chtmldir_noperm');
		preg_replace('~[_0-9a-z]~i', $configs['pb_shtmldir']) && showMsg('setting_illegal_shtmldir');
		in_array($configs['pb_shtmldir'], $option_message['systemdir']) && showMsg('setting_shtmldir_noperm');
		if ($configs['pb_rewriteext'] < 0 || $configs['pb_rewriteext'] > 6)
		{
			showMsg('setting_rewriteext_error');
		}
		$configs['pb_seotitle'] = trim($config['pb_seotitle']);
		$configs['pb_seokeywords'] = trim($config['pb_seokeywords']);
		$configs['pb_seodescription'] = trim($config['pb_seodescription']);
		$configs['pb_seomore'] = trim($config['pb_seomore']);
		$pb_chtmldir = $configs['pb_shtmldir'];
		$Cache->categories();
	}
	//email setting
	elseif ($job == 'email')
	{
		$configs['pb_mailtype'] = (int)$config['pb_mailtype'];
		$configs['pb_smtphost'] = trim($config['pb_smtphost']);
		if ($configs['pb_mailtype'] && !$configs['pb_smtphost'])
		{
			showMsg('setting_smtphost_empty');
		}
		$configs['pb_smtpauth'] = (int)$config['pb_smtpauth'];
		$configs['pb_smtpuser'] = trim($config['pb_smtpuser']);
		$configs['pb_smtppw'] = trim($config['pb_smtppw']);
		$configs['pb_smtpport'] = (int)$config['pb_smtpport'];
		if ($configs['pb_mailtype'] && (!$configs['pb_smtpuser'] || !$configs['pb_smtpport']))
		{
			showMsg('setting_smtpauth_error');
		}
		@extract($configs);
		require_once PBDIGG_ROOT.'include/mail.inc.php';
		if (($mailmsg = PMail($configs['pb_smtpuser'],'test','test')) !== TRUE)
		{
			showMsg('setting_mailtest_failed');
		}
	}
	//transfer setting
	elseif ($job == 'transfer')
	{
		$configs['pb_jstransfer'] = (int)$config['pb_jstransfer'];
		$configs['pb_jstime'] = $config['pb_jstime'] > 0 ? (int)$config['pb_jstime'] : 900;
		$configs['pb_jsurl'] = trim($config['pb_jsurl']);
		if ($configs['pb_jsurl'])
		{
			$domain = explode('|', $configs['pb_jsurl']);
			foreach ($domain as $value)
			{
				!isDOMAIN($value) && showMsg('setting_illegal_jsdomain');
			}
		}
//		$configs['pb_trantags'] = $config['pb_trantags'] > 0 ? (int)$config['pb_trantags'] : 10;
//		$configs['pb_otherlink'] = $config['pb_otherlink'] > 0 ? (int)$config['pb_otherlink'] : 10;
		
	}
	//passport setting
	elseif ($job == 'passport')
	{
		$configs['pb_passport'] = (int)$config['pb_passport'];
		$configs['pb_passportkey'] = (!$config['pb_passportkey'] || strlen($config['pb_passportkey']) < 16) ? suggestKey(16) : trim($config['pb_passportkey']);
		$configs['pb_passporttype'] = $config['pb_passporttype'] == 'server' ? 'server' : 'client';
		$configs['pb_pclienturl'] = trim($config['pb_pclienturl']);
		$configs['pb_pclientregister'] = trim($config['pb_pclientregister']);
		$configs['pb_pclientlogin'] = trim($config['pb_pclientlogin']);
		$configs['pb_pclientlogout'] = trim($config['pb_pclientlogout']);
		$configs['pb_pserverapi'] = trim($config['pb_pserverapi']);

		if ($configs['pb_pclienturl'] && !preg_match('~^https?:\/\/[_a-z0-9\.\-/]+?\/$~is', $configs['pb_pclienturl']))
		{
			showMsg('setting_passport_pclienturl');
		}
		if ($configs['pb_pclientregister'] && !preg_match('~^[_a-z0-9\.\-/\?=]+?$~is', $configs['pb_pclientregister']))
		{
			showMsg('setting_passport_pclientregister');
		}
		if ($configs['pb_pclientlogin'] && !preg_match('~^[_a-z0-9\.\-/\?=]+?$~is', $configs['pb_pclientlogin']))
		{
			showMsg('setting_passport_pclientlogin');
		}
		if ($configs['pb_pclientlogout'] && !preg_match('~^[_a-z0-9\.\-/\?=]+?$~is', $configs['pb_pclientlogout']))
		{
			showMsg('setting_passport_pclientlogout');
		}
		if ($configs['pb_pserverapi'] && !preg_match('~^[_a-z0-9\.\-/\?=]+?$~is', $configs['pb_pserverapi']))
		{
			showMsg('setting_passport_pserverapi');
		}

		$configs['pb_ucenable'] = (int)$config['pb_ucenable'];
		$_uc['uc_host'] = trim($config['uc_host']);
		$_uc['uc_user'] = trim($config['uc_user']);
		$_uc['uc_password'] = trim($config['uc_password']);
		$_uc['uc_dbname'] = trim($config['uc_dbname']);
		$uc_charsets = array('gbk', 'utf8', 'big5', 'latin1');
		$_uc['uc_charset'] = in_array($config['uc_charset'], $uc_charsets) ? $config['uc_charset'] : 'gbk';
		$_uc['uc_prefix'] = $config['uc_prefix'];
		$_uc['uc_key'] = $config['uc_key'];
		$_uc['uc_url'] = $config['uc_url'];
		$_uc['uc_avatar'] = $config['uc_avatar'];
		$_uc['uc_msg'] = $config['uc_msg'] ? 1 : 0;
		$_uc['uc_friend'] = $config['uc_friend'] ? 1 : 0;
		$_uc['uc_space'] = $config['uc_space'];
		$_uc['uc_spaceurl'] = $config['uc_spaceurl'];
		if ($_uc['uc_spaceurl'] && !preg_match('~^https?:\/\/[_a-z0-9\.\-/]+?\/$~is', $_uc['uc_spaceurl']))
		{
			showMsg('setting_illegal_spaceurl');
		}
		if ($_uc['uc_url'] && !preg_match('~^https?:\/\/[_a-z0-9\.\-/]+?\/$~is', $_uc['uc_url']))
		{
			showMsg('setting_illegal_ucurl');
		}
		$_uc['uc_id'] = (int)$config['uc_id'];

		$ucmsg = <<<EOT
<?php

!defined('IN_PBDIGG') && exit('Access Denied!');

define('UC_CONNECT', 'mysql');						// mysql 是直接连接的数据库, 为了效率, 建议采用 mysql

define('UC_DBHOST', '{$_uc['uc_host']}');				// UCenter 数据库主机
define('UC_DBUSER', '{$_uc['uc_user']}');						// UCenter 数据库用户名
define('UC_DBPW', '{$_uc['uc_password']}');								// UCenter 数据库密码
define('UC_DBNAME', '{$_uc['uc_dbname']}');						// UCenter 数据库名称
define('UC_DBCHARSET', '{$_uc['uc_charset']}');						// UCenter 数据库字符集
define('UC_DBTABLEPRE', '`{$_uc['uc_dbname']}`.{$_uc['uc_prefix']}');				// UCenter 数据库表前缀
define('UC_DBCONNECT', 0);

//通信相关
define('UC_KEY', '{$_uc['uc_key']}');			// 与 UCenter 的通信密钥, 要与 UCenter 保持一致
define('UC_API', '{$_uc['uc_url']}');		// UCenter 的 URL 地址, 在调用头像时依赖此常量
define('UC_CHARSET', '{$_uc['uc_charset']}');						// UCenter 的字符集
define('UC_IP', '');								// UCenter 的 IP, 当 UC_CONNECT 为非 mysql 方式时, 并且当前应用服务器解析域名有问题时, 请设置此值
define('UC_APPID', {$_uc['uc_id']});								// 当前应用的 ID

\$uc_id = '{$_uc['uc_id']}';
\$uc_avatar = '{$_uc['uc_avatar']}';
\$uc_msg = '{$_uc['uc_msg']}';
\$uc_friend = '{$_uc['uc_friend']}';
\$uc_url = '{$_uc['uc_url']}';
\$uc_space = '{$_uc['uc_space']}';
\$uc_spaceurl = '{$_uc['uc_spaceurl']}';
?>
EOT;
//		if ($configs['pb_ucenable'] && $DB->fetch_first("SELECT uid FROM {$db_prefix}members WHERE ucuid = 0 LIMIT 1"))
//		{
//			$configs['pb_ucenable'] = 0;
//			redirect('setting_uc_import');
//		}
		PWriteFile(PBDIGG_ROOT.'include/uc.inc.php', $ucmsg, 'wb');
		updateConfig($_uc);
		$Cache->uc($_uc);
	}
	updateConfig($configs);
	$Cache->config();
	redirect('setting_success', $basename);
}
else
{
	//basic setting
	if ($job == 'basic')
	{
		$pb_sitedir != '/' && $pb_sitedir = trim($pb_sitedir, '/');
		$pb_icp = str_replace(array('<a href="http://www.miibeian.gov.cn" target="_blank">','</a>'), '', $pb_icp);
		radioChecked('ifopen_', $pb_ifopen);
		$editor = getEditor(array(array('id'=>'whyclosed','type'=>'Basic','content'=>$pb_whyclosed,'width'=>450,'height'=>200)));
	}
	//core setting
	elseif ($job == 'core')
	{
		radioChecked('gzip_', $pb_gzip);
		radioChecked('rewrite_', $pb_rewrite);
		radioChecked('getpw_', $pb_getpw);
		$pb_lang = siteLang($pb_lang);
		$pb_style = siteStyle($pb_style);
		radioChecked('timeformat_', $pb_timeformat);
		radioChecked('exectime_', $pb_exectime);
		radioChecked('robots_', $pb_robots);
		$pb_timezone = html_select($option_message['timezone'], 'config[pb_timezone]', $pb_timezone);
	}
	//view setting
	elseif ($job == 'view')
	{
		radioChecked('dformat_', $pb_dformat);
		radioChecked('titlelink_', $pb_titlelink);
		radioChecked('contentthumb_', $pb_contentthumb);
		radioChecked('mautoplay_', $pb_mautoplay);
		radioChecked('copyctrl_', $pb_copyctrl);
		radioChecked('taglink_', $pb_taglink);
		radioChecked('fautoplay_', $pb_fautoplay);
		radioChecked('topicthumb_', $pb_topicthumb);
		radioChecked('tagcolor_', $pb_tagcolor);

		$showsign_1 = $pb_showsign & 1 ? 'checked="checked"' : '';
		$showsign_2 = $pb_showsign & 2 ? 'checked="checked"' : '';
		$pb_torder = html_select($option_message['torder'], 'config[pb_torder]', $pb_torder, 'id="torder"');
		$pb_tday = html_select($option_message['tday'], 'config[pb_tday]', $pb_tday, 'id="tday"');
//		$pb_titleubb = html_checkbox($option_message['titleubb'],'config[pb_titleubb][]', explode("\t", $pb_titleubb));
		$checkstatus_b = ($pb_titleubb & 1) ? 'checked' : '';
		$checkstatus_i = ($pb_titleubb & 2) ? 'checked' : '';
		$checkstatus_u = ($pb_titleubb & 4) ? 'checked' : '';
		$checkstatus_c = ($pb_titleubb & 8) ? 'checked' : '';
		$pb_tubbtype = html_checkbox($option_message['contentubb'],'config[pb_tubbtype][]', explode("\t", $pb_tubbtype));
		$pb_cubbtype = html_checkbox($option_message['contentubb'],'config[pb_cubbtype][]', explode("\t", $pb_cubbtype));
		$pb_signubbtype = html_checkbox($option_message['signubb'],'config[pb_signubbtype][]', explode("\t", $pb_signubbtype), 4);
		$pb_corder = explode("\t", $pb_corder);
		$pb_corder_one = html_select($option_message['corder'],'config[pb_corder_one]', $pb_corder[0]);
		$pb_corder_two = html_select($option_message['orderby'],'config[pb_corder_two]', $pb_corder[1]);
		list($pb_topicthumbh, $pb_topicthumbw) = explode("\t", $pb_topicthumbsize);
		list($pb_topicstyleh, $pb_topicstylew) = explode("\t", $pb_topicstylesize);
		list($pb_contentthumbh, $pb_contentthumbw) = explode("\t", $pb_contentthumbsize);
		list($pb_previewh, $pb_previeww) = explode("\t", $pb_previewsize);
		list($pb_mplayerh, $pb_mplayerw) = explode("\t", $pb_mplayersize);
		list($pb_signh, $pb_signw) = explode("\t", $pb_signsize);
		list($pb_signimgh, $pb_signimgw) = explode("\t", $pb_signimgsize);
	}
	//atc setting
	elseif ($job == 'atc')
	{
		radioChecked('tcheck_', $pb_tcheck);
		radioChecked('ccheck_', $pb_ccheck);
		radioChecked('trackback_', $pb_trackback);
		radioChecked('tftopicimg_', $pb_tftopicimg);
		radioChecked('urlsaveimg_', $pb_urlsaveimg);
		radioChecked('anonnews_', $pb_anonnews);
		radioChecked('autoshield_', $pb_autoshield);
		radioChecked('ifdigg_', $pb_ifdigg);
		radioChecked('ifbury_', $pb_ifbury);
		radioChecked('ifpost_', $pb_ifpost);
		radioChecked('ifcomment_', $pb_ifcomment);
		list($pb_contentlenmin, $pb_contentlenmax) = explode("\t", $pb_contentlen);
		list($pb_titlelenmin, $pb_titlelenmax) = explode("\t", $pb_titlelen);
		list($pb_commentlenmin, $pb_commentlenmax) = explode("\t", $pb_commentlen);
		@include_once PBDIGG_ROOT.'data/cache/cache_words.php';
		$words_banned = implode(',', $words_banned);
		$words_replace = implode(',', $words_replace);
		$words_links = implode(',', $words_links);
	}
	//member setting
	elseif ($job == 'member')
	{
		include_once PBDIGG_ROOT.'data/cache/cache_reg.php';
		@extract($_REGCONFIG);
		radioChecked('selfad_', $pb_selfad);
		radioChecked('selfavat_', $pb_selfavat);
		radioChecked('avatupload_', $pb_avatupload);
		$pb_avatsize = floor($pb_avatsize / 1024);
		radioChecked('status_', $reg_status);
//		radioChecked('invite_', $reg_invite);
		radioChecked('emailactive_', $reg_emailactive);
		radioChecked('sendemail_', $reg_sendemail);
		$editor = getEditor(array(array('id'=>'reg_agreement','type'=>'Basic','content'=>$reg_agreement,'width'=>450,'height'=>200)));
	}
	//credit setting
	elseif ($job == 'credit')
	{
		$pb_creditdb = explode("\t", $pb_creditdb);
	}
	//attachment setting
	elseif ($job == 'attachment')
	{
		radioChecked('allowupload_', $pb_allowupload);
		radioChecked('uploadtopicimg_', $pb_uploadtopicimg);
		radioChecked('attoutlink_', $pb_attoutlink);
		radioChecked('attoutput_', $pb_attoutput);
		$pb_attachdir = html_select($option_message['attachdir'], 'config[pb_attachdir]', $pb_attachdir);
		$pb_uploadmaxsize = floor($pb_uploadmaxsize / 1024);
		$pb_outputmaxsize = floor($pb_outputmaxsize / 1024);
		$pb_watertype = html_select($option_message['watertype'], 'config[pb_watertype]', $pb_watertype);
		list($pb_waterminh, $pb_waterminw) = explode("\t", $pb_waterminsize);
		radioChecked('waterposition_', $pb_waterposition, 9);
		$gdstatus = (!function_exists('imagecreatefromgif') || !function_exists('imagettfbbox') || !function_exists('imagealphablending')) ? $admin_cp['water_warning'] : ''; 
	}
	//safe setting
	elseif ($job == 'safe')
	{
		$gdcheck = array();
		foreach (array(1,2,4,8,16) as $v)
		{
			($v & $pb_gdcheck) && $gdcheck[] = $v;
		}
		$pb_gdcheck = html_checkbox($option_message['gdcheck'],'config[pb_gdcheck][]', $gdcheck);
		$pb_gdcodetype = html_select($option_message['gdcodetype'], 'config[pb_gdcodetype]', $pb_gdcodetype);
		$pb_ipallow = unserialize($pb_ipallow);
		$pb_ipdeny = unserialize($pb_ipdeny);
		$pb_adminipallow = unserialize($pb_adminipallow);
		radioChecked('adminsafecode_', $pb_adminsafecode);
		radioChecked('regqa_', $pb_regqa);
	}
	//seo setting
	elseif ($job == 'seo')
	{
		$pb_rewriteext = html_select(array('html', 'htm', 'shtml', 'php', 'asp', 'jsp', 'cgi'), 'config[pb_rewriteext]', $pb_rewriteext, 'id="rewriteext"');
		radioChecked('extenable_', $pb_extenable);
	}
	//email setting
	elseif ($job == 'email')
	{
		radioChecked('mailtype_', $pb_mailtype);
		radioChecked('smtpauth_', $pb_smtpauth);
	}
	//transfer setting
	elseif ($job == 'transfer')
	{
		radioChecked('jstransfer_', $pb_jstransfer);
	}
	elseif ($job == 'passport')
	{
		@include_once PBDIGG_CROOT.'cache_uc.php';
//		$display_client = $pb_passporttype == 'client' ? 'block' : 'none';
		radioChecked('ucenable_', $pb_ucenable);
		radioChecked('passport_', $pb_passport);
		radioChecked('ucspace_', $uc_space);
		radioChecked('ucavatar_', $uc_avatar);
		radioChecked('ucmsg_', $uc_msg);
		radioChecked('ucfriend_', $uc_friend);
		${'passport_'.$pb_passporttype} = 'checked="checked"';
		$uc_charset = html_select(array('gbk'=>'gbk', 'utf8'=>'utf8', 'big5'=>'big5', 'latin1'=>'latin1'), 'config[uc_charset]', $uc_charset);
	}
}
function updateConfig($params)
{
	global $DB, $db_prefix;
	if (is_array($params))
	{
		foreach ($params as $key => $value)
		{
			$DB->fetch_one("SELECT title FROM {$db_prefix}configs WHERE title = '$key'") ? $DB->db_exec("UPDATE {$db_prefix}configs SET text = '$value' WHERE title = '$key'") : $DB->db_exec("INSERT INTO {$db_prefix}configs (title,text) VALUES ('$key','$value')");
		}
	}
}

?>
