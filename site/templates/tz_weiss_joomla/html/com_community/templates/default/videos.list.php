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
<a id="lists" name="listing"></a>
<?php if ($videos) { ?>
    <ul class="cMedia-ThumbList Videos cResetList cFloatedList clearfix">
        <?php $i = 0;
        foreach ($videos as $video) {
            ?>

            <li class="video-permission-<?php echo $video->permissions  ?>">
                <div class="cMedia-Box">

                    <?php
                    /**
                     * ----------------------------------------------------------------------------------------------------------
                     * VIDEO THUMBNAIL
                     * ----------------------------------------------------------------------------------------------------------
                     */
                    ?>
                    <div class="cMedia-VideoCover">
                        <a class="cMedia-Thumb cVideo-Thumb" href="<?php echo $video->getURL(); ?>">
                            <img src="<?php echo $video->getThumbnail(); ?>" width="<?php echo $videoThumbWidth; ?>" height="<?php echo $videoThumbHeight; ?>" alt="" />
                            <?php if (!$video->isPending()): ?>
                                <b class="cVideo-Duration"><?php echo $video->getDurationInHMS(); ?></b>
                            <?php endif; ?>
                            <?php
                            if ($isCommunityAdmin && !$groupVideo && $showFeatured) {
                                if (in_array($video->id, $featuredList)) {
                                    ?>
                                    <span class="cMedia-Featured"></span>
                                    <?php
                                }
                            }
                            ?>
                        </a>

                        <div class="visible-desktop">
                            <?php if ($isCommunityAdmin || ($video->isOwner() && !$groupVideo) || ($groupVideo && $allowManageVideos)) { ?>
                                <div class="cMedia-Actions small<?php if (!in_array($video->id, $featuredList)) { ?> featured<?php } ?> ">
                                    <div>
                                        <a title="<?php echo JText::_('COM_COMMUNITY_EDIT') ?>" href="javascript:void(0);" onclick="joms.videos.showEditWindow('<?php echo $video->getId(); ?>', '<?php echo $redirectUrl; ?>');">
                                            <i class="com-icon-form"></i>
                                        <?php // echo JText::_('COM_COMMUNITY_EDIT')   ?>
                                        </a>
                                        <?php if ($isCommunityAdmin || ($video->isOwner() )) { ?>
                                            <a title="<?php echo JText::_('COM_COMMUNITY_DELETE') ?>" href="javascript:void(0);" onclick="joms.videos.deleteVideo('<?php echo $video->getId(); ?>', '<?php echo $currentTask; ?>', '<?php echo CFactory::getRequestUser()->id; ?>');">
                                                <i class="com-icon-block"></i>
                                            <?php // echo JText::_('COM_COMMUNITY_DELETE')   ?>
                                            </a>
                                        <?php } ?>
                                        <?php
                                        if ($isCommunityAdmin && !$groupVideo && $showFeatured && $video->permissions == 0) {
                                            if (!in_array($video->id, $featuredList)) {
                                                ?>
                                                <a id="featured-<?php echo $video->getId(); ?>" onclick="joms.featured.add('<?php echo $video->getId(); ?>', 'videos');" href="javascript:void(0);">
                                                    <i class="com-icon-award-plus"></i>
                                                </a>
                                                <?php
                                            } else {
                                                ?>
                                                <a title="<?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?>" onclick="joms.featured.remove('<?php echo $video->getId(); ?>', 'videos');" href="javascript:void(0);">
                                                    <i class="com-icon-award-minus"></i>
                                                <?php // echo JText::_('COM_COMMUNITY_REMOVE_FEATURED');  ?>
                                                </a>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <?php
                    /**
                     * ----------------------------------------------------------------------------------------------------------
                     * VIDEO SUMMARY
                     * ----------------------------------------------------------------------------------------------------------
                     */
                    ?>
                    <div class="cMedia-Summary">
                        <div class="cMedia-Title">
                            <?php if ($video->isPending()) { ?>
                                <b><?php echo $video->getTitle(); ?></b>
                            <?php } else { ?>
                                <a href="<?php echo $video->getURL(); ?>"><?php echo $video->getTitle(); ?></a>
                            <?php } ?>
                        </div>

                        <div class="cMedia-Hit">
                            <?php
                            if (CStringHelper::isPlural($video->getHits())) {
                                echo JText::sprintf('COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY', $video->getHits());
                            } else {
                                echo JText::sprintf('COM_COMMUNITY_VIDEOS_HITS_COUNT', $video->getHits());
                            }
                            ?>

                            <?php
                            // Show access class for "friends (30)" or "me only (40)"
                            $accessClass = 'public'; // NO need to display this
                            $accessClass = ($video->permissions == PRIVACY_MEMBERS) ? 'site' : $accessClass;
                            $accessClass = ($video->permissions == PRIVACY_FRIENDS) ? 'friends' : $accessClass;
                            $accessClass = ($video->permissions == PRIVACY_PRIVATE) ? 'me' : $accessClass;

                            $accessTitle = "";
                            $accessTitle = ($accessClass == 'site') ? JText::_('COM_COMMUNITY_PRIVACY_TITLE_SITE_MEMBERS') : $accessTitle;
                            $accessTitle = ($accessClass == 'friends') ? JText::_('COM_COMMUNITY_PRIVACY_TITLE_FRIENDS') : $accessTitle;
                            $accessTitle = ($accessClass == 'me') ? JText::_('COM_COMMUNITY_PRIVACY_TITLE_ME') : $accessTitle;

                            if ($accessClass != 'public') { ?>
                                <span>
                                    <i class="com-glyph-lock-<?php echo $accessClass; ?>" title="<?php echo $accessTitle; ?>"></i>
                                </span>
                            <?php } ?>
                        </div>

                        <div class="cMedia-Details small">
                            <span class="cMedia-LastUpdate">
                                <?php echo $video->getLastUpdated(); ?>
                            </span>

                            <span class="cMedia-Author">
                                <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $video->creator); ?>">
                                    <?php echo $video->getCreatorName(); ?>
                                </a>
                            </span>
                        </div>
                    </div>
                </div> <!-- end: .cMedia-Box -->
            </li>
            <?php
            $i++;

            if ($i % 4 == 0) {
                $i = 0;
                ?>
                <div class="clearfull"></div>
                <?php
            }
        } //end foreach
        ?>
        <!--end: VIDEO ITEM-->
    </ul>

    <!---end: VIDEO ITEM(S)-->

    <?php
    if (!empty($pagination)) {
        ?>
        <div class="cPagination">
        <?php echo $pagination->getPagesLinks(); ?>
        </div>
        <?php
    }
    ?>

    <?php
} else {
    $mainframe = JFactory::getApplication();
    $jinput = $mainframe->input;
    $task = $jinput->get('task');
    switch ($task) {
        case 'mypendingvideos':
            $msg = JText::_('COM_COMMUNITY_VIDEOS_PENDING_VIDEOS');
            break;
        case 'search':
            $msg = JText::_('COM_COMMUNITY_NO_RESULT');
            break;
        case 'myvideos':
            $isMine = ($user->id == $my->id);
            $msg = $isMine ? JText::_('COM_COMMUNITY_VIDEOS_NO_VIDEO') : JText::sprintf('COM_COMMUNITY_VIDEOS_NO_VIDEOS', $user->getDisplayName());
            break;
        default:
            $msg = JText::_('COM_COMMUNITY_VIDEOS_NO_VIDEO');
            break;
    }
    ?>
    <div class="cAlert cEmpty"><?php echo $msg; ?></div>
    <?php
}
?>
