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

Class CommunityControllerSystem extends CommunityController
{
	public function __construct() {
		parent::__construct();
	}
	
	public function ajaxAutoupdate($ordercode = '', $email = ''){
				
		//do save config first
		if(!empty($ordercode) && !empty($email)){
			$config	= JTable::getInstance( 'configuration' , 'CommunityTable' );
			$config->load( 'config' );
			$config->name = 'config';
			$params	= new JRegistry( $config->params );
			$params->set( 'autoupdateordercode' , $ordercode );
			$params->set( 'autoupdateemail' , $email );
			$config->params	= $params->toString();
			$saved = $config->store(); 
		}
		
		require_once( JPATH_ROOT.'/administrator/components/com_community/libraries/autoupdate.php' );
		
		//Check update
		$res = CAutoUpdate::getUpdate();
		@ob_end_clean();
		$objResponse	= new JAXResponse();	
		if(!$res){
			$msg = implode("\n", CAutoUpdate::getError());
			$objResponse->addScriptCall( 'joms.jQuery(".autoupdate-loader").hide(); ' );
			$objResponse->addScriptCall( 'joms.jQuery("#autoupdateordercode,#autoupdateemail,#autoupdatesubmit").removeAttr("disabled");' );
			$objResponse->addScriptCall( 'joms.jQuery("#autoupdatesubmit").val("'.JText::_( 'COM_COMMUNITY_CONFIGURATION_CHECK_AUTOUPDATE' ).'");' );
			$objResponse->addScriptCall( 'alert("'.JText::sprintf('COM_COMMUNITY_CONFIGURATION_AUTOUPDATE_ERROR', $msg ).'");' );
			return $objResponse->sendResponse();
		}	
		$objResponse->addScriptCall( 'joms.jQuery(".autoupdate-loader").hide(); ' );
		$objResponse->addScriptCall( 'joms.jQuery("#autoupdateordercode,#autoupdateemail,#autoupdatesubmit").removeAttr("disabled");' );
		$objResponse->addScriptCall( 'joms.jQuery("#autoupdatesubmit").val("'.JText::_( 'COM_COMMUNITY_CONFIGURATION_CHECK_AUTOUPDATE' ).'");' );
		if($res){
			
			jimport('joomla.installer.installer');
			jimport('joomla.installer.helper'); 
			//$package = JInstallerHelper::unpack($res);
			
			//Adapted from JInstallerHelper::unpack
			// Path to the archive =========
			$archivename = $res;
	
			// Temporary folder to extract the archive into
			$tmpdir = uniqid('install_');
	
			// Clean the paths to use for archive extraction
			$extractdir = JPath::clean(dirname($res) . '/' . $tmpdir);
			$archivename = JPath::clean($archivename);
	
			// Do the unpacking of the archive
			$result = JArchive::extract($archivename, $extractdir);
	
			if ($result === false) {
				$objResponse->addScriptCall( 'joms.jQuery(".autoupdate-loader").hide();' );
				$objResponse->addScriptCall( 'joms.jQuery(".do-download-update").remove();' );
				$objResponse->addScriptCall( 'alert("JomSocial package cannot be unpacked.")' );
				return $objResponse->sendResponse();
			}
			
			$appszip = false;
			$extractdirfiles = scandir($extractdir); //error_log(print_r($extractdirfiles, true));
			foreach($extractdirfiles as $f){
				if(strpos($f, 'com_community_') !== FALSE){
					//error_log('DIR: '.$extractdir.'/'.$f);
					$package = JInstallerHelper::unpack($extractdir.'/'.$f);
				}
			}
			
			//shouldnt be empty here, something's wrong with the package
			if(empty($package)){
				$objResponse->addScriptCall( 'joms.jQuery(".autoupdate-loader").hide();' );
				$objResponse->addScriptCall( 'joms.jQuery(".do-download-update").remove();' );
				$objResponse->addScriptCall( 'alert("'.JText::_('COM_COMMUNITY_CONFIGURATION_AUTOUPDATE_ERROR_NO_COMPONENT').'")' );
				return $objResponse->sendResponse();
			}
			//error_log( 'PACKAGE:'. print_r($package,true) );
						
			// Get an installer instance
			//
			//$installer = JInstaller::getInstance();
			//$installer->install($package['dir']);
		
			$objResponse->addScriptCall( 'joms.jQuery(".autoupdate-loader").hide();' );
			$objResponse->addScriptCall( 'joms.jQuery(".do-download-update").remove();' );
			$objResponse->addScriptCall( 'joms.jQuery("#autoupdate-progress").empty();' );
			$objResponse->addScriptCall( "joms.jQuery('#autoupdatesubmit').after(' <form onsubmit=\"return confirm(\\'".JText::_('COM_COMMUNITY_CONFIGURATION_AUTOUPDATE_ASK_PROCEED')."\\');\" class=\"do-download-update\" style=\"display:inline\" method=\"post\" action=\"".JURI::base()."index.php?option=com_installer&view=install\"><input type=\"hidden\" name=\"install_directory\" value=\'".$package['extractdir']."\'><input type=\"hidden\" name=\"task\" value=\"install.install\"><input type=\"hidden\" name=\"installtype\" value=\"folder\"><input type=\"submit\" value=\"".JText::_('COM_COMMUNITY_CONFIGURATION_AUTOUPDATE_INSTALL_BUTTON')."\" />".JHTML::_( 'form.token' )."</form>');"); 				
		}
		return $objResponse->sendResponse();
	}

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

		//$model		=& $this->getModel( $viewName );

		//$view->setModel( $model , $viewName );

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
}