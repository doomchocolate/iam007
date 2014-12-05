<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
?>
<div id="photo-<?php echo $photo->id; ?>" class="compose-photo">
	<img src="<?php echo JURI::base().$photo->thumbnail; ?>" alt="" />
	<a class="btn btn-small creator-change-photo" href="javascript: void(0);"><?php echo JText::_('COM_COMMUNITY_PHOTOS_CHANGE'); ?></a>
</div>