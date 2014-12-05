<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(empty($mode_noform)) { ?>
<div class="hikashop_currency_module" id="hikashop_currency_module">
	<form action="<?php echo hikashop_completeLink('currency&task=update'); ?>" method="post" name="hikashop_currency_form">
		<input type="hidden" name="return_url" value="<?php echo urlencode($redirectUrl); ?>" />
		<?php echo $currency->display('hikashopcurrency',hikashop_getCurrency(),'onchange="document.hikashop_currency_form.submit();"'); ?>
	</form>
</div>
<?php } else {
	echo '<div class="hikashop_currency_module" id="hikashop_currency_module">' . $currency->display(null, hikashop_getCurrency(), 'id="hikashopcurrency" onchange="window.localPage.switchCurrency(this);"') . '</div>';
	$doc = JFactory::getDocument();
	$doc->addScriptDeclaration('
if(!window.localPage) window.localPage = {};
window.localPage.switchCurrency = function(el) {
	var url = "'.hikashop_completeLink('currency&task=update&hikashopcurrency={ID}').'";
	url += ((url.indexOf("?") !== false) ? "?" : "&") + "return_url='.urlencode($redirectUrl).'";
	window.location = url.replace("{ID}", el.value);
};
');
}
