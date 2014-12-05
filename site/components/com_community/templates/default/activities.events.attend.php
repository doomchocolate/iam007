<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

$user = CFactory::getUser($this->act->actor);
$users = explode(',', $this->actors);
$userCount 	= count($users);
$actorsHTML = array();

$slice = 2;

if($userCount > 2)
{
	$slice = 1;
}

$users = array_slice($users,0,$slice);
?>
<div class="joms-stream-content">
	<i class="joms-icon-users"></i>
	<?php foreach($users as $actor)
	{
		if (!$actor) {
			$actor = $this->act->actor;
		}
		$user = CFactory::getUser($actor);
		$actorsHTML[] = '<a class="cStream-Author" href="'. CUrlHelper::userLink($user->id).'">'. $user->getDisplayName().'</a>';
	}

	$others = '';

	if($userCount > 2)
	{
		$others = JText::sprintf('COM_COMMUNITY_STREAM_OTHERS_JOIN_EVENT' , $userCount-1, 'onClick="joms.stream.showOthers('.$act->id.');return false;"' );
	}

	echo implode(', ', $actorsHTML).$others;

	$jtext =($userCount>1) ? 'COM_COMMUNITY_ACTIVITIES_EVENT_ATTEND_PLURAL' : 'COM_COMMUNITY_ACTIVITIES_EVENT_ATTEND';
	?>

	<?php echo JText::sprintf($jtext, $this->event->getLink(), $this->event->title);
	?>
</div>
