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
<div>
	 <div id="latest-members-nav" class="app-box-filter">
	 	<i class="loading cFloat-R"></i>
		<a class="newest-member active-state" href="javascript:void(0);"><?php echo JText::_('COM_COMMUNITY_NEWEST_MEMBERS') ?></a>
		<b>&middot;</b>
		<a class="featured-member" href="javascript:void(0);"><?php echo JText::_('COM_COMMUNITY_FEATURED') ?></a>
		<b>&middot;</b>
		<a class="active-member" href="javascript:void(0);"><?php echo JText::_('COM_COMMUNITY_ACTIVE_MEMBERS') ?></a>
		<b>&middot;</b>
		<a class="popular-member" href="javascript:void(0);"><?php echo JText::_('COM_COMMUNITY_POPULAR_MEMBERS') ?></a>
	</div>

	<div class="app-box-content">
		<div id="latest-members-container">
			<ul class="cThumbsList cResetList clearfix">
				<?php foreach($latestMembers as $member){ ?>
					<li>
						<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$member->id );?>">
							<img class="cAvatar jomNameTips" src="<?php echo $member->getThumbAvatar(); ?>" title="<?php echo CTooltip::cAvatarTooltip($member); ?>" alt="<?php echo CStringHelper::escape( $member->getDisplayName() ) ?>"/>
						</a>
					</li>
				<?php }?>
			</ul>
		</div>
	</div>

	<div class="app-box-footer">
		<a href="<?php echo CRoute::_('index.php?option=com_community&view=search&task=browse' ); ?>" class="app-title-link">
			<?php echo JText::_( 'COM_COMMUNITY_FRONTPAGE_BROWSE_ALL' ); ?>
			(<?php echo $totalMembers;?>)
		</a>
	</div>
</div>