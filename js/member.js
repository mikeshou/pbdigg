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
function changeAvatar(icon)
{
	$('#currentavatar').attr('src', pb_url+'images/portrait/'+icon);
	$('#defaultavatar').val(icon);
}