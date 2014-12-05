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
CAssets::attach('assets/easytabs/jquery.easytabs.min.js', 'js');
?>



<div class="cLayout cGroups-ViewMembers">
    <?php if ($type == '1' && !( $isMine || $isAdmin || $isSuperAdmin )) { ?>
        <div>
            <?php echo JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING'); ?>
        </div>
    <?php } else { ?>
        <?php if ($members): ?>
            <div id="notice"></div>
            <ul class="cIndexList forGroupMembers cResetList">
                <?php foreach ($members as $member) { ?>
                    <?php
                    /* do not display banned users but not mine || admin || superadmin */
                    if ($member->isBanned && !( $isMine || $isAdmin || $isSuperAdmin )) {
                        continue;
                    }
                    ?>
                    <li id="member_<?php echo $member->id; ?>">
                        <div class="cIndex-Box clearfix">
                            <a class="cIndex-Avatar cFloat-L" href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $member->id); ?>">
                                <img class="cAvatar" src="<?php echo $member->getThumbAvatar(); ?>" alt="<?php echo $member->getDisplayName(); ?>" />
                                <?php if ($member->isOnline()) { ?>
                                    <b class="cStatus-Online"><?php echo JText::_('COM_COMMUNITY_ONLINE'); ?></b>
                                <?php } ?>
                            </a>
                            <div class="cIndex-Content">
                                <!-- current user is isSuperAdministrator or group admin but not mine than we present options -->
                                <?php if (($isSuperAdmin || (!$member->isMe && $isAdmin) ) && (!$member->isMe)) { ?>
                                    <div class="btn-group pull-right">
                                        <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                            <i class="joms-icon-cog"></i>
                                            <span class="caret"></span>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <!-- if user already is group admin than we don't show this option -->
                                            <?php if (!$member->isAdmin) { ?>
                                                <li class="setAdmin">
                                                    <a href="javascript:void(0);" onclick="jax.call('community', 'groups,ajaxAddAdmin', '<?php echo $member->id; ?>', '<?php echo $groupid; ?>');">
                                                        <?php echo JText::_('COM_COMMUNITY_GROUPS_ADMIN'); ?>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                            <!-- not self revert and require group admin or superadmin and this's admin -->
                                            <?php if ((!$member->isMe) && ($isAdmin || $isSuperAdmin ) && ($member->isAdmin)) { ?>
                                                <li class="setAdmin">
                                                    <a href="javascript:void(0);" onclick="jax.call('community', 'groups,ajaxRemoveAdmin', '<?php echo $member->id; ?>', '<?php echo $groupid; ?>');">
                                                        <?php echo JText::_('COM_COMMUNITY_GROUPS_REVERT_ADMIN'); ?>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                            <?php
                                            if ($member->id != $group->ownerid && !$group->isAdmin($member->id) && $my->id != $member->id && !COwnerHelper::isCommunityAdmin($member->id)) {
                                                if (!$member->isBanned && ( $isAdmin || $isSuperAdmin )) {
                                                    ?>
                                                    <li>
                                                        <a href="javascript:void(0);" onclick="jax.call('community', 'groups,ajaxBanMember', '<?php echo $member->id; ?>', '<?php echo $groupid; ?>');">
                                                            <?php echo JText::_('COM_COMMUNITY_GROUPS_BAN_MEMBER'); ?>
                                                        </a>
                                                    </li>
                                                    <?php
                                                } else if ($member->isBanned == COMMUNITY_GROUP_BANNED && ( $isAdmin || $isSuperAdmin )) {
                                                    ?>
                                                    <li>
                                                        <a href="javascript:void(0);" onclick="jax.call('community', 'groups,ajaxUnbanMember', '<?php echo $member->id; ?>', '<?php echo $groupid; ?>');">
                                                            <?php echo JText::_('COM_COMMUNITY_GROUPS_MEMBER_UNBAN'); ?>
                                                        </a>
                                                    </li>
                                                    <?php
                                                }
                                            }
                                            ?>
                                            <?php
                                            if (($isMine || $isAdmin || $isSuperAdmin) && $my->id != $member->id) {
                                                ?>
                                                <li class="hasSeperator">
                                                    <a href="javascript:void(0)" onclick="joms.groups.confirmMemberRemoval(<?php echo $member->id; ?>, <?php echo $groupid; ?>);" >
                                                        <?php echo JText::_('COM_COMMUNITY_GROUPS_REMOVE_MEMBER_MESSAGE'); ?>
                                                    </a>
                                                </li>
                                                <?php
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                <?php }; ?>

                                <h4 class="cIndex-Name cResetH">
                                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $member->id); ?>"><?php echo $member->getDisplayName(); ?></a>
                                </h4>
                                <?php  if ($member->isAdmin) { ?>
                                    <p class="label"><?php echo JText::_('COM_COMMUNITY_GROUPS_ADMINS')?></p>
                                <?php } ?>

                                <?php if ($member->getStatus()) { ?>
                                    <p><?php echo $member->getStatus(); ?></p>
                                <?php } ?>

                                <!-- .cIndex-Action -->
                                <ul class="unstyled inline">
                                    <?php if ($my->id != $member->id && $config->get('enablepm')): ?>
                                        <li>
                                            <i class="com-icon-mail-go"></i>
                                            <a onclick="joms.messaging.loadComposeWindow(<?php echo $member->id; ?>)" href="javascript:void(0);">
                                                <?php echo JText::_('COM_COMMUNITY_INBOX_SEND_MESSAGE'); ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <li>
                                        <i class="com-icon-groups"></i>
                                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=friends&userid=' . $member->id); ?>">
                                            <?php echo JText::sprintf((CStringHelper::isPlural($member->friendsCount)) ? 'COM_COMMUNITY_FRIENDS_COUNT_MANY' : 'COM_COMMUNITY_FRIENDS_COUNT', $member->friendsCount); ?>
                                        </a>
                                    </li>

                                    <?php if (!$member->approved && ($isMine || $isAdmin || $isSuperAdmin )): ?>
                                        <li id="groups-approve-<?php echo $member->id; ?>">
                                            <i class="com-icon-tick"></i>
                                            <a href="javascript:void(0);" onclick="jax.call('community', 'groups,ajaxApproveMember', '<?php echo $member->id; ?>', '<?php echo $groupid; ?>');">
                                                <?php echo JText::_('COM_COMMUNITY_PENDING_ACTION_APPROVE'); ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                                <!-- .cIndex-Action -->
                            </div>

                        </div>
                    </li>

                    <?php
                }
                ?>
            </ul>
        <?php endif; ?>
        <?php
    }
    ?>

    <?php
    if ($pagination->getPagesLinks()) {
        ?>
        <div class="cPagination">
            <?php echo $pagination->getPagesLinks(); ?>
        </div>
        <?php
    }
    ?>
</div>

<script>
    joms.jQuery('.dropdown-toggle').dropdown();
</script>