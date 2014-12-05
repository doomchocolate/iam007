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
<ul class="cThumbsList cResetList clearfix">
	<?php
	foreach($members as $member) 
	{
	?>
	<li>
		<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$member->id );?>">
			<img class="cAvatar jomNameTips" src="<?php echo $member->getThumbAvatar(); ?>" title="<?php echo CTooltip::cAvatarTooltip($member); ?>" alt="<?php echo $this->escape( $member->getDisplayName() ) ?>"/>
		</a>
	</li>
	<?php
	}
	?>
</ul>