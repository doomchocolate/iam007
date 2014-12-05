<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die();
?>
<div class="cPageBlock page-action">
    
	<?php if( $isBlocked ) : ?>
		<a href="javascript:void(0);" class="icon-blockuser" onclick="joms.users.unBlockUser('<?php echo $userId;  ?>');">
			<i class="com-icon-block-shade"></i>
			<?php echo JText::_('COM_COMMUNITY_UNBLOCK_USER'); ?>
		</a>
	<?php else : ?>
	    <a href="javascript:void(0);" class="icon-blockuser" onclick="joms.users.blockUser('<?php echo $userId;  ?>');">
	    	<i class="com-icon-block-shade"></i>
	    	<?php echo JText::_('COM_COMMUNITY_BLOCK_USER'); ?>
	    </a>
	<?php endif ; ?>
	
</div>