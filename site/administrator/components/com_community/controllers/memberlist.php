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

JTable::addIncludePath( JPATH_ROOT . '/components/com_community/tables' );

class CommunityControllerMemberlist extends CommunityController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function delete()
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$data	= $jinput->post->get('cid' , '', 'NONE');	//JRequest::getVar( 'cid' , '' , 'post' );
		$error	= array();
		$list	= JTable::getInstance( 'Memberlist' , 'CTable' );

		if( !is_array( $data ) )
		{
			$data[]	= $data;
		}

		if( empty($data) )
		{
			JError::raiseError( '500' , JText::_('COM_COMMUNITY_INVALID_ID') );
		}

		foreach($data as $id)
		{
			$list->load( $id );

			if( !$list->delete() )
			{
				$error[]	= true;
			}

		}

		$mainframe	= JFactory::getApplication();

		if( in_array( $error , true ) )
		{
			$mainframe->redirect( 'index.php?option=com_community&view=memberlist' , JText::_('COM_COMMUNITY_MEMBERLIST_REMOVING_ERROR') , 'error' );
		}
		else
		{
			$mainframe->redirect( 'index.php?option=com_community&view=memberlist' , JText::_('COM_COMMUNITY_MEMBERLIST_DELETED'),'message' );
		}
	}
}