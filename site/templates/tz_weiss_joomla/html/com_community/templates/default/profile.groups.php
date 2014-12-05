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
<div class="cModule cProfile-Groups app-box">
	<h3 class="app-box-header cResetH"><?php echo JText::_('COM_COMMUNITY_GROUPS'); ?></h3>
	<div class="app-box-content">
			<?php
			if (count($groups) > 0)
			{
			?>
			<ul class="cResetList cThumbsList clearfix">
			<?php
				for($i = 0; ($i < 12) && ($i < count($groups)); $i++)
				{
					$row	= $groups[$i];
			?>
			<li>
				<a href="<?php echo $row->getLink( true );?>">
					<img title="<?php echo $this->escape($row->name);?>" alt="<?php echo $this->escape($row->name);?>" src="<?php echo $row->getThumbAvatar(); ?>" class="cAvatar jomNameTips"/>
				</a>
			</li>
			<?php
				}
			?>
			</ul>
			<?php
			}
			else
			{
			?>
				<div class="cEmpty"><?php echo JText::_('COM_COMMUNITY_GROUPS_NO_JOINED_YET');?></div>
			<?php
			}
			?>
	</div>
	<div class="app-box-footer">
		<a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=mygroups&userid=' . $user->id ); ?>">
			<?php echo JText::_('COM_COMMUNITY_GROUPS_VIEW_ALL'); ?>
			<span>(<?php echo $total;?>)</span>
		</a>
	</div>
</div>

