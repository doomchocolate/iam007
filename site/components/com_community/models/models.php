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

jimport('joomla.application.component.modellist');

/**
 * Put all shared data access here
 */
class JCCModel extends JModelLegacy {

	public function JCCModel(){
	parent::__construct();

	}

	public function &getNotes(){
		return array("test", "whatever");
	}

	public function &getSample()
	{
		$s = array("test", "whatever");
		return $s;
	}

	public function store()
	{
		// check if some core interface is implemented and execute them
		// use simple method check for now
		// @todo: use PHP5 reflection api.


		return parent::store();
	}

}

interface CGeolocationInterface
{
    public function resolveLocation($address);
}

interface CGeolocationSearchInterface
{
	public function searchWithin($address, $distance);
}

interface CLimitsInterface
{
	public function getTotalToday( $userId );
}

interface CNotificationsInterface
{
	public function getTotalNotifications( $userId );
}