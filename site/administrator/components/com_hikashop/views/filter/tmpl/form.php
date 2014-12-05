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
<form action="index.php?option=com_hikashop&amp;ctrl=filter" method="post"  name="adminForm" id="adminForm" >
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-filter">
	<table style="width:100%">
		<tr>
			<td valign="top" width="50%">
<?php } else { ?>
<div id="page-filter" class="row-fluid">
	<div class="span6">
<?php } ?>
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'MAIN_INFORMATION' ); ?></legend>
				<table class="paramlist admintable table">
					<tr>
						<td class="key">
								<?php echo JText::_( 'HIKA_NAME' ); ?>
						</td>
						<td>
							<input type="text" name="data[filter][filter_name]" id="name" class="inputbox" size="40" value="<?php echo $this->escape(@$this->element->filter_name); ?>" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'HIKA_TYPE' ); ?>
						</td>
						<td>
							<?php
								echo $this->filterType->display('data[filter][filter_type]',@$this->element->filter_type);
							?>
						</td>
					</tr>
					<tr>
						<td class="key">
								<?php echo JText::_( 'CATEGORY' ); ?>
						</td>
						<td>
							<span id="changeParent" >
								<?php echo (int)@$this->element->filter_category_id.' '.@$this->element->filter_category_name; ?>

							</span>
								<input type="hidden" id="categoryselectparentlisting" name="data[filter][filter_category_id]" value="<?php echo @$this->element->filter_category_id; ?>" />
							<?php
							echo $this->popup->display(
									'<img src="'. HIKASHOP_IMAGES.'edit.png" alt="'.JText::_('CATEGORY').'"/>',
									'CATEGORY',
									hikashop_completeLink("category&task=selectparentlisting&control=category",true ),
									'category_link',
									860, 480, '', '', 'link'
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
							<?php echo JHTML::_('hikaselect.booleanlist', "data[filter][filter_category_childs]" , '',@$this->element->filter_category_childs	); ?>
						</td>
					</tr>
				</table>
			</fieldset>
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'EXTRA_INFORMATION' ); ?></legend>
				<table class="paramlist admintable table">
					<tr>
						<td class="key">
								<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "data[filter][filter_published]" , '',@$this->element->filter_published); ?>
						</td>
					</tr>
					<tr id="applyOnClick">
						<td class="key">
								<?php echo JText::_( 'SUBMIT_ON_CLICK' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "data[filter][filter_direct_application]" , '',@$this->element->filter_direct_application); ?>
						</td>
					</tr>
					<tr id="filterHeight">
						<td class="key">
								<?php echo JText::_( 'PRODUCT_HEIGHT' ); ?>
						</td>
						<td>
							<input size="10" type="text" name="data[filter][filter_height]" id="name" class="inputbox" size="40" value="<?php echo $this->escape(@$this->element->filter_height); ?>" /> px
						</td>
					</tr>
					<tr>
						<td class="key">
								<?php echo JText::_( 'DELETABLE_FILTER' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "data[filter][filter_deletable]" , '',@$this->element->filter_deletable); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
								<?php echo JText::_( 'DYNAMIC_DISPLAY' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "data[filter][filter_dynamic]" , '',@$this->element->filter_dynamic); ?>
						</td>
					</tr>
				</table>
				</fieldset>
				<fieldset class="adminform">
				<legend><?php echo JText::_('ACCESS_LEVEL'); ?></legend>
				<?php
				if(hikashop_level(2)){
					$acltype = hikashop_get('type.acl');
					echo $acltype->display('filter_access',@$this->element->filter_access,'filter');
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
				<legend><?php echo JText::_( 'OPTIONS' ); ?></legend>
					<?php if(1){
							echo $this->loadTemplate('options');
					}?>
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
	<div class="clr"></div>

	<input type="hidden" name="cid[]" value="<?php echo @$this->element->filter_id; ?>" />
	<input type="hidden" name="option" value="com_hikashop" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="filter" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
