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
        <h5><?php echo JText::_('COM_COMMUNITY_CONFIGURATION_USER_STATUS'); ?></h5>
    </div>
    <div class="widget-body">
        <div class="widget-main">
            <table>
                <tbody>
                    <tr>
                        <td width="250" class="key">
                            <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_USER_STATUS_CHARACTER_LIMIT_TIPS'); ?>">
                                <?php echo JText::_('COM_COMMUNITY_CONFIGURATION_USER_STATUS_CHARACTER_LIMIT'); ?>
                            </span>
                        </td>
                        <td valign="top">
                            <div><input type="text" name="statusmaxchar" value="<?php echo $this->config->get('statusmaxchar'); ?>" size="5" /><?php echo JText::_('COM_COMMUNITY_CHARACTERS'); ?></div>
                        </td>
                    </tr>
                    <!-- Stream location -->

<!--                     <tr>
                        <td width="250" class="key">
                            <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_STREAM_LOCATION_TIPS'); ?>">
                                <?php echo JText::_('COM_COMMUNITY_CONFIGURATION_ENABLE_STREAM_LOCATION'); ?>
                            </span>
                        </td>
                        <td valign="top">
                            <?php $enable_stream_location = $this->config->get('enable_stream_location', COMMUNITY_STREAM_LOCATION_DISABLE); ?>
                            <select name="enable_stream_location">
                                <option value="<?php echo COMMUNITY_STREAM_LOCATION_DISABLE; ?>" <?php echo ($enable_stream_location == COMMUNITY_STREAM_LOCATION_DISABLE) ? 'selected="selected"' : ''; ?>>
                                    <?php echo JText::_('COM_COMMUNITY_DISABLE'); ?>
                                </option>
                                <option value="<?php echo COMMUNITY_STREAM_LOCATION_ENABLE; ?>" <?php echo ($enable_stream_location == COMMUNITY_STREAM_LOCATION_ENABLE) ? 'selected="selected"' : ''; ?>>
                                    <?php echo JText::_('COM_COMMUNITY_ENABLE'); ?>
                                </option>
                            </select>
                        </td>
                    </tr> -->

                    <tr>
                        <td width="250" class="key">
                            <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_STREAM_LOCATION_TIPS'); ?>">
                                <?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_ENABLE_STREAM_LOCATION' ); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo CHTMLInput::checkbox('streamlocation' ,'ace-switch ace-switch-5', null , $this->config->get('streamlocation') ); ?>
                        </td>
                    </tr>

                    <!-- Status Emoticons -->
                    <tr>
                        <td width="250" class="key">
                            <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_STATUSEMOTICONS_DESCRIPTION'); ?>">
                                <?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_STATUSEMOTICONS' ); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo CHTMLInput::checkbox('statusemoticon' ,'ace-switch ace-switch-5', null , $this->config->get('statusemoticon') ); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="key">
                            <span class="js-tooltip" title="<?php echo JTexT::_('COM_COMMUNITY_CONFIGURATION_ENABLE_MOOD_IN_STREAM_TIPS'); ?>">
                                <?php echo JText::_('COM_COMMUNITY_CONFIGURATION_ENABLE_MOOD_IN_STREAM')?>
                            </span>
                        </td>
                        <td>
                            <?php echo CHTMLInput::checkbox('enablemood', 'ace-switch ace-switch-5', null, $this->config->get('enablemood'))?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>