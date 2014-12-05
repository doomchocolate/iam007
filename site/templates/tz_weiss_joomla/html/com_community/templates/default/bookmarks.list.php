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

<h5><?php echo JText::_('COM_COMMUNITY_SHARE_THIS_VIA_LINK');?></h5>


<p>
	<b></b>
</p>
<ul class="js-bookmarks unstyled clearfix">
	<?php
	foreach($bookmarks as $bookmark)
	{
	?>
	<li><a href="<?php echo $bookmark->link;?>" target="_blank" class="<?php echo $bookmark->className;?>"><?php echo $this->escape($bookmark->name); ?></a></li>
	<?php
	}
	?>
</ul>
<?php if($config->get( 'shareviaemail' )){ ?>
<hr class="cSeperator" />

<form id="bookmarks-email" class="cForm">
	<ul class="cFormList cFormVertical cResetList">
		<li>
			<label class="form-label"><?php echo JText::_('COM_COMMUNITY_SHARE_THIS_VIA_EMAIL');?></label>
			<div class="form-field">
				<div class="input-wrap">
					<input type="text" id="bookmarks-email" name="bookmarks-email" class="input-block-level bookmarks-email" />
				</div>
				<span class="form-helper"><?php echo JText::_('COM_COMMUNITY_SHARE_THIS_VIA_EMAIL_INFO');?></span>
			</div>
		</li>
		<li>
			<label class="form-label"><?php echo JText::_('COM_COMMUNITY_SHARE_THIS_MESSAGE');?></label>
			<div class="form-field">
				<div class="input-wrap">
					<p>
						<textarea rows="3" class="input-block-level bookmarks-message" id="bookmarks-message" name="bookmarks-message"></textarea>
					</p>
				</div>
			</div>
		</li>
	</ul>
</form>
<?php } ?>