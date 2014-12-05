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

$appLib = CAppPlugins::getInstance();
$user = CFactory::getUser($this->act->actor);
$users = json_decode($this->act->actors);
$users = $users->userid;

// Get the plugin name
$param = new JRegistry($this->act->params);
$appName = $param->get('app');
$applicationName = '';

$plugin = $appLib->getPlugin($appName);
if(!is_null($plugin))
{
	$applicationName = $plugin->name;
}

$actorsHTML = array();
?>
<div class="">
	<i class="cStream-Icon com-icon-groups"></i>
	<?php foreach($users as $actor)
	{
		$user = CFactory::getUser($actor->id);
		$actorsHTML[] = '<a class="cStream-Author" href="'. CUrlHelper::userLink($user->id).'">'. $user->getDisplayName().'</a>';
	}

	echo implode(', ', $actorsHTML); ?>
	-
	<?php echo JText::sprintf('COM_COMMUNITY_ACTIVITIES_APPLICATIONS_ADDED' , $applicationName); ?>
</div>