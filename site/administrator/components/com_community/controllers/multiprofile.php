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

class CommunityControllerMultiProfile extends CommunityController
{
	public function __construct()
	{
		parent::__construct();
		$this->registerTask( 'publish' , 'savePublish' );
		$this->registerTask( 'unpublish' , 'savePublish' );
	}

	public function orderUp()
	{
		$this->updateOrder( -1 );
	}

	public function orderDown()
	{
		$this->updateOrder( 1 );
	}

	public function removeWatermark(){

		$mainframe	= JFactory::getApplication();

		$jinput 	= $mainframe->input;
		$id				= $jinput->request->get('id' , '', 'INT' ) ;

		$multiprofile	= JTable::getInstance( 'MultiProfile' , 'CTable' );
		$multiprofile->load( $id );

		JFile::delete( JPATH_ROOT .'/'.$multiprofile->watermark);
		JFile::delete( JPATH_ROOT .'/'.$multiprofile->thumb);
		JFile::delete( JPATH_ROOT .'/'.$multiprofile->avatar);

		$multiprofile->watermark_hash = '';
		$multiprofile->watermark_location = '';
		$multiprofile->thumb = '';
		$multiprofile->watermark = '';
		$multiprofile->avatar = '';
		$multiprofile->store();

		$mainframe->redirect('index.php?option=com_community&view=multiprofile&layout=edit&id='.$id , JText::_( 'COM_COMMUNITY_REMOVE_WATERMARK_SUCCESS' ),'message' );
	}

	public function updateOrder( $direction )
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$db			= JFactory::getDBO();
		$id			= $jinput->post->get('cid', array(), 'array'); //JRequest::getVar( 'cid', array(), 'post', 'array' );


		if( isset( $id[0] ) )
		{
			$row	= JTable::getInstance( 'Multiprofile' , 'CTable' );
			$row->load( (int) $id[0] );
			$row->move( $direction );

			$mainframe->redirect('index.php?option=com_community&view=multiprofile' , JText::_( 'COM_COMMUNITY_MULTIPROFILE_ORDERING_UPDATED' ) ,'message' );
		}

		$mainframe->redirect('index.php?option=com_community&view=multiprofile' , JText::_( 'COM_COMMUNITY_MULTIPROFILE_ORDERING_UPDATE_ERROR' ) , 'error' );
	}

	public function saveOrder()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$db			= JFactory::getDBO();
		$cid		= $jinput->post->get('cid', array(0), 'array') ; //JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$order		= $jinput->post->get('order', array(0), 'array') ; //JRequest::getVar( 'order', array (0), 'post', 'array' );
		$total		= count($cid);
		$conditions	= array ();

		JArrayHelper::toInteger($cid, array(0));
		JArrayHelper::toInteger($order, array(0));

		$row = JTable::getInstance('MultiProfile' , 'CTable' );

		// Update the ordering for items in the cid array
		for ($i = 0; $i < $total; $i ++)
		{
			$row->load( (int) $cid[$i] );

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
				$row->store();
			}
		}
		$mainframe		= JFactory::getApplication();

		$mainframe->redirect('index.php?option=com_community&view=multiprofile' , JText::_('COM_COMMUNITY_MULTIPROFILE_ORDERING_SAVED') ,'message' );
	}

	public function savePublish( $tableClass = 'Ctable')
	{
		parent::savePublish( $tableClass );
	}

	public function ajaxTogglePublish( $id , $type, $viewName=false)
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
		$row	= JTable::getInstance( 'MultiProfile' , 'CTable' );
		$row->load( $id );
		$row->publish( $row->id , (int) !$row->published );
		$row->load( $id );

		$image	= $row->published ? 'publish_x.png' : 'tick.png';

		$view	= $this->getView( 'multiprofile' , 'html' );

		$html	= $view->getPublish( $row , 'published' , 'multiprofile,ajaxTogglePublish' );

		$response->addAssign( $type . $id , 'innerHTML' , $html );

		return $response->sendResponse();
	}

	public function save()
	{
		jimport( 'joomla.filesystem.folder' );
		jimport( 'joomla.filesystem.file' );

		$mainframe  = JFactory::getApplication();
		$jinput     = $mainframe->input;
		$id         = JRequest::getInt( 'id' , 0 , 'POST' );
		$post       = JRequest::get( 'POST' );
		$fields     = $jinput->get('fields' , '', 'NONE');
		$name       = $jinput->get('name' , '', 'STRING');
		$tmpParents = $jinput->get('parents' , '', 'NONE');
		$mainframe  = JFactory::getApplication();
		$task       = JRequest::getCmd('task');
		$isNew      = $id == 0 ? true : false;
		$validated  = true;

		$multiprofile = JTable::getInstance( 'MultiProfile' , 'CTable' );
		$multiprofile->load( $id );

		// Skip watermarking if it's the same location
		$skipWatermark = (isset($multiprofile->watermark_location) && isset($post['watermark_location']) && ($post['watermark_location'] == $multiprofile->watermark_location)) ? true : false;

		// Bind with form post
		$multiprofile->bind($post);
		// Can't have an empty name now can we?
		if( empty($name) ){
			$validated = false;
			$mainframe->enqueueMessage( JText::_ ( 'COM_COMMUNITY_MULTIPROFILE_NAME_EMPTY' ), 'error' );
		}

		$date  = JFactory::getDate();
		$isNew = ($multiprofile->id == 0) ? true : false;

		if( $isNew ) {
			$multiprofile->created	= $date->toSql();
		}

		// Store watermarks for profile types.
		$watermark = $jinput->files->get('watermark' , '', 'NONE'); //JRequest::getVar( 'watermark' , '' , 'FILES');

		if(!empty($watermark['tmp_name'])){
			// Do not allow image size to exceed maximum width and height
			if( isset($watermark['name']) && !empty($watermark['name']) )
			{
				list( $width , $height ) = getimagesize( $watermark[ 'tmp_name' ] );

				/**
				* watermark can't large than 16px
				* @todo use define for min width & height instead fixed number here
				*/
				if( $width > 16 || $height > 16 )
				{
					$validated = false;
					$mainframe->enqueueMessage ( JText::_ ( 'COM_COMMUNITY_MULTIPROFILE_WATERMARK_IMAGE_EXCEEDS_SIZE' ), 'error' );
				}
			}
		}

		if($validated) {
			$multiprofile->store();

			// If image file is specified, we need to store the thumbnail.
			if(!empty($watermark['tmp_name'])){
				if( isset($watermark['name']) && !empty($watermark['name']) )
				{
					if(!JFolder::exists(JPATH_ROOT .'/'. COMMUNITY_WATERMARKS_PATH)){
						JFolder::create(JPATH_ROOT .'/'. COMMUNITY_WATERMARKS_PATH);
					}
					$watermarkFile	= 'watermark_' . $multiprofile->id . CImageHelper::getExtension( $watermark['type'] );
					JFile::copy( $watermark[ 'tmp_name' ] , JPATH_ROOT .'/'. COMMUNITY_WATERMARKS_PATH .'/'. $watermarkFile );

					$multiprofile->watermark = CString::str_ireplace( '/', '/' , COMMUNITY_WATERMARKS_PATH ) .  '/' . $watermarkFile;
					$multiprofile->store();
				}
			}

			// @rule: Create the watermarks folder if doesn't exists.
			if( !JFolder::exists( COMMUNITY_WATERMARKS_PATH ) )
			{
				if(!JFolder::create( COMMUNITY_WATERMARKS_PATH ) )
				{
					$mainframe->enqueueMessage( JText::_('COM_COMMUNITY_MULTIPROFILE_UNABLE_TO_CREATE_WATERMARKS_FOLDER') );
				}
			}

			// @rule: Create original folder within watermarks to store original user photos.
			if( !JFolder::exists( COMMUNITY_WATERMARKS_PATH . '/original' ) )
			{
				if(!JFolder::create( COMMUNITY_WATERMARKS_PATH . '/original' ) )
				{
					$mainframe->enqueueMessage( JText::_('COM_COMMUNITY_MULTIPROFILE_UNABLE_TO_CREATE_WATERMARKS_FOLDER') );
				}
			}

			if(!empty($watermark['tmp_name']) || (! $isNew && ! $skipWatermark)) {
				if(isset($watermark['name']) && !empty( $watermark['name'] )) {
					$watermarkPath = $watermark[ 'tmp_name'];
					$watermark_hash	= md5( $watermark['name'] . time() );
				} else {
					$watermarkPath = JPATH_ROOT .'/'. $multiprofile->watermark;
					$watermark_hash = $multiprofile->watermark_hash;
				}

				// Create default watermarks for avatar and thumbnails.

				// Generate filename
				$fileName		= CImageHelper::getHashName( $multiprofile->id . time() ). '.jpg';
				$thumbFileName	= 'thumb_' . $fileName;

				// Paths where the thumbnail and avatar should be saved.
				$thumbPath	= JPATH_ROOT .'/'. COMMUNITY_WATERMARKS_PATH .'/'. $thumbFileName;
				$avatarPath	= JPATH_ROOT .'/'. COMMUNITY_WATERMARKS_PATH .'/'. $fileName;

				// Copy existing default thumbnails into the path first.
				JFile::copy( JPATH_ROOT .'/'. DEFAULT_USER_THUMB , $thumbPath );
				JFile::copy( JPATH_ROOT .'/'. DEFAULT_USER_AVATAR , $avatarPath );

				$watermarkPath = $watermarkPath;

				list( $watermarkWidth , $watermarkHeight )	= getimagesize( $watermarkPath );

				$oldDefaultAvatar	= $multiprofile->avatar;
				$oldDefaultThumb	= $multiprofile->thumb;

				// Avatar Properties
				$avatarInfo		= getimagesize( $avatarPath );
				$avatarWidth	= $avatarInfo[ 0 ];
				$avatarHeight	= $avatarInfo[ 1 ];
				$avatarMime		= $avatarInfo[ 'mime' ];
				$avatarPosition	= $this->_getPositions( $multiprofile->watermark_location , $avatarWidth , $avatarHeight , $watermarkWidth , $watermarkHeight );
				CImageHelper::addWatermark( $avatarPath , $avatarPath , $avatarMime , $watermarkPath , $avatarPosition->x , $avatarPosition->y );
				$multiprofile->avatar	= CString::str_ireplace( '/', '/' , COMMUNITY_WATERMARKS_PATH ) . '/' . $fileName;

				// Thumbnail properties.
				$thumbInfo		= getimagesize( $thumbPath );
				$thumbWidth		= $thumbInfo[ 0 ];
				$thumbHeight	= $thumbInfo[ 1 ];
				$thumbMime		= $thumbInfo[ 'mime' ];
				$thumbPosition	= $this->_getPositions( $multiprofile->watermark_location , $thumbWidth , $thumbHeight , $watermarkWidth , $watermarkHeight );

				CImageHelper::addWatermark( $thumbPath , $thumbPath , $thumbMime , $watermarkPath , $thumbPosition->x , $thumbPosition->y );

				$multiprofile->thumb	= CString::str_ireplace( '/', '/' , COMMUNITY_WATERMARKS_PATH ) . '/' . $thumbFileName;

				// Since the default thumbnail is used by current users, we need to update their existing values.
				$multiprofile->updateUserDefaultImage( 'avatar' , $oldDefaultAvatar );
				$multiprofile->updateUserDefaultImage( 'thumb' , $oldDefaultThumb );

				$multiprofile->watermark_hash = $watermark_hash;
				$multiprofile->store();
			}

			// Since it would be very tedious to check if previous fields were enabled or disabled.
			// We delete all existing mapping and remap it again to ensure data integrity.
			if( !$isNew && empty($fields) )
			{
				$multiprofile->deleteChilds();
			}

			if( !empty( $fields ) )
			{
				$parents	= array();

				// We need to unique the parents first.
				foreach($fields as $id )
				{
					$customProfile		= JTable::getInstance( 'Profiles' , 'CommunityTable' );
					$customProfile->load( $id );

					// Need to only
					$parent				= $customProfile->getCurrentParentId();

					if( in_array( $parent , $tmpParents ) )
					{
						$parents[]	= $parent;
					}
				}
				$parents	= array_unique( $parents );

				$fields		= array_merge( $fields, $parents );

				foreach( $fields as $id )
				{
					$field				= JTable::getInstance( 'MultiProfileFields' , 'CTable' );
					$field->parent		= $multiprofile->id;
					$field->field_id	= $id;

					$field->store();
				}
			}

			if( $isNew ) {
				$message = JText::_('COM_COMMUNITY_MULTIPROFILE_CREATED_SUCCESSFULLY');
			} else {
				$message = JText::_( 'COM_COMMUNITY_MULTIPROFILE_UPDATED_SUCCESSFULLY' );
			}

			switch($task){
				case 'apply';
					$link   = 'index.php?option=com_community&view=multiprofile&layout=edit&id=' . $multiprofile->id;
					break;
				case 'save';
				default:
					$link   = 'index.php?option=com_community&view=multiprofile';
					break;
			}

			$mainframe->redirect( $link, $message, 'message' );
			return;
		}

		$document = JFactory::getDocument();
		$viewName = JRequest::getCmd( 'view' , 'community' );

		// Get the view type
		$viewType = $document->getType();

		// Get the view
		$view     = $this->getView( $viewName , $viewType );
		$view->setLayout( 'edit' );

		$model = $this->getModel( 'Profiles' );

		if( $model ) {
			$view->setModel( $model , $viewName );
		}

		$view->display();
	}

	public function apply()
	{
		$this->save();
	}

	/**
	 * Retrieve the proper x and y position depending on the user's choice of the watermark position.
	 **/
	private function _getPositions( $location , $imageWidth , $imageHeight , $watermarkWidth , $watermarkHeight )
	{
		$position	= new stdClass();

		// @rule: Get the appropriate X/Y position for the avatar
		switch( $location )
		{
			case 'top':
				$position->x	= ($imageWidth / 2) - ( $watermarkWidth / 2 );
				$position->y	= 0;
				break;
			case 'bottom':
				$position->x	= ($imageWidth / 2) - ( $watermarkWidth / 2 );
				$position->y	= $imageHeight - $watermarkHeight;
				break;
			case 'left':
				$position->x	= 0;
				$position->y	= ( $imageHeight / 2 ) - ($watermarkHeight / 2);
				break;
			case 'right':
				$position->x 	= $imageWidth - $watermarkWidth;
				$position->y	= ( $imageHeight / 2 ) - ($watermarkHeight / 2);
				break;
		}
		return $position;
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

		$view		= $this->getView( $viewName , $viewType );
		$profile	= $this->getModel( 'Profiles' );
		$view->setModel( $profile , false );

		parent::display();
	}

	public function add()
	{
		$mainframe	= JFactory::getApplication();
		$mainframe->redirect( 'index.php?option=com_community&view=multiprofile&layout=edit' );
	}

	/**
	 * Responsible for deleting single or multiple profile types.
	 **/
	public function delete()
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$data		= $jinput->post->get('cid' , '', 'NONE') ; //JRequest::getVar( 'cid' , '' , 'post' );
		$error		= array();
		$profile	= JTable::getInstance( 'MultiProfile' , 'CTable' );

		if( !is_array( $data ) )
		{
			$data[]	= $data;
		}

		if( empty($data) )
		{
			JError::raiseError( '500' , JText::_('COM_COMMUNITY_INVALID_ID') );
		}

		foreach($data as $id)
		{
			$profile->load( $id );

			if( $profile->hasUsers() )
			{
				$mainframe->redirect( 'index.php?option=com_community&view=multiprofile' , JText::sprintf('COM_COMMUNITY_MULTIPROFILE_UNABLE_TO_DELETE_MULTIPROFILE' , $profile->name ) , 'error' );
			}
			else
			{
				if( !$profile->delete() )
				{
					$error[]	= true;
				}
				else // in case something went wrong and deleted profile id assigned to certain users, set users to default profile ID: COMMUNITY_DEFAULT_PROFILE
				{
					$user = CFactory::getModel('user');
					$user->setDefProfileToUser($profile->id);
				}
			}
		}


		if( in_array( $error , true ) )
		{
			$mainframe->redirect( 'index.php?option=com_community&view=multiprofile' , JText::_('COM_COMMUNITY_MULTIPROFILE_REMOVING_ERROR') , 'error' );
		}
		else
		{
			$mainframe->redirect( 'index.php?option=com_community&view=multiprofile' , JText::_('COM_COMMUNITY_MULTIPROFILE_DELETED') ,'message' );
		}
	}
}