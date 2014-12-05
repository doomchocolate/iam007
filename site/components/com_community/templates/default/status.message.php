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
<script type="text/javascript">
//<![CDATA[

(function($) {

joms.status.Creator['message'] =
{
	reset: function(){
		joms.sharebox.shareStatus.reset();
		joms.jQuery('.joms-postbox-fetch').parent().remove();
	},
	getAttachment: function()
	{
		return { type: 'message' };
	}
};

})(joms.jQuery);

//]]>
</script>
<!-- Post write panel -->
<div id="joms-message-panel" class="joms-postbox-panel joms-tab-panel active">

	<div class="joms-postbox-element last clearfix">
		<figure class="joms-postbox-avatar">
			<img class="img-responsive joms-radius-rounded" src="<?php echo $my->getThumbAvatar(); ?>" alt="">
	  </figure>

		<div class="joms-postbox-field joms-textarea-bubble joms-postbox-overflow">

			<textarea data-minlength="0" data-maxlength="<?php echo CFactory::getConfig()->get('statusmaxchar');?>"  id="joms-message-status" placeholder="<?php echo JText::_('COM_COMMUNITY_STATUS_MESSAGE_HINT'); ?>" class="joms-postbox-status creator-message joms-radius-normal" name="joms-message-status"></textarea>

		</div>
	</div>
	<div class="joms-postbox-element last clearfix">
		<?php if(CFactory::getConfig()->get('streamlocation',0)) { ?>
			<div class="joms-postbox-field joms-postbox-location hidden">
				<div class="joms-postbox-map">
					<input type="text" class="joms-status-location typeahead" name="joms-status-location" placeholder="Enter your location here..." />
					<div class="joms-remove-attachment joms-location-cancel">
						<i class="joms-icon-remove"></i>
					</div>
					<div class="joms-location-add">
						<i class="joms-icon-map-marker"></i>
						add
					</div>
				</div>
			</div>
		<?php }?>

		<div class="joms-postbox-field joms-postbox-attach joms-postbox-overflow">
			<?php if(CFactory::getConfig()->get('streamlocation',0)) { ?>
				<button class="btn joms-postbox-location-button" data-default-string="Location"><i class="joms-icon-map-marker"></i> Location</button>
			<?php }?>
			<div class="btn-group joms-mood-dropdown">
				<button class="btn dropdown-toggle" data-toggle="dropdown" id="joms-postbox-mood" data-default-string="Mood"><i class="joms-icon-smiley"></i> Mood</button>
				<ul class="dropdown-menu">
					<li><a data-mood="no-mood" href="#"><i class="joms-icon-remove" style="color:#c83030;"></i>Remove Mood</a></li>
					<li class="divider"></li>
					<li><a data-mood="happy" href="#"><i class="joms-emoticon joms-emo-happy"></i> Happy</a></li>
					<li><a data-mood="sad" href="#"><i class="joms-emoticon joms-emo-sad"></i> Sad</a></li>
					<li><a data-mood="excited" href="#"><i class="joms-emoticon joms-emo-excited"></i> Excited</a></li>
					<li><a data-mood="tired" href="#"><i class="joms-emoticon joms-emo-sleepy"></i> Tired</a></li>
					<li><a data-mood="great" href="#"><i class="joms-emoticon joms-emo-laugh"></i> Great</a></li>
					<li><a data-mood="bored" href="#"><i class="joms-emoticon joms-emo-bored"></i> Bored</a></li>
					<li><a data-mood="loved" href="#"><i class="joms-emoticon joms-emo-in-love"></i> Loved</a></li>
					<li><a data-mood="angry" href="#"><i class="joms-emoticon joms-emo-angry"></i> Angry</a></li>
					<li><a data-mood="sick" href="#"><i class="joms-emoticon joms-emo-sick"></i> Sick</a></li>
					<li><a data-mood="blessed" href="#"><i class="joms-emoticon joms-emo-angel"></i> Blessed</a></li>
					<li><a data-mood="depressed" href="#"><i class="joms-emoticon joms-emo-upset"></i> Depressed</a></li>
					<li><a data-mood="sleepy" href="#"><i class="joms-emoticon joms-emo-sleeping"></i> Sleepy</a></li>
					<li><a data-mood="wonderful" href="#"><i class="joms-emoticon joms-emo-sunglass"></i> Wonderful</a></li>
					<li><a data-mood="surprised" href="#"><i class="joms-emoticon joms-emo-surprised"></i> Surprised</a></li>
					<li><a data-mood="shocked" href="#"><i class="joms-emoticon joms-emo-shocked"></i> Shocked</a></li>
				</ul>
				<input type="hidden" class="joms-status-mood" name="joms-status-mood" />
			</div>



		</div>

	</div>
</div>
<!--
<div class="creator-view type-message">
<div class="creator-hint"></div>
</div>
-->