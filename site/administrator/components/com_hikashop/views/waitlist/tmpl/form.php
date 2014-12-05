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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=waitlist" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
	<table class="admintable table" width="100%">
		<tr>
			<td class="key">
					<?php echo JText::_( 'HIKA_NAME' ); ?>
			</td>
			<td>
				<input type="text" size="40" name="data[waitlist][name]" value="<?php echo $this->escape(@$this->element->name); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'HIKA_EMAIL' ); ?>
			</td>
			<td>
				<input type="text" size="40" name="data[waitlist][email]" value="<?php echo $this->escape(@$this->element->email); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'PRODUCT' ); ?>
			</td>
			<td>
				<span id="product_id" >
					<?php echo (int)@$this->element->product_id.' '.@$this->element->product_name; ?>
					<input type="hidden" name="data[waitlist][product_id]" value="<?php echo @$this->element->product_id; ?>" />
				</span>
				<?php
					echo $this->popup->display(
							'<img src="'. HIKASHOP_IMAGES.'edit.png" alt="'.JText::_('PRODUCT').'"/>',
							'PRODUCT',
							 hikashop_completeLink("product&task=selectrelated&select_type=waitlist",true ),
							'product_link',
							760, 480, '', '', 'link'
						);
					?>
				<a href="#" onclick="document.getElementById('product_id').innerHTML='<input type=\'hidden\' name=\'data[waitlist][product_id]\' value=\'0\' />';return false;" >
					<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="delete"/>
				</a>
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'MENU' ); ?>
			</td>
			<td>
				<?php $menuType = hikashop_get('type.menus');
					echo $menuType->display('data[waitlist][product_item_id]',@$this->element->product_item_id);?>
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'DATE' ); ?>
			</td>
			<td>
				<?php echo JHTML::_('calendar', (@$this->element->date?hikashop_getDate(@$this->element->date,'%Y-%m-%d %H:%M'):''), 'data[waitlist][date]','date','%Y-%m-%d %H:%M',array('size'=>'20')); ?>
			</td>
		</tr>
	</table>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->waitlist_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="waitlist" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
