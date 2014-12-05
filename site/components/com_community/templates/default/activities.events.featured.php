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
$user = CFactory::getUser($this->act->actor);
$event = JTable::getInstance('Event', 'CTable');
$event->load($this->act->cid);

$date = JFactory::getDate($act->created);
if ( $config->get('activitydateformat') == "lapse" ) {
  $createdTime = CTimeHelper::timeLapse($date);
} else {
  $createdTime = $date->format($config->get('profileDateFormat'));
}

?>

<div class="joms-stream-avatar">
    <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
        <img class="img-responsive joms-radius-rounded" data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>">
    </a>
</div>
<div class="joms-stream-content">
    <span><?php echo $this->act->title; ?></span>
    <p class="joms-share-meta date"><?php echo $createdTime; ?></p>
    <div class="joms-stream-box joms-responsive clearfix">
        <aside>
            <i class="joms-icon-bullhorn joms-icon-thumbnail"></i>
        </aside>
        <article>
            <a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid='.$event->id); ?>"> <i class="joms-icon-users portrait-phone-only"></i>
                <p><?php echo $event->title; ?></p>
            </a>
            <div class="separator"></div>
            <p><?php echo JHTML::_('string.truncate',strip_tags($event->description) , $config->getInt('streamcontentlength')); ?></p>
        </article>
    </div>

    <?php
    // Tell actions that this is a featured stream
    $this->act->isFeatured = true;
    $this->load('activities.actions'); // remove all the actions #	?>
</div>