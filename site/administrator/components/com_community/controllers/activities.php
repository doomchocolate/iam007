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

require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );

/**
 * JomSocial Component Controller
 */
class CommunityControllerActivities extends CommunityController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function display($cachable = false, $urlparams = array())
	{
		$viewName	= JRequest::getCmd( 'view' , 'community' );

		// Set the default layout and view name
		$layout		= JRequest::getCmd( 'layout' , 'default' );

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

			$users	= $this->getModel( 'users' );
			$view->setModel( $users  , false );

			$groups	= $this->getModel( 'groups','CommunityAdminModel' );
			$view->setModel( $groups  , false );
		}

		// Set the layout
		$view->setLayout( $layout );

		// Display the view
		$view->display();

		// Display Toolbar. View must have setToolBar method
		if( method_exists( $view , 'setToolBar') )
		{
			$view->setToolBar();
		}
	}

	public function delete()
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$model		=& $this->getModel( 'activities' );
		$id			= $jinput->post->get( 'cid' , '', 'array' );
		$errors		= false;
		$message	= JText::_('COM_COMMUNITY_ACTIVITIES_DELETED');
		if( empty($id) )
		{
			JError::raiseError( '500' , JText::_('COM_COMMUNITY_INVALID_ID') );
		}

		for( $i = 0; $i < count($id); $i++ )
		{
			if( !$model->delete( $id[ $i ] ) )
			{
				$errors	= true;
			}
		}

		if( $errors )
		{
			$message	= JText::_('COM_COMMUNITY_ACTIVITIES_DELETING_ERROR');
		}
		$mainframe->redirect( 'index.php?option=com_community&view=activities' , $message ,'message');
	}

	public function purge()
	{
		$mainframe	= JFactory::getApplication();
		$model		=& $this->getModel( 'activities' );
		$message	= JText::_('COM_COMMUNITY_ACTIVITIES_PURGED');

		if( !$model->purge() )
		{
			$message	= JText::_('COM_COMMUNITY_ACTIVITIES_DELETING_ERROR');
		}
		$mainframe->redirect( 'index.php?option=com_community&view=activities' , $message ,'message');
	}

	public function archiveall()
	{
		$mainframe	= JFactory::getApplication();
		$model = $this->getModel('activities');

		if($model->archiveAll())
		{
			$mainframe->redirect('index.php?option=com_community&view=activities',JText::_('All activites has been archived'),'message');
		}

	}

	public function archiveSelected()
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$ids	= $jinput->post->get('cid', array(), 'array');
		$count	= count($ids);
		$model = $this->getModel('activities');

		foreach( $ids as $id )
		{
			$model->archiveSelected($id);
		}
		$message	= JText::sprintf( '%1$s Activites successfully archived.' , $count );
		$mainframe->redirect( 'index.php?option=com_community&view=activities' , $message ,'message');
	}
}