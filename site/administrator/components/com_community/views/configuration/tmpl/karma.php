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
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_KARMA' ); ?></h5>
		<div class="widget-toolbar no-border">
			<a href="http://tiny.cc/karmasystem" target="_blank"><i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
		</div>
	</div>
	<div class="widget-body">
		<div class="widget-main">

			<table>
				<tbody>
					<tr>
						<td width="120" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_KARMA_ENABLE_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_KARMA_ENABLE' ); ?>
							</span>
						</td>
						<td >
							<?php echo CHTMLInput::checkbox('enablekarma' ,'ace-switch ace-switch-5', null , $this->config->get('enablekarma') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_KARMA_DEFAULT_POINTS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_KARMA_DEFAULT_POINTS' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="defaultpoint" value="<?php echo $this->config->get('defaultpoint');?>" />
						</td>
					</tr>
					<tr>
						<td class="key" >
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_KARMA_POINTS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_KARMA_SMALLER_THAN' ); ?>
							</span>
						</td>
						<td>
					<?php echo $this->getKarmaHTML( 'point0' , $this->config->get('point0'), false );?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_KARMA_POINTS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_KARMA_GREATER_THAN' ); ?>
							</span>
						</td>
						<td>
							<?php echo $this->getKarmaHTML( 'point1' , $this->config->get('point1') , false, 'point0');?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_KARMA_POINTS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_KARMA_GREATER_THAN' ); ?>
							</span>
						</td>
						<td>
							<?php echo $this->getKarmaHTML( 'point2' , $this->config->get('point2') );?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_KARMA_POINTS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_KARMA_GREATER_THAN' ); ?>
							</span>
						</td>
						<td>
							<?php echo $this->getKarmaHTML( 'point3' , $this->config->get('point3') );?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_KARMA_POINTS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_KARMA_GREATER_THAN' ); ?>
							</span>
						</td>
						<td>
							<?php echo $this->getKarmaHTML( 'point4' , $this->config->get('point4') );?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_KARMA_POINTS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_KARMA_GREATER_THAN' ); ?>
							</span>
						</td>
						<td>
							<?php echo $this->getKarmaHTML( 'point5' , $this->config->get('point5') );?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>