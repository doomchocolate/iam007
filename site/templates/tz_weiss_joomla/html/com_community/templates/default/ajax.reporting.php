 <?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
?>
<form id="report-form" name="report-form" action="" method="post" class="cForm">
	<ul class="cFormList cFormVertical cResetList">
		<li>
			<label class="form-label"><?php echo JText::_('COM_COMMUNITY_PREDEFINED_REPORTS');?></label>
			<div class="form-field">
				<select class="input-block-level" id="report-predefined" onchange="if(this.value!=0) joms.jQuery('#report-message').val( this.value ); else joms.jQuery('#report-message').val('');">
					<option selected="selected" value="0"><?php echo JText::_('COM_COMMUNITY_SELECT_PREDEFINED_REPORTS'); ?></option>
					<?php
					if( $reports )
					{
						foreach( $reports as $report )
						{
							$reportString = (!strtoupper($report) === $report) ? JText::_( $report ) : $report ;
					?>
						<option value="<?php echo $reportString ;?>"><?php echo $reportString ; ?></option>
					<?php
						}
					}
					?>
				</select>
			</div>
		</li>
		<li>
			<label class="form-label"><?php echo JText::_('COM_COMMUNITY_REPORT_MESSAGE');?><span id="report-message-error"></span></label>
			<div class="form-field">
				<div class="input-wrap">
					<p>
						<textarea id="report-message" class="input-block-level" name="report-message"></textarea>
					</p>
				</div>
			</div>
		</li>
	</ul>
	<input type="hidden" name="reportFunc" value="<?php echo $reportFunc; ?>" />
</form>