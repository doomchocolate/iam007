<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div id="cModule-LatestDiscussion" class="cMods cMods-LatestDiscussion<?php echo $params->get('moduleclass_sfx'); ?>">
    <?php
    if (!empty($latest)) {
        $items = array();

        foreach ($latest as $data) {
            $items[$data->groupid][] = $data;
        }
        ?>
        <?php
        foreach ($items as $groupId => $data) {
            $table = JTable::getInstance('Group', 'CTable');
            $table->load($groupId);

            if (count($data) > 1) {
                ?>
                <div class="cMod-Row">
                    <?php if ($showavatar && !$repeatAvatar) { ?>
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $table->id); ?>" class="cThumb-Avatar l-float">
                            <img src="<?php echo $table->getAvatar('thumb'); ?>" alt="<?php echo CStringHelper::escape($table->name); ?>" width="45" height="45" />
                        </a>
                        <?php
                    }

                    $i = 0;
                    foreach ($data as $row) {
                        $creator = CFactory::getUser($row->creator);
                        ?>
                        <div class="clearfix bottom-gap">

                            <?php if ($showavatar && $repeatAvatar) { ?>
                                <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $table->id); ?>" class="cThumb-Avatar l-float">
                                    <img src="<?php echo $table->getAvatar('thumb'); ?>" alt="<?php echo CStringHelper::escape($table->name); ?>" width="45" height="45" />
                                </a>
                            <?php } ?>

                            <div class="cThumb-Detail <?php echo (!$showavatar) ? 'no-avatar' : ''; ?>" >
                                <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $table->id . '&topicid=' . $row->id); ?>" class="cThumb-Title"><?php echo $row->title; ?></a>
                                <div class="cThumb-Brief">
                                    <?php echo JText::_('by'); ?> <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $creator->id); ?>"><?php echo $creator->getDisplayName(); ?></a>
                                    <?php echo JText::sprintf((!CStringHelper::isSingular($row->counter)) ? 'MOD_LATESTDISC_REPLY_MANY' : 'MOD_LATESTDISC_REPLY', $row->counter); ?>
                                </div>
                            </div>
                        </div>
                        <?php
                        $i++;
                    }
                    ?>
                </div>
                <?php
            } else {
                $creator = CFactory::getUser($data[0]->creator);
                ?>
                <div class="cMod-Row">
                    <?php if ($showavatar) { ?>
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $table->id); ?>" class="cThumb-Avatar l-float">
                            <img src="<?php echo $table->getAvatar('thumb'); ?>" alt="<?php echo CStringHelper::escape($table->name); ?>" width="45" height="45" />
                        </a>
                    <?php } ?>
                    <div class="cThumb-Detail <?php echo (!$showavatar) ? 'no-avatar' : ''; ?>">
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $table->id . '&topicid=' . $data[0]->id); ?>" class="cThumb-Title"><?php echo $data[0]->title; ?></a>
                        <div class="cThumb-Brief">
                            <?php echo JText::_('by'); ?> <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $creator->id); ?>"><?php echo $creator->getDisplayName(); ?></a>
                            <?php echo JText::sprintf((!CStringHelper::isSingular($data[0]->counter)) ? 'MOD_LATESTDISC_REPLY_MANY' : 'MOD_LATESTDISC_REPLY', $data[0]->counter); ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        ?>
        <?php
    } else {
        echo JText::_("MOD_LATESTDISC_NO_DISCUSSION");
    }
    ?>
</div>
