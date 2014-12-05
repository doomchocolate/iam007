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
<div class="cGroup-Discussion cModule app-box">
	<h3 class="app-box-header cResetH"><?php echo JText::_('COM_COMMUNITY_GROUPS_RELATED_DISCUSSION_TITLE'); ?></h3>

	<div class="app-box-content">
		<ul class="app-box-list cResetList">
			<?php
				$i = 0;
				foreach ($discussions as $disc):
				$i++;
				if($i==4){break;} //break if it has more than 3
			?>
				<li>
					<b>
						<a class="cTitle" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $disc->groupid . '&topicid=' . $disc->id ); ?>">
							<?php echo $disc->title; ?>
						</a>
					</b>
					<div>
						<?php echo JHTML::_('string.truncate', $disc->lastmessage, 150) ?>
					</div>
					<div class="small">
						<a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $disc->groupid . '&topicid=' . $disc->id ); ?>">
							<?php echo JText::sprintf( (CStringHelper::isPlural($disc->count)) ? 'COM_COMMUNITY_TOTAL_REPLIES_MANY' : 'COM_COMMUNITY_GROUPS_DISCUSSION_REPLY_COUNT', $disc->count); ?>
						</a>
						<br />
						<?php echo JText::_('COM_COMMUNITY_GROUPS_RELATED_DISCUSSION_POSTED_IN');?>
						<a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid='. $disc->groupid ); ?>">
							<?php echo $disc->group_name; ?>
						</a>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>

