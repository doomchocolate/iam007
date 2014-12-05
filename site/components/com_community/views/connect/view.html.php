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
jimport ( 'joomla.application.component.view' );

class CommunityViewConnect extends CommunityView
{
	public function receiver()
	{
		$tmpl	=   new CTemplate();
		echo $tmpl->fetch( 'connect.receiver' );
	}

	public function update()
	{
		$config		= CFactory::getConfig();
		//CFactory::load( 'libraries' , 'facebook' );
		jimport('joomla.user.helper');

		// Once they reach here, we assume that they are already logged into facebook.
		// Since CFacebook library handles the security we don't need to worry about any intercepts here.
		$facebook		= new CFacebook();
		$connectModel	= CFactory::getModel( 'Connect' );
		$connectTable	= JTable::getInstance( 'Connect' , 'CTable' );
		$mainframe		= JFactory::getApplication();
		$config			= CFactory::getConfig();

		$connectId		= $facebook->getUserId();
		$connectTable->load( $connectId );

		//CFactory::load( 'libraries' , 'facebook' );
		$facebook   =	new CFacebook();
		$fields	    =	array( 'first_name' , 'last_name' , 'birthday' , 'current_location' , 'status' , 'pic' , 'sex' , 'name' , 'pic_square' , 'profile_url' , 'pic_big' , 'current_location');
		$user	    =	$facebook->getUserInfo( $fields );

		$tmpl	=   new CTemplate();
		echo $tmpl  ->set( 'user'   , $user )
			    ->fetch( 'facebook.update' );
	}

	public function inviteFrame()
	{
		//CFactory::load( 'libraries' , 'facebook' );
		$facebook	= new CFacebook();
		$config		= CFactory::getConfig();

		$tmpl	=   new CTemplate();
		echo $tmpl  ->set( 'facebook'	, $facebook )
			    ->set( 'config' 	, $config )
			    ->set( 'sitename' 	, $config->get('sitename') )
			    ->fetch( 'facebook.inviteframe' );
	}

	public function ajaxInvite()
	{
		//CFactory::load( 'libraries' , 'facebook' );
		$facebook	= new CFacebook();
		$config		= CFactory::getConfig();

		$tmpl	=   new CTemplate();
		echo $tmpl  ->set( 'facebook' 	, $facebook )
			    ->set( 'config' 	, $config )
			    ->set( 'sitename' 	, $config->get('sitename') )
			    ->fetch( 'facebook.inviteframe' );
	}
}
