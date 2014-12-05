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
<div>
	<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=limit" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
	<div id="hikashop_limit_form">
		<table width="700px" class="table" style="margin:auto">
			<tr>
				<td valign="top">
	<?php } else { ?>
	<div id="hikashop_limit_form" class="row-fluid">
		<div class="span6">
	<?php } ?>
					<table class="admintable table">
						<tr>
							<td class="key">
									<?php echo JText::_( 'PERIOD' ); ?>
							</td>
							<td>
								<?php
								$values = array();
								$values[] = JHTML::_('select.option', 'cart', JText::_('Cart'));
								$values[] = JHTML::_('select.option', 'daily', JText::_('Daily'));
								$values[] = JHTML::_('select.option', 'weekly', JText::_('Weekly'));
								$values[] = JHTML::_('select.option', 'monthly', JText::_('Monthly'));
								$values[] = JHTML::_('select.option', 'quarterly', JText::_('Quarterly'));
								$values[] = JHTML::_('select.option', 'yearly', JText::_('Yearly'));
								$values[] = JHTML::_('select.option', 'forever', JText::_('Forever'));
								echo JHTML::_('select.genericlist', $values, "data[limit][limit_periodicity]" , 'class="inputbox" size="1"', 'value', 'text', @$this->element->limit_periodicity );
								?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_TYPE' ); ?>
							</td>
							<td>
								<?php echo $this->type->display('data[limit][limit_type]',@$this->element->limit_type,true); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'VALUE' ); ?>
							</td>
							<td>
								<input type="text" name="data[limit][limit_value]" value="<?php echo $this->escape(@$this->element->limit_value); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'WEIGHT_SYMBOLS' ); ?>
							</td>
							<td>
								<?php echo $this->unit->display('data[limit][limit_unit]',@$this->element->limit_unit,true); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'CURRENCY' ); ?>
							</td>
							<td>
								<?php echo @$this->currency->display('data[limit][limit_currency_id]',@$this->element->limit_currency_id); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "data[limit][limit_published]" , '',@$this->element->limit_published	); ?>
							</td>
						</tr>
					</table>
	<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
				</td>
				<td valign="top">
	<?php } else { ?>
		</div>
		<div class="span6">
	<?php } ?>
					<table class="admintable table">
						<tr>
							<td class="key">
									<?php echo JText::_( 'START_DATE' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('calendar', (@$this->element->limit_start?hikashop_getDate(@$this->element->limit_start,'%Y-%m-%d %H:%M'):''), 'data[limit][limit_start]','limit_start','%Y-%m-%d %H:%M',array('size'=>'20')); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'END_DATE' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('calendar', (@$this->element->limit_end?hikashop_getDate(@$this->element->limit_end,'%Y-%m-%d %H:%M'):''), 'data[limit][limit_end]','limit_end','%Y-%m-%d %H:%M',array('size'=>'20')); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'PRODUCT' ); ?>
							</td>
							<td>
								<span id="product_id" >
									<?php echo (int)@$this->element->limit_product_id.' '.@$this->element->product_name; ?>
									<input type="hidden" name="data[limit][limit_product_id]" value="<?php echo @$this->element->limit_product_id; ?>" />
								</span>
								<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("product&task=selectrelated&select_type=limit",true ); ?>">
									<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit"/>
								</a>
								<a href="#" onclick="document.getElementById('product_id').innerHTML='<input type=\'hidden\' name=\'data[limit][limit_product_id]\' value=\'0\' />';return false;" >
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
									<?php echo (int)@$this->element->limit_category_id.' '.@$this->element->category_name; ?>
								</span>
								<input type="hidden" id="categoryselectparentlisting" name="data[limit][limit_category_id]" value="<?php echo @$this->element->limit_category_id; ?>" />
								<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("category&task=selectparentlisting&control=category",true ); ?>">
									<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit"/>
								</a>
								<a href="#" onclick="document.getElementById('changeParent').innerHTML='0 <?php echo $this->escape(JText::_('CATEGORY_NOT_FOUND'));?>'; document.getElementById('categoryselectparentlisting').value='0';return false;" >
									<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="delete"/>
								</a>
							</td>
						</tr>
						<tr>
							<td class="key" >
								<?php echo JText::_( 'ORDER_STATUS' );// only for orders ?>
							</td>
							<td>
								<?php echo $this->status->display('data[limit][limit_status][]',@$this->element->limit_status,' multiple="multiple" size="5"',false); ?>
							</td>
						</tr>
						<?php  ?>
						<tr>
							<td colspan="2">
								<fieldset class="adminform">
									<legend><?php echo JText::_('ACCESS_LEVEL'); ?></legend>
									<?php
									if(hikashop_level(2)){
										$acltype = hikashop_get('type.acl');
										echo $acltype->display('limit_access',@$this->element->limit_access,'limit');
									}else{
										echo hikashop_getUpgradeLink('business');;
									} ?>
								</fieldset>
							</td>
						</tr>
					</table>
	<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
				</td>
			</tr>
		</table>
	</div>
	<?php } else { ?>
		</div>
	</div>
	<?php } ?>
		<div class="clr"></div>
		<input type="hidden" name="cid[]" value="<?php echo @$this->element->limit_id; ?>" />
		<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="ctrl" value="limit" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>
