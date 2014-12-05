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

// The target/actor is of no importance. If the current user is either of of them, they should read it as 'you'
$user1 = CFactory::getUser($act->actor);
$user2 = CFactory::getUser($act->target);

$my = CFactory::getUser();
$you = null;
$other = null;

if($my->id == $user1->id) {
	$you = $user1;
	$other = $user2;
}

if($my->id == $user2->id) {
	$you = $user2;
	$other = $user1;
}
?>
<div class="joms-stream-content">
	<div class="cStream-Headline">
		<i class="joms-icon-user"></i>
		<?php
		if(!is_null($you))
		{
			// @todo: use sprintf with language code
			echo JText::sprintf('COM_COMMUNITY_STREAM_MY_FRIENDS', $other->getDisplayName(), CUrlHelper::userLink($other->id));
		?>
		<?php
		} else {
			// @todo: use sprintf with language code
			echo JText::sprintf('COM_COMMUNITY_STREAM_OTHER_FRIENDS', $user1->getDisplayName(),$user2->getDisplayName(), CUrlHelper::userLink($user1->id), CUrlHelper::userLink($user2->id));
		}
		?>
	</div>
</div>