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

/**
 * JomSocial Table Model
 */
class CommunityTableVideos extends JTable
{
	var $id				= null;
	var $parent			= null;
	var $name			= null;
	var $description		= null;
	var $published			= null;
	var $_width			= 0;
	var $_height		= 0;
	var $_provider		= null;


	public function __construct(&$db)
	{
		parent::__construct('#__community_videos','id', $db);

		$this->_width	= CVideosHelper::getVideoSize('width');
		$this->_height	= CVideosHelper::getVideoSize('height');
	}

	public function load( $oid = null, $reset = true)
	{
		if( parent::load( $oid ) )
		{
			// @todo: make sure loading is done ok
			$providerName	= JString::strtolower($this->type);
			if (empty($providerName)) {
				return false;
			}
			$libraryPath	= COMMUNITY_COM_PATH .'/libraries/videos' .'/'. $providerName . '.php';

			require_once($libraryPath);
			$className		 = 'CTableVideo' . JString::ucfirst($providerName);
			$this->_provider = new $className( $this->_db );

			return true;
		}
		return false;

	}

	public function delete( $id = null )
	{
		// Only delete if no groups are assigned to this category.
		parent::delete( $id );
		return true;
	}

	public function getPlayerHTML($width=null, $height=null, $defaultView=true)
	{
		$id		= ($this->type=='file') ? $this->id : $this->video_id;
		$width	= ($width) ? $width : $this->_width;
		$height	= ($height) ? $height : $this->_height;

		if ($defaultView)
		{
			$html		= $this->_provider->getViewHTML($id, $width , $height );
		} else {
			$html		= $this->_provider->getEmbedCode($id, $width , $height );
		}

		return $html;
	}
}
