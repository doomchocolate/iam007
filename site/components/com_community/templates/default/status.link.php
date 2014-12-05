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

joms.status.Creator['link'] =
{
	attachment: [],

	focus: function()
	{
		this.Message.defaultValue("<?php echo JText::_('COM_COMMUNITY_STATUS_LINK_HINT'); ?>", 'hint');
	},

	getAttachment: function()
	{
		return { type: 'link' };
	}
}

})(joms.jQuery);

//]]>
</script>

<div class="creator-view type-link">
	<ul class="creator-content"></ul>

	<div class="creator-form">
		<table class="formtable" cellspacing="1" cellpadding="0">
			<tr>
				<td>
					<label for="linkURL" class="label title">
						*<?php echo JText::_('COM_COMMUNITY_LINK_URL');?>
					</label>
				</td>
				<td class="value">
					<input type="text" id="linkUrl" name="linkUrl" class="required" value="" />
				</td>
				<td>
					<button class="button"><?php echo JText::_('COM_COMMUNITY_ADD_LINK'); ?></button>
				</td>
			</tr>
		</table>

		<div class="creator-content-action">
			<a class="icon-add" href=""><?php echo JText::_('COM_COMMUNITY_ADD_PHOTO'); ?></a>
		</div>

		<div class="creator-message">
			<textarea></textarea>
		</div>
	</div>
</div>