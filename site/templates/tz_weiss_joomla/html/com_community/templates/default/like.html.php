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
<?php if(COwnerHelper::isRegisteredUser()){ ?>
<div id="<?php echo $likeId ?>" class="like-snippet cLike">
	<?php if( $likes > 0 ){ ?>
		<?php if( $userLiked==COMMUNITY_LIKE ) { ?>
			<a class="meLike" href="javascript:void(0);" onclick="joms.like.unlike(this);" title="<?php echo JText::_('COM_COMMUNITY_LIKE_ITEM'); ?>. <?php echo JText::_('COM_COMMUNITY_UNLIKE'); ?>?"><i class="joms-icon-thumbs-up"></i><b><?php echo $likes; ?></b></a>
		<?php } else { ?>
			<a class="like-button" href="javascript:void(0);" onclick="joms.like.like(this)" title="<?php echo JText::_('COM_COMMUNITY_I_LIKE'); ?>"><i class="joms-icon-thumbs-up"></i><b><?php echo $likes; ?></b></a>
		<?php } ?>
	<?php } else { ?>
		<a class="like-button" href="javascript:void(0);" onclick="joms.like.like(this)"><i class="joms-icon-thumbs-up"></i><b><?php echo JText::_('COM_COMMUNITY_LIKE'); ?></b></a>
	<?php } ?>

	<?php if( $dislikes > 0 ){ ?>
		<?php if( $userLiked==COMMUNITY_DISLIKE ) { ?>
			<a class="meDislike" href="javascript:void(0);" onclick="joms.like.unlike(this);" title="<?php echo JText::_('COM_COMMUNITY_DISLIKE_ITEM'); ?>. <?php echo JText::_('COM_COMMUNITY_UNDISLIKE'); ?>?"><i class="joms-icon-thumbs-down"></i><b><?php echo $dislikes; ?></b></a>
		<?php } else { ?>
			<a class="dislike-button" href="javascript:void(0);" onclick="joms.like.dislike(this);" title="<?php echo JText::_('COM_COMMUNITY_DISLIKE'); ?>"><i class="joms-icon-thumbs-down"></i><?php echo empty($dislikes) ? '' : '<b>' . $dislikes . '</b>' ; ?></a>
		<?php } ?>
	<?php } else { ?>
		<a class="dislike-button" href="javascript:void(0);" onclick="joms.like.dislike(this);"><i class="joms-icon-thumbs-down"></i><?php echo empty($dislikes) ? '' : '<b>' . $dislikes . '</b>' ; ?></a>
	<?php } ?>
</div>
<?php } ?>
