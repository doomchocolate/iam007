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
if( $applications )
{
	foreach( $applications as $application )
	{
?>
		
<div class="app-item <?php echo $this->escape($application->name); ?>">

	<div class="app-avatar">
		<img src="<?php echo $application->appFavicon; ?>" alt="<?php echo $this->escape($application->title); ?>" />
	</div>

	<h3><?php echo $application->title; ?></h3>
	
	<div class="app-item-description">
		<?php echo $this->escape($application->description); ?>
	</div>
	
	<div class="app-item-details">
		<span style="margin-right: 10px;"><?php echo JText::_('COM_COMMUNITY_APPS_COLUMN_DATE'); ?>: <strong><?php echo $application->creationDate; ?></strong></span>
		<span style="margin-right: 10px;"><?php echo JText::_('COM_COMMUNITY_APPS_COLUMN_VERSION'); ?>: <strong><?php echo $application->version; ?></strong></span>
		<?php if($this->params->get('appsShowAuthor')) { ?>
			<span><?php echo JText::_('COM_COMMUNITY_APPS_COLUMN_AUTHOR'); ?>: <strong><?php echo $application->author; ?></strong></span>
		<?php } ?>	
	</div>
	
	<?php if( !$application->added && !$application->coreapp ) { ?>
		<a class="added-button" href="javascript:void(0);" onclick="joms.apps.add('<?php echo $this->escape($application->name); ?>')" title="<?php echo JText::_('COM_COMMUNITY_APPS_ADD_BUTTON'); ?>">
		   	<?php echo JText::_('COM_COMMUNITY_APPS_LIST_ADD'); ?>
	   	</a>	
	<?php } else { ?>
	
	<span class="added-ribbon">
	   	<?php echo JText::_('COM_COMMUNITY_APPS_LIST_ADDED'); ?>
	</span>	
	
	<?php } ?>
</div>	

<?php 
	}
}
else
{
?>
<div class="app-item">
	<div class="app-item-description"><?php echo JText::_('COM_COMMUNITY_NO_APPLICATIONS_INSTALLED');?></div>
</div>
<?php
}
?>


<?php 
if ( $pagination->getPagesLinks() ) 
{ 
?>
<div class="cPagination">
	<?php echo $pagination->getPagesLinks(); ?>
</div>
<?php 
} 
?>