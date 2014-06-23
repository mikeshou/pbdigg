var clientPC = navigator.userAgent.toLowerCase(); // Get client info
var clientVer = parseInt(navigator.appVersion); // Get browser version

var is_ie = ((clientPC.indexOf('msie') != -1) && (clientPC.indexOf('opera') == -1));
var is_win = ((clientPC.indexOf('win') != -1) || (clientPC.indexOf('16bit') != -1));

$(document).ready(function(){
includeScript('lang/'+pb_lang+'/lang.js');
})

var layer = null;
function refreshCheckcode()
{
	$('#checkcode').attr('src', pb_url+'checkcode.php?' + '&random=' + Math.random());
}
function openlayer(id, content, width, height, showclose, onopen, onshow, onclose)
{
	ifshowclose = showclose === true ? true : false;
	layer = new modal(id, content, width, height, ifshowclose, onopen, onshow, onclose);
	layer.showbox();
	if (/^\d+?$/.test(showclose))
	{
		setTimeout('layer.close()', showclose * 1000);
	}
}
function contentlayer(id, content, autoclose)
{
	if (layer)
	{
		layer.innerContent(content);
		if (autoclose == true)
		{
			setTimeout('layer.close()', 2000);
		}
	}
}
function dbaction(action, tid, rid)
{
	var id = action + '_' + tid + '_' +rid;
	$.ajax({
		type: 'POST',
		url: pb_url+'ajax.php',
		dataType: 'json',
		data: 'action=' + action + '&tid=' + tid + '&rid=' + rid + '&verify=' + verify + '&random=' + Math.random(),
		error: function(var1, var2 ,var3)
		{
			openlayer(id, var1.responseText, 600, 600, 2000);
		},
		success: function(data)
		{
			if (data[0] == '1')
			{
	           $('#'+id+' > span').html(data[1]);
	           $('#'+id).attr('disabled',true).css('cursor','default');
			}
			else
			{
				openlayer(id, data[1], 200, 100, 2);
			}
		}
	});
	return;
}
function pb_pop(url,title,width,height)
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
    window.open(url,title,"width="+width+",height="+height+",top="+topPos+",left="+leftPos+",scrollbars=no,resizable=no,status=no");
}
var gocheck = true;
function PBchoseAll(form)
{
	for(var i = 0; i < form.elements.length; i ++)
	{
		var e = form.elements[i];
		if (e.type == 'checkbox')
		{
			e.checked = gocheck;
		}
	}
	gocheck = (gocheck == true) ? false : true;
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
function getPageSize()
{
	var de = document.documentElement;
	var w = window.innerWidth || self.innerWidth || (de && de.clientWidth) || document.body.clientWidth;
	var h = window.innerHeight || self.innerHeight || (de && de.clientHeight) || document.body.clientHeight;
	arrayPageSize = [w,h];
	return arrayPageSize;
}
function realBody()
{
	return (document.compatMode && document.compatMode != "BackCompat")? document.documentElement : document.body;
}
function includeScript(js_file, code)
{
	var	s = document.createElement('script');
	s.type = 'text/javascript';
	if (js_file) {
		s.src = pb_url + 'js/' + js_file;
	} else if (code) {
		s.text = code;
	}
	document.getElementById('loadjs').appendChild(s);
	return true;
}