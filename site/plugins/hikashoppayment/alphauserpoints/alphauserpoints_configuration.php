<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?></table>
<fieldset>
	<legend><?php echo JText::sprintf( 'PAYMENT_OPTIONS', $this->element->payment_name); ?></legend>
	<table>
		<tr>
			<td class="key">
				<label for="data[payment][payment_params][value]"><?php
					echo JText::sprintf( 'RATES', $this->element->payment_name);
				?></label>
			</td>
			<td>
				<?php echo '1 '.JText::sprintf( 'POINTS' ).' '.JText::sprintf( 'EQUALS', $this->element->payment_name); ?>
				<input style="width: 50px;" type="text" name="data[payment][payment_params][value]" value="<?php echo @$this->element->payment_params->value; ?>" />
				<?php  echo $this->data['currency']->currency_code. ' ' .$this->data['currency']->currency_symbol; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[payment][payment_params][percent]"><?php
					echo JText::sprintf( 'GROUP_POINTS_BY', $this->element->payment_name);
				?></label>
			</td>
			<td>
				<input style="width: 50px;" type="text" name="data[payment][payment_params][grouppoints]" value="<?php echo @$this->element->payment_params->grouppoints; ?>" /> <?php echo JText::sprintf( 'POINTS' );?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[payment][payment_params][percent]"><?php
					echo JText::sprintf( 'MAXIMUM_POINTS', $this->element->payment_name);
				?></label>
			</td>
			<td>
				<input style="width: 50px;" type="text" name="data[payment][payment_params][maxpoints]" value="<?php echo @$this->element->payment_params->maxpoints; ?>" /> <?php echo JText::sprintf( 'POINTS' );?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[payment][payment_params][allowshipping]"><?php
					echo JText::sprintf( 'SHIPPING', $this->element->payment_name);
				?></label>
			</td>
			<td><?php
				echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][allowshipping]" , '',@$this->element->payment_params->allowshipping);
			?></td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[payment][payment_params][partialpayment]"><?php
					echo JText::sprintf( 'ALLOW_PARTIAL_PAYMENT', $this->element->payment_name);
				?></label>
			</td>
			<td><?php
				echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][partialpayment]" , 'onclick="setVisible(this.value);"',@$this->element->payment_params->partialpayment	);
			?></td>
		</tr>
<?php
$display = '';
if(empty($this->element->payment_params->partialpayment)){
	$display = ' style="display:none;"';
}
?>
		<tr>
			<td class="key">
				<div id="opt"<?php echo $display?>>
					<label for="data[payment][payment_params][percentmax]"><?php
						echo JText::sprintf( 'MAXIMUM_ORDER_PERCENT', $this->element->payment_name);
					?></label>
				</div>
			</td>
			<td>
				<div id="opt2"<?php echo $display?>>
					<input style="width: 50px;" type="text" name="data[payment][payment_params][percentmax]" value="<?php echo @$this->element->payment_params->percentmax; ?>" />%
				</div>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[payment][payment_params][percent]"><?php
					echo JText::sprintf( 'MINIMUM_ORDER_PERCENT', $this->element->payment_name);
				?></label>
			</td>
			<td>
				<input style="width: 50px;" type="text" name="data[payment][payment_params][percent]" value="<?php echo @$this->element->payment_params->percent; ?>" />%
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[payment][payment_params][minimumcost]"><?php
					echo JText::_( 'MINIMUM_COST' );
				?></label>
			</td>
			<td>
				<div id="opt2" style="display:block;">
					<input style="width: 50px;" type="text" name="data[payment][payment_params][minimumcost]" value="<?php echo @$this->element->payment_params->minimumcost; ?>" />
					<?php  echo $this->data['currency']->currency_code. ' ' .$this->data['currency']->currency_symbol; ?>
				</div>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[payment][payment_params][givebackpoints]"><?php
					echo JText::sprintf( 'GIVE_BACK_POINTS_IF_CANCELLED', $this->element->payment_name);
				?></label>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][givebackpoints]" , '',@$this->element->payment_params->givebackpoints ); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[payment][payment_params][valid_order_status]"><?php
					echo JText::sprintf( 'GIVE_POINTS_ON_STATUSES', $this->element->payment_name);
				?></label>
			</td>
			<td>
				<input id="aup_valid_order_statuses" name="data[payment][payment_params][valid_order_status]" value="<?php echo @$this->element->payment_params->valid_order_status; ?>" />
				<a id="link_aup_valid_order_statuses" class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php
					echo hikashop_completeLink('category&task=selectstatus&control=aup_valid_order_statuses&values='. @$this->element->payment_params->valid_order_status, true);
				?>">
					<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit"/>
				</a>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[payment][payment_params][virtualpoints]"><?php
					echo JText::sprintf( 'USE_VIRTUAL_POINTS', $this->element->payment_name);
				?></label>
			</td>
			<td><?php
				echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][virtualpoints]" , '',@$this->element->payment_params->virtualpoints );
			?></td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[payment][payment_params][virtual_coupon]"><?php
					echo JText::sprintf( 'USE_VIRTUAL_COUPON', $this->element->payment_name);
				?></label>
			</td>
			<td><?php
				echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][virtual_coupon]" , '',@$this->element->payment_params->virtual_coupon );
			?></td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[payment][payment_params][grouppoints_warning_lvl]"><?php
					echo JText::sprintf( 'GROUP_POINTS_WARNING_LEVEL', $this->element->payment_name);
				?></label>
			</td>
			<td>
				<input style="width: 50px;" type="text" name="data[payment][payment_params][grouppoints_warning_lvl]" value="<?php echo @$this->element->payment_params->grouppoints_warning_lvl; ?>" /> <?php echo JText::sprintf( 'POINTS' );?>
			</td>
		</tr>
	</table>
</fieldset>
<fieldset>
	<legend>
		<span style="color:#4ba20b;"><?php echo JText::_('BASIC_POINTS_RULES'); ?></span>
	</legend>
	<table>
	<tr>
		<td class="key">
			<label for="data[payment][payment_params][currency_rate]"><?php
				echo JText::_( 'RATES' );
			?></label>
		</td>
		<td>
			<input style="width: 50px; background-color:#e8f9db;" type="text" name="data[payment][payment_params][currency_rate]" value="<?php echo @$this->element->payment_params->currency_rate; ?>" />
			<?php  echo $this->data['currency']->currency_code. ' ' .$this->data['currency']->currency_symbol; ?>
			<?php echo JText::sprintf( 'EQUALS', $this->element->payment_name);
			echo '1 '.JText::sprintf( 'POINTS'); ?>
	</tr>
	<tr>
		<td class="key">
			<label for="data[payment][payment_params][productpoints]"><?php
				echo JText::_( 'PRODUCT_POINTS' );
			?></label>
		</td>
		<td>
			<input style="width: 50px; background-color:#e8f9db;" type="text" name="data[payment][payment_params][productpoints]" value="<?php echo @$this->element->payment_params->productpoints; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[payment][payment_params][limitetype]"><?php
				echo JText::sprintf( 'LIMITE_POINTS_BY_TYPE', $this->element->payment_name);
			?></label>
		</td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][limitetype]" , '',@$this->element->payment_params->limitetype	);
		?></td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[payment][payment_params][shippingpoints]"><?php
				echo JText::sprintf( 'EARN_POINTS_ON_SHIPPING', $this->element->payment_name);
			?></label>
		</td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][shippingpoints]" , '',@$this->element->payment_params->shippingpoints	);
		?></td>
	</tr>
	</table>
</fieldset>
<fieldset>
	<legend><span style="color:#4ba20b;"><?php echo JText::_( 'CATEGORIES_POINTS' ); ?></span></legend>
	<div style="text-align:right;">
		<a class="modal" id="hikashop_cat_popup" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("product&task=selectcategory&control=plugin",true ); ?>">
			<button class="btn" type="button" onclick="return false">
				<img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('ADD');?>
			</button>
		</a>
	</div>
	<br/>
	<table class="adminlist table table-striped" cellpadding="1" width="100%">
		<thead>
			<tr>
				<th class="title"><?php
					echo JText::_('HIKA_NAME');
				?></th>
				<th class="title titletoggle"><?php
					echo JText::_('POINTS');
				?></th>
				<th class="title"><?php
					echo JText::_('ID');
				?></th>
			</tr>
		</thead>
		<tbody id="category_listing">
<?php
	if(!empty($this->data['categories'])){
		$k = 0;
		for($i = 0,$a = count($this->data['categories']);$i<$a;$i++){
			$row =& $this->data['categories'][$i];
			if(!empty($row->category_id)){
?>
			<tr id="category_<?php echo $row->category_id;?>">
				<td>
					<a href="<?php echo hikashop_completeLink('category&task=edit&cid='.$row->category_id); ?>"><?php echo $row->category_name; ?></a>
				</td>
				<td align="center">
					<input style="width: 50px; background-color:#e8f9db;" type="text" name="category_points[<?php echo $row->category_id;?>]" id="category_points[<?php echo $row->category_id;?>]" value="<?php echo (int)@$row->category_points; ?>" />
				</td>
				<td width="1%" align="center">
					<?php echo $row->category_id; ?>
					<div id="category_div_<?php echo $row->category_id;?>">
						<input style="width: 50px; background-color:#e8f9db;" type="hidden" name="category[<?php echo $row->category_id;?>]" id="category[<?php echo $row->category_id;?>]" value="<?php echo $row->category_id;?>"/>
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
	<br/>
	<table>
		<tr>
			<td class="key">
				<label for="data[payment][payment_params][limitecategory]"><?php
					echo JText::sprintf( 'LIMITE_POINTS_BY_CATEGORY', $this->element->payment_name);
				?></label>
			</td>
			<td><?php
				echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][limitecategory]" , '',@$this->element->payment_params->limitecategory);
			?></td>
		</tr>
	</table>
</fieldset>
<fieldset>
	<legend><span style="color:#4ba20b;"><?php echo JText::_( 'GROUPS_POINTS' ); ?></span></legend>
<?php
	if(hikashop_level(2)){?>
		<table>
			<?php foreach($this->data['groups'] as $group){?>
			<tr>
				<td>
					<label for="groups[<?php echo $group->value; ?>]"><?php echo $group->text;?></label>
				</td>
				<td>
					<input style="width: 50px; background-color:#e8f9db;" type="text" name="groups[<?php echo $group->value; ?>]" value="<?php echo (int)@$group->points; ?>" />
				</td>
			</tr>
			<?php }?>
		</table>
		<br/>
		<table>
			<tr>
				<td class="key">
					<label for="data[payment][payment_params][limitegroup]"><?php
						echo JText::sprintf( 'LIMITE_POINTS_BY_GROUP', $this->element->payment_name);
					?></label>
				</td>
				<td><?php
					echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][limitegroup]" , '',@$this->element->payment_params->limitegroup);
				?></td>
			</tr>
		</table>
<?php
	}else{
		echo hikashop_getUpgradeLink('business');;
	} ?>
</fieldset>
<table>
