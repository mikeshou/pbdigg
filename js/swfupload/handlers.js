function addAtt(serverData,file)
{
	var data = serverData.split(',');
	var id = data[0];
	var type = data[1];
	var img = '<img src="'+pb_url+'include/upload.inc.php?action=show&type='+type+'&id='+id+'&timesession='+timesession+'&verify='+verify+'&random='+Math.random()+'" /><span>';
	
	if (type == 'attachment')
	{
		img += '<a onclick="insertatt(\'[file='+id+']\')" href="javascript:void(0);">'+I18N.insert+'</a> | ';
	}
	img += '<a onclick="delatt('+id+',\''+type+'\')" href="javascript:void(0);">'+I18N.del+'</a></span>';
	$('#uploadfile').append('<div class="attachment_show" id="attachment_'+id+'"></div>');
	$('#attachment_'+id).html(img);
}
function insertatt(str)
{
	editor.pasteHTML(str)
}
function delatt(id, type)
{
	$.ajax({
		type: 'GET',
		url: pb_url+'include/upload.inc.php',
		dataType: 'text',
		data: 'action=del&type=' + type + '&id=' + id + '&timesession=' + timesession + '&verify=' + verify + '&random=' + Math.random(),
		error: function(var1,var2,var3)
		{
			alert(var2.responseText);
			alert(I18N.del_attachment_error);
		},
		success: function(data)
		{
			$('#attachment_'+id).remove();
		}
	});
}
function supdelatt(id)
{
	$.ajax({
		type: 'POST',
		url: pb_url+'ajax.php',
		dataType: 'html',
		data: 'action=delattachment&aid=' + id + '&verify=' + verify + '&random=' + Math.random(),
		error: function(var1,var2,var3)
		{
			alert(I18N.del_attachment_error);
		},
		success: function(data)
		{
			if (data)
			{
				$('#e_attachment_'+id).remove();
			}
			else
			{
				alert(I18N.action_failed) ;
			}
		}
	});
}
function loadatt()
{
	$.get(pb_url+'include/upload.inc.php?action=load&type=attachment&timesession='+timesession+'&verify='+verify + '&random=' + Math.random(), function(data){
		if (data)
		{
			var aids = data.split(',');
			for (n in aids)
			{
				img = '<img src="'+pb_url+'include/upload.inc.php?action=show&type=attachment&id='+aids[n]+'&timesession='+timesession+'&verify='+verify+'" /><span><a onclick="insertatt(\'[file='+aids[n]+']\')" href="javascript:void(0);">'+I18N.insert+'</a> | <a onclick="delatt('+aids[n]+',\'attachment\')" href="javascript:void(0);">'+I18N.del+'</a></span>';
				$('#uploadfile').append('<div class="attachment_show" id="attachment_'+aids[n]+'"></div>');
				$('#attachment_'+aids[n]).html(img);
			}
		}
	});
}
//swfupload functions
function fileQueueError(file, errorCode, message)
{
	var errormsg;
	switch (errorCode) {
	case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
		errormsg = "请不要上传空文件";
		break;
	case SWFUpload.errorCode_QUEUE_LIMIT_EXCEEDED:
		errormsg = "队列文件数量超过设定值";
		break;
	case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
		errormsg = "文件尺寸超过设定值";
		break;
	case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
		errormsg = "文件类型不合法";
	default:
		errormsg = '上传错误，请与管理员联系！';
		break;
	}
	var progress = new FileProgress(file);
	progress.setError();
	progress.setStatus(errormsg);

}
function fileDialogComplete(numFilesSelected, numFilesQueued)
{
	if (numFilesSelected > 0) this.startUpload();
}
function uploadStart(file)
{
	var progress = new FileProgress(file);
	progress.setStatus("正在上传请稍后...");
	return true;
}
function uploadProgress(file, bytesLoaded, bytesTotal)
{
	var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
	var progress = new FileProgress(file);
	progress.setProgress(percent);
	progress.setStatus("正在上传请稍后...");
}
function uploadSuccess(file, serverData)
{
	addAtt(serverData,file);
	var progress = new FileProgress(file);
	progress.setSuccess();
	progress.setStatus("文件上传成功");
}
function uploadComplete(file)
{
	if (this.getStats().files_queued > 0)
	{
		 this.startUpload();
	}
}
function uploadError(file, errorCode, message) {
	var msg;
	switch (errorCode)
	{
		case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
			msg = "上传错误: " + message;
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
			msg = "上传错误";
			break;
		case SWFUpload.UPLOAD_ERROR.IO_ERROR:
			msg = "服务器 I/O 错误";
			break;
		case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
			msg = "服务器安全认证错误";
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
			msg = "附件安全检测失败，上传终止";
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			msg = '上传取消';
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			msg = '上传终止';
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			msg = '单次上传文件数限制为 '+swfu.settings.file_upload_limit+' 个';
			break;
		default:
			msg = message;
			break;
		}
	var progress = new FileProgress(file);
	progress.setError();
	progress.setStatus(msg);
}
function FileProgress(file)
{
	if (file != null) $('#progressName').html(file.name);
}
FileProgress.prototype.setProgress = function (percentage)
{
	$('#progressContainer').removeClass().addClass("progressContainer filegreen");
	$('#progressBarInProgress').css("width",percentage + "%");
}
FileProgress.prototype.setSuccess = function ()
{
	$('#progressContainer').removeClass().addClass("progressContainer fileblue");
	$('#progressBarInProgress').css("width","");
}
FileProgress.prototype.setError = function ()
{
	$('#progressContainer').removeClass().addClass("progressContainer filered");
	$('#progressBarInProgress').css("width","");
}
FileProgress.prototype.setStatus = function (status)
{
	$('#progressBarStatus').html(status);
}
