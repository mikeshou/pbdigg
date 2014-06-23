$('document').ready(function(){
	notice();
});

function notice()
{
	this.noticelink = $('#noticelink');
	this.links = $('#noticelink > a');
	this.linknum = links.size();
	this.id = this.p = 1;
	this.r = 0;
	this.play = function()
	{
		if (id > linknum)
		{
			id = 1;
		}
		links.css('display','none');
		$('#link_'+id).show('slow');
		if (!r) p = setTimeout('this.hidden()', '5000');		
	}
	this.hidden = function()
	{
		r = 0;
		$('#link_'+id).css('display','none');
		id++;
		setTimeout('this.play()', '500');
	}
	if (linknum > 1)
	{
		this.play();
		this.noticelink.mouseover(function(){clearTimeout(p);r=1;});
		this.noticelink.mouseout(function(){if(r)p=setTimeout('this.hidden()','2500')});
	}
}