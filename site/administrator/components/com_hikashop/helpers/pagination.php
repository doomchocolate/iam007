<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
jimport('joomla.html.pagination');

class hikashopBridgePaginationHelper extends JPagination {
	var $hikaSuffix='';
	var $form = '';
	function getPagesLinks(){
		$app = JFactory::getApplication();

		$lang = JFactory::getLanguage();

		$data = $this->_buildDataObject();

		$list = array();
		$itemOverride = false;
		$listOverride = false;

		$chromePath = JPATH_THEMES.DS.$app->getTemplate().DS.'html'.DS.'pagination.php';
		if (file_exists($chromePath)){
			require_once ($chromePath);
			if (function_exists('pagination_list_render')) {
				$listOverride = true;
				if(HIKASHOP_J30 && $app->isAdmin())
					$itemOverride = true;
			}
		}

		if ($data->all->base !== null) {
			$list['all']['active'] = true;
			$list['all']['data'] = ($itemOverride) ? pagination_item_active($data->all) : $this->_item_active($data->all);
		} else {
			$list['all']['active'] = false;
			$list['all']['data'] = ($itemOverride) ? pagination_item_inactive($data->all) : $this->_item_inactive($data->all);
		}
		$data->start->start = true;
		if ($data->start->base !== null) {
			$list['start']['active'] = true;
			$list['start']['data'] = ($itemOverride) ? pagination_item_active($data->start) : $this->_item_active($data->start);
		} else {
			$list['start']['active'] = false;
			$list['start']['data'] = ($itemOverride) ? pagination_item_inactive($data->start) : $this->_item_inactive($data->start);
		}
		$data->previous->previous = true;
		if ($data->previous->base !== null) {
			$list['previous']['active'] = true;
			$list['previous']['data'] = ($itemOverride) ? pagination_item_active($data->previous) : $this->_item_active($data->previous);
		} else {
			$list['previous']['active'] = false;
			$list['previous']['data'] = ($itemOverride) ? pagination_item_inactive($data->previous) : $this->_item_inactive($data->previous);
		}

		$list['pages'] = array(); //make sure it exists
		foreach ($data->pages as $i => $page)
		{
			if ($page->base !== null) {
				$list['pages'][$i]['active'] = true;
				$list['pages'][$i]['data'] = ($itemOverride) ? pagination_item_active($page) : $this->_item_active($page);
			} else {
				$list['pages'][$i]['active'] = false;
				$list['pages'][$i]['data'] = ($itemOverride) ? pagination_item_inactive($page) : $this->_item_inactive($page);
			}
		}
		$data->next->next = true;
		if ($data->next->base !== null) {
			$list['next']['active'] = true;
			$list['next']['data'] = ($itemOverride) ? pagination_item_active($data->next) : $this->_item_active($data->next);
		} else {
			$list['next']['active'] = false;
			$list['next']['data'] = ($itemOverride) ? pagination_item_inactive($data->next) : $this->_item_inactive($data->next);
		}
		$data->end->end = true;
		if ($data->end->base !== null) {
			$list['end']['active'] = true;
			$list['end']['data'] = ($itemOverride) ? pagination_item_active($data->end) : $this->_item_active($data->end);
		} else {
			$list['end']['active'] = false;
			$list['end']['data'] = ($itemOverride) ? pagination_item_inactive($data->end) : $this->_item_inactive($data->end);
		}

		if($this->total > $this->limit){
			return ($listOverride) ? pagination_list_render($list) : $this->_list_render($list);
		}
		else{
			return '';
		}
	}

	function _list_render($list){
		$html = null;

		$html .= '<span class="pagenav_start_chevron">&lt;&lt; </span>';
		$html .= $list['start']['data'];
		$html .= '<span class="pagenav_previous_chevron"> &lt; </span>';
		$html .= $list['previous']['data'];
		foreach( $list['pages'] as $page ) {
			$html .= ' '.$page['data'];
		}
		$html .= ' '. $list['next']['data'];
		$html .= '<span class="pagenav_next_chevron"> &gt;</span>';
		$html .= ' '. $list['end']['data'];
		$html .= '<span class="pagenav_end_chevron"> &gt;&gt;</span>';

		return $html;
	}

	function _list_footer($list){
		$html = '<div class="list-footer">'."\n";
		if(version_compare(JVERSION,'1.6','>=')){
			$display = JText::_('JGLOBAL_DISPLAY_NUM');
		}else{
			$display = JText::_('Display Num');
		}
		$html .= "\n<div class=\"limit\">".$display.$list['limitfield']."</div>";
		$html .= $list['pageslinks'];
		$html .= "\n<div class=\"counter\">".$list['pagescounter']."</div>";

		$html .= "\n<input type=\"hidden\" name=\"limitstart".$this->hikaSuffix."\" value=\"".$list['limitstart']."\" />";
		$html .= "\n</div>";

		return $html;
	}

	function getListFooter($minimum=20){
		$list = array();
		$list['limit']			= $this->limit;
		$list['limitstart']		= $this->limitstart;
		$list['total']			= $this->total;
		$list['limitfield']		= $this->getLimitBox($minimum);
		$list['pagescounter']	= $this->getPagesCounter();
		$list['pageslinks']		= $this->getPagesLinks();

		if(HIKASHOP_J30) {
			$app = JFactory::getApplication();
			if(!$app->isAdmin())
				return $this->_list_footer($list);
			if(empty($this->prefix))
				$this->prefix = '';
			$list['prefix'] = $this->prefix;
			if(function_exists('pagination_list_footer')) {
				$ret = pagination_list_footer($list);
				if(strpos($ret, $list['limitfield']) === false) {
					$display = JText::_('JGLOBAL_DISPLAY_NUM');
					$ret = "\n<div class=\"limit\">".$display.$list['limitfield'] ."</div>" . $ret;
				}
				if(strpos($ret, 'name="limitstart'.$this->hikaSuffix.'"') === false)
					$ret .= "<input type=\"hidden\" name=\"limitstart".$this->hikaSuffix."\" value=\"".$list['limitstart']."\" />";
				return $ret;
			}
		}
		return $this->_list_footer($list);
	}

	function getLimitBox($minimum=20){
		$limits = array ();
		for ($i = $minimum; $i <= $minimum*5; $i += $minimum) {
			$limits[] = JHTML::_('select.option', $i);
		}

		$limits[] = JHTML::_('select.option', '0', JText::_('HIKA_ALL'));

		if(version_compare(JVERSION,'3.0','<')){
			$viewall = $this->_viewall;
		} else {
			$viewall = @$this->viewall;
		}

		return JHTML::_('select.genericlist',  $limits, 'limit'.$this->hikaSuffix, 'class="chzn-done inputbox" size="1" style="width:70px" onchange="this.form.submit()"', 'value', 'text', $viewall ? 0 : $this->limit);
	}
}

if(HIKASHOP_J30){
	class hikashopPaginationHelper extends hikashopBridgePaginationHelper{
		function _item_active(JPaginationObject $item){
			$class = 'pagenav';
			$specials = array('start','end','previous','next');
			foreach($specials as $special){
				if(!empty($item->$special)){
					$class.=' hikashop_'.$special.'_link';
				}
			}
			if($item->base>0)
				return "<a class=\"".$class."\" title=\"".$item->text."\" onclick=\"javascript: document.adminForm".$this->hikaSuffix.$this->form.".limitstart".$this->hikaSuffix.".value=".$item->base."; document.adminForm".$this->hikaSuffix.$this->form.".submit();return false;\">".$item->text."</a>";
			else
				return "<a class=\"".$class."\" title=\"".$item->text."\" onclick=\"javascript: document.adminForm".$this->hikaSuffix.$this->form.".limitstart".$this->hikaSuffix.".value=0; document.adminForm".$this->hikaSuffix.$this->form.".submit();return false;\">".$item->text."</a>";
		}
		function _item_inactive(JPaginationObject $item){
			$mainframe = JFactory::getApplication();
			if ($mainframe->isAdmin()) {
				return "<span>".$item->text."</span>";
			} else {
				$class = 'pagenav';
				if(!is_numeric($item->text)){
					$class .= ' pagenav_text';
				}
				return '<span class="'.$class.'">'.$item->text."</span>";
			}
		}
	}
}else{
	class hikashopPaginationHelper extends hikashopBridgePaginationHelper{
		function _item_active(&$item){
			$class = 'pagenav';
			$specials = array('start','end','previous','next');
			foreach($specials as $special){
				if(!empty($item->$special)){
					$class.=' hikashop_'.$special.'_link';
				}
			}
			if($item->base>0)
				return "<a class=\"".$class."\" title=\"".$item->text."\" onclick=\"javascript: document.adminForm".$this->hikaSuffix.$this->form.".limitstart".$this->hikaSuffix.".value=".$item->base."; document.adminForm".$this->hikaSuffix.$this->form.".submit();return false;\">".$item->text."</a>";
			else
				return "<a class=\"".$class."\" title=\"".$item->text."\" onclick=\"javascript: document.adminForm".$this->hikaSuffix.$this->form.".limitstart".$this->hikaSuffix.".value=0; document.adminForm".$this->hikaSuffix.$this->form.".submit();return false;\">".$item->text."</a>";
		}
		function _item_inactive(&$item){
			$mainframe = JFactory::getApplication();
			if ($mainframe->isAdmin()) {
				return "<span>".$item->text."</span>";
			} else {
				$class = 'pagenav';
				if(!is_numeric($item->text)){
					$class .= ' pagenav_text';
				}
				return '<span class="'.$class.'">'.$item->text."</span>";
			}
		}
	}
}
