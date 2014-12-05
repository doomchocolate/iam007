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
class CommunityControllerConfiguration extends CommunityController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function ajaxResetPrivacy( $photoPrivacy = 0, $profilePrivacy = 0, $friendsPrivacy = 0 , $privacyvideos = 0 , $privacy_groups_list = 0 )
	{
		$response	= new JAXResponse();

		//CFactory::load( 'helpers' , 'owner' );

		if( !COwnerHelper::isCommunityAdmin() )
		{
			$response->addScriptCall( JText::_('COM_COMMUNITY_NOT_ALLOWED'));
			return $response->sendResponse();
		}

		$model	= $this->getModel( 'Configuration' );

		$model->updatePrivacy( $photoPrivacy , $profilePrivacy , $friendsPrivacy , $privacyvideos , $privacy_groups_list );

		$response->addAssign( 'privacy-update-result', 'innerHTML' , JText::_('COM_COMMUNITY_FRONTPAGE_ALL_PRIVACY_RESET') );

		return $response->sendResponse();
	}

	public function ajaxResetNotification( $params )
	{
		$response	= new JAXResponse();

		if( !COwnerHelper::isCommunityAdmin() )
		{
			$response->addScriptCall( JText::_('COM_COMMUNITY_NOT_ALLOWED'));
			return $response->sendResponse();
		}

		$model	= $this->getModel( 'Configuration' );

		$model->updateNotification( $params );

		$response->addAssign( 'notification-update-result', 'innerHTML' , JText::_('COM_COMMUNITY_FRONTPAGE_ALL_NOTIFICATION_RESET') );
		$response->addScriptCall("joms.jQuery('#notification-update-result').parent().find('input').val('". JText::_('COM_COMMUNITY_CONFIGURATION_PRIVACY_RESET_EXISTING_NOTIFICATION_BUTTON')."');");

		return $response->sendResponse();
	}

	/**
	 * Method to display the specific view
	 *
	 **/
	public function display( $cachable = false, $urlparams = array() )
	{
		// Set the default layout and view name
		$layout  = JRequest::getCmd( 'layout' , 'default' );
		$viewName = JRequest::getCmd( 'view' , 'community' );

		// Configuration section
		$cfgSection = JRequest::getCmd( 'cfgSection' , 'default' );

		// Get the document object
		$document	= JFactory::getDocument();

		// Get the view type
		$viewType	= $document->getType();

		// Get the view
		$view		= $this->getView( $viewName , $viewType );
		$model		= $this->getModel( $viewName );

		if( $model )
		{
			$view->setModel( $model , $viewName );

			$network	= $this->getModel( 'network' );
			$view->setModel( $network  , false );
		}

		// Set the layout
		$view->setLayout( $layout );

		// Set the configuration section
		$view->assign( 'cfgSection' , $cfgSection );

		// Display the view
		$view->display();

		// Display Toolbar. View must have setToolBar method
		if( method_exists( $view , 'setToolBar') )
		{
			$view->setToolBar();
		}
	}

	/**
	 * Method to save the configuration
	 **/
	public function saveconfig()
	{
		// Test if this is really a post request
		$method	= JInput::getMethod();

		if( $method == 'GET' )
		{
			JError::raiseError( 500 , JText::_('COM_COMMUNITY_ACCESS_NOT_ALLOWED') );
			return;
		}

		// Set the default section
		$cfgSection  = JRequest::getCmd( 'cfgSection' , 'default' );

		$mainframe	= JFactory::getApplication();

		$model	=& $this->getModel( 'Configuration' );

		// Try to save configurations
		if( $model->save() )
		{
			$message	= JText::_('COM_COMMUNITY_CONFIGURATION_UPDATED');

			$model	=& $this->getModel( 'Network' );

			// Try to save network configurations
			if( $model->save() )
			{
				$mainframe->redirect( 'index.php?option=com_community&view=configuration&cfgSection=' . $cfgSection, $message, 'message' );
			}
			else
			{
				JError::raiseWarning( 100 , JText::_('COM_COMMUNITY_CONFIGURATION_NETWORK_SAVE_FAIL') );
			}
		}
		else
		{
			JError::raiseWarning( 100 , JText::_('COM_COMMUNITY_CONFIGURATION_SAVE_FAIL') );
		}
	}

        /**
         * method cancel action
         *
         */
        public function cancel(){
                $mainframe	= JFactory::getApplication();
                $mainframe->redirect( 'index.php?option=com_community' );
        }
}