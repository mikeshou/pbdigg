<?php
/**
 * @version $Id: article_function.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

!defined('IN_PBDIGG') && exit('Access Denied');

function replace_atta($id, &$replace)
{
	return isset($replace[$id]) ? '[attachment='.$replace[$id].']' : '';
}

?>