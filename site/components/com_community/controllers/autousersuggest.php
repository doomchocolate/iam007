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

class CommunityAutoUserSuggestController extends CommunityBaseController
{
	public function ajaxAutoUserSuggest()
	{
		$config			= CFactory::getConfig();
		$displayName	= $config->get('displayname');
		$html 			= '';

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$searchModel = CFactory::getModel( 'Search' );
		$searchName = $jinput->get('q', '', 'STRING');

		$suggestions = $searchModel->getAutoUserSuggest($searchName, $displayName);

		if(!empty($suggestions))
		{
			$names="";
			foreach( $suggestions as $row ){
				$user 	= CFactory::getUser( $row->id );
				$avatar = $user->getAvatar();
				$names .= $row->username."|".$row->id."|".$avatar."|\n";
			}

			echo $names;
		}
		exit ();
	}
}
