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
<form action="<?php echo hikashop_completeLink('taxation');?>" method="post"  name="adminForm" id="adminForm">
	<center>
	<table class="admintable table">
		<tr>
			<td class="key">
					<?php echo JText::_( 'TAXATION_CATEGORY' ); ?>
			</td>
			<td>
				<?php echo $this->category->display( "data[taxation][category_namekey]" , @$this->element->category_namekey ); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'RATE' ); ?>
			</td>
			<td>
				<?php echo $this->ratesType->display( "data[taxation][tax_namekey]" , @$this->element->tax_namekey ); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'CUMULATIVE_TAX' ); ?>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "data[taxation][taxation_cumulative]" , '',@$this->element->taxation_cumulative	); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'POST_CODE' ); ?>
			</td>
			<td>
				<input type="text" name="data[taxation][taxation_post_code]" value="<?php echo @$this->element->taxation_post_code; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'ZONE' ); ?>
			</td>
			<td>
				<span id="zone_id" >
					<?php echo (int)@$this->element->zone_id.' '.@$this->element->zone_name_english; ?>
					<input type="hidden" name="data[taxation][zone_namekey]" value="<?php echo @$this->element->zone_namekey; ?>" />
				</span>
				<?php
					echo $this->popup->display(
						'<img src="'. HIKASHOP_IMAGES.'edit.png" alt="'.JText::_('HIKA_EDIT').'"/>',
						'ZONE',
						 hikashop_completeLink("zone&task=selectchildlisting&type=tax",true ),
						'zone_id_link',
						760, 480, '', '', 'link'
					);
				?>
				<a href="#" onclick="document.getElementById('zone_id').innerHTML='<input type=\'hidden\' name=\'data[taxation][zone_namekey]\' value=\'\'/>0 <?php echo $this->escape(JText::_('ZONE_NOT_FOUND'));?>';return false;" >
					<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="delete"/>
				</a>
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'CUSTOMER_TYPE' ); ?>
			</td>
			<td>
				<?php echo $this->taxType->display( "data[taxation][taxation_type]" , @$this->element->taxation_type ); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'START_DATE' ); ?>
			</td>
			<td>
				<?php echo JHTML::_('calendar', hikashop_getDate((@$this->element->taxation_date_start?@$this->element->taxation_date_start:''),'%Y-%m-%d %H:%M'), 'data[taxation][taxation_date_start]','taxation_date_start','%Y-%m-%d %H:%M',array('size'=>'20')); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'END_DATE' ); ?>
			</td>
			<td>
				<?php echo JHTML::_('calendar', hikashop_getDate((@$this->element->taxation_date_end?@$this->element->taxation_date_end:''),'%Y-%m-%d %H:%M'), 'data[taxation][taxation_date_end]','taxation_date_end','%Y-%m-%d %H:%M',array('size'=>'20')); ?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<fieldset class="adminform">
					<legend><?php echo JText::_('ACCESS_LEVEL'); ?></legend>
					<?php
					if(hikashop_level(2)){
						$acltype = hikashop_get('type.acl');
						echo $acltype->display('taxation_access',@$this->element->taxation_access,'taxation');
					}else{
						echo hikashop_getUpgradeLink('business');;
					} ?>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "data[taxation][taxation_published]" , '',@$this->element->taxation_published	); ?>
			</td>
		</tr>
	</table>
	</center>
	<div class="clr"></div>

	<input type="hidden" name="taxation_id" value="<?php echo @$this->element->taxation_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT;?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getString('ctrl');?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
