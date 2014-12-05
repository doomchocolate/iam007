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

class CommunityViewMultiProfile extends JViewLegacy
{
	/**
	 * The default method that will display the output of this view which is called by
	 * Joomla
	 *
	 * @param	string template	Template file name
	 **/
	public function display( $tpl = null )
	{

		if( $this->getLayout() == 'edit' )
		{
			$this->_displayEditLayout( $tpl );
			return;
		}


		// Set the titlebar text
		JToolBarHelper::title( JText::_('COM_COMMUNITY_CONFIGURATION_MULTIPROFILES'), 'multiprofile' );

		// Add the necessary buttons
		JToolBarHelper::publishList('publish', JText::_('COM_COMMUNITY_PUBLISH'));
		JToolBarHelper::unpublishList('unpublish', JText::_('COM_COMMUNITY_UNPUBLISH'));
		JToolBarHelper::divider();
		JToolBarHelper::trash('delete', JText::_('COM_COMMUNITY_DELETE'));
		JToolBarHelper::addNew('add', JText::_('COM_COMMUNITY_NEW'));

		$profiles	= $this->get( 'MultiProfiles' );
		$pagination	= $this->get( 'Pagination' );

		$mainframe			= JFactory::getApplication();

		$ordering			= $mainframe->getUserStateFromRequest( "com_community.multiprofile.filter_order",		'filter_order',		'ordering',	'cmd' );
		$orderingDirection	= $mainframe->getUserStateFromRequest( "com_community.multiprofile.filter_order_Dir",	'filter_order_Dir',	'ASC',			'word' );

 		$this->assignRef( 'profiles'	, $profiles );
		$this->assignRef( 'ordering'	, $ordering );
		$this->assignRef( 'orderingDirection'	, $orderingDirection );

		$this->assignRef( 'pagination'	, $pagination );
		parent::display( $tpl );
	}

	public function _displayEditLayout( $tpl )
	{
		JToolBarHelper::title( JText::_('COM_COMMUNITY_CONFIGURATION_MULTIPROFILES') , 'multiprofile' );

 		// Add the necessary buttons
 		JToolBarHelper::back('Back' , 'index.php?option=com_community&view=multiprofile');
 		JToolBarHelper::divider();
		JToolBarHelper::apply();
		JToolBarHelper::save();

		$mainframe    = JFactory::getApplication();
		$jinput       = $mainframe->input;
        $postedFields = $jinput->get('fields' , '', 'NONE');

		$id           = $jinput->request->get('id' , '', 'INT' ) ; //JRequest::getVar( 'id' , '' , 'REQUEST' );
		$multiprofile = JTable::getInstance( 'MultiProfile' , 'CTable' );
		$multiprofile->load( $id );

		$post = JRequest::get( 'POST' );
		$multiprofile->bind( $post );

		$profile = $this->getModel( 'Profiles' );
		$fields  = $profile->getFields();

		$config  = CFactory::getConfig();

		$this->assignRef( 'multiprofile', $multiprofile );
		$this->assignRef( 'fields'		, $fields );
		$this->assignRef( 'config'		, $config );
        $this->assignRef( 'postedFields', $postedFields);

 		parent::display( $tpl );
	}

	public function getWatermarkLocations()
	{
		$locations	= array(
			JHTML::_('select.option', 'top', 'Top'),
			JHTML::_('select.option', 'right', 'Right'),
			JHTML::_('select.option', 'bottom', 'Bottom'),
			JHTML::_('select.option', 'left', 'Left'),
		);
		return $locations;
	}

	/**
	 * Return the total number of users for specific profile
	 **/
	public function getTotalUsers( $profileId )
	{
		$db		= JFactory::getDBO();
		$query	= 'SELECT COUNT(1) FROM ' . $db->quoteName('#__community_users') . ' WHERE ' . $db->quoteName('profile_id') . '=' . $db->Quote( $profileId );
		$db->setQuery( $query );
		return $db->loadResult();
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
			$class = $row->$type == 1 ? 'disabled jgrid': '';

			$span = '<i class="icon-'.$state.'""></i>';
		}

		$href = '<a class="'.$class.'" href="javascript:void(0);" onclick="azcommunity.togglePublish(\'' . $ajaxTask . '\',\'' . $row->id . '\',\'' . $type . '\');">';

		$href .= $span;

		return $href;
	}

	/**
	 * Method to get the publish status HTML
	 *
	 * @param	object	Field object
	 * @param	string	Type of the field
	 * @param	string	The ajax task that it should call
	 * @return	string	HTML source
	 **/
	public function getItemsPublish( $isPublished , $fieldId )
	{
		$imgY    = 'tick.png';
		$imgX	= 'publish_x.png';
		$image	= '';

		if( $isPublished )
		{
			$image	= $imgY;
		}
		else
		{
			$image	= $imgX;
		}

		$href = '<a href="javascript:void(0);" onclick="azcommunity.toggleMultiProfileChild(' . $fieldId . ');"><img src="images/' . $image . '" border="0" /></a>';
		return $href;
	}


	/**
	 * Private method to set the toolbar for this view
	 *
	 * @access private
	 *
	 * @return null
	 **/
	public function setToolBar()
	{
	}
}