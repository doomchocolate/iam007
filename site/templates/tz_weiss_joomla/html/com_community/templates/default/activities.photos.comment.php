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
$action = $param->get('action');

$user = CFactory::getUser($this->act->actor);

$wall = JTable::getInstance('Wall', 'CTable');
$wall->load($param->get('wallid'));

$photo = JTable::getInstance('Photo','CTable');
$photo->load($act->cid);

$url = $photo->getPhotoLink();

$date = JFactory::getDate($act->created);
if ( $config->get('activitydateformat') == "lapse" ) {
  $createdTime = CTimeHelper::timeLapse($date);
} else {
  $createdTime = $date->format($config->get('profileDateFormat'));
}

$photo_info = $photo->getInfo();
$photo_size = $photo_info['size'];

?>
<div class="joms-stream-avatar">
	<a href="<?php echo CUrlHelper::userLink($user->id); ?>">
		<img class="img-responsive joms-radius-rounded" data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>">
	</a>
</div>

<div class="joms-stream-content">
	<header>
		<a href="<?php echo CUrlHelper::userLink($user->id); ?>"><?php echo $user->getDisplayName(); ?></a>
		<?php echo JText::sprintf('COM_COMMUNITY_ACTIVITIES_WALL_POST_PHOTO', $url, $this->escape($photo->caption)  ); ?>
		<p class="joms-share-meta date">
			<?php echo $createdTime; ?>
		</p>
	</header>
		<?php $comment = JHTML::_('string.truncate', $wall->comment, $config->getInt('streamcontentlength') );?>
		<p><?php echo CActivities::format($comment); ?></p>

		<div class="joms-stream-single-photo <?php echo $photo_size; ?>">
			<a href="<?php echo $url; ?>"><img src="<?php echo $photo->getImageURI(); ?>" /></a>
		</div>

	<?php
	// No comment on photo comment
	$this->load('activities.actions');
	?>
</div>
