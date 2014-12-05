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

defined('_JEXEC') or die('Restricted access');

JFactory::getLanguage()->load('plg_'.strtolower($this->element->payment_type).'_hs', JPATH_ADMINISTRATOR, 'ru-RU', true);
?>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][url]">
			<?php echo JText::_('URL'); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][url]" value="<?php echo @$this->element->payment_params->url; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][email]">
			<?php echo JText::_('HIKA_EMAIL'); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][email]" value="<?php echo @$this->element->payment_params->email; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][merchant_id]">
			<?php echo JText::_('MERCHANT_ID'); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][merchant_id]" value="<?php echo @$this->element->payment_params->merchant_id; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][secret_word]">
			<?php echo JText::_('SECRET_KEY'); ?>
		</label>
	</td>
	<td>
		<input type="password" name="data[payment][payment_params][secret_word]" value="<?php echo @$this->element->payment_params->secret_word; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][notification]">
			<?php echo JText::sprintf('ALLOW_NOTIFICATIONS_FROM_X', $this->element->payment_name); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('select.booleanlist', 'data[payment][payment_params][notification]', '', @$this->element->payment_params->notification); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][debug]">
			<?php echo JText::_('DEBUG'); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('select.booleanlist', 'data[payment][payment_params][debug]', '', @$this->element->payment_params->debug); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][logo_url]">
			<?php echo JText::_('LOGO'); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][logo_url]" value="<?php echo @$this->element->payment_params->logo_url; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][invalid_status]">
			<?php echo JText::_('INVALID_STATUS'); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display('data[payment][payment_params][invalid_status]', @$this->element->payment_params->invalid_status); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][pending_status]">
			<?php echo JText::_('PENDING_STATUS'); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display('data[payment][payment_params][pending_status]', @$this->element->payment_params->pending_status); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][verified_status]">
			<?php echo JText::_('VERIFIED_STATUS'); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display('data[payment][payment_params][verified_status]', @$this->element->payment_params->verified_status); ?>
	</td>
</tr>
