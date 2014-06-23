DROP TABLE `pb_admingroups`;
CREATE TABLE `pb_admingroups` (
  `adminid` tinyint(3) unsigned NOT NULL default '0',
  `allowadmincp` tinyint(1) NOT NULL default '0',
  `alloweditatc` tinyint(1) NOT NULL default '0',
  `allowdelatc` tinyint(1) NOT NULL default '0',
  `allowcheckatc` tinyint(1) unsigned NOT NULL default '0',
  `allowlockatc` tinyint(1) NOT NULL default '0',
  `allowmoveatc` tinyint(1) NOT NULL default '0',
  `allowcopyatc` tinyint(1) NOT NULL default '0',
  `allowtopatc` tinyint(1) NOT NULL default '0',
  `allowcommend` tinyint(1) NOT NULL default '0',
  `allowshield` tinyint(1) NOT NULL default '0',
  `allowtitlestyle` tinyint(1) unsigned NOT NULL default '0',
  `adminright` text NOT NULL,
  PRIMARY KEY  (`adminid`)
);
INSERT INTO `pb_admingroups` (`adminid`, `allowadmincp`, `alloweditatc`, `allowdelatc`, `allowcheckatc`, `allowlockatc`, `allowmoveatc`, `allowcopyatc`, `allowtopatc`, `allowcommend`, `allowshield`, `allowtitlestyle`, `adminright`) VALUES (1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 'a:17:{s:7:"setting";i:1;s:4:"cate";i:1;s:3:"tag";i:1;s:6:"member";i:1;s:5:"group";i:1;s:6:"module";i:1;s:5:"check";i:1;s:7:"special";i:1;s:5:"batch";i:1;s:7:"message";i:1;s:6:"plugin";i:1;s:3:"tpl";i:1;s:8:"database";i:1;s:4:"tool";i:1;s:12:"announcement";i:1;s:4:"link";i:1;s:3:"log";i:1;}'),
(2, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 'a:17:{s:7:"setting";i:1;s:4:"cate";i:1;s:3:"tag";i:0;s:6:"member";i:0;s:5:"group";i:0;s:6:"module";i:0;s:5:"check";i:1;s:7:"special";i:1;s:5:"batch";i:0;s:7:"message";i:0;s:6:"plugin";i:0;s:3:"tpl";i:0;s:8:"database";i:0;s:4:"tool";i:1;s:12:"announcement";i:0;s:4:"link";i:0;s:3:"log";i:0;}');
DROP TABLE `pb_adminlogs`;
CREATE TABLE `pb_adminlogs` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `action` text NOT NULL,
  `description` varchar(255) NOT NULL default '',
  `logdate` int(10) unsigned NOT NULL default '0',
  `logip` char(15) NOT NULL default '',
  `result` tinyint(1) NOT NULL default '1',
  `islog` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `uid` (`uid`),
  KEY `logdate` (`logdate`,`islog`)
);
DROP TABLE `pb_adminonlines`;
CREATE TABLE `pb_adminonlines` (
  `sid` char(32) NOT NULL,
  `username` varchar(30) NOT NULL,
  `loginfo` text NOT NULL,
  `logdate` int(10) unsigned NOT NULL default '0',
  `logip` char(15) NOT NULL,
  `lastactivity` int(10) unsigned NOT NULL default '0',
  `super` tinyint(1) unsigned NOT NULL default '0',
  UNIQUE KEY `sid` (`sid`)
);
DROP TABLE `pb_announcements`;
CREATE TABLE `pb_announcements` (
  `aid` smallint(6) unsigned NOT NULL auto_increment,
  `cid` smallint(6) unsigned NOT NULL default '0',
  `author` varchar(30) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `content` mediumtext NOT NULL,
  `postdate` int(10) unsigned NOT NULL default '0',
  `enddate` int(10) unsigned NOT NULL default '0',
  `displayorder` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`aid`),
  KEY `fid` (`cid`)
);
DROP TABLE `pb_article`;
CREATE TABLE `pb_article` (
  `tid` mediumint(8) unsigned NOT NULL,
  `content` mediumtext NOT NULL,
  `ainfo` text NOT NULL,
  `ifconvert` tinyint(1) unsigned NOT NULL default '1',
  `anonsite` varchar(255) NOT NULL default '',
  UNIQUE KEY `tid` (`tid`)
);
INSERT INTO `pb_article` (`tid`, `content`, `ainfo`, `ifconvert`, `anonsite`) VALUES (134, '<p>\r\n	ddd</p>', '', 0, '');
DROP TABLE `pb_attachments`;
CREATE TABLE `pb_attachments` (
  `aid` mediumint(8) unsigned NOT NULL auto_increment,
  `cid` smallint(6) unsigned NOT NULL default '0',
  `tid` mediumint(8) unsigned NOT NULL default '0',
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `filename` varchar(100) NOT NULL default '',
  `filetype` varchar(50) NOT NULL default '',
  `filesize` int(10) unsigned NOT NULL default '0',
  `description` varchar(255) NOT NULL default '',
  `filepath` varchar(255) NOT NULL default '',
  `downloads` mediumint(8) unsigned NOT NULL default '0',
  `isimg` tinyint(1) unsigned NOT NULL default '0',
  `uploaddate` int(10) unsigned NOT NULL default '0',
  `thumb` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`aid`),
  KEY `cid` (`cid`),
  KEY `tid` (`tid`),
  KEY `uid` (`uid`)
);
DROP TABLE `pb_categories`;
CREATE TABLE `pb_categories` (
  `cid` smallint(6) unsigned NOT NULL auto_increment,
  `cup` smallint(6) unsigned NOT NULL default '0',
  `depth` tinyint(3) unsigned NOT NULL default '0',
  `withchild` tinyint(1) unsigned NOT NULL default '0',
  `icon` varchar(255) NOT NULL default '',
  `name` varchar(50) NOT NULL default '',
  `dir` varchar(50) NOT NULL default '',
  `style` varchar(30) NOT NULL default '',
  `keywords` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `status` tinyint(1) unsigned NOT NULL default '0',
  `anonymity` tinyint(1) unsigned NOT NULL default '1',
  `tnum` mediumint(8) unsigned NOT NULL default '0',
  `cnum` int(10) unsigned NOT NULL default '0',
  `displayorder` mediumint(8) unsigned NOT NULL default '0',
  `ttype` char(100) NOT NULL default '',
  `cover` varchar(30) NOT NULL default '',
  `template` varchar(30) NOT NULL default '',
  `listtype` tinyint(1) unsigned NOT NULL default '0',
  `listnum` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`cid`)
);
INSERT INTO `pb_categories` (`cid`, `cup`, `depth`, `withchild`, `icon`, `name`, `dir`, `style`, `keywords`, `description`, `status`, `anonymity`, `tnum`, `cnum`, `displayorder`, `ttype`, `cover`, `template`, `listtype`, `listnum`) VALUES (1, 0, 0, 0, '', '默认分类', '', '', '', 'PBDigg 是一个基于PHP + MYSQL的开源Dig社区系统，经过完善设计并适用于各种服务器环境的高效、全新、快速、优秀的网站解决方案。PBDigg 融合了社会性标签、主题评论、Rss订阅等多种WEB2.0元素。PBDigg 的宗旨是：快乐发掘，一起分享', 1, 1, 1, 0, 0, '1', '', '', 0, 0);
DROP TABLE `pb_cdata`;
CREATE TABLE `pb_cdata` (
  `cdid` mediumint(8) unsigned NOT NULL auto_increment,
  `cid` smallint(6) unsigned NOT NULL default '0',
  `tid` mediumint(8) unsigned NOT NULL default '0',
  `rid` int(10) unsigned NOT NULL default '0',
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `type` enum('digg','bury') NOT NULL default 'digg',
  PRIMARY KEY  (`cdid`),
  KEY `tid` (`tid`),
  KEY `rid` (`rid`),
  KEY `uid` (`uid`)
);
DROP TABLE `pb_comments`;
CREATE TABLE `pb_comments` (
  `rid` int(10) unsigned NOT NULL auto_increment,
  `cid` smallint(6) unsigned NOT NULL default '0',
  `tid` mediumint(8) unsigned NOT NULL default '0',
  `author` varchar(30) NOT NULL default '',
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `content` text NOT NULL,
  `ifcheck` tinyint(1) NOT NULL default '0',
  `ifshield` tinyint(1) unsigned NOT NULL default '0',
  `ifconvert` tinyint(1) unsigned NOT NULL default '0',
  `postdate` int(10) unsigned NOT NULL default '0',
  `postip` char(15) NOT NULL default '',
  `digg` int(10) unsigned NOT NULL default '0',
  `diggdate` int(10) unsigned NOT NULL default '0',
  `bury` int(10) unsigned NOT NULL default '0',
  `burydate` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`rid`),
  KEY `postdate` (`postdate`,`digg`),
  KEY `tid` (`tid`,`postdate`),
  KEY `cid` (`cid`)
);
DROP TABLE `pb_commonlogs`;
CREATE TABLE `pb_commonlogs` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `action` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `logdate` int(10) unsigned NOT NULL default '0',
  `logip` char(15) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `uid` (`uid`)
);
DROP TABLE `pb_configs`;
CREATE TABLE `pb_configs` (
  `title` varchar(20) NOT NULL default '',
  `text` text NOT NULL,
  PRIMARY KEY  (`title`)
);
INSERT INTO `pb_configs` (`title`, `text`) VALUES ('pb_sitename', 'PBDigg 3.0'),
('pb_siteurl', 'http://localhost/'),
('pb_adminmail', 'pbdigg@qq.com'),
('pb_ifopen', '1'),
('pb_whyclosed', '<p>\r\n	网站升级，请稍后访问</p>\r\n'),
('pb_gzip', '1'),
('pb_style', 'pbdigg'),
('pb_timezone', '8'),
('pb_timeformat', '1'),
('pb_avatupload', '1'),
('pb_avatsize', '102400'),
('pb_avathight', '100'),
('pb_avatwidth', '100'),
('pb_tcheck', '0'),
('pb_refreshtime', '0'),
('pb_maxresult', '100'),
('pb_exectime', '1'),
('pb_customcredit', '积分'),
('pb_ckpath', '/'),
('pb_ckdomain', ''),
('pb_dateformat', 'Y-m-d'),
('pb_maxsearchctrl', '0'),
('pb_reposttime', '3'),
('pb_aperpage', '15'),
('pb_cperpage', '20'),
('pb_showsign', '3'),
('pb_attoutlink', '1'),
('pb_uploadtopicimg', '1'),
('pb_commentlen', '3	50000'),
('pb_contentlen', '3	50000'),
('pb_titlelen', '3	100'),
('pb_allowupload', '1'),
('pb_attachdir', '0'),
('pb_uploadmaxsize', '204800'),
('pb_attachnum', '5'),
('pb_uploadfiletype', 'jpg,gif,zip,rar'),
('pb_creditdb', '2	1	2	1	1	1	2	1	2	1'),
('pb_gdcheck', '0'),
('pb_checkanswer', '5'),
('pb_ipallow', ''),
('pb_ipdeny', ''),
('pb_adminipallow', ''),
('reg_status', '1'),
('reg_closereason', '本站暂停注册'),
('reg_agreement', '<p>\r\n	　　第 一 条 本站所刊载的所有资料及图表仅供参考使用。用户明确同意其使用本站网络服务所存在的风险将完全由其自己承担；因其使用本站网络服务而产生的一切后果也由其自己承担，本站对用户不承担任何责任。。</p>\r\n<p>\r\n	　　第 二 条 本站的用户在参加网站举办的各种活动时，我们将在您的同意及确认下，通过注册表格等形式要求您提供一些个人资料，如：您的姓名、性别、年龄、出生日期、身份证号、家庭住址、教育程度、公司情况、所属行业等。请您绝对放心，我们在未经您同意的情况下，绝对不会将您的任何资料以任何方式泄露给任何第三方。</p>\r\n<p>\r\n	　　第 三 条 当政府司法机关依照法定程序要求本站披露个人资料时，我们将根据执法单位之要求或为公共安全之目的提供个人资料。在此情况下之任何披露，本站均得免责。</p>\r\n<p>\r\n	　　第 四 条 由于用户将个人密码告知他人或与他人共享注册帐户，由此导致的任何个人资料泄露，本站不负任何责任。</p>\r\n<p>\r\n	　　第 五 条 任何由于黑客攻击、计算机病毒侵入或发作、因政府管制而造成的暂时性关闭等影响网络正常经营的不可抗力而造成的个人资料泄露、丢失、被盗用或被窜改等，本站均得免责。</p>\r\n<p>\r\n	　　第 六 条 由于与本站链接的其它网站所造成之个人资料泄露及由此而导致的任何法律争议和后果，本站均得免责。</p>\r\n<p>\r\n	　　第 七 条 本站如因系统维护或升级而需暂停服务时，将事先公告。若因线路及非本公司控制范围外的硬件故障或其它不可抗力而导致暂停服务，于暂停服务期间造成的一切不便与损失，本站不负任何责任。</p>\r\n<p>\r\n	　　第 八 条 本站使用者因为违反本声明的规定而触犯中华人民共和国法律的，一切后果自己负责，本站不承担任何责任。</p>\r\n<p>\r\n	　　第 九 条 凡以任何方式登陆本站或直接、间接使用本站资料者，视为自愿接受本站声明的约束。</p>\r\n<p>\r\n	　　第 十 条 本声明未涉及的问题参见国家有关法律法规，当本声明与国家法律法规冲突时，以国家法律法规为准。</p>\r\n<p>\r\n	　　第十一条 本站不担保网络服务一定能满足用户的要求，也不担保网络服务不会中断，对网络服务的及时性、安全性、准确性也都不作担保。</p>\r\n<p>\r\n	　　第十二条 本站不保证为向用户提供便利而设置的外部链接的准确性和完整性，同时，对于该等外部链接指向的不由本站实际控制的任何网页上的内容，本站不承担任何责任。</p>\r\n<p>\r\n	　　第十三条 对于因不可抗力或本站不能控制的原因造成的网络服务中断或其它缺陷，本站网不承担任何责任，但将尽力减少因此而给用户造成的损失和影响。</p>\r\n<p>\r\n	　　第十四条 本站所发的转让、合作、加工、组装等广告，仅是项目信息介绍，不能作为接产和签定合同的依据，接产客户要实地考察，签订公证合同；对于用户由于看到此类信息，付诸相应反映而造成损失或其它后果的，本站不承担任何责任</p>\r\n<p>\r\n	　　第十五条 本站之声明以及其修改权、更新权及最终解释权均属本站网所有。</p>\r\n'),
('reg_allowsameip', '0'),
('reg_bannames', '版主,管理员,斑竹,admin,administrator'),
('reg_minname', '3'),
('reg_maxname', '15'),
('reg_credit', '1'),
('pb_rewriteext', '0'),
('pb_seotitle', 'PBDigg'),
('pb_seokeywords', 'PBDigg，digg程序，php digg程序'),
('pb_seodescription', 'PBDigg 是基于PHP + MYSQL的开源Digg社区资讯系统，经过完善设计并适用于各种服务器环境，如：UNIX、LINUX、WINDOWS等，是一个高效、全新、快速、优秀的网站解决方案。PBDigg集DIGG民主投票、网站内容发掘、社会性标签tag归类、主题评论、RSS订阅等多种WEB2.0元素于一体。Digg的宗旨是：发掘、推荐、分享、交流。'),
('pb_seomore', ''),
('pb_msg', '10'),
('pb_fav', '10'),
('pb_indextitle', '50'),
('pb_indexcontent', '200'),
('pb_anonnews', '0'),
('words_banned', ''),
('pb_loadavg', '0'),
('pb_icp', '<a href="http://www.miibeian.gov.cn" target="_blank">苏ICP备06013559号</a>'),
('pb_statistic', '<script language="javascript" type="text/javascript" src="http://js.users.51.la/368390.js"></script>\r\n<noscript><a href="http://www.51.la/?368390" target="_blank"><img alt="我要啦免费统计" src="http://img.users.51.la/368390.asp" style="border:none" /></a></noscript>'),
('pb_lang', 'zh'),
('pb_rewrite', '0'),
('pb_getpw', '0'),
('pb_online', '10'),
('pb_torder', 'diggdate'),
('pb_dformat', '0'),
('pb_titlelink', '0'),
('pb_titleubb', '15'),
('pb_topicstylesize', '100	100'),
('pb_mautoplay', '1'),
('pb_tftopicimg', '1'),
('pb_tubbtype', 'flash	media	em'),
('pb_robots', '0'),
('pb_urlsaveimg', '1'),
('pb_topicthumbsize', '100	100'),
('pb_fautoplay', '1'),
('pb_mplayersize', '500	500'),
('pb_signubbtype', ''),
('pb_tperpage', '100'),
('pb_topicthumb', '1'),
('pb_previewsize', '0	300'),
('pb_watertype', '2'),
('pb_watertext', 'pbdigg.com'),
('pb_waterfont', 'cour.ttf'),
('pb_waterfontsize', '20'),
('pb_waterfontcolor', '#ff0000'),
('pb_waterimg', 'mark.png'),
('pb_watertransition', '85'),
('pb_waterquality', '75'),
('pb_waterminsize', '300	300'),
('pb_waterposition', '8'),
('pb_sitehash', 'nXhxHCNptE'),
('pb_adminsafecode', '0'),
('pb_selfavat', '1'),
('pb_pserverapi', 'passport_client.php'),
('reg_emailactive', '0'),
('reg_sendemail', '0'),
('reg_emailcontent', '您好{!--username--}：\r\n\r\n欢迎加入{!--sitename--}网!\r\n\r\n您的个人登录信息：\r\n\r\n用户名：{!--username--}\r\n密码：{!--password--}\r\n\r\n为确保安全，请不要泄露上述信息。\r\n\r\n\r\n(这是一封自动产生的email，请勿回复。)\r\n                                                                  \r\n------------------------------------------------------------------'),
('pb_mailtype', '0'),
('pb_smtphost', ''),
('pb_smtpauth', '0'),
('pb_smtpuser', ''),
('pb_smtppw', ''),
('pb_smtpport', '25'),
('pb_jstransfer', '0'),
('pb_jstime', '3600'),
('pb_jsurl', ''),
('pb_ifbury', '1'),
('pb_ifdigg', '1'),
('pb_ifpost', '1'),
('pb_ifcomment', '1'),
('pb_cachetime', '600'),
('pb_cubbtype', 'flash	media	em'),
('pb_passport', '0'),
('pb_tday', '7776000'),
('pb_contentthumbsize', '0	600'),
('words_links', ''),
('pb_ucenable', '0'),
('pb_tagcolor', '1'),
('pb_html', '0'),
('pb_ckpre', 'l*W+Hl$'),
('pb_contentthumb', '1'),
('pb_corder', 'postdate	desc'),
('pb_ccheck', '0'),
('pb_trackback', '0'),
('words_replace', ''),
('pb_diggname', '顶'),
('pb_buryname', '埋'),
('pb_reeditlimit', '0'),
('pb_selfad', '0'),
('pb_checkquestion', '1+1=?'),
('pb_htmldir', 'Ymd'),
('pb_passportkey', '{!-U!*]Oc{{~7D]@'),
('pb_passporttype', 'client'),
('pb_pclienturl', 'http://localhost/idc/'),
('pb_pclientregister', 'register.php'),
('pb_pclientlogin', 'login.php'),
('uc_enable', '0'),
('uc_host', ''),
('uc_user', ''),
('uc_password', ''),
('uc_dbname', ''),
('uc_charset', 'gbk'),
('uc_prefix', ''),
('pb_shtmldir', 'show'),
('uc_key', ''),
('pb_otherlink', '10'),
('pb_cpovertime', '900'),
('pb_chtmldir', 'category'),
('uc_url', ''),
('pb_pclientlogout', 'login.php?action=quit'),
('pb_extenable', '0'),
('uc_id', '0'),
('uc_avatar', '0'),
('uc_space', '0'),
('pb_signsize', '600	500'),
('pb_timecorrect', '0'),
('pb_gdcodetype', '4'),
('pb_gdheight', '53'),
('pb_gdwidth', '130'),
('pb_gdcodenum', '4'),
('pb_sitedir', '/'),
('pb_signimgsize', '122	111'),
('pb_copyctrl', '0'),
('pb_taglink', '1'),
('pb_attoutput', '0'),
('pb_outputmaxsize', '614400'),
('pb_autoshield', '1'),
('pb_regqa', '1'),
('uc_msg', '0'),
('uc_friend', '0'),
('uc_spaceurl', ''),
('pb_k', '');
DROP TABLE `pb_friends`;
CREATE TABLE `pb_friends` (
  `fid` int(10) unsigned NOT NULL auto_increment,
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `fuid` mediumint(8) unsigned NOT NULL default '0',
  `status` tinyint(1) unsigned NOT NULL default '0',
  `createdate` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`fid`),
  UNIQUE KEY `uid` (`uid`,`fuid`),
  KEY `fuid` (`fuid`)
);
DROP TABLE `pb_fsession`;
CREATE TABLE `pb_fsession` (
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `attachment` text NOT NULL,
  `timesession` int(10) unsigned NOT NULL default '0',
  UNIQUE KEY `uid` (`uid`,`timesession`),
  KEY `timesession` (`timesession`)
);
DROP TABLE `pb_links`;
CREATE TABLE `pb_links` (
  `lid` smallint(6) NOT NULL auto_increment,
  `displayorder` tinyint(3) unsigned NOT NULL default '0',
  `ifshow` tinyint(1) unsigned NOT NULL default '1',
  `sitename` varchar(255) NOT NULL default '',
  `siteurl` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `logo` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`lid`)
) ;
INSERT INTO `pb_links` (`lid`, `displayorder`, `ifshow`, `sitename`, `siteurl`, `description`, `logo`) VALUES (1, 0, 1, 'pbdigg', 'http://www.pbdigg.net/', 'pbdigg', 'http://www.pbdigg.net/logo.gif'),(2, 1, 1, '8du数据', 'http://www.8du.cc/', '8du数据', '');
DROP TABLE `pb_memberexp`;
CREATE TABLE `pb_memberexp` (
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `qq` varchar(12) NOT NULL default '',
  `msn` varchar(255) NOT NULL default '',
  `site` varchar(255) NOT NULL default '',
  `location` varchar(30) NOT NULL default '',
  `birthday` date NOT NULL default '0000-00-00',
  `signature` text NOT NULL,
  `showsign` tinyint(1) unsigned NOT NULL default '0',
  `ctsig` tinyint(1) unsigned NOT NULL default '1',
  UNIQUE KEY `uid` (`uid`)
);
DROP TABLE `pb_members`;
CREATE TABLE `pb_members` (
  `uid` mediumint(8) unsigned NOT NULL auto_increment,
  `username` varchar(20) NOT NULL default '',
  `password` char(32) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `adminid` tinyint(3) NOT NULL default '0',
  `groupid` tinyint(3) NOT NULL default '-1',
  `publicemail` tinyint(1) NOT NULL default '0',
  `gender` tinyint(1) unsigned NOT NULL default '3',
  `regip` char(15) NOT NULL default '',
  `regdate` int(10) unsigned NOT NULL default '0',
  `realgroup` tinyint(3) unsigned NOT NULL default '7',
  `postnum` mediumint(8) unsigned NOT NULL default '0',
  `commentnum` mediumint(8) unsigned NOT NULL default '0',
  `diggnum` int(10) unsigned NOT NULL default '0',
  `burynum` int(10) unsigned NOT NULL default '0',
  `currency` int(10) NOT NULL default '0',
  `lastip` char(15) NOT NULL default '',
  `lastvisit` int(10) unsigned NOT NULL default '0',
  `lastpost` int(10) unsigned NOT NULL default '0',
  `lastcomment` int(10) unsigned NOT NULL default '0',
  `lastupload` int(10) unsigned NOT NULL default '0',
  `lastsearch` int(10) unsigned NOT NULL default '0',
  `uploadnum` mediumint(8) unsigned NOT NULL default '0',
  `newmsg` tinyint(3) unsigned NOT NULL default '0',
  `friendnum` smallint(6) unsigned NOT NULL default '0',
  `collectionnum` smallint(6) unsigned NOT NULL default '0',
  `visitnum` int(10) unsigned NOT NULL default '0',
  `ucuid` mediumint(8) unsigned NOT NULL default '0',
  `avatar` varchar(255) NOT NULL,
  PRIMARY KEY  (`uid`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `regdate` (`regdate`)
) ;
DROP TABLE `pb_message`;
CREATE TABLE `pb_message` (
  `mid` int(10) unsigned NOT NULL auto_increment,
  `fuid` mediumint(8) unsigned NOT NULL default '0',
  `tuid` mediumint(8) unsigned NOT NULL default '0',
  `title` varchar(100) NOT NULL default '',
  `type` enum('r','s') NOT NULL default 'r',
  `content` text NOT NULL,
  `postdate` int(10) unsigned NOT NULL default '0',
  `ifread` tinyint(1) unsigned NOT NULL default '0',
  `ifsys` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`mid`),
  KEY `tuid` (`tuid`,`type`,`postdate`),
  KEY `fuid` (`fuid`,`type`,`postdate`)
);
DROP TABLE `pb_modconfig`;
CREATE TABLE `pb_modconfig` (
  `mvar` varchar(50) NOT NULL,
  `mtext` text NOT NULL,
  UNIQUE KEY `mvar` (`mvar`)
);
INSERT INTO `pb_modconfig` (`mvar`, `mtext`) VALUES ('article_status', '1');
DROP TABLE `pb_module`;
CREATE TABLE `pb_module` (
  `mid` tinyint(3) unsigned NOT NULL auto_increment,
  `identifier` varchar(30) NOT NULL default '',
  `name` varchar(30) NOT NULL default '',
  `author` varchar(30) NOT NULL default '',
  `publish` int(10) unsigned NOT NULL default '0',
  `version` char(10) NOT NULL default '',
  `description` text NOT NULL,
  `copyright` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`mid`),
  UNIQUE KEY `identifier` (`identifier`)
);
INSERT INTO `pb_module` (`mid`, `identifier`, `name`, `author`, `publish`, `version`, `description`, `copyright`) VALUES (1, 'article', '文章', '', 1239482147, '', '', 'Powered by PBDigg 2007-2009 PBDigg.com');
DROP TABLE `pb_plugins`;
CREATE TABLE `pb_plugins` (
  `pid` smallint(6) unsigned NOT NULL auto_increment,
  `status` tinyint(1) unsigned NOT NULL default '1',
  `pname` varchar(30) NOT NULL default '',
  `pmark` varchar(30) NOT NULL default '',
  `version` varchar(15) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `actionhook` varchar(255) NOT NULL default '',
  `filterhook` varchar(255) NOT NULL default '',
  `withstage` tinyint(1) unsigned NOT NULL default '0',
  `copyright` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`pid`)
);
DROP TABLE `pb_scaches`;
CREATE TABLE `pb_scaches` (
  `hash` varchar(32) NOT NULL default '',
  `keywords` varchar(255) NOT NULL default '',
  `num` mediumint(8) unsigned NOT NULL default '0',
  `ids` mediumtext NOT NULL,
  `searchip` char(15) NOT NULL default '',
  `searchtime` int(10) unsigned NOT NULL default '0',
  `exptime` int(10) unsigned NOT NULL default '0',
  UNIQUE KEY `khash` (`hash`),
  KEY `exptime` (`exptime`)
);
DROP TABLE `pb_sitestat`;
CREATE TABLE `pb_sitestat` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `newmember` varchar(20) NOT NULL default '',
  `membernum` mediumint(8) unsigned NOT NULL default '0',
  `catenum` int(10) unsigned NOT NULL default '0',
  `artnum` int(10) unsigned NOT NULL default '0',
  `comnum` int(10) unsigned NOT NULL default '0',
  `buildtime` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`id`)
);
INSERT INTO `pb_sitestat` (`id`, `newmember`, `membernum`, `catenum`, `artnum`, `comnum`, `buildtime`) VALUES (1, '', 0, 0, 0, 0, '0000-00-00');
DROP TABLE `pb_specialtpl`;
CREATE TABLE `pb_specialtpl` (
  `tplid` tinyint(3) unsigned NOT NULL auto_increment,
  `tplname` char(50) NOT NULL default '',
  `tplfunc` char(50) NOT NULL default '',
  `template` text NOT NULL,
  PRIMARY KEY  (`tplid`)
);
INSERT INTO `pb_specialtpl` (`tplid`, `tplname`, `tplfunc`, `template`) VALUES (1, '热门标签', 'hottags', '<a href="index.php?tag={!--encodetagname--}" target="_blank"><span style="{!--color--}">{!--tagname--}</span><em>({!--usenum--})</em></a>'),
(2, '系统标签', 'systags', '<a href="javascript:void(0);" onclick="addTag(''{!--tagname--}'')">{!--tagname--}({!--usenum--})</a>'),
(3, '上下文导航', 'prevnext', '上一篇：{!--prev--}<br />\r\n下一篇：{!--next--}'),
(4, '相关文章', 'linkarticle', '<li><a href="{!--turl--}" title="{!--altsubject--}">{!--subject--}</a></li>');
DROP TABLE `pb_tagcache`;
CREATE TABLE `pb_tagcache` (
  `tcacheid` int(10) unsigned NOT NULL auto_increment,
  `tagid` smallint(6) unsigned NOT NULL default '0',
  `tid` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`tcacheid`),
  KEY `tagid` (`tagid`),
  KEY `tid` (`tid`)
);
DROP TABLE `pb_tags`;
CREATE TABLE `pb_tags` (
  `tagid` smallint(6) unsigned NOT NULL auto_increment,
  `tagname` varchar(30) NOT NULL default '',
  `usenum` mediumint(8) unsigned NOT NULL default '0',
  `hits` int(10) unsigned NOT NULL default '0',
  `ifopen` tinyint(1) unsigned NOT NULL default '1',
  `ifsys` tinyint(1) unsigned NOT NULL default '0',
  `tagpic` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`tagid`),
  KEY `tagname` (`tagname`),
  KEY `usenum` (`usenum`,`ifopen`),
  KEY `hits` (`hits`)
);
DROP TABLE `pb_tdata`;
CREATE TABLE `pb_tdata` (
  `tdid` mediumint(8) unsigned NOT NULL auto_increment,
  `cid` smallint(6) unsigned NOT NULL default '0',
  `tid` mediumint(8) unsigned NOT NULL default '0',
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `type` enum('digg','bury','collection') NOT NULL default 'digg',
  PRIMARY KEY  (`tdid`),
  KEY `uid` (`uid`,`type`),
  KEY `tid` (`tid`,`uid`)
);
DROP TABLE `pb_templates`;
CREATE TABLE `pb_templates` (
  `tplid` smallint(6) unsigned NOT NULL auto_increment,
  `tplname` varchar(255) NOT NULL default '',
  `tplmark` varchar(30) NOT NULL default '',
  `cachetime` smallint(6) NOT NULL default '0',
  `trantattribute` text NOT NULL,
  `trantorder` varchar(30) NOT NULL default '',
  `trantby` enum('asc','desc') NOT NULL default 'asc',
  `trantnum` tinyint(3) NOT NULL default '0',
  `fields` varchar(255) NOT NULL default '',
  `specialfields` varchar(255) NOT NULL default '',
  `replacefields` varchar(255) NOT NULL default '',
  `cotentlimit` smallint(6) unsigned NOT NULL default '0',
  `titlelimit` tinyint(3) unsigned NOT NULL default '0',
  `timeformat` varchar(255) NOT NULL default '',
  `tranttype` enum('article','comment','member','html','sql') NOT NULL default 'article',
  `tplcontent` text NOT NULL,
  `querysql` text NOT NULL,
  PRIMARY KEY  (`tplid`)
);
DROP TABLE `pb_threads`;
CREATE TABLE `pb_threads` (
  `tid` mediumint(8) unsigned NOT NULL auto_increment,
  `cid` smallint(6) unsigned NOT NULL default '0',
  `author` varchar(30) NOT NULL default '',
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `subject` varchar(100) NOT NULL default '',
  `contentlink` varchar(255) NOT NULL default '',
  `linkhost` varchar(255) NOT NULL default '',
  `topicimg` varchar(255) NOT NULL default '',
  `ifcheck` tinyint(1) unsigned NOT NULL default '0',
  `ifshield` tinyint(1) unsigned NOT NULL default '0',
  `iflock` tinyint(1) unsigned NOT NULL default '0',
  `topped` tinyint(1) unsigned NOT NULL default '0',
  `postdate` int(10) unsigned NOT NULL default '0',
  `postip` char(15) NOT NULL default '',
  `digg` int(10) unsigned NOT NULL default '0',
  `diggdate` int(10) unsigned NOT NULL default '0',
  `bury` int(10) unsigned NOT NULL default '0',
  `burydate` int(10) unsigned NOT NULL default '0',
  `views` int(10) unsigned NOT NULL default '0',
  `comments` int(10) unsigned NOT NULL default '0',
  `commentdate` int(10) unsigned NOT NULL default '0',
  `keywords` varchar(255) NOT NULL default '',
  `pbrank` mediumint(8) unsigned NOT NULL default '0',
  `commend` tinyint(1) unsigned NOT NULL default '0',
  `commendpic` varchar(255) NOT NULL default '',
  `first` tinyint(1) unsigned NOT NULL default '0',
  `module` tinyint(3) unsigned NOT NULL default '1',
  `realurl` varchar(255) NOT NULL default '',
  `ishtml` tinyint(1) unsigned NOT NULL default '0',
  `summary` text NOT NULL,
  `titlecolor` char(6) NOT NULL default '',
  `titlestyle` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`tid`),
  KEY `uid` (`uid`),
  KEY `digg` (`digg`),
  KEY `diggdate` (`diggdate`,`ifcheck`,`postdate`),
  KEY `cid` (`cid`,`module`,`ifcheck`,`diggdate`)
);
DROP TABLE `pb_usergroups`;
CREATE TABLE `pb_usergroups` (
  `groupid` smallint(6) unsigned NOT NULL auto_increment,
  `adminid` smallint(6) unsigned NOT NULL default '0',
  `gtype` enum('system','special','member') NOT NULL default 'member',
  `grouptitle` varchar(30) NOT NULL default '',
  `uplower` int(10) NOT NULL default '0',
  `uphigher` int(10) unsigned NOT NULL default '0',
  `allowvisit` tinyint(1) NOT NULL default '0',
  `allowsort` tinyint(1) NOT NULL default '0',
  `allowread` tinyint(1) NOT NULL default '0',
  `allowpost` tinyint(1) NOT NULL default '0',
  `allowcomment` tinyint(1) NOT NULL default '0',
  `allowdigg` tinyint(1) NOT NULL default '0',
  `allowbury` tinyint(1) NOT NULL default '0',
  `allowbsearch` tinyint(1) NOT NULL default '0',
  `allowasearch` tinyint(1) NOT NULL default '0',
  `searchmax` smallint(5) unsigned NOT NULL default '0',
  `allowreport` tinyint(1) NOT NULL default '0',
  `allowhtml` tinyint(1) NOT NULL default '0',
  `allowmsg` tinyint(1) NOT NULL default '0',
  `msgmax` smallint(5) unsigned NOT NULL default '0',
  `allowfavors` tinyint(1) NOT NULL default '0',
  `favorsmax` smallint(5) unsigned NOT NULL default '0',
  `allowavatar` tinyint(1) NOT NULL default '0',
  `allowaupload` tinyint(1) NOT NULL default '0',
  `uploadmax` mediumint(8) unsigned NOT NULL default '0',
  `uploadtype` varchar(255) NOT NULL default '',
  `allowurl` tinyint(1) unsigned NOT NULL default '0',
  `allowtimestamp` tinyint(1) unsigned NOT NULL default '0',
  `allowinitstatus` tinyint(1) unsigned NOT NULL default '0',
  `inithit` smallint(6) unsigned NOT NULL default '0',
  `initdigg` smallint(6) unsigned NOT NULL default '0',
  `initbury` smallint(6) unsigned NOT NULL default '0',
  `allowad` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`groupid`)
);
INSERT INTO `pb_usergroups` (`groupid`, `adminid`, `gtype`, `grouptitle`, `uplower`, `uphigher`, `allowvisit`, `allowsort`, `allowread`, `allowpost`, `allowcomment`, `allowdigg`, `allowbury`, `allowbsearch`, `allowasearch`, `searchmax`, `allowreport`, `allowhtml`, `allowmsg`, `msgmax`, `allowfavors`, `favorsmax`, `allowavatar`, `allowaupload`, `uploadmax`, `uploadtype`, `allowurl`, `allowtimestamp`, `allowinitstatus`, `inithit`, `initdigg`, `initbury`, `allowad`) VALUES (1, 1, 'system', '系统管理员', 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 100, 1, 100, 1, 1, 204800, 'jpeg,jpg,gif,png,zip,rar', 0, 1, 1, 100, 100, 100, 1),
(2, 2, 'system', '前台管理员', 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 0, 1, 100, 1, 100, 1, 1, 204800, 'jpg,jpeg,gif,png,zip,rar', 1, 1, 1, 0, 0, 0, 1),
(3, 0, 'system', '禁止发言', 0, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0),
(4, 0, 'system', '禁止访问', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 1, 1, 1, 0, 0, 0, 0),
(5, 0, 'system', '游客', 0, 0, 1, 0, 1, 1, 1, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0),
(6, 0, 'system', '待验证会员', 0, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 10, 1, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0),
(7, 0, 'member', '普通会员', -2147483647, 2147483647, 1, 0, 1, 1, 1, 1, 1, 1, 0, 0, 1, 0, 1, 100, 1, 100, 1, 1, 204800, 'jpg,jpeg,gif,png,zip,rar', 0, 0, 0, 0, 0, 0, 0),
(8, 0, 'special', 'vip贵宾', 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0);