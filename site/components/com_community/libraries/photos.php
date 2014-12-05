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


define('C_ASPECT_LANDSCAPE_RATIO', 1.3125);

class CPhotos
{

	const ASPECT_PORTRAIT 	= 1;
	const ASPECT_LANDSCAPE 	= 2;
	const ASPECT_SQUARE		= 0;

	const ASPECT_LANDSCAPE_RATIO 	= 1.31;
	const ASPECT_PORTRAIT_RATIO 	= 0.76;
	const ASPECT_SQUARE_RATIO 		= 1.00;

	const ACTIVITY_SUMMARY_ITEM_COUNT = 5;


	/**
	 * Return HTML for acitivity stream
	 * @param  JTableActiities $act activity object
	 * @return string      html formatted output
	 */
	static public function getActivityContentHTML($act)
	{
		// Ok, the activity could be an upload OR a wall comment. In the future, the content should
		// indicate which is which

		$html 	 = '';
		//$param 	 = new CParameter( $act->params );
		$action  = $act->params->get('action' , false);
		$photoid = $act->params->get('photoid' , 0);
		$url	 = $act->params->get( 'url' , false );

		if( $action == 'wall' )
		{
			// unfortunately, wall post can also have 'photo' as its $act->apps. If the photo id is availble
			// for (newer activity stream, inside the param), we will show the photo snippet as well. Otherwise
			// just print out the wall content

			// Version 1.6 onwards, $params will contain photoid information
			// older version would have #photoid in the $title, since we link it to the photo

			$photoid = $param->get('photoid', false);

			if( $photoid )
			{
				$photo = JTable::getInstance( 'Photo' , 'CTable' );
				$photo->load( $act->cid );
				$helper	= new CAlbumsHelper( $photo->albumid );

				if( $helper->showActivity() )
				{
					$tmpl	= new CTemplate();
					return $tmpl->set( 'url' , $url )
								->set( 'photo'	, $photo )
								->set( 'param'	, $param )
								->set( 'act'	, $act )
								->fetch( 'activity.photos.wall' );
				}
			}
			return '';
		}
		elseif ( $action == 'upload' && $photoid > 0)
		{

			$photoModel = CFactory::getModel('photos');
			// Album object should have been created in the activities library
			$album = $act->album;
			$albumsHelper	= new CAlbumsHelper( $album );

			if( $albumsHelper->isPublic() )
			{
				// If content has link to image, we could assume it is "upload photo" action
				// since old content add this automatically.
				// The $act->cid will be the album id, Retrive the recent photos uploaded

				// If $act->activities has data, that means this is an aggregated content
				// display some of them
				$db = JFactory::getDBO();

				// If count is more than 1, get the last few photos, otherwise
				// the photo might have a custom message along with it. Show that single photo
				$count = $act->params->get('count', 1);
				$photosId = $act->params->get('photosId',null);
				$batchCount = $act->params->get('batchcount', $count);
				if(is_null($photosId)){

					if($count == 1) {
						$album->id = (empty($album->id)) ? 0 : $album->id;
						$sql = "SELECT * FROM #__community_photos WHERE `albumid`=".$album->id
							  ." AND `id`=" . $photoid
							  ." AND `status` != 'temp'";
						$db->setQuery($sql);
						$result = $db->loadObjectList();

					} else {
						$album->id = (empty($album->id)) ? 0 : $album->id;
						$sql = "SELECT * FROM #__community_photos WHERE `albumid`=".$album->id
							  ." AND `status` != 'temp'"
							  ." ORDER BY `id` DESC LIMIT 0, 5";
						$db->setQuery($sql);
						$result = $db->loadObjectList();
					}
				} else {
					$result = explode(',',$photosId);;
				}

				$photos = array();

                if (is_array($result) && count($result) > 0) {
                	foreach($result as $row)
                    {
						$photo	= JTable::getInstance( 'Photo' , 'CTable' );
						if(is_null($photosId)){
							$photo->bind( $row );
						} else {
							$photo->load($row);
						}

						if($photo->status != 'delete'){
							$photos[] = $photo;
						}

						if($photo->status == 'delete'){
							$batchCount --;
						}
                    }
                    foreach($photos as $key=>$data)
                    {
						if($data->id == $photoid)
						{
							$tmpArray		= $photos[0];
							$photos[0]		= $photos[$key];
							$photos[$key]	= $tmpArray;
						}
                    }

                    if(empty($act->activities))
                    {
                            $acts[] = $act;
                    }
                    else
                    {
                            $acts	= $act->activities;
                    }

                    $tmpl = new CTemplate();

                    if ( $batchCount == 0 ) $batchCount = 1;
                    return $tmpl->set( 'album'	, $album )
                        ->set( 'acts', $acts )
                        ->set( 'photos', $photos)
                        ->set( 'count',	$count)
                        ->set( 'batchCount', $batchCount)
                        ->set( 'stream', $act->params->get('stream' , false))
                        ->fetch( 'activity.photos.upload' );
                }

			}
		}

		return $html;
	}

	/**
	 * Return the given photo aspect ratio
	 */
	static public function getPhotoAspectRatio($srcPath)
	{
		/*
		$size = CImageHelper::getSize($srcPath);
		$ratio = ($size->width / $size->height);

		if( $ratio >  CPhotos::ASPECT_LANDSCAPE_RATIO)
			return CPhotos::ASPECT_LANDSCAPE;

		if( $ratio <  CPhotos::ASPECT_PORTRAIT_RATIO)
			return CPhotos::ASPECT_PORTRAIT;
		*/
		// Only allow square thumbnails for now
		return CPhotos::ASPECT_SQUARE;

	}

	/**
	 * Generate photo thumbnail
	 */
	static public function generateThumbnail($srcPath, $destPath, $destType)
	{
		$aspect = CPhotos::getPhotoAspectRatio($srcPath);
		list( $currentWidth , $currentHeight )	= getimagesize( $srcPath );

		$origWidth 	= $currentWidth;
		$origHeight = $currentHeight;
		$destWidth  = COMMUNITY_PHOTO_THUMBNAIL_SIZE;
		$destHeight = COMMUNITY_PHOTO_THUMBNAIL_SIZE;
		$sourceX = 0;
		$sourceY = 0;

		switch($aspect){
			/*
			case CPhotos::ASPECT_PORTRAIT:
				$currentHeight	= $currentWidth / CPhotos::ASPECT_PORTRAIT_RATIO;

				$sourceY		= intval( ( $origHeight - $currentHeight ) / 2 );
				$sourceX		= 0;

				$destHeight = 84;
				break;

			case CPhotos::ASPECT_LANDSCAPE:
				$currentWidth		= $currentHeight * CPhotos::ASPECT_LANDSCAPE_RATIO;
				$sourceX			= intval( ( $origWidth - $currentWidth ) / 2 );
				$sourceY 			= 0;

				$destWidth = 84;
				break;
			*/
			default:

		}

		//CImageHelper::resize( $srcPath , $destPath , $destType , $destWidth , $destHeight , $sourceX , $sourceY , $currentWidth , $currentHeight);
		CImageHelper::createThumb( $srcPath , $destPath , $destType , $destWidth , $destHeight );

	}

	/**
	 * Given the original source path
	 */
	public function getStorePath($srcPath)
	{
	}

	public function saveAvatarFromURL($url)
	{
		$tmpPath    = JPATH_ROOT .'/images';
		$my	    = CFactory::getUser();

		// Need to extract the non-https version since it will cause
		// certificate issue
		$avatarUrl  =	str_replace('https://', 'http://', $url);

		$source	    =	CRemoteHelper::getContent($url, true);
		JFile::write( $tmpPath , $source );

		// @todo: configurable width?
		$imageMaxWidth	= 160;

		// Get a hash for the file name.
		$fileName		= JApplication::getHash( $my->getDisplayName() . time() );
		$hashFileName	= JString::substr( $fileName , 0 , 24 );

		$extension  = JString::substr( $url , JString::strrpos( $url , '.' ) );

		$type	= 'image/jpg';

		if( $extension == '.png' )
		{
			$type	= 'image/png';
		}

		if( $extension == '.gif' )
		{
			$type	= 'image/gif';
		}

		//@todo: configurable path for avatar storage?
		$config		    = CFactory::getConfig();
		$storage	    = JPATH_ROOT .'/'. $config->getString('imagefolder') .'/avatar';
		$storageImage	    = $storage .'/'. $hashFileName . $extension;
		$storageThumbnail   = $storage .'/thumb_' . $hashFileName . $extension;
		$image		    = $config->getString('imagefolder') . '/avatar/' . $hashFileName . $extension;
		$thumbnail	    = $config->getString('imagefolder') . '/avatar/' . 'thumb_' . $hashFileName . $extension;

		$userModel	    = CFactory::getModel( 'user' );

		// Only resize when the width exceeds the max.
		CImageHelper::resizeProportional( $tmpPath , $storageImage , $type , $imageMaxWidth );
		CImageHelper::createThumb( $tmpPath , $storageThumbnail , $type );

		$removeOldImage = false;

		$userModel->setImage( $my->id , $image , 'avatar' , $removeOldImage );
		$userModel->setImage( $my->id , $thumbnail , 'thumb' , $removeOldImage );

		// Update the user object so that the profile picture gets updated.
		$my->set( '_avatar' , $image );
		$my->set( '_thumb'  , $thumbnail );

		return true;
	}

}