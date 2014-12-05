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

jimport( 'joomla.application.component.controller' );

/**
 * JomSocial Component Controller
 */
class CommunityControllerNetwork extends CommunityController
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Method to save the configuration
	 **/
	public function save()
	{
		// Test if this is really a post request
		$method	= JInput::getMethod();

		if( $method == 'GET' )
		{
			JError::raiseError( 500 , JText::_('COM_COMMUNITY_ACCESS_NOT_ALLOWED') );
			return;
		}

		$mainframe	= JFactory::getApplication();

		$model	=& $this->getModel( 'Network' );

		// Try to save network configurations
		if( $model->save() )
		{
			$message	= JText::_('COM_COMMUNITY_NETWORK_CONFIGURATION_UPDATED');
			$mainframe->redirect( 'index.php?option=com_community&view=network', $message, 'message' );
		}
		else
		{
			JError::raiseWarning( 100 , JText::_('COM_COMMUNITY_CONFIGURATION_NETWORK_SAVE_FAIL') );
		}
	}
}