<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

$groupsModel = CFactory::getModel('groups');
$activeGroup = $groupsModel->getMostActiveGroup();

if( is_null($activeGroup)) {
	$title = JText::_('COM_COMMUNITY_GROUPS_NONE_CREATED');
} else {
	$title       = JText::_('COM_COMMUNITY_ACTIVITIES_POPULAR_GROUP');
}

?>

<div class="joms-stream-box joms-responsive clearfix" style="margin-bottom:0px;">

 	<aside>
      <i class="joms-icon-bullhorn joms-icon-thumbnail"></i>
  </aside>

	<article>
		<a href="javascript:void(0);"><i class="joms-icon-bullhorn portrait-phone-only"></i> <?php echo $title; ?></a>
		<div class="separator"></div>
		<?php if( !is_null($activeGroup)) {
			$memberCount = $activeGroup->getMembersCount(); ?>

			<div class="space-12">
				<img src="<?php echo $activeGroup->getThumbAvatar(); ?>" class="joms-stream-avatar pull-left" style="width:36px;margin-right:8px;">

				<a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid='.$activeGroup->id) ?>">
					<h5 class="reset-gap"><?php echo $this->escape($activeGroup->name); ?></h5>
				</a>
				<p class="reset-gap">
					<?php echo JText::sprintf( (CStringHelper::isPlural( $memberCount)) ? 'COM_COMMUNITY_GROUPS_MEMBER_COUNT_MANY' : 'COM_COMMUNITY_GROUPS_MEMBER_COUNT' , $memberCount ); ?>
				</p>
			</div>
		<?php } ?>
	</article>
</div>
