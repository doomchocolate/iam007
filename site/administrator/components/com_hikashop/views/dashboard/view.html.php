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
class dashboardViewDashboard extends hikashopView {

	function display($tpl = null,$params=null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function($params);
		parent::display($tpl);
	}

	function listing(){
		$this->toolbar = array();
		$this->widgets();
		$this->links();
		hikashop_setTitle( HIKASHOP_NAME , 'hikashop' ,'dashboard' );
		if(HIKASHOP_J16 && JFactory::getUser()->authorise('core.admin', 'com_hikashop')) {
			$this->toolbar[] = array('name' => 'preferences');
		}
		$this->toolbar[] = array('name' => 'pophelp', 'target' => 'dashboard');
		$toggle = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggle);
	}

	function cpanel(){
		$this->links();
	}

	function widget($params){
		$this->edit=true;
		$widgetClass = hikashop_get('class.widget');
		$widget = $params;
		$widgetClass=hikashop_get('class.widget');
		if($widget->widget_params->display=='table'){
			foreach($widget->widget_params->table as $row){
				if(!empty($row))$widgetClass->data($row);
			}
		}else{
			$widgetClass->data($widget);
			if (isset($widget->widget_params->period_compare)  && $widget->widget_params->period_compare!='none' && $widget->widget_params->compare_with=='periods'){
				$widgetClass->data($widget);
			}
		}
		$this->assignRef('widget',$widget);
		if (!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		}else{
			$doc = JFactory::getDocument();
		}
		$doc->addScript((hikashop_isSSL() ? 'https://' : 'http://').'www.google.com/jsapi');
		$currencyHelper = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyHelper);
		$this->editView=true;
		if($widget->widget_params->display=='listing'){
			$this->setLayout($widget->widget_params->content_view);
		}else if($widget->widget_params->display=='column' || $widget->widget_params->display=='line' || $widget->widget_params->display=='area'){
			$this->setLayout('chart');
		}else{
			$this->setLayout($widget->widget_params->display);
		}
	}

	function widgets(){
		$widgetClass = hikashop_get('class.widget');
		$widgets = $widgetClass->get();
		foreach($widgets as $k => $widget){
			$content = @$widget->widget_params->content;
			if(!empty($content) || $widget->widget_params->display=='table'){
				if($widget->widget_params->display=='table'){
					foreach($widget->widget_params->table as $row){
						if(!empty($row))
							$widgetClass->data($row);
					}
				}else{
					$widgetClass->data($widget);
					if (isset($widget->widget_params->period_compare)  && $widget->widget_params->period_compare!='none' ){
						$widgetClass->data($widget);
					}
				}
			}
		}
		$this->assignRef('widgets',$widgets);
		if (!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		}else{
			$doc = JFactory::getDocument();
		}
		$doc->addScript((hikashop_isSSL() ? 'https://' : 'http://').'www.google.com/jsapi');
		$currencyHelper = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyHelper);
		if(hikashop_level(1)){
			$config =& hikashop_config();
			$manage = hikashop_isAllowed($config->get('acl_dashboard_manage','all'));
			$this->assignRef('manage',$manage);
			$delete = hikashop_isAllowed($config->get('acl_dashboard_delete','all'));
			$this->assignRef('delete',$delete);
			$this->toolbar[] = array('name' => 'link', 'icon' => 'new', 'alt' => 'NEW_WIDGET', 'url' => hikashop_completeLink('report&task=add&dashboard=true'), 'display' => $manage);
		}
	}

	function links(){
		$buttons = array();
		$desc = array();
		$desc['product'] = '<ul><li>'.JText::_('PRODUCTS_DESC_CREATE').'</li><li>'.JText::_('PRODUCTS_DESC_MANAGE').'</li><li>'.JText::_('CHATACTERISTICS_DESC_MANAGE').'</li></ul>';
		$desc['category'] = '<ul><li>'.JText::_('CATEGORIES_DESC_CREATE').'</li></ul>';
		$desc['user'] = '<ul><li>'.JText::_('CUSTOMERS_DESC_CREATE').'</li><li>'.JText::_('CUSTOMERS_DESC_MANAGE').'</li></ul>';
		$desc['order'] = '<ul><li>'.JText::_('ORDERS_DESC').'</li><li>'.JText::_('ORDERS_DESC_STATUS').'</li></ul>';
		$desc['banner'] = '<ul><li>'.JText::_('AFFILIATES_DESC').'</li>';
		$desc['banner'] .= '<li>'.JText::_('AFFILIATES_DESC_BANNERS').'</li>';
		$desc['banner'] .= '<li>'.JText::_('AFFILIATES_DESC_SALES').'</li></ul>';
		if(!hikashop_level(2)){
			$desc['banner'] .= ' <small style="color:red">'.JText::_('ONLY_FROM_BUSINESS').'</small>';
		}
		$desc['zone'] = '<ul><li>'.JText::_('ZONE_DESC').'</li><li>'.JText::_('ZONE_DESC_TAXES').'</li></ul>';
		$desc['discount'] = '<ul><li>'.JText::_('DISCOUNT_DESC').'</li><li>'.JText::_('DISCOUNT_DESC_LIMITS');
		if(!hikashop_level(1)){
			$desc['discount'] .= ' <small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
		}
		$desc['discount'] .= '</li></ul>';
		$desc['currency'] = '<ul><li>'.JText::_('CURRENCY_DESC').'</li><li>'.JText::_('CURRENCY_DESC_RATES');
		if(!hikashop_level(2)){
			$desc['currency'] .= ' <small style="color:red">'.JText::_('ONLY_FROM_BUSINESS').'</small>';
		}
		$desc['currency'] .= '</li></ul>';
		$desc['plugins'] = '<ul><li>'.JText::_('PLUGINS_DESC_PAYMENT').'</li><li>'.JText::_('PLUGINS_DESC_SHIPPING').'</li></ul>';
		$desc['view'] = '<ul><li>'.JText::_('DISPLAY_DESC_VIEW').'</li><li>'.JText::_('DISPLAY_DESC_CONTENT').'</li><li>'.JText::_('DISPLAY_DESC_FIELDS').'</li></ul>';
		$desc['config'] = '<ul><li>'.JText::_('CONFIG_DESC_CONFIG').'</li><li>'.JText::_('CONFIG_DESC_MODIFY').'</li><li>'.JText::_('CONFIG_DESC_EMAIL');
		if(!hikashop_level(1)){
			$desc['config'] .= ' <small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
		}
		$config =& hikashop_config();
		if(hikashop_isAllowed($config->get('acl_config_view','all'))) $desc['config'] .= '</li><li>'.JText::_('CONFIG_DESC_PLUGIN').'</li></ul>';
		$desc['documentation'] = '<ul><li>'.JText::_('HELP_DESC').'</li><li>'.JText::_('UPDATE_DESC').'</li><li>'.JText::_('FORUM_DESC').'</li></ul>';
		if(hikashop_isAllowed($config->get('acl_product_view','all'))) $buttons[] = array('link'=>'product','level'=>0,'image'=>'product','text'=>JText::_('PRODUCTS'));
		if(hikashop_isAllowed($config->get('acl_category_view','all'))) $buttons[] = array('link'=>'category','level'=>0,'image'=>'categories','text'=>JText::_('HIKA_CATEGORIES'));
		if(hikashop_isAllowed($config->get('acl_user_view','all'))) $buttons[] = array('link'=>'user','level'=>0,'image'=>'user','text'=>JText::_('CUSTOMERS'));
		if(hikashop_isAllowed($config->get('acl_order_view','all'))) $buttons[] = array('link'=>'order','level'=>0,'image'=>'order','text'=>JText::_('ORDERS'));
		if(hikashop_isAllowed($config->get('acl_banner_view','all'))) $buttons[] = array('link'=>'banner','level'=>2,'image'=>'affiliate','text'=>JText::_('AFFILIATES'));
		if(hikashop_isAllowed($config->get('acl_zone_view','all'))) $buttons[] = array('link'=>'zone','level'=>0,'image'=>'langmanager','text'=>JText::_('ZONES'));
		if(hikashop_isAllowed($config->get('acl_discount_view','all'))) $buttons[] = array('link'=>'discount','level'=>0,'image'=>'discount','text'=>JText::_('DISCOUNTS'));
		if(hikashop_isAllowed($config->get('acl_currency_view','all'))) $buttons[] = array('link'=>'currency','level'=>0,'image'=>'currency','text'=>JText::_('CURRENCIES'));
		if(hikashop_isAllowed($config->get('acl_plugins_view','all'))) $buttons[] = array('link'=>'plugins','level'=>0,'image'=>'plugin','text'=>JText::_('PLUGINS'));
		if(hikashop_isAllowed($config->get('acl_view_view','all'))) $buttons[] = array('link'=>'view','level'=>0,'image'=>'view','text'=>JText::_('DISPLAY'));
		if((!HIKASHOP_J16 || JFactory::getUser()->authorise('core.admin', 'com_hikashop')) && hikashop_isAllowed($config->get('acl_config_view','all'))) $buttons[] = array('link'=>'config','level'=>0,'image'=>'config','text'=>JText::_('HIKA_CONFIGURATION'));
		if(hikashop_isAllowed($config->get('acl_help_view','all'))) $buttons[] = array('link'=>'documentation','level'=>0,'image'=>'install','text'=>JText::_('UPDATE_ABOUT'));
		$htmlbuttons = array();
		foreach($buttons as $oneButton){
			$htmlbuttons[] = $this->_quickiconButton($oneButton['link'],$oneButton['image'],$oneButton['text'],$desc[$oneButton['link']],$oneButton['level']);
		}
		$this->assignRef('buttons', $htmlbuttons);
		$this->assignRef('buttonList', $buttons);
		$this->assignRef('descriptions', $desc);
	}

	function _quickiconButton( $link, $image, $text,$description,$level){
		$url = hikashop_level($level) ? 'onclick="document.location.href=\''.hikashop_completeLink($link).'\';"' : '';
		$html = '<div style="float:left;width: 100%;" '.$url.' class="icon"><a href="';
		$html .= hikashop_level($level) ? hikashop_completeLink($link) : '#';
		$html .= '"><table width="100%"><tr><td style="text-align: center;" width="120px">';
		$html .= '<span class="icon-48-'.$image.'" style="background-repeat:no-repeat;background-position:center;height:48px" title="'.$text.'"> </span>';
		$html .= '<span>'.$text.'</span></td><td>'.$description.'</td></tr></table></a>';
		$html .= '</div>';
		return $html;
	}
}
