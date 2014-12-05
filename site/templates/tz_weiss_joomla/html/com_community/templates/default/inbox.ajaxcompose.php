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

$jnow		= CTimeHelper::getDate();
?>

<div id="writeMessageContainer">
	<form name="jsform-inbox-ajaxcompose" method="post" action="" name="writeMessageForm" id="writeMessageForm">
		<ul class="cFormList cFormVertical cResetList">
			<li>
				<div class="form-field clearfix">
					<img src="<?php echo $user->getThumbAvatar(); ?>" height="50" alt="<?php echo $user->getDisplayName(); ?>" class="cFloat-L" />
					<strong>
						&nbsp;&nbsp;
						<?php echo $user->getDisplayName(); ?>
						&nbsp;&nbsp;
					</strong>
					<br />
					<span class="small">
						&nbsp;&nbsp;
						<?php echo $jnow->format( JText::_('DATE_FORMAT_LC2') );?>
						&nbsp;&nbsp;
					</span>
				</div>
			</li>
			<li>
				<label for="subject" class="form-label"><?php echo JText::_('COM_COMMUNITY_COMPOSE_SUBJECT'); ?></label>
				<div class="form-field">
					<div class="input-wrap">
						<input class="input-block-level" type="text" value="<?php echo (empty($subject))?'':$subject; ?>" id="subject" name="subject" />
					</div>
				</div>
			</li>
			<li>
				<label for="body" class="form-label"><?php echo JText::_('COM_COMMUNITY_COMPOSE_MESSAGE'); ?></label>
				<div class="form-field">
					<div class="input-wrap">
						<p>
							<textarea class="input-block-level" id="body" name="body" ><?php echo (empty($body))?'':$body;; ?></textarea>
						</p>
					</div>
				</div>
			</li>
		</ul>
		<input type="hidden" value="<?php echo $user->id; ?>" name="to" />
	</form>
</div>
