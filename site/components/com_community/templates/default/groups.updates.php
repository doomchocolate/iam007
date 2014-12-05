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

if($userid!=0)
{
?>

<div class="row-fluid">

	<?php if(sizeof($this->view('groups')->modGetUserGroups($userid))>0) { ?>

	<div class="span8">
		<div class="cMain">
			<?php
			$groupupdate = array_merge($this->view('groups')->modGetUserAnnouncement($userid),$this->view('groups')->modGetUserParticipatedDiscussion($userid));

			if(sizeof($groupupdate)>0) {
			?>
			<?php echo $this->view('groups')->modUserParticipatedDiscussion($userid); ?>
			<?php echo $this->view('groups')->modUserAnnouncement($userid); ?>
			<?php } elseif(!empty($my->_groups)) { ?>
			<div class="cEmpty cAlert"><?php echo JText::_('COM_COMMUNITY_GROUP_NO_UPDATE'); ?></div>
			<?php }else {?>
			<div class="cEmpty cAlert"><?php echo JText::sprintf( 'COM_COMMUNITY_GROUPS_UPDATE_DEFAULT' , CRoute::_('index.php?option=com_community&view=groups') ); ?></div>
			<?php } ?>
		</div>
	</div>
	<div class="span4">
		<div class="cSidebar">
			<?php echo $this->view('groups')->modUserGroups($userid); ?>
			<?php echo $this->view('groups')->modUserGroupPending($userid); ?>
			<?php echo $this->view('groups')->modUserGroupUpcomingEvents($userid); ?>
			<?php echo $this->view('groups')->modUserGroupVideosUpdate($userid); ?>
			<?php echo $this->view('groups')->modUserAlbumsUpdate($userid); ?>
		</div>
	</div>

	<?php } elseif(!empty($my->_groups)) { ?>
	<div class="cEmpty cAlert"><?php echo JText::_('COM_COMMUNITY_GROUP_NO_UPDATE'); ?></div>
	<?php }else {?>
	<div class="cEmpty cAlert"><?php echo JText::sprintf( 'COM_COMMUNITY_GROUPS_UPDATE_DEFAULT' , CRoute::_('index.php?option=com_community&view=groups') ); ?></div>

   <?php } ?>

</div>

<?php
}
else
{
?>
<div class="cEmpty cAlert"><?php echo JText::sprintf( 'COM_COMMUNITY_GROUPS_UPDATE_NOT_LOGGED_IN' , CRoute::_( 'index.php?option=com_community&view=frontpage' ), CRoute::_( 'index.php?option=com_community&view=register' )) ?></div>
<?php
}
?>
