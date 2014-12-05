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

require_once(JPATH_ROOT .'/components/com_community/libraries/tooltip.php');

class CommunityMemberlistController extends CommunityBaseController
{
	public function display($cacheable=false, $urlparams=false)
	{
		$document	= JFactory::getDocument();
		$viewType	= $document->getType();
		$mainframe	= JFactory::getApplication();
		$view = $this->getView('memberlist' , '' , $viewType);
		echo $view->get('display');
	}

	public function save()
	{
		if( !COwnerHelper::isCommunityAdmin() )
		{
			echo JText::_('COM_COMMUNITY_RESTRICTED_ACCESS');
			return;
		}
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$post				= JRequest::get('post');

		$table				= JTable::getInstance( 'Memberlist' , 'CTable' );
		$table->bind( $post );
		$date				= JFactory::getDate();
		$table->created		= $date->toSql();
		$table->store();

		if( empty( $table->title ) )
		{
			$mainframe->redirect( CRoute::_('index.php?option=com_community&view=memberlist', false ) , JText::_('COM_COMMUNITY_MEMBERLIST_TITLE_EMPTY') , 'error');
		}

		if( empty( $table->description ) )
		{
			$mainframe->redirect( CRoute::_('index.php?option=com_community&view=memberlist', false ) , JText::_('COM_COMMUNITY_MEMBERLIST_DESCRIPTION_EMPTY') , 'error');
		}

		$total	= $jinput->post->get( 'totalfilters' , '' , 'NONE');

		for( $i = 0; $i < $total; $i++ )
		{
			$filter	= $jinput->post->get( 'filter' . $i , '' , 'NONE');

			if( !empty( $filter ) )
			{
				$filters	= explode( ',' , $filter , 4 );

				$field		= explode( '=' , $filters[0] , 2 );
				$condition	= explode( '=' , $filters[1] , 2 );
				$type		= explode( '=' , $filters[2] , 2 );
				$value		= explode( '=' , $filters[3] , 2 );

				$criteria	= JTable::getInstance( 'MemberlistCriteria' , 'CTable' );
				$criteria->listid		= $table->id;
				$criteria->field		= $field[1];
				$criteria->value		= $value[1];
				$criteria->condition	= $condition[1];
				$criteria->type			= $type[1];

				$criteria->store();
			}
		}

		// Create the menu.
		//CFactory::load( 'helpers' , 'menu' );
		$menu				= JTable::getInstance( 'Menu' , 'JTable' );

		$menu->menutype		= JRequest::getWord( 'menutype' , '', 'POST' );
		//$menu->name			= $table->title;
		$menu->alias		= JFilterOutput::stringURLSafe( $table->title );
		$menu->link			= 'index.php?option=com_community&view=memberlist&listid=' . $table->id;
		$menu->published	= 1;
		$menu->type			= 'component';
		//$menu->ordering		= $menu->getNextOrder( 'menutype="' . $menu->menutype . '"');

		//$menu->componentid	= CMenuHelper::getComponentId();
		$menu->access		= JRequest::getInt('access' , '', 'POST');
		//rule: set default value for access level: public
		if($menu->access == ''){

				$menu->access = 1;
		}
		//joomla 1.6 has different field in jos_menu
		$menu->component_id	= CMenuHelper::getComponentId();
		$menu->path = $table->title;
		$menu->title = $table->title;
		$menu->level = 1;

		$menu->store();

		$id = CMenuHelper::getMenuIdByTitle($table->title);
		CMenuHelper::alterMenuTable($id);

		$mainframe->redirect( CRoute::_('index.php?option=com_community&view=memberlist&listid=' . $table->id , false ) , JText::_('COM_COMMUNITY_MEMBERLIST_CREATED') );
	}

	public function ajaxShowSaveForm()
	{
		//CFactory::load( 'helpers' , 'owner' );

		if( !COwnerHelper::isCommunityAdmin() )
		{
			echo JText::_('COM_COMMUNITY_RESTRICTED_ACCESS');
			return;
		}

		$response	= new JAXResponse();
		$args		= func_get_args();

		if( !isset( $args[0] ) )
		{
			$response->addScriptCall( 'alert' , 'COM_COMMUNITY_INVALID_ID' );
			return $resopnse->sendResponse();
		}

		$condition	= $args[0];
		array_shift( $args );

		$avatarOnly	= $args[0];
		array_shift( $args );

		$filters	= $args;

		$db = JFactory::getDBO();
		$query = 'SELECT a.*, SUM(b.home) AS home' .
				' FROM ' . $db->quoteName('#__menu_types') . ' AS a' .
				' LEFT JOIN ' . $db->quoteName('#__menu') . ' AS b ON b.menutype = a.menutype' .
				' GROUP BY a.id';
		$db->setQuery( $query );
		$menuTypes =  $db->loadObjectList();

		$menuAccess	= new stdClass();
		$menuAccess->access	= 0;

		$tmpl = new CTemplate();
		$tmpl->set( 'condition'	, $condition );
		$tmpl->set( 'menuTypes' , $menuTypes );
		$tmpl->set( 'menuAccess' , $menuAccess );
		$tmpl->set( 'avatarOnly' , $avatarOnly );
		$tmpl->set( 'filters' , $filters );

		$html     = $tmpl->fetch( 'ajax.memberlistform' );
		$actions  = '<button  class="btn" onclick="cWindowHide();">' . JText::_('COM_COMMUNITY_CANCEL_BUTTON') . '</button>';
		$actions .= '<button  class="btn btn-primary pull-right" onclick="joms.memberlist.submit();">' . JText::_('COM_COMMUNITY_SAVE_BUTTON') . '</button>';

		$response->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_SEARCH_FILTER') );
		$response->addScriptCall('cWindowAddContent', $html, $actions);

		return $response->sendResponse();
	}
}

