<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-cron">
<?php } else { ?>
<div id="page-cron" class="row-fluid">
	<div class="span12">
<?php } ?>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'CRON' ); ?></legend>
		<table class="admintable table" cellspacing="1">
			<tr>
				<td colspan="2">
				<?php echo $this->elements->cron_edit; ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('MIN_DELAY'); ?>
				</td>
				<td>
					<?php echo $this->elements->cron_frequency; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_('NEXT_RUN'); ?>
				</td>
				<td>
					<?php echo hikashop_getDate($this->config->get('cron_next')); ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_('CRON_URL'); ?>
				</td>
				<td>
					<a href="<?php echo $this->elements->cron_url; ?>" target="_blank"><?php echo $this->elements->cron_url; ?></a>
				</td>
			</tr>
		</table>
	</fieldset>
<?php if(HIKASHOP_BACK_RESPONSIVE) { ?>
	</div>
	<div class="span12">
<?php } ?>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'REPORT' ); ?></legend>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-cron-report">
	<table style="width:100%">
		<tr>
			<td valign="top" width="50%">
				<fieldset class="adminform">
<?php } else { ?>
<div id="page-cron-report" class="row-fluid">
	<div class="span6">
<?php } ?>
						<table class="admintable table" cellspacing="1">
							<tr>
								<td class="key">
									<?php echo JText::_('REPORT_SEND'); ?>
								</td>
								<td>
									<?php echo $this->elements->cron_sendreport;?>
								</td>
							</tr>
						</table>
						<table class="admintable table" cellspacing="1" id="cronreportdetail">
							<tr>
								<td class="key" >
								<?php echo JText::_('REPORT_SEND_TO'); ?>
								</td>
								<td>
									<input class="inputbox" type="text" name="config[cron_sendto]" size="50" value="<?php echo $this->config->get('cron_sendto'); ?>">
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<?php echo $this->elements->editReportEmail;?>
								</td>
							</tr>
						</table>

<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
				</fieldset>
			</td>
			<td valign="top" width="50%">
				<fieldset class="adminform">
<?php } else { ?>
	</div>
	<div class="span6">
<?php } ?>

						<table class="admintable table" cellspacing="1">
							<tr>
								<td class="key" >
									<?php echo JText::_('REPORT_SAVE'); ?>
								</td>
								<td>
									<?php echo $this->elements->cron_savereport;?>
								</td>
							</tr>
						</table>
						<table class="admintable table" cellspacing="1" id="cronreportsave">
							<tr>
								<td class="key" >
									<?php echo JText::_('REPORT_SAVE_TO'); ?>
								</td>
								<td>
									<input class="inputbox" type="text" name="config[cron_savepath]" size="60" value="<?php echo $this->config->get('cron_savepath'); ?>">
								</td>
							</tr>
							<tr>
								<td colspan="2" id="toggleDelete">
									<?php echo $this->elements->deleteReport;?>
									<?php echo $this->elements->seeReport; ?>
								</td>
							</tr>
						</table>

<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
				</fieldset>
			</td>
		</tr>
	</table>
</div>
<?php } else { ?>
	</div>
</div>
<?php } ?>
	</fieldset>
<?php if(HIKASHOP_BACK_RESPONSIVE) { ?>
	</div>
	<div class="span12">
<?php } ?>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'LAST_CRON' ); ?></legend>
		<table class="admintable table" cellspacing="1">
			<tr>
				<td class="key" >
					<?php echo JText::_('LAST_RUN'); ?>
				</td>
				<td>
					<?php echo hikashop_getDate($this->config->get('cron_last')); ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('CRON_TRIGGERED_IP'); ?>
				</td>
				<td>
					<?php echo $this->config->get('cron_fromip'); ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
				<?php echo JText::_('REPORT'); ?>
				</td>
				<td>
					<?php echo $this->config->get('cron_report'); ?>
				</td>
			</tr>
		</table>
	</fieldset>
<?php if(HIKASHOP_BACK_RESPONSIVE) { ?>
	</div>
<?php } ?>
</div>
