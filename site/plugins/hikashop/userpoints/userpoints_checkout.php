<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_userpoints_status">
	<fieldset>
		<legend><?php echo JText::_('POINTS'); ?></legend>
		<span class="hikashop_userpoints_status_value"><?php
			if($points > 0) {
				echo JText::sprintf('USERPOINTS_HAVE_X_POINTS', $points);
			} else {
				echo JText::_('USERPOINTS_NO_POINTS');
			}
		?></span>
<?php
	if(!empty($consume)) {
?>
			<br/><span class="hikashop_userpoints_status_user"><?php echo JText::sprintf('USERPOINTS_USER_FOR_DISCOUNT', $consume['points'], $discount); ?></span>
<?php if(!empty($this->plugin_options['ask_no_coupon'])) { ?>
			<br/><span class="hikashop_userpoints_status_question"><span><?php echo JText::_('USERPOINTS_USE_DISCOUNT_QUESTION');?></span> <?php echo JHTML::_('hikaselect.booleanlist', 'userpoints_use_coupon', 'onchange="this.form.submit();"', $use_coupon); ?></span>
<?php }
	}
?>
	</fieldset>
</div>
