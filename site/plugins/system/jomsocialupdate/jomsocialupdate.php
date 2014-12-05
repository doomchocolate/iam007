<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.filesystem.file' );

class  plgSystemJomsocialUpdate extends JPlugin
{

	public function plgSystemJomsocialUpdate(& $subject, $config)
	{

		parent::__construct($subject, $config);

		$this->mainframe	= JFactory::getApplication();

        $lang = JFactory::getLanguage();
        $lang->load('com_community.menu',JPATH_ADMINISTRATOR);

	}

	public function onAfterRender()
	{
		// Render status
		if( $this->_loadPlugin() )
			$this->_renderStatus();
	}

	private function _loadPlugin()
	{
		$my			= JFactory::getUSer();
		$arrayFormat= array('feed','raw');
		$format		= JRequest::getVar( 'format' , '' , 'REQUEST' );
		$nohtml		= JRequest::getVar( 'no_html' , '' , 'REQUEST' );
		$jax	 	= JPluginHelper::isEnabled('system', 'azrul.system');

		// Load only for backend
		if( $this->mainframe->isAdmin() && $my->id && $nohtml != 1 && !in_array($format,$arrayFormat) && $jax ){
			return true;
		}

		return false;
	}

	private function _renderStatus()
	{
		$date	= JFactory::getDate();
		$jparam	= new JConfig();

		if( !JFile::exists( JPATH_ROOT .'/administrator/components/com_community/community.xml' ) )
		{
			return false;
		}

		if(JFile::exists( JPATH_ROOT .'/administrator/components/com_community/jomsocialupdate.ini' ))
			$lastcheckdate	= JFile::read(JPATH_ROOT .'/administrator/components/com_community/jomsocialupdate.ini');
		else
			$lastcheckdate	= $date->format('Y-m-d H:i:s');
			JFile::write(JPATH_ROOT .'/administrator/components/com_community/jomsocialupdate.ini',$lastcheckdate);

		$dayInterval	= 1; // days
		$currentdate	= $date->format('Y-m-d H:i:s');

	    $checkVersion	= strtotime($currentdate) > strtotime($lastcheckdate)+($dayInterval*60*60*24);

		// Load language
		$lang		= JFactory::getLanguage();
		$lang->load( 'com_community', JPATH_ROOT .'/administrator' );

		$button	= $this->_getButton($checkVersion);
		$html	= JResponse::getBody();
		$html	= str_replace( '<div id="module-status">' , '<div id="module-status">' . $button , $html );

	  	// Load AJAX library for the back end.
		$jaxScript = '';
		$noHTML	= JRequest::getInt( 'no_html' , 0 );
		$format		= JRequest::getWord( 'format' , 'html' );
		if( !$noHTML && $format == 'html' )
		{
			require_once(AZRUL_SYSTEM_PATH .'/pc_includes/ajax.php');
			$jax		= new JAX( AZRUL_SYSTEM_LIVE . '/pc_includes' );
			$jax->setReqURI( rtrim( JURI::root() , '/' ). '/administrator/index.php' );
			$jaxScript	= $jax->getScript();
		}
		JResponse::setBody( $html . $jaxScript );
	}

	private function _getButton($checkVersion=false)
	{
		$button		= '';
		$updateText	= 'Jomsocial is updated';

		// Get the current version
		$data			= $this->_getCurrentVersionData();
		$version		= $this->_getLocalVersionNumber();

		if($data)
		{
			// Test versions
			if( version_compare($version , $data->version ,'<') ) {
				$updateText	= 'Jomsocial Update Available!';

				$button	= '<span class="jomsocial-update" style="background:#F0F0F0 url(\'components/com_community/assets/icons/community-favicon.png\') no-repeat scroll 3px 3px;">
								<a href="javascript:void(0);" onclick="azcommunity.checkVersion();">
									' . JText::_($updateText) . '
								</a>
							</span>';
			}
		}

		// If local community.xml not found
		if( empty($version) )
			$button	= '<span class="jomsocial-update" style="color:red;background:#F0F0F0 url(\'components/com_community/assets/icons/community-favicon.png\') no-repeat scroll 3px 3px;">
						Jomsocial Not Installed
						</span>';

		// If remote community.xml not found
		if( empty($data->version) )
			$button	= '<span class="jomsocial-update" style="color:red;background:#F0F0F0 url(\'components/com_community/assets/icons/community-favicon.png\') no-repeat scroll 3px 3px;">
						Jomsocial.com is not connected
						</span>';

		return $button;
	}


	private function _getLocalBuildNumber()
	{
		$versionString	= $this->_getLocalVersionString();
		$tmpArray		= explode( '.' , $versionString );

		if( isset($tmpArray[2]) )
		{
			return $tmpArray[2];
		}

		// Unknown build number.
		return 0;
	}

	private function _getLocalVersionNumber()
	{
		return $this->_getLocalVersionString();
	}

	/**
	 * Read version from remove server
	 * @return [object] [version of current info in server]
	 */
	private function _getCurrentVersionData()
	{
		// @TODO: Library

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

	/**
	 * Read local xml file to get current version
	 * @return [string] [Current version]
	 */
	private function _getLocalVersionString()
	{
		static $version	= '';

		$xml	= JPATH_ROOT .'/administrator/components/com_community/community.xml';

		$parser	= new SimpleXMLElement( $xml , NULL , true );

		if( is_object($parser) && empty( $version ) )
		{
			$version		= $parser->version;
		}

		return $version;
	}
}