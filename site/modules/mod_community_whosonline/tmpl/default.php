<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div>
	<div class="app-box-content">
		<ul class="cResetList cThumbsList clearfix">
			<?php for ( $i = 0; $i < count( $onlineMembers ); $i++ ) { ?>
				<?php $row =& $onlineMembers[$i]; ?>
				<li>
					<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$row->id ); ?>">
						<img class="cAvatar jomNameTips" src="<?php echo $row->user->getThumbAvatar(); ?>" title="<?php echo CTooltip::cAvatarTooltip($row->user); ?>" alt="<?php echo CStringHelper::escape($row->user->getDisplayName()); ?>" />
					</a>
				</li>
			<?php } ?>
		</ul>
	</div>
</div>