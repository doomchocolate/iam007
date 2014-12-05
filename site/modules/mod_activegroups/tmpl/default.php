<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<?php
$showAvatar = $params->get('show_avatar', '1');
$showTotal	= $params->get('show_total', '1');
?>
<ul id="cModule-ActiveGroups" class="cMods unstyled cMods-ActiveGroups<?php echo $params->get('moduleclass_sfx'); ?>">
	<?php

	if ( !empty($groups) ) {

		foreach ( $groups as $group ) {
	?>
	<li class="cMod-Row">
		<?php if($showAvatar) : ?>
			<a class="cThumb-Avatar l-float" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&groupid='.$group->id.'&task=viewgroup'); ?>">
				<img src="<?php echo $group->avatar; ?>" alt="<?php echo CStringHelper::escape( $group->name ); ?>" width="45" >
			</a>
		<?php endif;?>
		<div class="cThumb-Detail">
			<a class="cThumb-Title" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&groupid='.$group->id.'&task=viewgroup'); ?>">
				<?php echo $group->name; ?>
			</a><br />
			<?php if ( $showTotal == 1 ) : ?>
			<div class="cThumb-Brief small">
				<a href="<?php echo CRoute::_( "index.php?option=com_community&view=groups&task=viewmembers&groupid=" . $group->id ); ?>">
					<?php echo JText::sprintf( (!CStringHelper::isSingular($group->totalMembers)) ? 'MOD_ACTIVEGROUPS_MEMBER_MANY':'MOD_ACTIVEGROUPS_MEMBER', $group->totalMembers); ?>
				</a>
			</div>
			<?php endif; ?>
		</div>
	</li>
<?php
		}
	}
else
{
		echo JText::_("MOD_ACTIVEGROUPS_NO_ACTIVE_GROUPS");
}
?>
</ul>