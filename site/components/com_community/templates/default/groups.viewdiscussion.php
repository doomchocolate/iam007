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

$string = 'COM_COMMUNITY_GROUPS_DISCUSSION_CREATOR_TIME_LINK';

if($isTimeLapsed == 'lapse')
{
	$string = 'COM_COMMUNITY_GROUPS_DISCUSSION_CREATOR_TIME_LINK_LAPSED';
}
?>
<!-- COMMUNITY: START cLayout -->
<div class="cLayout cPage cGroup-ViewDiscussion">

	<div class="cPageActions clearfix">
		<div class="cPageAction cFloat-R">
			<?php echo $reportHTML;?>
			<?php echo $bookmarksHTML;?>
		</div>

		<div class="cPageMeta cFloat-L">
			<i class="update-icon com-icon-comment"></i>
			<span><?php echo JText::sprintf($string , $creatorLink , $creator->getDisplayName() , $discussion->created);?></span>
		</div>
	</div>

	<div class="row-fluid">
		<div class="span8">
			<div class="cMain">
				<div class="cPageStory clearfull">
					<!--Discussion : Avatar-->
					<a href="<?php echo CUrlHelper::userLink($creator->id); ?>" class="cPageStory-Author cFloat-L"><img class="cAvatar" src="<?php echo $creator->getThumbAvatar(); ?>" border="0" alt="" /></a>
					<!--Discussion : Avatar-->

					<!--Discussion : Detail-->
					<div class="cPageStory-Content">
						<?php echo $discussion->message; ?>
					</div>
					<!--Discussion : Detail-->
				</div>

				<div class="cPageStory-Replies">
					<?php if($config->get('group_discuss_order') == 'DESC'){ ?>
						<div id="wallForm" class="cWall-Form"><?php echo $wallForm; ?></div>
						<div id="wallContent" class="cWall-Content"><?php echo $wallContent; ?></div>
					<?php } else { ?>
						<div id="wallContent" class="cWall-Content"><?php echo $wallContent; ?></div>
						<div id="wallForm" class="cWall-Form"><?php echo $wallForm; ?></div>
					<?php } ?>
				</div>

			</div>
		</div>
		<div class="span4">
			<div class="cSidebar">
				<?php echo $filesharingHTML;?>

				<?php
					$keywords = explode(' ',$discussion->title);
					echo $this->view('groups')->modRelatedDiscussion($keywords);
				?>
			</div>
		</div>
	</div>

</div>
<!-- COMMUNITY: END cLayout -->