<?php
/**
 * @version $Id: initupload.php v3.0 $
 * @package PBDigg
 * @copyright Copyright (C) 2007 - 2009 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

define('PB_PAGE', 'initupload');
require_once '../../include/common.inc.php';

if (checkPostHash($verify) && $allowaupload && $pb_allowupload && in_array($type, array('attachment', 'commend')))
{
	$allowuploadsize = $swfext = $attachnum = '';
	$mid = isset($mid) ? intval($mid) : 1;
	if ($module->checkModuleID($mid))
	{
		$currentModuleObj = $module->getModuleObject($mid);
		$exts = $currentModuleObj->getUploadExts();
		foreach ($exts as $ext)
		{
			$swfext .= '*.'.$ext.';';
		}
		$allowuploadsize = ceil($currentModuleObj->getUploadSize() / 1024);
		$attachnum = $currentModuleObj->getUploadNums($type);
	}

echo <<<EOT

var swfu;
var timesession = "{$timestamp}";
var uploadtype = "{$type}";

\$(document).ready(function(){


swfu = new SWFUpload({
		flash_url:pb_url+"js/swfupload/swfupload.swf?"+Math.random(),
		upload_url:pb_url+"include/upload.inc.php",
		post_params:{"timesession":timesession,"action":"upload","type":uploadtype,"verify":verify,"pb_auth":"{$pb_auth}","mid":"{$mid}"},
		file_size_limit:{$allowuploadsize},
		file_types:"{$swfext}",
		file_types_description:"All Files",
		file_upload_limit:{$attachnum},
		custom_settings : {progressTarget : "divFileProgress"},

		button_image_url: "",
		button_width: 90,
		button_height: 18,
		button_placeholder_id: "buttonPlaceHolder",
		button_text: "{$common_message['upload']}",
		button_text_style: "",
		button_text_top_padding: 3,
		button_text_left_padding: 12,
		button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
		button_cursor: SWFUpload.CURSOR.HAND,
		
		file_queue_error_handler:fileQueueError,
		file_dialog_complete_handler:fileDialogComplete,
		upload_start_handler:uploadStart,
		upload_progress_handler:uploadProgress,
		upload_error_handler:uploadError,
		upload_success_handler:uploadSuccess,
		upload_complete_handler:uploadComplete
	});

\$('#timesession').val(timesession);
EOT;

if ($type == 'attachment') echo 'loadatt();';

echo '})';
}
	
?>

