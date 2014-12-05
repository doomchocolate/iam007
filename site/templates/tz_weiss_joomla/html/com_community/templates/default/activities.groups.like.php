<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

$user = CFactory::getUser($this->act->actor);
$truncateVal = 60;
$date = JFactory::getDate($act->created);
if ( $config->get('activitydateformat') == "lapse" ) {
  $createdTime = CTimeHelper::timeLapse($date);
} else {
  $createdTime = $date->format($config->get('profileDateFormat'));
}

// Setup group table
$group = JTable::getInstance('Group', 'CTable');
$group->load($act->groupid);
$this->set('group', $group);

?>

<div class="joms-stream-content">
    <span><i class="joms-icon-users"></i>
    <?php echo CLikesHelper::generateHTML($act, $likedContent) ?></span>
    <p class="joms-share-meta date"><?php echo $createdTime; ?></p>
    <div class="joms-stream-box joms-responsive clearfix">
      <aside>
        <i class="joms-icon-users joms-icon-thumbnail"></i>
      </aside>
      <article>
        <a href="<?php echo $this->group->getLink();?>"> <i class="joms-icon-users portrait-phone-only"></i> <?php echo JHTML::_('string.truncate',$this->group->name , $truncateVal); ?></a>
        <p><?php echo JHTML::_('string.truncate',strip_tags($group->description) , $config->getInt('streamcontentlength')); ?></p>
      </article>
    </div>
</div>
