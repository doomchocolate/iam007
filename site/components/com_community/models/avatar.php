<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die('Restricted access');

require_once ( JPATH_ROOT .'/components/com_community/models/models.php');

/**
 *
 */
class CommunityModelAvatar extends JCCModel
{
	/**
	 * Return live path to large avatar
	 */
	public function getLargeImg($id , $appType = 'profile'){
		return $this->_getImage($id, 2, $appType , 'components/com_community/assets/default.jpg');
	}

	/**
	 * Return live path to large avatar
	 */
	public function getMediumImg($userid){
		//return
	}


	/**
	 * Return live path to large avatar
	 * $addType	The type of the calling app be it group, profile etc.
	 */
	public function getSmallImg($id , $appType = 'profile'){
		return $this->_getImage($id, 0, $appType , 'components/com_community/assets/default_thumb.jpg');
	}

	/**
	 *
	 */
	public function _getImage($id, $type, $appType , $default){
		$db = $this->getDBO();

		$strSQL	= 'SELECT ' . $db->quoteName('path') .' FROM ' . $db->quoteName('#__community_avatar') . ' '
				. 'WHERE ' . $db->quoteName('id') .'=' . $db->Quote($id) . ' '
				. 'AND ' . $db->quoteName('apptype') .'=' . $db->Quote($appType) . ' '
				. 'AND ' . $db->quoteName('type') .'=' . $db->Quote($type);

  		$db->setQuery($strSQL);
  		$path = $db->loadResult();

  		if(!$path){
  			// Display default image
  			$path = $default;
		}

  		return JURI::base() . $path;
	}

	/**
	 * Set small thumbnail avatar
	 * @param	int		userid
	 * @param	string	relative path to avatar image
	 */
	public function setLargeImg($id, $path , $appType){
		$this->_setImage($id, $path, 2 , $appType);
		return $this;
	}

	/**
	 * Set small thumbnail avatar
	 * @param	int		userid
	 * @param	string	relative path to avatar image
	 */
	public function setMediumImg($id, $path , $appType ){
		$obj = new stdClass();

		$obj->userid = $id;
  		$obj->path = $path;
  		$obj->type = 1;
  		$obj->appType	= $appType;
		return $this;
	}

	/**
	 * Set small thumbnail avatar
	 * @param	int		userid
	 * @param	string	relative path to avatar image
	 */
	public function setSmallImg($id, $path , $appType){
		$this->_setImage($id, $path, 0 , $appType);
		return $this;
	}



	/**
	 *
	 */
	public function _setImage($id, $path, $type , $appType){
		$db = $this->getDBO();

		$obj = new stdClass();

		$obj->id	 	= $id;

		// Fix back quotes
  		$obj->path		= CString::str_ireplace( '\\' , '/' , $path );
  		$obj->type 		= $type;
  		$obj->appType	= $appType;

  		$sql = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_avatar')
			.  ' WHERE ' . $db->quoteName('id') .'=' . $db->Quote($id)
			.  ' AND ' . $db->quoteName('apptype') .'=' . $db->Quote($appType)
			.  ' AND ' . $db->quoteName('type') .'=' . $db->Quote($type);
  		$db->setQuery($sql);
  		$exist = $db->loadResult();

  		if(!$exist){
  			$db->insertObject('#__community_avatar', $obj);

  			if($db->getErrorNum()) {
				JError::raiseError( 500, $db->stderr());
		    }

  		}else{
  			// Need to delete old image
  			$sql = 'SELECT ' . $db->quoteName('path') .' FROM ' . $db->quoteName('#__community_avatar')
				.  ' WHERE ' . $db->quoteName('id') .'=' . $db->Quote($id)
				.  ' AND ' . $db->quoteName('apptype') .'=' . $db->Quote($appType)
				.  ' AND ' . $db->quoteName('type') .'=' . $db->Quote($type);
			$db->setQuery($sql);

			$oldfile = $db->loadResult();
			$oldfile = CString::str_ireplace('/', '/' , $oldfile);
			if($db->getErrorNum()) {
				JError::raiseError( 500, $db->stderr());
		    }

			JFile::delete($oldfile);

  			$sql = 'UPDATE ' . $db->quoteName('#__community_avatar') .' SET ' . $db->quoteName('path') .'=' . $db->Quote($obj->path)
			  	 . ' WHERE ' . $db->quoteName('id') .'=' . $db->Quote($id)
			  	 . ' AND ' . $db->quoteName('apptype') .'=' . $db->Quote($appType)
				 . ' AND ' . $db->quoteName('type').'=' . $db->Quote($type);

			$db->setQuery($sql);
  			$db->query();

  			if($db->getErrorNum()) {
				JError::raiseError( 500, $db->stderr());
		    }
  		}
	}
}
