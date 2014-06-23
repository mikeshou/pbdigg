var baseHeight;
var insertbody = 'comment_reply_body';

function showCaptcha(id)
{
	var captcha = $('#'+id);
	if (captcha.css('display') == 'none') captcha.css('display', '');
}
function storeCaret(textEl)
{
	if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
}
function keysubmit(event)
{
	if((event.ctrlKey && event.keyCode == 13) || (event.altKey && event.keyCode == 83))
	{
		$('#send_content').click();
	}
}
function comment(tid)
{
	var sendcomment = $('#send_content');
	var comment = $('#comment_reply_body').val();
	var captcha = $('#captcha').val();
	sendcomment.attr('disabled', 'disabled');
	openlayer('comment', I18N.add_comment_loading, 400, 300, false);
	if (comment.length < comment_minc || comment.length > comment_maxc)
	{
		contentlayer('comment', comment_length_error, true);
		sendcomment.attr('disabled', '');
		return false;
	}
	sendAjax('comment', 'action=comment&content='+encodeURIComponent(comment)+'&tid='+tid + '&captcha=' + captcha, finishComment);
}
function finishComment(data, textStatus)
{
	if (data[0] == '1')
	{
		eval('location.reload(true);');
	}
	else
	{
		$('#comment_reply_body').focus();
	}
	$('#captcha').val('');
	$('#checkcode').attr('src', pb_url + 'checkcode.php?' + '&random=' + Math.random());
	$('#send_content').attr('disabled', '');
	contentlayer('comment', data[1], true);
	return;
}
function sendAjax(id, param, func)
{
	$.ajax({
		type : 'POST',
		url : pb_url+'ajax.php',
		dataType : 'json',
		data : param +'&verify=' + verify + '&random=' + Math.random(),
		error : function(var1, var2, var3)
		{
			contentlayer(id, I18N.ajax_response_failed);
		},
		success : function(data, textStatus)
		{
			if (typeof func == 'function')
			{
				func(data, textStatus);
			}
			else
			{
				if (data[0] == '1')
				{
					contentlayer(id, data[1]);
				}
				else
				{
					$('#'+id+'_tip').html(data[1]);
				}
			}
		}
	});
	return;
}
function ajaxlayer(action, tid, rid)
{
	if (/^\d+?$/.test(tid) && /^\d+?$/.test(rid))
	{
		openlayer(action, I18N.ajax_loading, 400, 300, true);
		$.ajax({
			type: 'GET',
			url: pb_url+'ajax.php',
			dataType: 'json',
			data: 'action=' + action + '&tid=' + tid + '&rid=' + rid  + '&verify=' + verify + '&random=' + Math.random(),
			error : function (var1, var2, var3)
			{
				contentlayer(action, I18N.ajax_response_failed, false);
			},
			success: function(data)
			{
				contentlayer(action, data[1]);
				if (action == 'titlestyle')
				{
					includeScript('colorpicker/colorpicker.js');
				} else if (action == 'commendarticle') {
					includeScript('swfupload/initupload.php?verify='+verify+'&type=commend' + '&random=' + Math.random());
				}
			}
		});
	}
	return;
}
function finishEditComment(data, textStatus)
{
	var id = data[2].split('_');
	if (data[0] == '1')
	{
		$('#comment_content_'+id[2]).html(data[1]);
		contentlayer(data[2], I18N.edit_comment_success, true);
	}
	else
	{
		$('#'+data[2]+'_tip').html(data[1]);
	}
	return;
}
function editcomment(tid, rid, func)
{
	if (/^\d+?$/.test(tid) && /^\d+?$/.test(rid))
	{
		$('#editcomment_'+tid+'_'+rid+'+_tip').html(I18N.edit_comment_loading);
		sendAjax('editcomment_'+tid+'_'+rid, 'action=editcomment&comment='+encodeURIComponent($('#editcomment_'+tid+'_'+rid).val())+'&tid='+tid+'&rid='+rid, func);
	}
	return;
}
function submitAction(action, tid, rid, func)
{
	if (/^\d+?$/.test(tid) && /^\d+?$/.test(rid))
	{
		$('#'+action+'_'+tid+'_'+rid+'+_tip').html(I18N.ajax_loading);
		func = typeof func == 'function' ? func : finishAction;
		sendAjax(action+'_'+tid+'_'+rid, 'action='+action+'&tid='+tid+'&rid='+rid, func);
	}
	return;
}
function finishAction(data, textStatus)
{
	if (data[0] == '1')
	{
		var code = data[1] ? data[1] : 'location.reload(true);';
		eval(code);
	}
	else
	{
		$('#'+data[2]+'_tip').html(data[1]);
	}
	return;
}
function showcomment(p)
{
	if (/^\d+?$/.test(p))
	{
		$('#coment_reply').children('div').remove();
		$('#coment_reply').append('<div class="loading">'+I18N.ajax_loading+'</div>');
		sendAjax('showcomment', 'action=showcomment&tid='+tid+'&p='+p, htmlcomment);
	}
	return;
}
function htmlcomment(data, textStatus)
{
	$('#coment_reply > .loading').remove();
	$('#coment_reply').append(data[1]);
}
function cmArticle(tid, action)
{
	if (/^\d+?$/.test(tid) && action == 'movearticle' || action == 'copyarticle')
	{
		var targetcid = $('#targetcid').val();
		sendAjax(action+'_'+tid+'_0', 'action='+action+'&tid='+tid+'&targetcid='+targetcid, finishAction);
	}
}
function titlestyle(tid)
{
	if (/^\d+?$/.test(tid))
	{
		var titlecolor = $('#color_value').val();
		var title_b = ($('#title_b').attr('checked')) ? 1 : 0;
		var title_i = ($('#title_i').attr('checked')) ? 1 : 0;
		var title_u = ($('#title_u').attr('checked')) ? 1 : 0;
		sendAjax('titlestyle_'+tid+'_0', 'action=titlestyle&tid='+tid+'&title_b='+title_b+'&title_i='+title_i+'&title_u='+title_u+'&titlecolor='+titlecolor, finishAction);
	}
}
function commend(tid)
{
	var h = $('#h').val();
	var w = $('#w').val();
	if (!/^\d+?$/.test(h) || !/^\d+?$/.test(w) || !/^\d+?$/.test(tid))
	{
		$('#commendarticle_'+tid+'_0_tip').html(I18N.illegal_data_type);
		return false;
	}
	sendAjax('commendarticle_'+tid+'_0', 'action=commendarticle&tid='+tid+'&w='+w+'&h='+h+'&timesession='+timesession, finishAction);
}
function recomment(rid, author)
{
	var quote = $.trim($('#comment_content_' + rid).text());
	var add = '';
	if (quote.length > 50)
	{
		add = "...";
	}
	quote = quote.substr(0, 50) + add;
	insert_text('[quote=' + author + ']'+quote+'[/quote]');
	return;
}
function insert_text(text)
{
	var textarea = document.getElementById(insertbody);

	if (!isNaN(textarea.selectionStart))
	{
		var sel_start = textarea.selectionStart;
		var sel_end = textarea.selectionEnd;

		mozWrap(textarea, text, '')
		textarea.selectionStart = sel_start + text.length;
		textarea.selectionEnd = sel_end + text.length;
	}
	else if (textarea.createTextRange && textarea.caretPos)
	{
		if (baseHeight != textarea.caretPos.boundingHeight) 
		{
			textarea.focus();
			storeCaret(textarea);
		}

		var caret_pos = textarea.caretPos;
		caret_pos.text = caret_pos.text.charAt(caret_pos.text.length - 1) == ' ' ? caret_pos.text + text + ' ' : caret_pos.text + text;
	}
	else
	{
		textarea.value = textarea.value + text;
	}
	textarea.focus();
}
function mozWrap(txtarea, open, close)
{
	var selLength = txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	var scrollTop = txtarea.scrollTop;

	if (selEnd == 1 || selEnd == 2) 
	{
		selEnd = selLength;
	}

	var s1 = (txtarea.value).substring(0,selStart);
	var s2 = (txtarea.value).substring(selStart, selEnd)
	var s3 = (txtarea.value).substring(selEnd, selLength);

	txtarea.value = s1 + open + s2 + close + s3;
	txtarea.selectionStart = selEnd + open.length + close.length;
	txtarea.selectionEnd = txtarea.selectionStart;
	txtarea.focus();
	txtarea.scrollTop = scrollTop;

	return;
}
function initInsertions() 
{
	var textarea = document.getElementById(insertbody);

	if (is_ie && typeof(baseHeight) != 'number')
	{
		textarea.focus();
		baseHeight = document.selection.createRange().duplicate().boundingHeight;
	}
}
function emoticon(id)
{
	insert_text('[em:'+id+']', insertbody);
	layer.close();
}