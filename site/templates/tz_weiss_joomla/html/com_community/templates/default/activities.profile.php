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

/**
 * @since 3.2 we'll use CActivity for each activity object
 * @todo in sprint 3 we must move everything into CActivity while passing into template layout
 */
/* Temporary fix for sprint 2 */
if ($this->act instanceof CTableActivity) {
    /* If this's CTableActivity then we use getProperties() */
    $activity = new CActivity($this->act->getProperties());
} else {
    /* If it's standard object than we just passing it */
    $activity = new CActivity($this->act);
}

$address = $activity->getLocation();
$user = $activity->getActor();
$target = $activity->getTarget();
$headMetas = $activity->getParams('headMetas');
/* We do convert into JRegistry to make it easier to use */
if ($headMetas) {
    $headMetaParams = new JRegistry($headMetas);
}
if ($act->app == 'profile.avatar.upload') {
    $this->load('activities.profile.avatar.upload');
    return;
}


if (!empty($act->params)) {
    if (!is_object($act->params)) {
        $act->params = new JRegistry($act->params);
    }
    $mood = $act->params->get('mood', null);
} else {
    $mood = null;
}
$title = $activity->get('title');
?>
<!-- Avatar -->
<div class="joms-stream-avatar">
    <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
        <img class="img-responsive joms-radius-rounded" data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>">
    </a>
</div>
<!-- Main body -->
<div class="joms-stream-content">
    <!-- Header -->
    <header>
        <!-- Author name & reference -->
        <a href="<?php echo CUrlHelper::userLink($user->id); ?>" data-joms-username class="joms-share-user"><strong><?php echo $user->getDisplayName(); ?></strong></a>

        <!-- put if else target here -->

        <?php
        if ($activity->get('eventid')) {
            $event = $this->event;
            ?>
                            <span class="cStream-Reference">
                                ➜ <a class="cStream-Reference" href="<?php echo CUrlHelper::eventLink($event->id); ?>"><?php echo $event->title; ?></a>
                            </span>
            <?php
        } else if ($activity->get('groupid')) {
            $group = $this->group;
            ?>
                            <span class="cStream-Reference">
                                ➜ <a class="cStream-Reference" href="<?php echo CUrlHelper::groupLink($group->id); ?>"><?php echo $group->name; ?></a>
                            </span>
        <!-- Target is user profile -->
        <?php } else if ( ( $activity->get('app') == 'profile' ) && ( $activity->get('target') != 0 ) && $activity->get('target') != $user->id ) { ?>
                            <span class="cStream-Reference">
                                ➜ <a class="cStream-Reference" href="<?php echo CUrlHelper::userLink($activity->target); ?>"><?php echo CFactory::getUser($activity->get('target'))->getDisplayName(); ?></a>
                            </span>
        <?php } ?>

        <!-- Privacy -->
        <div>
			<?php /* Do filter not show privacy option for events' activities & groups' activities */ ?>
			<?php if ( strpos($activity->get('app'),'events') === false  && strpos($activity->get('app'),'groups') === false ) { ?>
				<?php echo ($activity->get('groupid') || ($activity->get('app') == 'profile') && $activity->get('target') != $activity->get('actor') )? '' : CActivitiesHelper::getStreamPermissionHTML($activity->access,$user->id); ?>
			<?php } ?>
            <span class="joms-share-meta date"><?php echo $activity->getCreateTimeFormatted(); ?></span>
        </div>

    </header>
    <div data-type="stream-editor" class="cStream-Respond" style="display:none">
        <div class="cStream-Form" style="display:block">
            <div class="cStream-FormInput"><textarea><?php echo $activity->get('title'); ?></textarea></div>
            <div class="cStream-FormSubmit">
                <a data-action="cancel" href="javascript:" class="cStream-FormCancel">Cancel</a>
                <button data-action="save" class="btn btn-primary btn-small">Edit Post</button>
            </div>
        </div>
    </div>
    <p data-type="stream-content">
        <span><?php echo empty($title) ? ltrim(CActivities::format($activity->get('title'), $mood),' -') : CActivities::format($activity->get('title'), $mood); ?></span>
        <?php if ($address) { ?>
        <span class="joms-status-location"><?php if(!empty($title)){?>- <?php }?>in
            <a data-action="open-map" data-stream-id="<?php echo $activity->get('id'); ?>" href="javascript:"
                onclick="joms.share.map('<?php echo $activity->get('id'); ?>')"><?php echo $address ?></a></span>
        <?php } ?>
    </p>
    <!-- Fetched data -->
    <?php if ($headMetas) { ?>

        <?php if ($headMetaParams->get('title') || $headMetaParams->get('description')) { ?>

            <?php

                if($headMetaParams->get('type') == 'video'){
                    if($headMetaParams->get('video_provider') == 'break'){
                        $href= 'href="'.$headMetaParams->get('link').'" target="_blank"';
                    }else{
                        $href= 'href=\'javascript:jax.call("community" , "videos,ajaxShowStreamVideoWindow", "'.$activity->id.'");\'';
                    }

                }else{
                    $href = $headMetaParams->get('link') ? $headMetaParams->get('link') : '#';
                    $href = "href='".$href."' target='_blank'";
                }
            ?>

            <?php if($headMetaParams->get('type') == 'video'){ ?>
            <div class="joms-stream-box joms-fetch-wrapper clearfix" >
                <div style="position:relative;">
                    <div class="row-fluid">
                        <div class="span4">
                            <a <?php echo $href; ?> class="cVideo-Thumb">
                                <div style="margin-bottom:12px; position:relative">
                                    <img src="<?php echo $headMetaParams->get('image'); ?>"
                                        alt="<?php echo $this->escape($headMetaParams->get('title')); ?>"
                                        style="max-width:100%" />
                                </div>
                            </a>
                        </div>
                        <div class="span8">
                            <article class="joms-stream-fetch-content" style="margin-left:0; padding-top:0">
                                <a <?php echo $href; ?>><?php echo $this->escape($headMetaParams->get('title')); ?></a>
                                <div class="separator"></div>
                                <p class="reset-gap">
                                    <?php echo JHTML::_('string.truncate', $headMetaParams->get('description'), $config->getInt('streamcontentlength'), true, false); ?>
                                </p>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
            <?php }else{ ?>
            <div class="joms-stream-box joms-fetch-wrapper clearfix">
                <div style="position:relative;">
                    <div class="row-fluid">
                        <?php if ($headMetaParams->get('image')) { ?>
                        <div class="span4">
                            <a <?php echo $href; ?>>
                                <img class="joms-stream-thumb" src="<?php echo $headMetaParams->get('image'); ?>" />
                            </a>
                        </div>
                        <?php } ?>
                        <div class="span<?php echo $headMetaParams->get('image') ? '8' : '12' ?>">
                            <article class="joms-stream-fetch-content" style="margin-left:0; padding-top:0">
                                <a <?php echo $href; ?>>
                                    <span class="joms-stream-fetch-title"><?php echo $headMetaParams->get('title'); ?></span>
                                </a>
                                    <span class="joms-stream-fetch-desc"><?php echo CStringHelper::trim_words($headMetaParams->get('description')); ?></span>
                                    <?php if ($headMetaParams->get('link')) { ?>
                                        <cite><?php echo preg_replace('#^https?://#', '', $headMetaParams->get('link')); ?></cite>
                                    <?php } ?>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        <?php }
    }
    ?>

    <?php $this->load('activities.actions'); ?>
</div>