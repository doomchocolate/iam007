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
$moduleCount = count(JModuleHelper::getModules('js_side_frontpage')) + count(JModuleHelper::getModules('js_side_top'));
$class = ($moduleCount > 0) ? 'span8' : 'span12';
$jinput = JFactory::getApplication()->input;
/**
 * @todo 3.3
 * All of these code must be provided in object. DO NOT PUT ANY CODE LOGIC HERE !
 */
$filter = $jinput->get('filter');
$filterValue = $jinput->get('value');
$filterText = JText::_("COM_COMMUNITY_FILTERBAR_ALL");
if ( $filter == 'apps') {
    switch ($filterValue) {
        case 'profile':
            $filterText = JText::_("COM_COMMUNITY_FILTERBAR_TYPE_STATUS");
            break;
        case 'photo':
            $filterText = JText::_("COM_COMMUNITY_FILTERBAR_TYPE_PHOTO");
            break;
        case 'video':
            $filterText = JText::_("COM_COMMUNITY_FILTERBAR_TYPE_VIDEO");
            break;
        case 'group':
            $filterText = JText::_("COM_COMMUNITY_FILTERBAR_TYPE_GROUP");
            break;
        case 'event':
            $filterText = JText::_("COM_COMMUNITY_FILTERBAR_TYPE_EVENT");
            break;
    }
} else {
    switch ($filterValue) {
        case 'me-and-friends':
            $filterText = JText::_("COM_COMMUNITY_FILTERBAR_RELATIONSHIP_ME_AND_FRIENDS");
            break;
    }
}
?>
<script type="text/javascript">joms.filters.bind();</script>
<!-- begin: #cFrontpageWrapper -->

<?php
/**
 * if user logged in
 * 		load frontpage.members.php
 * else
 * 		load frontpage.guest.php
 */
echo $header;
?>

<div class="row-fluid">
    <div class="<?php echo $class; ?>">
        <!-- begin: .cMain -->
        <div class="cMain">
            <?php
            /**
             * ----------------------------------------------------------------------------------------------------------
             * Activity stream section here
             * ----------------------------------------------------------------------------------------------------------
             */
            ?>
            <?php if ($config->get('showactivitystream') == '1' || ($config->get('showactivitystream') == '2' && $my->id != 0 )) { ?>

                <!-- Recent Activities
                <h3><?php // echo JText::_('COM_COMMUNITY_FRONTPAGE_RECENT_ACTIVITIES');     ?></h3>
                -->

                <?php $userstatus->render(); ?>

                <?php if ($alreadyLogin == 1) : ?>

                    <div class="joms-activity-filter clearfix">
                        <div class="joms-activity-filter-action">
                            <a><i class="joms-icon-caret-down"></i><?php echo JText::_("COM_COMMUNITY_FILTERBAR_FILTERBY"); ?></a>
                            <span class="joms-activity-filter-status" data-default="<?php echo JText::_("COM_COMMUNITY_FILTERBAR_ALL"); ?>"><?php echo $filterText; ?></span>
                        </div>
                        <form class="reset-gap">

                            <ul class="unstyled joms-activity-filter-dropdown joms-postbox-dropdown" style="display: none">
                                <li data-filter="all" class="<?php echo ($filter == 'all') ? 'active' : ''; ?>" ><i class="joms-icon-star"></i> <?php echo JText::_("COM_COMMUNITY_FILTERBAR_ALL"); ?></li>
                                <li data-filter="apps" data-value="profile" class="<?php echo ($filter == 'apps' && $filterValue == "profile") ? 'active' : ''; ?>"><i class="joms-icon-pencil"></i> <?php echo JText::_("COM_COMMUNITY_FILTERBAR_STATUS"); ?></li>
                                <li data-filter="apps" data-value="photo" class="<?php echo ($filter == 'apps' && $filterValue == "photo") ? 'active' : ''; ?>"><i class="joms-icon-picture"></i> <?php echo JText::_("COM_COMMUNITY_FILTERBAR_PHOTO"); ?></li>
                                <li data-filter="apps" data-value="video" class="<?php echo ($filter == 'apps' && $filterValue == "video") ? 'active' : ''; ?>"><i class="joms-icon-videocam"></i> <?php echo JText::_("COM_COMMUNITY_FILTERBAR_VIDEO"); ?></li>
                                <li data-filter="apps" data-value="group" class="<?php echo ($filter == 'apps' && $filterValue == "group") ? 'active' : ''; ?>"><i class="joms-icon-users"></i> <?php echo JText::_("COM_COMMUNITY_FILTERBAR_GROUP"); ?></li>
                                <li data-filter="apps" data-value="event" class="<?php echo ($filter == 'apps' && $filterValue == "event") ? 'active' : ''; ?>"><i class="joms-icon-calendar"></i> <?php echo JText::_("COM_COMMUNITY_FILTERBAR_EVENT"); ?></li>
                                <li data-filter="privacy" data-value="me-and-friends" class="<?php echo ($filter == 'privacy' && $filterValue == "me-and-friends") ? 'active' : ''; ?>"><i class="joms-icon-user2"></i> <?php echo JText::_("COM_COMMUNITY_FILTERBAR_MEANDFRIENDS"); ?></li>
                            </ul>
                        </form>
                    </div>




                <?php endif; ?>

                <div class="cActivity cFrontpage-Activity" id="activity-stream-container">
                    <div class="cActivity-LoadLatest joms-latest-activities-container">
                        <a id="activity-update-click" class="btn btn-block" href="javascript:void(0);"></a>
                    </div>
                    <?php echo $userActivities; ?>
                </div>
            <?php } ?>
        </div>
        <!-- end: .cMain -->

    </div>
    <?php if ($moduleCount > 0) { ?>
        <div class="span4">
            <div class="cSidebar">
                <?php $this->renderModules('js_side_top'); ?>
                <?php $this->renderModules('js_side_frontpage'); ?>

            </div>
            <!-- end: .cSidebar -->
        </div>
    <?php } ?>
</div>
