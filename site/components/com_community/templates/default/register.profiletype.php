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
<form action="<?php echo CRoute::getURI(); ?>" method="post" id="jomsForm" name="jomsForm" class="community-form-validate">
<div class="jsProfileType">
	<ul class="unstyled">
	<?php
		foreach($profileTypes as $profile)
		{
	?>
		<li class="space-12">

			<label for="profile-<?php echo $profile->id;?>" class="radio">
				<input id="profile-<?php echo $profile->id;?>" type="radio" value="<?php echo $profile->id;?>" name="profileType" <?php echo $default == $profile->id ? ' disabled CHECKED' :'';?> />
			  <strong class="bold"><?php echo $profile->name;?></strong>
			</label>
			<?php if( $profile->approvals ){?>
				<span class="help-block"><?php echo JText::_('COM_COMMUNITY_REQUIRE_APPROVAL');?></span>
			<?php } ?>

			<span class="help-block">
				<?php echo $profile->description;?>
			</span>

			<?php if( $default == $profile->id ){?>
					<em><?php echo JText::_('COM_COMMUNITY_ALREADY_USING_THIS_PROFILE_TYPE');?></em>
			<?php } ?>



		</li>
	<?php
		}
	?>
	</ul>
</div>
<?php if( (count($profileTypes) == 1 && $profileTypes[0]->id != $default) || count($profileTypes) > 1 ){?>
<div style="margin-top: 5px;">
	<?php if( $showNotice ){ ?>
	<span style="color: red;font-weight:700;"><?php echo JText::_('COM_COMMUNITY_NOTE');?>:</span>
	<span><?php echo $message;?></span>
	<?php } ?>
</div>
<table class="ccontentTable paramlist" cellspacing="1" cellpadding="0">
  <tbody>
	<tr>
		<td class="paramlist_key" style="text-align:left">
			<div id="cwin-wait" style="display:none;"></div>
			<input class="btn btn-primary validateSubmit" type="submit" id="btnSubmit" value="<?php echo JText::_('COM_COMMUNITY_NEXT'); ?>" name="submit">
		</td>
		<td class="paramlist_value">
			
		</td>
	</tr>
</tbody>
</table>
<?php } ?>
<input type="hidden" name="id" value="0" />
<input type="hidden" name="gid" value="0" />
<input type="hidden" id="authenticate" name="authenticate" value="0" />
<input type="hidden" id="authkey" name="authkey" value="" />
</form>