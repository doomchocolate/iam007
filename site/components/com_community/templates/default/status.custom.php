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

joms.status.Creator['custom'] = 
{
	attachment: [],

	focus: function()
	{
		this.Message.parent().hide();
		this.Privacy.parent().hide();
		this.ShareButton.hide();
	},

	blur: function()
	{
		this.Message.parent().show();
		this.Privacy.parent().show();
		this.ShareButton.show();
	},

	getAttachment: function()
	{
		return { type: 'custom' };
	}
}

})(joms.jQuery);

//]]>
</script>

<!-- =========================== admin only =========================== -->
<div id="joms-custom-panel" class="joms-postbox-panel joms-tab-panel">
	<div class="joms-postbox-element clearfix">
		<figure class="joms-postbox-avatar">
	      <img class="img-responsive joms-radius-rounded" src="<?php echo $my->getThumbAvatar(); ?>" alt="">
	    </figure>
	    
		<div class="joms-postbox-field clearfix">
			<label for="joms-custom-message-opt-predefined" class="joms-radio">
				<input id="joms-custom-message-opt-predefined" type="radio" data-toggle="radio" name="joms-custom-message-opt" checked value="predefined" />
				<strong>Select a Predefined Message</strong>
			</label>
			<select name="joms-custom-panel-option" class="wide">
				<option value="system.registered">Last 10 registered users</option>
				<option value="system.populargroup">Most popular group to date.</option>
				<option value="system.totalphotos">Total photos uploaded to the site.</option>
				<option value="system.popularprofiles">Most popular users on the site.</option>
				<option value="system.popularphotos">Check out the most popular photos on this site.</option>
				<option value="system.popularvideos">Check out the most popular videos on this site.</option>
			</select>
			<br class="joms-clearline"/>
			<label for="joms-custom-message-opt-custom" class="joms-radio">
				<input id="joms-custom-message-opt-custom" type="radio" data-toggle="radio" name="joms-custom-message-opt" value="write" />
				<strong>Write a Custom Message</strong>
			</label>
			<textarea data-minlength="0" data-maxlength="<?php echo CFactory::getConfig()->get('statusmaxchar');?>" id="joms-custom-panel-text" class="joms-postbox-status input creator-message textarea" style="display: block;" rows="5" cols="45" name="joms-custom-text">
			</textarea>
		</div>

	</div>
</div>
<!-- =========================== end admin only ======================= -->

<!--
<div class="creator-view type-custom">

	<div class="creator-form align-inherit">
		<form id="activities-custom-message" name="activities-custom-message" method="post" action="" class="cForm reset-gap">
			<strong><?php echo JText::_('COM_COMMUNITY_ACTIVITES_CUSTOM_MESSAGES' );?></strong>
			<ul class="cFormList cResetList">
				<li>
					<label class="label-radio" for="custom-predefined-message">
						<input type="radio" name="custom-message" class="input radio" id="custom-predefined-message" value="predefined" onclick="joms.activities.selectCustom('predefined');" checked="checked"/>
						<?php echo JText::_('COM_COMMUNITY_SELECT_PREDEFINED_MESSAGES');?>						
					</label>
					<label class="label-radio">
						<select name="custom-predefined" id="custom-predefined" class="input-block-level">
							<?php
							foreach( $customActivities as $key => $message )
							{
							?>
							<option value="<?php echo $key;?>"><?php echo $message; ?></option>
							<?php
							}
							?>
						</select>
					</label>
				</li>
				<li>
					<label class="label-radio" for="custom-text-message">
						<input type="radio" class="input radio" name="custom-message" id="custom-text-message" value="text" onclick="joms.activities.selectCustom('text');" />
						<?php echo JText::_('COM_COMMUNITY_WRITE_A_CUSTOM_MESSAGE');?>
					</label>
					<label class="label-radio">
						<textarea name="custom-text" id="custom-text" cols="45" rows="5" style="display: none;" class="input textarea"></textarea>
					</label>
				</li>
				<li>
					<input type="button" class="btn btn-primary top-gap" name="button" id="button" value="<?php echo JText::_('COM_COMMUNITY_POST_IT');?>" onclick="joms.activities.addCustom()" />
				</li>
			</ul>
		</form>
	</div>

</div>
-->