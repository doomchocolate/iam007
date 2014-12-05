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
$cart_type = JRequest::getString('cart_type','cart');
$cart_id = JRequest::getInt('cart_id','');
$tmpl = JRequest::getString('tmpl','view');
$config = hikashop_config();
global $Itemid;
$Itemid = '&Itemid='.$Itemid;
if($cart_type == 'wishlist'){
	$addText = JText::_('ADD_TO_CART');
}
else{
	$addText = JText::_('ADD_TO_WISHLIST');
}
$app = JFactory::getApplication();
$userCurrent = hikashop_loadUser(true);
if(isset($userCurrent))
	$userCurrent = $userCurrent->id;
else
	$userCurrent = 0;

?>
<form method="POST" id="hikashop_show_cart_form" name="hikashop_show_cart_form" action="<?php echo hikashop_completeLink('cart'.$Itemid);?>">
<div onload="document.getElementById('task').value='savecart'" id="hikashop_cart_listing">
<?php if($tmpl != 'component'){ ?>
<fieldset>
	<div class="header hikashop_header_title"><h1><?php if($cart_type == 'cart')echo JText::_('CARTS');else echo JText::_('WISHLISTS'); ?></h1></div>
	<div class="toolbar hikashop_header_buttons" id="toolbar" style="float: right;">
		<table class="hikashop_no_border">
			<tr>
				<td>
					<?php
						if($cart_type == 'cart' && $config->get('enable_multicart')){
					?>
							<a href="<?php echo hikashop_completeLink('cart&task=showcarts&cart_type='.$cart_type.$Itemid); ?>">
								<span class="icon-32-show_cart" title="<?php echo JText::_('DISPLAY_THE_CARTS'); ?>">
								</span>
								<?php echo JText::_('DISPLAY_THE_CARTS'); ?>
							</a>
					<?php
						}elseif($userCurrent == $this->cartVal->user_id && $cart_type == 'wishlist'){
					?>
							<a href="<?php echo hikashop_completeLink('cart&task=showcarts&cart_type='.$cart_type.$Itemid); ?>">
								<span class="icon-32-show_wishlist" title="<?php echo JText::_('DISPLAY_THE_WISHLISTS'); ?>">
								</span>
								<?php echo JText::_('DISPLAY_THE_WISHLISTS'); ?>
							</a>
					<?php
						}
					?>
				</td>
			<!-- Wishlist V2
				<td>
					<a title="<?php echo JText::_('HIKA_EMAIL');?>" class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink('product&task=sendcart',true); ?>">
						<img src="<?php echo HIKASHOP_IMAGES; ?>go.png" alt="<?php echo JText::_('HIKA_EMAIL');?>"/>
					</a>
				</td>
			-->
				<td>
					<?php echo $this->popup->display(
						'<span class="icon-32-print" title="'. JText::_('HIKA_PRINT').'"></span>'. JText::_('HIKA_PRINT'),
						'HIKA_PRINT',
						hikashop_completeLink('cart&task=showcart&cart_type='.$cart_type.'&cart_id='.$cart_id,true),
						'hikashop_print_cart',
						760, 480, '', '', 'link'
					); ?>
				</td>
				<?php if($this->cartVal->display && ($userCurrent == $this->cartVal->user_id)){ ?>
				<td>
					<a href="#" onclick="javascript:document.forms['hikashop_show_cart_form'].submit();">
						<span class="icon-32-save" title="<?php echo JText::_('HIKA_SAVE'); ?>">
						</span>
						<?php echo JText::_('HIKA_SAVE'); ?>
					</a>
				</td>
				<?php } ?>
				<td>
				<?php
				if(!$config->get('enable_multicart') && $cart_type == 'cart'){
				?>
					<a href="#" onclick="history.back()">
				<?php
				}else{
				?>
					<a href="<?php echo JRoute::_('index.php?option='.HIKASHOP_COMPONENT.'&ctrl=cart&task=showcarts&cart_type='.$cart_type.$Itemid); ?>" >
				<?php } ?>
						<span class="icon-32-back" title="<?php echo JText::_('HIKA_BACK'); ?>">
						</span>
						<?php echo JText::_('HIKA_BACK'); ?>
					</a>
				</td>
			</tr>
		</table>
	</div>
</fieldset>
<?php
}else{
	$js = "window.hikashop.ready( function() {setTimeout(function(){window.focus();window.print();setTimeout(function(){hikashop.closeBox();}, 1000);},1000);});";
	$doc = JFactory::getDocument();
	$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
}

if(($this->cartVal->display == 'registered' && $userCurrent == 0) || ($this->cartVal->display == 'link' && JRequest::getString('link',0) == 0)){
	$this->cartVal->display = 0;
}

if(($this->cartVal->display && $cart_type == 'wishlist') || ($this->cartVal->display == 'main' && $cart_type == 'cart' && $this->cartVal->user_id == $userCurrent)){

?>
<div class="iframedoc" id="iframedoc"></div>
<table class="hikashop_showcart_infos table table-striped table-hover" width="100%">
	<?php if($userCurrent == $this->cartVal->user_id){ ?>
	<tr>
		<td class="key">
			<?php echo JText::_('HIKASHOP_CART_NAME'); ?>:
		</td>
		<td width="60%">
		<?php if($tmpl != 'component'){ ?>
			<input type="text" id="cart_name" name="cart_name" value="<?php echo $this->cartVal->cart_name; ?>" class="inputbox"/>
		<?php }else{ ?>
			<span id="hikashop_wishlist_name" class="hikashop_wishlist_name"><?php echo $this->cartVal->cart_name; ?></span>
		<?php } ?>
		</td>
	</tr>
	<?php } if($cart_type != 'cart' && $userCurrent == $this->cartVal->user_id){ ?>
	<tr>
		<td class="key">
	<?php
		$baseUrl = JURI::base();
		$baseUrl .= 'index.php?option=com_hikashop&ctrl=';
		$token = "";
		if($this->cartVal->display == 'public'){
			$displayLink = JText::_('HIKASHOP_EVERYBODY');
		}elseif($this->cartVal->display == 'registered'){
			$displayLink = JText::_('HIKASHOP_REGISTERED_USERS');
		}elseif(!$this->cartVal->display || $this->cartVal->display == 'main' || $this->cartVal->display == 'nobody'){
			$this->cartVal->display = 'nobody';
			$displayLink = JText::_('HIKASHOP_NOBODY');
		}else{// email
			$displayLink = JText::_('HIKA_EMAIL');
			$token = $this->cartVal->display;
			$this->cartVal->display = 'email';
		}
		echo JText::_('SHARE'); ?>:
		</td>
		<td>
			<span id="hikashop_wishlist_share" class="hikashop_wishlist_share">
				<select style="width:145px;" id="hikashop_wishlist_share_select" name="cart_share" onChange="showLink(cart_share.value);">
					<option value="<?php echo $this->cartVal->display;?>"><?php echo $displayLink; ?></option>
				 	<?php
				 	if($this->cartVal->display != "nobody")
						echo "<option value='nobody'>".JText::_('HIKASHOP_NOBODY')."</option>";
					if($this->cartVal->display != "public")
						echo "<option value='public'>".JText::_('HIKASHOP_EVERYBODY')."</option>";
				 	if($this->cartVal->display != "registered")
						echo "<option value='registered'>".JText::_('HIKASHOP_REGISTERED_USERS')."</option>";
				 	if($this->cartVal->display != "email")
						echo "<option value='email'>".JText::_('HIKA_EMAIL')."</option>";
					?>
				</select>
			</span>
			<span class="hikashop_wishlist_share_text" id="hikashop_wishlist_share_text"></span>
		</td>
	</tr>
	<script type="text/javascript">
		window.onload=function(){
			var link = document.getElementById('hikashop_wishlist_link');
			var linkDisplay = document.getElementById('hikashop_wishlist_link_display');
			var linkDisplayText = document.getElementById('hikashop_wishlist_link_display_text');
			var linkShare = document.getElementById('hikashop_wishlist_share');
			var linkShareSelect = document.getElementById('hikashop_wishlist_share_select').value;
			var linkShareText = document.getElementById('hikashop_wishlist_share_text');
			if(linkShareSelect == 'public') linkShareText.innerHTML = 'Anybody';
			else if(linkShareSelect == 'registered') linkShareText.innerHTML = 'Registered users';
			else if(linkShareSelect == 'email') linkShareText.innerHTML = 'E-mail';
			else linkShareText.innerHTML = 'Nobody';
			if(linkShareSelect == 'email'){
				link.style.display="table-row";
				if(linkDisplay){
					linkDisplay.value = "<?php echo $baseUrl.'cart&task=showcart&cart_id='.$cart_id.'&cart_type='.$cart_type.'&Itemid='.$Itemid.'&link='.$token ?>";
					linkShareText.style.display="none";
				}
				if(linkDisplayText){
					linkDisplayText.innerHTML = "<?php echo $baseUrl.'cart&task=showcart&cart_id='.$cart_id.'&cart_type='.$cart_type.'&Itemid='.$Itemid.'&link='.$token ?>";
					linkShare.style.display="none";
				}
			}else if(linkShareSelect == 'nobody'){
				link.style.display="none";
				if(linkDisplay){
					linkShareText.style.display="none";
				}else{
					linkShare.style.display="none";
				}
			}else{
				link.style.display="table-row";
				if(linkDisplay){
					linkDisplay.value = "<?php echo $baseUrl.'cart&task=showcart&cart_id='.$cart_id.'&cart_type='.$cart_type.'&Itemid='.$Itemid ?>";
					linkShareText.style.display="none";
				}
				if(linkDisplayText){
					linkDisplayText.innerHTML = "<?php echo $baseUrl.'cart&task=showcart&cart_id='.$cart_id.'&cart_type='.$cart_type.'&Itemid='.$Itemid ?>";
					linkShare.style.display="none";
				}
			}
		}
		function showLink(share){
			var link = document.getElementById('hikashop_wishlist_link');
			var linkShareText = document.getElementById('hikashop_wishlist_share_text');
			link.style.display="none";
			var linkDisplay = document.getElementById('hikashop_wishlist_link_display');
			var linkDisplayText = document.getElementById('hikashop_wishlist_link_display_text');
			if(share == 'public') linkShareText.innerHTML = 'Anybody';
			else if(share == 'registered') linkShareText.innerHTML = 'Registered users';
			else if(share == 'email') linkShareText.innerHTML = 'E-mail';
			else linkShareText.innerHTML = 'Nobody';
			if(share == 'public' || share == 'registered'){
				if(linkDisplay)
					linkDisplay.value = "<?php echo $baseUrl.'cart&task=showcart&cart_id='.$cart_id.'&cart_type='.$cart_type.'&Itemid='.$Itemid ?>";
				if(linkDisplayText)
					linkDisplayText.innerHTML = "<?php echo $baseUrl.'cart&task=showcart&cart_id='.$cart_id.'&cart_type='.$cart_type.'&Itemid='.$Itemid ?>";
				link.style.display="table-row";
			}
			else if(share == 'email'){
				<?php
					$chaine = "abcdefghijklmnpqrstuvwxy0123456789";
					srand((double)microtime()*1000000);
					for($i=0; $i<20; $i++) {
						$token .= $chaine[rand()%strlen($chaine)];
					}
					$tokenLink = '&link='.$token;
				?>
				if(linkDisplay)
					linkDisplay.value = "<?php echo $baseUrl.'cart&task=showcart&cart_id='.$cart_id.'&cart_type='.$cart_type.'&Itemid='.$Itemid.$tokenLink ?>";
				if(linkDisplayText)
					linkDisplayText.innerHTML = "<?php echo $baseUrl.'cart&task=showcart&cart_id='.$cart_id.'&cart_type='.$cart_type.'&Itemid='.$Itemid.$tokenLink ?>";
				link.style.display="table-row";
			}
			else{
				link.style.display="none";
			}
			link.focus();
		}
	</script>
	<input type="hidden" name="hikashop_wishlist_token" value="<?php echo $token; ?>"/>
	<tr width="100%" id='hikashop_wishlist_link' style="display:none;">
		<td  class="key">
			<span class="hikashop_wishlist_link_text"><?php echo JText::_('HIKASHOP_WISHLIST_LINK'); ?>:</span>
		</td>
		<?php if($tmpl != 'component'){ ?>
		<td width="60%">
			<input onClick="javascript:this.focus();this.select();" style="width:100%;" readonly="readonly" type="text" id="hikashop_wishlist_link_display" name="hikashop_wishlist_link_display" value=""/>
		</td>
	</tr>
		<?php }else{ ?>
	<tr><td colspan="2"><span class="hikashop_wishlist_link_display_text" id="hikashop_wishlist_link_display_text"></span></td></tr>
		<?php
		}
		?>
	<?php } ?>
</table>
	<table id="hikashop_cart_product_listing" class="hikashop_cart_products adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="hikashop_cart_num_title title titlenum" align="center">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="hikashop_cart_image_title title" align="center">
					<?php echo JText::_( 'HIKA_IMAGE' );?>
				</th>
				<th class="hikashop_cart_name_title title" align="center">
					<?php echo JText::_('CART_PRODUCT_NAME'); ?>
				</th>
				<th class="hikashop_cart_quantity_title title" align="center">
					<?php echo JText::_('PRODUCT_QUANTITY'); ?>
				</th>
				<th class="hikashop_cart_price_title title" align="center">
					<?php echo JText::_('PRODUCT_PRICE'); ?>
				</th>
				<th class="hikashop_cart_status_title title" align="center">
					<?php echo JText::_('HIKASHOP_CHECKOUT_STATUS'); ?>
				</th>
				<?php if($tmpl != 'component' && hikashop_level(1) && (($this->config->get('enable_wishlist') && $cart_type == 'cart')||$cart_type == 'wishlist')){ ?>
				<th class="hikashop_cart_action_title title" align="center">
					<a  style="cursor: pointer;" onClick="checkAll();"><?php echo JText::_('HIKASHOP_ADD_TO'); ?></a>
				</th>
				<th class="hikashop_cart_delete_title title" align="center">
					<?php echo JText::_('HIKA_DELETE'); ?>
				</th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php
			$app = JFactory::getApplication();
			$config =& hikashop_config();
			$i = 1;
			$k = 1;
			$total_quantity = 0;
			if(!empty($this->rows)){
				$productClass = hikashop_get('class.product');
				foreach($this->rows as $cart){
					if(!@$cart->hide){
						$total_quantity += $cart->cart_product_quantity;
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
							}else{
								$stockText = "<span class='hikashop_red_color'>".JText::_('NOT_ENOUGH_STOCK')."</span>";
							}
							$inStock = 0;
						}
						if($k ==1)$k = 0;else $k =1;
						?>
						<tr class="hikashop_show_cart row<?php echo $k; if((int)$cart->cart_product_quantity == 0) echo " hika_wishlist_green";?>">
							<td data-title="<?php echo JText::_('HIKA_NUM'); ?>" align="center"><?php echo $i; ?></td>
							<td data-title="<?php echo JText::_('HIKA_IMAGE'); ?>" align="center">
							<?php
								$width = (int)$this->config->get('thumbnail_x');
								$height = (int)$this->config->get('thumbnail_y');
								if(isset($cart->images[0])){
									$image_options = array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'));
									$img = $this->image->getThumbnail(@$cart->images[0]->file_path, array('width' => $width, 'height' => $height), $image_options);
									if($img->success) {
										echo '<img class="hikashop_product_cart_image" title="'.$this->escape(@$cart->images[0]->file_description).'" alt="'.$this->escape(@$cart->images[0]->file_name).'" src="'.$img->url.'"/>';
									}
								}
							?>
							</td>
							<td data-title="<?php echo JText::_('CART_PRODUCT_NAME'); ?>" align="center">
								<a class="hikashop_no_print" href="<?php echo hikashop_completeLink('product&task=show&cid='.$cart->product_id.'&name='.$cart->alias.'&Itemid='.$Itemid); ?>">
								<?php
									if(!isset($cart->bought) || !$cart->bought){
										echo $cart->product_name;
									}else{
										echo JHTML::tooltip(implode('<br />',$cart->bought), JText::_('HIKA_BOUGHT_BY'), '',$cart->product_name);
									}
									if($this->params->get('show_code')){
										echo ' ('.$cart->product_code.')';
									}
								?>
								</a>
							</td>
							<td data-title="<?php echo JText::_('PRODUCT_QUANTITY'); ?>" align="center"><input id="hikashop_product_quantity_field_<?php echo $cart->product_id;?>" name="data[products][<?php echo $cart->product_id;?>][quantity]" class="hikashop_show_cart_quantity" type="text" value="<?php echo $cart->cart_product_quantity;?>"/></td>
							<td data-title="<?php echo JText::_('PRODUCT_PRICE'); ?>" align="center">
							<?php
								if(!isset($cart->prices[0])){
									$cart->prices[0] = new stdClass();
									$cart->prices[0]->price_value=0;
									$cart->prices[0]->price_value_with_tax=0;
									$cart->prices[0]->price_currency_id = hikashop_getCurrency();
								}

								if($this->params->get('show_cart_price','-1')=='-1'){
									$this->params->set('show_cart_price',$config->get('show_cart_price'));
								}
								if ($this->params->get('show_cart_price',1)) {
									if(empty($cart->prices[0]->price_value)){
										echo JText::_('FREE_PRICE');
									}else{
										if($config->get('price_with_tax')){
											echo $this->currencyHelper->format($cart->prices[0]->price_value_with_tax,$cart->prices[0]->price_currency_id);
										}
										if($config->get('price_with_tax')==2){
											echo JText::_('PRICE_BEFORE_TAX');
										}
										if($config->get('price_with_tax')==2||!$config->get('price_with_tax')){
											echo $this->currencyHelper->format($cart->prices[0]->price_value,$cart->prices[0]->price_currency_id);
										}
										if($config->get('price_with_tax')==2){
											echo JText::_('PRICE_AFTER_TAX');
										}
									}
								}
							?>
							</td>
							<td data-title="<?php echo JText::_('HIKASHOP_CHECKOUT_STATUS'); ?>" align="center"><?php echo $stockText;?></td>
							<?php if($tmpl != 'component'){
									if(hikashop_level(1) && (($this->config->get('enable_wishlist') && $cart_type == 'cart')||$cart_type == 'wishlist')){
								?>
							<td data-title="<?php echo JText::_('HIKASHOP_ADD_TO'); ?>"  align="center" class="hikashop_show_cart_add">
								<?php
								?>
								<input type="checkbox" name="data[products][<?php echo $cart->product_id;?>][checked]" value="1"/>
							</td>
							<?php }
								if($userCurrent == $this->cartVal->user_id){ ?>
							<td data-title="<?php echo JText::_('HIKA_DELETE'); ?>" align="center" class="hikashop_show_cart_delete">
								<a class="hikashop_no_print" href="<?php echo hikashop_completeLink('product&task=updatecart&stay=1&delete=1&quantity=0&cart_type='.$cart_type.'&cart_id='.$cart->cart_id.'&cart_product_id='.$cart->cart_product_id.'&Itemid='.$Itemid); ?>"  title="<?php echo JText::_('HIKA_DELETE'); ?>">
									<img src="<?php echo HIKASHOP_IMAGES . 'delete2.png';?>" border="0" alt="<?php echo JText::_('HIKA_DELETE'); ?>" />
								</a>
							</td>
						<?php
							}
						}
						?>
						</tr>
						<?php
						$i++;
					}
				}
				if($cart_type == 'wishlist') echo '<input type="hidden" name="add_to" value="cart"/>';
				else echo '<input type="hidden" name="add_to" value="wishlist"/>';
			}
			?>
		</tbody>
		<tfoot>
			<tr class="hika_show_cart_total">
				<td class="hika_show_cart_total_text">
					<?php echo JText::_('HIKASHOP_TOTAL'); ?>
				</td>
				<td></td>
				<td align="center" class="hika_show_cart_total_quantity">
					<?php echo $total_quantity; ?>
				</td>
				<td align="center" class="hika_show_cart_total_price">
					<?php
						if(empty($this->total->prices)) {
							$this->total->prices[0] = new stdClass();
							$this->total->prices[0]->price_value = 0;
							$this->total->prices[0]->price_value_with_tax = 0;
							$this->total->prices[0]->price_currency_id = hikashop_getCurrency();
						}
						if($config->get('price_with_tax')){
							echo $this->currencyHelper->format($this->total->prices[0]->price_value_with_tax,$this->total->prices[0]->price_currency_id);
						}
						if($config->get('price_with_tax')==2){
							echo JText::_('PRICE_BEFORE_TAX');
						}
						if($config->get('price_with_tax')==2||!$config->get('price_with_tax')){
							echo $this->currencyHelper->format($this->total->prices[0]->price_value,$this->total->prices[0]->price_currency_id);
						}
						if($config->get('price_with_tax')==2){
							echo JText::_('PRICE_AFTER_TAX');
						}
					?>
				</td>
				<?php if($tmpl != 'component'){ ?>
				<td></td>
				<?php if(hikashop_level(1) && (($this->config->get('enable_wishlist') && $cart_type == 'cart')||$cart_type == 'wishlist')){ ?>
				<td align="center">
					<?php
						echo $this->cart->displayButton($addText,'wishlist',$this->params,hikashop_completeLink('cart&task=convert&cart_type=cart&cart_id='.$cart_id.'&Itemid='.$Itemid),'document.getElementById(\'task\').value = \'addtocart\'; document.forms[\'hikashop_show_cart_form\'].submit(); return false;');
						if($cart_type == 'wishlist' && $config->get('show_compare',0) != 0 && $config->get('wishlist_to_compare',0) != 0)
							echo $this->cart->displayButton(JText::_('HIKASHOP_COMPARE_LIST'),'wishlist',$this->params,'','document.getElementById(\'task\').value = \'addtocart\'; document.getElementById(\'action\').value = \'compare\'; document.forms[\'hikashop_show_cart_form\'].submit(); return false;');
					?>
				</td>
				<?php } ?>
				<td></td>
				<?php } ?>
			</tr>
		</tfoot>
	</table>
<?php
		if($cart_type == 'cart' && $total_quantity > 0 && hikashop_level(1) && $this->config->get('enable_wishlist')&&$tmpl != 'component'){
			$this->params->set('cart_type','wishlist');
			echo $this->cart->displayButton(JText::_('CART_TO_WISHLIST'),'wishlist',$this->params,hikashop_completeLink('cart&task=convert&cart_type=cart&cart_id='.$cart_id.'&Itemid='.$Itemid),'window.location.href = \''.hikashop_completeLink('cart&task=convert&cart_type=cart&cart_id='.$cart_id.'&Itemid='.$Itemid).'\';return false;');
		}
	}else{
		echo "<div class='hikashop_not_authorized'>".JText::_('HIKASHOP_NOT_AUTHORIZED')."</div>";
	}
?>
	</div>
	<div class="clear_both"></div>
	<input type="hidden" id="task" name="task" value="savecart"/>
	<input type="hidden" name="cid" value=""/>
	<input type="hidden" name="cart_id" value="<?php echo $cart_id; ?>"/>
	<input type="hidden" name="from_id" value="<?php echo $cart_id; ?>"/>
	<input type="hidden" name="cart_type" value="<?php echo $cart_type; ?>"/>
	<input type="hidden" id="action" name="action" value=""/>
</form>
