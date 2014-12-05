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

$album = JTable::getInstance('Album', 'CTable');
$album->load($act->cid);
$act->album = $album;
$this->set('album', $album);


// get created date time
$date = JFactory::getDate($act->created);
if ($config->get('activitydateformat') == "lapse") {
    $createdTime = CTimeHelper::timeLapse($date);
} else {
    $createdTime = $date->format($config->get('profileDateFormat'));
}


$user = CFactory::getUser($this->act->actor);
if (is_object($act->params)) {
    $action = $act->params->get('action');
} else {
    $act->params = new CParameter($act->params);
    $action = $act->params->get('action');
}

//
// Load saperate template for featured photo
if ($act->app == 'albums.featured') {
    $this->load('activities.photos.featured');
    return;
}

// Load saperate template for comment on a photo
// @since 2.8 .Newers stream uses 'photos.comment'
if ($action == 'wall' || $act->app == 'photos.comment') {
    $this->load('activities.photos.comment');
    return;
}

?>

<div class="joms-stream-avatar">
    <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
        <img class="img-responsive joms-radius-rounded" data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>" >
    </a>
</div>

<div class="joms-stream-content">

    <header>
        <a class="joms-stream-author" href="<?php echo CUrlHelper::userLink($user->id); ?>"><?php echo $user->getDisplayName(); ?></a>

<?php
// If we're using new stream style or has old style data (which contains {multiple} )
if ($act->params->get('style') == COMMUNITY_STREAM_STYLE || strpos($act->title, '{multiple}')) {
    // New style
    $count = $act->params->get('count', $act->params->get('count', 1));
    if (CStringHelper::isPlural($count)) {
        if($act->groupid){
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($act->groupid);
            $this->set('group', $group);
            echo JText::sprintf('COM_COMMUNITY_ACTIVITY_GROUP_PHOTOS_UPLOAD_TITLE', $count, CUrlHelper::groupLink($group->id), CStringHelper::escape($group->name));
        }else{
            echo JText::sprintf('COM_COMMUNITY_ACTIVITY_PHOTO_UPLOAD_TITLE_MANY', $count, $album->getURI(), CStringHelper::escape($album->name));
        }
    } else {
        if($act->groupid){
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($act->groupid);
            $this->set('group', $group);
            echo JText::sprintf('COM_COMMUNITY_ACTIVITY_GROUP_PHOTO_UPLOAD_TITLE', CUrlHelper::groupLink($group->id), CStringHelper::escape($group->name));
        }else{
            echo JText::sprintf('COM_COMMUNITY_ACTIVITY_PHOTO_UPLOAD_TITLE', $album->getURI(), CStringHelper::escape($album->name));
        }
    }
}
?>
    <?php if(!$act->groupid) {?>
        <div>
            <?php echo CActivitiesHelper::getStreamPermissionHTML($act->access,$act->actor); ?>
            <span class="joms-share-meta date"><?php echo $createdTime; ?></span>
        </div>
    <?php }?>
    </header>

<?php
// If custom message is there for single photo upload, display it
// User for style=1 only (since 2.8)
if (!empty($act->title) && $act->params->get('style') == 1) {
    ?>


    <?php } ?>

    <?php
    //remove for now
    if ($act->groupid && false) {
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($act->groupid);
        $this->set('group', $group);
        ?>


        <a class="joms-stream-reference" href="<?php echo CUrlHelper::groupLink($group->id); ?>"><i class="joms-icon-users"></i><?php echo $group->name; ?></a>

    <?php
    }

    $html = CPhotos::getActivityContentHTML($act);
    echo $html;
    ?>



    <?php
    // No action for wall comment
    if ($action != 'wall') {
        $this->load('activities.actions');
    }
    ?>




</div>