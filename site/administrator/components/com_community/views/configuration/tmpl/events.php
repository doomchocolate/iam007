<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
?>

<div class="widget-box">
	<div class="widget-header widget-header-flat">
		<h5>&nbsp;</h5>
		<div class="widget-toolbar no-border">
			<a href="http://tiny.cc/jsevents" target="_blank">
				<i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
		</div>
	</div>
	<div class="widget-body">
		<div class="widget-main">
			<fieldset class="adminform">

				<table width="100%">
					<tbody>
						<tr>
							<td width="250" class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EVENTS_ENABLE_EVENTS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EVENTS_ENABLE_EVENTS' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('enableevents' ,'ace-switch ace-switch-5', null , $this->config->get('enableevents') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EVENTS_ENABLE_GUEST_SEARCH_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EVENTS_ENABLE_GUEST_SEARCH' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('enableguestsearchevents' ,'ace-switch ace-switch-5', null , $this->config->get('enableguestsearchevents') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EVENTS_MODERATE_EVENT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EVENTS_MODERATE_EVENT' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('event_moderation' ,'ace-switch ace-switch-5', null , $this->config->get('event_moderation') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EVENTS_ALLOW_CREATION_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EVENTS_ALLOW_CREATION' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('createevents' ,'ace-switch ace-switch-5', null , $this->config->get('createevents') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_SHOW_FEATURED_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REPEAT' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('enablerepeat' ,'ace-switch ace-switch-5', null , $this->config->get('enablerepeat') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EVENTS_CREATE_LIMIT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EVENTS_CREATE_LIMIT' ); ?>
								</span>
							</td>
							<td>
								<input type="text" name="eventcreatelimit" value="<?php echo $this->config->get('eventcreatelimit' );?>" size="10" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EVENTS_ICAL_EXPORT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EVENTS_ICAL_EXPORT' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('eventexportical' ,'ace-switch ace-switch-5', null , $this->config->get('eventexportical') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EVENTS_ICAL_IMPORT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EVENTS_ICAL_IMPORT' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('event_import_ical' ,'ace-switch ace-switch-5', null , $this->config->get('event_import_ical') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EVENTS_SHOW_MAPS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EVENTS_SHOW_MAPS' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('eventshowmap' ,'ace-switch ace-switch-5', null , $this->config->get('eventshowmap') ); ?>
							</td>
						</tr>

			            <tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EVENTS_DEFAULT_RADIUS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EVENTS_DEFAULT_RADIUS' ); ?>
								</span>
							</td>
							<td>
			                    <input <?php if($this->config->get('eventradiusmeasure') == COMMUNITY_EVENT_UNIT_KM) echo 'checked="checked"'; ?> type="radio" name="eventradiusmeasure" id="eventradiusmeasure_1" value="<?php echo COMMUNITY_EVENT_UNIT_KM; ?>" onclick="document.getElementById('nearby_radius_measurement').innerHTML='<?php echo COMMUNITY_EVENT_UNIT_KM; ?>';" />
								<span class="lbl"><?php echo JText::_('COM_COMMUNITY_EVENTS_KILOMETRE');?></span>


			                    <input <?php if(!$this->config->get('eventradiusmeasure') || $this->config->get('eventradiusmeasure') == COMMUNITY_EVENT_UNIT_MILES) echo 'checked="checked"'; ?> type="radio" name="eventradiusmeasure" id="eventradiusmeasure_2" value="<?php echo COMMUNITY_EVENT_UNIT_MILES; ?>" onclick="document.getElementById('nearby_radius_measurement').innerHTML='<?php echo COMMUNITY_EVENT_UNIT_MILES; ?>';" />
								<span class="lbl"><?php echo JText::_('COM_COMMUNITY_EVENTS_MILES');?></span>
							</td>
						</tr>

						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EVENTS_NEARBY_RADIUS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EVENTS_NEARBY_RADIUS' ); ?>
								</span>
							</td>
							<td>
								<select name="event_nearby_radius" class="inline">
										<option value="<?php echo COMMUNITY_EVENT_WITHIN_5; ?>"<?php echo ( $this->config->get('event_nearby_radius') == COMMUNITY_EVENT_WITHIN_5 ) ? ' selected="true"' : '';?>>5</option>
										<option value="<?php echo COMMUNITY_EVENT_WITHIN_10; ?>"<?php echo ( $this->config->get('event_nearby_radius') == COMMUNITY_EVENT_WITHIN_10 ) ? ' selected="true"' : '';?>>10</option>
										<option value="<?php echo COMMUNITY_EVENT_WITHIN_20; ?>"<?php echo ( $this->config->get('event_nearby_radius') == COMMUNITY_EVENT_WITHIN_20 ) ? ' selected="true"' : '';?>>20</option>
										<option value="<?php echo COMMUNITY_EVENT_WITHIN_50; ?>"<?php echo ( $this->config->get('event_nearby_radius') == COMMUNITY_EVENT_WITHIN_50 ) ? ' selected="true"' : '';?>>50</option>
								</select>
			                    <label id="nearby_radius_measurement" class="inline"><?php echo $this->config->get('eventradiusmeasure'); ?></label>
							</td>
						</tr>

						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EVENTS_CALENDAR_FIRST_DAY_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EVENTS_CALENDAR_FIRST_DAY' ); ?>
								</span>
							</td>
							<td>
								<select name="event_calendar_firstday">
										<option value="<?php echo COMMUNITY_EVENT_CALENDAR_MONDAY; ?>"<?php echo ( $this->config->get('event_calendar_firstday') == COMMUNITY_EVENT_CALENDAR_MONDAY ) ? ' selected="true"' : '';?>><?php echo JText::_( 'COM_COMMUNITY_EVENTS_MONDAY_OPTION' ); ?></option>
										<option value="<?php echo COMMUNITY_EVENT_CALENDAR_SUNDAY; ?>"<?php echo ( $this->config->get('event_calendar_firstday') == COMMUNITY_EVENT_CALENDAR_SUNDAY ) ? ' selected="true"' : '';?>><?php echo JText::_( 'COM_COMMUNITY_EVENTS_SUNDAY_OPTION' ); ?></option>
								</select>
							</td>
						</tr>
					</tbody>
				</table>
			</fieldset>

			<fieldset class="adminform">
				<table>
					<tbody>
						<tr>
							<td class="key" width="250">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EVENTS_LISTINGS_DATE_FORMAT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EVENTS_LISTINGS_DATE_FORMAT' ); ?>
								</span>
							</td>
							<td>
								<select name="eventdateformat">
										<option value="M d Y"<?php echo ( $this->config->get('eventdateformat') == 'M d Y' ) ? ' selected="true"' : '';?>><?php echo JText::_('COM_COMMUNITY_MONTH_DAY_OPTION');?></option>
										<option value="d M Y"<?php echo ( $this->config->get('eventdateformat') == 'd M Y' ) ? ' selected="true"' : '';?>><?php echo JText::_('COM_COMMUNITY_DAY_MONTH_OPTION');?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EVENTS_TIME_SELECTION_FORMAT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EVENTS_TIME_SELECTION_FORMAT' ); ?>
								</span>
							</td>
							<td>
								<select name="eventshowampm">
										<option value="1"<?php echo ( $this->config->get('eventshowampm') == '1' ) ? ' selected="true"' : '';?>><?php echo JText::_('COM_COMMUNITY_12H_OPTION');?></option>
										<option value="0"<?php echo ( $this->config->get('eventshowampm') == '0' ) ? ' selected="true"' : '';?>><?php echo JText::_('COM_COMMUNITY_24H_OPTION');?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EVENTS_SHOW_TIMEZONE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EVENTS_SHOW_TIMEZONE' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('eventshowtimezone' ,'ace-switch ace-switch-5', null , $this->config->get('eventshowtimezone') ); ?>
							</td>
						</tr>

					</tbody>
				</table>
			</fieldset>
		</div>
	</div>
</div>

