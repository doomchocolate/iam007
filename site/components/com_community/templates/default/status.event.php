<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
JHtml::_('behavior.framework', true);
?>
<script type="text/javascript">
//<![CDATA[

(function($) {

var Creator;

joms.status.Creator['event'] =
{
	attachment: {},
	initialize: function()
	{
		Creator = this;

		Creator.Form = Creator.View.find('.creator-form');

		Creator.Hint = Creator.View.find('.creator-hint');
	},


	/*focus: function()
	{
		this.Message.defaultValue("<?php echo JText::_('COM_COMMUNITY_STATUS_EVENT_HINT'); ?>", 'hint');

		Creator.Privacy.parent().hide();
	},*/

	blur: function()
	{
		Creator.Privacy.parent().show();
	},

	getAttachment: function()
	{
		var attachment = Creator.Form.serializeJSON();

		attachment.type = 'event';

		return attachment;
	},

	submit: function()
	{
		return true; // Let server-side do all validation work
	},

	reset: function()
	{
		Creator.Form[0].reset();
		toggleEventDateTime();
		toggleEventRepeat();
	},

	error: function(message)
	{
		if ($.trim(message).length>0)
		{
			Creator.Hint
				.html(message)
				.show();
		}
	},
	success: function(message)
	{
		Creator.Hint
				.html(message)
				.show()
				.fadeOut(5000);
		Creator.reset();
	}
}

})(joms.jQuery);

//]]>
</script>

<!-- Post event panel -->
<div id="joms-event-panel" class="joms-postbox-panel joms-tab-panel">
	<form class="creator-form align-inherit reset-gap">
	<div class="joms-postbox-element clearfix">
		<figure class="joms-postbox-avatar">
	      <img class="img-responsive joms-radius-rounded" src="<?php echo $my->getThumbAvatar(); ?>" alt="">
	    </figure>
		<div class="joms-postbox-field joms-textarea-bubble">
			<textarea data-minlength="0" data-maxlength="<?php echo CFactory::getConfig()->get('statusmaxchar');?>" id="joms-event-status" placeholder="<?php echo JText::_('COM_COMMUNITY_STATUS_EVENT_HINT'); ?>" class="joms-postbox-status joms-radius-normal creator-message " name="joms-event-status"></textarea>
		</div>
	</div>
	<div class="creator-hint alert alert-danger" style="display:none;"></div>
	<div class="joms-postbox-element">
		<label for="joms-event-title" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_TITLE_LABEL'); ?>"><span class="joms-i"><i class="joms-icon-quotes-left"></i></span></label>
		<div class="joms-postbox-field">
			<input type="text" placeholder="<?php echo JText::_('COM_COMMUNITY_EVENTS_TITLE_LABEL'); ?>" class="wide required jomNameTips" id="joms-event-title" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_TITLE_TIPS'); ?>" name="title" />
		</div>
	</div>

	<div class="joms-postbox-element">
		<label for="joms-event-category" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_CATEGORY'); ?>"><span class="joms-i"><i class="joms-icon-tags"></i></span></label>
		<div class="joms-postbox-field">
			<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_CATEGORY_TIPS');?>"><?php echo $lists['categoryid']; ?></span>
		</div>
	</div>

	<div class="joms-postbox-element">
		<label><span class="joms-i"><i class="joms-icon-clock"></i></span></label>
		<div class="joms-postbox-field clearfix">
			<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_START_TIME_TIPS'); ?>">
				<input type="text" class="joms-datepicker" readonly name="startdate" id="joms-event-start-date" />
				<span id="start-time">
					<?php echo $startHourSelect; ?>
					<span class="joms-time-separator">:</span>
					<?php  echo $startMinSelect; ?> <?php echo $startAmPmSelect;?>
				</span>
			</span>
		</div>
	</div>

	<div class="joms-postbox-element">
		<label><span class="joms-i"><i class="joms-icon-clock2"></i></span></label>
		<div class="joms-postbox-field clearfix">
			<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_END_TIME_TIPS'); ?>">
				<input type="text" class="joms-datepicker" readonly name="enddate" id="joms-event-end-date" />
				<span id="end-time">
					<?php echo $endHourSelect; ?>
					<span class="joms-time-separator">:</span>
					<?php echo $endMinSelect; ?> <?php echo $endAmPmSelect;?>
				</span>
			</span>
		</div>
	</div>

	<div class="joms-postbox-element">
		<label><span class="joms-i"><i class="joms-icon-map-marker"></i></span></label>
		<div class="joms-postbox-field clearfix">
			<input placeholder="<?php echo JText::_('COM_COMMUNITY_EVENTS_LOCATION'); ?> <?php echo JText::_('COM_COMMUNITY_EVENTS_LOCATION_DESCRIPTION');?>" type="text" class="wide required jomNameTips" name="location" id="joms-event-location" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_LOCATION_TIPS'); ?>" />
		</div>
	</div>

	<div class="joms-postbox-element">
		<label><span class="joms-i"><i class="joms-icon-globe"></i></span></label>
		<div class="joms-postbox-field clearfix">
			<select name="timezone" id="joms-event-timezone" class="wide required jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_TIMEZONE_TIPS'); ?>">
				<option value="-12:00">(GMT -12:00) Eniwetok, Kwajalein</option>
				<option value="-11:00">(GMT -11:00) Midway Island, Samoa</option>
				<option value="-10:00">(GMT -10:00) Hawaii</option>
				<option value="-09:50">(GMT -9:30) Taiohae</option>
				<option value="-09:00">(GMT -9:00) Alaska</option>
				<option value="-08:00">(GMT -8:00) Pacific Time (US &amp; Canada)</option>
				<option value="-07:00">(GMT -7:00) Mountain Time (US &amp; Canada)</option>
				<option value="-06:00">(GMT -6:00) Central Time (US &amp; Canada), Mexico City</option>
				<option value="-05:00">(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima</option>
				<option value="-04:50">(GMT -4:30) Caracas</option>
				<option value="-04:00">(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz</option>
				<option value="-03:50">(GMT -3:30) Newfoundland</option>
				<option value="-03:00">(GMT -3:00) Brazil, Buenos Aires, Georgetown</option>
				<option value="-02:00">(GMT -2:00) Mid-Atlantic</option>
				<option value="-01:00">(GMT -1:00) Azores, Cape Verde Islands</option>
				<option value="+00:00" selected="selected">(GMT) Western Europe Time, London, Lisbon, Casablanca</option>
				<option value="+01:00">(GMT +1:00) Brussels, Copenhagen, Madrid, Paris</option>
				<option value="+02:00">(GMT +2:00) Kaliningrad, South Africa</option>
				<option value="+03:00">(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg</option>
				<option value="+03:50">(GMT +3:30) Tehran</option>
				<option value="+04:00">(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi</option>
				<option value="+04:50">(GMT +4:30) Kabul</option>
				<option value="+05:00">(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent</option>
				<option value="+05:50">(GMT +5:30) Bombay, Calcutta, Madras, New Delhi</option>
				<option value="+05:75">(GMT +5:45) Kathmandu, Pokhara</option>
				<option value="+06:00">(GMT +6:00) Almaty, Dhaka, Colombo</option>
				<option value="+06:50">(GMT +6:30) Yangon, Mandalay</option>
				<option value="+07:00">(GMT +7:00) Bangkok, Hanoi, Jakarta</option>
				<option value="+08:00">(GMT +8:00) Beijing, Perth, Singapore, Hong Kong</option>
				<option value="+08:75">(GMT +8:45) Eucla</option>
				<option value="+09:00">(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk</option>
				<option value="+09:50">(GMT +9:30) Adelaide, Darwin</option>
				<option value="+10:00">(GMT +10:00) Eastern Australia, Guam, Vladivostok</option>
				<option value="+10:50">(GMT +10:30) Lord Howe Island</option>
				<option value="+11:00">(GMT +11:00) Magadan, Solomon Islands, New Caledonia</option>
				<option value="+11:50">(GMT +11:30) Norfolk Island</option>
				<option value="+12:00">(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka</option>
				<option value="+12:75">(GMT +12:45) Chatham Islands</option>
				<option value="+13:00">(GMT +13:00) Apia, Nukualofa</option>
				<option value="+14:00">(GMT +14:00) Line Islands, Tokelau</option>
			</select>
		</div>
	</div>

	<?php if (false) { // disable all-day option ?>
	<div class="joms-postbox-element">
		<div class="joms-postbox-field clearfix">
			<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_ALL_DAY_TIPS');?>" style="display: inline-block">
				<label class="joms-checkbox" for="joms-event-all-day">
				<input placeholder="<?php echo JText::_('COM_COMMUNITY_EVENTS_ALL_DAY'); ?>" type="checkbox" id="joms-event-all-day" data-toggle="checkbox" name="allday" /> <strong><?php echo JText::_('COM_COMMUNITY_EVENTS_ALL_DAY'); ?></strong></label>
			</span>
		</div>
	</div>
	<?php } ?>

	<?php if ($enableRepeat) { ?>
		<div class="joms-postbox-element last">
		<label for="repeat" class="form-label" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT'); ?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT'); ?></label>
		<div class="form-field joms-postbox-field clearfix">
			<span class="jomNameTips" original-title="<?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_TIPS'); ?>">
			<span id="repeatcontent"></span>
			<select name="repeat" id="repeat" onChange="toggleEventRepeat()" class="input select">
				<option value=""><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_NONE'); ?></option>
				<option value="daily"><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_DAILY'); ?></option>
				<option value="weekly"><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_WEEKLY'); ?></option>
				<option value="monthly"><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_MONTHLY'); ?></option>
			</select>
			</span>

			<span id="repeatendinput">
			<span class="label">&nbsp;&nbsp;*<?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_END'); ?>&nbsp;</span>
			<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_END_TIPS'); ?>">

                                    <input type="text" name="repeatend" id="repeatend" style="width:auto;" size="10" class="input-medium" readonly/>
                                        <script>
                                                joms.jQuery("#repeatend" ).datepicker
                                                        ({
                                                                minDate: 0,
                                                                changeMonth: true,
                                                                changeYear: true,
                                                                dateFormat: 'yy-mm-dd',
                                                                onClose: function ( selectedDate ) {
								var repeatEndDate = new Date(selectedDate);
                                                                        var startDate = new Date(joms.jQuery('#startdate').datepicker('getDate'));
								var endDate = new Date(joms.jQuery('#enddate').datepicker('getDate'));
								if ( repeatEndDate < startDate ) {
									joms.jQuery('#startdate').datepicker('setDate',selectedDate);
								}
								if ( repeatEndDate < endDate ) {

									joms.jQuery('#enddate').datepicker('option','minDate',joms.jQuery('#startdate').datepicker('getDate')).datepicker('setDate',selectedDate);
								}
							}
                                                        });
                                        </script>
			</span>
			</span>
		</div>
		</div>
	<?php  } ?>
	<script>
	function toggleEventDateTime() {
		if( joms.jQuery('#joms-event-all-day').is(':checked') ){
			joms.jQuery('span#start-time, span#end-time').hide();
			joms.jQuery('#starttime-hour').val('12');
			joms.jQuery('#starttime-min').val('00');
			joms.jQuery('#starttime-ampm').val('am');
			joms.jQuery('#endtime-hour').val('11');
			joms.jQuery('#endtime-min').val('59');
			joms.jQuery('#endtime-ampm').val('pm');
			joms.jQuery('#joms-event-end-date').val(joms.jQuery('#joms-event-start-date').val());
			joms.jQuery('#joms-event-end-date').datepicker( 'setDate',joms.jQuery('#joms-event-start-date').val() );
		}else{
			joms.jQuery('span#start-time, span#end-time').show();
		}
	}

	function toggleEventRepeat() {
		if( joms.jQuery('#repeat').val() != '' ) {
			joms.jQuery('#repeatendinput').show();
			joms.jQuery('input#repeatend').addClass('required');

			if (joms.jQuery('#repeat').val() == 'daily') {
					limitdesc = '<?php echo addslashes(sprintf(Jtext::_('COM_COMMUNITY_EVENTS_REPEAT_LIMIT_DESC'), COMMUNITY_EVENT_RECURRING_LIMIT_DAILY));?>';
			}else if (joms.jQuery('#repeat').val() == 'weekly') {
					limitdesc = '<?php echo addslashes(sprintf(Jtext::_('COM_COMMUNITY_EVENTS_REPEAT_LIMIT_DESC'), COMMUNITY_EVENT_RECURRING_LIMIT_WEEKLY));?>';
			}else if (joms.jQuery('#repeat').val() == 'monthly') {
					limitdesc = '<?php echo addslashes(sprintf(Jtext::_('COM_COMMUNITY_EVENTS_REPEAT_LIMIT_DESC'), COMMUNITY_EVENT_RECURRING_LIMIT_MONTHLY));?>';
			}
		}
		else {
			joms.jQuery('#repeatendinput').hide();
			joms.jQuery('input#repeatend').removeClass('required');
		}
	}
	</script>
	</form>
</div>
<!--
<div class="creator-view type-event">
	<div class="creator-hint alert"></div>

	<form class="creator-form align-inherit reset-gap">
		<ul class="cFormList cFormHorizontal createEvent cResetList">
			<li>
				<label for="title" class="form-label" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_TITLE_LABEL'); ?>">
					<?php echo JText::_('COM_COMMUNITY_EVENTS_TITLE_LABEL'); ?>
				</label>
				<div class="form-field">
					<input name="title" id="title" type="text" class="required jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_TITLE_TIPS'); ?>" value="" />
				</div>
			</li>
			<li>
				<label for="catid" class="form-label" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_CATEGORY');?>">
					<?php echo JText::_('COM_COMMUNITY_EVENTS_CATEGORY');?>
				</label>
				<div class="form-field">
					<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_CATEGORY_TIPS');?>"><?php echo $lists['categoryid']; ?></span>
				</div>
			</li>
			<li>
				<label for="location" class="form-label"><?php echo JText::_('COM_COMMUNITY_EVENTS_LOCATION'); ?></label>
				<div class="form-field">
					<input name="location" id="location" type="text" class="required jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_LOCATION_TIPS'); ?>" value="" />
					<div class="small">
						<?php echo JText::_('COM_COMMUNITY_EVENTS_LOCATION_DESCRIPTION');?>
					</div>
				</div>
			</li>
			<li>
				<label class="form-label" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_START_TIME'); ?>">
					<?php echo JText::_('COM_COMMUNITY_EVENTS_START_TIME'); ?>
				</label>
				<div class="form-field">
					<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_START_TIME_TIPS'); ?>">
						<input type="text" name="startdate" id="startdate" style="width:auto;" size="10" class="span2 input-medium" readonly/>
						<script>
							joms.jQuery("#startdate" ).datepicker
								({

									minDate: 0,
									changeMonth: true,
									changeYear: true,
									dateFormat: 'yy-mm-dd',
									onClose: function ( selectedDate ) {
										var startDate = new Date(selectedDate);
										var endDate = new Date(joms.jQuery('#enddate').datepicker('getDate'));
                                                                                /* set minDate as startDate */
                                                                                joms.jQuery('#enddate').datepicker('option','minDate',selectedDate);
										if ( startDate > endDate ) {
											joms.jQuery('#enddate').datepicker('setDate',selectedDate); /* reset endDate same as startDate */
										}
									}
								}).datepicker('setDate', new Date());
						</script>

						<span id="start-time">
						<?php echo $startHourSelect; ?>:<?php  echo $startMinSelect; ?> <?php echo $startAmPmSelect;?>
						</span>
					</span>
				</div>
			</li>
			<li id="event-end-datetime">
				<label class="form-label" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_END_TIME'); ?>">
					<?php echo JText::_('COM_COMMUNITY_EVENTS_END_TIME'); ?>
				</label>
				<div class="form-field">
					<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_END_TIME_TIPS'); ?>">
						<input type="text" name="enddate" id="enddate" style="width:auto;" size="10" class="required input-medium" readonly/>
						<script>
							joms.jQuery("#enddate" ).datepicker
								({
									minDate: 0,
									changeMonth: true,
									changeYear: true,
									dateFormat: 'yy-mm-dd',
								}).datepicker('setDate', new Date());
						</script>
						<span id="end-time">
							<?php echo $endHourSelect; ?>:<?php echo $endMinSelect; ?> <?php echo $endAmPmSelect;?>
						</span>
					</span>
				</div>
			</li>
			<li>
				<div class="form-field">
					<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_ALL_DAY_TIPS');?>" style="display: inline-block">
						<label class="label-checkbox" for="allday">
							<input id="allday" name="allday" type="checkbox" class="input checkbox" onclick="toggleEventDateTime();" value="1"/>
							<?php echo JText::_('COM_COMMUNITY_EVENTS_ALL_DAY'); ?>
						</label>
					</span>
					<script type="text/javascript">
					function toggleEventDateTime()
					{
						if( joms.jQuery('#allday').attr('checked') == 'checked' ){
							joms.jQuery('span#start-time, span#end-time').hide();
							joms.jQuery('#starttime-hour').val('12');
							joms.jQuery('#starttime-min').val('00');
							joms.jQuery('#starttime-ampm').val('am');
							joms.jQuery('#endtime-hour').val('11');
							joms.jQuery('#endtime-min').val('59');
							joms.jQuery('#endtime-ampm').val('pm');

						}else{
							joms.jQuery('span#start-time, span#end-time').show();
						}
					}

					function toggleEventRepeat()
					{
						if( joms.jQuery('#repeat').val() != '' )
						{
							joms.jQuery('#repeatendinput').show();
							joms.jQuery('input#repeatend').addClass('required');

							if (joms.jQuery('#repeat').val() == 'daily') {
									limitdesc = '<?php echo addslashes(sprintf(Jtext::_('COM_COMMUNITY_EVENTS_REPEAT_LIMIT_DESC'), COMMUNITY_EVENT_RECURRING_LIMIT_DAILY));?>';
							}else if (joms.jQuery('#repeat').val() == 'weekly') {
									limitdesc = '<?php echo addslashes(sprintf(Jtext::_('COM_COMMUNITY_EVENTS_REPEAT_LIMIT_DESC'), COMMUNITY_EVENT_RECURRING_LIMIT_WEEKLY));?>';
							}else if (joms.jQuery('#repeat').val() == 'monthly') {
									limitdesc = '<?php echo addslashes(sprintf(Jtext::_('COM_COMMUNITY_EVENTS_REPEAT_LIMIT_DESC'), COMMUNITY_EVENT_RECURRING_LIMIT_MONTHLY));?>';
							}
						}
						else
						{
								joms.jQuery('#repeatendinput').hide();
								joms.jQuery('input#repeatend').removeClass('required');
						}
					}
					</script>
				</div>
			</li>

			<?php if ($enableRepeat) { ?>
			<li>
				<label for="repeat" class="form-label" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT'); ?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT'); ?></label>
				<div class="form-field">
					<span class="jomNameTips" original-title="<?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_TIPS'); ?>">
					<span id="repeatcontent"></span>
					<select name="repeat" id="repeat" onChange="toggleEventRepeat()" class="input select">
						<option value=""><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_NONE'); ?></option>
						<option value="daily"><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_DAILY'); ?></option>
						<option value="weekly"><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_WEEKLY'); ?></option>
						<option value="monthly"><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_MONTHLY'); ?></option>
					</select>
					</span>

					<span id="repeatendinput">
					<span class="label">&nbsp;&nbsp;*<?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_END'); ?>&nbsp;</span>
					<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_END_TIPS'); ?>">

                                            <input type="text" name="repeatend" id="repeatend" style="width:auto;" size="10" class="input-medium" readonly/>
                                                <script>
                                                        joms.jQuery("#repeatend" ).datepicker
                                                                ({
                                                                        minDate: 0,
                                                                        changeMonth: true,
                                                                        changeYear: true,
                                                                        dateFormat: 'yy-mm-dd',
                                                                        onClose: function ( selectedDate ) {
										var repeatEndDate = new Date(selectedDate);
                                                                                var startDate = new Date(joms.jQuery('#startdate').datepicker('getDate'));
										var endDate = new Date(joms.jQuery('#enddate').datepicker('getDate'));
										if ( repeatEndDate < startDate ) {
											joms.jQuery('#startdate').datepicker('setDate',selectedDate);
										}
										if ( repeatEndDate < endDate ) {

											joms.jQuery('#enddate').datepicker('option','minDate',joms.jQuery('#startdate').datepicker('getDate')).datepicker('setDate',selectedDate);
										}
									}
                                                                });
                                                </script>
					</span>
					</span>
				</div>
			</li>
			<?php  } ?>
		</ul>
	</form>
</div>-->

<?php
    if($config->get('event_calendar_firstday')  == 'Sunday')
    {
        $first_day = 0;
    }
    else
    {
        $first_day = 1;
    }
?>

<script type="text/javascript">
    toggleEventDateTime();
	joms.jQuery(document).ready(function(){
		toggleEventRepeat();

        joms.jQuery('.hasDatepicker').datepicker('option', 'firstDay', <?php echo $first_day; ?>);
	});

	joms.jQuery('#joms-event-all-day').on('click', this, function(){
		toggleEventDateTime();
	});

</script>
