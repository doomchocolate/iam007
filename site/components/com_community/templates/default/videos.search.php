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



<div class="Videos">
	<form name="searchVideo" action="<?php echo CRoute::getURI(); ?>" method="get" class="form-horizontal">


		<div class="input-append">
		  <input type="text" id="q" class="input-large" name="search-text" placeholder="<?php echo JText::_('COM_COMMUNITY_SEARCH_VIDEO_PLACEHOLDER');?>" />
			<input type="submit" name="search" class="btn btn-primary" value="<?php echo JText::_('COM_COMMUNITY_SEARCH_BUTTON_TEMP');?>"/>
		</div>

		<input type="hidden" name="Itemid" value="<?php echo CRoute::getItemId(); ?>" />
		<input type="hidden" name="option" value="com_community" />
		<input type="hidden" name="task" value="search" />
		<input type="hidden" name="view" value="videos" />
	</form>
	
	<?php
	if( !empty($search) )
	{
	?>
	<div class="cSearch-Result">
		<p>
			<span>
				<?php echo JText::sprintf( 'COM_COMMUNITY_SEARCH_RESULT' , $search ); ?>
			</span>
			<span class="cFloat-R">
				<?php echo JText::sprintf( (CStringHelper::isPlural($videosCount)) ? 'COM_COMMUNITY_VIDEOS_SEARCH_RESULT_TOTAL_MANY' : 'COM_COMMUNITY_VIDEOS_SEARCH_RESULT_TOTAL' , $videosCount ); ?>
			</span>
		</p>
		
		<?php echo $videosHTML; ?>

		<?php 
		if ( !empty($pagination) ) 
		{
		?>
		<div class="cPagination">
			<?php echo $pagination; ?>
		</div>
		<?php
		}
		?>
	</div>	
	<?php
	}
	?>

	<div class="cSearch-Jumper">
	<?php
	echo JText::_('COM_COMMUNITY_SEARCH_FOR');

	foreach ($searchLinks as $key => $value) 
	{
	?>
		<a href="<?php echo $value; ?>"><?php echo $key; ?></a>
	<?php
	}
	?>
	</div>
</div>