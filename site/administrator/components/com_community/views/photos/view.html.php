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
class CommunityViewPhotos extends JViewLegacy
{
	/**
	 * The default method that will display the output of this view which is called by
	 * Joomla
	 *
	 * @param	string template	Template file name
	 **/
	public function display( $tpl = null )
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$search				= $mainframe->getUserStateFromRequest( "com_community.videos.search", 'search', '', 'string' );

		// Set the titlebar text
		JToolBarHelper::title( JText::_('COM_COMMUNITY_PHOTOS'), 'photos' );

		// Add the necessary buttons
		JToolBarHelper::trash('delete', JText::_('COM_COMMUNITY_DELETE'));
		JToolBarHelper::publishList( 'publish' , JText::_('COM_COMMUNITY_PUBLISH') );
		JToolBarHelper::unpublishList( 'unpublish' , JText::_('COM_COMMUNITY_UNPUBLISH') );

		$photos		= $this->get( 'Photos' );
		$pagination	= $this->get( 'Pagination' );

		// /$catHTML	= $this->_getCategoriesHTML( $categories );

 		$this->assignRef( 'photos' 		, $photos );
 		$this->assignRef( 'pagination'	, $pagination );
 		$this->assignRef( 'search'		, $search );
 		//var_dump($photos);
		parent::display( $tpl );
	}

	public function getPublish( $row , $type , $ajaxTask )
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

			$span = '<i class="icon-'.$state.'"></i>';
		}

		if(!isset($row->pid))
		{
			$row->pid = $row->id;
		}
		$href = '<a class="'.$class.'" href="javascript:void(0);" onclick="azcommunity.togglePublish(\'' . $ajaxTask . '\',\'' . $row->pid . '\',\'' . $type . '\');">';

		$href .= $span;

		return $href;
	}

	public function formatBytes($id)
	{
		$photo = JTable::getInstance('Photo','CTable');
		$photo->load($id);

		if(JFile::exists(JPATH_ROOT .'/'.$photo->image)){
			$size =  round(filesize(JPATH_ROOT .'/'.$photo->image)/1048576,2) . ' MB';
		} else {
			$size =  round(filesize(JPATH_ROOT .'/'.$photo->original)/1048576,2) . ' MB';
		}
		return $size;
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

	public function getConnectType( $userId )
	{
		$model	= $this->getModel( 'Users' );
		$type	= $model->getUserConnectType( $userId );
		$image	= '';

		switch( $type )
		{
			case 'facebook':
				$image	= '<img src="' . rtrim( JURI::root() , '/' ) . '/administrator/components/com_community/assets/icons/facebook.gif" />';
				break;
			case 'joomla':
			default:
				$image	= '<img src="' . rtrim( JURI::root() , '/' ) . '/administrator/components/com_community/assets/icons/joomla-icon.png" />';
				break;
		}
		return $image;
	}

	public function getProfileName($obj)
	{
		$profileId = $obj->getProfileType();

		$profile = JTable::getInstance('MultiProfile', 'CTable');
        $profile->load($profileId);

        return $profile->getName();
	}

}
