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
jimport( 'joomla.application.component.view'); 

require_once( COMMUNITY_COM_PATH .'/helpers/string.php' );

class CommunityViewSearch extends CommunityView
{

	public function display($data = null)
	{
		$mainframe  = JFactory::getApplication();
		$document 	= JFactory::getDocument();
		
		$model      = CFactory::getModel( 'search' );
		$members    = $model->getPeople();

		// Prepare feeds		
// 		$document->setTitle($title);
       	
		foreach($members as $member)
		{   
			$user   = CFactory::getUser($member->id);
			$friendCount = JText::sprintf( (CStringHelper::isPlural($user->getFriendCount())) ? 'COM_COMMUNITY_FRIENDS_COUNT_MANY' : 'COM_COMMUNITY_FRIENDS_COUNT', $user->getFriendCount());

			$item = new JFeedItem();
			$item->title 		= $user->getDisplayName();
			$item->link 		= CRoute::_('index.php?option=com_community&view=profile&userid='.$user->id);  
			$item->description 	= '<img src="' . $user->getThumbAvatar() . '" alt="" />&nbsp;' . $friendCount; 
			$item->date         = '';
			
			$item->description = CString::str_ireplace('_QQQ_', '"', $item->description);
			// Make sure url is absolute
            $item->description  = CString::str_ireplace('href="/', 'href="'. JURI::base(), $item->description);
      
            $document->addItem( $item );
		}

	}
	
	public function browse(){
		return $this->display();
	}
}
