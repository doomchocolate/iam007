<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_product_contact_<?php echo JRequest::getInt('cid');?>_page" class="hikashop_product_contact_page">
	<fieldset>
		<div class="" style="float:left">
			<h1><?php
if(!empty($this->product->images)) {
	$image = reset($this->product->images);
	$img = $this->imageHelper->getThumbnail($image->file_path, array(50,50), array('default' => true), true);
	if($img->success) {
		echo '<img src="'.$img->url.'" alt="" style="vertical-align:middle"/> ';
	}
}
	echo $this->product->product_name; ?></h1>
		</div>
		<div class="toolbar" id="toolbar" style="float: right;">
			<button class="btn" type="button" onclick="checkFields();"><img src="<?php echo HIKASHOP_IMAGES; ?>ok.png" alt=""/><?php echo JText::_('OK'); ?></button>
<?php if(JRequest::getCmd('tmpl', '') != 'component') { ?>
			<button class="btn" type="button" onclick="history.back();"><img src="<?php echo HIKASHOP_IMAGES; ?>cancel.png" alt=""/><?php echo JText::_('HIKA_CANCEL'); ?></button>
<?php } ?>
		</div>
		<div style="clear:both"></div>
	</fieldset>
<?php
	$formData = JRequest::getVar('formData','');
	if(isset($this->element->name) && !isset($formData->name)){
		$formData->name = $this->element->name;
	}
	if(isset($this->element->email) && !isset($formData->email)){
		$formData->email = $this->element->email;
	}
?>
	<form action="<?php echo hikashop_completeLink('product'); ?>" id="hikashop_contact_form" name="hikashop_contact_form" method="post">
		<dl>
			<dt><label for="data[contact][name]"><?php echo JText::_( 'HIKA_USER_NAME' ); ?></label></dt>
			<dd>
				<input id="hikashop_contact_name" type="text" name="data[contact][name]" size="40" value="<?php echo $this->escape(@$formData->name);?>" />
			</dd>
			<dt><label for="data[contact][email]"><?php echo JText::_( 'HIKA_EMAIL' ); ?></label></dt>
			<dd>
				<input id="hikashop_contact_email" type="text" name="data[contact][email]" size="40" value="<?php echo $this->escape(@$formData->email);?>" />
			</dd>
			<dt><label for="data[contact][altbody]"><?php echo JText::_( 'ADDITIONAL_INFORMATION' ); ?></label></dt>
			<dd>
<textarea id="hikashop_contact_altbody" cols="60" rows="10" name="data[contact][altbody]" style="width:100%;">
<?php if(isset($formData->altbody)) echo $formData->altbody; ?>
</textarea>
			</dd>
		</dl>
		<input type="hidden" name="data[contact][product_id]" value="<?php echo JRequest::getInt('cid');?>" />
		<input type="hidden" name="cid" value="<?php echo JRequest::getInt('cid');?>" />
		<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="ctrl" value="product" />
		<input type="hidden" name="redirect_url" value="<?php $redirect_url = JRequest::getString('redirect_url', ''); echo $this->escape($redirect_url); ?>" />
<?php if(JRequest::getVar('tmpl', '') == 'component') { ?>
		<input type="hidden" name="tmpl" value="component" />
<?php } ?>
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>
