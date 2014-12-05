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

// Load params
$param = $act->params;
$user = CFactory::getUser($this->act->actor);
$album	= JTable::getInstance( 'Album' , 'CTable' );
$album->load( $act->cid );
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

<?php

?>
<div class="joms-stream-content">
	<header>
		<a href="<?php echo CUrlHelper::userLink($user->id); ?>"><?php echo $user->getDisplayName(); ?></a>
		<?php echo JText::sprintf('COM_COMMUNITY_ACTIVITIES_WALL_POST_ALBUM', CRoute::_($album->getURI()), $this->escape($album->name) ); ?>
    <p class="joms-share-meta date">
      <?php echo $createdTime; ?>
    </p>
    <p class="reset-gap"><?php echo CActivities::format( JHTML::_('string.truncate', $wall->comment, $config->getInt('streamcontentlength') ) );?></p>
	</header>

	<div class="joms-stream-box joms-responsive joms-fetch-wrapper clearfix">
      <div class="row-fluid">
        <div class="span3">
          <a href="<?php echo $album->getURI(); ?>">
            <img src="<?php echo $album->getCoverThumbURI();?>" />
          </a>
        </div>
        <div class="span9 joms-stream-fetch-content">
          <a href="<?php echo $album->getURI(); ?>"><span class="joms-stream-fetch-title"><?php echo $album->name; ?></span></a>
          <div class="separator"></div>
          <p class="reset-gap"><?php echo $album->description; ?></p>
        </div>
      </div>
    </div>
</div>
