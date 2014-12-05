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

class CWallTrigger
{
	public function onAfterWallDelete($id)
	{
		$db= JFactory::getDBO();

		$sql = 'DELETE FROM ' . $db->quoteName('#__community_activities')
			.' WHERE ' . $db->quoteName('cid') .' = '. $db->Quote($id)
			.' AND ' . $db->quoteName('app') .' = ' . $db->Quote('groups.wall');
		$db->setQuery($sql);
		$db->query();
	}

	public function onWallDisplay( $row )
	{
		//CFactory::load( 'helpers' , 'string' );
		CError::assert( $row->comment, '', '!empty', __FILE__ , __LINE__ );

		// @rule: Only nl2br text that doesn't contain html tags
		if( !CStringHelper::isHTML( $row->comment ) )
		{
			$row->comment	= CStringHelper::nl2br( $row->comment );
		}
	}

}