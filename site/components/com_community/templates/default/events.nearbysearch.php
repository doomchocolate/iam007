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
 <div id="com-events-finder" class="app-box">
	<h3 class="app-box-header">
		<?php echo JText::_('COM_COMMUNITY_EVENTS_NEARBY'); ?>
	</h3>
	<div class="app-box-content">
		<div id="showNearByEventsForm">
			<div class="input-wrap">
				<input type="text" id="userInputLocation" name="userInputLocation" class="input-block-level">
			</div>
			<div class="small cFormTips">
					<?php echo JText::_('COM_COMMUNITY_EVENTS_LOCATION_DESCRIPTION');?>
			</div>
			<button class="btn btn-primary" onclick="joms.geolocation.validateNearByEventsForm();"><?php echo JText::_('COM_COMMUNITY_SEARCH'); ?></button>
			<span id="autodetectLocation" style="display: none;">&nbsp;<?php echo JText::_('COM_COMMUNITY_OR') ?>&nbsp;<a href="javascript:void(0);" onclick="joms.geolocation.showNearByEvents();"><?php echo JText::_('COM_COMMUNITY_EVENTS_AUTODETECT') ?></a></span>
		</div>
		<div id="community-event-nearby-listing" class="app-box-result" style="display: none">
			<span id="showNearByEventsLoading" class="loading" style="display: none; float: left; margin-top: 10px; margin-left: 80px;"></span>
		</div>
	</div>
</div>