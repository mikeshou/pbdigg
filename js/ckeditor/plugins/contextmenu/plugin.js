/*
Copyright (c) 2003-2009, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.plugins.add('contextmenu',{requires:['menu'],beforeInit:function(a){a.contextMenu=new CKEDITOR.plugins.contextMenu(a);a.addCommand('contextMenu',{exec:function(){a.contextMenu.show();}});}});CKEDITOR.plugins.contextMenu=CKEDITOR.tools.createClass({$:function(a){this.id='cke_'+CKEDITOR.tools.getNextNumber();this.editor=a;this._.listeners=[];this._.functionId=CKEDITOR.tools.addFunction(function(b){this._.panel.hide();a.focus();a.execCommand(b);},this);},_:{onMenu:function(a,b,c,d){var e=this._.menu,f=this.editor;if(e){e.hide();e.removeAll();}else{e=this._.menu=new CKEDITOR.menu(f);e.onClick=CKEDITOR.tools.bind(function(o){var p=true;e.hide();if(CKEDITOR.env.ie)e.onEscape();if(o.onClick)o.onClick();else if(o.command)f.execCommand(o.command);p=false;},this);e.onEscape=function(){f.focus();if(CKEDITOR.env.ie)f.getSelection().unlock(true);};}var g=this._.listeners,h=[],i=this.editor.getSelection(),j=i&&i.getStartElement();if(CKEDITOR.env.ie)i.lock();e.onHide=CKEDITOR.tools.bind(function(){e.onHide=null;if(CKEDITOR.env.ie)f.getSelection().unlock();this.onHide&&this.onHide();},this);for(var k=0;k<g.length;k++){var l=g[k](j,i);if(l)for(var m in l){var n=this.editor.getMenuItem(m);if(n){n.state=l[m];e.add(n);}}}e.show(a,b||(f.lang.dir=='rtl'?2:1),c,d);}},proto:{addTarget:function(a){a.on('contextmenu',function(b){var c=b.data;c.preventDefault();var d=c.getTarget().getDocument().getDocumentElement(),e=c.$.clientX,f=c.$.clientY;CKEDITOR.tools.setTimeout(function(){this._.onMenu(d,null,e,f);},0,this);},this);},addListener:function(a){this._.listeners.push(a);},show:function(a,b,c,d){this.editor.focus();this._.onMenu(a||CKEDITOR.document.getDocumentElement(),b,c||0,d||0);}}});
