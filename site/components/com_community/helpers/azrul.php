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

function getJomSocialPoweredByLink()
{
   	$powerBy = '';
	if (!COMMUNITY_PRO_VERSION) {
		$powerBy = '<div style="text-align:center;font-size:85%"><a title="JomSocial, Social Networking for Joomla!" href="http://www.jomsocial.com">Powered by JomSocial</a></div>';
	}

	return $powerBy;
}

function checkFolderExist( $folderLocation )
{
	if( JFolder::exists( $folderLocation ) )
	{
		return true;
	}

	return false;
}