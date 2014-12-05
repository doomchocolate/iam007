<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<?php
	$config =& hikashop_config();
	if(empty($this->rows)){echo "<div style='color: red; font-size: 14px; text-align: center; width: 100%;'>".JText::_('HIKA_NEW_WISHLIST_BACKEND')."</div>";}
?>
<form action="index.php?option=com_hikashop&amp;ctrl=cart" method="post"  name="adminForm" id="adminForm" >
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-cart">
	<table style="width:100%">
		<tr>
			<td valign="top" width="50%">
<?php } else { ?>
<div id="page-cart" class="row-fluid">
	<div class="span6">
<?php } ?>
				<fieldset class="adminform" id="htmlfieldset_general">
					<legend><?php echo JText::_('MAIN_INFORMATION'); ?></legend>
					<table class="admintable table">
						<tr>
							<td class="key">
									<?php echo JText::_( 'ID' ); ?>
							</td>
							<td>
								<?php echo $this->cart->cart_id; ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_NAME' ); ?>
							</td>
							<td>
								<input type="text" name="data[cart][cart_name]" value="<?php echo $this->cart->cart_name; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_TYPE' ); ?>
							</td>
							<td>
								<select name="data[cart][cart_type]" >
								<?php
									if($this->cart->cart_type == 'cart'){
										echo "<option value='wishlist'>".JText::_( 'WISHLIST' )."</option>";
										echo "<option value='cart' selected='selected'>".JText::_( 'HIKASHOP_CHECKOUT_CART' )."</option>";
									}else{
										echo "<option value='wishlist' selected='selected'>".JText::_( 'WISHLIST' )."</option>";
										echo "<option value='cart'>".JText::_( 'HIKASHOP_CHECKOUT_CART' )."</option>";
									}
								?>
								</select>
							</td>
						</tr>
						<?php if($this->cart->cart_type == 'cart'){?>
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKASHOP_COUPON' ); ?>
							</td>
							<td>
								<input type="text" name="data[cart][cart_coupon]" value="<?php echo $this->cart->cart_coupon; ?>" />
							</td>
						</tr>
						<?php } ?>
						<tr>
							<td class="key">
									<?php echo JText::_( 'DATE' ); ?>
							</td>
							<td>
								<?php
								echo hikashop_getDate($this->cart->cart_modified,'%Y-%m-%d %H:%M');?>
							</td>
						</tr>
					</table>
				</fieldset>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
			<td valign="top" width="50%">
<?php } else { ?>
	</div>
	<div class="span6">
<?php } ?>
				<fieldset class="adminform" id="htmlfieldset_customer">
					<legend><?php echo JText::_('CUSTOMER'); ?></legend>
					<div style="float:right;">
						<?php
							echo $this->popup->display(
								'<img style="vertical-align:middle;" alt="'.JText::_('HIKA_EDIT').'" src="'. HIKASHOP_IMAGES.'edit.png"/>',
								'HIKA_EDIT',
								hikashop_completeLink('order&task=user&cart_type='.$this->cart->cart_type.'&cart_id='.$this->cart->cart_id,true),
								'hikashop_edit_customer',
								760, 480, '', '', 'link'
							);
						?>
					</div>
					<table class="admintable table">
					<?php if(!empty($this->user->user_id)){?>
						<?php if(!empty($this->user->name)){?>
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_USER_NAME' ); ?>
							</td>
							<td>
								<?php echo $this->user->name.' ('.$this->user->username.')'; ?>
							</td>
						</tr>
						<?php }?>
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_EMAIL' ); ?>
							</td>
							<td>
								<?php echo $this->user->email; ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'ID' ); ?>
							</td>
							<td>
								<?php echo $this->user->user_id; ?>
								<?php if(hikashop_isAllowed($config->get('acl_user_manage','all'))){ ?>
								<a href="<?php echo hikashop_completeLink('user&task=edit&cid[]='. $this->user->user_id.'&cart_id='.$this->cart->cart_id); ?>">
									<img src="<?php echo HIKASHOP_IMAGES; ?>go.png" alt="go" />
								</a>
								<?php } ?>
								<input type="hidden" value="<?php echo $this->user->user_id;?>" name="data[user][user_id]"/>
							</td>
						</tr>
					<?php } ?>

						<tr>
							<td class="key">
									<?php echo JText::_( 'IP' ); ?>
							</td>
							<td>
								<?php
								if(!empty($this->user)) echo $this->user->user_created_ip;
								?>
							</td>
						</tr>
					</table>
				</fieldset>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
		</tr>
		<tr>
			<td  colspan="2">
<?php } else { ?>
	</div>
	<div class="span12">
<?php } ?>
				<fieldset class="adminform" id="htmlfieldset_products">
					<legend><?php echo JText::_('PRODUCT_LIST'); ?></legend>
					<div style="float:right;">
						<?php
							echo $this->popup->display(
								'<img src="'.HIKASHOP_IMAGES.'add.png"/>'.JText::_('ADD_EXISTING_PRODUCT'),
								'ADD_EXISTING_PRODUCT',
								hikashop_completeLink('order&type=cart&task=product_select&cart_type='.$this->cart->cart_type.'&cart_id='.$this->cart->cart_id,true),
								'product_add_button',
								1100, 480, '', '', 'button'
							);
						?>
					</div>

					<table class="adminlist table table-striped table-hover" cellpadding="1">
						<thead>
							<tr>
								<th class="hikashop_order_item_name_title title">
									<?php echo JText::_('PRODUCT'); ?>
								</th>
								<th class="hikashop_order_item_files_title title">
									<?php echo JText::_('PRODUCT_QUANTITY'); ?>
								</th>
								<th class="hikashop_order_item_price_title title">
									<?php echo JText::_('PRICE'); ?>
								</th>
								<th class="hikashop_order_item_quantity_title title titletoggle">
									<?php echo JText::_('HIKASHOP_CHECKOUT_STATUS'); ?>
								</th>
								<th class="hikashop_order_item_action_title title titletoggle">
									<?php echo JText::_('ACTIONS'); ?>
								</th>
							</tr>
						</thead>
						<tbody>
						<?php
							$app = JFactory::getApplication();
							$i = 1;
							$k = 1;
							$total_price = 0;
							$total_quantity = 0;
							$currency = 1;

							$currencyHelper = hikashop_get('class.currency');
							$productClass = hikashop_get('class.product');
							if(!empty($this->rows)){
								foreach($this->rows as $cart){
									if(!isset($cart->prices[0])){
										$cart->prices[0] = new stdClass();
										$cart->prices[0]->price_value = 0;
										$cart->prices[0]->price_currency_id = 1;
									}
									$total_price += $cart->prices[0]->price_value * $cart->cart_product_quantity;
									$total_quantity += $cart->cart_product_quantity;
									$currency = $cart->prices[0]->price_currency_id;
									$productClass->addAlias($cart);
									$quantityLeft = $cart->product_quantity - $cart->cart_product_quantity;
									$inStock = 1;
									if(($cart->product_quantity - $cart->cart_product_quantity) >= 0 || $cart->product_quantity == -1){
										if($cart->product_quantity == -1)
											$stockText = "<span class='hikashop_green_color'>".JText::sprintf('X_ITEMS_IN_STOCK',JText::_('HIKA_UNLIMITED'))."</span>";
										else
											$stockText = "<span class='hikashop_green_color'>".JText::sprintf('X_ITEMS_IN_STOCK',$cart->product_quantity)."</span>";
									}else{
										if($cart->product_code != @$cart->cart_product_code){
											$stockText = "<span class='hikashop_red_color'>".JText::_('HIKA_NOT_SALE_ANYMORE'). "</span>";
											$inStock = 0;
										}else{
											$stockText = "<span class='hikashop_red_color'>".JText::_('NOT_ENOUGH_STOCK')."</span>";
											$inStock = 0;
										}
									}

								if($k ==1)$k = 0;else $k =1;

								if(($cart->product_type == 'main' && $cart->cart_product_quantity == 0)||$cart->cart_product_quantity == 0)continue;
								?>
								<tr>
									<td class="hikashop_order_item_name_value">
										<span class="hikashop_order_item_name">
											<?php echo $this->popup->display(
												$cart->product_name.' '.$cart->product_code,
												$cart->product_name.' '.$cart->product_code,
												hikashop_frontendLink('index.php?option=com_hikashop&ctrl=product&task=show&cid='.$cart->product_id.'&tmpl=component'),
												'hikashop_see_product_'.$cart->product_id,
												760, 480, '', '', 'link'
											);
											$config =& hikashop_config();
											$manage = hikashop_isAllowed($config->get('acl_product_manage','all'));
											if($manage){ ?>
												<a target="_blank" href="<?php echo hikashop_completeLink('product&task=edit&cid[]='. $cart->product_id); ?>">
													<img src="<?php echo HIKASHOP_IMAGES; ?>go.png" alt="<?php echo JText::_('HIKA_EDIT'); ?>" />
												</a>
											<?php } ?>
										</span>
										<p class="hikashop_cart_product_custom_item_fields">
										<?php
										if(hikashop_level(2) && !empty($this->fields['item'])){
											foreach($this->fields['item'] as $field){
												$namekey = $field->field_namekey;
												if(empty($product->$namekey)){
													continue;
												}
												echo '<p class="hikashop_order_item_'.$namekey.'">'.$this->fieldsClass->getFieldName($field).': '.$this->fieldsClass->show($field,$product->$namekey).'</p>';
											}
										}?>
										</p>
									</td>
									<td align="center" class="hikashop_cart_item_quantity_value order">
										<input type="text" id="product_<?php echo $cart->product_id; ?>" name="data[cart_product][<?php echo $cart->product_id; ?>][cart_product_quantity]" value="<?php echo $cart->cart_product_quantity;?>"/>
									</td>
									<td class="hikashop_cart_item_price_value">
										<?php
											echo $currencyHelper->format($cart->prices[0]->price_value,$cart->prices[0]->price_currency_id);
										?>
									</td>
									<td width="20%" class="hikashop_cart_item_status_value">
										<?php echo $stockText; ?>
									</td>
									<td align="center" class="hikashop_cart_item_action_value">
										<a onclick="javascript: document.getElementById('product_<?php echo $cart->product_id; ?>').value = 0; submitbutton('apply');" href="#">
											<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/>
										</a>
									</td>
								</tr>
								<?php

							}
							}else{
								?>
								<tr><td colspan="5"></td></td>
								<?php
							}
						?>
						</tbody>
					</table>
				</fieldset>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
		</tr>
		<tr>
			<td  colspan="2">
<?php } else { ?>
	</div>
	<div class="span12">
<?php } ?>
				<fieldset class="adminform" id="htmlfieldset_general">
					<legend><?php echo JText::_('HIKA_DETAILS'); ?></legend>
					<table class="admintable table">
						<tr>
							<td class="key">
									<?php echo JText::_( 'PRODUCT_QUANTITY' ); ?>
							</td>
							<td>
								<?php echo $total_quantity; ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'CART_PRODUCT_TOTAL_PRICE' ); ?>
							</td>
							<td>
								<?php
									echo $currencyHelper->format($total_price,$currency);
								?>
							</td>
						</tr>
					</table>
				</fieldset>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
		</tr>
	</table>
</div>
<?php } else { ?>
	</div>
</div>
<?php } ?>
	<div style="clear:both" class="clr"></div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->cart->cart_id; ?>" />
	<input type="hidden" name="cart_type" value="<?php echo @$this->cart->cart_type; ?>" />
	<input type="hidden" name="option" value="com_hikashop" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="cart" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
