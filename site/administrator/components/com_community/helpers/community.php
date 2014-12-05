<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

class CommunityHelper
{
	public static function addSubmenu($view)
	{
		$views = array(
			'community'        => 'community',
			'users'            => 'users',
			'multiprofile'     => 'users',
			'configuration'    => 'community',
			'profiles'         => 'users',
			'groups'           => 'groups',
			'groupcategories'  => 'groups',
			'events'           => 'events',
			'eventcategories'  => 'events',
			'videoscategories' => 'community',
			'reports'          => 'community',
			'userpoints'       => 'users',
			'about'            => 'community'
		);

		$subViews = array(
			'community' => array(
				'community'        => JText::_('COM_COMMUNITY_TOOLBAR_HOME'),
				'configuration'    => JText::_('COM_COMMUNITY_TOOLBAR_CONFIGURATION'),
				'users'            => JText::_('COM_COMMUNITY_TOOLBAR_USERS'),
				'groups'           => JText::_('COM_COMMUNITY_TOOLBAR_GROUPS'),
				'events'           => JText::_('COM_COMMUNITY_TOOLBAR_EVENTS'),
				'videoscategories' => JText::_('COM_COMMUNITY_TOOLBAR_VIDEO_CATEGORIES'),
				'reports'          => JText::_('COM_COMMUNITY_TOOLBAR_REPORTINGS'),
				'about'            => JText::_('COM_COMMUNITY_TOOLBAR_ABOUT'),
			),
			'users' => array(
				'community'    => JText::_('COM_COMMUNITY_TOOLBAR_HOME'),
				'users'        => JText::_('COM_COMMUNITY_TOOLBAR_USERS'),
				'multiprofile' => JText::_('COM_COMMUNITY_TOOLBAR_MULTIPROFILES'),
				'profiles'     => JText::_('COM_COMMUNITY_TOOLBAR_CUSTOMPROFILES'),
				'userpoints'   => JText::_('COM_COMMUNITY_TOOLBAR_USERPOINTS'),
			),
			'groups' => array(
				'community'       => JText::_('COM_COMMUNITY_TOOLBAR_HOME'),
				'groups'          => JText::_('COM_COMMUNITY_TOOLBAR_GROUPS'),
				'groupcategories' => JText::_('COM_COMMUNITY_TOOLBAR_GROUP_CATEGORIES'),
			),
			'events' => array(
				'community'       => JText::_('COM_COMMUNITY_TOOLBAR_HOME'),
				'events'          => JText::_('COM_COMMUNITY_TOOLBAR_EVENTS'),
				'eventcategories' => JText::_('COM_COMMUNITY_TOOLBAR_EVENT_CATEGORIES')
			),
		);

		$currentView = '';

		if (array_key_exists($view, $views))
		{
			$currentView = $views[$view];
		}

		if ( ! array_key_exists($currentView, $subViews))
		{
			$currentView = 'community';
		}

		foreach ($subViews[$currentView] as $key => $val)
		{
			$isActive = ($view == $key);

			JSubMenuHelper::addEntry($val, 'index.php?option=com_community&view='.$key , $isActive);
		}
	}
}