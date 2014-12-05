<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(false) { ?>
<div class="well">
<ul class="nav nav-pills nav-stacked nav-list">
	<li class="nav-header">List header</li>
<?php } else { ?>
<div id="cpanel">
<?php }

	foreach($this->buttonList as $btn) {
		if(empty($btn['url']))
			$btn['url'] = hikashop_completeLink($btn['link']);
		if(empty($btn['icon']))
			$btn['icon'] = 'icon-48-' . $btn['image'];

		if(false) {
			echo '<li><a href="'.$btn['url'].'"><i class="icon-'.$btn['image'].'"></i> '.$btn['text'].'</a></li>';
		} else {
?>
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo $btn['url'];?>">
					<span class="<?php echo $btn['icon'];?>" style="background-repeat:no-repeat;background-position:center;height:48px;padding:10px 0;"></span>
					<span><?php echo $btn['text'];?></span>
				</a>
			</div>
		</div>
<?php
		}
	}

if(false) { ?>
</ul>
</div>
<?php } else { ?>
	<div style="clear:both"></div>
</div>
<?php }
