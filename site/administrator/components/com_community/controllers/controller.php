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

jimport( 'joomla.application.component.controller' );

/**
 * JomSocial Base Controller
 */
class CommunityController extends JControllerLegacy
{
	public function __construct()
	{
		parent::__construct();

		// Only process this if task != azrul_ajax
		$task	= JRequest::getCmd( 'task' , '' );
		$document = JFactory::getDocument();
		if( $document instanceof JDocumentHTML)
		{
			$app = JFactory::getApplication();
			$template = $app->getTemplate();

			// Add some javascript that may be needed
			if( $task != 'azrul_ajax' )
			{
				$version = new JVersion();
				if($version->getHelpVersion() <='0.25') {
					// load jquery if joomla 2.5.x
					$document->addScript( COMMUNITY_ASSETS_URL . '/js/jquery.min.js' );
					$document->addScript( COMMUNITY_ASSETS_URL . '/js/bootstrap.min.js' );
					$document->addScript( COMMUNITY_ASSETS_URL . '/js/joomla25.min.js' );
					// load css if joomla 2.5.x
					$document->addStyleSheet( COMMUNITY_ASSETS_URL . '/css/joomla25.css' );
				}
				//$document->addScript( COMMUNITY_ASSETS_URL . '/js/jquery.min.js' );
				//$document->addScript( COMMUNITY_ASSETS_URL . '/js/bootstrap.min.js' );
				//$document->addScript( COMMUNITY_ASSETS_URL . '/js/ace-elements.min.js' );
				//$document->addScript( COMMUNITY_ASSETS_URL . '/js/ace.min.js' );

				//dont load in installer
				if(JRequest::getCmd('view','community') !== 'installer')
				{
					require_once JPATH_COMPONENT.'/helpers/community.php';
				}
			}
			$document->addScript(COMMUNITY_ASSETS_URL.'/admin.js');
			$document->addScript( JURI::root() . 'components/com_community/assets/window-1.0.js' );
			$document->addStyleSheet( COMMUNITY_ASSETS_URL . '/css/bootstrap.min.css' );
			$document->addStyleSheet( COMMUNITY_ASSETS_URL . '/css/font-awesome.min.css' );
			$document->addStyleSheet( COMMUNITY_ASSETS_URL . '/css/ace-fonts.css' );
			$document->addStyleSheet( COMMUNITY_ASSETS_URL . '/css/ace.min.css' );
			$document->addStyleSheet( COMMUNITY_ASSETS_URL . '/css/fullcalendar.css' );
		}
	}

	/**
	 * Method to display the specific view
	 *
	 **/
	public function display($cachable = false, $urlparams = array())
	{
		$viewName	= JRequest::getCmd( 'view' , 'community' );

		// Set the default layout and view name
		$layout		= JRequest::getCmd( 'layout' , 'default' );

		// Get the document object
		$document	= JFactory::getDocument();

		// Get the view type
		$viewType	= $document->getType();

		// Get the view
		$view		= $this->getView( $viewName , $viewType );

		$model		= $this->getModel( $viewName );

		if( $model )
		{
			$view->setModel( $model , $viewName );
		}

		// Set the layout
		$view->setLayout( $layout );

		// Display the view
		$view->display();

		// Display Toolbar. View must have setToolBar method
		if( method_exists( $view , 'setToolBar') )
		{
			$view->setToolBar();
		}
	}

	/**
	 * Save the publish status
	 *
	 * @access public
	 *
	 **/
	public function savePublish( $tableClass = 'CommunityTable' )
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$status 	= JRequest::getVar('status',NULL);
		// Determine the view.
		$viewName	= JRequest::getCmd( 'view' , 'configuration' );

		// Determine whether to publish or unpublish
		$state	= ( JRequest::getWord( 'task' , '' ) == 'publish' ) ? 1 : 0;

		//$id		= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$id			= $jinput->post->get( 'cid', array(), 'array' );

		$count	= count($id);

		$table	= JTable::getInstance( $viewName , $tableClass );
		$table->publish( $id , $state );

		switch ($state)
		{
			case 1:
				$message = JText::sprintf('Item(s) successfully Published', $count);
				break;
			case 0:
				$message = JText::sprintf('Item(s) successfully Unpublished', $count);
				break;
		}

		$extendURL = '';
		if(isset($status))
		{
			$extendURL = '&status='.$status;
		}

		$mainframe->redirect( 'index.php?option=com_community&view=' . $viewName . $extendURL , $message ,'message');
	}

	/**
	 * AJAX method to toggle publish status
	 *
	 * @param	int	id	Current field id
	 * @param	string field	The field publish type
	 *
	 * @return	JAXResponse object	Azrul's AJAX Response object
	 **/
	public function ajaxTogglePublish( $id, $field , $viewName )
	{
		$user	= JFactory::getUser();

		// @rule: Disallow guests.
		if ( $user->get('guest'))
		{
			JError::raiseError( 403, JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN') );
			return;
		}

		$response	= new JAXResponse();

		// Load the JTable Object.
		$row	= JTable::getInstance( $viewName , 'CommunityTable' );
		$row->load( $id );

		if( $row->$field == 1)
		{
			$row->$field	= 0;
			$row->store();
			$image			= 'publish_x.png';
		}
		else
		{
			$row->$field	= 1;
			$row->store();
			$image			= 'tick.png';
		}
		// Get the view
		$view		= $this->getView( $viewName , 'html' );

		$html	= $view->getPublish( $row , $field , $viewName . ',ajaxTogglePublish' );

	   	$response->addAssign( $field . $id , 'innerHTML' , $html );

                if ( $viewName == 'videos' && $row->$field == 0) {
                    $params = new CParameter('');
                    $params->set('url', '');
                    $params->set('target', $row->creator);

                    CNotificationLibrary::add(
                            'system_messaging', 
                            NULL, 
                            $row->creator, 
                            JText::sprintf('COM_COMMUNITY_VIDEO_UNPUBLISHED', $row->title), 
                            '', 
                            '', 
                            $params) ;
                        
                    
                }
                
	   	return $response->sendResponse();
	}

	public function cacheClean($cacheId){
		$cache = CFactory::getFastCache();

		$cache->clean($cacheId);
	}

	public function _getCurrentVersionData()
	{
		$component_name = "com_community_std";
		$data = 'http://www.jomsocial.com/ijoomla_latest_version.txt';
		$installed_version = $this->_getLocalVersionNumber();

		$version = "";
		$ch = @curl_init($data);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		@curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		$version = @curl_exec($ch);
		if(isset($version) && trim($version) != ""){
			$pattern = "";
			if(version_compare(JVERSION, '3.0', 'ge')){
				$pattern = "/3.0_".$component_name."=(.*);/msU";
			} else {
				$pattern = "/1.6_com_community=(.*);/msU";
			}

			if($installed_version != 0 && $installed_version != ""){// on Joomla 2.5 and need to check available version, 2.8 or 3.0
				if(strpos($installed_version, "2.6") !== FALSE){
					$pattern = "/1.6_com_community=(.*);/msU";
				}
				elseif(strpos($installed_version, "2.8") !== FALSE){
					$pattern = "/3.0_com_community_std=(.*);/msU";
				}
				else{
					$pattern = "/3.0_".$component_name."=(.*);/msU";
				}
			} else {
				$pattern = "/3.0_".$component_name."=(.*);/msU";
			}

			preg_match($pattern, $version, $result);

			if(is_array($result) && count($result) > 0){
				$version = trim($result["1"]);
			} else {
				$version = "";
			}

			$data = new stdClass();
			$data->version = (string)$version;

			return $data;
		}

		return false;
	}

	public function _getLocalVersionNumber()
	{
		$xml		= JPATH_COMPONENT . '/community.xml';
		$parser		= new SimpleXMLElement( $xml , NULL , true );

		$version	= $parser->version;

		return $version;
	}
}
