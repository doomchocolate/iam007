<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// test
// $act->app can be a single word or in app.action form.
// EG:// 'event', 'event.wall'. Find the first part only
$appName = explode('.', $act->app);
$appName = $appName[0];

// Grab primary object to be used in permission checking, defined by appname
$obj = $act;
if ($appName == 'groups') {
    $obj = $this->group;
}

if ($appName == 'events') {
    $obj = $this->event;
}

$my = CFactory::getUser();
$allowLike = ($my->authorise('community.add', 'activities.like.' . $this->act->actor, $obj) );
//$allowComment = ($my->authorise('community.add', 'activities.comment.' . $this->act->actor, $obj) );
$showLocation = !empty($this->act->location);

// @todo: delete permission shoudl be handled within ACL system
$allowDelete = ( ($act->actor == $my->id) || $isCommunityAdmin || ( $act->target == $my->id )) && ($my->id != 0);

// Allow system message deletion only for admin
if ($act->app == 'users.featured') {
    $allowDelete = $isCommunityAdmin;
}

//Discussion Replies shouldnt allow any commenting - 30Jan13 (http://www.ijoomla.com:8080/browse/JOM-142)
// create new event stream shouldnt allow any commenting - 10 jan 2014

$allowComment = CActivitiesHelper::isActionAllowed($act->app, 'comment');
$allowComment = $allowComment && ($my->authorise('community.add', 'activities.comment.' . $this->act->actor, $obj) );

if ($act->app == 'groups.discussion.reply' || $act->app == 'groups.discussion' || $act->app == 'groups.bulletin' || $act->app == 'events' || $act->app == 'groups' || strpos($act->app,'featured') !== false ) {
    $allowComment = false;
}

// Allow comment for system post
if ($appName == 'system') {
    $allowComment = !empty($my->id);
}

// No like/comment support from the activity stream
if ($appName == 'photos' || $appName == 'videos') {
    // $allowLike = false;
    // $allowComment = false;
}
?>
<div data-type="stream-action" class="cStream-Actions clearfix">

    <!-- Show if it is explicitly allowed: -->
    <nav class="joms-stream-status-action">
        <?php if ($allowLike) { ?>
            <?php if ($act->userLiked != COMMUNITY_LIKE) { ?>
                <a data-action="like" data-stream-id="<?php echo $act->id; ?>" href="#" class="joms-icon-thumbs-up"><?php echo JText::_('COM_COMMUNITY_LIKE') ?></a>
            <?php } else { ?>
                <a data-action="unlike" data-stream-id="<?php echo $act->id; ?>" href="#" class="joms-icon-thumbs-down"><?php echo JText::_('COM_COMMUNITY_UNLIKE') ?></a>
            <?php } ?>
        <?php } ?>
        <?php if ($allowComment) { ?>
            <a data-action="comment" data-stream-id="<?php echo $act->id; ?>" href="#" class="joms-icon-comment"
            <?php
            if ($act->commentCount > 0) {
                echo 'style="display:none"';
            }
            ?>><?php echo JText::_('COM_COMMUNITY_COMMENT') ?></a>
           <?php } ?>

           <?php
               // important notice : video comment is not videos.comment, to keep the legacy code, additional checking will be done
               if ($my->id > 0 && $my->id != $act->actor && ( ($act->access == 0 || $act->access == 10) && ($act->group_access == 0 && $act->event_access == 0)) && $act->app != 'groups.bulletin'
                   && $act->app != 'cover.upload' && strpos($act->app,'comment') === false && strpos($act->app,'featured') === false && $act->app != 'groups.discussion.reply'|| ($act->app == 'videos' && strpos($act->params,'wallid') === false)) {
            ?>
            <a data-action="share" data-stream-id="<?php echo $act->id; ?>" href="javascript:void(0);" onclick="joms.share.popup('<?php echo $act->id; ?>')" class="joms-icon-forward"><?php echo JText::_('COM_COMMUNITY_SHARE') ?></a>
        <?php } ?>
        <?php if (CFactory::getConfig()->get('enablereporting') && (($my->id == 0 && CFactory::getConfig()->get('enableguestreporting')) || ($my->id > 0 && $my->id != $act->actor)) && strpos($act->app,'featured') === false ) { ?>
            <a data-action="report" onclick="joms.report.showWindow('activities,reportActivities', '<?php echo $act->id; ?>');
                        return false;" data-stream-id="<?php echo $act->id; ?>" href="#" class="joms-icon-warning-sign"><?php echo JText::_('COM_COMMUNITY_REPORT') ?></a>
           <?php } ?>
    </nav>

    <?php
    // Format created date
    $config = CFactory::getConfig();
    $date = JFactory::getDate($act->created);
    if ($config->get('activitydateformat') == "lapse") {
        $createdTime = CTimeHelper::timeLapse($date);
    } else {
        $createdTime = $date->format($config->get('profileDateFormat'));
    }
    ?>

</div>

<?php if ($allowComment || $allowLike || $showLike) { ?>
    <div data-type="stream-comments" class="cStream-Respond wall-cocs" id="wall-cmt-<?php echo $act->id; ?>">
        <?php if ($act->likeCount > 0 && $showLike) { /* hide count if no one like it */ ?>
            <div class="cStream-Likes">
                <i class="stream-icon joms-icon-thumbs-up"></i>
                <a onclick="jax.call('community', 'system,ajaxStreamShowLikes', '<?php echo $act->id; ?>');
                                return false;" href="#showLikes"><?php echo ($act->likeCount > 1) ? JText::sprintf('COM_COMMUNITY_LIKE_THIS_MANY', $act->likeCount) : JText::sprintf('COM_COMMUNITY_LIKE_THIS', $act->likeCount); ?></a>
            </div>
        <?php } ?>
        <?php if ($act->commentCount > 1) { ?>
            <div data-type="stream-more" class="cStream-More" data-commentmore="true" >
                <i class="stream-icon joms-icon-comment"></i>
                <a href="#showallcomments"><?php echo JText::sprintf('COM_COMMUNITY_ACTIVITY_NO_COMMENT', $act->commentCount, 'wall-cmt-count') ?></a>
            </div>
        <?php } ?>
        <?php if ($act->commentCount > 0) { ?>
            <?php echo $act->commentLast; ?>
        <?php } ?>

        <?php if ($allowComment) : ?>
            <!-- post new comment form -->
            <div data-type="stream-newcomment" class="cStream-Form stream-form wallform <?php
            if ($act->commentCount == 0): echo 'wallnone';
            endif;
            ?>" data-formblock="true">
                <form class="reset-gap">
                    <div class="cStream-FormInput">
                        <textarea class="cStream-FormText" name="comment"></textarea>
                    </div>
                    <div class="cStream-FormSubmit">
                        <a data-action="cancel" class="cStream-FormCancel" href="javascript:"><?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON'); ?></a>
                        <button data-action="save" class="btn btn-primary btn-small"><?php echo JText::_('COM_COMMUNITY_POST_COMMENT_BUTTON'); ?></button>
                    </div>
                </form>
            </div>

            <?php /* Hide reply button if no one has post a comment */ ?>
            <?php if ($allowComment): ?>
                <div data-type="stream-reply" data-replyblock="true" <?php
                if ($act->commentCount < 1) {
                    echo 'style="display:none"';
                }
                ?> >
                    <span class="cStream-Reply"><a data-action="reply" href="javascript:"><?php echo JText::_('COM_COMMUNITY_REPLY'); ?></a></span>
                </div>
            <?php endif; ?>
    <?php endif; ?>

    </div>
<?php } ?>

