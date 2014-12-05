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

if( !empty( $apps ) )
{
?>


<?php
	foreach( $apps as $app )
	{
?>
	<?php if ($itemType=='edit') { ?>
		<div class="app-item <?php if ($app->isCoreApp) echo 'app-core'; ?> app-item-edit" id="app-<?php echo $app->id; ?>">
			<?php if( !$app->isCoreApp ) { ?>
			<a class="app-action-remove" href="javascript:void(0);" onclick="joms.apps.windowTitle= '<?php echo JText::_('COM_COMMUNITY_APPS_AJAX_REMOVED');?>';joms.apps.remove('<?php echo $app->id; ?>');" title="<?php echo JText::_('COM_COMMUNITY_APPS_LIST_REMOVE'); ?>">
				<i class="com-icon-block"></i>
			</a>
			<?php } ?>
		
			<img src="<?php echo $app->favicon['16']; ?>" alt="<?php echo $this->escape($app->title); ?>" class="app-favicon" />
			<b class="app-title"><?php echo $this->escape($app->title); ?></b>
			<div class="app-actions">
				<a href="javascript:void(0);" onclick="joms.apps.showAboutWindow('<?php echo $app->name; ?>');" title="<?php echo JText::_('COM_COMMUNITY_APPS_LIST_ABOUT'); ?>">
					<i class="com-icon-info"></i>
				</a>
				<a href="javascript:void(0);" onclick="joms.apps.showSettingsWindow('<?php echo $app->id; ?>','<?php echo $app->name; ?>');" title="<?php echo JText::_('COM_COMMUNITY_APPS_COLUMN_SETTINGS'); ?>">
					<i class="com-icon-cog"></i>
				</a>
				<a href="javascript:void(0);" onclick="joms.apps.showPrivacyWindow('<?php echo $app->name; ?>');" title="<?php echo JText::_('COM_COMMUNITY_APPS_COLUMN_PRIVACY'); ?>">
					<i class="com-icon-lock-open"></i>
				</a>
			</div>
		</div>
	<?php } ?>
	
	<?php if ($itemType=='browse') { ?>
		<div class="cApp-Item Browse <?php echo $this->escape($app->name); ?>">
			<img width="50" src="<?php echo $app->favicon['64']; ?>" alt="<?php echo $this->escape($app->title); ?>" class="pull-left" />
			<div class="cApp-Content clearfix">
				<a class="btn pull-right" href="javascript:void(0);" onclick="joms.editLayout.addApp('<?php echo $this->escape($app->name); ?>', '<?php echo $app->position; ?>');">
					<?php echo JText::_('COM_COMMUNITY_APPS_LIST_ADD'); ?>
				</a>

				<div class="cApp-Title">
					<b><?php echo $this->escape($app->title); ?></b>
				</div>
				<div class="cApp-Description"><?php echo $this->escape($app->description); ?></div>
			</div>
		</div>
	<?php } ?>
<?php
	}
?>

<?php
}
else
{
?>
<div class="cEmpty cAlert"><?php echo JText::_('COM_COMMUNITY_NO_MORE_APPS_TO_BE_ADDED');?></div>
<?php
}
?>