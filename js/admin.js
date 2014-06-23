includeScript('lang/'+pb_lang+'/lang.js');

var ifcheck = true;
$(document).ready(function(){
	$('.text tr').hover(function(){$(this).addClass("hover");},function(){$(this).removeClass("hover");});
});
function checkDel()
{
	return window.confirm("确认删除？");
}
function PBchoseAll(form)
{
	for(var i = 0; i < form.elements.length; i ++)
	{
		var e = form.elements[i];
		if (e.type == 'checkbox')
		{
			e.checked = ifcheck;
		}
	}
	ifcheck = (ifcheck == true) ? false : true;
}
function pb_pop(url,width,height)
{
    var w = 1024;
    var h = 768;
    if (document.all || document.layers)
    {
        w = screen.availWidth;
        h = screen.availHeight;
    }
    var leftPos = (w/2-width/2);
    var topPos = (h/2.3-height/2.3);
    window.open(url,'',"width="+width+",height="+height+",top="+topPos+",left="+leftPos+",scrollbars=no,resizable=no,status=no");
}
//收放菜单
function show_menu(obj,id)
{
	$('#'+obj+id).slideToggle("slow",menu_icon(obj,id)); 
}
function menu_icon(obj,id)
{
	var src = $('#'+obj+'icon_'+id).attr("src");
	if ($('#'+obj+id).css('display') == 'none')
	{
		var re = /expand\.gif/g;
		src = src.replace(re, 'collapse.gif');
		$('#'+obj+'icon_'+id).attr("src", src);
		$('#'+obj+'icon_'+id).attr("alt", 'Collapse');
	}
	else
	{
		var re = /collapse\.gif/g;
		src = src.replace(re, 'expand.gif');
		$('#'+obj+'icon_'+id).attr("src", src);
		$('#'+obj+'icon_'+id).attr("alt", 'Expand');
	}
}
function suggestKey(field, len)
{
	var key = 'abcdefhijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWYXZ~!@$^*()+-,.;[]{}|/';
	var i = 0;
	var suggestKey = '';
	if (!len) len = 16;
	while (i ++ < len)
	{
		suggestKey += key.charAt(Math.random() * key.length);
	}
	document.getElementById(field).value = suggestKey;
}
function checkSwitch(obj, id)
{
	var target = obj.value == '1' ? '0' : '1';
	document.getElementById(id+'_'+target).checked = 'checked';
}
function displaySwitch(obj, action, id)
{
	document.getElementById(id).style.display = (action == 'yes') ? 'block' : 'none';
}
function getX(obj)
{
	var left = obj.offsetLeft;
	var parent = obj.offsetParent;
	while(parent = parent.offsetParent)
	{
		left += parent.offsetLeft;
	}
	return left
}
function getY(obj)
{
	var top = obj.offsetTop;
	var parent = obj.offsetParent;
	while(parent = parent.offsetParent)
	{
		top += parent.offsetTop;
	}
	return top;
}
function bind(obj, event, func)
{
	if ($.browse.msie)
	{
		obj.attachEvent('on'+event, func);
	}
	else
	{
		obj.addEventListener(event, func, false);
	}
}
function unbind(obj, event, func)
{
	if ($.browse.msie)
	{
		obj.attachEvent('on'+event, func);
	}
	else
	{
		obj.removeEventListener(event, func, false);
	}
}
function ietruebody()
{
	return (document.compatMode && document.compatMode!="BackCompat") ? document.documentElement : document.body;
}
function checkListType()
{
	var i = 0;
	$('input:checkbox').each(function(){
		if (this.checked == true) i++;
		if (i > 1)
		{
			alert(I18N.cate_list_type);
			$('#listtype_0')[0].checked = 'checked';
			return false;
		}
	});
	return true;
}
function includeScript(js_file)
{
  document.writeln('<script language="JavaScript" type="text/javascript" src="'+ pb_url + 'js/' + js_file + '"></script>');
}