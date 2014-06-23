
var oldtopicimg = '';
function changetopicimg(type)
{
	var topicimg = $('#topicimg');
	if (type == 'edit')
	{
		oldtopicimg = topicimg.html();
		topicimg.empty().html('<input type="file" name="topicimg" />[<span onclick="changetopicimg(\'cancel\')">'+I18N.cancel+'</span>]')
	}
	else if (type == 'cancel')
	{
		topicimg.empty().html(oldtopicimg);
	}
	return;
}
function checktitle()
{
	var title = $('#title').val();
	if (title.length < title_min || title.length > title_max)
	{
		$('#cktitle').css('display','block');
	}
	else
	{
		$('#cktitle').css('display','none');
	}
}
function checksource()
{
	var url = $('#source').val();
	var reg = /^(https?|ftp|gopher|news|telnet|mms|rtsp):\/\/[^\s]*$/i;
	if (url && !reg.test(url))
	{
		$('#cksource').css('display','block');
	}
	else
	{
		$('#cksource').css('display','none');
	}
}
var tagnum = 0;
function addTag(tagName)
{
	if (tagnum < 5)
	{
		var tagObj = $('#tag');
		var tags = tagObj.val();
		if (tags.length > 0)
		{
			tagdata = tags.split(",")
			for (i=0; i < tagdata.length; i++)
			{
				if (tagdata[i].toLowerCase() == tagName.toLowerCase())
				{
					return false;
				}
			}
			tagObj.val(tags+"," + tagName);
		}
		else
		{
			tagObj.val(tagName);
		}
		tagnum++;
	}
	return false;
}
function postForm()
{
	var title = $('#title').val();
	if (title.length < title_min || title.length > title_max)
	{
		$('#cktitle').css('display','block');
		$('#title').focus();
		return false;
	}
	var url = $('#source').val();
	var reg = /^(https?|ftp|gopher|news|telnet|mms|rtsp):\/\/[^\s]*$/i;
	if (url && !reg.test(url))
	{
		$('#cksource').css('display','block');
		$('#source').focus();
		return false;
	}
	var contentlength = editor.getSource().length;
	if (contentlength < content_min || contentlength > content_max)
	{
		$('#ckcontent').css('display','block');
		$('#content').focus();
		return false;
	}
	return true;
}
