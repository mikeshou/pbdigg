<?php
/**
 * @version $Id: ajax.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_PAGE', 'ajax');
require_once './include/common.inc.php';
require_once PBDIGG_ROOT.'include/validate.func.php';
require_once PBDIGG_ROOT.'include/ubb.func.php';
require_once PBDIGG_CROOT.'cache_reg.php';
$ajax_message = loadLang('ajax');
$common_message += $ajax_message;

$tid = (int)$tid;
$rid = (int)$rid;
$uid = (int)$customer['uid'];

$response = array('0','',$action.'_'.$tid.'_'.$rid);

switch ($action)
{
	case 'username':
		$username = convert_encoding('UTF-8', $db_charset, trim($username));
		$namelen = strlen($username);
		if ($namelen < $reg_minname)
		{
			$response[1] = $ajax_message['ajax_username_tooshort'];
		}
		elseif ($namelen > $reg_maxname)
		{
			$response[1] = $ajax_message['ajax_username_toolong'];
		}
		elseif (!preg_match('~^[_0-9a-z\x7f-\xff]+$~i', $username))
		{
			$response[1] = $common_message['member_illegal_name'];
		}
		elseif ($reg_bannames && (strpos($reg_bannames, $username) !== FALSE))
		{
			$response[1] = $common_message['member_banned_name'];
		}
		elseif ($DB->fetch_first("SELECT COUNT(*) num FROM {$db_prefix}members WHERE username = '$username'"))
		{
			$response[1] = $common_message['member_username_exist'];
		}
		$response[0] = $response[1] ? '0' : '1';
		break;

	case 'email':
		if (!isEMAIL($email))
		{
			$response[1] = $common_message['member_email_format_error'];
		}
		elseif ($DB->fetch_first("SELECT COUNT(*) num FROM {$db_prefix}members WHERE email = '$email'"))
		{
			$response[1] = $common_message['member_email_exist'];
		}
		$response[0] = $response[1] ? '0' : '1';
		break;

	case 'captcha':
		if (!ckgdcode($captcha))
		{
			$response[1] = $common_message['checkcode_error'];
		}
		$response[0] = $response[1] ? '0' : '1';
		break;
		
	case 'answer':
		if (!ckqa($answer))
		{
			$response[1] = $common_message['checkqa_error'];
		}
		$response[0] = $response[1] ? '0' : '1';
		break;

	case 'digg':
		if (!$pb_ifdigg || !$allowdigg)
		{
			$response[1] = $ajax_message['ajax_action_disabled'];
			break;
		}
		$pb_creditdb = explode("\t", $pb_creditdb);
		if ($tid && $rid)
		{
			$diggcredit = intval($pb_creditdb[5]);
			$rdigged = gCookie('pb_rdigged');
			$rs = $DB->fetch_one("SELECT c.rid, c.cid, c.tid, c.digg, c.ifcheck cifcheck, c.ifshield, t.ifcheck tifcheck  
									FROM {$db_prefix}comments c LEFT JOIN {$db_prefix}threads t USING (tid) 
									WHERE c.rid = '$rid' AND c.tid = '$tid'");
			if ($rs && $rs['cifcheck'] && $rs['tifcheck'] && !$rs['ifshield'])
			{
				if ($logStatus && ($DB->fetch_first("SELECT COUNT(*) num FROM {$db_prefix}cdata WHERE tid = '$tid' AND rid = '$rid' AND uid = '$uid' AND type = 'digg'")) || strpos($rdigged, ",$rid,") !== FALSE)
				{
					$response[1] = $ajax_message['ajax_dbaction_repeat'];
				}
				else
				{
					$DB->db_exec("UPDATE {$db_prefix}comments SET digg = digg + 1, diggdate = '$timestamp' WHERE rid = '$rid' LIMIT 1");
					if ($logStatus)
					{
						$DB->db_exec("UPDATE {$db_prefix}members SET diggnum = diggnum + 1, currency = currency + $diggcredit WHERE uid = '$uid'");
						$DB->db_exec("INSERT INTO {$db_prefix}cdata (cid, tid, rid, uid, type) VALUES(".$rs['cid'].", '$tid', '$rid', '$uid', 'digg')");
					}
					else
					{
						sCookie('pb_rdigged', $rdigged.($rdigged ? '' : ',').$rid.',', 86400);
					}
					$response[0] = '1';
					$response[1] = ++$rs['digg'];
				}
			}
			else
			{
				$response[1] = $ajax_message['ajax_action_disabled'];
			}
		}
		else
		{
			$diggcredit = intval($pb_creditdb[4]);
			$tdigged = gCookie('pb_tdigged');
			$rs = $DB->fetch_one("SELECT cid, tid, digg, bury, views, comments, ifcheck, ifshield, pbrank, postdate FROM {$db_prefix}threads WHERE tid = '$tid'");
			if ($rs && $rs['ifcheck'] && !$rs['ifshield'])
			{
				if ($logStatus && ($DB->fetch_first("SELECT COUNT(*) num FROM {$db_prefix}tdata WHERE tid = '$tid' AND uid = '$uid' AND type = 'digg'")) || strpos($tdigged, ','.$tid.',') !== FALSE)
				{
					$response[1] = $ajax_message['ajax_dbaction_repeat'];
				}
				else
				{
					$pbrank = pbrank($rs['pbrank'], ++$rs['digg'], $rs['bury'], $rs['views'], $rs['comments'], $rs['postdate']);
					$DB->db_exec("UPDATE {$db_prefix}threads SET digg = digg + 1, diggdate = '$timestamp', pbrank = '$pbrank' WHERE tid = '$tid'");
					if ($logStatus)
					{
						$DB->db_exec("UPDATE {$db_prefix}members SET diggnum = diggnum + 1, currency = currency + $diggcredit WHERE uid = '$uid'");
						$DB->db_exec("INSERT INTO {$db_prefix}tdata (cid, tid, uid, type) VALUES(".$rs['cid'].", '$tid', '$uid', 'digg')");
					}
					else
					{
						sCookie('pb_tdigged', $tdigged.($tdigged ? '' : ',').$tid.',', 86400);
					}
					$response[0] = '1';
					$response[1] = $rs['digg'];
				}
			}
			else
			{
				$response[1] = $ajax_message['ajax_action_disabled'];
			}
		}
		break;

	case 'bury':
		if (!$pb_ifbury || !$allowbury)
		{
			$response[1] = $ajax_message['ajax_action_disabled'];
			break;
		}
		$pb_creditdb = explode("\t", $pb_creditdb);
		if ($tid && $rid)
		{
			$burycredit = intval($pb_creditdb[7]);
			$rburied = gCookie('pb_rburied');
			$rs = $DB->fetch_one("SELECT c.rid, c.cid, c.tid, c.bury, c.ifcheck cifcheck, c.ifshield, t.ifcheck tifcheck  
									FROM {$db_prefix}comments c LEFT JOIN {$db_prefix}threads t USING (tid) 
									WHERE c.rid = '$rid' AND c.tid = '$tid'");
			if ($rs && $rs['cifcheck'] && $rs['tifcheck'] && !$rs['ifshield'])
			{
				if ($logStatus && ($DB->fetch_first("SELECT COUNT(*) num FROM {$db_prefix}cdata WHERE tid = '$tid' AND rid = '$rid' AND uid = '$uid' AND type = 'bury'")) || strpos($rburied, ",$rid,") !== FALSE)
				{
					$response[1] = $ajax_message['ajax_dbaction_repeat'];
				}
				else
				{
					$DB->db_exec("UPDATE {$db_prefix}comments SET bury = bury + 1, burydate = '$timestamp' WHERE rid = '$rid' LIMIT 1");
					if ($logStatus)
					{
						$DB->db_exec("UPDATE {$db_prefix}members SET burynum = burynum + 1, currency = currency + $burycredit WHERE uid = '$uid'");
						$DB->db_exec("INSERT INTO {$db_prefix}cdata (cid, tid, rid, uid, type) VALUES(".$rs['cid'].", '$tid', '$rid', '$uid', 'bury')");
					}
					else
					{
						sCookie('pb_rburied', $rburied.($rburied ? '' : ',').$rid.',', 86400);
					}
					$response[0] = '1';
					$response[1] = ++$rs['bury'];
				}
			}
			else
			{
				$response[1] = $ajax_message['ajax_action_disabled'];
			}
		}
		else
		{
			$burycredit = intval($pb_creditdb[6]);
			$tburied = gCookie('pb_tburied');
			$rs = $DB->fetch_one("SELECT cid, tid, digg, bury, views, comments, ifcheck, ifshield, pbrank, postdate FROM {$db_prefix}threads WHERE tid = '$tid'");
			if ($rs && $rs['ifcheck'] && !$rs['ifshield'])
			{
				if ($logStatus && ($DB->fetch_first("SELECT COUNT(*) num FROM {$db_prefix}tdata WHERE tid = '$tid' AND uid = '$uid' AND type = 'bury'")) || strpos($tburied, ','.$tid.',') !== FALSE)
				{
					$response[1] = $ajax_message['ajax_dbaction_repeat'];
				}
				else
				{
					$pbrank = pbrank($rs['pbrank'], $rs['digg'], ++$rs['bury'], $rs['views'], $rs['comments'], $rs['postdate']);
					$DB->db_exec("UPDATE {$db_prefix}threads SET bury = bury + 1, burydate = '$timestamp', pbrank = '$pbrank' WHERE tid = '$tid'");
					if ($logStatus)
					{
						$DB->db_exec("UPDATE {$db_prefix}members SET burynum = burynum + 1, currency = currency + $burycredit WHERE uid = '$uid'");
						$DB->db_exec("INSERT INTO {$db_prefix}tdata (cid, tid, uid, type) VALUES('".$rs['cid']."', '$tid', '$uid', 'bury')");
					}
					else
					{
						sCookie('pb_tburied', $tburied.($tburied ? '' : ',').$tid.',', 86400);
					}
					$response[0] = '1';
					$response[1] = $rs['bury'];
				}
			}
			else
			{
				$response[1] = $ajax_message['ajax_action_disabled'];
			}
		}
		break;

	case 'comment':
		if ($pb_reposttime)
		{
			$pb_lastcomment = $logStatus ? $customer['lastcomment'] : gCookie('pb_lastcomment');
			if ($pb_lastcomment && $pb_reposttime && ($timestamp - $pb_lastcomment < $pb_reposttime))
			{
				$response[1] = $ajax_message['ajax_flood_ctrl'];
				break;
			}
			sCookie('pb_lastcomment', $timestamp, 1);
		}

		if ((!$allowcomment || !$pb_ifcomment) && !SUPERMANAGER)
		{
			$response[1] = $ajax_message['ajax_comment_nopermission'];
			break;
		}
		$comment = convert_encoding('UTF-8', $db_charset, stripslashes($content));
		!$allowhtml && $comment = HConvert($comment);
		if ($ck = checkComment($comment))
		{
			$response[1] = $ck;
			break;
		}
		if (($pb_gdcheck & 8) && !ckgdcode($captcha))
		{
			$response[1] = $common_message['checkcode_error'];
			break;
		}
		$rs = $DB->fetch_one("SELECT cid, ifcheck, iflock, digg, bury, views, comments, pbrank, postdate FROM {$db_prefix}threads WHERE tid = '$tid'");
		if (!$rs || $rs['iflock'] || !$rs['ifcheck'])
		{
			$response[1] = $ajax_message['ajax_action_disabled'];
			break;
		}
		$cid = (int)$rs['cid'];
		$pb_creditdb = explode("\t", $pb_creditdb);
		$ifconvert = $comment == conentUBB($comment, 'c', true) ? 0 : 1;
		$ifcheck = (($pb_ccheck && !SUPERMANAGER) || ($pb_anonnews && !$uid)) ? 0 : 1;
		$DB->db_exec("INSERT INTO {$db_prefix}comments (rid, cid, tid, author, uid, content, ifcheck, ifshield, ifconvert, postdate, postip, digg, diggdate, bury, burydate) 
						VALUES (NULL, ".$rs['cid'].", '$tid', '".addslashes($customer['username'])."', '$uid', '".addslashes($comment)."', $ifcheck, 0, '$ifconvert', '$timestamp', '".$_PBENV['PB_IP']."', 0, 0, 0, 0)");
		$rid = intval($DB->db_insert_id());
		$pbrank = pbrank($rs['pbrank'], $rs['digg'], $rs['bury'], $rs['views'], ++$rs['comments'], $rs['postdate']);
		if ($logStatus)
		{
			$DB->db_exec("UPDATE {$db_prefix}members m, {$db_prefix}threads t, {$db_prefix}categories c 
							SET m.commentnum = m.commentnum + 1, m.lastcomment = '$timestamp', m.currency = m.currency + " . intval($pb_creditdb[1]) .", t.comments = t.comments + 1, t.commentdate = '$timestamp', t.pbrank = '$pbrank', c.cnum = c.cnum + 1 
							WHERE t.tid = '$tid' AND c.cid = '$cid' AND m.uid = '$uid'");
		}
		else
		{
			$DB->db_exec("UPDATE {$db_prefix}threads t, {$db_prefix}categories c 
					SET t.comments = t.comments + 1, c.cnum = c.cnum + 1, t.commentdate = '$timestamp', t.pbrank = '$pbrank' 
					WHERE t.tid = '$tid' AND c.cid = '$cid'");
		}
		$DB->db_exec("UPDATE {$db_prefix}sitestat SET comnum = comnum + 1 WHERE id = 1");
		
		$response[0] = '1';
	
		sCookie('pb_lastcomment', $timestamp);
		break;

	case 'editcomment':
		$editcomment = $DB->fetch_one("SELECT c.rid, c.cid, c.tid, c.uid, c.content, c.ifcheck, m.adminid FROM {$db_prefix}comments c LEFT JOIN {$db_prefix}members m USING (uid) WHERE c.rid = '$rid'");
		if ($editcomment && (SUPERMANAGER || ($customer['adminid'] && $alloweditatc && (!$editcomment['adminid'] || $editcomment['adminid'] >= $customer['adminid'])) || ($editcomment['uid'] == $customer['uid'] && $customer['uid'])))
		{
			if (checkPost())
			{
				$comment = convert_encoding('UTF-8', $db_charset, stripslashes($comment));
				!$allowhtml && $comment = HConvert($comment);
				if ($ck = checkComment($comment))
				{
					$response[1] = $ck;
				}
				else
				{
					if ($comment != $editcomment['content'])
					{
						$ifconvert = $comment == conentUBB($comment, 'c', true) ? 0 : 1;
						$DB->db_exec("UPDATE {$db_prefix}comments SET content = '".addslashes($comment)."', ifconvert = '$ifconvert' WHERE rid = '$rid'");
					}
					$response[0] = '1';
					$response[1] = conentUBB(stripslashes($comment), 'c');
					
				}
			}
			else
			{
				$response[0] = '1';
				$response[1] = getAjaxTPL();
			}
		}
		else
		{
			$response[1] = $ajax_message['ajax_action_disabled'];
		}
		break;

	case 'delcomment':
		$creditdb = explode("\t", $pb_creditdb);
		$rs = $DB->fetch_one("SELECT c.uid, c.cid, c.tid, m.adminid FROM {$db_prefix}comments c LEFT JOIN {$db_prefix}members m USING (uid) WHERE c.rid = '$rid'");
		if ($rs && (SUPERMANAGER || ($customer['adminid'] && $allowdelatc && (!$rs['adminid'] || $rs['adminid'] >= $customer['adminid'])) || ($rs['uid'] == $customer['uid'] && $customer['uid'])))
		{
			if (checkPost())
			{
				$uid = (int)$rs['uid'];
				$cid = (int)$rs['cid'];
				$uid && $DB->db_exec("UPDATE {$db_prefix}members SET commentnum = commentnum - 1, currency = currency - ".intval($creditdb[3])." WHERE uid = '$uid'");
				$DB->db_exec("UPDATE {$db_prefix}threads SET comments = comments - 1 WHERE tid = '$tid'");
				$DB->db_exec("UPDATE {$db_prefix}categories SET cnum = cnum - 1 WHERE cid = '$cid'");
				$DB->db_exec("DELETE FROM {$db_prefix}comments WHERE rid = '$rid'");
				$DB->db_exec("DELETE FROM {$db_prefix}cdata WHERE rid = '$rid'");
				$DB->db_exec("UPDATE {$db_prefix}sitestat SET comnum = comnum - 1 WHERE id = 1");
				$DB->db_exec("INSERT INTO {$db_prefix}commonlogs (uid, action, description, logdate, logip) VALUES (".$customer['uid'].", '$action', '".$ajax_message['ajax_del_comment'].' RID:'.$rid."', $timestamp, '".$_PBENV['PB_IP']."')");
			}
			else
			{
				$response[1] = getAjaxTPL();
			}
			$response[0] = '1';
		}
		else
		{
			$response[1] = $ajax_message['ajax_action_disabled'];
		}
		break;
	
	case 'checkcomment':
	
		//审核评论
		$rs = $DB->fetch_one("SELECT ifcheck FROM {$db_prefix}comments WHERE rid = '$rid'");
		if ($rs && $customer['adminid'] && $allowcheckatc)
		{
			if (checkPost())
			{
				$checkstatus = $rs['ifcheck'] ? 0 : 1;
				$DB->db_exec("UPDATE {$db_prefix}comments SET ifcheck = $checkstatus WHERE rid = '$rid'");
				$DB->db_exec("INSERT INTO {$db_prefix}commonlogs (uid, action, description, logdate, logip) VALUES (".$customer['uid'].", '$action', '".$ajax_message['ajax_check_comment'].' TID:'.$tid.' RID:'.$rid."', $timestamp, '".$_PBENV['PB_IP']."')");
			}
			else
			{
				$actionlang = $rs['ifcheck'] ? $ajax_message['ajax_uncheck_action'] : $ajax_message['ajax_check_action'];
				$response[1] = getAjaxTPL();
			}
			$response[0] = '1';
		}
		else
		{
			$response[1] = $ajax_message['ajax_action_disabled'];
		}
		break;


	case 'shieldcomment':
	
		//屏蔽评论
		$rs = $DB->fetch_one("SELECT ifshield FROM {$db_prefix}comments WHERE rid = '$rid'");
		if ($rs && $customer['adminid'] && $allowshield)
		{
			if (checkPost())
			{
				$shieldstatus = $rs['ifshield'] ? 0 : 1;
				$DB->db_exec("UPDATE {$db_prefix}comments SET ifshield = $shieldstatus WHERE rid = '$rid'");
				$DB->db_exec("INSERT INTO {$db_prefix}commonlogs (uid, action, description, logdate, logip) VALUES (".$customer['uid'].", '$action', '".$ajax_message['ajax_shield_comment'].' TID:'.$tid.' RID:'.$rid."', $timestamp, '".$_PBENV['PB_IP']."')");
			}
			else
			{
				$actionlang = $rs['ifshield'] ? $ajax_message['ajax_unshield_action'] : $ajax_message['ajax_shield_action'];
				$response[1] = getAjaxTPL();
			}
			$response[0] = '1';
		}
		else
		{
			$response[1] = $ajax_message['ajax_action_disabled'];
		}
		break;
	
	case 'showcomment':
		$p = $p < 1 ? 1 : (int)$p;
		$article = $DB->fetch_one("SELECT tid, ifcheck, comments FROM {$db_prefix}threads WHERE tid = '$tid'");

		if ($article && $article['ifcheck'] && $article['comments'])
		{
			//评论采用ajax分页
			$conditionsql = $fieldsql = '';
			$comments = array();
			$pagesize = (int)$pb_cperpage;
			$i = ($p - 1) * $pagesize + 1;
			$commentMultLink = commentMultLink($p, $article['comments'], 'showcomment', $pagesize);
			$limit = sqlLimit($p, $pagesize);

			!$allowcheckatc && $conditionsql .= ' AND c.ifcheck = 1';
			if ($pb_showsign & 2)
			{
				list($pb_signh, $pb_signw) = explode("\t", $pb_signsize);
				$fieldsql .= ',mx.signature,mx.showsign,mx.ctsig ';
			}
			list($ordersql, $bysql) = explode("\t", $pb_corder);
			
			$query = $DB->db_query("SELECT c.rid,c.cid,c.tid,c.author,c.uid,c.content,c.ifcheck,c.ifshield,c.ifconvert,c.postdate,c.postip,c.digg,c.diggdate,c.bury,c.burydate,
									m.username,m.email,m.adminid,m.groupid,m.publicemail,m.gender,m.regdate,m.realgroup,m.postnum,m.commentnum,m.diggnum,m.burynum,m.currency,m.lastvisit,m.lastpost,m.lastcomment,m.uploadnum,m.friendnum,m.collectionnum,m.visitnum,m.ucuid,m.avatar,
									mx.qq,mx.msn,mx.site,mx.location,mx.birthday {$fieldsql}
									FROM {$db_prefix}comments c 
									LEFT JOIN {$db_prefix}members m 
									ON c.uid = m.uid 
									LEFT JOIN {$db_prefix}memberexp mx 
									ON m.uid = mx.uid 
									WHERE c.tid = '$tid' {$conditionsql}
									ORDER BY c.{$ordersql} {$bysql} {$limit}");
			while ($comment = $DB->fetch_all($query))
			{
				$comment['postdate'] = formatPostTime($comment['postdate']);
				!$comment['author'] && $comment['author'] = $common_message['anonymity'];
				
				//会员签名
				if (($pb_showsign & 2) && $comment['showsign'] && $comment['signature'])
				{
					$comment['ctsig'] && $comment['signature'] = signUBB($comment['signature']);
					
					$comment['signature'] = '<div id="signature_"'.$i.' style="'.($pb_signh ? 'max-height:'.$pb_signh.'px;maxHeight:'.$pb_signh.'px;' : '').($pb_signw ? 'max-width:'.$pb_signw.'px;maxWidth:'.$pb_signw.'px;' : '').'overflow:hidden;">'.preg_replace('~(?:\r\n|\n\r|\r|\n)~', '<br />', $comment['signature']).'</div>';
				}
				else
				{
					$comment['signature'] = '';
				}
		
				switch ($comment['gender'])
				{
					case '0':
						$comment['gender_name'] = $common_message['male'];
						break;
					case '1':
						$comment['gender_name'] = $common_message['female'];
						break;
					default:
						$comment['gender_name'] = $common_message['secrecy'];
						break;
				}
				$comment['avatar'] = userFace($comment['avatar'], $comment['uid']);
				$comment['uurl'] = userSpace($comment['uid'], $comment['ucuid']);
		
//				$comment['digged'] = strpos(gCookie('pb_rdigged'), ','.$comment['rid'].',') === FALSE ? 0 : 1;
//				$comment['buryed'] = strpos(gCookie('pb_rburied'), ','.$comment['rid'].',') === FALSE ? 0 : 1;
		
				$contentShield = $shieldNotice = '';
				if ($comment['ifshield'] || $comment['groupid'] == 3 && $pb_autoshield)
				{
					if ($customer['adminid'])
					{
						$shieldNotice = articleShield('show_admin_shield');
					}
					else
					{
						$comment['content'] = articleShield($comment['groupid'] == 3 ? 'show_auto_shield' : 'show_content_shield');
						$contentShield = true;
					}
				}
				if (!$contentShield)
				{
					$comment['content'] = $shieldNotice.$comment['content'];
					$comment['ifconvert'] && $comment['content'] = conentUBB($comment['content'], 'c');
					$comment['content'] = preg_replace('~(?:\r\n|\n\r|\r|\n)~i', '<br />', $comment['content']);
				}
				$comment['alloweditatc'] = $comment['allowdelatc'] = $comment['allowcheckatc'] = $comment['allowshield'] = '';
				//管理权限
				if ($customer['adminid'])
				{
					$comment['alloweditatc'] = $comment['allowdelatc'] = $comment['allowcheckatc'] = $comment['allowshield'] = 1;
				}
				if ($logStatus && ($customer['uid'] == $comment['uid']))
				{
					$comment['alloweditatc'] = $comment['allowdelatc'] = 1;
				}
				$comments[$i++] = $comment;
			}
			$response[1] = getAjaxTPL('comment');
		}
		else
		{
			$response[1] = $ajax_message['ajax_action_disabled'];
		}
		break;

	case 'delarticle':
		$creditdb = explode("\t", $pb_creditdb);
		$article = $DB->fetch_one("SELECT t.uid, t.cid, t.tid, t.module, t.postdate, t.topicimg, m.adminid FROM {$db_prefix}threads t LEFT JOIN {$db_prefix}members m USING (uid) WHERE t.tid = '$tid'");
		if ($article && (SUPERMANAGER || ($customer['adminid'] && $allowdelatc && (!$article['adminid'] || $article['adminid'] >= $customer['adminid'])) || ($article['uid'] == $customer['uid'] && $customer['uid'] && (!$pb_reeditlimit || $timestamp - $article['postdate'] < $pb_reeditlimit * 60))))
		{
			if (checkPost())
			{
				$uid = (int)$article['uid'];
				$cid = (int)$article['cid'];
				$moduleid = (int)$article['module'];

				$DB->db_exec("UPDATE {$db_prefix}members SET postnum = postnum -1, currency = currency - ".intval($creditdb[2])." WHERE uid = '$uid'");
				$commentuid = array();
				$query = $DB->db_query("SELECT uid FROM {$db_prefix}comments WHERE tid = '$tid'");
				$cnum = intval($DB->db_num($query));
				while ($rs = $DB->fetch_all($query))
				{
					$rs['uid'] && $commentuid[$rs['uid']]++;
				}
				if ($commentuid)
				{
					foreach ($commentuid as $key => $value)
					{
						$DB->db_exec("UPDATE {$db_prefix}members SET commentnum = commentnum - $value, currency = currency - ".($value * intval($creditdb[3]))." WHERE uid = '$key'");
					}
				}
				$DB->db_exec("UPDATE {$db_prefix}categories SET cnum = cnum - $cnum, tnum = tnum - 1 WHERE cid = '$cid'");
				$DB->db_exec("DELETE FROM {$db_prefix}threads WHERE tid = '$tid'");
				$DB->db_exec("DELETE FROM {$db_prefix}tdata WHERE tid = '$tid'");
				$DB->db_exec("DELETE FROM {$db_prefix}comments WHERE tid  = '$tid'");
				$DB->db_exec("DELETE FROM {$db_prefix}cdata WHERE tid = '$tid'");
				/**** tag ***/
				$query = $DB->db_query("SELECT tagid FROM {$db_prefix}tagcache WHERE tid = '$tid'");
				$tagid = '';
				while ($rs = $DB->fetch_all($query))
				{
					$tagid .= ($tagid ? ',' : '').$rs['tagid'];
				}
				if ($tagid)
				{
					$DB->db_exec("UPDATE {$db_prefix}tags SET usenum = usenum - 1 WHERE tagid IN ($tagid)");
					$DB->db_exec("DELETE FROM {$db_prefix}tagcache WHERE tid = $tid");
				}
				if ($article['topicimg'])
				{
					list($tpath, $th, $tw, $ttype) = explode('|', $article['topicimg']);
					$ttype == '0' && PDel(PBDIGG_ATTACHMENT.'topic/'.$tpath);
				}
				require_once PBDIGG_ROOT.'include/attachment.func.php';
				delAttachment("tid = '$tid'");
				$currentModuleData = $module->getModuleObject($moduleid);
				$currentModuleData && $currentModuleData->del($tid);
				$DB->db_exec("UPDATE {$db_prefix}sitestat SET artnum = artnum - 1, comnum = comnum - $cnum WHERE id = 1");
				$DB->db_exec("INSERT INTO {$db_prefix}commonlogs (uid, action, description, logdate, logip) VALUES (".$customer['uid'].", '$action', '".$ajax_message['ajax_del_article'].' TID:'.$tid."', $timestamp, '".$_PBENV['PB_IP']."')");
				$Cache->tplvar();
				$response[1] = 'location.href="'.$_PBENV['PB_URL'].'"';
			}
			else
			{
				$response[1] = getAjaxTPL();
			}
			$response[0] = '1';
		}
		else
		{
			$response[1] = $ajax_message['ajax_action_disabled'];
		}
		break;
	
	case 'copyarticle':
		$article = $DB->fetch_one("SELECT cid, module, keywords, topicimg FROM {$db_prefix}threads WHERE tid = '$tid'");
		if ($article && $customer['adminid'] && $allowcopyatc)
		{
			$currentCateData = &$_categories[$article['cid']];
			if (!$currentCateData)
			{
				$response[1] = $ajax_message['ajax_action_disabled'];
				break;
			}
			if (checkPost())
			{
				$targetcid = (int)$targetcid;
				$moduleid = $article['module'];

				if ($article['cid'] == $targetcid)
				{
					$response[1] = $ajax_message['ajax_same_cid'];
					break;
				}
				if (!in_array($moduleid, $_categories[$targetcid]['ttype']))
				{
					$response[1] = $ajax_message['ajax_module_nonexistence'];
					break;
				}

				$topicimg = $h = $w = '';
				
				if ($article['topicimg'])
				{
					list($tpath, $th, $tw, $ttype) = explode('|', $article['topicimg']);
					if (!$ttype)
					{
						$topicimg = substr($tpath, 0, strrpos($tpath, '/')).'/'.md5($tpath).'.'.Fext($tpath);
						PCopy(PBDIGG_ATTACHMENT.'topic/'.$tpath, PBDIGG_ATTACHMENT.'topic/'.$topicimg);
						$topicimg .= '|'.$th.'|'.$tw.'|0';
					}
					else
					{
						$topicimg = $article['topicimg'];
					}
				}

				$DB->db_exec("INSERT INTO {$db_prefix}threads (cid, author, uid, subject, contentlink, linkhost, topicimg, ifcheck, ifshield, iflock, topped, postdate, postip, digg, diggdate, bury, burydate, views, comments, commentdate, keywords, pbrank, commend, commendpic, first, module, realurl, ishtml, summary, titlecolor, titlestyle) SELECT '$targetcid', author, uid, subject, contentlink, linkhost, '$topicimg', ifcheck, ifshield, iflock, topped, postdate, postip, digg, diggdate, bury, burydate, views, comments, commentdate, keywords, pbrank, commend, commendpic, first, module, realurl, ishtml, summary, titlecolor, titlestyle FROM {$db_prefix}threads WHERE tid = '$tid'");
				$newtid = $DB->db_insert_id();
				
				//附件
				$atta_replace = array();
				$query = $DB->db_query("SELECT * FROM {$db_prefix}attachments WHERE tid = '$tid'");
				while ($rs = $DB->fetch_all($query))
				{
					$real_path = $rs['filepath'];
					list ($filename, $ext) = explode('.', substr(strrchr($real_path, '/'), 1));
					$dir = substr($real_path, 0, strrpos($real_path, '/'));
					$newfilename = md5($filename);
					$newfilepath = $dir.'/'.$newfilename.'.'.$ext;
					PCopy(PBDIGG_ATTACHMENT.$rs['filepath'], PBDIGG_ATTACHMENT.$newfilepath);
					if ($rs['thumb'])
					{
						PCopy(PBDIGG_ATTACHMENT.'thumb_'.$rs['filepath'], PBDIGG_ATTACHMENT.$dir.'/thumb_'.$newfilename.'.'.$ext);
					}
					$DB->db_exec("INSERT INTO {$db_prefix}attachments (cid, tid, uid, filename, filetype, filesize, description, filepath, downloads, isimg, uploaddate, thumb) SELECT '$targetcid', '$newtid', uid, filename, filetype, filesize, description, '$newfilepath', 0, isimg, uploaddate, thumb FROM {$db_prefix}attachments WHERE aid = '".$rs['aid']."'");
					$atta_replace[$rs['aid']] = $DB->db_insert_id();
				}
				if ($article['keywords'])
				{
					$query = $DB->db_query("SELECT t.tagid FROM {$db_prefix}tags t, {$db_prefix}tagcache tc WHERE tc.tagid = t.tagid AND tc.tid = '$tid'");
					$tagid = $tagcache = '';
					while ($rs = $DB->fetch_all($query))
					{
						$tagid .= $rs['tagid'].',';
						$tagcache .= '('.$rs['tagid'].', '.$newtid.'),';
					}
					if ($tagid && $tagcache)
					{
						$tagid = substr($tagid, 0, -1);
						$tagcache = substr($tagcache, 0, -1);
						$DB->db_exec("UPDATE {$db_prefix}tags SET usenum = usenum + 1 WHERE tagid IN ($tagid)");
						$DB->db_exec("INSERT INTO {$db_prefix}tagcache (tagid, tid) VALUES $tagcache");
					}
				}
				$currentModuleData = $module->getModuleObject($moduleid);
				$currentModuleData && $currentModuleData->copy($tid, $newtid, $targetcid, $atta_replace);
				$DB->db_exec("UPDATE {$db_prefix}categories SET tnum = tnum + 1 WHERE cid = '$targetcid'");
				$DB->db_exec("UPDATE {$db_prefix}sitestat SET artnum = artnum + 1 WHERE id = 1");
				$DB->db_exec("INSERT INTO {$db_prefix}commonlogs (uid, action, description, logdate, logip) VALUES (".$customer['uid'].", '$action', '".$ajax_message['ajax_copy_article'].' TID:'.$tid."', $timestamp, '".$_PBENV['PB_IP']."')");
			}
			else
			{
				$cateoption = '';
				cateOption($_categories, $cateoption);
				$cateoption = "<select name=\"targetcid\" id=\"targetcid\">".$cateoption."</select>";
				$response[1] = getAjaxTPL();
			}
			$response[0] = '1';
		}
		else
		{
			$response[1] = $ajax_message['ajax_action_disabled'];
		}
		break;

	case 'movearticle':
		$article = $DB->fetch_one("SELECT cid, module, comments FROM {$db_prefix}threads WHERE tid = '$tid'");
		if ($article && $customer['adminid'] && $allowmoveatc)
		{
			$currentCateData = &$_categories[$article['cid']];
			if (!$currentCateData)
			{
				$response[1] = $ajax_message['ajax_action_disabled'];
				break;
			}
			if (checkPost())
			{
				$targetcid = (int)$targetcid;
				$oldcid = $article['cid'];
				$comments = $article['comments'];
				$moduleid = $article['module'];

				if ($oldcid == $targetcid)
				{
					$response[1] = $ajax_message['ajax_same_cid'];
					break;
				}
				if (!in_array($moduleid, $_categories[$targetcid]['ttype']))
				{
					$response[1] = $ajax_message['ajax_module_nonexistence'];
					break;
				}
				$DB->db_exec("UPDATE {$db_prefix}threads SET cid = '$targetcid' WHERE tid = '$tid'");
				$DB->db_exec("UPDATE {$db_prefix}comments SET cid = '$targetcid' WHERE tid = '$tid'");
				$DB->db_exec("UPDATE {$db_prefix}attachments SET cid = '$targetcid' WHERE tid = '$tid'");
				$DB->db_exec("UPDATE {$db_prefix}categories SET tnum = tnum + 1, cnum = cnum + $comments WHERE cid = '$targetcid'");
				$DB->db_exec("UPDATE {$db_prefix}categories SET tnum = tnum - 1, cnum = cnum - $comments WHERE cid = '$oldcid'");
				$DB->db_exec("UPDATE {$db_prefix}cdata SET cid = '$targetcid' WHERE tid = '$tid'");
				$DB->db_exec("UPDATE {$db_prefix}tdata SET cid = '$targetcid' WHERE tid = '$tid'");
				$currentModuleData = $module->getModuleObject($moduleid);
				$currentModuleData && $currentModuleData->move($tid, $oldcid, $targetcid);
				$DB->db_exec("INSERT INTO {$db_prefix}commonlogs (uid, action, description, logdate, logip) VALUES (".$customer['uid'].", '$action', '".$ajax_message['ajax_move_article'].' TID:'.$tid."', $timestamp, '".$_PBENV['PB_IP']."')");
			}
			else
			{
				$cateoption = '';
				cateOption($_categories, $cateoption);
				$cateoption = "<select name=\"targetcid\" id=\"targetcid\">".$cateoption."</select>";
				$response[1] = getAjaxTPL();
			}
			$response[0] = '1';
		}
		else
		{
			$response[1] = $ajax_message['ajax_action_disabled'];
		}
		break;
	
	case 'checkarticle':
	
		//审核
		$rs = $DB->fetch_one("SELECT ifcheck FROM {$db_prefix}threads WHERE tid = '$tid'");
		if ($rs && $customer['adminid'] && $allowcheckatc)
		{
			if (checkPost())
			{
				$DB->db_exec("UPDATE {$db_prefix}threads SET ifcheck = ifcheck ^ 1 WHERE tid = '$tid'");
				$DB->db_exec("INSERT INTO {$db_prefix}commonlogs (uid, action, description, logdate, logip) VALUES (".$customer['uid'].", '$action', '".$ajax_message['ajax_check_article'].' TID:'.$tid.' RID:'.$rid."', $timestamp, '".$_PBENV['PB_IP']."')");
			}
			else
			{
				$actionlang = $rs['ifcheck'] ? $ajax_message['ajax_uncheck_action'] : $ajax_message['ajax_check_action'];
				$response[1] = getAjaxTPL();
			}
			$response[0] = '1';
		}
		else
		{
			$response[1] = $ajax_message['ajax_action_disabled'];
		}
		break;

	case 'lockarticle':
		//锁定
		$rs = $DB->fetch_one("SELECT iflock FROM {$db_prefix}threads WHERE tid = '$tid'");
		if ($rs && $customer['adminid'] && $allowlockatc)
		{
			if (checkPost())
			{
				$DB->db_exec("UPDATE {$db_prefix}threads SET iflock = iflock ^ 1 WHERE tid = '$tid'");
				$DB->db_exec("INSERT INTO {$db_prefix}commonlogs (uid, action, description, logdate, logip) VALUES (".$customer['uid'].", '$action', '".$ajax_message['ajax_lock_article'].' TID:'.$tid.' RID:'.$rid."', $timestamp, '".$_PBENV['PB_IP']."')");
			}
			else
			{
				$actionlang = $rs['iflock'] ? $ajax_message['ajax_unlock_action'] : $ajax_message['ajax_lock_action'];
				$response[1] = getAjaxTPL();
			}
			$response[0] = '1';
		}
		else
		{
			$response[1] = $ajax_message['ajax_action_disabled'];
		}
		break;

	case 'shieldarticle':
		//屏蔽
		$rs = $DB->fetch_one("SELECT ifshield FROM {$db_prefix}threads WHERE tid = '$tid'");
		if ($rs && $customer['adminid'] && $allowshield)
		{
			if (checkPost())
			{
				$DB->db_exec("UPDATE {$db_prefix}threads SET ifshield = ifshield ^ 1 WHERE tid = '$tid'");
				$DB->db_exec("INSERT INTO {$db_prefix}commonlogs (uid, action, description, logdate, logip) VALUES (".$customer['uid'].", '$action', '".$ajax_message['ajax_shield_article'].' TID:'.$tid.' RID:'.$rid."', $timestamp, '".$_PBENV['PB_IP']."')");
			}
			else
			{
				$actionlang = $rs['ifshield'] ? $ajax_message['ajax_unshield_action'] : $ajax_message['ajax_shield_action'];
				$response[1] = getAjaxTPL();
			}
			$response[0] = '1';
		}
		else
		{
			$response[1] = $ajax_message['ajax_action_disabled'];
		}
		break;
		
	case 'toparticle':
		//置顶
		$rs = $DB->fetch_one("SELECT topped FROM {$db_prefix}threads WHERE tid = '$tid'");
		if ($rs && $customer['adminid'] && $allowtopatc)
		{
			if (checkPost())
			{
				$DB->db_exec("UPDATE {$db_prefix}threads SET topped = topped ^ 1 WHERE tid = '$tid'");
				$DB->db_exec("INSERT INTO {$db_prefix}commonlogs (uid, action, description, logdate, logip) VALUES (".$customer['uid'].", '$action', '".$ajax_message['ajax_top_article'].' TID:'.$tid.' RID:'.$rid."', $timestamp, '".$_PBENV['PB_IP']."')");
			}
			else
			{
				$actionlang = $rs['topped'] ? $ajax_message['ajax_untop_action'] : $ajax_message['ajax_top_action'];
				$response[1] = getAjaxTPL();
			}
			$response[0] = '1';
		}
		else
		{
			$response[1] = $ajax_message['ajax_action_disabled'];
		}
		break;
		
	case 'firstarticle':

		//头条
		$rs = $DB->fetch_one("SELECT first FROM {$db_prefix}threads WHERE tid = '$tid'");
		if ($rs && $customer['adminid'] && $allowcommend)
		{
			if (checkPost())
			{
				$DB->db_exec("UPDATE {$db_prefix}threads SET first = first ^ 1 WHERE tid = '$tid'");
				$DB->db_exec("INSERT INTO {$db_prefix}commonlogs (uid, action, description, logdate, logip) VALUES (".$customer['uid'].", '$action', '".$ajax_message['ajax_first_article'].' TID:'.$tid.' RID:'.$rid."', $timestamp, '".$_PBENV['PB_IP']."')");
			}
			else
			{
				$actionlang = $rs['first'] ? $ajax_message['ajax_unfirst_action'] : $ajax_message['ajax_first_action'];
				$response[1] = getAjaxTPL();
			}
			$response[0] = '1';
		}
		else
		{
			$response[1] = $ajax_message['ajax_action_disabled'];
		}
		break;

	case 'commendarticle':
		//推荐
		$rs = $DB->fetch_one("SELECT commend, commendpic FROM {$db_prefix}threads WHERE tid = '$tid'");
		if ($rs && $customer['adminid'] && $allowcommend)
		{
			if (checkPost())
			{
				$sql = '';
				if ($rs['commend'])
				{
					$sql = "commend = '0', commendpic = ''";
				}
				else
				{
					$timesession = (int)$timesession;
					$attachment = $DB->fetch_one("SELECT * FROM {$db_prefix}fsession WHERE uid = '".$customer['uid']."' AND timesession = '$timesession'");
					if ($attachment && $attachment['attachment'])
					{
						$attachment = unserialize($attachment['attachment']);
						$file = $attachment[1][3].'.'.$attachment[1][4];
						if (file_exists(PBDIGG_ATTACHMENT.'temp/'.$file) && $attachment[1][6])
						{
							$h = intval($h);
							$h <= 0 && $h = 230;
							$w = intval($w);
							$w <= 0 && $w = 290;
							thumb(PBDIGG_ATTACHMENT.'temp/'.$file, $h, $w, '');
							PMove(PBDIGG_ATTACHMENT.'temp/'.$file, PBDIGG_ATTACHMENT.'commend/'.$file);
							$sql .= "commend = '2', commendpic = '".addslashes($file)."'";
						}
						else
						{
							$sql .= "commend = '1', commendpic = ''";
						}
					}
					else
					{
						$sql .= "commend = '1', commendpic = ''";
					}
				}
				$rs['commendpic'] && PDel(PBDIGG_ATTACHMENT.'commend/'.$rs['commendpic']);

				$DB->db_exec("UPDATE {$db_prefix}threads SET $sql WHERE tid = '$tid'");
				$DB->db_exec("INSERT INTO {$db_prefix}commonlogs (uid, action, description, logdate, logip) VALUES (".$customer['uid'].", '$action', '".$ajax_message['ajax_commend_article'].' TID:'.$tid.' RID:'.$rid."', $timestamp, '".$_PBENV['PB_IP']."')");
			}
			else
			{
				$response[1] = getAjaxTPL();
			}
			$response[0] = '1';
		}
		else
		{
			$response[1] = $ajax_message['ajax_action_disabled'];
		}
		break;

	case 'titlestyle':
		//标题
		$rs = $DB->fetch_one("SELECT titlecolor, titlestyle FROM {$db_prefix}threads WHERE tid = '$tid'");
		if ($rs && $customer['adminid'] && $allowcommend)
		{
			if (checkPost())
			{
				$titlecolor = (preg_match('~^#[0-9a-fA-F]{3}$~i', $titlecolor) || preg_match('~^#[0-9a-fA-F]{6}$~i', $titlecolor)) ? $titlecolor : '';
				$titlestyle = 0;
				($pb_titleubb & 1) && $title_b && $titlestyle += 1;
				($pb_titleubb & 2) && $title_i && $titlestyle += 2;
				($pb_titleubb & 4) && $title_u && $titlestyle += 4;
				$DB->db_exec("UPDATE {$db_prefix}threads SET titlecolor = '".substr($titlecolor, 1)."', titlestyle = '$titlestyle' WHERE tid = '$tid'");
				$DB->db_exec("INSERT INTO {$db_prefix}commonlogs (uid, action, description, logdate, logip) VALUES (".$customer['uid'].", '$action', '".$ajax_message['ajax_commend_article'].' TID:'.$tid.' RID:'.$rid."', $timestamp, '".$_PBENV['PB_IP']."')");
			}
			else
			{
				$checkstatus_b = ($rs['titlestyle'] & 1) ? 'checked' : '';
				$checkstatus_i = ($rs['titlestyle'] & 2) ? 'checked' : '';
				$checkstatus_u = ($rs['titlestyle'] & 4) ? 'checked' : '';
				$response[1] = getAjaxTPL();
			}
			$response[0] = '1';
		}
		else
		{
			$response[1] = $ajax_message['ajax_action_disabled'];
		}
		break;
		
	case 'delattachment':
		$aid = (int)$aid;
		$rs = $DB->fetch_one("SELECT aid, tid, uid, filepath, thumb FROM {$db_prefix}attachments WHERE aid = '$aid'");
		if (!$rs) exit;
		$tid = (int)$rs['tid'];
		$uid = (int)$rs['uid'];
		if (checkPost() && (SUPERMANAGER || ($rs['uid'] == $customer['uid'] && $rs['uid']) || ($customer['adminid'] && $alloweditatc)))
		{
			PDel(PBDIGG_ATTACHMENT.$rs['filepath']);
			if ($rs['thumb'])
			{
				$pos = strrpos($rs['filepath'], '/');
				$dir = substr($rs['filepath'], 0, $pos);
				$filename = substr($rs['filepath'], $pos + 1);
				PDel(PBDIGG_ATTACHMENT.$dir.'/thumb_'.$filename);
			}
			$DB->db_exec("DELETE FROM {$db_prefix}attachments WHERE aid = '$aid'");
			if ($moduleid = intval($DB->fetch_first("SELECT module FROM {$db_prefix}threads WHERE tid = '$tid'")))
			{
				$currentModuleObj = $module->getModuleObject($moduleid);
				$currentModuleObj && $currentModuleObj->delattachment($tid, $aid);
			}
			$DB->db_exec("UPDATE {$db_prefix}members SET uploadnum = uploadnum - 1 WHERE uid = '$uid'");
			$DB->db_exec("INSERT INTO {$db_prefix}commonlogs (uid, action, description, logdate, logip) VALUES (".$customer['uid'].", '$action', '".$ajax_message['ajax_del_attachment'].' AID:'.$aid."', $timestamp, '".$_PBENV['PB_IP']."')");
			echo '1';
		}
		exit;
		break;
	default:
		$response[1] = $common_message['illegal_request'];
		break;
}

$response[1] = convert_encoding($db_charset, 'UTF-8', $response[1]);

if (function_exists('json_encode'))
{
	$response = json_encode($response);
}
else
{
   require_once(PBDIGG_ROOT.'include/json.class.php');
   $json = new Services_JSON;
   $response = $json->encode($response);
}
echo $response;
exit;

function getAjaxTPL($tpl = 'ajax')
{
	extract($GLOBALS);
	ob_start();
	require_once pt_fetch($tpl);
	$response = preg_replace('~<!--\d{10}-->\n~', '', ob_get_contents());
	ob_end_clean();
	return $response;
}
?>
