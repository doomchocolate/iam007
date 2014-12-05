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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=badge" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
	<?php
		$this->badge_name = "data[badge][badge_name]";
		$this->badge_position = "data[badge][badge_position]";

	?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-badge">
	<table style="width:100%;margin:auto;">
		<tr>
			<td valign="top">
<?php } else { ?>
<div id="page-badge" class="row-fluid">
	<div class="span6">
<?php } ?>
				<table class="admintable table" style="margin:auto">
					<tr>
						<td class="key">
								<?php echo JText::_( 'HIKA_NAME' ); ?>
						</td>
						<td>
							<input type="text" size="40" name="data[badge][badge_name]" value="<?php echo $this->escape(@$this->element->badge_name); ?>" />
						</td>
					</tr>
					<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
							</td>
							<td>
									<?php echo JHTML::_('hikaselect.booleanlist', "data[badge][badge_published]" , '',@$this->element->badge_published);?>
							</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'START_DATE' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('calendar', (@$this->element->badge_start?hikashop_getDate(@$this->element->badge_start,'%Y-%m-%d %H:%M'):''), 'data[badge][badge_start]','badge_start','%Y-%m-%d %H:%M',array('size'=>'20')); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
								<?php echo JText::_( 'END_DATE' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('calendar', (@$this->element->badge_end?hikashop_getDate(@$this->element->badge_end,'%Y-%m-%d %H:%M'):''), 'data[badge][badge_end]','badge_end','%Y-%m-%d %H:%M',array('size'=>'20')); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'PRODUCT_QUANTITY' ); ?>
						</td>
						<td>
							<input type="text" name="data[badge][badge_quantity]" value="<?php echo @$this->element->badge_quantity; ?>" />
						</td>
					</tr>
					<tr>
						<td class="key">
								<?php echo JText::_( 'CATEGORY' ); ?>
						</td>
						<td>
							<span id="changeParent" >
								<?php echo (int)@$this->element->badge_category_id.' '.@$this->element->category_name; ?>
							</span>
								<input type="hidden" id="categoryselectparentlisting" name="data[badge][badge_category_id]" value="<?php echo @$this->element->badge_category_id; ?>" />
							<?php
								echo $this->popup->display(
									'<img src="'. HIKASHOP_IMAGES.'edit.png" alt="'.JText::_('CATEGORY').'"/>',
									'CATEGORY',
									hikashop_completeLink("category&task=selectparentlisting&control=category",true ),
									'category_link',
									760, 480, '', '', 'link'
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
							<?php echo JHTML::_('hikaselect.booleanlist', "data[badge][badge_category_childs]" , '',@$this->element->badge_category_childs	); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
								<?php echo JText::_( 'DISCOUNT' ); ?>
						</td>
						<td>
							<span id="changeDiscount" >
								<?php echo (int)@$this->element->badge_discount_id.' '.@$this->element->discount_code; ?>
							</span>
								<input type="hidden" id="discountselectparentlisting" name="data[badge][badge_discount_id]" value="<?php echo @$this->element->badge_discount_id; ?>" />
							<?php
								echo $this->popup->display(
									'<img src="'. HIKASHOP_IMAGES.'edit.png" alt="'.JText::_('DISCOUNT').'"/>',
									'DISCOUNT',
									 hikashop_completeLink("discount&task=select_coupon&badge=true",true ),
									'discount_link',
									760, 480, '', '', 'link'
								);
							?>
							<a href="#" onclick="document.getElementById('changeDiscount').innerHTML='0 <?php echo $this->escape(JText::_('DISCOUNT_NOT_FOUND'));?>'; document.getElementById('discountselectparentlisting').value='0';return false;" >
								<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="delete"/>
							</a>
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'URL' ); ?>
						</td>
						<td>
							<input type="text" name="data[badge][badge_url]" value="<?php echo @$this->element->badge_url; ?>" />
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
				<table class="admintable table" margin="auto">
						<tr>
							<td class="key">
								<?php echo JText::_( 'HIKA_IMAGES' ); ?>
							</td>
							<td>
								<?php echo $this->image->display(@$this->element->badge_image,true,$this->escape(@$this->element->badge_image), '' , '' , 100, 100); ?>
								<input type="file" name="files" size="30" />
								<?php echo JText::sprintf('MAX_UPLOAD',(hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize')); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'KEEP_SIZE' ); ?>
							</td>
							<td>
									<?php echo JHTML::_('hikaselect.booleanlist', "data[badge][badge_keep_size]" , 'onchange="hikashopSizeUpdate(this.value);"',@$this->element->badge_keep_size);?>
							</td>
						</tr>
						<tr id="field_size">
							<td class="key">
									<?php echo JText::_( 'FIELD_SIZE' ); ?>
							</td>
							<td>
								<?php if(!isset($this->element->badge_size))$this->element->badge_size=30;?>
								<input type="text" size="2" name="data[badge][badge_size]" value="<?php echo $this->escape($this->element->badge_size);?>" />
							<?php echo JText::_( '%' );?>

							</td>
						</tr>
						<tr>
						<td class="key">
								<?php echo JText::_( 'POSITION' );?>
						</td>
						<td>
								<?php echo $this->badge->display("data[badge][badge_position]",@$this->element->badge_position);?>
						</td>
					</tr>
					<tr>
						<td class="key">
								<?php echo JText::_( 'VERTICAL_DISTANCE' );?>
						</td>
						<td>
								<?php if(!isset($this->element->badge_vertical_distance))$this->element->badge_vertical_distance=0;?>
								<input type="text" size="2" name="data[badge][badge_vertical_distance]" value="<?php echo $this->escape($this->element->badge_vertical_distance);?>" />
							<?php echo JText::_( 'px' );?>

							</td>
					</tr>
					<tr>
						<td class="key">
								<?php echo JText::_( 'HORIZONTAL_DISTANCE' );?>
						</td>
						<td>
								<?php if(!isset($this->element->badge_horizontal_distance))$this->element->badge_horizontal_distance=0;?>
								<input type="text" size="2" name="data[badge][badge_horizontal_distance]" value="<?php echo $this->escape($this->element->badge_horizontal_distance);?>" />
							<?php echo JText::_( 'px' );?>

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

	<input type="hidden" name="cid[]" value="<?php echo @$this->element->badge_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="badge" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
