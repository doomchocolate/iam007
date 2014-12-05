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

jimport( 'joomla.application.component.model' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class CommunityModelUsers extends JModelLegacy
{
	/**
	 * Configuration data
	 *
	 * @var object	JPagination object
	 **/
	var $_pagination;
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$mainframe	= JFactory::getApplication();

		// Call the parents constructor
		parent::__construct();

		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest( 'com_community.users.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( 'com_community.users.limitstart', 'limitstart', 0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 *	Set the avatar for specific application. Caller must have a database table
	 *	that is named after the appType. E.g, users should have jos_community_users
	 *
	 * @param	appType		Application type. ( users , groups )
	 * @param	path		The relative path to the avatars.
	 * @param	type		The type of Image, thumb or avatar.
	 *
	 **/
	public function setImage(  $id , $path , $type = 'thumb' )
	{
		CError::assert( $id , '' , '!empty' , __FILE__ , __LINE__ );
		CError::assert( $path , '' , '!empty' , __FILE__ , __LINE__ );

		$db			=& $this->getDBO();

		// Fix the back quotes
		$path		= CString::str_ireplace( '\\' , '/' , $path );
		$type		= JString::strtolower( $type );

		// Test if the record exists.
		$query		= 'SELECT ' . $db->quoteName( $type ) . ' FROM ' . $db->quoteName( '#__community_users' )
					. 'WHERE ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $id );

		$db->setQuery( $query );
		$oldFile	= $db->loadResult();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
	    }

	    if( !$oldFile )
	    {
	    	$query	= 'UPDATE ' . $db->quoteName( '#__community_users' ) . ' '
	    			. 'SET ' . $db->quoteName( $type ) . '=' . $db->Quote( $path ) . ' '
	    			. 'WHERE ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $id );
	    	$db->setQuery( $query );
	    	$db->query( $query );

			if($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
		    }
		}
		else
		{
	    	$query	= 'UPDATE ' . $db->quoteName( '#__community_users' ) . ' '
	    			. 'SET ' . $db->quoteName( $type ) . '=' . $db->Quote( $path ) . ' '
	    			. 'WHERE ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $id );
	    	$db->setQuery( $query );
	    	$db->query( $query );

			if($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
		    }

			// If old file is default_thumb or default, we should not remove it.
			// Need proper way to test it
			if(!Jstring::stristr( $oldFile , 'components/com_community/assets/default.jpg' ) && !Jstring::stristr( $oldFile , 'components/com_community/assets/default_thumb.jpg' ) )
			{
				// File exists, try to remove old files first.
				$oldFile	= CString::str_ireplace( '/' , '/', $oldFile );
				JFile::delete($oldFile);
			}
		}
	}

	/**
	 * Retrieves the JPagination object
	 *
	 * @return object	JPagination object
	 **/
	public function &getPagination()
	{
		return $this->_pagination;
	}

	public function getTotal()
	{
		// Load total number of rows
		if( empty($this->_total) )
		{
			$this->_total	= $this->_getListCount( $this->_buildQuery() );
		}

		return $this->_total;
	}

	public function _buildQuery()
	{
		$db			= JFactory::getDBO();
		$category	= JRequest::getInt( 'category' , 0 );
		$condition	= '';

		if( $category != 0 )
		{
			$condition	= ' WHERE a.categoryid=' . $db->Quote( $category );
		}

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__users' ) . ' '
				. 'WHERE ' . $db->quoteName( 'block' ) . '=' . $db->Quote( '0' ) . ' '
				. 'ORDER BY ' . $db->quoteName( 'name' );

		return $query;
	}

	public function getAllUsers( $useLimit = true , $useSearch = true )
	{
		if(empty($this->_data))
		{
			$db        = JFactory::getDBO();
			$mainframe = JFactory::getApplication();
			$status    = JRequest::getInt( 'status' , 2 );
			$usertype  = JRequest::getString( 'usertype' , 'all' );

            $limit			= $this->getState('limit');
            $limitstart 	= $this->getState('limitstart');
			$search			= $mainframe->getUserStateFromRequest( "com_community.users.search", 'search', '', 'string' );
			// $usertype		= $mainframe->getUserStateFromRequest( "com_community.users.usertype", 'usertype', 'joomla', 'string' );
			$profileType	= $mainframe->getUserStateFromRequest( "com_community.users.usertype", 'profiletype', '', 'int' );
                        /* by default order by registerDate */
			$ordering		= $mainframe->getUserStateFromRequest( "com_community.users.filter_order",		'filter_order',		'registerDate',	'cmd' );
			$orderDirection	= $mainframe->getUserStateFromRequest( "com_community.users.filter_order_Dir",	'filter_order_Dir',	'DESC',			'word' );

			$searchQuery	= '';
			$joinQuery		= '';
			$orderby = 'ORDER BY '. $ordering .' '. $orderDirection;

			if( !empty( $search ) && $useSearch )
			{
				$searchQuery	= 'WHERE name LIKE ' . $db->Quote( '%' . $search . '%' ) . ' '
								. 'OR username LIKE ' . $db->Quote( '%' . $search . '%' );
			}

			if( !empty( $usertype ) )
			{
				if( $usertype == 'jomsocial' )
				{
					$joinQuery		= ' INNER JOIN ' . $db->quoteName( '#__community_users' ) . ' AS b '
									. ' ON a.' . $db->quoteName('id') . ' = b.' . $db->quoteName('userid')
									. ' AND b.points > ' . $db->Quote( 0 );
				}

				if( $usertype == 'facebook' )
				{
					$joinQuery		= 'INNER JOIN ' . $db->quoteName( '#__community_connect_users' ) . ' AS b '
									. 'ON a.id=b.userid ';
				}

				if($usertype == 'joomla')
				{
					$joinQuery		= 'LEFT JOIN ' . $db->quoteName( '#__community_connect_users' ) . ' AS b '
									. 'ON a.id = b.userid ';

					if( !empty( $search) )
						$searchQuery	.= ' AND b.userid IS NULL ';
					else
						$searchQuery	.= ' WHERE b.userid IS NULL ';
				}
			}

			if( !empty( $profileType ) )
			{
				$joinQuery	.= 'INNER JOIN ' . $db->quoteName( '#__community_users' ) . ' AS c '
							. 'ON a.id = c.userid ';

				if( !empty( $search ) )
				{
					$searchQuery	.= ' AND c.profile_id=' . $db->Quote( $profileType ) . ' ';
				}
				else
				{
					$searchQuery	.= 'WHERE c.profile_id=' . $db->Quote( $profileType ) . ' ';
				}
			}

			if( $status != 2 ){
				if( !empty( $searchQuery ) )
				{
					$searchQuery	.= ' AND a.block=' . $db->Quote( $status ) . ' ';
				}
				else
				{
					$searchQuery	.= 'WHERE a.block=' . $db->Quote( $status ) . ' ';
				}
			}

			$query	= 'SELECT * FROM ' . $db->quoteName( '#__users' ) . ' AS a '
					. $joinQuery
					. $searchQuery
					. $orderby;

			if( $useLimit )
			{
	            // Appy pagination
	            if ( empty($this->_pagination))
	            {
	                jimport('joomla.html.pagination');
	                $this->_pagination = new JPagination( $this->_getListCount( $query ) , $limitstart, $limit);
	            }

				$this->_data	= $this->_getList( $query , $this->getState('limitstart'), $this->getState('limit') );
			}
			else
			{
				$db->setQuery($query);
				$this->_data	= $db->loadObjectList();
			}
		}
		return $this->_data;
	}

	public function getUsers()
	{
		if(empty($this->_data))
		{

			$query = $this->_buildQuery( );

			$this->_data	= $this->_getList( $this->_buildQuery() , $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_data;
	}

	public function getCommunityUser()
	{
		$db		= JFactory::getDBO();

		$query	= "SELECT * FROM " . $db->quoteName( '#__community_users');

		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		return $result;
	}

	public function getAllCommunityUsers()
	{
		$db		= JFactory::getDBO();

		$query	= "SELECT `userid` FROM " . $db->quoteName( '#__community_users');

		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		return $result;
	}

	/**
	 * Method to retrieve all user's id from the site.
	 */
	public function getSiteUsers( $limitstart , $limit )
	{
		$db		= JFactory::getDBO();

		$query	= 'SELECT id FROM ' . $db->quoteName( '#__users' ) . ' '
				. 'WHERE '.$db->quoteName( 'block' ) . ' = ' . $db->quote(0)
				. ' LIMIT ' . $limitstart . ',' . $limit;

		$db->setQuery( $query );

		$result	= $db->loadResult();

		return $result;
	}

	public function isLatestTable()
	{
		$fields	= $this->_getFields();

		if(!array_key_exists( 'friendcount' , $fields ) )
		{
			return false;
		}

		return true;
	}

	public function _getFields( $table = '#__community_users' )
	{
		$result	= array();
		$db		= JFactory::getDBO();

		$query	= 'SHOW FIELDS FROM ' . $db->quoteName( $table );

		$db->setQuery( $query );

		$fields	= $db->loadObjectList();

		foreach( $fields as $field )
		{
			$result[ $field->Field ]	= preg_replace( '/[(0-9)]/' , '' , $field->Type );
		}

		return $result;
	}

	/**
	 *	Return connect type of specific user
	 **/
	public function getUserConnectType( $userId )
	{
		$db		= JFactory::getDBO();

		$query	= 'SELECT `type` FROM ' . $db->quoteName( '#__community_connect_users' ) . ' '
				. 'WHERE ' . $db->quoteName( 'userid' ) . '=' . $db->quote( $userId );

		$db->setQuery( $query );

		$type	= $db->loadResult();

		if( !$type )
		{
			$type	= 'joomla';
		}

		return $type;
	}

	public function removeProfilePicture( $id , $type = 'thumb' )
	{
		$db		= $this->getDBO();
		$type	= JString::strtolower( $type );

		// Test if the record exists.
		$query		= 'SELECT ' . $db->quoteName( $type ) . ' FROM ' . $db->quoteName( '#__community_users' )
					. 'WHERE ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $id );

		$db->setQuery( $query );
		$oldFile	= $db->loadResult();

		$query	=   'UPDATE ' . $db->quoteName( '#__community_users' ) . ' '
			    . 'SET ' . $db->quoteName( $type ) . '=' . $db->Quote( '' ) . ' '
			    . 'WHERE ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $id );

		$db->setQuery( $query );
		$db->query( $query );

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		// If old file is default_thumb or default, we should not remove it.
		// Need proper way to test it
		if(!JString::stristr( $oldFile , 'components/com_community/assets/default.jpg' ) && !JString::stristr( $oldFile , 'components/com_community/assets/default_thumb.jpg' ) && !JString::stristr( $oldFile , 'avatar_' ) )
		{
			// File exists, try to remove old files first.
			$oldFile	= CString::str_ireplace( '/' , '/' , $oldFile );

			if( JFile::exists( $oldFile ) )
			{
				JFile::delete($oldFile);
			}
		}

		return true;
	}

	public function getMembersCount($type = 'all')
	{
		$db = $this->getDBO();

		switch($type) {
			case 'jomsocial':
				$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__users' ) . ' as a'
						. ' INNER JOIN ' . $db->quoteName( '#__community_users' ) . ' AS b '
						. ' ON a.' . $db->quoteName('id') . ' = b.' . $db->quoteName('userid')
						. ' AND ' . $db->quoteName( 'block' ) . '=' . $db->Quote( 0 )
						. ' AND b.points > ' . $db->Quote( 0 );
				break;
			case 'all':
			default:
				$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__users' )
						. ' WHERE ' . $db->quoteName( 'block' ) . '=' . $db->Quote( 0 );
		}

		$db->setQuery( $query );

		$result	= $db->loadResult();

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		return $result;
	}

	public function getGenderInfo()
	{
		$db = $this->getDBO();

		$query = 'SELECT SUM(CASE '.$db->quoteName('value').' WHEN '.$db->Quote('Male').' THEN 1 ELSE 0 END) as Male, '.
					'SUM(CASE '.$db->quoteName('value').' WHEN '.$db->Quote('Female').' THEN 1 ELSE 0 END) as Female '.
					'FROM '.$db->quoteName('#__community_fields_values');

		$db->setQuery($query);

		$result = $db->loadObjectList();

		$data = new stdClass();
		foreach($result as $_result)
		{
			$data->Male = (empty($_result->Male)) ? 0 : $_result->Male;
			$data->Female = (empty($_result->Female)) ? 0 : $_result->Female;
		}

		return $data;
	}

	public function getLatestMembers()
	{
		$db = $this->getDBO();

		$query = 'SELECT '.$db->quoteName('id').' FROM '. $db->quoteName('#__users')
				.' ORDER BY '.$db->quoteName('id').' DESC'
				.' LIMIT 0,10';

		$db->setQuery($query);

		$result = $db->loadObjectList();

		$userList = array();

		foreach($result as $_result)
		{
			$user = CFactory::getUser($_result->id);

			$user->memberstatus = 'approved';

			if($user->lastvisitDate == '0000-00-00 00:00:00' && $user->isBlocked())
			{
				$user->memberstatus = 'pending';
			}
			elseif($user->isBlocked())
			{
				$user->memberstatus = 'blocked';
			}

			$userList[] = $user;
		}


		return $userList;
	}

	public function getUserCountry()
	{
		$db = $this->getDBO();

		$sql = 'SELECT '.$db->quoteName('id').' FROM '.$db->quoteName('#__community_fields')
				.' WHERE '.$db->quoteName('type').'='.$db->Quote('country')
				.' AND '.$db->quoteName('visible').'='.$db->Quote('1');

		$db->setQuery($sql);

		$countryId = $db->loadResult();
		if(!empty($countryId))
		{
			$query = 'SELECT TRIM('.$db->quoteName('value').') as '.$db->quoteName('country').', COUNT('.$db->quoteName('value').') as '.$db->quoteName('count').' FROM '.$db->quoteName('#__community_fields_values')
					.' WHERE '. $db->quoteName('field_id').'='.$db->quote($countryId)
					.' AND '.$db->quoteName('value').' != "" '
					.' GROUP BY '.$db->quoteName('country')
					.' ORDER BY '.$db->quoteName('count').' DESC'
					.' LIMIT 0,5';

			$db->setQuery($query);

			return $db->loadObjectList();
		}

		return false;
	}

	public function getUserCity()
	{
		$db = $this->getDBO();

		$sql = 'SELECT '.$db->quoteName('id').' FROM '.$db->quoteName('#__community_fields')
				.' WHERE '.$db->quoteName('fieldcode').'='.$db->Quote('FIELD_CITY');

		$db->setQuery($sql);

		$id = $db->loadResult();

		if(!empty($id))
		{
			$query = 'SELECT '.$db->quoteName('value').' as '.$db->quoteName('city').', COUNT('.$db->quoteName('value').') as '.$db->quoteName('count').' FROM '.$db->quoteName('#__community_fields_values')
					.' WHERE '. $db->quoteName('field_id').'='.$db->quote($id)
					.' GROUP BY '.$db->quoteName('value')
					.' ORDER BY '.$db->quoteName('count').' DESC'
					.' LIMIT 0,5';

			$db->setQuery($query);

			return $db->loadObjectList();
		}

		return false;
	}

	public function getAllUserId()
	{
		$db = $this->getDBO();

		$query = 'SELECT '.$db->quoteName('id').' FROM '.$db->quoteName('#__users');

		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}

	public function getUserGenderList()
	{
		$db = $this->getDBO();

		$query = 'SELECT '.$db->quoteName('id').' FROM '.$db->quoteName('#__community_fields')
				.' WHERE '.$db->quoteName('fieldcode').' = '.$db->quote('FIELD_GENDER');

		$db->setQuery($query);

		$result = $db->loadResult();

		$sql = 'SELECT * FROM'.$db->quoteName('#__community_fields_values')
				.' WHERE '.$db->quoteName('field_id').' = '.$db->Quote($result);

		$db->setQuery($sql);

		return $db->loadObjectList();
	}

	public function getUserBirthDateList()
	{
		$db = $this->getDBO();

		$query = 'SELECT '.$db->quoteName('id').' FROM '.$db->quoteName('#__community_fields')
				.' WHERE '.$db->quoteName('fieldcode').' IN( '.$db->quote('FIELD_BIRTHDATE').','. $db->quote('FIELD_BIRTHDAY').' )';

		$db->setQuery($query);

		$result = $db->loadResult();

		$sql = 'SELECT * FROM'.$db->quoteName('#__community_fields_values')
				.' WHERE '.$db->quoteName('field_id').' = '.$db->Quote($result);

		$db->setQuery($sql);

		return $db->loadObjectList();
	}

	public function getPendingMember()
	{
		$db		= $this->getDBO();

		$query	= 'SELECT a.' . $db->quoteName( 'id' ) . ' FROM ' . $db->quoteName( '#__users' ) . ' AS ' . $db->quoteName('a')
				. ' INNER JOIN ' . $db->quoteName( '#__community_users' ) . ' AS b '
				. ' ON a.' . $db->quoteName('id') . ' = b.' . $db->quoteName('userid')
				. ' WHERE a.' . $db->quoteName( 'block' ) . '=' . $db->Quote( 1 )
				. ' AND b.points > ' . $db->Quote( 0 );

		$db->setQuery( $query );

		$result	= $db->loadRowList();

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		return count($result);
	}
}