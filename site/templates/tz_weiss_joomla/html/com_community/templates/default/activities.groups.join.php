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

$user = CFactory::getUser($this->act->actor);
$users = explode(',', $this->actors);

// We want the last guy joining to be the first mentioned
$users = array_reverse($users);

$userCount = count($users);

$slice = 2;

if($userCount > 2)
{
	$slice = 1;
}

$users = array_slice($users,0,$slice);

$actorsHTML = array();
?>
<div class="joms-stream-content">
	<i class="joms-icon-users"></i>
	<?php
	foreach($users as $actor) {
		$user = CFactory::getUser($actor);
		$actorsHTML[] = '<a class="cStream-Author" href="'. CUrlHelper::userLink($user->id).'">'. $user->getDisplayName().'</a>';
	}

	$others = '';

	if($userCount > 2)
	{
		$others = JText::sprintf('COM_COMMUNITY_STREAM_OTHERS_JOIN_GROUP' , $userCount-1, 'onClick="joms.stream.showOthers('.$act->id.');return false;"'    );
	}

	echo implode( ' '. JText::_('COM_COMMUNITY_AND') . ' ' , $actorsHTML).$others;
	echo JText::sprintf('COM_COMMUNITY_GROUPS_GROUP_JOIN' , $this->group->getLink(), $this->group->name);
	?>
</div>