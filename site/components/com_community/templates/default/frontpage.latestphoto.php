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
<h3 class="app-box-header">
	<?php echo JText::_('COM_COMMUNITY_PHOTOS_NEW_PHOTOS'); ?>
</h3>
<div class="app-box-content">
	
	<?php
	if( $latestPhotos )
	{
	?>
	<ul class="cThumbsList cResetList clearfix">	
	<?php
		for( $i = 0 ; $i < count( $latestPhotos ); $i++ )
		{
			$row	=& $latestPhotos[$i];
	?>
	<li>
	<a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $row->albumid .  '&userid=' . $row->user->id . '&photoid=' . $row->id);?>">
		<img class="cAvatar cMediaAvatar jomNameTips" title="<?php echo JText::sprintf('COM_COMMUNITY_PHOTOS_UPLOADED_BY' , $row->user->getDisplayName() );?>" src="<?php echo $row->getThumbURI(); ?>" alt="<?php echo $this->escape( $row->user->getDisplayName() );?>" />
	</a>
	</li>
	<?php
		}
	?>
	</ul>
	<?php
	}
	else
	{
	?>
	<div class="cEmpty"><?php echo JText::_('COM_COMMUNITY_PHOTOS_NO_PHOTOS_UPLOADED');?></div>
	<?php } ?>
</div>

<div class="app-box-footer">
	<a href="<?php echo CRoute::_('index.php?option=com_community&view=photos'); ?>"><?php echo JText::_('COM_COMMUNITY_FRONTPAGE_VIEW_ALL_PHOTOS'); ?></a>
</div>