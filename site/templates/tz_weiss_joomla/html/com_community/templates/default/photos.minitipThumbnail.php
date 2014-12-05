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

<div id="thumbnail-container">
	<?php //var_dump($thumbnails);
		foreach ($thumbnails as $num => $thumbnail_data):
			$photo = JTable::getInstance('Photo' , 'CTable');
			$photo->bind($thumbnail_data);
	?>
	<span>
		<img name="photo_thumbnail<?php echo $num;?>" width="40" height="40" src="<?php echo $photo->getThumbURI(); ?>"/>
	</span>
	<?php endforeach; ?>
	<br class="clear" />
</div>	