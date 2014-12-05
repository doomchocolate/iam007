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
class CommunityViewGroups extends JViewLegacy
{
	/**
	 * The default method that will display the output of this view which is called by
	 * Joomla
	 *
	 * @param	string template	Template file name
	 **/
	public function display( $tpl = null )
	{
		$js	=   'assets/editors/ckeditor/ckeditor.js';
		CAssets::attach($js, 'js');
		if( $this->getLayout() == 'edit' )
		{
			$this->_displayEditLayout( $tpl );
			return;
		}

		$document	= JFactory::getDocument();

		// Set the titlebar text
		JToolBarHelper::title( JText::_('COM_COMMUNITY_GROUPS'), 'groups');

		// Add the necessary buttons
		JToolBarHelper::addNew( 'newGroup' , JText::_('COM_COMMUNITY_PROFILES_NEW_GROUP') );
		JToolBarHelper::deleteList( JText::_('COM_COMMUNITY_GROUP_DELETION_WARNING') , 'deleteGroup' , JText::_('COM_COMMUNITY_DELETE') );
		JToolBarHelper::divider();
		JToolBarHelper::publishList( 'publish' , JText::_('COM_COMMUNITY_PUBLISH') );
		JToolBarHelper::unpublishList( 'unpublish' , JText::_('COM_COMMUNITY_UNPUBLISH') );

		// Get required data's
		$groups		= $this->get( 'Groups' );
		$categories	= $this->get( 'Categories' );
		$pagination	= $this->get( 'Pagination' );

		// We need to assign the users object to the groups listing to get the users name.
		for( $i = 0; $i < count( $groups ); $i++ )
		{
			$row		=& $groups[$i];
			$row->user	= CFactory::getUser( $row->ownerid );
			$row->description = strip_tags($row->description,'<strong><a>');
			$row->description = CStringHelper::trim( $row->description, 200 );
			$row->category = $this->_getCatName($categories,$row->categoryid);

		}
		$mainframe	= JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest( "com_community.groups.filter_order",		'filter_order',		'a.name',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "com_community.groups.filter_order_Dir",	'filter_order_Dir',	'',			'word' );
		$search				= $mainframe->getUserStateFromRequest( "com_community.users.search", 'search', '', 'string' );
		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		$catHTML	= $this->_getCategoriesHTML( $categories );

		$this->assignRef( 'groups' 		, $groups );
		$this->assignRef( 'categories' 	, $catHTML);
		$this->assignRef( 'lists' 		, $lists );
		$this->assignRef( 'search'		, $search );
		$this->assignRef( 'pagination'	, $pagination );

		parent::display( $tpl );
	}

	public function _displayEditLayout( $tpl )
	{

		// Load frontend language file.
		$lang	= JFactory::getLanguage();
		$lang->load( 'com_community' , JPATH_ROOT );

		// Set the titlebar text
		JToolBarHelper::title( JText::_('COM_COMMUNITY_GROUPS'), 'groups');

 		// Add the necessary buttons
 		JToolBarHelper::back('Back' , 'index.php?option=com_community&view=groups');
 		JToolBarHelper::divider();
		JToolBarHelper::save();

		$group	= JTable::getInstance( 'Group' , 'CTable' );
		$group->load( JRequest::getInt( 'groupid') );

		$post	= JRequest::get( 'POST' );
		$group->bind( $post );

		$categories	= $this->get( 'Categories' );

		$editor = new CEditor('jomsocial');

		$this->assignRef( 'categories', $categories );
		$this->assignRef( 'group'	, $group );
		$this->assignRef( 'editor' , $editor);

		$document = JFactory::getDocument();
		JHTML::_('behavior.modal', 'a.modal');

		$this->url    =	'index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=jform_user_id_to';

		parent::display( $tpl );
	}

	public function _getCategoriesHTML( &$categories )
	{
		// Check if there are any categories selected
		$category	= JRequest::getInt( 'category' , 0 );

		$select	= '<select class="no-margin" name="category" onchange="submitform();">';

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
			$class = $row->$type == 1 ? 'disabled jgrid': '';

			$span = '<i class="icon-'.$state.'""></i>';
		}

		$href = '<a class="'.$class.'" href="javascript:void(0);" onclick="azcommunity.togglePublish(\'' . $ajaxTask . '\',\'' . $row->id . '\',\'' . $type . '\');">';

		$href .= $span;

		return $href;
	}

	public function setToolBar()
	{
	}

	public function getThumbAvatar($obj) {

        // For group avatars that are stored in a remote location, we should return the proper path.
        if ($obj->storage != 'file' && !empty($obj->thumb)) {
            $storage = CStorage::getStorage($obj->storage);
            return $storage->getURI($obj->thumb);
        }


        $thumb = CUrlHelper::avatarURI($obj->thumb, 'group_thumb.png');

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

		$statusArray = array(2=>JText::_('COM_COMMUNITY_ALL_STATE'),0=>JText::_('JUNPUBLISHED'),1=>JText::_('JPUBLISHED'));

		foreach($statusArray as $key=>$array)
		{
			$selected = ($status == $key) ? 'selected="true"' : '';
			$select .='<option value="'.$key.'"'.$selected.' >'.JText::_($array).'</option>';
		}

		$select	.= '</select>';

		return $select;
	}
}