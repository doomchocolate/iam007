<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if ($this->config->get('show_quantity_field')<2) { ?>
<form action="<?php echo hikashop_completeLink('product&task=updatecart'); ?>" method="post" name="hikashop_product_form_<?php echo $this->row->product_id.'_'.$this->params->get('main_div_name'); ?>" enctype="multipart/form-data">
    <?php }

    if ($this->config->get('show_quantity_field')<2) {
    $module_id = $this->params->get('from_module',0);

    $this->formName = ',\'hikashop_product_form_'.$this->row->product_id.'_'.$this->params->get('main_div_name').'\'';
    $this->ajax='';
    if(!$this->config->get('ajax_add_to_cart',0)||!empty($itemFields)){
        $this->ajax = 'if(hikashopCheckChangeForm(\'item\',\'hikashop_product_form_'.$this->row->product_id.'_'.$this->params->get('main_div_name').'\')){ return hikashopModifyQuantity(\''.$this->row->product_id.'\',field,1,\'hikashop_product_form_'.$this->row->product_id.'_'.$this->params->get('main_div_name').'\',\'cart\','.$module_id.'); } return false;';
    }
    $this->setLayout('quantity_custom');
    echo $this->loadTemplate();
    ?>
<?php }elseif(empty($this->row->has_options)&& !$this->config->get('catalogue') && ($this->config->get('display_add_to_cart_for_free_products') || !empty($this->row->prices))){
    if($this->row->product_quantity==-1 || $this->row->product_quantity>0){ ?>
        <input id="hikashop_listing_quantity_<?php echo $this->row->product_id;?>" type="text" style="width:40px;" name="data[<?php echo $this->row->product_id;?>]" class="hikashop_listing_quantity_field" value="0" />
    <?php }else{
        echo JText::_('NO_STOCK');
    }
} ?>
