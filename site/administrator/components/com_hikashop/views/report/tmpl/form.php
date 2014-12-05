<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=report" method="post" name="adminForm" id="adminForm">
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-report">
	<table style="width:100%">
		<tr>
			<td valign="top" width="50%">
<?php } else { ?>
<div id="page-report" class="row-fluid">
	<div class="span6">
<?php } ?>
				<fieldset class="adminform">
					<legend><?php echo JText::_('CURRENT_REPORT'); ?></legend>
					<table class="paramlist admintable table">
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_NAME' ); ?>
							</td>
							<td>
								<input type="text" name="data[widget][widget_name]" id="name" class="inputbox" size="40" value="<?php echo $this->escape(@$this->element->widget_name); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "data[widget][widget_published]" , '',@$this->element->widget_published, 'HIKASHOP_YES', 'HIKASHOP_NO', 'data_widget_widget_published'); ?>
							</td>
						</tr>
					</table>
				</fieldset>
				<fieldset class="adminform">
					<legend><?php echo JText::_('ACCESS_LEVEL'); ?></legend>
					<?php
					if(hikashop_level(2)){
						$acltype = hikashop_get('type.acl');
						echo $acltype->display('widget_access',@$this->element->widget_access,'widget');
					}else{
						echo hikashop_getUpgradeLink('business');;
					} ?>
				</fieldset>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
			<td valign="top" width="50%">
<?php } else { ?>
	</div>
	<div class="span6">
<?php } ?>
				<fieldset class="adminform">
					<legend><?php echo JText::_('DISPLAY'); ?></legend>
					<table class="paramlist admintable table">
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_TYPE' );?>
							</td>
							<td >
								<?php echo $this->widget_dataType->display('data[widget][widget_params][display]',@$this->element->widget_params->display, '', 'widget_display',@$this->element->widget_id, $this->row_id, $this->element->widget_params->display); ?>
							</td>
						</tr>
						<tr id="widget_date">
							<td class="key" >
								<?php echo JText::_( 'DATE_TYPE' );// only for orders ?>
							</td>
							<td>
								<?php echo $this->dateType->display('data[widget][widget_params][date_type]',@$this->element->widget_params->date_type); ?>
							</td>
						</tr>
						<tr id="widget_group">
							<td class="key" >
								<?php echo JText::_( 'DATE_GROUP' );//only for graph and gauge ?>
							</td>
							<td>
								<?php echo $this->dateGroup->display('data[widget][widget_params][date_group]',@$this->element->widget_params->date_group); ?>
							</td>
						</tr>
						<tr id="widget_period">
							<td class="key"><?php echo JText::_('PERIOD'); ?></td>
							<td>
								<span><input <?php if(empty($this->element->widget_params->periodType) || $this->element->widget_params->periodType == 'proposedPeriod') echo 'checked="checked"'; ?> onClick="updatePeriodSelection()" type="radio" value="proposedPeriod" name="data[widget][widget_params][periodType]" id="display_proposed_period"/>
									<?php echo $this->periodType->display('data[widget][widget_params][proposedPeriod]',@$this->element->widget_params->proposedPeriod); ?>
								</label></span><br/>
								<span><input <?php if(!empty($this->element->widget_params->periodType) && $this->element->widget_params->periodType == 'specificPeriod') echo 'checked="checked"'; ?> onClick="updatePeriodSelection()" type="radio" value="specificPeriod" name="data[widget][widget_params][periodType]" id="display_specific_period"/>
								<?php echo JText::_('START_DATE').' ';
										echo JHTML::_('calendar', hikashop_getDate((@$this->element->widget_params->start?@$this->element->widget_params->start:''),'%Y-%m-%d %H:%M'), 'data[widget][widget_params][start]','period_start','%Y-%m-%d %H:%M',array('size'=>'20'));
										echo ' '.JText::_('END_DATE').' '; echo JHTML::_('calendar', hikashop_getDate((@$this->element->widget_params->end?@$this->element->widget_params->end:''),'%Y-%m-%d %H:%M'), 'data[widget][widget_params][end]','period_end','%Y-%m-%d %H:%M',array('size'=>'20'));
								?>
								<br/><?php echo JText::_('PERIOD').' '; echo $this->delay->display('data[widget][widget_params][period]',(int)@$this->element->widget_params->period,3); ?>
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
			<div id="widget_type">
				<fieldset class="adminform">
					<legend><?php echo JText::_('HIKA_TYPE'); ?></legend>
					<table class="paramlist admintable">
						<tr>
							<td>
								<div class="controls">
									<fieldset class="radio btn-group">
										<span><input <?php if(empty($this->element->widget_params->content) || $this->element->widget_params->content == 'orders') echo 'checked="checked"'; ?> onClick="updateDisplayType()" type="radio" value="orders" name="data[widget][widget_params][content]" id="type_orders"/><label for="type_orders"><?php echo JText::_( 'ORDERS' );  ?></label></span>
										<span id="type_listing_sales"><input <?php if(empty($this->element->widget_params->content) || $this->element->widget_params->content == 'sales') echo 'checked="checked"'; ?> onClick="updateDisplayType()" type="radio" value="sales" name="data[widget][widget_params][content]" id="type_sales"/><label for="type_sales"><?php echo JText::_( 'SALES' );  ?></label></span>
										<span id="type_listing_taxes"><input <?php if(!empty($this->element->widget_params->content) && $this->element->widget_params->content == 'taxes') echo 'checked="checked"'; ?> onClick="updateDisplayType()" type="radio" value="taxes" name="data[widget][widget_params][content]" id="type_taxes"/><label for="type_taxes"><?php echo JText::_( 'TAXES' );  ?></label></span>
										<span id="customers_button"><input <?php if(!empty($this->element->widget_params->content) && $this->element->widget_params->content == 'customers') echo 'checked="checked"'; ?> onClick="updateDisplayType()" type="radio" value="customers" name="data[widget][widget_params][content]" id="type_customers"/><label for="type_customers"><?php echo JText::_( 'CUSTOMERS' );  ?></label></span>
										<span id="partners_button"><input <?php if(!empty($this->element->widget_params->content) && $this->element->widget_params->content == 'partners') echo 'checked="checked"'; ?> onClick="updateDisplayType()" type="radio" value="partners" name="data[widget][widget_params][content]" id="type_partners"/><label for="type_partners"><?php echo JText::_( 'PARTNERS' );  ?></label></span>
										<span id="type_listing_prod"><input <?php if(!empty($this->element->widget_params->content) && $this->element->widget_params->content == 'products') echo 'checked="checked"'; ?> onClick="updateDisplayType()" type="radio" value="products" name="data[widget][widget_params][content]" id="type_products"/><label for="type_products"><?php echo JText::_( 'PRODUCTS' );  ?></label></span>
										<span id="type_listing_cat"><input <?php if(!empty($this->element->widget_params->content) && $this->element->widget_params->content == 'categories') echo 'checked="checked"'; ?> onClick="updateDisplayType()" type="radio" value="categories" name="data[widget][widget_params][content]" id="type_categories"/><label for="type_categories"><?php echo JText::_( 'HIKA_CATEGORIES' );  ?></label></span>
										<span id="type_listing_discounts"><input <?php if(!empty($this->element->widget_params->content) && $this->element->widget_params->content == 'discounts') echo 'checked="checked"'; ?> onClick="updateDisplayType()" type="radio" value="discounts" name="data[widget][widget_params][content]" id="type_discounts"/><label for="type_discounts"><?php echo JText::_( 'DISCOUNT' );  ?></label></span>
										<?php ?>
									</fieldset>
								</div>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-report2">
	<table style="width:100%">
		<tr>
			<td valign="top" width="50%">
<?php } else { ?>
<div id="page-report2" class="row-fluid">
	<div class="span6">
<?php } ?>
				<div id="filters">
					<fieldset class="adminform">
						<legend><?php echo JText::_( 'FILTERS' ); ?></legend>
						<table class="paramlist admintable table" width="100%">
							<tr id="widget_status">
								<td class="key" >
									<?php echo JText::_( 'ORDER_STATUS' );  ?>
								</td>
								<td>
									<?php echo $this->status->display('data[widget][widget_params][filters][a.order_status][]',@$this->element->widget_params->filters['a.order_status'],' multiple="multiple" size="5"',false); ?>
								</td>
							</tr>
							<tr id="widget_currencies">
								<td class="key">
												<?php echo JText::_( 'CURRENCIES' ); ?>
									</td>
									<td>
											<?php 	$currency=hikashop_get('type.currency');
											 $currencyList=$currency->display("data[widget][widget_params][filters][a.order_currency_id][]", @$this->element->widget_params->filters['a.order_currency_id'], 'multiple="multiple" size="4"');
												echo $currencyList;
											?>
									</td>
								</tr>
								<tr>
									<td class="key">
											<?php echo JText::_( 'HIKASHOP_SHIPPING_METHOD' ); ?>
									</td>
									<td>
										<?php echo $this->shippingMethods->display('data[widget][widget_params][shipping][]',@$this->element->widget_params->shipping_type,@$this->element->widget_params->shipping_id,true,'multiple="multiple" size="5"'); ?>
									</td>
								</tr>
								<tr>
									<td class="key">
											<?php echo JText::_( 'HIKASHOP_PAYMENT_METHOD' ); ?>
									</td>
									<td>
										<?php echo $this->paymentMethods->display('data[widget][widget_params][payment][]',@$this->element->widget_params->payment_type,@$this->element->widget_params->payment_id, true, 'multiple="multiple" size="5"'); ?>
									</td>
								</tr>
								<tr>
									<td class="key">
											<?php echo JText::_( 'HIKA_CATEGORIES' ); ?>
									</td>
									<td>
											<div style="text-align:right;">
												<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("product&task=selectcategory",true ); ?>">
														<button class="btn" type="button" onclick="return false">
															<img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('ADD');?>
														</button>
												</a>
											</div>
											<br/>
											<table class="adminlist table table-striped table-hover" cellpadding="1" width="100%">
												<thead>
														<tr>
															<th class="title">
																	<?php echo JText::_('HIKA_NAME'); ?>
															</th>
															<th class="title">
																	<?php echo JText::_('HIKA_DELETE'); ?>
															</th>
															<th class="title">
																	<?php echo JText::_('ID'); ?>
															</th>
														</tr>
												</thead>
											<tbody id="category_listing">
													<?php
														if(!empty($this->element->widget_params->categories) && $this->element->widget_params->categories!='all'){
								$k = 0;
								for($i = 0,$a = count($this->element->widget_params->categories)+1;$i<$a-1;$i++){

									$row =& $this->element->widget_params->categories[$i];
									if(!empty($row->category_id)){
									?>
										<tr id="category_<?php echo $row->category_id;?>">
											<td>
												<div id="category_<?php echo $row->category_id; ?>_id">
												<a href="<?php echo hikashop_completeLink('category&task=edit&cid='.$row->category_id); ?>"><?php echo $row->category_name; ?></a>
											</td>
											<td align="center">
												<a href="#" onclick="return deleteRow('category_div_<?php echo $row->category_id;?>','category[<?php echo $row->category_id;?>]','category_<?php echo $row->category_id; ?>');">
													<img src="../media/com_hikashop/images/delete.png"/>
												</a>
											</td>
											<td width="1%" align="center">
												<?php echo $row->category_id; ?>
												<div id="category_div_<?php echo $row->category_id;?>">
													<input style="width: 50px; background-color:#e8f9db;" type	="hidden" name="category[<?php echo $row->category_id;?>]" id="category[<?php echo $row->category_id;?>]" value="<?php echo $row->category_id;?>"/>
												</div>
											</td>
										</tr>
									<?php
									}
									$k = 1-$k;
								}
							}
													?>
											</tbody>
										</table>
								</td>
						</tr>
						<tr>
								<td class="key">
										<?php echo JText::_( 'INCLUDING_SUB_CATEGORIES' ); ?>
							</td>
							<td>
									<?php echo JHTML::_('hikaselect.booleanlist', "data[widget][widget_params][category_childs]" , '',@$this->element->widget_params->category_childs	); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'PRODUCTS' ); ?>
								</td>
								<td>
										<div style="float:right">
											<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("product&task=selectrelated&select_type=widget",true ); ?>">
													<button class="btn" type="button" onclick="return false">
														 <img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('ADD');?>
													</button>
											</a>
										</div>
								 <br/>
										<table class="adminlist table table-striped table-hover" cellpadding="1">
											<thead>
													<tr>
														<th class="title">
																<?php echo JText::_('HIKA_NAME'); ?>
														</th>
														<th class="title">
																<?php echo JText::_('HIKA_DELETE'); ?>
														</th>
														<th class="title">
																<?php echo JText::_( 'ID' ); ?>
														</th>
													</tr>
										</thead>
										<tbody id="widget_listing">
												<?php
													$type='widget';
													if(!empty($this->element->widget_params->products)){
															$k = 0;
															foreach($this->element->widget_params->products as $product){
																$row =$product;
																$id = rand();
															?>
																<tr class="<?php echo "row$k"; ?>" id="<?php echo $type.'_'.$row->product_id.'_'.$id;?>">
																	<td>
																			<a href="<?php echo hikashop_completeLink('product&task=edit&cid='.$row->product_id); ?>"><?php echo $row->product_name; ?></a>
																	</td>
																	<td align="center">
																			<a href="#" onclick="return deleteRow('<?php echo $type.'_div_'.$row->product_id.'_'.$id;?>','<?php echo $type;?>[<?php echo $row->product_id;?>][<?php echo $id;?>]','<?php echo $type.'_'.$row->product_id.'_'.$id;?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
																	</td>
																	<td width="1%" align="center">
																			<?php echo $row->product_id; ?>
																			<div id="<?php echo $type.'_div_'.$row->product_id.'_'.$id;?>">
																				<input type="hidden" name="<?php echo $type;?>[<?php echo $row->product_id;?>]" id="<?php echo $type;?>[<?php echo $row->product_id;?>][<?php echo $id;?>]" value="<?php echo $row->product_id;?>"/>
																			</div>
																	</td>
																</tr>
															<?php
																$k = 1-$k;
															}
													}
													?>
											</tbody>
										</table>
								 </td>
							 </tr>
							 <tr>
									<td class="key">
											<?php echo JText::_( 'COUPONS' ); ?>
									</td>
									<td>
											<div style="text-align:right;">
												<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("discount&task=select_coupon",true ); ?>">
														<button class="btn" type="button" onclick="return false">
															<img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('ADD');?>
														</button>
												</a>
											</div>
											<br/>
											<table class="adminlist table table-striped table-hover" cellpadding="1" width="100%">
												<thead>
														<tr>
															<th class="title">
																	<?php echo JText::_('HIKA_NAME'); ?>
															</th>
															<th class="title">
																	<?php echo JText::_('HIKA_DELETE'); ?>
															</th>
															<th class="title">
																	<?php echo JText::_('ID'); ?>
															</th>
														</tr>
												</thead>
											<tbody id="coupon_listing">
													<?php
														if(!empty($this->element->widget_params->coupons) && $this->element->widget_params->coupons!='all'){
								$k = 0;
								for($i = 0,$a = count($this->element->widget_params->coupons)+1;$i<$a-1;$i++){
									$row =& $this->element->widget_params->coupons[$i];
									if(!empty($row->discount_id)){
									?>
										<tr id="coupon_<?php echo $row->discount_id;?>">
											<td>
												<div id="coupon_<?php echo $row->discount_id; ?>_id">
												<a href="<?php echo hikashop_completeLink('discount&task=edit&cid='.$row->discount_id); ?>"><?php echo $row->discount_code; ?></a>
											</td>
											<td align="center">
												<a href="#" onclick="return deleteRow('coupon_div_<?php echo $row->discount_id;?>','coupon[<?php echo $row->discount_id;?>]','coupon_<?php echo $row->discount_id; ?>');">
													<img src="../media/com_hikashop/images/delete.png"/>
												</a>
											</td>
											<td width="1%" align="center">
												<?php echo $row->discount_id; ?>
												<div id="coupon_div_<?php echo $row->discount_id;?>">
													<input style="width: 50px; background-color:#e8f9db;" type	="hidden" name="coupon[<?php echo $row->discount_id;?>]" id="coupon[<?php echo $row->discount_id;?>]" value="<?php echo $row->discount_id;?>"/>
												</div>
											</td>
										</tr>
									<?php
									}
									$k = 1-$k;
								}
							}
													?>
											</tbody>
										</table>
								</td>
						</tr>
					</table>
				</fieldset>
			</div>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
			<td valign="top" width="50%">
<?php } else { ?>
	</div>
	<div class="span6">
<?php } ?>
			<div id="customers_options">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'SPECIFIC_OPTIONS' ); ?></legend>
					<table class="paramlist admintable table">
						<tr id="widget_status">
							<td class="key" >
								<?php echo JText::_( 'DISPLAYED_INFORMATION' );  ?>
							</td>
							<td>
								<select name="data[widget][widget_params][customers]" size=2>
									<option <?php if(!isset($this->element->widget_params->customers) || $this->element->widget_params->customers=='last_customers') echo "selected=\"selected\""; ?> value="last_customers"><?php echo JText::_( 'LAST_CUSTOMER' );  ?></option>
									<option <?php if(isset($this->element->widget_params->customers) && $this->element->widget_params->customers == 'best_customers') echo "selected=\"selected\""; ?> value="best_customers"><?php echo JText::_( 'BEST_CUSTOMER' );  ?></option>
								</select>
							</td>
						</tr>
						<tr id="widget_status">
							<td class="key" >
								<?php echo JText::_( 'ORDERING' ); ?>
							</td>
							<td>
								<select name="data[widget][widget_params][customers_order]" size=2>
									<option <?php if(!isset($this->element->widget_params->customers_order) || $this->element->widget_params->customers_order == 'sales') echo "selected=\"selected\""; ?> value="sales"><?php echo JText::_( 'SALES' );  ?></option>
									<option <?php if(isset($this->element->widget_params->customers_order) && $this->element->widget_params->customers_order=='orders') echo "selected=\"selected\""; ?> value="orders"><?php echo JText::_( 'ORDERS' );  ?></option>
								</select>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
			<div id="partners_options">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'SPECIFIC_OPTIONS' ); ?></legend>
					<table class="paramlist admintable table">
						<tr id="widget_status">
							<td class="key" >
								<?php echo JText::_( 'DISPLAYED_INFORMATION' );  ?>
							</td>
							<td>
								<select name="data[widget][widget_params][partners]" size=2>
									<option <?php if(!isset($this->element->widget_params->partners) || $this->element->widget_params->partners=='last_customers') echo "selected=\"selected\""; ?> value="last_customers"><?php echo JText::_( 'LAST_PARTNER' );  ?></option>
									<option <?php if(isset($this->element->widget_params->partners) && $this->element->widget_params->partners == 'best_customers') echo "selected=\"selected\""; ?> value="best_customers"><?php echo JText::_( 'BEST_PARTNER' );  ?></option>
								</select>
							</td>
						</tr>
						<tr id="widget_status">
							<td class="key" >
								<?php echo JText::_( 'ORDERING' );  ?>
							</td>
							<td>
								<select name="data[widget][widget_params][partners_order]" size=2>
									<option <?php if(!isset($this->element->widget_params->partners_order) || $this->element->widget_params->partners_order == 'sales') echo "selected=\"selected\""; ?> value="sales"><?php echo JText::_( 'SALES' );  ?></option>
									<option <?php if(isset($this->element->widget_params->partners_order) && $this->element->widget_params->partners_order=='orders') echo "selected=\"selected\""; ?> value="orders"><?php echo JText::_( 'ORDERS' );  ?></option>
								</select>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
			<div id='widget_compare'>
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'COMPARE' ); ?></legend>
					<table width:"100%" class="paramlist admintable table">
						<tr>
							<td class="key" >
								<?php echo JText::_( 'COMPARE' );  ?>
							</td>
							<td>
								<span>
									<input <?php if(empty($this->element->widget_params->compare_with) || $this->element->widget_params->compare_with == 'values') echo 'checked="checked"'; ?> onClick="updateCompare()" type="radio" value="values" name="data[widget][widget_params][compare_with]" id="compare_with_values"/>
									<label  for="compare_with_values"><?php echo JText::_( 'VALUES' ); ?>:</label>
									<span><input <?php if(!empty($this->element->widget_params->compares['a.order_status'])) echo 'checked="checked"'; ?> type="checkbox" value="a.order_status" name="data[widget][widget_params][compares][a.order_status]" id="compares_order_status"/><label for="compares_order_status">Order status</label></span>
									<span><input <?php if(!empty($this->element->widget_params->compares['a.order_currency_id'])) echo 'checked="checked"'; ?> type="checkbox" value="d.currency_name" name="data[widget][widget_params][compares][a.order_currency_id]" id="compares_order_currency_id"/><label for="compares_order_currency_id">Currencies</label></span>
									<span><input <?php if(!empty($this->element->widget_params->compares['a.order_payment_method'])) echo 'checked="checked"'; ?> type="checkbox" value="a.order_payment_method" name="data[widget][widget_params][compares][a.order_payment_method]" id="compares_order_payment_method"/><label for="compares_order_payment_method">Payment Methods</label></span>
									<span><input <?php if(!empty($this->element->widget_params->compares['a.order_shipping_method'])) echo 'checked="checked"'; ?> type="checkbox" value="a.order_shipping_method" name="data[widget][widget_params][compares][a.order_shipping_method]" id="compares_order_shipping_method"/><label for="compares_order_shipping_method">Shipping Methods</label></span>
									<span><input <?php if(!empty($this->element->widget_params->compares['a.order_discount_code'])) echo 'checked="checked"'; ?> type="checkbox" value="a.order_discount_code" name="data[widget][widget_params][compares][a.order_discount_code]" id="compares_order_discount_code"/><label for="compares_order_discount_code">Coupons</label></span>
									<span><input <?php if(!empty($this->element->widget_params->compares['prod.order_product_name'])) echo 'checked="checked"'; ?> type="checkbox" value="prod.order_product_name" name="data[widget][widget_params][compares][prod.order_product_name]" id="compares_products"/><label for="compares_products">Products</label></span>
									<span><input <?php if(!empty($this->element->widget_params->compares['c.category_id'])) echo 'checked="checked"'; ?> type="checkbox" value="c.category_name" name="data[widget][widget_params][compares][c.category_id]" id="compares_categories"/><label for="compares_categories">Categories</label></span>
											<?php  ?>
								</span>
								<br/>
								<span>
									<input <?php if(empty($this->element->widget_params->compare_with) || $this->element->widget_params->compare_with == 'periods') echo 'checked="checked"'; ?> onClick="updateCompare()" type="radio" value="periods" name="data[widget][widget_params][compare_with]" id="compare_with_period"/>
									<label  for="compare_with_period"><?php echo JText::_( 'PERIOD' ); ?>:</label>
									<select name="data[widget][widget_params][period_compare]" id="compare_period">
												<option <?php if(!isset($this->element->widget_params->period_compare) || $this->element->widget_params->period_compare=='none') echo "selected=\"selected\""; ?> value="none">None</option>
										<option <?php if(isset($this->element->widget_params->period_compare) && $this->element->widget_params->period_compare == 'last_period') echo "selected=\"selected\""; ?> value="last_period">Last Similare Period</option>
										<option <?php if(isset($this->element->widget_params->period_compare) && $this->element->widget_params->period_compare == 'last_year') echo "selected=\"selected\""; ?> value="last_year">Same period last year</option>
									</select>
								</span>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
			<div id="widget_specific_options">
			 	<fieldset class="adminform">
					<legend><?php echo JText::_( 'OPTIONS' ); ?></legend>
					<table class="paramlist admintable table">
						<tr id="widget_limit">
							<td class="key">
								<?php echo JText::_( 'LIMIT' );?>
							</td>
							<td>
								<input type="texts" name="data[widget][widget_params][limit]" value="<?php echo $this->escape(@$this->element->widget_params->limit); ?>" onchange="if(this.value <0 || this.value > 50){ alert('Setting a negative value or a too high value for the limit might might broke the dashboard.'); this.value=7;}" />
							</td>
						</tr>
						<tr id="widget_region">
							<td class="key">
								<?php echo JText::_( 'ZONE' );//only for map ?>
							</td>
							<td>
								<?php echo $this->region->display('data[widget][widget_params][region]',@$this->element->widget_params->region); ?>
							</td>
						</tr>
						<?php if(hikashop_level(2)){ ?>
						<tr>
							<td class="key">
								<?php echo JText::_('ENCODING_FORMAT'); ?>
							</td>
							<td>
								<?php echo $this->encoding->display("data[widget][widget_params][format]",@$this->element->widget_params->format); ?>
							</td>
						</tr>
						<?php }else{ ?>
						<tr>
							<td class="key">
								<?php echo JText::_('ENCODING_FORMAT'); ?>
							</td>
							<td>
								<?php echo hikashop_getUpgradeLink('business');; ?>
							</td>
						</tr>
						<?php } ?>
						<tr id="map_options">
							<td class="key">
								<?php echo JText::_( 'ORDERING' ); ?>
							</td>
							<td>
								<div class="controls">
									<fieldset class="radio btn-group">
										<input <?php if(empty($this->element->widget_params->map_source) || $this->element->widget_params->map_source == 'shipping') echo 'checked="checked"'; ?> type="radio" value="shipping" name="data[widget][widget_params][map_source]" id="map_source_shipping"/><label for="map_source_shipping"><?php echo JText::_( 'HIKASHOP_SHIPPING_ADDRESS' );  ?></label>
										<input <?php if(!empty($this->element->widget_params->map_source) && $this->element->widget_params->map_source == 'billing') echo 'checked="checked"'; ?> type="radio" value="billing" name="data[widget][widget_params][map_source]" id="map_source_billing"/><label for="map_source_billing"><?php echo JText::_( 'HIKASHOP_BILLING_ADDRESS' );  ?></label>
									</fieldset>
								</div>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
			<div id="products_options">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'SPECIFIC_OPTIONS' ); ?></legend>
					<table class="paramlist admintable">
						<tr id='product_datas'>
							<td class="key">
								<?php echo JText::_( 'DISPLAYED_INFORMATION' );?>
							</td>
							<td>
								<div class="controls">
									<fieldset class="radio btn-group">
										<input <?php if(empty($this->element->widget_params->product_data) || $this->element->widget_params->product_data == 'sales') echo 'checked="checked"'; ?> type="radio" value="sales" name="data[widget][widget_params][product_data]" id="data_sales"/><label for="data_sales"><?php echo JText::_( 'SALES' );  ?></label>
										<input <?php if(!empty($this->element->widget_params->product_data) && $this->element->widget_params->product_data == 'orders') echo 'checked="checked"'; ?> type="radio" value="orders" name="data[widget][widget_params][product_data]" id="data_orders"/><label for="data_orders"><?php echo JText::_( 'ORDERS' );  ?></label>
										<span id="data_hits"><input <?php if(!empty($this->element->widget_params->product_data) && $this->element->widget_params->product_data == 'clicks') echo 'checked="checked"'; ?> type="radio" value="clicks" name="data[widget][widget_params][product_data]" id="data_clicks"/><label for="data_clicks"><?php echo JText::_( 'CLICKS' );  ?></label></span>
									</fieldset>
								</div>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'ORDERING' );?>
							</td>
							<td>
							<div class="controls">
										<fieldset class="radio btn-group">
								<input <?php if(empty($this->element->widget_params->product_order_by) || $this->element->widget_params->product_order_by == 'best') echo 'checked="checked"'; ?> type="radio" value="best" name="data[widget][widget_params][product_order_by]" id="order_best"/><label for="order_best"><?php echo JText::_( 'BEST' );  ?></label>
												<input <?php if(!empty($this->element->widget_params->product_order_by) && $this->element->widget_params->product_order_by == 'worst') echo 'checked="checked"'; ?> type="radio" value="worst" name="data[widget][widget_params][product_order_by]" id="order_worst"/><label for="order_worst"><?php echo JText::_( 'WORST' );  ?></label>
								</fieldset>
							</div>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
			<div id="orders_options">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'SPECIFIC_OPTIONS' ); ?></legend>
					<table class="paramlist admintable table">
						<tr id="orders_order_by">
							<td class="key">
								<?php echo JText::_( 'ORDERING' ); ?>
							</td>
							<td>
							<div class="controls">
								<fieldset class="radio btn-group">
										<input <?php if(empty($this->element->widget_params->orders_order_by) || $this->element->widget_params->orders_order_by == 'last') echo 'checked="checked"'; ?> type="radio" value="last" name="data[widget][widget_params][orders_order_by]" id="orders_order_last"/><label for="orders_order_last"><?php echo JText::_( 'LAST' );  ?></label>
										<input <?php if(!empty($this->element->widget_params->orders_order_by) && $this->element->widget_params->orders_order_by == 'best') echo 'checked="checked"'; ?> type="radio" value="best" name="data[widget][widget_params][orders_order_by]" id="orders_order_best"/><label for="orders_order_best"><?php echo JText::_( 'BEST' );  ?></label>
								</fieldset>
							</div>
							</td>
						</tr>
						<tr id="orders_total_calculation">
							<td class="key">
								<?php echo JText::_( 'INCLUDE_SHIPPING' ); ?>
							</td>
							<td>
							<div class="controls">
								<fieldset class="radio btn-group">
									<input <?php if(empty($this->element->widget_params->orders_total_calculation) || $this->element->widget_params->orders_total_calculation == 'include_fees') echo 'checked="checked"'; ?> type="radio" value="include_fees" name="data[widget][widget_params][orders_total_calculation]" id="include_fees"/><label for="include_fees"><?php echo JText::_( 'YES' );  ?></label>
									<input <?php if(!empty($this->element->widget_params->orders_total_calculation) && $this->element->widget_params->orders_total_calculation == 'exclude_fees') echo 'checked="checked"'; ?> type="radio" value="exclude_fees" name="data[widget][widget_params][orders_total_calculation]" id="exclude_fees"/><label for="exclude_fees"><?php echo JText::_( 'NO' );  ?></label>
								</fieldset>
							</div>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
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
	<?php if($this->dashboard){ 'ok';?>
	<input type="hidden" name="dashboard" value="<?php echo "TRUE"; ?>"/>
	<?php } ?>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->widget_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="delete_row" id="delete_row" value="-1" />
	<input type="hidden" name="ctrl" value="report" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<div id="chart" ></div>
<br style="clear:both" />
