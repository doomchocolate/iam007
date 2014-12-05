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

if ($this->act->app == 'groups.like') {
  $this->load('activities.groups.like');
} else if ($this->act->app == 'events.like') {
  $this->load('activities.events.like');
} else if ($this->act->app == 'album.like') {
  $this->load('activities.album.like');
} else if ($this->act->app == 'profile.like') {
  $this->load('activities.profile.like');
} else if ($this->act->app == 'videos.like') {
  $this->load('activities.videos.like');
} else { ?>

<div class="joms-stream-content">
    <p><i class="joms-icon-users"></i>
    <?php echo CLikesHelper::generateHTML($act, $likedContent); ?></p>
    <?php if ($likedContent !== null) { ?>
        <div class="liked-content">
            <a href="<?php echo $likedContent->url_link; ?>"><img class="joms-stream-single-photo" src="<?php echo $likedContent->thumb; ?>" /></a>
        </div>
    <?php } ?>
</div>

<?php } ?>