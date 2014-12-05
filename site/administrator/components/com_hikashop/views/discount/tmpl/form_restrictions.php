<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>						<tr id="hikashop_min_order">
							<td class="key">
									<?php echo JText::_( 'MINIMUM_ORDER_VALUE' ); ?>
							</td>
							<td>
								<input type="text" name="data[discount][discount_minimum_order]" value="<?php echo @$this->element->discount_minimum_order; ?>" />
							</td>
						</tr>
						<tr id="hikashop_min_products">
							<td class="key">
									<?php echo JText::_( 'MINIMUM_NUMBER_OF_PRODUCTS' ); ?>
							</td>
							<td>
								<input type="text" name="data[discount][discount_minimum_products]" value="<?php echo (int)@$this->element->discount_minimum_products; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'DISCOUNT_QUOTA' ); ?>
							</td>
							<td>
								<input type="text" name="data[discount][discount_quota]" value="<?php echo @$this->element->discount_quota; ?>" />
							</td>
						</tr>
						<tr id="hikashop_quota_per_user">
							<td class="key">
									<?php echo JText::_( 'DISCOUNT_QUOTA_PER_USER' ); ?>
							</td>
							<td>
								<input type="text" name="data[discount][discount_quota_per_user]" value="<?php echo @$this->element->discount_quota_per_user; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'PRODUCT' ); ?>
							</td>
							<td>
								<span id="product_id" >
									<?php echo (int)@$this->element->discount_product_id.' '.@$this->element->product_name; ?>
									<input type="hidden" name="data[discount][discount_product_id]" value="<?php echo @$this->element->discount_product_id; ?>" />
								</span>
								<?php
									echo $this->popup->display(
											'<img src="'. HIKASHOP_IMAGES.'edit.png" alt="'.JText::_('PRODUCT').'"/>',
											'PRODUCT',
											 hikashop_completeLink("product&task=selectrelated&select_type=discount",true ),
											'product_link',
											760, 480, '', '', 'link'
										);
									?>
								<a href="#" onclick="document.getElementById('product_id').innerHTML='<input type=\'hidden\' name=\'data[discount][discount_product_id]\' value=\'0\' />';return false;" >
									<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="delete"/>
								</a>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'CATEGORY' ); ?>
							</td>
							<td>
								<span id="changeParent" >
									<?php echo (int)@$this->element->discount_category_id.' '.@$this->element->category_name; ?>
								</span>
									<input type="hidden" id="categoryselectparentlisting" name="data[discount][discount_category_id]" value="<?php echo @$this->element->discount_category_id; ?>" />
								<?php
									echo $this->popup->display(
											'<img src="'. HIKASHOP_IMAGES.'edit.png" alt="'.JText::_('CATEGORY').'"/>',
											'CATEGORY',
											hikashop_completeLink("category&task=selectparentlisting&control=category",true ),
											'category_link',
											760, 480, '', '', 'link'
										);
									?>
								<a href="#" onclick="document.getElementById('changeParent').innerHTML='0 <?php echo $this->escape(JText::_('CATEGORY_NOT_FOUND'));?>'; document.getElementById('categoryselectparentlisting').value='0';return false;" >
									<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="delete"/>
								</a>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'INCLUDING_SUB_CATEGORIES' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "data[discount][discount_category_childs]" , '',@$this->element->discount_category_childs	); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'ZONE' ); ?>
							</td>
							<td>
								<span id="zone_id" >
									<?php echo (int)@$this->element->discount_zone_id.' '.@$this->element->zone_name_english; ?>
									<input type="hidden" name="data[discount][discount_zone_id]" value="<?php echo @$this->element->discount_zone_id; ?>" />
								</span>
								<?php
									echo $this->popup->display(
										'<img src="'. HIKASHOP_IMAGES.'edit.png" alt="'.JText::_('ZONE').'"/>',
										'ZONE',
										 hikashop_completeLink("zone&task=selectchildlisting&type=discount",true ),
										'zone_id_link',
										760, 480, '', '', 'link'
									);
								?>
								<a href="#" onclick="document.getElementById('zone_id').innerHTML='<input type=\'hidden\' name=\'data[discount][discount_zone_id]\' value=\'0\' />';return false;" >
									<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="delete"/>
								</a>
							</td>
						</tr>
						<tr id="hikashop_auto_load">
							<td class="key">
									<?php echo JText::_( 'COUPON_AUTO_LOAD' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "data[discount][discount_auto_load]" , '',@$this->element->discount_auto_load	); ?>
							</td>
						</tr>
<?php



?>
						<tr id="hikashop_discount_coupon_product_only">
							<td class="key">
									<?php echo JText::_('COUPON_APPLIES_TO_PRODUCT_ONLY'); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "data[discount][discount_coupon_product_only]" , '',@$this->element->discount_coupon_product_only	); ?>
							</td>
						</tr>
						<?php
						?>
						<tr id="hikashop_discount_coupon_nodoubling">
							<td class="key">
									<?php echo JText::_('COUPON_HANDLING_OF_DISCOUNTED_PRODUCTS'); ?>
							</td>
							<td>
								<?php

									$options = array();
									$options[] = JHTML::_('select.option', 0, JText::_('STANDARD_BEHAVIOR'));
									$options[] = JHTML::_('select.option', 1, JText::_('IGNORE_DISCOUNTED_PRODUCTS'));
									$options[] = JHTML::_('select.option', 2, JText::_('OVERRIDE_DISCOUNTED_PRODUCTS'));
									echo JHTML::_('hikaselect.genericlist', $options, "data[discount][discount_coupon_nodoubling]" , '', 'value', 'text', @$this->element->discount_coupon_nodoubling );
								?>
							</td>
						</tr>
<?php




JPluginHelper::importPlugin('hikashop');
$dispatcher = JDispatcher::getInstance();
$html = array();
$dispatcher->trigger('onDiscountBlocksDisplay', array(&$this->element, &$html));
if(!empty($html)) {
	echo implode("\r\n", $html);
}
?>
						<tr>
							<td colspan="2">
								<fieldset class="adminform">
									<legend><?php echo JText::_('ACCESS_LEVEL'); ?></legend>
									<?php
									if(hikashop_level(2)){
										$acltype = hikashop_get('type.acl');
										echo $acltype->display('discount_access',@$this->element->discount_access);
									}else{
										echo hikashop_getUpgradeLink('business');;
									} ?>
								</fieldset>
							</td>
						</tr>
