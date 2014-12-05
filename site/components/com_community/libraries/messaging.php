<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// no direct access
defined('_JEXEC') or die('Restricted access');

class CMessaging
{
	/**
	 * Load messaging javascript header
	 */
	static public function load()
	{
		if( ! defined('CMESSAGING_LOADED'))
		{
			$config	= CFactory::getConfig();
			include_once JPATH_ROOT.'/components/com_community/libraries/core.php';

			$js = 'assets/window-1.0.min.js';
			CAssets::attach($js, 'js');

			$js = 'assets/script-1.2.min.js';
			CAssets::attach($js, 'js');

			// $css = 'assets/window.css';
			// CAssets::attach($css, 'css');


			CTemplate::addStyleSheet('style');
		}
	}

	/**
	 * Get link to popup window
	 */
	static public function getPopup($id)
	{
		CMessaging::load();
		return "joms.messaging.loadComposeWindow('{$id}')";
	}

	static public function send($data)
	{
		//notifyEmailMessage
	}
}