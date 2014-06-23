<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_magic_quotes_runtime(0);

define('UC_CLIENT_VERSION', '1.5.0');
define('UC_CLIENT_RELEASE', '20081212');

define('API_DELETEUSER', 1);
define('API_RENAMEUSER', 1);
define('API_GETTAG', 1);
define('API_SYNLOGIN', 1);
define('API_SYNLOGOUT', 1);
define('API_UPDATEPW', 1);
define('API_UPDATEBADWORDS', 1);
define('API_UPDATEHOSTS', 1);
define('API_UPDATEAPPS', 1);
define('API_UPDATECLIENT', 1);
define('API_UPDATECREDIT', 1);
define('API_GETCREDITSETTINGS', 1);
define('API_GETCREDIT', 1);
define('API_UPDATECREDITSETTINGS', 1);

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');

define('IN_PBDIGG', TRUE);
define('PBDIGG_ROOT', str_replace('\\', '/', substr((dirname(__FILE__)), 0, -3)));
define('IN_UCAPI', TRUE);

require_once PBDIGG_ROOT.'data/cache/cache_config.php';

!$pb_ucenable && exit(API_RETURN_FORBIDDEN);

$get = $post = $_PBENV = array();

$_PBENV['PHP_SELF'] = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];

if (!$pb_siteurl)
{
	//获取当前pb访问地址
	$sHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? (int)$_SERVER['SERVER_NAME'] : (int)getenv('SERVER_NAME'));
	$sPort = isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : (int)getenv('SERVER_PORT');
	$sSecure = (isset($_SERVER['HTTPS']) || $sPort == 433) ? 1 : 0;
	$sDir = substr(trim(dirname($_PBENV['PHP_SELF'])), 0, -3);
	$pb_siteurl = 'http'.(isset($_SERVER['HTTPS']) || $sPort == 433 ? 's' : '').'://'.preg_replace('~[\\\\/]{2,}~i', '/', $sHost.($sPort && (!$sSecure && $sPort != 80 || $sSecure && $sPort != 433) && strpos($sHost, ':') === FALSE ? ':'.$sPort : '').$sDir);
}
$_PBENV['PB_URL'] = htmlspecialchars($pb_siteurl);

if (isset($_SERVER['HTTP_CLIENT_IP']))
{
	$currentIP = $_SERVER['HTTP_CLIENT_IP'];
}
elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
{
	$currentIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else
{
	$currentIP = $_SERVER['REMOTE_ADDR'];
}
$_PBENV['PB_IP'] = preg_match('~^[\d\.]{7,15}$~', $currentIP, $match) ? $match[0] : 'unknow';
unset($match);

require_once PBDIGG_ROOT.'include/uc.inc.php';
require_once PBDIGG_ROOT.'data/sql.inc.php';
require_once PBDIGG_ROOT.'include/global.func.php';
require_once PBDIGG_ROOT.'include/'.$pb_datatype.'.class.php';
require_once PBDIGG_ROOT.'include/member.class.php';

$timestamp = time();
$pb_timecorrect && $timestamp += $pb_timecorrect;

parse_str(authcode($_GET['code'], 'DECODE', UC_KEY), $get);
!$get && exit('Invalid Request');
reS($get);
($timestamp - $get['time'] > 3600) && exit('Authracation has expiried');

require_once PBDIGG_ROOT.'include/uc_client/lib/xml.class.php';
$post = xml_unserialize(file_get_contents('php://input'));
reS($post);

$DB = new MySQL($db_host, $db_username, $db_password, $db_name, $db_pconnect);
unset($db_password);
$Member = new Member();

$action = $get['action'];

switch ($get['action'])
{
	case 'test':
		exit(API_RETURN_SUCCEED);
		break;

	case 'deleteuser'://删除用户
		!API_DELETEUSER && exit(API_RETURN_FORBIDDEN);
		//DZ接口问题，貌似删除不会成功！！！有问题去找戴志康
		$Member->Action('del', explode(',', stripslashes($get['ids'])));
		exit(API_RETURN_SUCCEED);
		break;

	case 'renameuser'://更改用户名
		$ucuid = (int)$get['uid'];
		(!API_RENAMEUSER || !$ucuid) && exit(API_RETURN_FORBIDDEN);
		$newusername = $get['newusername'];
		$uid = $DB->fetch_one("SELECT uid FROM {$db_prefix}members WHERE ucuid = '$ucuid'");
		if ($uid)
		{
			$uid = (int)$uid['uid'];
			$DB->db_exec("UPDATE {$db_prefix}members SET username = '$newusername' WHERE uid = '$uid'");
			$DB->db_exec("UPDATE {$db_prefix}threads SET author = '$newusername' WHERE uid = '$uid'");
			$DB->db_exec("UPDATE {$db_prefix}comments SET author = '$newusername' WHERE uid = '$uid'");
		}
		exit(API_RETURN_SUCCEED);
		break;		

	case 'updatepw'://更改密码
		!API_UPDATEPW && exit(API_RETURN_FORBIDDEN);
		//不更改
//		$Member->Action('password', array($get['username'], $get['password'], $get['password'], '', FALSE));
		exit(API_RETURN_SUCCEED);
		break;

	case 'gettag'://获取标签 API 接口
		!API_GETTAG && exit(API_RETURN_FORBIDDEN);
		$tagname = trim($get['id']);
		if(!$tagname || !isTag($tagname))
		{
			exit(API_RETURN_FAILED);
		}
		$rs = $DB->fetch_one("SELECT ifopen FROM {$db_prefix}tags WHERE tagname = '$tagname'");
		!$rs['ifopen'] && exit(API_RETURN_FAILED);

		$threads = array();
		$query = $DB->db_query("SELECT t.tid, t.subject, t.postdate FROM {$db_prefix}threads t LEFT JOIN {$db_prefix}tagcache tc USING (tid) LEFT JOIN {$db_prefix}tags tg ON tc.tagid = tg.tagid WHERE tg.tagname = '$tagname' ORDER BY t.tid DESC LIMIT 10");
		while ($rs = $DB->fetch_array($query))
		{
			$threads[] = array(
				'subject' => $rs['subject'],
				'postdate' => gdate($rs['postdate'], 'Y-m-d'),
				'turl' => rewriteThread($rs['tid'])
			);
		}
		echo uc_serialize(array($tagname, $threads), 1);
		break;

		case 'synlogin'://同步登录 API 接口
		!API_SYNLOGIN && exit(API_RETURN_FORBIDDEN);
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		$uid = (int)$get['uid'];
		$cookietime = '';
		$auth = $DB->fetch_one("SELECT uid, password FROM {$db_prefix}members WHERE ucuid = '$uid'");
		if ($auth)
		{
			sCookie('pb_auth', PEncode($auth['uid']."\t".pbNewPW($auth['password']), $pb_sitehash), $cookietime);
		}
		break;

	case 'synlogout'://同步登出 API 接口
		!API_SYNLOGOUT && exit(API_RETURN_FORBIDDEN);
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		clearcookie();
		break;

	case 'updatebadwords'://更新关键字列表
		!API_UPDATEBADWORDS && exit(API_RETURN_FORBIDDEN);
		if (is_array($post))
		{
			require_once PBDIGG_ROOT.'data/cache/cache_words.php';
			foreach ($post as $k => $v)
			{
				$words_banned[] = $words_replace[] = stripslashes($v);
			}
			PWriteFile(PBDIGG_ROOT.'data/cache/cache_words.php', "<?php\n\r\$words_banned = ".pb_var_export($words_banned).";\n\r\$words_replace = ".pb_var_export($words_replace).";\n\r\$words_links = ".pb_var_export($words_links).";\n\r?>");
		}
		exit(API_RETURN_SUCCEED);
		break;

	case 'updatehosts'://更新HOST文件
		!API_UPDATEHOSTS && exit(API_RETURN_FORBIDDEN);
		exit(API_RETURN_SUCCEED);
		break;

	case 'updateapps'://更新应用列表
		!API_UPDATEAPPS && exit(API_RETURN_FORBIDDEN);
		exit(API_RETURN_SUCCEED);
		break;

	case 'updateclient'://更新客户端缓存
		!API_UPDATECLIENT && exit(API_RETURN_FORBIDDEN);
		exit(API_RETURN_SUCCEED);
		break;

	case 'updatecredit'://更新用户积分
		!UPDATECREDIT && exit(API_RETURN_FORBIDDEN);
		$amount = intval($get['amount']);
		$uid = intval($get['uid']);
		$DB->db_exec("UPDATE {$db_prefix}members SET currency = currency + '$amount' WHERE ucuid = '$uid'");
		exit(API_RETURN_SUCCEED);
		break;

	case 'getcreditsettings'://向 UCenter 提供积分设置
		!GETCREDITSETTINGS && exit(API_RETURN_FORBIDDEN);
		echo uc_serialize(array('1' => array($pb_customcredit, '')));
		break;

	case 'updatecreditsettings'://更新应用积分设置
		!API_UPDATECREDITSETTINGS && exit(API_RETURN_FORBIDDEN);
		exit(API_RETURN_SUCCEED);
		break;

	case 'getcredit'://此接口负责把应用程序的积分设置传递给 UCenter
		$uid = intval($get['uid']);
		echo $db->fetch_first("SELECT currency FROM {$db_prefix}members WHERE ucuid = '$uid'");
		exit(API_RETURN_SUCCEED);
		break;

	default:
		exit(API_RETURN_FAILED);
		break;
}


function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

	$ckey_length = 4;

	$key = md5($key ? $key : UC_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}
