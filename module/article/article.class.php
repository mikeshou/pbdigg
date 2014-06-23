<?php
/**
 * @version $Id: article.class.php v1.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 * 
 * 文章模型类
 */

!defined('IN_PBDIGG') && exit('Access Denied');

require_once PBDIGG_ROOT.'module/article/include/article_function.php';

class article
{

	var $DB = NULL;
	var $db_prefix;

	function article()
	{
		$this->__construct();
	}

	function __construct()
	{
		global $DB, $db_prefix;
		$this->DB = $DB;
		$this->db_prefix = $db_prefix;
	}
	/**
	 * 添加主题
	 * 
	 * @param array $article 递交参数信息
	 */
	function add(&$post)
	{
		global $pb_watertype, $pb_tftopicimg, $customer, $pb_reposttime, $action, $timestamp, $_PBENV, $pb_uploadfiletype, $uploadtype, $allowaupload, $pb_allowupload, $pb_uploadtopicimg, $pb_uploadmaxsize, $uploadmax,
			$pb_titlelen, $pb_contentlen, $verifyhash, $logStatus, $pb_gdcheck, $pb_attachnum, $pb_style, $allowurl, $allowtimestamp, $allowinitstatus,
			$allowhtml, $currentCateData, $cid, $moduleid, $pb_tcheck, $pb_anonnews, $pb_urlsaveimg, $pb_creditdb, $initdigg, $initbury, $inithit;

		$pb_lastpost = $logStatus ? $customer['lastpost'] : gCookie('pb_lastpost');
		if (!$customer['adminid'] && $pb_reposttime && ($timestamp - $pb_lastpost < $pb_reposttime)) showMsg('post_flood_ctrl');
		if (checkPost())
		{
			$attachment_pattern = $attachment_replace = $attachments = array();
			$uid = (int)$customer['uid'];
			$timesession = (int)$_POST['timesession'];
		
			if (($pb_gdcheck & 4) && !ckgdcode($_POST['captcha']))
			{
				showMsg('checkcode_error');
			}
			if ($timesession)
			{
				$fsession = $this->DB->fetch_one("SELECT * FROM {$this->db_prefix}fsession WHERE uid = '$uid' AND timesession = '$timesession'");
				$fsession && $fsession['attachment'] && $attachments = unserialize($fsession['attachment']);
			}

			$title = checkTitle(HConvert(trim($_POST['title'])));
			$content = checkContent(trim(stripslashes($_POST['content'])));
			$tag = checkTag(HConvert(trim($_POST['tag'])));
			$summary = traceHtml(cutSpilthHtml(PBSubstr(preg_replace(array('~\[\/?[^\]]*\]~im','~(\r\n|\n\r|\r|\n){1,}~im'), '', (strip_tags($_POST['summary']) ? trim(stripslashes($_POST['summary'])) : $content)), 500)));
			safeConvert($summary);
			$author = $logStatus ? addslashes($customer['username']) : HConvert(trim($_POST['author']));
			$anonsite = (!$logStatus && substr($anonsite, 0, 7) == 'http://') ? HConvert($_POST['anonsite']) : '';
			if (strpos($content, 'embed') !== FALSE)
			{
				$content = ubb_deparse($content);
			}
			!$allowhtml && safeConvert($content);
			$ifconvert = $content == conentUBB($content, 't', true) ? 0 : 1;
			$source = ($_POST['source'] && checkURL($_POST['source'])) ? HConvert($_POST['source']) : '';
			$linkhost = ($source && preg_match('~^(https?|ftp|gopher|news|telnet|mms|rtsp):\/\/([^\/]*)~i', $source, $linkhost)) ? addslashes(HConvert($linkhost[2])) : '';
			$ifcheck = (($pb_tcheck && !SUPERMANAGER) || ($pb_anonnews && !$uid)) ? '0' : '1';

			$tptime = intval(pStrToTime($_POST['postdate']));
			$postdate = ($allowtimestamp && $_POST['postdate'] && $tptime && ($tptime <= $timestamp)) ? $tptime : $timestamp;
			$digg = ($allowinitstatus && $_POST['digg'] >= 0 && $_POST['digg'] <= $initdigg) ? (int)$_POST['digg'] : 0;
			$bury = ($allowinitstatus && $_POST['bury'] >= 0 && $_POST['bury'] <= $initbury) ? (int)$_POST['bury'] : 0;
			$views = ($allowinitstatus && $_POST['views'] >= 0 && $_POST['views'] <= $inithit) ? (int)$_POST['views'] : 0;
			$pbrank = pbrank(0, $digg, $bury, $views, 0, $postdate);
			$ishtml = 0;
			$realurl = '';
			$keywords = '';
			if ($pb_urlsaveimg && preg_match('~<img.+?src=(?:["|\']?)((?:https?|ftp):\/\/(?:[-_a-z0-9\.\~!$&\'\(\)*+,;=:@|/]+|%[\dA-F]{2})+\.(?:gif|jpg|jpeg|bmp|png))(?:["|\']?)[^>]*?>~i', $content))
			{
				require_once PBDIGG_ROOT.'include/remotefile.func.php';
				$content = get_remote_files($content);
			}

			$topicimgsql = $topicimg  = $topicdata = $attanum = $attatid = '';
			$ainfo = array();

			if ($allowaupload && $pb_allowupload)
			{
				if ($pb_uploadtopicimg)
				{
					require_once PBDIGG_ROOT.'include/Upload.class.php';
					$Upload = new Upload();
					$attasize = ($pb_uploadmaxsize > $uploadmax) ? $uploadmax : $pb_uploadmaxsize;
					$topicdata = $Upload->moveFile('topic', '', array('gif','jpg','jpeg','bmp','png'), $attasize);
					if ($topicdata)
					{
						$topicimg = gdate($timestamp, 'ym').'/'.$topicdata[0][1].'.'.$topicdata[0][2].'|'.$topicdata[0][7][1].'|'.$topicdata[0][7][2].'|0';
					}
				}
				if ($attachments)
				{
					$uploaddir = getUploadDir();
					$filepath = PBDIGG_ATTACHMENT.$uploaddir.'/';
					foreach ($attachments as $k => $v)
					{
						PMove(PBDIGG_ATTACHMENT.'temp/'.$v[3].'.'.$v[4], $filepath.$v[3].'.'.$v[4]);
						$v[5] && PMove(PBDIGG_ATTACHMENT.'temp/thumb_'.$v[3].'.'.$v[4], $filepath.'thumb_'.$v[3].'.'.$v[4]);
						if ($pb_uploadtopicimg && $pb_tftopicimg && $v[6] && !$topicimg)
						{
							$topicimg = makeTopicImg($filepath.$v[3].'.'.$v[4]);
						}
						$this->DB->db_exec("INSERT INTO {$this->db_prefix}attachments (cid,tid,uid,filename,filetype,filesize,description,filepath,isimg,uploaddate,thumb) VALUES ('$cid',0,'$uid','".addslashes($v[0])."','".addslashes($v[1])."',".intval($v[2]).",'','".addslashes($uploaddir.'/'.$v[3].'.'.$v[4])."',".$v[6].",$timestamp,".$v[5].")");
						$aid = $this->DB->db_insert_id();
						$attachment_pattern[] = '[file='.$k.']';
						$attachment_replace[] = '[attachment='.$aid.']';
						$attatid .= $aid.',';
						$ainfo[$aid] = array('aid'=>$aid,'filename'=>$v[0],'filesize'=>$v[2],'filepath'=>$uploaddir.'/'.$v[3].'.'.$v[4],'filetype'=>$v[1],'isimg'=>$v[6],'thumbpath'=>($v[5] ? $uploaddir.'/thumb_'.$v[3].'.'.$v[4] : ''));
						++$attanum;
					}
					$this->DB->db_exec("DELETE FROM {$this->db_prefix}fsession WHERE uid = '$uid' AND timesession = '$timesession'");
				}
			}

			$this->DB->db_exec("INSERT INTO {$this->db_prefix}threads (tid, cid, author, uid, subject, contentlink, linkhost, topicimg, ifcheck, ifshield, iflock, topped, postdate, postip, digg, diggdate, bury, burydate, views, comments, commentdate, keywords, pbrank, commend, commendpic, first, module, realurl, ishtml, summary, titlecolor, titlestyle) VALUES (NULL, '$cid', '$author', '$uid', '$title', '$source', '$linkhost', '$topicimg', '$ifcheck', 0, 0, 0, '$postdate', '".$_PBENV['PB_IP']."', '$digg', '$postdate', '$bury', '$postdate', '$views', 0, '$postdate', '', '$pbrank', 0, '', 0, '$moduleid', '$realurl', '$ishtml', '".addslashes($summary)."', '', 0)");
			$tid = $this->DB->db_insert_id();
			$ainfo = $ainfo ? addslashes(serialize($ainfo)) : '';

			$this->DB->db_exec("INSERT INTO {$this->db_prefix}article (tid, content, ainfo, ifconvert, anonsite) VALUES ('$tid', '', '$ainfo', '$ifconvert', '$anonsite')");
			//attachments
			if ($attatid)
			{
				$content = str_replace($attachment_pattern, $attachment_replace, $content);
				$this->DB->db_exec("UPDATE {$this->db_prefix}attachments SET tid = '$tid' WHERE aid IN (".substr($attatid, 0, -1).")");
			}

			//tags
			if ($tag)
			{
				$tag = explode(',', $tag);
				$i = 1;
				foreach ($tag as $key => $value)
				{
					$rs = $this->DB->fetch_one("SELECT tagid, usenum, ifopen, ifsys, tagpic FROM {$this->db_prefix}tags WHERE tagname = '$value'");
					if ($rs)
					{
						if ($rs['ifopen'])
						{
							$this->DB->db_exec("UPDATE {$this->db_prefix}tags SET usenum = usenum + 1 WHERE tagid = ".$rs['tagid']);
							$tagid = $rs['tagid'];
							if (!$topicimg && $rs['tagpic'])
							{
								$topicimgsql = ", topicimg = '".addslashes($rs['tagpic'])."|1'";
								$topicimg = true;
							}
						}
						else
						{
							unset($tag[$key]);
							continue;
						}
					}
					else
					{
						$this->DB->db_exec("INSERT INTO {$this->db_prefix}tags (tagname, usenum, hits, ifopen, ifsys, tagpic) VALUES ('$value', 1, 0, 1, 0, '')");
						$tagid = $this->DB->db_insert_id();
					}
					$this->DB->db_exec("INSERT INTO {$this->db_prefix}tagcache (tagid, tid) VALUES ($tagid, $tid)");
					$keywords .= ($keywords ? ',' : '').$value;
					if ($i++ >= 5) break;
				}
			}

			$this->DB->db_exec("UPDATE {$this->db_prefix}threads SET keywords = '$keywords' $topicimgsql WHERE tid = '$tid'");
			$this->DB->db_exec("UPDATE {$this->db_prefix}article SET content = '".addslashes($content)."' WHERE tid = '$tid'");
			$this->DB->db_exec("UPDATE {$this->db_prefix}categories SET tnum = tnum + 1 WHERE cid = '$cid'");
			if ($uid)
			{
				$pb_creditdb = explode("\t", $pb_creditdb);
				$this->DB->db_exec("UPDATE {$this->db_prefix}members SET uploadnum = uploadnum + ".(int)$attanum.", postnum = postnum + 1, currency = currency + ".$pb_creditdb[0].", lastpost = '$timestamp' WHERE uid = '$uid'");
			}
			else
			{
				sCookie('pb_lastpost', $timestamp);
			}
			$this->DB->db_exec("UPDATE {$this->db_prefix}sitestat SET artnum = artnum + 1 WHERE id = 1");
			redirect(($ifcheck ? 'post_newthread_succeed' : 'post_newthread_unchecked'), 'show.php?tid='.$tid);
		}
		else
		{
			$post['attaexts'] = implode(',', $this->getUploadExts());
			$post['attanum'] = $this->getUploadNums();
			$post['attasize'] = ceil($this->getUploadSize() / 1024);
			$post['editor'] = getEditor(array(array('id'=>'content','type'=>'PBDigg','content'=>'','width'=>600,'height'=>300),array('id'=>'summary','type'=>'Basic','content'=>'','width'=>400,'height'=>200)));
		}
	}
	/**
	 * 删除文章
	 * 
	 * @param array $tid 文章ID数组
	 */
	function del($tid)
	{
		if (empty($tid)) return;
		!is_array($tid) && $tid = (array)$tid;
		$this->DB->db_exec("DELETE FROM {$this->db_prefix}article WHERE tid IN (".implode(',', $tid).")");
	}
	/**
	 * 编辑主题
	 * 
	 * @param int $tid 主题tid
	 */
	function edit(&$post, $tid)
	{
		global $pb_watertype, $pb_tftopicimg, $pb_topicstylesize, $pb_reeditlimit, $customer, $timestamp, $_PBENV, $pb_uploadfiletype, $uploadtype, $allowaupload, $pb_allowupload, $pb_uploadtopicimg, $pb_uploadmaxsize, $uploadmax,
			$pb_titlelen, $pb_contentlen, $verifyhash, $logStatus, $pb_gdcheck, $pb_attachnum, $pb_style, $allowurl, $allowtimestamp, $allowinitstatus,
			$allowhtml, $currentCateData, $cid, $moduleid, $pb_tcheck, $pb_anonnews, $pb_urlsaveimg, $pb_creditdb, $_attdir, $action, $initdigg, $initbury, $inithit, $timesession;

		$tid = (int)$tid;
		$uid = (int)$customer['uid'];
		
		$post = $this->DB->fetch_one("SELECT t.*, a.* FROM {$this->db_prefix}threads t LEFT JOIN {$this->db_prefix}article a USING (tid) WHERE t.tid = '$tid'");
		if (!$post) showMsg('post_thread_nonexistence');
		if (!$uid || (!$customer['adminid'] && $post['uid'] != $uid)) showMsg('post_edit_nopermission');
		if (!$customer['adminid'] && $pb_reeditlimit && $timestamp - $post['postdate'] > $pb_reeditlimit * 60) showMsg('post_edit_timelimit');

		if (checkPost())
		{
			if (($pb_gdcheck & 4) && !ckgdcode($_POST['captcha']))
			{
				showMsg('checkcode_error');
			}

			$updatesql = $updateinfosql = $keywords = '';
			$ainfo = $attachments = array();
			addS($post);
			$timesession = (int)$_POST['timesession'];
			
			if ($timesession)
			{
				$fsession = $this->DB->fetch_one("SELECT * FROM {$this->db_prefix}fsession WHERE uid = '$uid' AND timesession = '$timesession'");
				$fsession && $fsession['attachment'] && $attachments = unserialize($fsession['attachment']);
			}

			$title = checkTitle(HConvert(trim($_POST['title'])));
			$post['title'] != $title && $updatesql .= " subject = '$title',";
			$content = checkContent(trim(stripslashes($_POST['content'])));
			$tag = checkTag(HConvert(trim($_POST['tag'])));
			$summary = traceHtml(cutSpilthHtml(PBSubstr(preg_replace(array('~\[\/?[^\]]*\]~im','~(\r\n|\n\r|\r|\n){1,}~im'), '', (strip_tags($_POST['summary']) ? trim(stripslashes($_POST['summary'])) : $content)), 500)));
			safeConvert($summary);
			$summary != $post['summary'] && $updatesql .= " summary = '".addslashes($summary)."',";
			$anonsite = ($customer['adminid'] && substr($anonsite, 0, 7) == 'http://') ? HConvert($_POST['anonsite']) : '';
			$anonsite != $post['anonsite'] && $updateinfosql .= " anonsite = '$anonsite',";
			if (strpos($content, 'embed') !== FALSE)
			{
				$content = ubb_deparse($content);
			}
			!$allowhtml && safeConvert($content);
			$ifconvert = $content == conentUBB($content, 't', true) ? 0 : 1;
			$source = ($_POST['source'] && checkURL($_POST['source'])) ? HConvert($_POST['source']) : '';
			if ($post['source'] != $source)
			{
				$linkhost = ($source && preg_match('~^(https?|ftp|gopher|news|telnet|mms|rtsp):\/\/([^\/]*)~i', $source, $linkhost)) ? addslashes(HConvert($linkhost[2])) : '';
				$updatesql .= " contentlink = '$source', linkhost = '$linkhost',";
			}
			$ifcheck = (($pb_tcheck && !SUPERMANAGER) || ($pb_anonnews && !$customer['uid'])) ? '0' : '1';

			$tptime = intval(pStrToTime($_POST['postdate']));
			$allowtimestamp && $_POST['postdate'] && $tptime && ($tptime <= $timestamp) && $updatesql .= " postdate = '$tptime',";
			$allowinitstatus && $_POST['digg'] >= 0 && $_POST['digg'] <= $initdigg && $updatesql .= " digg = '".(int)$_POST['digg']."',";
			$allowinitstatus && $_POST['bury'] >= 0 && $_POST['bury'] <= $initbury && $updatesql .= " bury = '".(int)$_POST['bury']."',";
			$allowinitstatus && $_POST['views'] >= 0 && $_POST['views'] <= $inithit && $updatesql .= " views = '".(int)$_POST['views']."',";

			if ($post['keywords'] != $tag)
			{
				$tag = array_filter(array_unique(explode(',', $tag)));
				$oldtag = array();
				$query = $this->DB->db_query("SELECT tg.tagname FROM {$this->db_prefix}tags tg, {$this->db_prefix}tagcache tc WHERE tg.tagid = tc.tagid AND tc.tid = '$tid'");
				while ($rs = $this->DB->fetch_all($query))
				{
					$oldtag[] = $rs['tagname'];
				}
				if ($tag != $oldtag)
				{
					$deltags = $addtags = $rs = $t_tags = array();
					$tagid = $tagname = '';
					$deltags = array_diff($oldtag, $tag);
					$addtags = array_diff($tag, $oldtag);
					if ($deltags)
					{
						foreach ($deltags as $value)
						{
							$tagname .= "'".$value."',";
						}
						$query = $this->DB->db_query("SELECT tagid FROM {$this->db_prefix}tags WHERE tagname IN (".substr($tagname, 0, -1).")");
						while ($rs = $this->DB->fetch_all($query))
						{
							$tagid .= $rs['tagid'].',';
						}
						$tagid = substr($tagid, 0, -1);
						$this->DB->db_exec("DELETE FROM {$this->db_prefix}tagcache WHERE tagid IN ($tagid) AND tid = $tid");
						$this->DB->db_exec("UPDATE {$this->db_prefix}tags SET usenum = usenum - 1 WHERE tagid IN ($tagid)");
					}
					$t_tags = array_diff($oldtag, $deltags);
					$i = count($t_tags);
					$tagid = '';
					foreach ($addtags as $key => $value)
					{
						$rs = $this->DB->fetch_one("SELECT tagid, usenum, ifopen FROM {$this->db_prefix}tags WHERE tagname = '$value'");
						if ($rs)
						{
							if ($rs['ifopen'])
							{
								$this->DB->db_exec("UPDATE {$this->db_prefix}tags SET usenum = usenum + 1 WHERE tagid = ".$rs['tagid']);
								$tagid = $rs['tagid'];
							}
							else
							{
								unset($addtags[$key]);
								continue;
							}
						}
						else
						{
							$this->DB->db_exec("INSERT INTO {$this->db_prefix}tags (tagname, usenum, hits, ifopen, ifsys, tagpic) VALUES ('$value', 1, 0, 1, 0, '')");
							$tagid = $this->DB->db_insert_id();
						}
						$this->DB->db_exec("INSERT INTO {$this->db_prefix}tagcache (tagid, tid) VALUES ($tagid, $tid)");
						if ($i++ >= 5) break;
					}
					$updatesql .= " keywords = '".HConvert(addslashes(implode(',', array_merge($t_tags, $addtags))))."',";
				}
			}

			if ($pb_urlsaveimg && preg_match('~<img.+?src=(?:["|\']?)((?:https?|ftp):\/\/(?:[-_a-z0-9\.\~!$&\'\(\)*+,;=:@|/]+|%[\dA-F]{2})+\.(?:gif|jpg|jpeg|bmp|png))(?:["|\']?)[^>]*?>~i', $content))
			{
				require_once PBDIGG_ROOT.'include/remotefile.func.php';
				$content = get_remote_files($content);
			}
			$post['content'] != $content && $updateinfosql .= " content = '".addslashes($content)."',";
			$topicimg  = $topicdata = $attanum = $topicimgpath = $tmph = $tmpw = $topictype = $attatid = '';
			$deltopicimg = false;
			if ($allowaupload && $pb_allowupload)
			{
				if ($post['topicimg'])
				{
					list($topicimgpath,$tmph,$tmpw,$topictype) = explode('|', $post['topicimg']);
				}
				if ($pb_uploadtopicimg && isset($_FILES['topicimg']))
				{
					require_once PBDIGG_ROOT.'include/Upload.class.php';
					$Upload = new Upload();
					$attasize = ($pb_uploadmaxsize > $uploadmax) ? $uploadmax : $pb_uploadmaxsize;
					$topicdata = $Upload->moveFile('topic', '', array('gif','jpg','jpeg','bmp','png'), $attasize);
					if ($topicdata)
					{
						$topicimg = gdate($timestamp, 'ym').'/'.$topicdata[0][1].'.'.$topicdata[0][2].'|'.$topicdata[0][7][1].'|'.$topicdata[0][7][2].'|0';
						$updatesql .= " topicimg = '$topicimg',";
					}
					else
					{
						$updatesql .= " topicimg = '',";
						$deltopicimg = true;
					}
					$post['topicimg'] && $topictype == '0' && PDel(PBDIGG_ATTACHMENT.'topic/'.$topicimgpath);
				}
				if ($attachments)
				{
					$uploaddir = getUploadDir();
					$filepath = PBDIGG_ATTACHMENT.$uploaddir.'/';
					foreach ($attachments as $k => $v)
					{
						PMove(PBDIGG_ATTACHMENT.'temp/'.$v[3].'.'.$v[4], $filepath.$v[3].'.'.$v[4]);
						$v[5] && PMove(PBDIGG_ATTACHMENT.'temp/thumb_'.$v[3].'.'.$v[4], $filepath.'thumb_'.$v[3].'.'.$v[4]);
						if ($pb_uploadtopicimg && $pb_tftopicimg && $v[6] && !$topicimg && !$deltopicimg)
						{
							$topicimg = makeTopicImg($filepath.$v[3].'.'.$v[4]);
							$post['topicimg'] && $topictype == '0' && PDel(PBDIGG_ATTACHMENT.'topic/'.$topicimgpath);
							$updatesql .= " topicimg = '$topicimg',";
						}
						$this->DB->db_exec("INSERT INTO {$this->db_prefix}attachments (cid,tid,uid,filename,filetype,filesize,description,filepath,isimg,uploaddate,thumb) VALUES ('$cid','$tid','$uid','".addslashes($v[0])."','".addslashes($v[1])."',".intval($v[2]).",'','".addslashes($uploaddir.'/'.$v[3].'.'.$v[4])."',".$v[6].",$timestamp,".$v[5].")");
						$aid = $this->DB->db_insert_id();
						$attachment_pattern[] = '[file='.$k.']';
						$attachment_replace[] = '[attachment='.$aid.']';
						$attatid .= $aid.',';
						$ainfo[$aid] = array('aid'=>$aid,'filename'=>$v[0],'filesize'=>$v[2],'filepath'=>$uploaddir.'/'.$v[3].'.'.$v[4],'filetype'=>$v[1],'isimg'=>$v[6],'thumbpath'=>($v[5] ? $uploaddir.'/thumb_'.$v[3].'.'.$v[4] : ''));
						++$attanum;
					}
					if ($ainfo)
					{
						$post['ainfo'] && $ainfo = $ainfo + unserialize(stripslashes($post['ainfo']));
						$updateinfosql .= " ainfo = '".addslashes(serialize($ainfo))."',";
						$updateinfosql = str_replace($attachment_pattern, $attachment_replace, $updateinfosql);
						$this->DB->db_exec("UPDATE {$this->db_prefix}members SET uploadnum = uploadnum + $attanum WHERE uid = '$uid'");
					}
					$this->DB->db_exec("DELETE FROM {$this->db_prefix}fsession WHERE uid = '$uid' AND timesession = '$timesession'");
				}
			}
			$this->DB->db_exec("UPDATE {$this->db_prefix}threads SET $updatesql ifcheck = $ifcheck WHERE tid = '$tid'");
			$this->DB->db_exec("UPDATE {$this->db_prefix}article SET $updateinfosql ifconvert = '$ifconvert' WHERE tid = '$tid'");
			redirect(($ifcheck ? 'post_newthread_succeed' : 'post_newthread_unchecked'), 'show.php?tid='.$tid);
		}
		else
		{
			global $common_message;
			$attachments = '';

			$post['attaexts'] = implode(',', $this->getUploadExts());
			$post['attanum'] = $this->getUploadNums();
			$post['attasize'] = ceil($this->getUploadSize() / 1024);
			if ($post['ifconvert'])
			{
				$post['content'] = ubb_parse($post['content']);
			}
			$post['editor'] = getEditor(array(array('id'=>'content','type'=>'PBDigg','content'=>$post['content'],'width'=>600,'height'=>300),array('id'=>'summary','type'=>'Basic','content'=>$post['summary'],'width'=>400,'height'=>200)));
			$post['postdate'] = gdate($post['postdate'], 'Y-m-d H:i:s');
			if ($post['ainfo'])
			{
				$post['ainfo'] = unserialize($post['ainfo']);
				foreach ($post['ainfo'] as $k => $v)
				{
					$attachments .= '<div class="attachment_show" id="e_attachment_'.$k.'"><img src="'.$_PBENV['PB_URL'].'attachments/'.$v['filepath'].'" /><span><a onclick="insertatt(\'[attachment='.$k.']\')" href="javascript:void(0);">'.$common_message['insert'].'</a> | <a onclick="supdelatt('.$k.')" href="javascript:void(0);">'.$common_message['delete'].'</a></span></div>';
				}
			}
			$post['attachments'] = &$attachments;
			if ($post['topicimg'])
			{
				list($topicimg,,,) = explode('|', $post['topicimg']);
				$post['topicimg'] = $_PBENV['PB_URL'].$_attdir.'/topic/'.$topicimg;
			}
		}
	}

	function copy($oldtid, $newtid, $targetcid, $replace_atta)
	{
		$data = $this->DB->fetch_one("SELECT content, ainfo, ifconvert, anonsite FROM {$this->db_prefix}article WHERE tid = '$oldtid'");
		$newainfo = array();
		if ($data)
		{
			if ($data['ainfo'] && $replace_atta)
			{
				$ainfo = unserialize($data['ainfo']);
				foreach ($replace_atta as $k => $v)
				{
					$ainfo[$k]['aid'] = $v;
					$newainfo[$v] = $ainfo[$k];
				}
			}
			$replace_atta && $data['content'] = preg_replace('~\[attachment=(\d+?)\]~ie', "replace_atta(\\1, \$replace_atta)", $data['content']);
			$newainfo = $newainfo ? addslashes(serialize($newainfo)) : '';
			$this->DB->db_exec("INSERT INTO {$this->db_prefix}article (tid, content, ainfo, ifconvert, anonsite) VALUES ('$newtid', '".addslashes($data['content'])."', '$newainfo', '".$data['ifconvert']."', '".addslashes($data['anonsite'])."')");
		}
	}

	function move($tid, $oldcid, $targetcid)
	{
		return;
	}

	/**
	 * 显示列表
	 * 
	 * @param int $cid 分类ID
	 */
	function category($cid, &$article)
	{
		//文章模型无需实现该方法
	}
	/**
	 * 显示主题
	 * 
	 * @param int $tid 主题tid
	 */
	function show($tid, &$article)
	{
		global $common_message, $pb_copyctrl, $pb_taglink, $pb_sitedir, $pb_previewsize, $pb_attoutput, $pb_outputmaxsize, $_attdir, $pb_autoshield, $customer;
		$tinfo = $this->DB->fetch_one("SELECT * FROM {$this->db_prefix}article WHERE tid = '$tid'");
		if (!$tinfo) return;
		$article += $tinfo;
		unset($tinfo);

		$contentShield = $shieldNotice = '';
		if ($article['ifshield'] || $article['groupid'] == 3 && $pb_autoshield)
		{
			if ($customer['adminid'])
			{
				$shieldNotice = articleShield('show_admin_shield');
			}
			else
			{
				$article['content'] = articleShield($article['groupid'] == 3 ? 'show_auto_shield' : 'show_content_shield');
//				$article['subject'] = articleShield('show_title_shield');
				$article['altsubject'] = strip_tags($article['subject']);
				$contentShield = true;
			}
		}
		if (!$contentShield)
		{
			$article['content'] = $shieldNotice.$article['content'];
			//ubb
			$article['ifconvert'] && $article['content'] = conentUBB($article['content']);
			if($pb_taglink && $article['keywords'])
			{
				global $_tagkeywords;
				$_tagkeywords = $article['keywords'];
				$article['content'] = preg_replace('~(^|>)([^<]+)(?=<|$)~ie',"replace_tag('\\1', '\\2')", $article['content']);
//				foreach ($article['keywords'] as $v)
//				{
//					$v && $article['content'] = preg_replace("~(?<=[\s\"\]>()]|[\x7f-\xff]|^)(".preg_quote($v,'~').")([.,:;-?!()\s\"<\[]|[\x7f-\xff]|$)~siU", "<a href=\"".$pb_sitedir."index.php?tag=\\1\"><span style=\"text-decoration:underline;\">\\1</span></a>\\2", $article['content']);
//				}
			}
			$pb_copyctrl && $article['content'] = preg_replace('~<br \/>~eis', 'contentCopyCtrl()', $article['content']);
		}
		$article['anonsite'] && $article['anonsite'] = '<a href="'.((strtolower(substr($article['anonsite'], 0, 7)) == 'http://') ? $article['anonsite'] : 'http://'.$article['anonsite']).'" target="_blank" class="anonsite">'.HConvert($article['author']).'</a>';

		//attachment
		$article['attach_image'] = $article['attach_other'] = array();
		list ($pb_previewh, $pb_previeww) = explode("\t", $pb_previewsize);
		$article['preview_image'] = $pb_previeww ? "\$('#content img').each(function(){if (this.width > {$pb_previeww}){this.height=parseInt(this.height*{$pb_previeww}/this.width);this.width={$pb_previeww};}})" : '';
		if ($article['ainfo'])
		{
			global $ainfo, $onload;
			$ainfo = unserialize($article['ainfo']);

			if ($pb_previewh || $pb_previeww)
			{
				$onload = ' onload="';
				$pb_previeww && $onload .= "if(this.width>'".$pb_previeww."')this.width='".$pb_previeww."';";
				$pb_previewh && $onload .= "if(this.height>'".$pb_previewh."')this.height='".$pb_previewh."';";
				$onload .= '"';
			}
			require_once PBDIGG_ROOT.'include/attachment.func.php';
			$article['content'] = attachment($article['content']);
			foreach ($ainfo as $v)
			{
				$extension = strtolower(Fext($v['filename']));
				$isimg = ($v['isimg'] && in_array($extension, array('jpg', 'jpeg', 'gif', 'png', 'bmp'))) ? true : false;
				
				$url = $pb_sitedir.(($isimg || $pb_attoutput || ($pb_outputmaxsize && $v['filesize'] > $pb_outputmaxsize)) ? $_attdir.'/'.$v['filepath'] : 'attachments.php?aid='.$v['aid']);
				$thumburl = $v['thumbpath'] ? $pb_sitedir.$_attdir.'/'.$v['thumbpath'] : $url;
				if ($isimg)
				{
					$article['attach_image'][] = array('thumburl'=>$thumburl,'url'=>$url,'filename'=>htmlspecialchars($v['filename']),'onload'=>$onload);
				}
				else
				{
					$article['attach_other'][] = array('url'=>$url,'filename'=>htmlspecialchars($v['filename']),'icon'=>attachtype($extension));
				}
			}
		}
	}
	/**
	 * 删除分类主题
	 * 
	 * @param array $cid 分类cid数组
	 */
	function delCateArticle($cid)
	{
		if (empty($cid)) return;
		!is_array($cid) && $cid = (array)$cid;
		$query = $this->DB->db_query("SELECT tid FROM {$this->db_prefix}threads WHERE cid IN (".implode(',', $cid).")");
		$tids = '';
		while ($rs = $this->DB->fetch_all($query))
		{
			$tids .= ($tids ? ',' : '').$rs['tid'];
		}
		$tids && $this->DB->db_exec("DELETE FROM {$this->db_prefix}article WHERE tid IN ($tids)");
	}

	function delattachment($tid, $aid)
	{
		$article = $this->DB->fetch_one("SELECT ainfo FROM {$this->db_prefix}article WHERE tid = '$tid'");
		if ($article && $article['ainfo'])
		{
			$ainfo = unserialize($article['ainfo']);
			unset($ainfo[$aid]);
			$ainfo = $ainfo ? addslashes(serialize($ainfo)) : '';
			$this->DB->db_exec("UPDATE {$this->db_prefix}article SET ainfo = '$ainfo' WHERE tid = '$tid'");
		}
	}
	
	/**
	 * 返回允许上传的附件类型
	 * @return Array 附件后缀数组
	 */
	function getUploadExts($type = 'attachment')
	{
		global $pb_uploadfiletype, $uploadtype;
		$exts = array();
		if ($type == 'commend') $uploadtype = 'jpg,jpeg,gif,png,bmp';
		if ($pb_uploadfiletype && $uploadtype)
		{
			$haystack = ','.strtolower($pb_uploadfiletype).',';
			$exts = explode(',', strtolower($uploadtype));
			foreach ($exts as $key => $ext)
			{
				if (strpos($haystack, ','.$ext.',') === FALSE)
				{
					unset($exts[$key]);
				}
			}
		}
		return $exts;
	}
	/**
	 * 允许上传的最大附件尺寸
	 */
	function getUploadSize()
	{
		global $pb_uploadmaxsize, $uploadmax;
		return ($pb_uploadmaxsize > $uploadmax) ? $uploadmax : $pb_uploadmaxsize;
	}
	/**
	 * 单次上传附件数
	 */
	function getUploadNums($type = 'attachment')
	{
		global $pb_attachnum;
		return $type == 'attachment' ? intval($pb_attachnum) : '1';
	}
	
	/**
	 * 模型变量调用接口
	 * @param string $field 字段名
	 * @param string $table 表名
	 * @param rel $var 引用参数
	 */
	function transfer($field, $table, &$var)
	{
//		$this->${'transfer_'.$field}($var);
	}
}
?>