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
class CommunityViewReports extends JViewLegacy
{
	/**
	 * The default method that will display the output of this view which is called by
	 * Joomla
	 *
	 * @param	string template	Template file name
	 **/
	public function display( $tpl = null )
	{
		//$reports	= $this->get( 'Reports' );
		//$pagination	= $this->get( 'Pagination' );

		$model = $this->getModel( 'Reports' );
		$reports	= $model->getReports();
		$pagination	= $model->getPagination();

		if( $this->getLayout() == 'childs' )
		{
			$this->_displayChilds( $tpl );
			return;
		}
		// Set the titlebar text
		JToolBarHelper::title( JText::_('COM_COMMUNITY_REPORTS'), 'reports' );

		// Add the necessary buttons
		JToolBarHelper::trash('removeReport', JText::_('COM_COMMUNITY_DELETE'));
		JToolBarHelper::trash('purgeProcessed', JText::_('COM_COMMUNITY_REPORTS_PURGE_COMPLETED') , false );

 		$this->assignRef( 'reports'		, $reports );
 		$this->assignRef( 'pagination'	, $pagination );
		parent::display( $tpl );
	}

	public function _displayChilds( $tpl )
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$reportId	= $jinput->get('reportid' , '', 'INT');

		// Set the titlebar text
		JToolBarHelper::title( JText::_('COM_COMMUNITY_REPORTS_VIEWING_REPORTERS'), 'reports' );

		// Add the necessary buttons
		JToolBarHelper::trash('removeReport', JText::_('COM_COMMUNITY_DELETE'));
		$report		= JTable::getInstance( 'reports' , 'CommunityTable' );
		$report->load( $reportId );

		$model		= $this->getModel( 'Reporters' );
		$reporters	= $model->getReporters( $reportId );
		$pagination	= $model->getPagination();

		foreach($reporters as $key=>$data)
		{
			$return = explode(',',$data->method);
			$reporters[$key]->method = $return[1];
		}

		$this->assignRef( 'reporters' , $reporters );
		$this->assignRef( 'pagination'	, $pagination );
		parent::display( $tpl );
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

	public function _getStatusHTML()
	{
		// Check if there are any categories selected
		$status	= JRequest::getInt( 'status' , 3 );

		$select	= '<select class="no-margin" name="status" onchange="submitform();">';

		$statusArray = array( 3=>JText::_('COM_COMMUNITY_VIEW_ALL'), 1=>JText::_('COM_COMMUNITY_REPORTS_PROCESSED'), 0=>JText::_('COM_COMMUNITY_PENDING'), 2=>JText::_('COM_COMMUNITY_REPORTS_IGNORED'));

		foreach($statusArray as $key=>$array)
		{
			$selected = ($status == $key) ? 'selected="true"' : '';
			$select .='<option value="'.$key.'"'.$selected.' >'.JText::_($array).'</option>';
		}

		$select	.= '</select>';

		return $select;
	}
}
