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

<div class="cInbox-Stream<?php if(isset($isMine) && $isMine) echo ' Mine'?> clearfix" id="message-<?php echo $msg->id; ?>" >
	<a href="<?php echo $authorLink;?>" class="cMessage-Avatar cFloat-L">
		<img src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>" width="48" class="cAvatar" />
	</a>

	<div class="cMessage-Body">
		<a class="btn btn-small cFloat-R" href="javascript:jax.call('community', 'inbox,ajaxRemoveMessage', <?php echo $msg->id; ?>);" title="<?php echo JText::_('COM_COMMUNITY_INBOX_REMOVE_MESSAGE'); ?>">
			<?php echo JText::_('COM_COMMUNITY_INBOX_REMOVE_MESSAGE'); ?>
		</a>
		<b class="cMessage-Author">
			<a href="<?php echo $authorLink;?>"><?php echo $user->getDisplayName(); ?></a>
		</b>
		<br />
		<small class="cMessage-Time">
		<?php
			$postdate =  CTimeHelper::timeLapse(CTimeHelper::getDate($msg->posted_on));
			echo $postdate;
		?>
		</small>

		<div class="cMessage-Content">
			<?php echo filter_var(htmlspecialchars_decode($msg->body), FILTER_UNSAFE_RAW); ?>
		</div>
	</div>
</div>
