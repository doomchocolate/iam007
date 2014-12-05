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
	$access="";
	$access2="";
	if(hikashop_level(2)){
		$access="var access = 'all'; var p_access = parent.window.document.getElementById('widget_access'); if(p_access) access = p_access.value;";
		$access2="p_access = document.getElementById('widget_access'); if(p_access) p_access.value = access;";
	}
?>
<script type="text/javascript">
function submitCurrentForm() {
	var published = 1;
	name = parent.window.document.getElementById('name').value;
	publishedNo = parent.window.document.getElementById('data_widget_widget_published0').checked;
	<?php echo $access; ?>

	if(publishedNo){
		published=0;
	}
	document.getElementById('name').value = name;
	document.getElementById('published').value = published;
	<?php echo $access2; ?>
	this.hikashop.submitform('apply_table', 'adminForm');
}
</script>
<div id="iframedoc"></div>
<?php
	if($this->first) echo hikashop_display(JText::_( 'CONFIGURE_WIDGET_ROW' ));
?>
<fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="btn" type="button" onclick="submitCurrentForm()"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=product" method="post" name="adminForm" id="adminForm">
	<table width="100%" >
		<tr>
				<td width="50%" valign="top">
					<fieldset class="adminform">
							<legend><?php echo JText::_('CURRENT_REPORT'); ?></legend>
							<table class="paramlist admintable table">
								<tr>
										<td class="key">
											<label for="data[widget][widget_params][table][<?php echo $this->row->row_id; ?>][row_name]">
													<?php echo JText::_( 'HIKA_NAME' ); ?>
											</label>
									</td>
									<td>
										<input type="text" name="data[widget][widget_params][table][<?php echo $this->row->row_id; ?>][row_name]" id="row_name" class="inputbox" size="40" value="<?php echo $this->escape(@$this->row->row_name); ?>" />
									</td>
								</tr>
							<tr id="widget_period">
									<td class="key"><?php echo JText::_('PERIOD'); ?></td>
								<td>
									<input <?php if(empty($this->row->widget_params->periodType) || $this->row->widget_params->periodType == 'proposedPeriod') echo 'checked="checked"'; ?> type="radio" value="proposedPeriod" name="data[widget][widget_params][table][<?php echo $this->row->row_id; ?>][widget_params][periodType]" id="display_proposed_period"/>
										<?php echo $this->periodType->display('data[widget][widget_params][table]['.$this->row->row_id.'][widget_params][proposedPeriod]',@$this->row->widget_params->proposedPeriod); ?>
											</label><br/>
											<input <?php if(!empty($this->row->widget_params->periodType) && $this->row->widget_params->periodType == 'specificPeriod') echo 'checked="checked"'; ?> type="radio" value="specificPeriod" name="data[widget][widget_params][table][<?php echo $this->row->row_id; ?>][widget_params][periodType]" id="display_specific_period"/><label for="display_specific_period">
										<?php echo JText::_('START_DATE').' '; echo JHTML::_('calendar', hikashop_getDate((@$this->row->widget_params->start?@$this->row->widget_params->start:''),'%Y-%m-%d %H:%M'), 'data[widget][widget_params][table]['.$this->row->row_id.'][widget_params][start]','period_start','%Y-%m-%d %H:%M',array('size'=>'20')); ?>
										<?php echo JText::_('END_DATE').' '; echo JHTML::_('calendar', hikashop_getDate((@$this->row->widget_params->end?@$this->row->widget_params->end:''),'%Y-%m-%d %H:%M'), 'data[widget][widget_params][table]['.$this->row->row_id.'][widget_params][end]','period_end','%Y-%m-%d %H:%M',array('size'=>'20')); ?>
										<br/><?php echo JText::_('PERIOD').' '; echo $this->delay->display('data[widget][widget_params][table]['.$this->row->row_id.'][widget_params][period]',(int)@$this->row->widget_params->period,3); ?>
								</td>
							</tr>
					</table>
					</fieldset>
	 		</td>
		</tr>
		<tr id="widget_type">
				<td colspan=2>
					<fieldset class="adminform">
							<legend><?php echo JText::_('HIKA_TYPE'); ?></legend>
							<table class="paramlist admintable table">
								<tr>
									<td>
										<div class="controls">
											<fieldset class="radio btn-group">
												<input <?php if(empty($this->row->widget_params->content) || $this->row->widget_params->content == 'orders') echo 'checked="checked"'; ?> onchange="updateDisplay();" type="radio" value="orders" name="data[widget][widget_params][table][<?php echo $this->row->row_id; ?>][widget_params][content]" id="type_orders"/><label for="type_orders"><?php echo JText::_( 'ORDERS' );  ?></label>
												<input <?php if(empty($this->row->widget_params->content) || $this->row->widget_params->content == 'sales') echo 'checked="checked"'; ?> onchange="updateDisplay();" type="radio" value="sales" name="data[widget][widget_params][table][<?php echo $this->row->row_id; ?>][widget_params][content]" id="type_sales"/><label for="type_sales"><?php echo JText::_( 'SALES' );  ?></label>
												<input <?php if(!empty($this->row->widget_params->content) && $this->row->widget_params->content == 'taxes') echo 'checked="checked"'; ?> onchange="updateDisplay();" type="radio" value="taxes" name="data[widget][widget_params][table][<?php echo $this->row->row_id; ?>][widget_params][content]" id="type_taxes"/><label for="type_taxes"><?php echo JText::_( 'TAXES' );  ?></label>
												<input <?php if(!empty($this->row->widget_params->content) && $this->row->widget_params->content == 'customers') echo 'checked="checked"'; ?> onchange="updateDisplay();" type="radio" value="customers" name="data[widget][widget_params][table][<?php echo $this->row->row_id; ?>][widget_params][content]" id="type_customers"/><label for="type_customers"><?php echo JText::_( 'CUSTOMERS' );  ?></label>
												<input <?php if(!empty($this->row->widget_params->content) && $this->row->widget_params->content == 'partners') echo 'checked="checked"'; ?> onchange="updateDisplay();" type="radio" value="partners" name="data[widget][widget_params][table][<?php echo $this->row->row_id; ?>][widget_params][content]" id="type_partners"/><label for="type_partners"><?php echo JText::_( 'PARTNERS' );  ?></label>
												<input <?php if(!empty($this->row->widget_params->content) && $this->row->widget_params->content == 'best') echo 'checked="checked"'; ?> onchange="updateDisplay();" type="radio" value="best" name="data[widget][widget_params][table][<?php echo $this->row->row_id; ?>][widget_params][content]" id="type_best"/><label for="type_best"><?php echo JText::_( 'BEST' );  ?></label>
												<input <?php if(!empty($this->row->widget_params->content) && $this->row->widget_params->content == 'worst') echo 'checked="checked"'; ?> onchange="updateDisplay();" type="radio" value="worst" name="data[widget][widget_params][table][<?php echo $this->row->row_id; ?>][widget_params][content]" id="type_worst"/><label for="type_worst"><?php echo JText::_( 'WORST' );  ?></label>
												<?php ?>
											</fieldset>
										</div>
									</td>
								</tr>
							</table>
					</fieldset>
				</td>
		</tr>
		<tr>
			<td width="50%" valign="top" rowspan=2>
					<fieldset id='sales_options' class="adminform">
						<legend><?php echo JText::_( 'OPTIONS' ); ?></legend>
						<table class="paramlist admintable" width="100%">
							<tr id="tax_include">
								<td class="key" >
									<?php echo JText::_( 'WITH_TAX' );  ?>
								</td>
								<td>
									<?php echo JHTML::_('hikaselect.booleanlist', 'data[widget][widget_params][table]['.$this->row->row_id.'][widget_params][with_tax]' , '',@$this->row->widget_params->with_tax); ?>
								</td>
							</tr>
							<tr id="include_shipping">
								<td class="key" >
									<?php echo JText::_( 'INCLUDE_SHIPPING' );  ?>
								</td>
								<td>
									<?php echo JHTML::_('hikaselect.booleanlist', 'data[widget][widget_params][table]['.$this->row->row_id.'][widget_params][include_shipping]' , '',@$this->row->widget_params->include_shipping); ?>
								</td>
							</tr>
						</table>
					</fieldset>

					<fieldset id='filters' class="adminform">
						<legend><?php echo JText::_( 'FILTERS' ); ?></legend>
						<table class="paramlist admintable table" width="100%">
							<tr id="widget_status">
						<td class="key" >
							<?php echo JText::_( 'ORDER_STATUS' );  ?>
						</td>
						<td>
							<?php echo $this->status->display('data[widget][widget_params][table]['.$this->row->row_id.'][widget_params][filters][a.order_status][]',@$this->row->widget_params->filters['a.order_status'],' multiple="multiple" size="5"',false); ?>
						</td>
					</tr>
								<tr id="widget_currencies">
								<td class="key">
										<label for="data[widget][widget_params][table][<?php echo $this->row->row_id; ?>][widget_params][filters][a.order_currency_id]">
												<?php echo JText::_( 'CURRENCIES' ); ?>
											</label>
									</td>
									<td>
												<?php 	$currency=hikashop_get('type.currency');
												 $currencyList=$currency->display("data[widget][widget_params][table][".$this->row->row_id."][widget_params][filters][a.order_currency_id][]", @$this->row->widget_params->filters['a.order_currency_id'], 'multiple="multiple" size="4"');
											 echo $currencyList;
											?>
									</td>
								</tr>
								<tr>
						<td class="key">
							<label>
								<?php echo JText::_( 'HIKASHOP_SHIPPING_METHOD' ); ?>
							</label>
						</td>
						<td>
							<?php if(!empty($this->shipping)){
								echo $this->shipping->display('data[widget][widget_params][table]['.$this->row->row_id.'][widget_params][shipping][]',$this->row->widget_params->shipping, 'multiple', true, 'multiple="multiple" size="5"');
						}?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<label>
								<?php echo JText::_( 'HIKASHOP_PAYMENT_METHOD' ); ?>
							</label>
						</td>
						<td>
							<?php echo $this->paymentMethods->display('data[widget][widget_params][table]['.$this->row->row_id.'][widget_params][payment][]',$this->row->widget_params->payment_type,@$this->row->widget_params->payment_id, true, 'multiple="multiple" size="5"'); ?>
						</td>
					</tr>
									<tr>
									<td class="key">
												<?php echo JText::_( 'HIKA_CATEGORIES' ); ?>
									</td>
									<td>
												<div style="text-align:right;">
													<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 380}}" href="<?php echo hikashop_completeLink("product&task=selectcategory",true ); ?>">
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
													if(!empty($this->row->widget_params->categories) && $this->row->widget_params->categories!='all'){
								$k = 0;
								for($i = 0,$a = count($this->row->widget_params->categories)+1;$i<$a-1;$i++){

									$row =& $this->row->widget_params->categories[$i];
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
										<label for="data[widget][widget_params][table][<?php echo $this->row->row_id; ?>][widget_params][category_childs]">
											<?php echo JText::_( 'INCLUDING_SUB_CATEGORIES' ); ?>
										</label>
								</td>
								<td>
										<?php echo JHTML::_('hikaselect.booleanlist', "data[widget][widget_params][table][".$this->row->row_id."][widget_params][category_childs]" , '',@$this->row->widget_params->category_childs	); ?>
								</td>
							</tr>
							<tr>
								<td class="key">
										<?php echo JText::_( 'PRODUCTS' ); ?>
									</td>
									<td>
											<div style="float:right">
												<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 380}}" href="<?php echo hikashop_completeLink("product&task=selectrelated&select_type=widget",true ); ?>">
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
														if(!empty($this->row->widget_params->products)){
																$k = 0;
																foreach($this->row->widget_params->products as $product){
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
												<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 380}}" href="<?php echo hikashop_completeLink("discount&task=select_coupon",true ); ?>">
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
													if(!empty($this->row->widget_params->coupons) && $this->row->widget_params->coupons!='all'){
								$k = 0;
								for($i = 0,$a = count($this->row->widget_params->coupons)+1;$i<$a-1;$i++){
									$row =& $this->row->widget_params->coupons[$i];
									if(!empty($row->discount_id)){
									?>
										<tr id="row_coupon_<?php echo $row->discount_id;?>">
											<td>
												<div id="row_coupon_<?php echo $row->discount_id; ?>_id">
												<a href="<?php echo hikashop_completeLink('discount&task=edit&cid='.$row->discount_id); ?>"><?php echo $row->discount_code; ?></a>
											</td>
											<td align="center">
												<a href="#" onclick="return deleteRow('row_coupon_div_<?php echo $row->discount_id;?>','row_coupon[<?php echo $row->discount_id;?>]','row_coupon_<?php echo $row->discount_id; ?>');">
													<img src="../media/com_hikashop/images/delete.png"/>
												</a>
											</td>
											<td width="1%" align="center">
												<?php echo $row->discount_id; ?>
												<div id="coupon_div_<?php echo $row->discount_id;?>">
													<input style="width: 50px; background-color:#e8f9db;" type	="hidden" name="row_coupon[<?php echo $row->discount_id;?>]" id="row_coupon[<?php echo $row->discount_id;?>]" value="<?php echo $row->discount_id;?>"/>
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
		<fieldset id='customers_options' class="adminform">
			<legend><?php echo JText::_( 'OPTIONS' ); ?></legend>
			<table class="paramlist admintable table" width="100%">
						<tr id="widget_status">
					<td class="key" >
						<?php echo JText::_( 'DISPLAYED_INFORMATION' );  ?>
					</td>
					<td>
								<select name="data[widget][widget_params][table][<?php echo $this->row->row_id; ?>][widget_params][customers]" size=3>
									<option <?php if(!isset($this->row->widget_params->customers) || $this->row->widget_params->customers=='last_customer') echo "selected=\"selected\""; ?> value="last_customer"><?php echo JText::_( 'LAST_CUSTOMER' );  ?></option>
							<option <?php if(isset($this->row->widget_params->customers) && $this->row->widget_params->customers == 'best_customer') echo "selected=\"selected\""; ?> value="best_customer"><?php echo JText::_( 'BEST_CUSTOMER' );  ?></option>
							<option <?php if(isset($this->row->widget_params->customers) && $this->row->widget_params->customers == 'total_customers') echo "selected=\"selected\""; ?> value="total_customers"><?php echo JText::_( 'TOTAL_CUSTOMERS' );  ?></option>
						</select>
					</td>
				</tr>
			</table>
		</fieldset>
		<fieldset id='partners_options' class="adminform">
			<legend><?php echo JText::_( 'OPTIONS' ); ?></legend>
			<table class="paramlist admintable table" width="100%">
						<tr id="widget_status">
					<td class="key" >
						<?php echo JText::_( 'DISPLAYED_INFORMATION' );  ?>
					</td>
					<td>
								<select name="data[widget][widget_params][table][<?php echo $this->row->row_id; ?>][widget_params][partners]" size=3>
									<option <?php if(!isset($this->row->widget_params->partners) || $this->row->widget_params->partners=='last_partners') echo "selected=\"selected\""; ?> value="last_partners"><?php echo JText::_( 'LAST_PARTNER' );  ?></option>
							<option <?php if(isset($this->row->widget_params->partners) && $this->row->widget_params->partners == 'best_partners') echo "selected=\"selected\""; ?> value="best_partners"><?php echo JText::_( 'BEST_PARTNER' );  ?></option>
							<option <?php if(isset($this->row->widget_params->partners) && $this->row->widget_params->partners == 'total_partners') echo "selected=\"selected\""; ?> value="total_partners"><?php echo JText::_( 'TOTAL_PARTNERS' );  ?></option>
						</select>
					</td>
				</tr>
			</table>
		</fieldset>
		<fieldset id='best_options'>
			<legend><?php echo JText::_( 'OPTIONS' ); ?></legend>
			<table class="paramlist admintable" width="100%">
						<tr id="widget_status">
					<td class="key" >
						<?php echo JText::_( 'APPLY_ON' );  ?>
					</td>
					<td>
								<select name="data[widget][widget_params][table][<?php echo $this->row->row_id; ?>][widget_params][apply_on]" size=4>
									<option <?php if(!isset($this->row->widget_params->apply_on) || $this->row->widget_params->apply_on=='product') echo "selected=\"selected\""; ?> value="product"><?php echo JText::_( 'PRODUCT' );  ?></option>
							<option <?php if(isset($this->row->widget_params->apply_on) && $this->row->widget_params->apply_on == 'category') echo "selected=\"selected\""; ?> value="category"><?php echo JText::_( 'CATEGORY' );  ?></option>
							<option <?php if(isset($this->row->widget_params->apply_on) && $this->row->widget_params->apply_on == 'shipping_method') echo "selected=\"selected\""; ?> id="best_worst_shipping" value="shipping_method"><?php echo JText::_( 'HIKASHOP_SHIPPING_METHOD' );  ?></option>
							<option <?php if(isset($this->row->widget_params->apply_on) && $this->row->widget_params->apply_on == 'payment_method') echo "selected=\"selected\""; ?> id="best_worst_payment" value="payment_method"><?php echo JText::_( 'HIKASHOP_PAYMENT_METHOD' );  ?></option>
							<option <?php if(isset($this->row->widget_params->apply_on) && $this->row->widget_params->apply_on == 'currency') echo "selected=\"selected\""; ?> id="best_worst_currency" value="currency"><?php echo JText::_( 'CURRENCY' );  ?></option>
							<option <?php if(isset($this->row->widget_params->apply_on) && $this->row->widget_params->apply_on == 'discount') echo "selected=\"selected\""; ?> value="discount"><?php echo JText::_( 'DISCOUNT' );  ?></option>
							<option <?php if(isset($this->row->widget_params->apply_on) && $this->row->widget_params->apply_on == 'country') echo "selected=\"selected\""; ?> id="best_worst_country" value="country"><?php echo JText::_( 'COUNTRY' );  ?></option>
						</select>
					</td>
				</tr>
			</table>
		</fieldset>
	</table>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" id="name" name="data[widget][widget_name]" value="" />
	<input type="hidden" id="name" name="data[widget][widget_params][table][<?php echo $this->row->row_id; ?>][widget_params][display]" value="table" />
	<input type="hidden" id="published" name="data[widget][widget_published]" value="" />
	<input type="hidden" id="access" name="data[widget][widget_access]" value="" />
	<input type="hidden" name="data[edit_row]" value="1" />
	<input type="hidden" name="data[widget][widget_id]" value="<?php echo (int)@$this->element->widget_id; ?>" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->widget_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getVar('ctrl');?>" />

	<?php echo JHTML::_( 'form.token' );?>
</form>
