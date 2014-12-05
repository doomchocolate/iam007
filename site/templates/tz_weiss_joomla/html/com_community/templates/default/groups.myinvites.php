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
<?php echo $sortings; ?>
<div class="cLayout cGroups-Invitations">
	<?php
	if( $groups )
	{
	?>
	<div class="cAlert">
		<?php echo JText::sprintf( CStringHelper::isPlural( $count ) ? 'COM_COMMUNITY_GROUPS_INVIT_COUNT_MANY' : 'COM_COMMUNITY_GROUPS_INVIT_COUNT' , $count ); ?>
	</div>

	<ul class="cIndexList forGroups cResetList">
	<?php
		for( $i = 0; $i < count( $groups ); $i++ )
		{
			$group	=& $groups[$i]; 
	?>
		<li id="groups-invite-<?php echo $group->id;?>">
			<div class="cIndex-Box clearfix">
				<a href="<?php echo CRoute::_( 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id );?>" class="cIndex-Avatar cFloat-L">
					<img src="<?php echo $group->getThumbAvatar();?>" alt="<?php echo $this->escape($group->name); ?>" class="cAvatar" />
				</a>
				<div class="cIndex-Content">
					<h3 class="cIndex-Name cResetH">
						<a href="<?php echo CRoute::_( 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id );?>"><?php echo $group->name; ?></a>
					</h3>
					<div class="cIndex-Status">
						<?php echo $this->escape($group->description); ?>
						<div class="cIndex-Created small"><?php echo JText::sprintf('COM_COMMUNITY_GROUPS_CREATE_TIME_ON' , JHTML::_('date', $group->created, JText::_('DATE_FORMAT_LC')) );?></div>
					</div>
		            
					<div class="cIndex-Actions">
						<div>
							<i class="com-icon-groups"></i>
							<a href="<?php echo CRoute::_( 'index.php?option=com_community&view=groups&task=viewmembers&groupid=' . $group->id ); ?>">
								<?php echo JText::sprintf((CStringHelper::isPlural($group->membercount)) ? 'COM_COMMUNITY_GROUPS_MEMBER_COUNT_MANY':'COM_COMMUNITY_GROUPS_MEMBER_COUNT', $group->membercount);?>
							</a>
						</div>
						<div>
							<i class="com-icon-wall"></i>
							<span>
								<?php echo JText::sprintf((CStringHelper::isPlural($group->discusscount)) ? 'COM_COMMUNITY_GROUPS_DISCUSSION_COUNT_MANY' :'COM_COMMUNITY_GROUPS_DISCUSSION_COUNT', $group->discusscount);?>
							</span>
						</div>
						<div>
							<i class="com-icon-comment"></i>
							<span>
								<?php echo JText::sprintf((CStringHelper::isPlural($group->wallcount)) ? 'COM_COMMUNITY_GROUPS_WALL_COUNT_MANY' : 'COM_COMMUNITY_GROUPS_WALL_COUNT', $group->wallcount);?>
							</span>
						</div>
					</div>
					<div id="group-invite-notice"></div>
				</div>
					<div class="community-groups-pending-actions pull-right">
						<a class="btn" href="javascript:void(0);" onclick="joms.groups.invitation.reject('<?php echo $group->id;?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_REJECT');?></a>
						<a class="btn-primary btn" href="javascript:void(0);" onclick="joms.groups.invitation.accept('<?php echo $group->id;?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_ACCEPT');?></a>
					</div>
			</div>
		</li>
	<?php
		}
	?>
	</ul>
	<?php
	}else
	{
	?>
	<div class="cEmpty cAlert"><?php echo JText::_('COM_COMMUNITY_GROUPS_NO_INVITATIONS'); ?></div>
	<?php
	}
	?>
	

<?php 
if ( $pagination->getPagesLinks() ) 
{
?>
<div class="cPagination">
	<?php echo $pagination->getPagesLinks(); ?>
</div>
<?php 
} 
?>
</div>
