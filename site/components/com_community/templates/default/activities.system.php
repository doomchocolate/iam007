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

$action = $act->params->get('action');

switch ($action) {
	case 'registered_users':
		$this->load('activities.system.registered');
		break;

	case 'total_photos':
		$this->load('activities.system.totalphotos');
		break;

	case 'top_videos':
		$this->load('activities.system.topvideos');
		break;
	case 'top_photos':
		$this->load('activities.system.topphotos');
		break;

	case 'top_users':
		$this->load('activities.system.topusers');
		break;

	case 'top_groups':
		$this->load('activities.system.topgroups');
		break;

	case 'message':
		$this->load('activities.system.message');
		break;
	default:
		# code...
		break;
}