<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
?>
<?php
	if($groups)
	{
?>
<div class="cModule cGroups-GroupInvites app-box">
	<h3 class="app-box-header"><?php echo JText::_('COM_COMMUNITY_GROUPS_PENDING_INVITATIONS');?></h3>
	<div class="app-box-content">
		<ul class="cThumbDetails cResetList">
		<?php
			for( $i = 0; $i < count( $groups ); $i++ )
			{
				$group	=&  $groups[$i];
		?>
		<li>
			<a class="cThumb-Avatar cFloat-L" href="<?php echo CRoute::_( 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id ); ?>">
				<img class="cAvatar" src="<?php echo $group->getThumbAvatar(); ?>" alt="<?php echo $group->name; ?>" />
			</a>

			<div class="cThumb-Detail">
				<a class="cThumb-Title" href="<?php echo CRoute::_( 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id ); ?>"><?php echo $group->name; ?></a>
				<div class="cThumb-Brief small"><?php echo JText::sprintf((CStringHelper::isPlural($group->membercount)) ? 'COM_COMMUNITY_GROUPS_MEMBER_COUNT_MANY':'COM_COMMUNITY_GROUPS_MEMBER_COUNT', $group->membercount);?></div>
			</div>
		</li>
		<?php
			}
		?>		
		</ul>
	</div>
	<div class="app-box-footer">
		<a href="<?php echo CRoute::_('index.php?option=com_community&view=groups'); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_VIEW_ALL');?></a>
	</div>
</div>
<?php
	}
?>
