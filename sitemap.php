<?php
/**
 * @version $Id: sitemap.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_PAGE', 'sitemap');
require_once './include/common.inc.php';

$pb_seotitle = $common_message['sitemap_title'];

require_once pt_fetch('sitemap');

PBOutPut();
?>
