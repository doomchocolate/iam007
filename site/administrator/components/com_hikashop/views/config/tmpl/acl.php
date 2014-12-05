<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="page-acl">
<table class="admintable" cellspacing="1">
	<tr>
		<td class="key" >
			<?php echo JText::_('INHERIT_PARENT_GROUP_ACCESS'); ?>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', "config[inherit_parent_group_access]" , '', $this->config->get('inherit_parent_group_access')); ?>
		</td>
	</tr>
</table>
<br style="font-size:1px;" />
	<table class="admintable table" cellspacing="1">
		<?php
		$acltable = hikashop_get('type.acltable');
		$aclcats = array();
		$acltrans = array();
		$aclcats['badge'] = array('view','manage','delete');
		$aclcats['banner'] = array('view','manage','delete');
		$aclcats['category'] = array('view','manage','delete');
		$aclcats['characteristic'] = array('view','manage','delete');
		$acltrans['characteristic'] = 'characteristics';
		$aclcats['config'] = array('view','manage');
		$acltrans['config'] = 'hika_configuration';
		$aclcats['currency'] = array('view','manage','delete');
		$aclcats['dashboard'] = array('view','manage','delete');
		$acltrans['dashboard'] = 'hikashop_cpanel';
		$aclcats['discount'] = array('view','manage','delete');
		$aclcats['email'] = array('view','manage','delete');
		$aclcats['entry'] = array('view','manage','delete');
		$acltrans['entry'] = 'hikashop_entry';
		$aclcats['field'] = array('view','manage','delete');
		$aclcats['filter'] = array('view','manage','delete');
		$aclcats['forum'] = array('view');
		$aclcats['documentation'] = array('view');
		$acltrans['documentation'] = 'help';
		$aclcats['import'] = array('view');
		$aclcats['limit'] = array('view','manage','delete');
		$aclcats['massaction'] = array('view','manage','delete');
		$aclcats['menus'] = array('view','manage','delete');
		$aclcats['modules'] = array('view','manage','delete');
		$aclcats['order'] = array('view','manage','delete');
		$acltrans['order'] = 'hikashop_order';
		$aclcats['plugins'] = array('view','manage');
		$aclcats['product'] = array('view','manage','delete');
		$aclcats['report'] = array('view','manage', 'delete');
		$aclcats['taxation'] = array('view','manage','delete');
		$aclcats['update_about'] = array('view');
		$aclcats['user'] = array('view','manage','delete');
		$aclcats['view'] = array('view','manage','delete');
		$aclcats['vote'] = array('view','manage','delete');
		$aclcats['warehouse'] = array('view','manage','delete');
		$aclcats['wishlist'] = array('view','manage','delete');
		$config =& hikashop_config();
		if($config->get('product_waitlist')){
			$aclcats['waitlist'] = array('view','manage','delete');
		}
		$aclcats['zone'] = array('view','manage','delete');
		foreach($aclcats as $category => $actions){ ?>
		<tr>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<td width="185" class="key" valign="top">
<?php } else { ?>
<td>
<?php } ?>
				<?php
				$trans='';

				if(!empty($acltrans[$category])){
					 $trans = JText::_(strtoupper($acltrans[$category]));
					 if($trans == strtoupper($acltrans[$category])){
					 	$trans = '';
					 }
				}
				if(empty($trans)) $trans = JText::_('HIKA_'.strtoupper($category));
				if($trans == 'HIKA_'.strtoupper($category)) $trans = JText::_(strtoupper($category));

				echo $trans;
				?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
			<td>
<?php } ?>
				<?php echo $acltable->display($category,$actions)?>
			</td>
		</tr>
		<?php } ?>
	</table>
</div>
