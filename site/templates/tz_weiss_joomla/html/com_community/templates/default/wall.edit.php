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
<textarea id="wall-edit-<?php echo $id;?>" name="message" style="width: 95%; margin-bottom: 5px"><?php echo $message; ?></textarea>
<input type="button" class="btn btn-small" value="<?php echo JText::_('COM_COMMUNITY_CANCEL');?>" onclick="joms.walls.edit('<?php echo $id;?>' , '<?php echo $editableFunc;?>');" />
<input type="button" class="btn btn-small btn-primary" value="<?php echo JText::_('COM_COMMUNITY_SAVE');?>"   onclick="joms.walls.save('<?php echo $id;?>' , '<?php echo $editableFunc;?>');" />
