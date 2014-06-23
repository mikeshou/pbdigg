/*
Copyright (c) 2003-2009, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

(function()
{
	CKEDITOR.dialog.add( 'flash', function( editor )
	{
		return {
			title : editor.lang.flash.title,
			minWidth : 420,
			minHeight : 310,
			onShow : function()
			{
				this.getContentElement( 'info', 'src' ).focus();
				this.setupContent();
			},
			onOk : function()
			{
				var data = {}, editor = this.getParentEditor(), element = null;

				this.commitContent( data );
				
				element = '['+data.type+'='+data.height+','+data.width+']'+data.src+'[/'+data.type+']';
				//alert(element);
				editor.insertHtml(element);
			},
			contents : [
				{
					id : 'info',
					label : editor.lang.common.generalTab,
					accessKey : 'I',
					elements :
					[
						{
							type : 'vbox',
							padding : 0,
							children :
							[
								{
									type : 'html',
									html : '<span>' + CKEDITOR.tools.htmlEncode( editor.lang.image.url ) + '</span>'
								},
								{
									type : 'hbox',
									align : 'right',
									id : 'src',
									type : 'text',
									label : '',
									validate : function()
									{
										if ( !this.getValue() || !/^(?:http|https|ftp|news):\/\/(.*)$/.test(this.getValue()) )
										{
											alert( editor.lang.link.noUrl );
											return false;
										}
										return true;
									},
									commit : function( data )
									{
										data.src = this.getValue();
									}
								}
							]
						},
						{
							type : 'hbox',
							widths : [ '33%', '33%', '33%' ],
							children :
							[
								{
									id : 'type',
									type : 'select',
									label : editor.lang.link.type,
									'default' : 'flash',
									style : 'width:100%',
									items :
									[
										[ 'flash' ],
										[ 'media' ]
									],
									commit : function( data )
									{
										data.type = this.getValue();
									}
								},
								{
									type : 'text',
									id : 'width',
									style : 'width:100%',
									label : editor.lang.flash.width,
									setup : function()
									{
										this.setValue(400);
									},
									commit : function( data )
									{
										data.width = this.getValue() ? this.getValue() : '400';
									}
								},
								{
									type : 'text',
									id : 'height',
									style : 'width:100%',
									label : editor.lang.flash.height,
									setup : function()
									{
										this.setValue(340);
									},
									commit : function( data )
									{
										data.height = this.getValue() ? this.getValue() : '340';
									}
								}
							]
						}
					]
				}
			]
		};
	} );
})();
