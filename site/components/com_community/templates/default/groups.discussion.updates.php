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
	<?php if( $discussions ) {?>
	<h3 class="cStreamTitle cResetH"><?php echo JText::_('COM_COMMUNITY_GROUPS_PARTICIPATED_DISCUSSION_UPDATE'); ?></h3>
	<ul class="cStreamList forDiscussion pushedUp cResetList">
		<?php foreach($discussions as $discussion){ ?>
		<li>
			<b class="cStream-Avatar cFloat-L">
				<img src="<?php echo CFactory::getUser($discussion['post_by'])->getThumbAvatar(); ?>" class="cAvatar" />
			</b>
			<div class="joms-stream-content">
				<div class="cStream-Headline">
					<a href="<?php echo $discussion['discussion_link'] ?>" ><?php echo $discussion['title']; ?></a>
					<span class="arrow">&#9654;</span>
					<a href="<?php echo $discussion['group_link']; ?>" > <?php echo $discussion['group_name']; ?> </a>
				</div>
				<div class="cStream-Attachment">
					<?php echo substr($discussion['comment'],0,250); if(strlen($discussion['comment']) > 250){echo ' ...';} ?>
				</div>
				<div class="cStream-Actions clearfix small">
					<span><?php echo $discussion['created_by']; ?></span>
					<span><?php echo $discussion['created_interval']; ?></span>
				</div>
			</div>
		</li>
		<?php } ?>
	</ul>
	<?php } ?>