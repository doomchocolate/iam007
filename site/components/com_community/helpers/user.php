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

class CUserHelper
{
	static public function getUserId( $username )
	{
		$displayConfig = CFactory::getConfig()->get('displayname');

		$db		= JFactory::getDBO();
		$query	= 'SELECT ' . $db->quoteName( 'id' ) . ' '
				. 'FROM ' . $db->quoteName( '#__users' ) . ' '
				. 'WHERE ' . $db->quoteName( $displayConfig ) . '=' . $db->Quote( $username );

		$db->setQuery( $query );

		$id		= $db->loadResult();

		return $id;
	}

	static function getThumb( $userId , $imageClass = '' , $anchorClass = '' )
	{
		//CFactory::load( 'helpers' , 'string' );
		$user	= CFactory::getUser( $userId );

		$imageClass		= (!empty( $imageClass ) ) ? ' class="' . $imageClass . '"' : '';
		$anchorClass	= ( !empty( $anchorClass ) ) ? ' class="' . $anchorClass . '"' : '';

		$data	= '<a href="' . CRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id ) . '"' . $anchorClass . '>';
		$data	.= '<img src="'.$user->getThumbAvatar().'" alt="' . CStringHelper::escape( $user->getDisplayName() ) . '"' . $imageClass . ' />';
		$data	.= '</a>';

		return $data;
	}

	/**
	 * Get the html code to be added to the page
	 *
	 * return	$html	String
	 */
	static public function getBlockUserHTML( $userId, $isBlocked )
	{
		$my    = CFactory::getUser();
		$html = '';

		if(!empty($my->id)) {

		    $tmpl  = new Ctemplate();

		    $tmpl->set( 'userId'   , $userId );
		    $tmpl->set( 'isBlocked', $isBlocked);
		    $html = $tmpl->fetch( 'block.user' );

	  	}

		return $html;
	}

	static public function isUseFirstLastName()
	{
		$isUseFirstLastName	= false;

		// Firstname, Lastname for base on field code FIELD_GIVENNAME, FIELD_FAMILYNAME
		$modelProfile	= CFactory::getModel('profile');

		$firstName		= $modelProfile->getFieldId('FIELD_GIVENNAME');
		$lastName		= $modelProfile->getFieldId('FIELD_FAMILYNAME');
		$isUseFirstLastName	= ($firstName && $lastName);

		if ($isUseFirstLastName)
		{
			$table		= JTable::getInstance('ProfileField', 'CTable');
			$table->load($firstName);
			$isFirstNamePublished	= $table->published;
			$table->load($lastName);
			$isLastNamePublished	= $table->published;
			$isUseFirstLastName		= ($isFirstNamePublished && $isLastNamePublished);

			// we don't use this html because the generated class name doesn't match in this case
			//$firstNameHTML	= CProfile::getFieldHTML($firstName);
			//$lastNameHTML	= CProfile::getFieldHTML($lastName);
		}

		return $isUseFirstLastName;
	}

	/**
	 * Add default items for status box
	 */
	static function addDefaultStatusCreator(&$status)
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$my 		= CFactory::getUser();
		$userid		= $jinput->get('userid', $my->id, 'INT'); //JRequest::getVar('userid', $my->id);
		$user		= CFactory::getUser($userid);
		$config 	= CFactory::getConfig();
		$template	= new CTemplate();

		$isMine = COwnerHelper::isMine($my->id, $user->id);

		/* Message creator */
		$creator        = new CUserStatusCreator('message');
		$creator->title = JText::_('COM_COMMUNITY_STATUS');
		$creator->html  = $template->fetch('status.message');
		$status->addCreator($creator);

		if($isMine){
		if( $config->get( 'enablephotos') )
		{
			/* Photo creator */
			$creator        = new CUserStatusCreator('photo');
			$creator->title = JText::_('COM_COMMUNITY_SINGULAR_PHOTO');
			$creator->html  = $template->fetch('status.photo');

			$status->addCreator($creator);
		}

		if( $config->get( 'enablevideos') )
		{
			/* Video creator */
			$creator        = new CUserStatusCreator('video');
			$creator->title = JText::_('COM_COMMUNITY_SINGULAR_VIDEO');
			$creator->html  = $template->fetch('status.video');

			$status->addCreator($creator);
		}

		if( $config->get( 'enableevents') && ($config->get('createevents') || COwnerHelper::isCommunityAdmin() )  )
		{
			/* Event creator */
			//CFactory::load( 'helpers' , 'event' );
			$dateSelection = CEventHelper::getDateSelection();

			$model		= CFactory::getModel( 'events' );
			$categories	= $model->getCategories();

			// Load category tree

			$cTree	= CCategoryHelper::getCategories($categories);
			$lists['categoryid']	=   CCategoryHelper::getSelectList( 'events', $cTree );

			$template->set( 'startDate'       , $dateSelection->startDate );
			$template->set( 'endDate'         , $dateSelection->endDate );
			$template->set( 'startHourSelect' , $dateSelection->startHour );
			$template->set( 'endHourSelect'   , $dateSelection->endHour );
			$template->set( 'startMinSelect'  , $dateSelection->startMin );
			$template->set( 'repeatEnd'       , $dateSelection->endDate );
			$template->set( 'enableRepeat'    , $my->authorise('community.view', 'events.repeat'));
			$template->set( 'endMinSelect'    , $dateSelection->endMin );
			$template->set( 'startAmPmSelect' , $dateSelection->startAmPm );
			$template->set( 'endAmPmSelect'   , $dateSelection->endAmPm );
			$template->set( 'lists'           , $lists );

			$creator  = new CUserStatusCreator('event');
			$creator->title = JText::_('COM_COMMUNITY_SINGULAR_EVENT');
			$creator->html  = $template->fetch('status.event');

			$status->addCreator($creator);
		}
		}
	}

    /**
     * @param $message
     * @param $actor
     * @param $activity
     * Check message string if it has tagging properties and set a notification to the tagged users if it does
     */
    static function parseTaggedUserNotification($message, $actor, $activity){

        $pattern	= '/@\[\[(\d+):([a-z]+):([^\]]+)\]\]/';
        preg_match_all( $pattern , $message , $matches );

        if( isset($matches[1]) && count($matches[1]) > 0 ){
            //lets count total recipients and blast notifications
            $taggedIds = array();

            foreach( $matches[1] as $uid ){
                $taggedIds[] = (CFactory::getUser($uid)->get('id') != 0) ? $uid : null;
            }

            //set parameter to be replaced in the template
            $url = CRoute::emailLink('index.php?option=com_community&view=profile&userid=' . $actor->id . '&actid=' . $activity->id.'#activity-stream-container');
            $params = new CParameter();
            $params->set('url', $url);
            $params->set('content', $message);
            $params->set('post', '<a href="' . $url . '">' . JText::_('COM_COMMUNITY_SINGULAR_POST') . '</a>');

            //add to notifications
            CNotificationLibrary::add('users_tagged', $actor->id, $taggedIds, JText::sprintf('COM_COMMUNITY_PROFILE_USER_TAGGED_EMAIL_SUBJECT'), JText::sprintf('COM_COMMUNITY_PROFILE_USER_TAGGED_COMMENT_EMAIL_CONTENT', $url), '', $params, true, '', JText::_('COM_COMMUNITY_NOTIFICATION_USER_TAGGED'));
        }
    }
}