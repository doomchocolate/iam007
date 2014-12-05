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
$noData = true;
?>
<div class="cModule cProfile-About app-box">
    <h3 class="app-box-header cResetH"><?php echo JText::_('COM_COMMUNITY_ABOUT_ME'); ?></h3>

    <div class="app-box-content">
        <?php
        foreach ($profile['fields'] as $groupName => $items) {
            // Gather display data for the group. If there is no data, we can
            // later completely hide the whole segment
            $hasData = false;
            ob_start();
            ?>
            <div class="cField">

                <?php if ($groupName != 'ungrouped') { ?>
                    <h3 class="cField-Title cResetH"><?php echo JText::_($groupName); ?></h3>
                <?php } ?>

                <ul class="cField-List cResetList">
                    <?php foreach ($items as $item) { ?>
                        <?php
                        if (CPrivacy::isAccessAllowed($my->id, $profile['id'], 'custom', $item['access'])) {
                            // There is some displayable data here
                            $hasData = $hasData || CProfileLibrary::getFieldData($item) != '';
                            ?>

                            <?php
                            $fieldData = CProfileLibrary::getFieldData($item);
                            
                            // Escape unless it is URL type, since URL type is in HTML format
                            if ($item['type'] != 'url' && $item['type'] != 'email' && $item['type'] != 'list' && $item['type'] != 'checkbox') {
                                $fieldData = $this->escape($fieldData);
                            }

                            // If textarea, we need to support multiline entry
                            if ($item['type'] == 'textarea') {
                                $fieldData = nl2br($fieldData);
                            }

                            if (!empty($fieldData)) {

                                ?>
                                <li>
                                    <h3 class="cField-Name cResetH"><?php echo JText::_($item['name']); ?></h3>
                                    <?php if (!empty($item['searchLink']) && is_array($item['searchLink'])) { ?>
                                        <div class="cField-Content">
                                            <?php
                                            foreach ($item['searchLink'] as $linkKey => $linkValue) {
                                                $item['value'] = $linkKey;
                                                if ($item['type'] == 'checkbox') {
                                                    echo '<a href="' . $linkValue . '">' . $item['value'] . '</a><br />';
                                                } else {
                                                    echo '<a href="' . $linkValue . '">' . $fieldData . '</a><br />';
                                                }
                                            }
                                            ?>
                                        </div>
                                    <?php } else { ?>
                                        <div class="cField-Content">
                                            <?php echo (!empty($item['searchLink'])) ? '<a href="' . $item['searchLink'] . '"> ' . $fieldData . ' </a>' : $fieldData; ?>
                                        </div>
                                    <?php } ?>
                                </li>
                                <?php
                            }
                        }
                        ?>
                    <?php } ?>
                </ul>
            </div>
            <?php
            $html = ob_get_contents();
            ob_end_clean();

            // We would only display the profile data in the group if there is actually some
            // data to be displayed
            if ($hasData) {
                echo $html;
                $noData = false;
            }
        }

        if ($noData) {
            if ($isMine) {
                ?>
                <div class="cEmpty">
                    <?php echo JText::_('COM_COMMUNITY_PROFILES_SHARE_ABOUT_YOURSELF'); ?>
                </div>
                <?php
            } else {
                ?>
                <div class="cEmpty">
                    <?php echo JText::_('COM_COMMUNITY_PROFILES_NO_INFORMATION_SHARE'); ?>
                </div>
                <?php
            }
        }
        ?>
    </div>
    <div class="app-box-footer">
        <?php if ($isMine): ?>
            <a class="edit-this" href="<?php echo CRoute::_('index.php?option=com_community&view=profile&task=edit'); ?>" title="<?php echo JText::_('COM_COMMUNITY_PROFILE_EDIT'); ?>"><?php echo JText::_('COM_COMMUNITY_PROFILE_EDIT'); ?></a>
        <?php endif; ?>
    </div>
</div>