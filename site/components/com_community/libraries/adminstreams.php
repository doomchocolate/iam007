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

require_once( JPATH_ROOT .'/components/com_community/libraries/core.php' );
//CFactory::load( 'libraries' , 'comment' );

class CAdminstreams implements CCommentInterface
{
	static function getActivityContentHTML($act)
	{
		// Ok, the activity could be an upload OR a wall comment. In the future, the content should
		// indicate which is which
		$html = '';

		$param = new CParameter( $act->params );
		$action = $param->get('action' , false);
		$count =  $param->get('count', false);
		$config = CFactory::getConfig();
		switch ($action)
		{
		    case CAdminstreamsAction::TOP_USERS:

			    $model		= CFactory::getModel( 'user' );
			    $members		= $model->getPopularMember( $count );
			    $html    = '';

			    //Get Template Page
			    $tmpl   =	new CTemplate();
			    $html   =	$tmpl	->set( 'members'    , $members )
						->fetch( 'activity.members.popular' );

			    return $html;
		    break;
		    case CAdminstreamsAction::TOP_PHOTOS:

			    $model		= CFactory::getModel( 'photos');
			    $photos		= $model->getPopularPhotos( $count , 0 );

			    $tmpl   =	new CTemplate();
			    $html   =	$tmpl	->set( 'photos'	, $photos )
						->fetch( 'activity.photos.popular' );

			    return $html;
		    break;
		    case CAdminstreamsAction::TOP_VIDEOS:

			    $model		= CFactory::getModel( 'videos');
			    $videos		= $model->getPopularVideos( $count );

			    $tmpl   =	new CTemplate();
			    $html   =	$tmpl	->set( 'videos'	, $videos )
						->fetch( 'activity.videos.popular' );

			    return $html;
		    break;
		}


	}

	static public function sendCommentNotification( CTableWall $wall , $message )
	{

	}
}
class CAdminstreamsAction
{
	const TOP_USERS	    = 'top_users';
	const TOP_PHOTOS    = 'top_photos';
	const TOP_VIDEOS    = 'top_videos';

}
