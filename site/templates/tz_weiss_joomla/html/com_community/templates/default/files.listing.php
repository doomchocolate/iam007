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
<?php if(!empty($data)){?>
	<?php foreach($data as $_data){?>
		<li class="file_<?php echo $_data->id?>">
			<span class="cIcon <?php echo $_data->type; ?>"></span>
			<div>
				<span class="filename"><a href="javascript:void(0)" onClick ="joms.file.ajaxdownloadFile('group','<?php echo $_data->id?>');"><?php echo $_data->name?></a></span>
				<span class="info"><?php echo ($_data->hits > 1 ) ? JText::sprintf('COM_COMMUNITY_FILE_HIT_PLURAL',$_data->hits) : JText::sprintf('COM_COMMUNITY_FILE_HIT_SINGULAR',$_data->hits); ?> <?php echo $_data->filesize?></span><br>
				<span class="uploaded"><?php echo JText::sprintf('COM_COMMUNITY_FILES_UPLOAD_BY' , $_data->user->getDisplayName() , $_data->parentName , $_data->parentType );?></span>
				<a href="javascript:void(0)" class="cFile-Delete cFloat-R" onclick="joms.file.ajaxDeleteFile('discussion','<?php echo $_data->id?>');return false;"><?php echo JText::_('COM_COMMUNITY_DELETE'); ?></a>
			</div>
		</li>
	<?php }?>
<?php } 
	else 
	{
	?>	
		<span class="noFiles"><?php echo JText::_('COM_COMMUNITY_FILES_NO_FILE'); ?></span>
	<?php
	}
?>