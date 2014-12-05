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

<div class="cLayout cProfile-Preferences">
    <form class="cForm" method="post" action="<?php echo CRoute::getURI(); ?>" name="saveProfile">
        <ul class="cPageTabs cResetList cFloatedList clearfix">
            <li><a href="#generalPref"><?php echo JText::_('COM_COMMUNITY_PROFILE_PREFERENCES_GENERAL'); ?></a></li>
            <li><a id='privacyPref' href="#privacyPref"><?php echo JText::_('COM_COMMUNITY_PROFILE_PREFERENCES_PRIVACY'); ?></a></li>
            <li><a id='emailPref' href="#emailPref"><?php echo JText::_('COM_COMMUNITY_PROFILE_PREFERENCES_EMAIL'); ?></a></li>
            <li><a href="#blockignorePref"><?php echo JText::_('COM_COMMUNITY_PROFILE_PREFERENCES_BLOCKIGNORELIST'); ?></a></li>
        </ul>

        <div class="cTabsContent">

            <div id="generalPref">
                <div class="ctitle"><h3><?php echo JText::_('COM_COMMUNITY_EDIT_PREFERENCES'); ?></h3></div>
                <ul class="cFormList cFormHorizontal cResetList">
                    <?php echo $beforeFormDisplay; ?>
                    <li>
                        <label for="activityLimit" class="form-label">
                            <?php echo JText::_('COM_COMMUNITY_PREFERENCES_ACTIVITY_LIMIT'); ?>
                        </label>
                        <div class="form-field">
                            <input type="text" id="activityLimit" class="jomNameTips input text" title="<?php echo JText::_('COM_COMMUNITY_PREFERENCES_ACTIVITY_LIMIT_DESC'); ?>" name="activityLimit" value="<?php echo $params->get('activityLimit', 20); ?>" size="5" maxlength="3" />
                        </div>
                    </li>
                    <li>
                        <label for="profileLikes" class="form-label">
                            <?php echo JText::_('COM_COMMUNITY_PREFERENCES_PROFILE_LIKES'); ?>
                        </label>
                        <div class="form-field">
                            <label class="label-checkbox">
                                <input type="checkbox" class="input checkbox title jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_PROFILE_LIKE_ENABLE_DESC'); ?>" value="1" id="profileLikes-yes" name="profileLikes" <?php if ($params->get('profileLikes', 1) == 1) { ?>checked="checked" <?php } ?>/>
                                <?php echo JText::_('COM_COMMUNITY_PROFILE_LIKE_ENABLE'); ?>
                            </label>
                        </div>
                    </li>
                    <li>
                        <label class="form-label">
                            <?php echo JText::_('COM_COMMUNITY_PREFERENCES_PROFILE_VIEW'); ?>
                        </label>
                        <div class="form-field">
                            <div class="form-privacy inline">
                                <?php echo CPrivacy::getHTML('privacyProfileView', $params->get('privacyProfileView'), COMMUNITY_PRIVACY_BUTTON_LARGE, array('public' => true, 'members' => true, 'friends' => true, 'self' => false)); ?>
                            </div>
                        </div>
                    </li>

                    <?php if ($jConfig->get('sef')) { ?>
                        <li class="has-seperator">
                            <label class="form-label">
                                <?php echo JText::_('COM_COMMUNITY_YOUR_PROFILE_URL'); ?>
                            </label>
                            <div class="form-field show-info">
                                <?php echo JText::sprintf('COM_COMMUNITY_YOUR_CURRENT_PROFILE_URL', $prefixURL); ?>
                            </div>
                        </li>
                    <?php } ?>

                    <li class="has-seperator">
                        <div class="form-field">
                            <input type="submit" class="btn btn-primary" value="<?php echo JText::_('COM_COMMUNITY_SAVE_BUTTON'); ?>" />
                        </div>
                    </li>
                </ul>
            </div>

            <div id="privacyPref">
                <!-- friends privacy -->
                <div class="ctitle"><h3><?php echo JText::_('COM_COMMUNITY_EDIT_YOUR_PRIVACY'); ?></h3></div>
                <ul class="cFormList cFormHorizontal cResetList">
                    <li>
                        <label class="form-label">
                            <?php echo JText::_('COM_COMMUNITY_PRIVACY_FRIENDS_FIELD'); ?>
                        </label>
                        <div class="form-field">
                            <div class="form-privacy inline">
                                <?php echo CPrivacy::getHTML('privacyFriendsView', $params->get('privacyFriendsView'), COMMUNITY_PRIVACY_BUTTON_LARGE); ?>
                            </div>
                        </div>
                    </li>
                    <!-- photos privacy -->
                    <?php if ($config->get('enablephotos')): ?>
                        <li>
                            <label class="form-label">
                                <?php echo JText::_('COM_COMMUNITY_PRIVACY_PHOTOS_FIELD'); ?>
                            </label>
                            <div class="form-field">
                                <div class="form-privacy inline">
                                    <?php echo CPrivacy::getHTML('privacyPhotoView', $params->get('privacyPhotoView'), COMMUNITY_PRIVACY_BUTTON_LARGE); ?>
                                </div>
                                <label class="label-checkbox inline">
                                    <input type="checkbox" name="resetPrivacyPhotoView" class="input checkbox" value="1"/> <?php echo JText::_('COM_COMMUNITY_PHOTOS_PRIVACY_APPLY_TO_ALL'); ?>
                                </label>
                            </div>
                        </li>
                    <?php endif; ?>
                    <!-- videos privacy -->
                    <?php if ($config->get('enablevideos')): ?>
                        <li>
                            <label class="form-label">
                                <?php echo JText::_('COM_COMMUNITY_PRIVACY_VIDEOS_FIELD'); ?>
                            </label>
                            <div class="form-field">
                                <div class="form-privacy inline">
                                    <?php echo CPrivacy::getHTML('privacyVideoView', $params->get('privacyVideoView'), COMMUNITY_PRIVACY_BUTTON_LARGE); ?>
                                </div>
                                <label class="label-checkbox inline">
                                    <input type="checkbox" name="resetPrivacyVideoView" class="input checkbox" value="1"/> <?php echo JText::_('COM_COMMUNITY_VIDEOS_PRIVACY_RESET_ALL'); ?>
                                </label>
                            </div>
                        </li>
                    <?php endif; ?>
                    <?php if ($config->get('enablegroups')) { ?>
                        <!-- groups privacy -->
                        <li>
                            <label class="form-label">
                                <?php echo JText::_('COM_COMMUNITY_PRIVACY_GROUPS_FIELD'); ?>
                            </label>
                            <div class="form-field">
                                <div class="form-privacy inline">
                                    <?php echo CPrivacy::getHTML('privacyGroupsView', $params->get('privacyGroupsView'), COMMUNITY_PRIVACY_BUTTON_LARGE); ?>
                                </div>
                            </div>
                        </li>
                    <?php } ?>

                    <li class="has-seperator">
                        <div class="form-field">
                            <input type="submit" class="btn btn-primary" value="<?php echo JText::_('COM_COMMUNITY_SAVE_BUTTON'); ?>" />
                        </div>
                    </li>
                </ul>
            </div>

            <div id="emailPref">
                <div class="ctitle"><h3><?php echo JText::_('COM_COMMUNITY_EDIT_EMAIL_PRIVACY'); ?></h3></div>
                <ul class="cCheckList cResetList">
                    <?php
                    if ($config->get('privacy_search_email') == 1) {
                        ?>
                        <li class="check-row clearfix">
                            <label for="search_email" class="label-checkbox">
                                <input type="checkbox" class="input checkbox" value="1" id="email-email-yes" name="search_email" <?php if ($my->get('_search_email') == 1) { ?>checked="checked" <?php } ?>/>
                                <?php echo JText::_('COM_COMMUNITY_PRIVACY_EMAIL'); ?>
                                <!--input type="hidden" name="search_email" value="0" /-->
                            </label>
                        </li>
                        <?php
                    }
                    ?>
                    <!-- Start New email preference -->
                    <li class="check-header clearfix tableHeader">
                        <div class="check-control cFloat-R">
                            <b><?php echo JText::_('COM_COMMUNITY_PRIVACY_EMAIL_LABEL'); ?></b>
                            <b><?php echo JText::_('COM_COMMUNITY_PRIVACY_NOTIFICATION_LABEL'); ?></b>
                        </div>
                    </li>
                    <?php
                    $isadmin = COwnerHelper::isCommunityAdmin();
                    foreach ($notificationTypes->getTypes() as $group) {
                        if ($notificationTypes->isAdminOnlyGroup($group->description) && !$isadmin) {
                            continue;
                        }
                        ?>
                        <li class="check-section clearfix">
                            <div class="check-control cFloat-R">
                                <b><input type="checkbox" class="input checkbox" onclick="toggleChecked('email<?php echo JText::_($group->description); ?>', this.checked)" ></b>
                                <b><input type="checkbox" class="input checkbox" onclick="toggleChecked('global<?php echo JText::_($group->description); ?>', this.checked)" ></b>
                            </div>

                            <b><?php echo JText::_($group->description); ?></b>
                        </li>
                        <?php
                        foreach ($group->child as $id => $type) {
                            if ($type->adminOnly && !$isadmin)
                                continue;
                            $emailId = $notificationTypes->convertEmailId($id);
                            $emailset = $params->get($emailId, $config->get($emailId));
                            $notifId = $notificationTypes->convertNotifId($id);
                            $notifset = $params->get($notifId, $config->get($notifId));
                            ?>
                            <li class="check-row clearfix">
                                <div class="check-control cFloat-R">
                                    <b>
                                        <input type="hidden" name="<?php echo $emailId; ?>" value="0" />
                                        <input id="<?php echo $emailId; ?>" type="checkbox" name="<?php echo $emailId; ?>" value="1" <?php if ($emailset == 1) echo 'checked="checked"'; ?> class="input checkbox email<?php echo JText::_($group->description); ?>" /></b>
                                    <b>
                                        <input type="hidden" name="<?php echo $notifId; ?>" value="0" />
                                        <input id="<?php echo $notifId; ?>" type="checkbox" name="<?php echo $notifId; ?>" value="1" <?php if ($notifset == 1) echo 'checked="checked"'; ?> class="input checkbox global<?php echo JText::_($group->description); ?>" /></b>

                                </div>

                                <label><?php echo JText::_($type->description); ?></label>
                            </li>
                            <?php
                        }
                    }
                    ?>

                    <!-- End New email preference -->
                    <?php echo $afterFormDisplay; ?>
                </ul>
                <input type="submit" class="btn btn-primary" value="<?php echo JText::_('COM_COMMUNITY_SAVE_BUTTON'); ?>" />
            </div>

            <div id="blockignorePref">
                <div class="ctitle"><h3><?php echo JText::_('COM_COMMUNITY_MY_BLOCKEDIGNORED_LIST'); ?></h3></div>
                <ul class="unstyled joms-blocked-ignored">
                    <?php
                    foreach ($blockedUsers as $row) {
                        $user = CFactory::getUser($row->blocked_userid);
                        ?>
                        <li class="clearfix">
                            <img src="<?php echo $user->getAvatar(); ?>" alt="">
                            <article>
                                <h5 class="reset-gap"><?php echo $user->getDisplayName(); ?></h5>
                                <p><?php echo JText::_('COM_COMMUNITY_PREFERENCES_' . strtoupper(($row->type == 'block') ? 'blocked' : 'ignored' )); ?></p>
                                <ul class="unstyled joms-action">
                                    <?php if ($row->type == 'block' || $row->type == '') { ?>
                                        <li><a href="#" onClick="joms.users.confirmUnBlockUser(<?php echo $user->id; ?>);"><?php echo JText::_('COM_COMMUNITY_PREFERENCES_UNBLOCK'); ?></a></li>
                                        <li><a href="#" onClick="joms.users.confirmIgnoreUser(<?php echo $user->id; ?>);"><?php echo JText::_('COM_COMMUNITY_PREFERENCES_IGNORE'); ?></a></li>
                                    <?php } else { ?>
                                        <li><a href="#" onClick="joms.users.confirmUnIgnoreUser(<?php echo $user->id; ?>);" ><?php echo JText::_('COM_COMMUNITY_PREFERENCES_UNIGNORE'); ?></a></li>
                                        <li><a href="#" onClick="joms.users.confirmBlockUser(<?php echo $user->id; ?>);"><?php echo JText::_('COM_COMMUNITY_PREFERENCES_BLOCK'); ?></a></li>
                                    <?php } ?>
                                </ul>
                            </article>
                        </li>
                    <?php } ?>
                </ul>
            </div>


        </div>
    </form>
</div>

<script type="text/javascript">
    function toggleChecked(className, status) {
        joms.jQuery("." + className).each(function() {
            joms.jQuery(this).attr("checked", status);
        })
    }

    joms.jQuery(document).ready(function() {
        joms.privacy.init();

        var tabContainers = joms.jQuery('.cTabsContent > div');

        var url = document.location.href;

        var filter = ':first';
        if (url.indexOf("#privacyPref") !== -1)
        {
            filter = '#privacyPref';
        }
        if (url.indexOf("#emailPref") !== -1)
        {
            filter = '#emailPref';
        }

        joms.jQuery('.cPageTabs li a').click(function() {
            tabContainers.hide().filter(this.hash).fadeIn(500);
            joms.jQuery(this).parent().addClass('cTabCurrent');
            joms.jQuery(this).parent().siblings().removeClass('cTabCurrent');
            return false;
        }).filter(filter).click();

    });
</script>