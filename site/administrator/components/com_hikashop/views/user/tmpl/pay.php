<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div style="text-align:center">
	<form action="index.php?option=com_hikashop&amp;ctrl=user" method="post"  name="adminForm" id="adminForm" >
	<?php
	echo JText::sprintf('PAY_X_TO_X',$this->currencyHelper->format($this->user->accumulated['currenttotal'],$this->user->user_currency_id),@$this->user->name.' ('.$this->user->user_partner_email.')');
	echo '<br/>';
	echo JText::sprintf('REDIRECTION_FOR_PAYMENT',$this->method);
	echo '<br/>';
	echo JHTML::_('hikaselect.booleanlist', "pay", '',0);
	echo '<br/>';
	?>
		<input type="button" name="pay_confirm" class="btn" value="<?php echo JText::_('PROCEED');?>" onclick="document.adminForm.submit();" />
		<input type="hidden" name="cid[]" value="<?php echo @$this->user->user_id; ?>" />
		<input type="hidden" name="tmpl" value="component" />
		<input type="hidden" name="pay_method" value="<?php echo $this->method;?>" />
		<input type="hidden" name="option" value="com_hikashop" />
		<input type="hidden" name="task" value="pay_confirm" />
		<input type="hidden" name="ctrl" value="user" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>
