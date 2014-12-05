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
        <h5><?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_AMAZONS3'); ?></h5>
        <div class="widget-toolbar no-border">
            <a href="http://tiny.cc/jssysreq" target="_blank"><i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC_REQUIREMENT'); ?></a>
            <a href="http://tiny.cc/jss3setup" target="_blank"><i class="js-icon-wrench"></i> <?php echo JText::_('COM_COMMUNITY_DOC_SETTING_UP'); ?></a>
            <a href="http://tiny.cc/SetupAmazonS3" target="_blank"><i class="js-icon-facetime-video"></i> <?php echo JText::_('COM_COMMUNITY_DOC_VIDEO'); ?></a>
        </div>
    </div>

    <div class="widget-body">
        <div class="widget-main">


            <table>
                <tbody>
                    <tr>
                        <td width="350" class="key">
                            <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_AMAZONS3_BUCKET_PATH_TIPS'); ?>">
                                <?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_AMAZONS3_BUCKET_PATH'); ?>
                            </span>
                        </td>
                        <td valign="top">
                            <input type="text" name="storages3bucket" value="<?php echo $this->config->get('storages3bucket', ''); ?>" size="50" />
                        </td>
                    </tr>
                    <tr>
                        <td width="350" class="key">
                            <span class="js-tooltip" title="">
                                <?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_AMAZONS3_BUCKET_URL'); ?>
                            </span>
                        </td>
                        <td valign="top">
                            <input type="text" name="storages3bucket_url" value="<?php echo $this->config->get('storages3bucket_url', '<bucket>.s3.amazonaws.com'); ?>" size="50" />
                        </td>                        
                    </tr>
                    <tr>
                        <td class="key">
                            <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_AMAZONS3_ACCESS_KEY_TIPS'); ?>">
                                <?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_AMAZONS3_ACCESS_KEY'); ?>
                            </span>
                        </td>
                        <td valign="top">
                            <input type="text" name="storages3accesskey" value="<?php echo $this->config->get('storages3accesskey', ''); ?>" size="50" />
                        </td>
                    </tr>
                    <tr>
                        <td class="key">
                            <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_AMAZONS3_SECRET_KEY_TIPS'); ?>">
                                <?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_AMAZONS3_SECRET_KEY'); ?>
                            </span>
                        </td>
                        <td valign="top">
                            <input type="text" name="storages3secretkey" value="<?php echo $this->config->get('storages3secretkey', ''); ?>" size="50" />
                        </td>
                    </tr>
                    <tr>
                        <td class="key">
                            <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_AMAZONS3_CLASS_TIPS'); ?>">
                                <?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_AMAZONS3_CLASS'); ?>
                            </span>
                        </td>
                        <td valign="top">
                            <select name="amazon_storage_class">
                                <option <?php echo ( $this->config->get('amazon_storage_class') == 'STANDARD' ) ? 'selected="true"' : ''; ?> value="STANDARD"><?php echo JText::_('COM_COMMUNITY_STANDARD_OPTION'); ?></option>
                                <option <?php echo ( $this->config->get('amazon_storage_class') == 'REDUCED_REDUNDANCY' ) ? 'selected="true"' : ''; ?> value="REDUCED_REDUNDANCY"><?php echo JText::_('COM_COMMUNITY_REDUCED_REDUNDANCY_OPTION'); ?></option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>

</div>