<?php
/**
 * @version $Id: menu.class.php v1.0 $
 * @package PBDigg
 * @author zhuzhu2hao
 * @copyright Copyright (C) 2007 - 2008 PBDigg.com. All Rights Reserved.
 * @license PBDigg is free software and use is subject to license terms
 */

class menu
{
	var $_catedata = array();

	var $_lang = array();
	
	var $_MenuCache = array();

	function menu()
	{
		$this->__construct();
	}

	function __construct()
	{
		global $_categories;
		$this->_catedata = isset($_categories) ? $_categories : array();
		$this->_lang = loadLang('common');
	}
	/**
	 * 顶级菜单
	 */
//	function headerMenu($num = 5)
//	{
//		global $_PBENV, $allowsort;
//
//		$num = (is_int($num) && $num > 0) ? $num : 5;
//		$i = 1;
//		$headerMenu = '<li><a href="'.$_PBENV['PB_URL'].'" class="h-drop"><strong class="firstpage">'.$this->_lang['first_page'].'</strong></a></li>';
//
//		foreach ($this->_catedata as $k => $v)
//		{
//			if (!$v['depth'] && ($allowsort | $v['status']))
//			{
//				$headerMenu .= '<li class="h-drop" rel="'.$k.'"><a href="'.$v['url'].'" title="'.$v['name'].'"><strong>'.$v['name'].'</strong><em id="cate'.$k.'" class="catedrop">&nbsp;</em></a>';	
//				if ($v['withchild'])
//				{
//					$headerMenu .= '<ul class="submenu-drop" id="catem'.$k.'" style="display:none">';
//					foreach ($this->_catedata as $kk => $vv)
//					{
//						$vv['cup'] == $k && $headerMenu .= '<li><a href="'.$v['url'].'" title="'.$vv['name'].'">'.$vv['name'].'</a></li>';
//					}
//					$headerMenu .= '</ul>';
//				}
//				$headerMenu .= '</li>';
//			}
//			if ($i++ == $num)
//			{
//				$headerMenu .= '<li><a href="'.$_PBENV['PB_URL'].'sitemap.php" class="h-drop"><strong class="morepage">'.$this->_lang['more_page'].'</strong></a></li>';
//				break;
//			}
//		}
//		return $headerMenu;
//	}

	function headerMenu($num = 5)
	{
		global $_PBENV, $allowsort;

		$num = (is_int($num) && $num > 0) ? $num : 5;
		
		$i = 0;
		$headerMenu = '<div class="menu"><div class="menusel"><h2><a href="'.$_PBENV['PB_URL'].'">'.$this->_lang['first_page'].'</a></h2></div>';

		foreach ($this->_catedata as $k => $v)
		{
			if (!$v['depth'] && ($allowsort | $v['status']))
			{
				$headerMenu .= '<div id="menu'.$i.'" class="menusel"><h2><a href="category.php?cid='.$v['cid'].'" title="'.$v['name'].'">'.$v['name'].'</a></h2>';

				if ($v['withchild'])
				{
					$headerMenu .= '<div class="position">';
					$this->unlimitBlockMenu($headerMenu, $v['cid'], ' class="clearfix typeul"');
					$headerMenu .= '</div>';
				}
				$headerMenu .= '</div>';
				$i++;
			}
			if ($i == $num)
			{
				$headerMenu .= '<div class="menusel"><h2><a href="'.$_PBENV['PB_URL'].'sitemap.php" classs="move">'.$this->_lang['more_page'].'</a></h2></div>';
				break;
			}
		}
		$headerMenu .= "</div>\n";
		
		$headerMenu .= <<<EOT
<script type="text/javascript">

for (var x = 0; x < {$num}; x++)
{
	var menuid = document.getElementById('menu'+x);
	if (menuid)
	{
		menuid.num = x;
		type();	
	}
}
function type()
{
	var menuh2 = menuid.getElementsByTagName('h2');
	var menuul = menuid.getElementsByTagName('ul');
	if (menuul.length)
	{
		menuh2[0].onmouseover = show;
		menuh2[0].onmouseout = unshow;
		menuul[0].onmouseover = show;
		menuul[0].onmouseout = unshow;
		var menuli = menuul[0].getElementsByTagName('li');
		for(var i = 0; i < menuli.length; i++)
		{
			menuli[i].num = i;
			var liul = menuli[i].getElementsByTagName('ul')[0];
			if(liul)
			{
				typeshow()
			}
		}
	}
	function show()
	{
		menuul[0].className = 'clearfix typeul block'
	}
	function unshow()
	{
		menuul[0].className = 'typeul'
	}
	function typeshow()
	{
		menuli[i].onmouseover = showul;
		menuli[i].onmouseout = unshowul;
	}
	function showul()
	{
		menuli[this.num].getElementsByTagName('ul')[0].className = 'block';
	}
	function unshowul()
	{
		menuli[this.num].getElementsByTagName('ul')[0].className = '';
	}
}

</script>
EOT;
		return $headerMenu;
	}
	/**
	 * 块菜单
	 */
	function blockMenu()
	{
		$topone = $toptwo = array();
		$menu = '';
		foreach ($this->_catedata as $k => $v)
		{
			switch ($v['depth'])
			{
				case 0:
					$topone[$k] = $v;
					break;
				case 1:
					$toptwo[$v['cup']][] = $v;
					break;
			}
		}
		foreach ($topone as $k => $v)
		{
			$menu .= '<ul class="blockmenu"><h2><a href="category.php?cid='.$v['cid'].'" title="'.$v['name'].'">'.$v['name'].'</a></h2>';
			if (isset($toptwo[$k]))
			{
				foreach ($toptwo[$k] as $sk => $sv)
				{
					$menu .= '<li><a href="category.php?cid='.$sv['cid'].'" title="'.$sv['name'].'">'.$sv['name'].'</a></li>';
				}
			}
			$menu .= '</ul>';
		}
		return $menu;
	}

	/**
	 * 树形菜单
	 */
	function treeMenu($start, $tree = '', $level = 1)
	{
		global $_PBENV;
		static $id = 1;
		$child = $this->getChild($start);

		foreach ($child as $k => $v)
		{
			if ($this->getChild($k))
			{
				$tree .= '<p>'.str_repeat('<img src="'.$_PBENV['PB_URL'].'images/common/treemenu_blank.png" />', $level - 1).'<img src="'.$_PBENV['PB_URL'].'images/common/treemenu_pnode.png" onclick="toggleFolder(\'folder'.$id.'\', this)"/><img src="'.$_PBENV['PB_URL'].'images/common/treemenu_folderclosed.png" onclick="toggleFolder(\'folder'.$id.'\', this)"/><a href="category.php?cid='.$v['cid'].'" title="'.$v['name'].'">'.$v['name'].'</a></p>';
				$tree .= '<div id="folder'.$id.'">';
				$id++;
				$tree = $this->treeMenu($k, $tree, $level + 1);
				$tree .= '</div>';
			}
			else
			{
				$tree .= '<p>'.str_repeat('<img src="'.$_PBENV['PB_URL'].'images/common/treemenu_blank.png" />', $level).'<img src="'.$_PBENV['PB_URL'].'images/common/treemenu_doc.png" /><a href="category.php?cid='.$v['cid'].'" title="'.$v['name'].'">'.$v['name'].'</a></p>';
			}
		}

		return $tree;
	}
	/**
	 * 无限块菜单
	 * @param rel $menu 输出菜单
	 * @param int $start 起始CID
	 */
	function unlimitBlockMenu(&$menu, $start = 0, $class = '')
	{
		$child = $this->getChild($start);
		$menu .= '<ul'.$class.'>';
		foreach ($child as $k => $v)
		{
			$menu .= '<li><a href="category.php?cid='.$v['cid'].'" title="'.$v['name'].'">'.$v['name'].'</a>';
			if ($this->getChild($k))
			{
				$this->unlimitBlockMenu($menu, $k);
			}
			$menu .= '</li>';
		}
		$menu .= '</ul>';
	}
	/**
	 * 位置导航条
	 */
	function nav($cid = 0)
	{
		global $_PBENV;
		$afterlink = $beforelink = '';
		$navlink = '<a href="'.$_PBENV['PB_URL'].'">'.$this->_lang['first_page'].'</a>'.$this->_lang['nav_separator'];
		if (!array_key_exists($cid, $this->_catedata)) return $navlink;

		if ($this->_catedata[$cid]['cup'])
		{
			$cup = $this->_catedata[$cid]['cup'];
			while (true)
			{
				$beforelink = '<a href="category.php?cid='.$this->_catedata[$cup]['cid'].'" title="'.$this->_catedata[$cup]['name'].'">'.$this->_catedata[$cup]['name'].'</a>'.$this->_lang['nav_separator'].$beforelink;
				$cup = $this->_catedata[$this->_catedata[$cup]['cid']]['cup'];
				if (!$cup) break;
			}
		}
		foreach ($this->_catedata as $k => $v)
		{
			if ($cid == $v['cup'])
			{
				$afterlink .= $this->_lang['nav_separator'].'<a href="category.php?cid='.$v['cid'].'">'.$v['name'].'</a>';
				break;
			}
		}
		return $navlink.'<a href="category.php?cid='.$this->_catedata[$cid]['cid'].'">'.$beforelink.$this->_catedata[$cid]['name'].'</a>'.$afterlink;
	}

	function getParent($cid)
	{
		$parent = array();
		if(!array_key_exists($cid, $this->_catedata)) return;
		$cup = $this->_catedata[$cid]['cup'];
		foreach($this->_catedata as $k => $v)
		{
			if($k == $cup)
			{
				$parent[$k] = $v;
				break;
			}
		}
		return $parent;
	}

	function getChild($cid)
	{
		$child = array();
		foreach($this->_catedata as $k => $v)
		{
			$v['cup'] == $cid && $child[$k] = $v;
		}
		return $child;
	}
}
?>