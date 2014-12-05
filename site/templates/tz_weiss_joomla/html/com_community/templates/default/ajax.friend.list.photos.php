 <?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
?>

<?php
//s:foreach
$i = 0;
foreach( $friends as $id )
{
$user			= CFactory::getUser( $id );
$invited		= in_array( $user->id , $selected );
?>

<li id="invitation-friend-<?php echo $user->id;?>">
	<div class="cGrid-Box clearfix">
		<img class="cGrid-Avatar cFloat-L" src="<?php echo addslashes($user->getThumbAvatar());?>" width="40" height="40" />
		<div class="cGrid-Info">
			<b><?php echo $user->getDisplayName();?></b>
			<?php
			if(!$invited){
			?>
				<div class="cGrid-Check">
					<label for="friend-<?php echo $user->id;?>" class="label-checkbox">
						<input type="checkbox" onclick="joms.invitation.selectMemberUnique('#invitation-friend-<?php echo $user->id;?>');" value="<?php echo $user->id;?>" name="friends[]" id="friend-<?php echo $user->id;?>">
						<?php echo JText::_('COM_COMMUNITY_INVITE_SELECTED');?>
					</label>
				</div>
			<?php 
			} else {
			?>
				<div class="cGrid-Box"><?php echo JText::_('COM_COMMUNITY_INVITE_INVITED');?></div>
			<?php
			}
			?>
		</div>
	</div>
</li>
<?php
}
?>