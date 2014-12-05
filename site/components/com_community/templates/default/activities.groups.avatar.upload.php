<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

$user = CFactory::getUser($act->actor);

$date = JFactory::getDate($act->created);
if ( $config->get('activitydateformat') == "lapse" ) {
  $createdTime = CTimeHelper::timeLapse($date);
} else {
  $createdTime = $date->format($config->get('profileDateFormat'));
}

$groupLink = CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid='.$act->groupid);
$group = JTable::getInstance('Group', 'CTable');
$group->load($act->groupid);
?>

<div class="joms-stream-avatar">
    <a href="<?php echo ((int)$user->id !== 0) ? CUrlHelper::userLink($user->id) : 'javascript:void(0);'; ?>">
        <img class="img-responsive joms-radius-rounded" data-author="<?php echo $user->_userid; ?>" src="<?php echo $user->getThumbAvatar(); ?>">
    </a>
</div>

<div class="joms-stream-content">
  <header>
    <a class="joms-stream-author" href="<?php echo CUrlHelper::userLink($user->id); ?>">
      <?php echo $user->getDisplayName(); ?></a>
      <span><?php echo JText::sprintf('COM_COMMUNITY_CHANGE_GROUP_AVATAR',$groupLink, $act->appTitle);?></span>
    <p class="joms-share-meta date"><?php echo $createdTime; ?></p>
  </header>
  <div class="joms-stream-box joms-padding-small joms-inline-block">
    <img src="<?php echo $group->getAvatar(); ?>">
  </div>
  <?php $this->load('activities.actions'); ?>
</div>