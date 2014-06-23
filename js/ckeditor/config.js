
CKEDITOR.editorConfig = function( config )
{
	//config.uiColor = '#AADC6E';
	
	config.toolbar = 'PBDigg';
	config.menu_groups = 'clipboard';
	config.toolbar_PBDigg =
	[
	    ['Undo','Redo','-','Format','Font','FontSize','-','Cut','Copy','Paste','PasteText','PasteFromWord','-'],
	    ['Outdent','Indent','Blockquote'],
	    '/',
	    ['Bold','Italic','Underline','Strike','-','TextColor','BGColor','-'],
	    ['NumberedList','BulletedList','-'],
	    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
	    ['Link','Unlink'],
	    ['Image','Flash','Table','-','RemoveFormat','-','Source']
	];
	config.toolbar = 'Basic';

	config.toolbar_Basic =
	[
	    ['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'TextColor','BGColor', '-', 'Link', 'Unlink','-','Source']
	];
};
