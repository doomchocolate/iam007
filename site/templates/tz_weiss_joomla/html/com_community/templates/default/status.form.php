<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') OR DIE();
?>

<script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/plupload.combined.js"></script>
<script>
  joms || (joms = {});
  joms.constants || (joms.constants = {});
  joms.language || (joms.language = {});

  joms.constants.uid                          = '<?php echo $my->id; ?>';

  joms.constants.album                        = <?php echo json_encode($album); ?>;
  joms.constants.eventCategories              = <?php echo json_encode( CFactory::getModel('events')->getCategories() ); ?>;
  joms.constants.videoCategories              = <?php echo json_encode( CFactory::getModel('videos')->getCategories() ); ?>;
  joms.constants.customActivities             = <?php echo json_encode( CActivityStream::getCustomActivities() ); ?>;

  joms.constants.juri || (joms.constants.juri = {});
  joms.constants.juri.base                    = '<?php echo JURI::base(); ?>';
  joms.constants.juri.root                    = '<?php echo JURI::root(); ?>';

  joms.constants.settings || (joms.constants.settings = {});
  joms.constants.settings.isProfile           = <?php echo ($type == 'profile') ? 1 : 0; ?>;
  joms.constants.settings.isMyProfile         = <?php echo ($my->id == $target) ? 1 : 0; ?>;
  joms.constants.settings.isGroup             = <?php echo ($type == 'groups') ? 1 : 0; ?>;
  joms.constants.settings.isEvent             = <?php echo ($type == 'events') ? 1 : 0; ?>;
  joms.constants.settings.isAdmin             = <?php echo (COwnerHelper::isCommunityAdmin() && $target == $my->id) ? 1 : 0; ?>;

  joms.constants.conf || (joms.constants.conf = {});

  joms.constants.conf.statusmaxchar           = +'<?php echo CFactory::getConfig()->get("statusmaxchar"); ?>';
  joms.constants.conf.profiledefaultprivacy   = +'<?php echo CFactory::getUser($my->id)->getParams()->get("privacyProfileView"); ?>';
  joms.constants.conf.maxvideouploadsize      = +'<?php echo CFactory::getConfig()->get("maxvideouploadsize"); ?>';
  joms.constants.conf.maxuploadsize           = +'<?php echo CFactory::getConfig()->get("maxuploadsize"); ?>';
  joms.constants.conf.enablephotos            = +'<?php echo $permission->enablephotos; ?>';
  joms.constants.conf.enablevideos            = +'<?php echo $permission->enablevideos; ?>';
  joms.constants.conf.enablevideosupload      = +'<?php echo $permission->enablevideosupload; ?>';
  joms.constants.conf.enablevideosmap         = +'<?php echo CFactory::getConfig()->get("videosmapdefault");?>';
  joms.constants.conf.enableevents            = +'<?php echo $permission->enableevents; ?>';
  joms.constants.conf.enablecustoms           = +'<?php echo CFactory::getConfig()->get("custom_activity") ? "1" : "0"; ?>';
  joms.constants.conf.limitphoto              = +'<?php echo CFactory::getConfig()->get("limit_photos_perday");?>';
  joms.constants.conf.uploadedphoto           = +'<?php echo CFactory::getModel("photos")->getTotalToday($my->id); ?>';
  joms.constants.conf.enablemood              = +'<?php echo CFactory::getConfig()->get("enablemood"); ?>';
  joms.constants.conf.enablelocation          = +'<?php echo CFactory::getConfig()->get("streamlocation"); ?>';
  joms.constants.conf.limitvideo              = +'<?php echo CFactory::getConfig()->get("limit_videos_perday");?>';
  joms.constants.conf.uploadedvideo           = +'<?php echo CFactory::getModel("videos")->getTotalToday($my->id); ?>';

  joms.constants.postbox || (joms.constants.postbox = {});
  joms.constants.postbox.attachment           = {};
  joms.constants.postbox.attachment.element   = '<?php echo $type ?>';
  joms.constants.postbox.attachment.target    = '<?php echo $target ?>';

  <?php if(JFactory::getApplication()->input->get('view') == 'profile'){ ?>
  joms.constants.postbox.attachment.filter   = 'active-profile';
  <?php } ?>

  joms.language.yes                           = '<?php echo JText::_("COM_COMMUNITY_YES"); ?>';
  joms.language.no                            = '<?php echo JText::_("COM_COMMUNITY_NO"); ?>';

  joms.language.status || (joms.language.status = {});
  joms.language.status['status_hint']         = '<?php echo JText::_("COM_COMMUNITY_STATUS_MESSAGE_HINT"); ?>';
  joms.language.status['photo_hint']          = '<?php echo JText::_("COM_COMMUNITY_STATUS_PHOTO_HINT"); ?>';
  joms.language.status['photos_hint']         = '<?php echo JText::_("COM_COMMUNITY_STATUS_PHOTOS_HINT"); ?>';
  joms.language.status['video_hint']          = '<?php echo JText::_("COM_COMMUNITY_STATUS_VIDEO_HINT"); ?>';
  joms.language.status['event_hint']          = '<?php echo JText::_("COM_COMMUNITY_STATUS_EVENT_HINT"); ?>';
  joms.language.status['custom_hint']         = '<?php echo JText::_("COM_COMMUNITY_STATUS_MESSAGE_HINT"); ?>';
  joms.language.status.mood                   = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_STATUS_MOOD"); ?>';
  joms.language.status.remove_mood_button     = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_STATUS_REMOVE_MOOD_BUTTON"); ?>';
  joms.language.status.location               = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_STATUS_LOCATION"); ?>';

  joms.language.postbox || (joms.language.postbox = {});
  joms.language.postbox.status                = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_STATUS"); ?>';
  joms.language.postbox.photo                 = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_PHOTO"); ?>';
  joms.language.postbox.video                 = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_VIDEO"); ?>';
  joms.language.postbox.event                 = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_EVENT"); ?>';
  joms.language.postbox.custom                = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_CUSTOM"); ?>';
  joms.language.postbox.post_button           = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_POST_BUTTON"); ?>';
  joms.language.postbox.cancel_button         = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_CANCEL_BUTTON"); ?>';
  joms.language.postbox.upload_button         = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_UPLOAD_BUTTON"); ?>';

  joms.language.photo || (joms.language.photo = {});
  joms.language.photo.upload_button           = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_PHOTO_UPLOAD_BUTTON"); ?>';
  joms.language.photo.upload_button_more      = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_PHOTO_UPLOAD_BUTTON_MORE"); ?>';
  joms.language.photo.upload_limit_exceeded   = '<?php echo JText::_("COM_COMMUNITY_PHOTO_UPLOAD_LIMIT_EXCEEDED"); ?>';

  joms.language.video || (joms.language.video = {});
  joms.language.video.location                = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_STATUS_LOCATION"); ?>';
  joms.language.video.category_label          = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_VIDEO_CATEGORY_LABEL"); ?>';
  joms.language.video.share_button            = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_VIDEO_SHARE_BUTTON"); ?>';
  joms.language.video.link_hint               = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_VIDEO_LINK_HINT"); ?>';
  joms.language.video.upload_button           = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_VIDEO_UPLOAD_BUTTON"); ?>';
  joms.language.video.upload_hint             = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_VIDEO_UPLOAD_HINT"); ?>';
  joms.language.video.upload_maxsize          = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_VIDEO_UPLOAD_MAXSIZE"); ?>';
  joms.language.video.upload_limit_exceeded   = '<?php echo JText::_("COM_COMMUNITY_VIDEO_UPLOAD_LIMIT_EXCEEDED"); ?>';

  joms.language.event || (joms.language.event = {});
  joms.language.event.title_hint              = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_EVENT_TITLE_HINT"); ?>';
  joms.language.event.date_and_time           = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_EVENT_DATE_AND_TIME"); ?>';
  joms.language.event.event_detail            = '<?php echo JText::_("COM_COMMUNITY_EVENTS_DETAIL"); ?>';
  joms.language.event.category                = '<?php echo JText::_("COM_COMMUNITY_EVENTS_CATEGORY"); ?>';
  joms.language.event.location                = '<?php echo JText::_("COM_COMMUNITY_EVENTS_LOCATION"); ?>';
  joms.language.event.location_hint           = '<?php echo JText::_("COM_COMMUNITY_EVENTS_LOCATION_DESCRIPTION"); ?>';
  joms.language.event.start                   = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_EVENT_START"); ?>';
  joms.language.event.start_date_hint         = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_EVENT_START_DATE_HINT"); ?>';
  joms.language.event.start_time_hint         = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_EVENT_START_TIME_HINT"); ?>';
  joms.language.event.end                     = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_EVENT_END"); ?>';
  joms.language.event.end_date_hint           = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_EVENT_END_DATE_HINT"); ?>';
  joms.language.event.end_time_hint           = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_EVENT_END_TIME_HINT"); ?>';
  joms.language.event.done_button             = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_EVENT_DONE_BUTTON"); ?>';

  joms.language.custom || (joms.language.custom = {});
  joms.language.custom.predefined_button      = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_CUSTOM_PREDEFINED_BUTTON"); ?>';
  joms.language.custom.predefined_label       = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_CUSTOM_PREDEFINED_LABEL"); ?>';
  joms.language.custom.custom_button          = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_CUSTOM_CUSTOM_BUTTON"); ?>';
  joms.language.custom.custom_label           = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_CUSTOM_CUSTOM_LABEL"); ?>';

  joms.language.mood || (joms.language.mood = {});
  joms.language.mood.happy                    = '<?php echo JText::_("COM_COMMUNITY_MOOD_HAPPY"); ?>';
  joms.language.mood.meh                      = '<?php echo JText::_("COM_COMMUNITY_MOOD_MEH"); ?>';
  joms.language.mood.sad                      = '<?php echo JText::_("COM_COMMUNITY_MOOD_SAD"); ?>';
  joms.language.mood.loved                    = '<?php echo JText::_("COM_COMMUNITY_MOOD_LOVED"); ?>';
  joms.language.mood.excited                  = '<?php echo JText::_("COM_COMMUNITY_MOOD_EXCITED"); ?>';
  joms.language.mood.pretty                   = '<?php echo JText::_("COM_COMMUNITY_MOOD_PRETTY"); ?>';
  joms.language.mood.tired                    = '<?php echo JText::_("COM_COMMUNITY_MOOD_TIRED"); ?>';
  joms.language.mood.angry                    = '<?php echo JText::_("COM_COMMUNITY_MOOD_ANGRY"); ?>';
  joms.language.mood.speachless               = '<?php echo JText::_("COM_COMMUNITY_MOOD_SPEACHLESS"); ?>';
  joms.language.mood.shocked                  = '<?php echo JText::_("COM_COMMUNITY_MOOD_SHOCKED"); ?>';
  joms.language.mood.irretated                = '<?php echo JText::_("COM_COMMUNITY_MOOD_IRRETATED"); ?>';
  joms.language.mood.sick                     = '<?php echo JText::_("COM_COMMUNITY_MOOD_SICK"); ?>';
  joms.language.mood.annoyed                  = '<?php echo JText::_("COM_COMMUNITY_MOOD_ANNOYED"); ?>';
  joms.language.mood.relieved                 = '<?php echo JText::_("COM_COMMUNITY_MOOD_RELIEVED"); ?>';
  joms.language.mood.blessed                  = '<?php echo JText::_("COM_COMMUNITY_MOOD_BLESSED"); ?>';
  joms.language.mood.bored                    = '<?php echo JText::_("COM_COMMUNITY_MOOD_BORED"); ?>';

  joms.language.moodshort || (joms.language.moodshort = {});
  joms.language.moodshort.happy               = '<?php echo JText::_("COM_COMMUNITY_MOOD_SHORT_HAPPY"); ?>';
  joms.language.moodshort.meh                 = '<?php echo JText::_("COM_COMMUNITY_MOOD_SHORT_MEH"); ?>';
  joms.language.moodshort.sad                 = '<?php echo JText::_("COM_COMMUNITY_MOOD_SHORT_SAD"); ?>';
  joms.language.moodshort.loved               = '<?php echo JText::_("COM_COMMUNITY_MOOD_SHORT_LOVED"); ?>';
  joms.language.moodshort.excited             = '<?php echo JText::_("COM_COMMUNITY_MOOD_SHORT_EXCITED"); ?>';
  joms.language.moodshort.pretty              = '<?php echo JText::_("COM_COMMUNITY_MOOD_SHORT_PRETTY"); ?>';
  joms.language.moodshort.tired               = '<?php echo JText::_("COM_COMMUNITY_MOOD_SHORT_TIRED"); ?>';
  joms.language.moodshort.angry               = '<?php echo JText::_("COM_COMMUNITY_MOOD_SHORT_ANGRY"); ?>';
  joms.language.moodshort.speachless          = '<?php echo JText::_("COM_COMMUNITY_MOOD_SHORT_SPEACHLESS"); ?>';
  joms.language.moodshort.shocked             = '<?php echo JText::_("COM_COMMUNITY_MOOD_SHORT_SHOCKED"); ?>';
  joms.language.moodshort.irretated           = '<?php echo JText::_("COM_COMMUNITY_MOOD_SHORT_IRRETATED"); ?>';
  joms.language.moodshort.sick                = '<?php echo JText::_("COM_COMMUNITY_MOOD_SHORT_SICK"); ?>';
  joms.language.moodshort.annoyed             = '<?php echo JText::_("COM_COMMUNITY_MOOD_SHORT_ANNOYED"); ?>';
  joms.language.moodshort.relieved            = '<?php echo JText::_("COM_COMMUNITY_MOOD_SHORT_RELIEVED"); ?>';
  joms.language.moodshort.blessed             = '<?php echo JText::_("COM_COMMUNITY_MOOD_SHORT_BLESSED"); ?>';
  joms.language.moodshort.bored               = '<?php echo JText::_("COM_COMMUNITY_MOOD_SHORT_BORED"); ?>';

  joms.language.geolocation || (joms.language.geolocation = {});
  joms.language.geolocation.loading           = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_LOADING"); ?>';
  joms.language.geolocation.loaded            = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_LOADED"); ?>';
  joms.language.geolocation.error             = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_ERROR"); ?>';
  joms.language.geolocation.select_button     = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_SELECT_BUTTON"); ?>';
  joms.language.geolocation.remove_button     = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_REMOVE_BUTTON"); ?>';
  joms.language.geolocation.near_here         = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_NEAR_HERE"); ?>';
  joms.language.geolocation.empty             = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_GEOLOCATION_EMPTY"); ?>';

  joms.language.fetch || (joms.language.fetch = {});
  joms.language.fetch['title_hint']           = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_FETCH_TITLE_HINT"); ?>';
  joms.language.fetch['description_hint']     = '<?php echo JText::_("COM_COMMUNITY_POSTBOX_FETCH_DESCRIPTION_HINT"); ?>';

  joms.language.privacy || (joms.language.privacy = {});
  joms.language.privacy['public']             = '<?php echo JText::_("COM_COMMUNITY_PRIVACY_PUBLIC"); ?>';
  joms.language.privacy['public_desc']        = '<?php echo JText::_("COM_COMMUNITY_PRIVACY_PUBLIC_DESC"); ?>';
  joms.language.privacy['site_members']       = '<?php echo JText::_("COM_COMMUNITY_PRIVACY_SITE_MEMBERS"); ?>';
  joms.language.privacy['site_members_desc']  = '<?php echo JText::_("COM_COMMUNITY_PRIVACY_SITE_MEMBERS_DESC"); ?>';
  joms.language.privacy['friends']            = '<?php echo JText::_("COM_COMMUNITY_PRIVACY_FRIENDS"); ?>';
  joms.language.privacy['friends_desc']       = '<?php echo JText::_("COM_COMMUNITY_PRIVACY_FRIENDS_DESC"); ?>';
  joms.language.privacy['me']                 = '<?php echo JText::_("COM_COMMUNITY_PRIVACY_ME"); ?>';
  joms.language.privacy['me_desc']            = '<?php echo JText::_("COM_COMMUNITY_PRIVACY_ME_DESC"); ?>';

  joms.language.stream || (joms.language.stream = {});
  joms.language.stream.remove_comment         = '<?php echo JText::_("COM_COMMUNITY_COMMENT_REMOVE"); ?>';
  joms.language.stream.remove_comment_message = '<?php echo JText::_("COM_COMMUNITY_COMMENT_REMOVE_MESSAGE"); ?>';

</script>
<div class="joms-postbox clearfix" style="display:none">
  <div class="joms-postbox-preview" style="display:none"></div>
  <div id="joms-postbox-status" class="joms-postbox-content">
    <div class="joms-postbox-tabs"></div>
  </div>
  <nav class="joms-postbox-tab joms-postbox-tab-root clearfix" style="display:none">
    <ul class="unstyled">
      <li data-tab="status">
        <i class="joms-icon-pencil"></i><span class="visible-desktop"><?php echo JText::_("COM_COMMUNITY_POSTBOX_STATUS"); ?></span>
      </li>
      <li data-tab="photo">
        <i class="joms-icon-camera"></i><span class="visible-desktop"><?php echo JText::_("COM_COMMUNITY_POSTBOX_PHOTO"); ?></span>
      </li>
      <li data-tab="video">
        <i class="joms-icon-videocam"></i><span class="visible-desktop"><?php echo JText::_("COM_COMMUNITY_POSTBOX_VIDEO"); ?></span>
      </li>
      <li data-tab="event">
        <i class="joms-icon-calendar"></i><span class="visible-desktop"><?php echo JText::_("COM_COMMUNITY_POSTBOX_EVENT"); ?></span>
      </li>
      <?php if ( CFactory::getConfig()->get("custom_activity") && COwnerHelper::isCommunityAdmin() && $target == $my->id ) { ?>
      <li data-tab="custom">
        <i class="joms-icon-bullhorn"></i><span class="visible-desktop"><?php echo JText::_("COM_COMMUNITY_POSTBOX_CUSTOM"); ?></span>
      </li>
      <?php } ?>
    </ul>
  </nav>
</div>
