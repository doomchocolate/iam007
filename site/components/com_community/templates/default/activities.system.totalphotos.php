<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
$photosModel = CFactory::getModel( 'photos' );
$total       = $photosModel->getTotalSitePhotos();
?>

<div class="joms-stream-box joms-responsive clearfix " style="margin-bottom:0px;">

  <aside>
      <i class="joms-icon-bullhorn joms-icon-thumbnail"></i>
  </aside>

  <article>
    <a href="javascript:void(0);">
      <i class="joms-icon-bullhorn portrait-phone-only"></i> <?php echo JText::_('COM_COMMUNITY_ACTIVITIES_TOTAL_PHOTOS'); ?>
    </a>
    <div class="separator"></div>
    <p><?php echo JText::sprintf('COM_COMMUNITY_TOTAL_PHOTOS_ACTIVITY_TITLE', CRoute::_('index.php?option=com_community&view=photos') ,$total); ?></p>
  </article>

</div>

