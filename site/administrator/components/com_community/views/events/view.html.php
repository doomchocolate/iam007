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

jimport( 'joomla.application.component.view' );

/**
 * Configuration view for JomSocial
 */
class CommunityViewEvents extends JViewLegacy
{
	/**
	 * The default method that will display the output of this view which is called by
	 * Joomla
	 *
	 * @param	string template	Template file name
	 **/
	public function display( $tpl = null )
	{
		$document	= JFactory::getDocument();
        $config		= CFactory::getConfig();

		// Get required data's
		$events		= $this->get( 'Events' );
		$categories	= $this->get( 'Categories' );
		$pagination	= $this->get( 'Pagination' );

		$mainframe	= JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest( "com_community.events.filter_order",		'filter_order',		'a.title',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "com_community.events.filter_order_Dir",	'filter_order_Dir',	'',			'word' );
		$search				= $mainframe->getUserStateFromRequest( "com_community.events.search", 'search', '', 'string' );

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		// We need to assign the users object to the groups listing to get the users name.
		for( $i = 0; $i < count( $events ); $i++ )
		{
			$row                =&  $events[$i];
			$row->user          =   JFactory::getUser( $row->creator );

            // Truncate the description
            $row->description = str_ireplace('<p>','',$row->description);
			$row->description = str_ireplace('</p>','',$row->description);
            $row->description   =   CStringHelper::trim( $row->description, 200 );

            $date = new JDate($row->startdate);
            $row->startdate = $date->format('Y-m-d');
            $row->category = $this->_getCatName($categories,$row->catid);
		}

		$catHTML	= $this->_getCategoriesHTML( $categories );

		$this->assignRef( 'events' 		, $events );
		$this->assignRef( 'categories' 	, $catHTML);
		$this->assignRef( 'search'		, $search );
		$this->assignRef( 'lists'		, $lists );
		$this->assignRef( 'pagination'	, $pagination );

		parent::display( $tpl );
	}


	public function _getCategoriesHTML( $categories )
	{
		// Check if there are any categories selected
		$category	= JRequest::getInt( 'category' , 0 );

		$select	= '<select name="category" onchange="submitform();" class="no-margin">';

		$select	.= ( $category == 0 ) ? '<option value="0" selected="true">' : '<option value="0">';
		$select .= JText::_('COM_COMMUNITY_ALL_CATEGORY') . '</option>';

		for( $i = 0; $i < count( $categories ); $i++ )
		{
			$selected	= ( $category == $categories[$i]->id ) ? ' selected="true"' : '';
			$select	.= '<option value="' . $categories[$i]->id . '"' . $selected . '>' . $categories[$i]->name . '</option>';
		}
		$select	.= '</select>';

		return $select;
	}


	/**
	 * Method to get the publish status HTML
	 *
	 * @param	object	Field object
	 * @param	string	Type of the field
	 * @param	string	The ajax task that it should call
	 * @return	string	HTML source
	 **/
	public function getPublish( &$row , $type , $ajaxTask )
	{

		$version = new Jversion();
		$currentV = $version->getHelpVersion();

		$class = 'jgrid';

		$alt	= $row->$type ? JText::_('COM_COMMUNITY_PUBLISHED') : JText::_('COM_COMMUNITY_UNPUBLISH');
		$state = $row->$type == 1 ? 'publish' : 'unpublish';
		$span = '<span class="state '.$state.'"><span class="text">'.$alt.'</span></span></a>';

		if($currentV >= '0.30')
		{
			$class = $row->$type == 1 ? 'jgrid': '';

			$span = '<i class="icon-'.$state.'""></i>';
		}

		$href = '<a class="'.$class.'" href="javascript:void(0);" onclick="azcommunity.togglePublish(\'' . $ajaxTask . '\',\'' . $row->id . '\',\'' . $type . '\');">';

		$href .= $span;

		return $href;
	}

	public function setToolBar()
	{
		// Set the titlebar text
		JToolBarHelper::title( JText::_('COM_COMMUNITY_EVENTS'), 'events');

		// Add the necessary buttons
		JToolBarHelper::deleteList( JText::_('COM_COMMUNITY_EVENTS_DELETE_WARNING') , 'deleteEvent' , JText::_('COM_COMMUNITY_DELETE') );
		JToolBarHelper::divider();
		JToolBarHelper::publishList( 'publish' , JText::_('COM_COMMUNITY_PUBLISH') );
		JToolBarHelper::unpublishList( 'unpublish' , JText::_('COM_COMMUNITY_UNPUBLISH') );
	}

	public function getThumbAvatar($thumb = null) {

        $thumb = CUrlHelper::avatarURI($thumb, 'event_thumb.png');

        return $thumb;
    }

    private function _getCatName($categories,$id)
    {
    	foreach($categories as $cat)
    	{
    		if($cat->id == $id)
    		{
    			return $cat->name;
    		}

    	}

    	return 'No category';
    }

    public function _getStatusHTML()
	{
		// Check if there are any categories selected
		$status	= JRequest::getInt( 'status' , 2 );

		$select	= '<select class="no-margin" name="status" onchange="submitform();">';

		$statusArray = array(2=>JText::_('COM_COMMUNITY_ALL_STATE'),0=>JText::_('COM_COMMUNITY_UNPUBLISH'),1=>JText::_('COM_COMMUNITY_PUBLISH'));

		foreach($statusArray as $key=>$array)
		{
			$selected = ($status == $key) ? 'selected="true"' : '';
			$select .='<option value="'.$key.'"'.$selected.' >'.JText::_($array).'</option>';
		}

		$select	.= '</select>';

		return $select;
	}
}