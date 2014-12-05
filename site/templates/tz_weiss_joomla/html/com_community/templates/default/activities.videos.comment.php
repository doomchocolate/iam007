<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Load params
$param = $act->params;
$user = CFactory::getUser($this->act->actor);
$wall = JTable::getInstance('Wall', 'CTable');
$wall->load($param->get('wallid'));

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
	<header>
		<a href="<?php echo CUrlHelper::userLink($user->id); ?>"><?php echo $user->getDisplayName(); ?></a>
		<?php echo JText::sprintf('COM_COMMUNITY_VIDEOS_ACTIVITIES_WALL_POST_VIDEO', $this->video->getViewURI(), $this->escape($this->video->title) ); ?>
		<p class="joms-share-meta date">
			<?php echo $createdTime; ?>
		</p>
	</header>

	<?php $comment = JHTML::_('string.truncate', $wall->comment, $config->getInt('streamcontentlength') ); ?>
	<p><?php echo CActivities::format($comment); ?></p>


  <div class="joms-stream-box joms-fetch-wrapper clearfix" >
    <div class="row-fluid">
        <div class="span4">
            <a href="javascript:joms.walls.showVideoWindow('<?php echo $video->id; ?>')" class="cVideo-Thumb">
                <div>
                    <img src="<?php echo $video->getThumbnail(); ?>"
                      alt="<?php echo $this->escape($video->title); ?>"
                     />
                    <b><?php echo CVideosHelper::toNiceHMS(CVideosHelper::formatDuration($video->getDuration())); ?></b>
                </div>
            </a>
        </div>
        <div class="span8">
            <article class="joms-stream-fetch-content" style="margin-left:0; padding-top:0">
                <a href="javascript:joms.walls.showVideoWindow('<?php echo $video->id; ?>')"><?php echo $video->title; ?></a>
                <div class="separator"></div>
                <p class="reset-gap">
                    <?php echo JHTML::_('string.truncate', $video->description, $config->getInt('streamcontentlength'), true, false); ?>
                </p>
            </article>
        </div>
    </div>
  </div>


	<?php
	// No comment on video comment
	$this->load('activities.actions');
	?>
</div>
