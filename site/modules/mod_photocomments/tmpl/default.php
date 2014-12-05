<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="cModule-PhotoComments" class="cMods cMods-PhotoComments<?php echo $params->get('moduleclass_sfx'); ?>">
    <?php
    if ($comments) {
        $i = 1;
        $total = count($comments);
        $char_limit = intval($params->get('character_limit'));
        $captionLimit = intval($params->get('caption_limit'));
        foreach ($comments as $comment) {

            if (($char_limit > 0) && (JString::strlen($comment->comment) > $char_limit)) {
                $comment->comment = JString::substr($comment->comment, 0, $char_limit) . '...';
            }
            if (($captionLimit > 0) && (JString::strlen($comment->caption) > $captionLimit)) {
                $comment->caption = JString::substr($comment->caption, 0, $captionLimit) . '...';
            }
            $poster = CFactory::getUser($comment->post_by);

            if ($comment->phototype == PHOTOS_USER_TYPE) {
                $link = CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $comment->albumid . '&photoid=' . $comment->contentid . '&userid=' . $comment->creator); // . '#photoid=' . $comment->contentid;
            } else {
                $link = CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $comment->albumid . '&photoid=' . $comment->contentid . '&groupid=' . $comment->groupid); // . '#photoid=' . $comment->contentid;
            }
            ?>
            <div class="cMod-Row">
                <a href="<?php echo $link; ?>" class="cThumb-Avatar l-float">
                    <img src="<?php echo $poster->getThumbAvatar(); ?>" width="45" height="45" alt="" />
                </a>
                <div class="cThumb-Detail">
                    <a href="<?php echo $link; ?>" class="cThumb-Title"><?php echo $comment->caption; ?></a>
                    <div class="cThumb-Brief">
                        <?php echo CStringHelper::escape($comment->comment); ?>
                    </div>
                </div>
            </div>
            <?php
            $i++;
        }
    } else {
        ?>
        <?php echo JText::_('MOD_PHOTOCOMMENTS_NO_COMMENTS'); ?>
        <?php
    }
    ?>
</div>