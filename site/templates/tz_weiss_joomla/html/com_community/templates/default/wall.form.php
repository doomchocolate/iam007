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
<script type="text/javascript" language="javascript">

function wallRemove( id )
{
	if(confirm('<?php echo JText::_('COM_COMMUNITY_WALL_CONFIRM_REMOVE'); ?>'))
	{		
		joms.jQuery('#wall_'+id).fadeOut('normal').remove();
		if(typeof getCacheId == 'function') {
			cache_id = getCacheId();
		}else{
			cache_id = "";
		}	
		jax.call('community','<?php echo $ajaxRemoveFunc; ?>', id, cache_id );
	}
}

joms.jQuery(document).ready(function(){
	//joms.utils.textAreaWidth('#wall-message');
	joms.utils.autogrow('#wall-message');
});
</script>

<div class="cComment-Avatar cFloat-L">
	<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id );?>"><img class="avatar" alt="" src="<?php echo $my->getThumbAvatar()?>"></a>
</div>

<div class="cComment-Body">
	
	<div class="cComment-Input">
		<textarea id="wall-message" name="message" class="textarea input-block-level"></textarea>
	</div>

	<div class="cComment-Actions">
		<button id="wall-submit" class="btn btn-primary" onclick="joms.walls.add('<?php echo $uniqueId; ?>', '<?php echo $ajaxAddFunction;?>');return false;" name="save">
			<?php echo JText::_('COM_COMMUNITY_WALL_ADD_COMMENT');?>
		</button>
		<div style="position:absolute; left:0; top:0; display:none;" id="wall-message-counter"></div>
		<a name="app-walls" href="javascript:void(0);"></a>
	</div>
</div>
