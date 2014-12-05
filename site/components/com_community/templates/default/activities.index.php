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

<?php
$appLib = CAppPlugins::getInstance();
$filter = JFactory::getApplication()->input->get('filter',$filter);
$filterValue =  JFactory::getApplication()->input->get('value');
$actId = JFactory::getApplication()->input->get('actid');
$date = JFactory::getDate();
if ( $config->get('activitydateformat') == "lapse" ) {
  $createdTime = CTimeHelper::timeLapse($date);
} else {
  $createdTime = $date->format($config->get('profileDateFormat'));
}

// 2. welcome message for new installation
if (isset($freshInstallMsg)) :
    ?>
    <div class="cAlert" style="margin-top: 10px">
        <?php echo $freshInstallMsg; ?>
    </div>
<?php endif; ?>

<?php if(count($activities) == 0 && $actId){ ?>
    <div class="cAlert" style="margin-top: 10px">
        <?php echo JText::_('COM_COMMUNITY_STREAM_CONTENT_UNAVAILABLE'); ?>
    </div>
<?php }?>

<!-- begin new stream -->
<ul class="list-unstyled" data-filter="<?php echo $filter; ?>" data-filter-value="<?php echo $filterValue; ?>" data-filterid="<?php echo $filterId; ?>" data-groupid="<?php $this->groupId; ?>" data-eventid="<?php $this->eventId; ?>" data-profileid="<?php $this->profileId; ?>">

    <?php foreach ($activities as $act): ?>
        <?php if (empty($act->app)) {
            continue;
        } ?>
    <?php
        /* If actor is no longer exists than we do not display it */
        if ( $act->actor != 0 ) {
            $actor = $act->actor;
        } else {
            $actor = $act->params->get('actors');
        }
        $tUser = CFactory::getUser($actor);
        /* User not exists and this's not system activity */
        if ($tUser->_userid == 0 && strpos($act->app,'system.') === false )
            continue;
    ?>
    <?php
        //special case for video in normal post
        $headMetas = $act->params;
        $isVideo = '';
        /* We do convert into JRegistry to make it easier to use */
        if (!empty($headMetas) && $headMetas != '{}') {
            $headMetaParams = json_decode($headMetas);
            $headMetaParams = isset($headMetaParams->headMetas) ? json_decode($headMetaParams->headMetas) : null ;
            if(isset($headMetaParams->type ) && $headMetaParams->type == 'video'){
                $isVideo = 'videos';
            }
        }
        ob_start();
    ?>

        <li id="<?php echo $act->app; ?>-newsfeed-item-<?php echo $act->id; ?>" class="joms-stream post feed-<?php echo (!empty($isVideo)) ? $isVideo : $act->app; ?>" data-streamid="<?php echo $act->id; ?>">
            <?php
            ob_start();
            $this->set('act', $act);
            $act->createdtime = $createdTime;
            // Load ONLY known app
            switch ($act->app) {
                case 'users.featured':
                    $this->load('activities.profile.featured');
                    break;

                case 'profile.avatar.upload':
                case 'profile':
                    $this->load('activities.profile');
                    break;
                case 'profile.status.share':
                    $this->load('activities.profile.status.share');
                    break;
                case 'albums.comment':
                case 'albums':
                    $this->load('activities.albums');
                    break;

                case 'albums.featured':
                case 'photos.comment':
                case 'photos':
                    $this->load('activities.photos');
                    break;

                case 'videos.featured':
                    $this->load('activities.videos.featured');
                    break;
                case 'videos.comment':
                case 'videos':
                    $this->load('activities.videos');
                    break;

                case 'friends.connect':
                    $this->load('activities.friends.connect');
                    break;

                case 'groups.featured':
                case 'groups.wall':
                case 'groups.join':
                case 'groups.bulletin':
                case 'groups.discussion':
                case 'groups.discussion.reply':
                case 'groups.update':
                case 'groups':
                    $this->load('activities.groups');
                    break;
                case 'groups.avatar.upload':
                    $this->load('activities.groups.avatar.upload');
                    break;
                case 'events.featured':
                case 'events.wall':
                case 'events.attend':
                case 'events':
                    $this->load('activities.events');
                    break;
                case 'events.avatar.upload':
                    $this->load('activities.events.avatar.upload');
                    break;
                case 'system.message':
                case 'system.videos.popular':
                case 'system.photos.popular':
                case 'system.members.popular':
                case 'system.photos.total':
                case 'system.groups.popular':
                case 'system.members.registered':
                    $this->load('activities.system');
                    break;

                case 'app.install':
                    $this->load('activities.app.install');
                    break;
                case 'profile.like':
                case 'groups.like':
                case 'events.like':
                case 'photo.like':
                case 'videos.like':
                case 'album.like':
                    $this->load('activities.likes');
                    break;
                case 'cover.upload':
                    $this->load('activities.photos.cover');
                    break;
                default:
                    // If none of the above, only load 3rd party stream data
                    // For some known stream, convert it into new app naming, which is the folder/plugin format
                    // try load the plugin getStreamHTML
                    $appName = explode('.', $act->app);
                    $appName = $appName[0];

                    $plugin = $appLib->getPlugin($appName);

                    if (!is_null($plugin)) {
                        if (method_exists($plugin, 'onCommunityStreamRender')) {
                            $stream = $plugin->onCommunityStreamRender($act);

                            if(!isset($stream->access)){
                              $stream->access = 10;
                            }

                            $this->set('stream', $stream);
                            $this->load('activities.stream');
                        }
                    } else {
                        // Process the old ways
                        $user = CFactory::getUser($act->actor);
                        $actorLink = '<a class="cStream-Author" href="' . CUrlHelper::userLink($user->id) . '">' . $user->getDisplayName() . '</a>';
                        $title = $act->title;

                        // Handle 'single' view exclusively
                        $title = preg_replace('/\{multiple\}(.*)\{\/multiple\}/i', '', $title);
                        $search = array('{single}', '{/single}');
                        $title = CString::str_ireplace($search, '', $title);
                        $title = CString::str_ireplace('{actor}', $actorLink, $title);

                        $stream = new stdClass();
                        $stream->actor = $user;
                        $stream->target = null;
                        $stream->headline = $title;
                        $stream->message = $act->content;
                        $stream->access = $act->access;
                        $stream->attachments = array();

                        $this->set('stream', $stream);
                        $this->load('activities.stream');
                    }

                    break;
            }

            $html = ob_get_contents();
            $html = trim($html);
            $showStream = true;
            ob_end_clean();
            echo $html;

            // Show debug message
            if (empty($html)) {
                // enable only during stream debugging
                // echo 'UNPROCESSED STREAM POST: ' . $act->app;
                $showStream = false;
            }

            if ($my->id > 0) {
                $this->load('activities.stream.options');
            }
            ?>
        </li>

            <?php
            $html = ob_get_contents();
            $html = trim($html);
            ob_end_clean();

            // Only show if there is a content t be shown
            if ($showStream) {
                echo $html;
            }
            ?>

    <?php endforeach; ?>

</ul>

<!-- end new stream -->



<div class="cActivity-LoadMore" id="activity-more">
    <?php if ($showMoreActivity && !CFactory::getConfig()->get('infinitescroll',0)) { ?>
      <a class="more-activity-text btn btn-block" href="javascript:void(0);" onclick="joms.activities.more();"><?php echo JText::_('COM_COMMUNITY_MORE'); ?></a>
    <?php } ?>
    <div class="loading"></div>
</div>
<?php if ($my->id != 0) { ?>
    <script>
        joms.activities.infinitesScrollenable = '<?php echo CFactory::getConfig()->get("infinitescroll",0)?>';
        joms.activities.init();
    </script>
<?php } ?>
<?php if ($config->get('newtab')) { ?>
    <script type="text/javascript">
        joms.jQuery(document).ready(function() {
            joms.jQuery("div.cStream-Headline > a").attr('target', '_blank');
            joms.jQuery("div.joms-stream-content").find('a').attr('target','_blank')/
            joms.jQuery("div.cStream-Headline > span > a").attr('target', '_blank');
            joms.jQuery("div.cStream-Headline > b > a").attr('target', '_blank');
            joms.jQuery("div.cStream-Content > a").attr('target', '_blank');
            joms.jQuery("div.cStream-Attachment .cSnip-Detail > a").attr('target', '_blank');
            joms.jQuery("nav.joms-stream-status-action a").attr('target','');
            joms.jQuery('#activity-stream-container ul li p span.joms-status-location a').attr('target','');
            joms.jQuery('.joms-privacy-dropdown.joms-stream-privacy').find('a').attr('target','')

        });
    </script>
<?php } ?>