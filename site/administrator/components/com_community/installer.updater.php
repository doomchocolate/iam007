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

require_once JPATH_ROOT.'/administrator/components/com_community/models/configuration.php';
require_once JPATH_ROOT.'/components/com_community/libraries/parameter.php';
abstract class communityInstallerUpdate
{
	/**
	* Method to check if column exitst
	* @param $tablename
	* @param $columname
	* @return true/false
	*/

	private static function _isExistTableColumn($tablename,$columname)
	{
		$db = JFactory::getDBO();

		$query = 'SHOW FIELDS FROM '. $db->quoteName($tablename);
		$db->setQuery( $query );

		$resutl = $db->loadObjectList();

		foreach($resutl as $_result)
		{
			if($_result->Field == $columname)
			{
				return false;
			}
		}

		return true;
	}

	/**
	* Method to check if Menu is exist
	* @return true/false
	*/

	private static function _isExistMenu()
	{
		$db	= JFactory::getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__menu_types' ) . ' WHERE ';
		$query	.= $db->quoteName( 'menutype' ) . '=' . $db->Quote( 'jomsocial' );

		$db->setQuery( $query );

		return $db->loadResult() > 0;
	}

	/**
	 * Get Current Used template
	 * @return [string] [return template name]
	 */
	private static function _getCurrentTemplate()
	{
		$configuration = new CommunityModelConfiguration();
		$config = $configuration->getParams();

		return $config->get('template');
	}

	/**
	* Method Update DbVersion
	* @param $dbversion
	*/

	public static function updateDBVersion($dbversion)
	{
		$db		= JFactory::getDBO();

		$query	= 'UPDATE ' . $db->quoteName( '#__community_config' ) . 'SET ';
		$query .=  $db->quoteName( 'params' ) . ' = ' . $db->quote( $dbversion ) . ' WHERE ';
		$query .=  $db->quoteName( 'name' ) . ' = ' . $db->quote( 'dbversion' );

		$db->setQuery( $query );
		$db->Query();
	}

	/**
	 * Method to insert Db Version
	 * @param $dbversion
	 */

	public static function insertDBVersion($dbversion)
	{
		$db		=& JFactory::getDBO();

		$query	= 'INSERT INTO ' . $db->quoteName( '#__community_config' )
				. '(' . $db->quoteName( 'name' ) . ', '. $db->quoteName( 'params' ). ')'
				. 'VALUES('. $db->quote( 'dbversion' ) . ', '. $db->quote( $dbversion ). ')';

		$db->setQuery( $query );
		$db->Query();
	}

	/**
	 * Method to insert Basic Config
	 *
	 * */

	public static function insertBasicConfig()
	{
		$db		=& JFactory::getDBO();

		$query	= 'INSERT INTO ' . $db->quoteName( '#__community_config' )
				. '(' . $db->quoteName( 'name' ) . ', '. $db->quoteName( 'params' ). ')'
				. 'VALUES('. $db->quote( 'config' ) . ', "")';

		$db->setQuery( $query );
		$db->Query();
	}

	public static function update_11()
	{
		$db	= JFactory::getDBO();

		// Update Event Table
		if( self::_isExistTableColumn('#__community_events', 'allday') )
		{
			$query	= 'ALTER TABLE ' . $db->quoteName( '#__community_events' ) . ' ADD ' . $db->quoteName( 'allday' ) . ' TINYINT( 11 ) NOT NULL DEFAULT ' . $db->quote(0);
			$query .= ' , ADD ' . $db->quoteName( 'repeat' ) . ' VARCHAR( 50 ) DEFAULT NULL COMMENT ' . $db->Quote('null,daily,weekly,monthly');
			$query .= ' , ADD ' . $db->quoteName( 'repeatend' ) . ' DATE NOT NULL';
			$query .= ' , ADD ' . $db->quoteName( 'parent' ) . ' INT( 11 ) NOT NULL COMMENT ' . $db->Quote('parent for recurring event') . ' AFTER ' . $db->quoteName('id');
			$query .= ' , ADD KEY `idx_catid` (`catid`)';
			$query .= ' , ADD KEY `idx_published` (`published`)';

			$db->setQuery( $query );
			$db->query();
		}

		//Update Community Profile table
		if( self::_isExistTableColumn('#__community_profiles', 'profile_lock') )
		{
			$query	= 'ALTER TABLE '.$db->quoteName('#__community_profiles')
			. ' ADD '.$db->quoteName('profile_lock')
			. ' TINYINT (1) NULL DEFAULT '.$db->quote(0)
			. ' AFTER '.$db->quoteName('create_events');

			$db->setQuery( $query );
			$db->query();

		}

		//Update Community Groups Discussion table
		if( self::_isExistTableColumn('#__community_groups_discuss', 'params') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_groups_discuss') .' ADD '. $db->quoteName('params') .' TEXT NOT NULL ';

			$db->setQuery( $query );
			$db->query();
		}

		//Update Community Groups Bulletins Table
		if( self::_isExistTableColumn('#__community_groups_bulletins', 'params') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_groups_bulletins') .' ADD '. $db->quoteName('params') .' TEXT NOT NULL ';

			$db->setQuery( $query );
			$db->query();
		}

		//Update Community Register table
		if( self::_isExistTableColumn('#__community_register', 'ip') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_register') .' CHANGE '. $db->quoteName('ip').' '. $db->quoteName('ip').' VARCHAR( 39 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; ';

			$db->setQuery( $query );
			$db->query();
		}

		//Update Community Register Auth token table
		if( self::_isExistTableColumn('#__community_register_auth_token', 'ip') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_register_auth_token') .' CHANGE '. $db->quoteName('ip').' '. $db->quoteName('ip').' VARCHAR( 39 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; ';

			$db->setQuery( $query );
			$db->query();
		}

		// Update Menu link to mygroup
		if( self::_isExistMenu() )
		{
			$query	= 'UPDATE ' . $db->quoteName('#__menu') . ' SET ' . $db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_community&view=groups&task=mygroupupdate');
			$query .= ' WHERE ' . $db->quoteName('menutype') . ' = ' . $db->quote('jomsocial');
			$query .= ' AND  '. $db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_community&view=groups&task=mygroups');
			$query .= ' AND  '. $db->quoteName('alias') . ' = ' . $db->quote('groups');

			$db->setQuery( $query );
			$db->query();
		}
	}

	public static function update_12()
	{
		$db = JFactory::getDBO();

		if( self::_isExistTableColumn('#__community_photos', 'params') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_photos') .' ADD '. $db->quoteName('params') .' TEXT NOT NULL ';

			$db->setQuery( $query );
			$db->query();
		}

		if( self::_isExistTableColumn('#__community_photos_albums', 'params') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_photos_albums') .' ADD '. $db->quoteName('params') .' TEXT NOT NULL ';

			$db->setQuery( $query );
			$db->query();
		}

		if( self::_isExistTableColumn('#__community_videos', 'params') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_videos') .' ADD '. $db->quoteName('params') .' TEXT NOT NULL ';

			$db->setQuery( $query );
			$db->query();
		}

		$query	= 'ALTER TABLE '. $db->quoteName('#__community_register_auth_token') .' CHANGE '. $db->quoteName('ip').' '. $db->quoteName('ip').' VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; ';

		$db->setQuery( $query );
		$db->query();

		$query = 'DELETE FROM  '.$db->quoteName('#__community_activities') .' WHERE '. $db->quoteName('title') .' LIKE '.$db->quote('%{multiple}%');

		$db->setQuery( $query );
		$db->query();

		if(self::_getCurrentTemplate() == 'blackout')
		{
			$configuration = new CommunityModelConfiguration();
			$configuration->updateTemplate( 'default' );
		}

		$query = 'UPDATE ' .$db->quoteName('#__community_activities'). 'SET ' .$db->quoteName('app'). ' = ' .$db->quote('albums.featured')
					.' WHERE '.$db->quoteName('app'). ' = ' .$db->quote('photos.featured');

		$db->setQuery( $query );
		$db->query();

		if( self::_isExistTableColumn('#__community_activities', 'actors') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_activities') .' ADD '. $db->quoteName('actors') .' TEXT NOT NULL ';

			$db->setQuery( $query );
			$db->query();
		}

		// Delete old app activity string
		$query ='DELETE FROM '. $db->quoteName('#__community_activities') .' WHERE '.$db->quoteName('app').'='.$db->quote('feed').' OR '.$db->quoteName('app').'='.$db->quote('friendslocation').' OR '.$db->quoteName('app').'='.$db->quote('kunena').'
				 OR '.$db->quoteName('app').'='.$db->quote('latestphoto').' OR '.$db->quoteName('app').'='.$db->quote('myarticles').' OR '.$db->quoteName('app').'='.$db->quote('mycontacts').' OR '.$db->quoteName('app').'='.$db->quote('mygoogleads').'
				 OR '.$db->quoteName('app').'='.$db->quote('mytaggedvideos').' OR '.$db->quoteName('app').'='.$db->quote('twitter').' OR '.$db->quoteName('app').'='.$db->quote('wall');

		$db->setQuery($query);
		$db->query();

		//Delete old profile avatar string
		$query = 'DELETE FROM  '.$db->quoteName('#__community_activities') .' WHERE '. $db->quoteName('app') .' LIKE '.$db->quote('profile') .' AND '. $db->quoteName('comment_type') .' LIKE '.$db->quote('profile.avatar.upload');

		$db->setQuery( $query );
		$db->query();

		//Delete old groups updated stream
		$query = 'DELETE FROM  '.$db->quoteName('#__community_activities') .' WHERE '. $db->quoteName('app') .' LIKE '.$db->quote('groups') .' AND '. $db->quoteName('app') .' LIKE '.$db->quote('%updated group%');

		$db->setQuery( $query );
		$db->query();

	}

	public static function update_13()
	{
		$db = JFactory::getDBO();

		if( self::_isExistTableColumn('#__community_users', 'cover') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_users') .' ADD '. $db->quoteName('cover') .' TEXT NOT NULL AFTER '.$db->quoteName('thumb');

			$db->setQuery( $query );
			$db->query();
		}

		if( self::_isExistTableColumn('#__community_groups', 'cover') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_groups') .' ADD '. $db->quoteName('cover') .' TEXT NOT NULL AFTER '.$db->quoteName('thumb');

			$db->setQuery( $query );
			$db->query();
		}

		if( self::_isExistTableColumn('#__community_events', 'cover') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_events') .' ADD '. $db->quoteName('cover') .' TEXT NOT NULL AFTER '. $db->quoteName('thumb');

			$db->setQuery( $query );
			$db->query();
		}

		if( self::_isExistTableColumn('#__community_groups', 'hits') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_groups') .' ADD '. $db->quoteName('hits') .' INT NOT NULL';

			$db->setQuery( $query );
			$db->query();
		}

		if( self::_isExistTableColumn('#__community_photos_albums', 'eventid') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_photos_albums') .' ADD '. $db->quoteName('eventid') .' INT NOT NULL AFTER '. $db->quoteName('groupid');

			$db->setQuery( $query );
			$db->query();
		}

		if( self::_isExistMenu() )
		{
			$query	= 'UPDATE '.$db->quoteName('#__menu').' SET '.$db->quoteName('link').' = '.$db->quote('index.php?option=com_community&view=groups&task=mygroups')
			. ' WHERE '.$db->quoteName('menutype').' = '.$db->quote('jomsocial')
			. ' AND  '. $db->quoteName('link').' = '.$db->quote('index.php?option=com_community&view=groups&task=mygroupupdate')
			. ' AND  '. $db->quoteName('alias').' = '.$db->quote('groups');
			$db->setQuery( $query );
			$db->query();
		}

		//remove backend double toolbar
		$file       = JPATH_ROOT.'/administrator/components/com_community/toolbar.community.php';

		if(JFile::exists($file))
		{
			JFile::delete($file);
		}

		//Use default template
		$configuration = new CommunityModelConfiguration();
		$configuration->updateTemplate( 'default' );
	}

	public static function update_14()
	{
		$db = JFactory::getDBO();

		if( self::_isExistTableColumn('#__community_events', 'params') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_events') .' ADD '. $db->quoteName('params') .' TEXT NOT NULL';

			$db->setQuery( $query );
			$db->query();
		}
	}

	public static function update_15(){
		$db = JFactory::getDBO();

		if( self::_isExistTableColumn('#__community_wall', 'params') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_wall') .' ADD '. $db->quoteName('params') .' TEXT NOT NULL';

			$db->setQuery( $query );
			$db->query();
		}

		if( self::_isExistTableColumn('#__community_blocklist', 'type') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_blocklist') .' ADD '. $db->quoteName('type') .' VARCHAR(50) NOT NULL';

			$db->setQuery( $query );
			$db->query();
		}

        $query = 'SHOW INDEX FROM '.$db->quoteName('#__community_config');
        $db->setQuery( $query );
        $result = $db->loadAssoc();
        if(!isset($result['Column_name']) || empty($result['Column_name'])){
            $query = 'ALTER TABLE '.$db->quoteName('#__community_config').' ADD PRIMARY KEY ( '.$db->quoteName('name').' )' ;
            $db->setQuery( $query );
            $db->query();
        }
	}
}