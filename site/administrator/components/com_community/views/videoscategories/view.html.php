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
class CommunityViewVideosCategories extends JViewLegacy
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

		$categories	= $this->get( 'Categories' );
		$pagination	= $this->get( 'Pagination' );

		// Escape the output
		//CFactory::load( 'helpers' , 'string' );
		foreach ($categories as $row)
		{
			$row->name	= CStringHelper::escape($row->name);
			$row->description	= CStringHelper::escape($row->description);
			if( $row->parent == 0 )
			{
				$row->pname	=   JText::_("COM_COMMUNITY_NO_PARENT");
			}
			else
			{
				$parent   = JTable::getInstance( 'VideosCategory', 'CTable' );
				$parent->load( $row->parent );

				$row->pname	=   CStringHelper::escape( $parent->name );
			}
		}

		$this->assignRef( 'categories'	, $categories );
		$this->assignRef( 'pagination'	, $pagination );
		parent::display( $tpl );
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

		$imgY	= 'tick.png';
		$imgX	= 'publish_x.png';

		$image	= $row->$type ? $imgY : $imgX;

		$alt	= $row->$type ? JText::_('COM_COMMUNITY_PUBLISHED') : JText::_('COM_COMMUNITY_UNPUBLISH');

		$href	= '<a class="jgrid" href="javascript:void(0);" onclick="azcommunity.togglePublish(\'' . $ajaxTask . '\',\'' . $row->id . '\',\'' . $type . '\');">';

		$state	= $row->$type ? 'publish' : 'unpublish';
		$href	.= '<span class="state '.$state.'"><span class="text">'.$alt.'</span></span></a>';

		return $href;
	}

	public function setToolBar()
	{
		// Set the titlebar text
		JToolBarHelper::title( JText::_('COM_COMMUNITY_VIDEO_CATEGORIES'), 'videoscategories');

		// Add the necessary buttons
		//JToolBarHelper::publishList( 'publish' , JText::_('COM_COMMUNITY_PUBLISH') );
		//JToolBarHelper::unpublishList( 'unpublish' , JText::_('COM_COMMUNITY_UNPUBLISH') );
		//JToolBarHelper::divider();
		JToolBarHelper::trash( 'removecategory', JText::_('COM_COMMUNITY_DELETE'));
		JToolBarHelper::addNew( 'newcategory' , JText::_('COM_COMMUNITY_NEW') );
	}
}