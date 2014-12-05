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
defined('_JEXEC') or die('Restricted access');

// Set the tables path
//JTable::addIncludePath( JPATH_ROOT .'/administrator/components/com_community/tables' );
require_once(JPATH_ROOT .'/components/com_community/libraries/core.php');

class CVideoTagging
{
	var $friendList	= null;
	var $_error		= null;

	/**
	 * private method. Used to append zero into a string.
	 */
	public function _appendZero($val)
	{
		if(JString::strlen($val) == 1)
		{
			return '00' . $val;
		}
		else if(JString::strlen($val) == 2)
		{
			return '0' . $val;
		}
		else
		{
			return $val;
		}
	}

	/**
	 *
	 */
	public function getError()
	{
		return $this->_error;
	}


	/**
	 *
	 *
	 */
	public function isTagExists($videoId, $userId)
	{
		//CFactory::load( 'models' , 'videotagging' );
		$tagModel = CFactory::getModel('videotagging');

		//reset the error message.
		$this->_error	= null;

		if($tagModel->isTagExists($videoId, $userId))
		{
			$this->_error	= JText::_('COM_COMMUNITY_PHOTO_TAG_EXIST');
			return true;
		}
	}

	/**
	 * Method use to create a new tag on a video
	 * @object - $tagObj
	 * @param	$photoId - Current video id being tag.
	 * @param	$userId - User that tagged into video
	 **/
	//function addTag( $photoId, $userId, $posX, $posY, $w=0, $h=0)
	public function addTag( $tagObj )
	{
		//CFactory::load( 'models' , 'videotagging' );
		$tagModel = CFactory::getModel('videotagging');

		//reset the error message.
		$this->_error	= null;

		if($tagModel->isTagExists($tagObj->videoId, $tagObj->userId))
		{
			$this->_error	= JText::_('COM_COMMUNITY_VIDEO_TAG_EXIST');
			return 0;
		}

		$tagId		= 0;

		if($tagModel->addTag($tagObj->videoId , $tagObj->userId)->return_value['addTag'])
		{
			$tagId	= $tagModel->getTagId($tagObj->videoId, $tagObj->userId);
		}
		else
		{
			$this->_error	= $tagModel->getError();
		}

		return $tagId;
	}

	/**
	 * Method use to create a remove a tagged user from video
	 *
	 * @param	$videoId - Current video id being tag.
	 * @param	$userId - User that tagged into video.
	 **/
	public function removeTag( $videoId, $userId)
	{
		//CFactory::load( 'models' , 'videotagging' );
		$tagModel = CFactory::getModel('videotagging');

		//reset the error message.
		$this->_error	= null;

		if($tagModel->removeTag( $videoId, $userId ))
		{
			return true;
		}
		else
		{
			$this->_error	= $tagModel->getError();
			return false;
		}
	}

	public function removeTagByVideo($videoId)
	{
		//CFactory::load( 'models' , 'videotagging' );
		$tagModel = CFactory::getModel('videotagging');

		//reset the error message.
		$this->_error	= null;

		if($tagModel->removeTagByVideo( $videoId ))
		{
			return true;
		}
		else
		{
			$this->_error	= $tagModel->getError();
			return false;
		}
	}

	/**
	 * Method use to get all the tagged users from a video
	 *
	 * @param	$photoId - Current video id being tag.
	 * @param	$userId - User that tagged into video.
	 **/
	public function getTaggedList( $videoId )
	{
		//CFactory::load( 'models' , 'videotagging' );
		$tagModel = CFactory::getModel('videotagging');

		$config	= CFactory::getConfig();

		$taggedList	= $tagModel->getTaggedList( $videoId );
		$result		= null;

		return $taggedList;
	}

	/**
	 * Method use to get all friend list belong to current logged in user which
	 * excluded those already tagged in the current viewing video
	 * @param	$photoId - Current video id being tag.
	 * @param	$userId - User that tagged into video.
	 **/
	public function getFriendList( $videoId )
	{
		if(empty($this->friendList))
		{
			//CFactory::load( 'models' , 'videotagging' );
			$tagModel = CFactory::getModel('videotagging');
			$this->friendList	= $tagModel->getFriendList($videoId);
		}

		return $this->friendList;
	}

}
?>