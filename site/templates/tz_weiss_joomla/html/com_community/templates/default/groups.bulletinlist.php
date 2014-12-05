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
<ul class="cStreamList forBulletin pushedUp cResetList">
<?php
if( $bulletins )
{
	for($i = 0; $i < count( $bulletins ); $i++ )
	{
		$row	=& $bulletins[$i];
?>
	<li>
		<a href="<?php echo CUrlHelper::userLink($row->creator->id); ?>" class="cStream-Avatar cFloat-L">
			<img src="<?php echo $row->creator->getThumbAvatar(); ?>"  alt="<?php echo $row->creator->getDisplayName(); ?>" class="cAvatar" />
		</a>
		<div class="cStream-Content">
			<a class="stream-heading" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewbulletin&groupid=' . $groupId . '&bulletinid=' . $row->id);?>">
				<?php echo $row->title; ?>
			</a>
			<?php
				// Only display news item for first item
				if( $i == 0 )
				{
			?>
			<div class="updateText">
				<?php echo $row->message;?>
			</div>
			<?php
				}
			?>
			<div class="cStream-Actions clearfix small">
				<span><?php echo JHTML::_('date' , $row->date, JText::_('DATE_FORMAT_LC')); ?></span>
				<span><?php echo JText::sprintf( 'COM_COMMUNITY_BULLETIN_CREATED_BY' , $row->creator->getDisplayName() , CRoute::_('index.php?option=com_community&view=profile&userid=' . $row->creator->id ) ); ?></span>
			</div>
		</div>
	</li>
<?php
	} //end for
} // end if
else
{
?>
	<li>
		<div class="cAlert updateEmpty"><?php echo JText::_('COM_COMMUNITY_GROUPS_BULLETIN_NOITEM'); ?></div>
	</li>
<?php
}	
?>
</ul>