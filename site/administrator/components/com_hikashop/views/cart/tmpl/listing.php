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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=cart&amp;cart_type=<?php echo JRequest::getString('cart_type','cart');?>" method="post" name="adminForm" id="adminForm">
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_( 'FILTER' ); ?>:
				<input type="hidden" id="backend_listing_vote" value="both"/>
				<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" onchange="document.adminForm.submit();" />
				<button class="btn" onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
				<button class="btn" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
			</td>
		</tr>
	</table>
	<table id="hikashop_cart_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title title_product_id">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'a.cart_id', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title title_cart_user_id">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_USERNAME'), 'a.cart_user_id', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title title_cart_current">
					<?php echo JHTML::_('grid.sort', JText::_('SHOW_DEFAULT'), 'a.cart_current', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title title_cart_quantity">
					<?php  echo JText::_('PRODUCT_QUANTITY'); ?>
				</th>
				<th class="title title_cart_total">
					<?php echo JText::_('CART_PRODUCT_TOTAL_PRICE'); ?>
				</th>
				<th class="title title_cart_date">
					<?php echo JHTML::_('grid.sort', JText::_('DATE'), 'a.cart_created', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title title_cart_action">
					<?php echo JText::_('HIKA_ACTION'); ?>
				</th>
				<th class="title title_cart_id">
					<?php echo JHTML::_('grid.sort', JText::_('ID'), 'a.cart_id', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
			$config =& hikashop_config();
			$cart_type = JRequest::getString('cart_type','cart');
			$i = 0;
			$k = 1;
			foreach($this->carts as $cart){
				if($k ==1)$k = 0;else $k =1;
				if($cart->cart_id != null){
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td align="center">
						<?php echo $this->pagination->getRowOffset($i); ?>
						</td>
						<td align="center">
							<?php echo JHTML::_('grid.id', $i, $cart->cart_id ); ?>
						</td>
						<td align="center">
							<?php
								if(hikashop_isAllowed($config->get('acl_wishlist_manage','all'))){
									echo "<a href=".hikashop_completeLink('cart&task=edit&cart_type='.$cart_type.'&cart_id='.$cart->cart_id.'&cid[]='.$cart->cart_id,false,true).">";echo $cart->cart_name."</a>";
								}else{
									echo $cart->cart_name;
								}
							?>
						</td>
						<td align="center">
							<?php
							$user = null;
							if($cart->user_id != 0){
								$userClass = hikashop_get('class.user');
								$user_id = $userClass->getID($cart->user_id);
								if(!empty($user_id))
									$user = $userClass->get($user_id);
								else
									$user = $userClass->get($cart->user_id);
								if(empty($user)){
									$user_id = $userClass->getID($cart->user_id);
									$user = $userClass->get($user_id);
								}
								if(!empty($user->username)){
									echo $user->name.' ( '.$user->username.' )</a><br/>';
								}
								$target = '';
								if($this->popup)
									$target = '" target="_top';
								$url = hikashop_completeLink('user&task=edit&cid[]='.$cart->user_id);
								$config =& hikashop_config();
								if(hikashop_isAllowed($config->get('acl_user_manage','all'))) echo $user->user_email.'<a href="'.$url.$target.'"><img src="'.HIKASHOP_IMAGES.'edit.png" alt="edit"/></a>';
							}else{
								echo JText::_('NO_REGISTRATION');
							}
							?>
						</td>
						<td align="center">
						<?php if($cart->cart_current == 1){	?>
								<a href="<?php echo hikashop_completeLink('cart&task=edit&cart_type='.$cart_type.'&cid[]='.$cart->cart_id.'&user_id='.$cart->user_id);?>">
									<img src="../media/com_hikashop/images/icon-16-default.png" alt="current"/>
								</a>
						<?php } ?>
						</td>
						<td align="center">
							<?php
								echo $cart->quantity;
							?>
						</td>
						<td align="center">
							<span class='hikashop_product_price_full hikashop_product_price'>
							<?php
								echo $this->currencyHelper->format($cart->price,$cart->currency);
							?>
							</span>
						</td>
						<td align="center">
							<?php
								echo hikashop_getDate($cart->cart_modified);
							?>
						</td>
						<td align="center">
							<?php if($this->manage){ ?>
							<a href="<?php echo hikashop_completeLink('cart&task=edit&cart_type='.$cart_type.'&cid[]='.$cart->cart_id);?>">
								<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/>
							</a>

							<?php } ?>
						</td>
						<td width="1%" align="center">
							<?php echo $cart->cart_id; ?>
						</td>
					</tr>
				<?php
					$i++;
				}
			}
			?>
		</tbody>
	</table>
	<input type="hidden" name="cart_type" value="<?php echo JRequest::getString('cart_type', 'cart'); ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
