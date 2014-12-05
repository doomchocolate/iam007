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
class CommunityViewProfiles extends JViewLegacy
{
	/**
	 * The default method that will display the output of this view which is called by
	 * Joomla
	 *
	 * @param	string template	Template file name
	 **/
	public function display( $tpl = null )
	{
		$profile	= $this->getModel( 'Profiles' );

		$fields		=& $profile->getFields(true);
		$pagination	=& $profile->getPagination();

		$this->assignRef( 'fields' 		, $fields );
		$this->assignRef( 'pagination'	, $pagination );
		parent::display( $tpl );
	}

	/**
	 * Method to get the Field type in text
	 *
	 * @param	string	Type of field
	 *
	 * @return	string	Text representation of the field type.
	 **/
	public function getFieldText( $type )
	{
		$model	= $this->getModel( 'Profiles' );
		$types	= $model->getProfileTypes();
		$value	= isset( $types[ $type ] ) ? $types[ $type ] : '';

		return $value;
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

		$alts	= array(	0	=>	JText::_('COM_COMMUNITY_UNPUBLISH'),
							1	=>	JText::_('COM_COMMUNITY_PUBLISHED'),
							2	=>	JText::_('COM_COMMUNITY_ADMINONLY'));

		$key	= $row->$type?$row->$type:0;

		$alt	= $alts[$key];
		$state	= $row->$type ? 'publish' : 'unpublish';
		$class = 'jgrid';
		$span = '<span class="state '.$state.'"><span class="text">'.$alt.'</span></span></a>';

		if($currentV >= '0.30')
		{
			$class = $row->type == 1 ? 'disabled jgrid': '';

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
	public function showPublish( &$row , $type)
	{
		$imgY	= 'tick.png';
		$imgX	= 'publish_x.png';

		$image	= $row->$type ? $imgY : $imgX;

		$state	= $row->$type ? 'publish' : 'unpublish';
		$alt	= $row->$type ? JText::_('COM_COMMUNITY_PUBLISHED') : JText::_('COM_COMMUNITY_UNPUBLISH');

		$href	= '<a class="jgrid"><span class="state '.$state.'"><span class="text">'.$alt.'</span></a>';

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

		// Set the titlebar text
		JToolBarHelper::title( JText::_('COM_COMMUNITY_CUSTOM_PROFILES'), 'profiles' );

		// Add the necessary buttons
		JToolBarHelper::publishList('publish', JText::_('COM_COMMUNITY_PUBLISH'));
		JToolBarHelper::unpublishList('unpublish', JText::_('COM_COMMUNITY_UNPUBLISH'));
		JToolBarHelper::divider();
		JToolBarHelper::trash('removefield', JText::_('COM_COMMUNITY_DELETE'));
		JToolBarHelper::addNew('newgroup', JText::_('COM_COMMUNITY_PROFILES_NEW_GROUP'));
		JToolBarHelper::addNew('newfield', JText::_('COM_COMMUNITY_NEW_FIELD'));
	}
}
