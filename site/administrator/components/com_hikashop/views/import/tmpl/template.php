<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset class="adminform">
<legend><?php echo JText::_( 'PRODUCT_TEMPLATE' ); ?></legend>
	<div id="template_product">
	<?php echo JText::_('NO_PRODUCT_TEMPLATE'); ?>
	</div>
	<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("product&task=selectrelated&select_type=import",true ); ?>">
		<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit"/>
	</a>
	<a href="#" onclick="document.getElementById('template_product').innerHTML='<?php echo JText::_('NO_PRODUCT_TEMPLATE',true);?>';return false;" >
		<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="delete"/>
	</a>
</fieldset>
