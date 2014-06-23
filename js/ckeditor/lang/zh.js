CKEDITOR.lang['zh'] =
{
	dir : 'ltr',
	editorTitle		: '文书处理器, %1',
	// Toolbar buttons without dialogs.
	source			: '原始码',
	newPage			: '开新档案',
	save			: '储存',
	preview			: '预览',
	cut				: '剪下',
	copy			: '复制',
	paste			: '贴上',
	print			: '列印',
	underline		: '底线',
	bold			: '粗体',
	italic			: '斜体',
	selectAll		: '全选',
	removeFormat	: '清除格式',
	strike			: '删除线',
	subscript		: '下标',
	superscript		: '上标',
	horizontalrule	: '插入水平线',
	pagebreak		: '插入分页符号',
	unlink			: '移除超连结',
	undo			: '复原',
	redo			: '重复',

	// Common messages and labels.
	common :
	{
		browseServer	: '浏览伺服器端',
		url				: 'URL',
		protocol		: '通讯协定',
		upload			: '上传',
		uploadSubmit	: '上传至伺服器',
		image			: '影像',
		flash			: 'Flash',
		form			: '表单',
		checkbox		: '核取方块',
		radio		: '选项按钮',
		textField		: '文字方块',
		textarea		: '文字区域',
		hiddenField		: '隐藏栏位',
		button			: '按钮',
		select	: '清单/选单',
		imageButton		: '影像按钮',
		notSet			: '<尚未设定>',
		id				: 'ID',
		name			: '名称',
		langDir			: '语言方向',
		langDirLtr		: '由左而右 (LTR)',
		langDirRtl		: '由右而左 (RTL)',
		langCode		: '语言代码',
		longDescr		: '详细 URL',
		cssClass		: '样式表类别',
		advisoryTitle	: '标题',
		cssStyle		: '样式',
		ok				: '确定',
		cancel			: '取消',
		generalTab		: '一般',
		advancedTab		: '进阶',
		validateNumberFailed	: '需要输入数字格式',
		confirmNewPage	: '现存的修改尚未储存，要开新档案？',
		confirmCancel	: '部份选项尚未储存，要关闭对话盒？',

		// Put the voice-only part of the label in the span.
		unavailable		: '%1<span class="cke_accessibility">, 已关闭</span>'
	},

	// Special char dialog.
	specialChar		:
	{
		toolbar		: '插入特殊符号',
		title		: '请选择特殊符号'
	},

	// Link dialog.
	link :
	{
		toolbar		: '插入/编辑超连结',
		menu		: '编辑超连结',
		title		: '超连结',
		info		: '超连结资讯',
		target		: '目标',
		upload		: '上传',
		advanced	: '进阶',
		type		: '超连接类型',
		toAnchor	: '本页锚点',
		toEmail		: '电子邮件',
		target		: '目标',
		targetNotSet	: '<尚未设定>',
		targetFrame	: '<框架>',
		targetPopup	: '<快显视窗>',
		targetNew	: '新视窗 (_blank)',
		targetTop	: '最上层视窗 (_top)',
		targetSelf	: '本视窗 (_self)',
		targetParent	: '父视窗 (_parent)',
		targetFrameName	: '目标框架名称',
		targetPopupName	: '快显视窗名称',
		popupFeatures	: '快显视窗属性',
		popupResizable	: '可缩放',
		popupStatusBar	: '状态列',
		popupLocationBar	: '网址列',
		popupToolbar	: '工具列',
		popupMenuBar	: '选单列',
		popupFullScreen	: '全萤幕 (IE)',
		popupScrollBars	: '卷轴',
		popupDependent	: '从属 (NS)',
		popupWidth		: '宽',
		popupLeft		: '左',
		popupHeight		: '高',
		popupTop		: '右',
		id				: 'ID',
		langDir			: '语言方向',
		langDirNotSet	: '<尚未设定>',
		langDirLTR		: '由左而右 (LTR)',
		langDirRTL		: '由右而左 (RTL)',
		acccessKey		: '存取键',
		name			: '名称',
		langCode		: '语言方向',
		tabIndex		: '定位顺序',
		advisoryTitle	: '标题',
		advisoryContentType	: '内容类型',
		cssClasses		: '样式表类别',
		charset			: '连结资源之编码',
		styles			: '样式',
		selectAnchor	: '请选择锚点',
		anchorName		: '依锚点名称',
		anchorId		: '依元件 ID',
		emailAddress	: '电子邮件',
		emailSubject	: '邮件主旨',
		emailBody		: '邮件内容',
		noAnchors		: '(本文件尚无可用之锚点)',
		noUrl			: '请输入欲连结的 URL',
		noEmail			: '请输入电子邮件位址',
		linkurl			: '连接位址'
	},

	// Anchor dialog
	anchor :
	{
		toolbar		: '插入/编辑锚点',
		menu		: '锚点属性',
		title		: '锚点属性',
		name		: '锚点名称',
		errorName	: '请输入锚点名称'
	},

	// Find And Replace Dialog
	findAndReplace :
	{
		title				: '寻找与取代',
		find				: '寻找',
		replace				: '取代',
		findWhat			: '寻找:',
		replaceWith			: '取代:',
		notFoundMsg			: '未找到指定的文字。',
		matchCase			: '大小写须相符',
		matchWord			: '全字相符',
		matchCyclic			: '循环搜索',
		replaceAll			: '全部取代',
		replaceSuccessMsg	: '共完成 %1 次取代'
	},

	// Table Dialog
	table :
	{
		toolbar		: '表格',
		title		: '表格属性',
		menu		: '表格属性',
		deleteTable	: '删除表格',
		rows		: '列数',
		columns		: '栏数',
		border		: '边框',
		align		: '对齐',
		alignNotSet	: '<未设定>',
		alignLeft	: '靠左对齐',
		alignCenter	: '置中',
		alignRight	: '靠右对齐',
		width		: '宽度',
		widthPx		: '像素',
		widthPc		: '百分比',
		height		: '高度',
		cellSpace	: '间距',
		cellPad		: '内距',
		caption		: '标题',
		summary		: '摘要',
		headers		: '标题',
		headersNone		: '无标题',
		headersColumn	: '第一栏',
		headersRow		: '第一列',
		headersBoth		: '第一栏和第一列',
		invalidRows		: '必须有一或更多的列',
		invalidCols		: '必须有一或更多的栏',
		invalidBorder	: '边框大小必须为数字格式',
		invalidWidth	: '表格宽度必须为数字格式',
		invalidHeight	: '表格高度必须为数字格式',
		invalidCellSpacing	: '储存格间距必须为数字格式',
		invalidCellPadding	: '储存格内距必须为数字格式',

		cell :
		{
			menu			: '储存格',
			insertBefore	: '向左插入储存格',
			insertAfter		: '向右插入储存格',
			deleteCell		: '删除储存格',
			merge			: '合并储存格',
			mergeRight		: '向右合并储存格',
			mergeDown		: '向下合并储存格',
			splitHorizontal	: '横向分割储存格',
			splitVertical	: '纵向分割储存格',
			title			: '储存格属性',
			cellType		: '储存格类别',
			rowSpan			: '储存格列数',
			colSpan			: '储存格栏数',
			wordWrap		: '自动换行',
			hAlign			: '水平对齐',
			vAlign			: '垂直对齐',
			alignTop		: '向上对齐',
			alignMiddle		: '置中对齐',
			alignBottom		: '向下对齐',
			alignBaseline	: '基线对齐',
			bgColor			: '背景颜色',
			borderColor		: '边框颜色',
			data			: '数据',
			header			: '标题',
			yes				: '是',
			no				: '否',
			invalidWidth	: '储存格宽度必须为数字格式',
			invalidHeight	: '储存格高度必须为数字格式',
			invalidRowSpan	: '储存格列数必须为整数格式',
			invalidColSpan	: '储存格栏数度必须为整数格式'
		},

		row :
		{
			menu			: '列',
			insertBefore	: '向上插入列',
			insertAfter		: '向下插入列',
			deleteRow		: '删除列'
		},

		column :
		{
			menu			: '栏',
			insertBefore	: '向左插入栏',
			insertAfter		: '向右插入栏',
			deleteColumn	: '删除栏'
		}
	},

	// Button Dialog.
	button :
	{
		title		: '按钮属性',
		text		: '显示文字 (值)',
		type		: '类型',
		typeBtn		: '按钮 (Button)',
		typeSbm		: '送出 (Submit)',
		typeRst		: '重设 (Reset)'
	},

	// Checkbox and Radio Button Dialogs.
	checkboxAndRadio :
	{
		checkboxTitle : '核取方块属性',
		radioTitle	: '选项按钮属性',
		value		: '选取值',
		selected	: '已选取'
	},

	// Form Dialog.
	form :
	{
		title		: '表单属性',
		menu		: '表单属性',
		action		: '动作',
		method		: '方法',
		encoding	: '表单编码',
		target		: '目标',
		targetNotSet	: '<尚未设定>',
		targetNew	: '新视窗 (_blank)',
		targetTop	: '最上层视窗 (_top)',
		targetSelf	: '本视窗 (_self)',
		targetParent	: '父视窗 (_parent)'
	},

	// Select Field Dialog.
	select :
	{
		title		: '清单/选单属性',
		selectInfo	: '资讯',
		opAvail		: '可用选项',
		value		: '值',
		size		: '大小',
		lines		: '行',
		chkMulti	: '可多选',
		opText		: '显示文字',
		opValue		: '选取值',
		btnAdd		: '新增',
		btnModify	: '修改',
		btnUp		: '上移',
		btnDown		: '下移',
		btnSetValue : '设为预设值',
		btnDelete	: '删除'
	},

	// Textarea Dialog.
	textarea :
	{
		title		: '文字区域属性',
		cols		: '字元宽度',
		rows		: '列数'
	},

	// Text Field Dialog.
	textfield :
	{
		title		: '文字方块属性',
		name		: '名称',
		value		: '值',
		charWidth	: '字元宽度',
		maxChars	: '最多字元数',
		type		: '类型',
		typeText	: '文字',
		typePass	: '密码'
	},

	// Hidden Field Dialog.
	hidden :
	{
		title	: '隐藏栏位属性',
		name	: '名称',
		value	: '值'
	},

	// Image Dialog.
	image :
	{
		title		: '影像属性',
		titleButton	: '影像按钮属性',
		menu		: '影像属性',
		infoTab	: '影像资讯',
		btnUpload	: '上传至伺服器',
		url		: 'URL',
		upload	: '上传',
		alt		: '替代文字',
		width		: '宽度',
		height	: '高度',
		lockRatio	: '等比例',
		resetSize	: '重设为原大小',
		border	: '边框',
		hSpace	: '水平距离',
		vSpace	: '垂直距离',
		align		: '对齐',
		alignLeft	: '靠左对齐',
		alignAbsBottom: '绝对下方',
		alignAbsMiddle: '绝对中间',
		alignBaseline	: '基准线',
		alignBottom	: '靠下对齐',
		alignMiddle	: '置中对齐',
		alignRight	: '靠右对齐',
		alignTextTop	: '文字上方',
		alignTop	: '靠上对齐',
		preview	: '预览',
		alertUrl	: '请输入影像 URL',
		linkTab	: '超连结',
		button2Img	: '要把影像按钮改成影像吗？',
		img2Button	: '要把影像改成影像按钮吗？'
	},

	// Flash Dialog
	flash :
	{
		properties		: 'Flash 属性',
		propertiesTab	: '属性',
		title		: 'Flash 属性',
		chkPlay		: '自动播放',
		chkLoop		: '重复',
		chkMenu		: '开启选单',
		chkFull		: '启动全萤幕显示',
 		scale		: '缩放',
		scaleAll		: '全部显示',
		scaleNoBorder	: '无边框',
		scaleFit		: '精确符合',
		access			: '允许脚本访问',
		accessAlways	: '永远',
		accessSameDomain	: '相同域名',
		accessNever	: '永不',
		align		: '对齐',
		alignLeft	: '靠左对齐',
		alignAbsBottom: '绝对下方',
		alignAbsMiddle: '绝对中间',
		alignBaseline	: '基准线',
		alignBottom	: '靠下对齐',
		alignMiddle	: '置中对齐',
		alignRight	: '靠右对齐',
		alignTextTop	: '文字上方',
		alignTop	: '靠上对齐',
		quality		: '质素',
		qualityBest		 : '最好',
		qualityHigh		 : '高',
		qualityAutoHigh	 : '高（自动）',
		qualityMedium	 : '中（自动）',
		qualityAutoLow	 : '低（自动）',
		qualityLow		 : '低',
		windowModeWindow	 : '视窗',
		windowModeOpaque	 : '不透明',
		windowModeTransparent	 : '透明',
		windowMode	: '视窗模式',
		flashvars	: 'Flash 变数',
		bgcolor	: '背景颜色',
		width	: '宽度',
		height	: '高度',
		hSpace	: '水平距离',
		vSpace	: '垂直距离',
		validateSrc : '请输入欲连结的 URL',
		validateWidth : '宽度必须为数字格式',
		validateHeight : '高度必须为数字格式',
		validateHSpace : '水平间距必须为数字格式',
		validateVSpace : '垂直间距必须为数字格式'
	},

	// Speller Pages Dialog
	spellCheck :
	{
		toolbar			: '拼字检查',
		title			: '拼字检查',
		notAvailable	: '抱歉，服务目前暂不可用',
		errorLoading	: '无法联系侍服器: %s.',
		notInDic		: '不在字典中',
		changeTo		: '更改为',
		btnIgnore		: '忽略',
		btnIgnoreAll	: '全部忽略',
		btnReplace		: '取代',
		btnReplaceAll	: '全部取代',
		btnUndo			: '复原',
		noSuggestions	: '- 无建议值 -',
		progress		: '进行拼字检查中…',
		noMispell		: '拼字检查完成：未发现拼字错误',
		noChanges		: '拼字检查完成：未更改任何单字',
		oneChange		: '拼字检查完成：更改了 1 个单字',
		manyChanges		: '拼字检查完成：更改了 %1 个单字',
		ieSpellDownload	: '尚未安装拼字检查元件。您是否想要现在下载？'
	},

	smiley :
	{
		toolbar	: '表情符号',
		title	: '插入表情符号'
	},

	elementsPath :
	{
		eleTitle : '%1 元素'
	},

	numberedlist : '编号清单',
	bulletedlist : '项目清单',
	indent : '增加缩排',
	outdent : '减少缩排',

	justify :
	{
		left : '靠左对齐',
		center : '置中',
		right : '靠右对齐',
		block : '左右对齐'
	},

	blockquote : '引用文字',

	clipboard :
	{
		title		: '贴上',
		cutError	: '浏览器的安全性设定不允许编辑器自动执行剪下动作。请使用快捷键 (Ctrl+X) 剪下。',
		copyError	: '浏览器的安全性设定不允许编辑器自动执行复制动作。请使用快捷键 (Ctrl+C) 复制。',
		pasteMsg	: '请使用快捷键 (<strong>Ctrl+V</strong>) 贴到下方区域中并按下 <strong>确定</strong>',
		securityMsg	: '因为浏览器的安全性设定，本编辑器无法直接存取您的剪贴簿资料，请您自行在本视窗进行贴上动作。'
	},

	pastefromword :
	{
		toolbar : '自 Word 贴上',
		title : '自 Word 贴上',
		advice : '请使用快捷键 (<strong>Ctrl+V</strong>) 贴到下方区域中并按下 <strong>确定</strong>',
		ignoreFontFace : '移除字型设定',
		removeStyle : '移除样式设定'
	},

	pasteText :
	{
		button : '贴为纯文字格式',
		title : '贴为纯文字格式'
	},

	templates :
	{
		button : '样版',
		title : '内容样版',
		insertOption: '取代原有内容',
		selectPromptMsg: '请选择欲开启的样版<br> (原有的内容将会被清除):',
		emptyListMsg : '(无样版)'
	},

	showBlocks : '显示区块',

	stylesCombo :
	{
		label : '样式',
		voiceLabel : '样式',
		panelVoiceLabel : '选择样式',
		panelTitle1 : '块级元素样式',
		panelTitle2 : '内联元素样式',
		panelTitle3 : '物件元素样式'
	},

	format :
	{
		label : '格式',
		voiceLabel : '格式',
		panelTitle : '格式',
		panelVoiceLabel : '选择段落格式',

		tag_p : '一般',
		tag_pre : '已格式化',
		tag_address : '位址',
		tag_h1 : '标题 1',
		tag_h2 : '标题 2',
		tag_h3 : '标题 3',
		tag_h4 : '标题 4',
		tag_h5 : '标题 5',
		tag_h6 : '标题 6',
		tag_div : '一般 (DIV)'
	},

	font :
	{
		label : '字体',
		voiceLabel : '字体',
		panelTitle : '字体',
		panelVoiceLabel : '选择字体'
	},

	fontSize :
	{
		label : '大小',
		voiceLabel : '文字大小',
		panelTitle : '大小',
		panelVoiceLabel : '选择文字大小'
	},

	colorButton :
	{
		textColorTitle : '文字颜色',
		bgColorTitle : '背景颜色',
		auto : '自动',
		more : '更多颜色…'
	},

	colors :
	{
		'000' : 'Black',
		'800000' : 'Maroon',
		'8B4513' : 'Saddle Brown',
		'2F4F4F' : 'Dark Slate Gray',
		'008080' : 'Teal',
		'000080' : 'Navy',
		'4B0082' : 'Indigo',
		'696969' : 'Dim Gray',
		'B22222' : 'Fire Brick',
		'A52A2A' : 'Brown',
		'DAA520' : 'Golden Rod',
		'006400' : 'Dark Green',
		'40E0D0' : 'Turquoise',
		'0000CD' : 'Medium Blue',
		'800080' : 'Purple',
		'808080' : 'Gray',
		'F00' : 'Red',
		'FF8C00' : 'Dark Orange',
		'FFD700' : 'Gold',
		'008000' : 'Green',
		'0FF' : 'Cyan',
		'00F' : 'Blue',
		'EE82EE' : 'Violet',
		'A9A9A9' : 'Dark Gray',
		'FFA07A' : 'Light Salmon',
		'FFA500' : 'Orange',
		'FFFF00' : 'Yellow',
		'00FF00' : 'Lime',
		'AFEEEE' : 'Pale Turquoise',
		'ADD8E6' : 'Light Blue',
		'DDA0DD' : 'Plum',
		'D3D3D3' : 'Light Grey',
		'FFF0F5' : 'Lavender Blush',
		'FAEBD7' : 'Antique White',
		'FFFFE0' : 'Light Yellow',
		'F0FFF0' : 'Honeydew',
		'F0FFFF' : 'Azure',
		'F0F8FF' : 'Alice Blue',
		'E6E6FA' : 'Lavender',
		'FFF' : 'White'
	},

	scayt :
	{
		title : '即时拼写检查',
		enable : '启用即时拼写检查',
		disable : '关闭即时拼写检查',
		about : '关於即时拼写检查',
		toggle : '启用／关闭即时拼写检查',
		options : '选项',
		langs : '语言',
		moreSuggestions : '更多拼写建议',
		ignore : '忽略',
		ignoreAll : '全部忽略',
		addWord : '添加单词',
		emptyDic : '字典名不应为空.',
		optionsTab : '选项',
		languagesTab : '语言',
		dictionariesTab : '字典',
		aboutTab : '关於'
	},

	about :
	{
		title : '关於 CKEditor',
		dlgTitle : '关於 CKEditor',
		moreInfo : '访问我们的网站以获取更多关於协议的信息',
		copy : 'Copyright &copy; $1. All rights reserved.'
	},

	maximize : '最大化',

	fakeobjects :
	{
		anchor : '锚点',
		flash : 'Flash 动画',
		div : '分页',
		unknown : '不明物件'
	},

	resize : '拖拽改变大小'
};
