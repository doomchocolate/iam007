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
class CommunityViewVideos extends JViewLegacy
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
		//$search				= $mainframe->getUserStateFromRequest( "com_community.videos.search", 'search', '', 'string' );
		$search	= JRequest::getVar('search','');
		// Set the titlebar text
		JToolBarHelper::title( JText::_('COM_COMMUNITY_VIDEOS'), 'videos' );

		// Add the necessary buttons
		JToolBarHelper::trash('delete', JText::_('COM_COMMUNITY_DELETE'));
		JToolBarHelper::publishList( 'publish' , JText::_('COM_COMMUNITY_PUBLISH') );
		JToolBarHelper::unpublishList( 'unpublish' , JText::_('COM_COMMUNITY_UNPUBLISH') );

		$videos		= $this->get( 'Videos' );
		$pagination	= $this->get( 'Pagination' );
		$categories = $this->get( 'Categories' );

		foreach($videos as $key=>$vid)
		{
			foreach($categories as $cat)
			{
				$videos[$key]->categoryName = '';

				if($cat->id == $vid->category_id)
				{
					$videos[$key]->categoryName = $cat->name;
					break;
				}
			}
		}

		$catHTML	= $this->_getCategoriesHTML( $categories );

 		$this->assignRef( 'videos' 		, $videos );
 		$this->assignRef( 'pagination'	, $pagination );
 		$this->assignRef( 'search'		, $search );
 		$this->assignRef( 'categories', $catHTML );
		parent::display( $tpl );
	}

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

			$span = '<i class="icon-'.$state.'"></i>';
		}

		$href = '<a class="'.$class.'" href="javascript:void(0);" onclick="azcommunity.togglePublish(\'' . $ajaxTask . '\',\'' . $row->id . '\',\'' . $type . '\');">';

		$href .= $span;

		return $href;
	}

	public function getThumbnail($obj)
	{
		$config = CFactory::getConfig();
		$file   = $obj->thumb;

		// Site origin
		if (JString::substr($file, 0, 4)=='http')
		{
			$uri = $file;
			return $uri;
		}

		// Remote storage
		if($obj->storage != 'file')
		{

			$storage = CStorage::getStorage($obj->storage);
			$uri = $storage->getURI($file);
			return $uri;
		}

		// Default thumbnail
		if (empty($file) || !JFile::exists(JPATH_ROOT.'/'.$file))
		{

			$template = new CTemplateHelper();
			$asset = $template->getTemplateAsset('video_thumb.png', 'images');
			$uri = $asset->url;
			return $uri;
		}

		// Strip cdn path if exists.
		// Note: At one point, cdn path was stored along with the thumbnail path
		//       in the db which is the mistake we are trying to rectify here.
		$file   = str_ireplace($config->get('videocdnpath'), '', $file);

		// CDN or local
		$baseUrl = $config->get('videobaseurl') or
		$baseUrl = JURI::root();
		$uri = str_replace('\\', '/', rtrim($baseUrl, '/') . '/' . ltrim($file, '/'));
		return $uri;
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
}