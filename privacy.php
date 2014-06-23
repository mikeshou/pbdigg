<?php

error_reporting(0);

define('IN_PBDIGG', TRUE);

include_once './data/sql.inc.php';
unset($db_host, $db_name, $db_password, $siteFounder);
include_once './data/cache/cache_config.php';
include_once './data/cache/cache_reg.php';
include_once './languages/'.$pb_lang.'/common.lang.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $db_charset;?>" />
<title><?php echo $common_message['privacy_title'];?></title>
</head>

<body>
<div><h1><?php echo $common_message['privacy_title'];?></h1></div>
<div>
<p><strong><?php echo $common_message['privacy_title'];?></strong></p>
<?php
echo $reg_agreement;
?>
</div>
</body>
</html>