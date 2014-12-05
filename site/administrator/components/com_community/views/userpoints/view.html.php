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
class CommunityViewUserPoints extends JViewLegacy
{
	/**
	 * The default method that will display the output of this view which is called by
	 * Joomla
	 *
	 * @param	string template	Template file name
	 **/
	public function display( $tpl = null )
	{
		global $option;
		$mainframe	= JFactory::getApplication();

		$userpoints	= $this->get( 'UserPoints' );
		$pagination	= $this->get( 'Pagination' );
		$ordering	= $this->get( 'Ordering' );

		$acl   = JFactory::getACL();
 		$this->assignRef( 'userpoints'	, $userpoints );
 		$this->assignRef( 'pagination'	, $pagination );
 		$this->assignRef( 'lists'	, $ordering );
 		$this->assignRef( 'acl'	, $acl );

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
	 * Private method to set the toolbar for this view
	 *
	 * @access private
	 *
	 * @return null
	 **/
	public function setToolBar()
	{
		// Set the titlebar text
		JToolBarHelper::title( JText::_('Points and Activities'), 'userpoints' );

		// Add the necessary buttons
		JToolBarHelper::addNew('ruleScan', JText::_('COM_COMMUNITY_USERPOINTS_RULE_SCAN'));
		JToolBarHelper::publishList('publish', JText::_('COM_COMMUNITY_PUBLISH'));
		JToolBarHelper::unpublishList('unpublish', JText::_('COM_COMMUNITY_UNPUBLISH'));
		JToolBarHelper::trash('removeRules', JText::_('COM_COMMUNITY_DELETE'));
		JToolBarHelper::custom('documentation', 'help', 'help', JText::_('Help'), false);
	}
}