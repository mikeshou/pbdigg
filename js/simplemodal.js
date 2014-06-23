var dialoghandler = new Array();
var move = false, _X, _Y;
function modal(id, content, width, height, showclose, onopen, onshow, onclose)
{
	this.id = id;
	this.content = content;
	this.width = typeof width == 'undefined' ? '400' : width;
	this.height = typeof height == 'undefined' ? '0' : height;
	this.showclose = typeof showclose == 'boolean' ? showclose : true;
	this.onopen = typeof onopen == 'function' ? onopen : null;
	this.onshow = typeof onshow == 'function' ? onshow : null;
	this.onclose = typeof onclose == 'function' ? onclose : null;
	this.dialog = {};
}
modal.prototype.showbox = function ()
{
	if (typeof dialoghandler[this.id] == 'undefined')
	{
		dialoghandler[this.id] = new Array();
		this.create();
		$('#modalData_'+this.id).html(this.content);
		this.resize();
		this.open();
		if (this.onshow)
		{
			this.onshow.apply(this);
		}
	}

	return this;
}
modal.prototype.create = function ()
{
	this.dialog.overlay = $('<div id="modalOverlay_'+this.id+'" class="modalOverlay"></div>').hide().appendTo('body');//外框层
	this.dialog.container = $('<div id="modalContainer_'+this.id+'" class="modalContainer"></div>');//内容层
	this.dialog.overlay.append(this.dialog.container);
	if (this.showclose)
	{
		this.dialog.container.append('<div id="modalHeader_'+this.id+'" class="modalHeader"><a href="javascript:void(0)" id="modalClose_'+this.id+'" class="login-close">关闭</a></div>');
	}
	this.dialog.container.append('<div id="modalData_'+this.id+'"></div>');
	if ($.browser.msie && ($.browser.version < 7)) this.fixIE();
}
modal.prototype.resize = function()
{
	if (this.width) this.dialog.overlay.css('width',this.width+'px');
	if (this.height) this.dialog.overlay.css('height',this.height+'px');
	var pagesize = getPageSize();
	var x = (pagesize[0] - this.dialog.overlay.width()) / 2;
	var y = (pagesize[1] - this.dialog.overlay.height()) / 3 + realBody().scrollTop;
	this.dialog.overlay.css({position:'absolute',left:x+'px',top:y+'px',zIndex:'3000'});
}
modal.prototype.open = function ()
{
	if (this.dialog.iframe) this.dialog.iframe.show();
	if (this.onopen)
	{
		this.onopen.apply(this);
	}
	else
	{
		this.dialog.overlay.show();
	}
	this.bindEvents();
}
modal.prototype.close = function ()
{
	if (!this.dialog.overlay) return false;

	if (this.onclose)
	{
		this.onclose.apply(this);
	}
	else
	{
		this.dialog.overlay.remove();
		if (this.dialog.iframe) this.dialog.iframe.remove();
		delete dialoghandler[this.id];
	}
	this.unbindEvents();
	layer = null;
}
modal.prototype.bindEvents = function ()
{
	var modal = this;
	$('#modalClose_'+this.id).click(function (e) {e.preventDefault();modal.close();});
	$('#modalHeader_'+this.id).mousedown(function(e) {
		$('#modalOverlay_'+modal.id).css('cursor','move');
		var offset = $('#modalOverlay_'+modal.id).offset();
		_X = offset.left - e.clientX
		_Y = offset.top - e.clientY
		move = true;
	});
	$('#modalHeader_'+this.id).mousemove(function(e) {
		if (move){
			$('#modalOverlay_'+modal.id).css({left:e.clientX + _X + 'px',top:e.clientY + _Y + 'px'});
		}
	});
	$('#modalHeader_'+this.id).mouseup(function(e){
		move = false;
		$('#modalOverlay_'+modal.id).css('cursor','default');
	});
}
modal.prototype.unbindEvents = function ()
{
	$('#modalClose_'+this.id).unbind('click');
}
modal.prototype.fixIE = function ()
{
	this.dialog.iframe = $('<iframe src="javascript:void(0);"></iframe>').css({opacity:0,position:'absolute',height:'100%',width:'100%',zIndex:1000,top:0,left:0}).hide().appendTo('body');
}
modal.prototype.innerContent = function (content)
{
	$('#modalData_' + this.id).empty().html(content);
//	$('#modalData_' + this.id).empty();
//	document.getElementById('modalData_' + this.id).innerHTML = content;
}
