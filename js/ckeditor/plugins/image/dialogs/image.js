(function(){var IMAGE=1,LINK=2,PREVIEW=4,CLEANUP=8;var imageDialog=function(editor,dialogType){return{title:editor.lang.image.title,minWidth:420,minHeight:100,onShow:function(){this.imageElement=false;this.imageEditMode=false;var editor=this.getParentEditor(),sel=this.getParentEditor().getSelection(),element=sel.getSelectedElement();if(element&&element.getName()=='img'&&!element.getAttribute('_cke_protected_html'))this.imageEditMode='img';if(this.imageEditMode){this.imageElement=element;this.setupContent(IMAGE,this.imageElement)}},onOk:function(){if(!this.imageEditMode){this.imageElement=editor.document.createElement('img');this.imageElement.setAttribute('alt','')}this.commitContent(IMAGE,this.imageElement);if(!this.imageEditMode){editor.insertElement(this.imageElement)}},contents:[{id:'info',label:editor.lang.image.infoTab,accessKey:'I',elements:[{type:'vbox',padding:0,children:[{type:'html',html:'<span>'+CKEDITOR.tools.htmlEncode(editor.lang.image.url)+'</span>'},{type:'hbox',id:'txtUrl',type:'text',label:'',setup:function(type,element){if(type==IMAGE){var url=element.getAttribute('_cke_saved_src')||element.getAttribute('src');var field=this;setTimeout(function(){field.setValue(url);field.focus()},0)}},commit:function(type,element){if(type==IMAGE&&(this.getValue()||this.isChanged())){element.setAttribute('_cke_saved_src',decodeURI(this.getValue()));element.setAttribute('src',decodeURI(this.getValue()))}else if(type==CLEANUP){element.setAttribute('src','');element.removeAttribute('src')}}}]}]}]}};CKEDITOR.dialog.add('image',function(editor){return imageDialog(editor,'image')})})();