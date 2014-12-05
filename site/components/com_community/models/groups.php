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

require_once ( JPATH_ROOT .'/components/com_community/models/models.php');

class CommunityModelGroups extends JCCModel
implements CLimitsInterface, CNotificationsInterface
{
	/**
	 * Configuration data
	 *
	 * @var object	JPagination object
	 **/
	var $_pagination	= '';

	/**
	 * Configuration data
	 *
	 * @var object	JPagination object
	 **/
	var $total			= '';

	/**
	 * member count data
	 *
	 * @var int
	 **/
	var $membersCount	= array();

	/**
	 * Constructor
	 */
	public function CommunityModelGroups()
	{
		parent::JCCModel();

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		// Get pagination request variables
 	 	$limit		= ($mainframe->getCfg('list_limit') == 0) ? 5 : $mainframe->getCfg('list_limit');
	    $limitstart = $jinput->request->get('limitstart', 0); //JRequest::getVar('limitstart', 0, 'REQUEST');

	    if(empty($limitstart))
 	 	{
 	 		$limitstart = $jinput->get('start', 0, 'INT');
 	 	}

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	public function getPagination()
	{
		return $this->_pagination;
	}

	/**
	 * Deprecated since 1.8, use $groupd->updateStats()->store();
	 */
	public function substractMembersCount( $groupId )
	{
		$this->addWallCount($groupId);
	}

	/**
	 * Deprecated since 1.8, use $groupd->updateStats()->store();
	 */
	public function addDiscussCount( $groupId )
	{
		$this->addWallCount($groupId);
	}

	/**
	 * Deprecated since 1.8, use $groupd->updateStats()->store();
	 */
	public function substractDiscussCount( $groupId )
	{
		$this->addWallCount($groupId);
	}

	/**
	 * Retrieves the most active group throughout the site.
	 * @param   none
	 *
	 * @return  CTableGroup The most active group table object.
	 **/
	public function getMostActiveGroup()
	{
		$db		= $this->getDBO();

		$query	= 'SELECT '.$db->quoteName('cid').' FROM '.$db->quoteName('#__community_activities')
				. ' WHERE '.$db->quoteName('app').' LIKE ' . $db->Quote( 'groups%' ).''
				. ' GROUP BY '.$db->quoteName('cid')
				. ' ORDER BY COUNT(1) DESC '
				. ' LIMIT 1';
		$db->setQuery( $query );

		$id		= $db->loadResult();

		$group	= JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $id );

		// return a null object if there is no group yet
		if( $id )
			return $group;
		else
			return null;
	}

	public function getGroupInvites( $userId , $sorting = null )
	{
		$db			= $this->getDBO();
		$extraSQL	= ' AND a.userid=' . $db->Quote($userId);
		$orderBy	= '';
		$limit			= $this->getState('limit');
		$limitstart 	= $this->getState('limitstart');
		$total			= 0;


		switch($sorting)
		{

			case 'mostmembers':
				// Get the groups that this user is assigned to
				$query		= 'SELECT a.'.$db->quoteName('groupid').' FROM ' . $db->quoteName('#__community_groups_invite') . ' AS a '
							. ' LEFT JOIN ' . $db->quoteName('#__community_groups_members') . ' AS b '
							. ' ON a.'.$db->quoteName('groupid').'=b.'.$db->quoteName('groupid')
							. ' WHERE b.'.$db->quoteName('approved').'=' . $db->Quote( '1' )
							. $extraSQL;

				$db->setQuery( $query );
				$groupsid		= $db->loadColumn();

				if($db->getErrorNum())
				{
					JError::raiseError( 500, $db->stderr());
				}

				if( $groupsid )
				{
					$groupsid		= implode( ',' , $groupsid );

					$query			= 'SELECT a.* '
									. ' FROM ' . $db->quoteName('#__community_groups_invite') . ' AS a '
									. ' INNER JOIN '.$db->quoteName('#__community_groups').' AS b '
									. ' ON a.'.$db->quoteName('groupid').'=b.'.$db->quoteName('id')
									. ' WHERE a.'.$db->quoteName('groupid').' IN (' . $groupsid . ') '
									. ' ORDER BY b.'.$db->quoteName('membercount').' DESC '
									. ' LIMIT ' . $limitstart . ',' . $limit;
				}
				break;
			case 'mostdiscussed':
				if( empty($orderBy) )
					$orderBy	= ' ORDER BY b.'.$db->quoteName('discusscount').' DESC ';
			case 'mostwall':
				if( empty($orderBy) )
					$orderBy	= ' ORDER BY b.'.$db->quoteName('wallcount').' DESC ';
			case 'alphabetical':
				if( empty($orderBy) )
					$orderBy	= 'ORDER BY b.'.$db->quoteName('name').' ASC ';
			case 'mostactive':
				//@todo: Add sql queries for most active group

			default:
				if( empty($orderBy) )
					$orderBy	= ' ORDER BY b.'.$db->quoteName('created').' DESC ';

				$query	= 'SELECT distinct a.* FROM '
						. $db->quoteName('#__community_groups_invite') . ' AS a '
						. ' INNER JOIN ' . $db->quoteName( '#__community_groups' ) . ' AS b ON a.'.$db->quoteName('groupid').'=b.'.$db->quoteName('id')
						. ' INNER JOIN ' . $db->quoteName('#__community_groups_members') . ' AS c ON a.'.$db->quoteName('groupid').'=c.'.$db->quoteName('groupid')
						. ' AND c.'.$db->quoteName('approved').'=' . $db->Quote( '1' )
						. ' AND b.'.$db->quoteName('published').'=' . $db->Quote( '1' ) . ' '
						. $extraSQL
						. $orderBy
						. 'LIMIT ' . $limitstart . ',' . $limit;
				break;
		}
		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		$query	= 'SELECT COUNT(distinct b.'.$db->quoteName('id').') FROM ' . $db->quoteName('#__community_groups_invite') . ' AS a '
				. ' INNER JOIN ' . $db->quoteName( '#__community_groups' ) . ' AS b '
				. ' ON a.'.$db->quoteName('groupid').'=b.'.$db->quoteName('id')
				. ' INNER JOIN ' . $db->quoteName('#__community_groups_members') . ' AS c '
				. ' ON a.'.$db->quoteName('groupid').'=c.'.$db->quoteName('groupid')
				. ' WHERE b.'.$db->quoteName('published').'=' . $db->Quote( '1' )
				. ' AND c.'.$db->quoteName('approved').'=' . $db->Quote( '1' )
				. $extraSQL;

		$db->setQuery( $query );
		$total	= $db->loadResult();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		if( empty($this->_pagination) )
		{
			jimport('joomla.html.pagination');

			$this->_pagination	= new JPagination( $total , $limitstart , $limit );
		}

		return $result;
	}

	/**
	 * Return an array of ids the user belong to
	 * @param type $userid
	 * @return type
	 *
	 */
	public function getGroupIds($userId)
	{
		$db		= $this->getDBO();
		$query		= 'SELECT DISTINCT a.'.$db->quoteName('id').' FROM ' . $db->quoteName('#__community_groups') . ' AS a '
				. ' LEFT JOIN ' . $db->quoteName('#__community_groups_members') . ' AS b '
				. ' ON a.'.$db->quoteName('id').'=b.'.$db->quoteName('groupid')
				. ' WHERE b.'.$db->quoteName('approved').'=' . $db->Quote( '1' )
				. ' AND b.memberid=' . $db->Quote($userId);

		$db->setQuery( $query );
		$groupsid		= $db->loadColumn();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		return $groupsid;

	}

	/**
	 * Returns an object of groups which the user has registered.
	 *
	 * @access	public
	 * @param	string 	User's id.
	 * @returns array  An objects of custom fields.
	 * @todo: re-order with most active group stays on top
	 */
	public function getGroups( $userId = null , $sorting = null , $useLimit = true )
	{
		$db		= $this->getDBO();

		$extraSQL	= '';


		if( !is_null($userId) )
		{
			$extraSQL	= ' AND b.memberid=' . $db->Quote($userId);
		}

		$orderBy	= '';

		$limitSQL = '';
		$total		= 0;
		$limit		= $this->getState('limit');
		$limitstart = $this->getState('limitstart');
		if($useLimit){
			$limitSQL	= ' LIMIT ' . $limitstart . ',' . $limit ;
		}

		switch($sorting)
		{
			case 'mostmembers':
				// Get the groups that this user is assigned to
				$query		= 'SELECT a.'.$db->quoteName('id').' FROM ' . $db->quoteName('#__community_groups') . ' AS a '
							. ' LEFT JOIN ' . $db->quoteName('#__community_groups_members') . ' AS b '
							. ' ON a.'.$db->quoteName('id').'=b.'.$db->quoteName('groupid')
							. ' WHERE b.'.$db->quoteName('approved').'=' . $db->Quote( '1' )
							. $extraSQL;

				$db->setQuery( $query );
				$groupsid		= $db->loadColumn();

				if($db->getErrorNum())
				{
					JError::raiseError( 500, $db->stderr());
				}

				if( $groupsid )
				{
					$groupsid		= implode( ',' , $groupsid );

					$query			= 'SELECT a.* '
									. 'FROM ' . $db->quoteName('#__community_groups') . ' AS a '
									. ' WHERE a.'.$db->quoteName('published').'=' . $db->Quote( '1' )
									. ' AND a.'.$db->quoteName('id').' IN (' . $groupsid . ') '
									. ' ORDER BY a.'.$db->quoteName('membercount').' DESC '
									. $limitSQL;
				}
				break;
			case 'mostactive':
				$query	= 'SELECT *, (a.' . $db->quoteName('discusscount') . ' + a.' . $db->quoteName('wallcount') . ' ) AS count FROM ' . $db->quoteName('#__community_groups') . ' as a'
						. ' INNER JOIN ' . $db->quoteName('#__community_groups_members') . ' AS b ON a.' . $db->quoteName('id') . '= b.' . $db->quoteName('groupid')
						. ' WHERE a.' . $db->quoteName('published') . ' = ' . $db->Quote('1')
						. $extraSQL
						. ' GROUP BY a.' . $db->quoteName('id')
						. ' ORDER BY count DESC '
						. $limitSQL;
				break;
			case 'mostdiscussed':
				if( empty($orderBy) )
					$orderBy	= ' ORDER BY a.'.$db->quoteName('discusscount').' DESC ';
			case 'mostwall':
				if( empty($orderBy) )
					$orderBy	= ' ORDER BY a.'.$db->quoteName('wallcount').' DESC ';
			case 'alphabetical':
				if( empty($orderBy) )
					$orderBy	= 'ORDER BY a.'.$db->quoteName('name').' ASC ';
			default:
				if( empty($orderBy) )
					$orderBy	= ' ORDER BY a.created DESC ';

				$query	= 'SELECT a.* FROM '
						. $db->quoteName('#__community_groups') . ' AS a '
						. ' INNER JOIN ' . $db->quoteName('#__community_groups_members') . ' AS b '
						. ' ON a.'.$db->quoteName('id').'=b.'.$db->quoteName('groupid')
						. ' AND b.'.$db->quoteName('approved').'=' . $db->Quote( '1' )
						. ' AND a.'.$db->quoteName('published').'=' . $db->Quote( '1' ) . ' '
						. $extraSQL
						. $orderBy
						. $limitSQL;
				break;
		}
		
		$db->setQuery( $query );

		$result	= $db->loadObjectList();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_groups') . ' AS a '
				. ' INNER JOIN ' . $db->quoteName('#__community_groups_members') . ' AS b '
				. ' WHERE a.'.$db->quoteName('id').'=b.'.$db->quoteName('groupid')
				. ' AND a.'.$db->quoteName('published').'=' . $db->Quote( '1' ) . ' '
				. ' AND b.'.$db->quoteName('approved').'=' . $db->Quote( '1' )
				. $extraSQL;

		$db->setQuery( $query );
		$total	= $db->loadResult();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		if( empty($this->_pagination) )
		{
			jimport('joomla.html.pagination');

			$this->_pagination	= new JPagination( $total , $limitstart , $limit );
		}

		return $result;
	}

	/**
	 * Return the number of groups count for specific user
	 **/
	public function getGroupsCount( $userId )
	{
		// guest obviously has no group
		if($userId == 0)
		{
			return 0;
		}

		$db		= $this->getDBO();

		$query	= 'SELECT COUNT(*) FROM '. $db->quoteName( '#__community_groups_members' ) . ' AS a '
				. 'INNER JOIN ' . $db->quoteName('#__community_groups') . ' AS b '
				. 'WHERE a.'.$db->quoteName('groupid').'= b.'.$db->quoteName('id'). ' '
				. 'AND '. $db->quoteName( 'memberid' ) . '=' . $db->Quote( $userId ) . ' '
				. 'AND '. $db->quoteName( 'approved' ) . '=' . $db->Quote( '1' ) . ' '
				. 'AND b.'.$db->quoteName( 'published' ). '='. $db->Quote( '1' );

		$db->setQuery( $query );
		$count	= $db->loadResult();

		return $count;
	}

	public function getTotalToday( $userId )
	{
		$date	= JFactory::getDate();
		$db		= JFactory::getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_groups' ) . ' AS a '
				. ' WHERE a.'.$db->quoteName('ownerid').'=' . $db->Quote( $userId )
				. ' AND TO_DAYS(' . $db->Quote( $date->toSql( true ) ) . ') - TO_DAYS( DATE_ADD( a.'.$db->quoteName('created').' , INTERVAL ' . $date->getOffset() . ' HOUR ) ) = '.$db->Quote(0);
		$db->setQuery( $query );

		$count		= $db->loadResult();

		return $count;
	}
	/**
	 * Return the number of groups cretion count for specific user
	 **/
	public function getGroupsCreationCount( $userId )
	{
		// guest obviously has no group
		if($userId == 0)
			return 0;

		$db		= $this->getDBO();

		$query	= 'SELECT COUNT(*) FROM '
				. $db->quoteName( '#__community_groups' ) . ' '
				. 'WHERE ' . $db->quoteName( 'ownerid' ) . '=' . $db->Quote( $userId );
		$db->setQuery( $query );

		$count	= $db->loadResult();

		return $count;
	}

	/**
	 * Returns the count of the members of a specific group
	 *
	 * @access	public
	 * @param	string 	Group's id.
	 * @return	int	Count of members
	 */
	public function getMembersCount( $id )
	{
		$db	= $this->getDBO();

		if( !isset($this->membersCount[$id] ) )
		{
			$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_groups_members') . ' '
					. 'WHERE '.$db->quoteName('groupid').'=' . $db->Quote( $id ) . ' '
					. 'AND ' . $db->quoteName( 'approved' ) . '=' . $db->Quote( '1' );

			$db->setQuery( $query );
			$this->membersCount[$id]	= $db->loadResult();

			if($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}
		}
		return $this->membersCount[$id];
	}

	/**
	 * Return the count of the user's friend of a specific group
	 */
	public function getFriendsCount( $userid, $groupid )
	{
		$db	= $this->getDBO();

		$query	=   'SELECT COUNT(DISTINCT(a.'.$db->quoteName('connect_to').')) AS id  FROM ' . $db->quoteName('#__community_connection') . ' AS a '
			    . ' INNER JOIN ' . $db->quoteName( '#__users' ) . ' AS b '
			    . ' INNER JOIN ' . $db->quoteName( '#__community_groups_members' ) . ' AS c '
			    . ' ON a.'.$db->quoteName('connect_from').'=' . $db->Quote( $userid )
			    . ' AND a.'.$db->quoteName('connect_to').'=b.'.$db->quoteName('id')
			    . ' AND c.'.$db->quoteName('groupid').'=' . $db->Quote( $groupid )
			    . ' AND a.'.$db->quoteName('connect_to').'=c.'.$db->quoteName('memberid')
			    . ' AND a.'.$db->quoteName('status').'=' . $db->Quote( '1' )
			    . ' AND c.'.$db->quoteName('approved').'=' . $db->Quote( '1' );

		$db->setQuery( $query );

		$total = $db->loadResult();

		return $total;
	}

	public function getInviteFriendsList($userid, $groupid){
		$db	= $this->getDBO();

		$query	=   'SELECT DISTINCT(a.'.$db->quoteName('connect_to').') AS id  FROM ' . $db->quoteName('#__community_connection') . ' AS a '
			    . ' INNER JOIN ' . $db->quoteName( '#__users' ) . ' AS b '
			    . ' ON a.'.$db->quoteName('connect_from').'=' . $db->Quote( $userid )
			    . ' AND a.'.$db->quoteName('connect_to').'=b.'.$db->quoteName('id')
			    . ' AND a.'.$db->quoteName('status').'=' . $db->Quote( '1' )
				. ' AND b.'.$db->quoteName('block').'=' .$db->Quote('0')
				. ' WHERE NOT EXISTS ( SELECT d.'.$db->quoteName('blocked_userid') . ' as id'
									. ' FROM '.$db->quoteName('#__community_blocklist') . ' AS d  '
									. ' WHERE d.'.$db->quoteName('userid').' = '.$db->Quote($userid)
									. ' AND d.'.$db->quoteName('blocked_userid').' = a.'.$db->quoteName('connect_to').')'
				. ' AND NOT EXISTS (SELECT e.'.$db->quoteName('memberid') . ' as id'
									. ' FROM '.$db->quoteName('#__community_groups_members') . ' AS e  '
									. ' WHERE e.'.$db->quoteName('groupid').' = '.$db->Quote($groupid)
									. ' AND e.'.$db->quoteName('memberid').' = a.'.$db->quoteName('connect_to')
				.')' ;

		$db->setQuery( $query );

		$friends = $db->loadColumn();

		return $friends;
	}


	public function getInviteListByName($namePrefix ,$userid, $cid, $limitstart = 0, $limit = 8){
		$db	= $this->getDBO();

		$andName = '';
		$config = CFactory::getConfig();
		$nameField = $config->getString('displayname');
		if(!empty($namePrefix)){
			$andName	= ' AND b.' . $db->quoteName( $nameField ) . ' LIKE ' . $db->Quote( '%'.$namePrefix.'%' ) ;
		}
		$query	=   'SELECT DISTINCT(a.'.$db->quoteName('connect_to').') AS id  FROM ' . $db->quoteName('#__community_connection') . ' AS a '
			    . ' INNER JOIN ' . $db->quoteName( '#__users' ) . ' AS b '
			    . ' ON a.'.$db->quoteName('connect_from').'=' . $db->Quote( $userid )
			    . ' AND a.'.$db->quoteName('connect_to').'=b.'.$db->quoteName('id')
			    . ' AND a.'.$db->quoteName('status').'=' . $db->Quote( '1' )
				. ' AND b.'.$db->quoteName('block').'=' .$db->Quote('0')
				. ' WHERE NOT EXISTS ( SELECT d.'.$db->quoteName('blocked_userid') . ' as id'
									. ' FROM '.$db->quoteName('#__community_blocklist') . ' AS d  '
									. ' WHERE d.'.$db->quoteName('userid').' = '.$db->Quote($userid)
									. ' AND d.'.$db->quoteName('blocked_userid').' = a.'.$db->quoteName('connect_to').')'
				. ' AND NOT EXISTS (SELECT e.'.$db->quoteName('memberid') . ' as id'
									. ' FROM '.$db->quoteName('#__community_groups_members') . ' AS e  '
									. ' WHERE e.'.$db->quoteName('groupid').' = '.$db->Quote($cid)
									. ' AND e.'.$db->quoteName('memberid').' = a.'.$db->quoteName('connect_to')
				.')'
				. $andName
				. ' ORDER BY b.' . $db->quoteName($nameField)
				. ' LIMIT ' . $limitstart.','.$limit
				;
		$db->setQuery( $query );
		$friends = $db->loadColumn();

		//calculate total
		$query	=   'SELECT COUNT(DISTINCT(a.'.$db->quoteName('connect_to').'))  FROM ' . $db->quoteName('#__community_connection') . ' AS a '
			    . ' INNER JOIN ' . $db->quoteName( '#__users' ) . ' AS b '
			    . ' ON a.'.$db->quoteName('connect_from').'=' . $db->Quote( $userid )
			    . ' AND a.'.$db->quoteName('connect_to').'=b.'.$db->quoteName('id')
			    . ' AND a.'.$db->quoteName('status').'=' . $db->Quote( '1' )
				. ' AND b.'.$db->quoteName('block').'=' .$db->Quote('0')
				. ' WHERE NOT EXISTS ( SELECT d.'.$db->quoteName('blocked_userid') . ' as id'
									. ' FROM '.$db->quoteName('#__community_blocklist') . ' AS d  '
									. ' WHERE d.'.$db->quoteName('userid').' = '.$db->Quote($userid)
									. ' AND d.'.$db->quoteName('blocked_userid').' = a.'.$db->quoteName('connect_to').')'
				. ' AND NOT EXISTS (SELECT e.'.$db->quoteName('memberid') . ' as id'
									. ' FROM '.$db->quoteName('#__community_groups_members') . ' AS e  '
									. ' WHERE e.'.$db->quoteName('groupid').' = '.$db->Quote($cid)
									. ' AND e.'.$db->quoteName('memberid').' = a.'.$db->quoteName('connect_to')
				.')'
				. $andName;

		$db->setQuery( $query );
		$this->total	=  $db->loadResult();

		return $friends;
	}

	/**
	 * Return an object of group's invitors
	 */
	public function getInvitors( $userid, $groupid )
	{
		if($userid == 0)
		{
		    return false;
		}

		$db	=  $this->getDBO();

		$query	=   'SELECT DISTINCT(' . $db->quoteName( 'creator' ) . ') FROM ' . $db->quoteName('#__community_groups_invite') . ' '
			    . 'WHERE ' . $db->quoteName( 'groupid' ) . '=' . $db->Quote( $groupid ) . ' '
			    . 'AND ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $userid );

		$db->setQuery( $query );

		$results  =	$db->loadObjectList();

		// bind to table
		$data = array();
		foreach($results AS $row) {
			$invitor = JTable::getInstance('GroupInvite', 'CTable');
			$invitor->bind($row);
			$data[] = $invitor;
		}

		return $data;
	}

	/**
	 * Returns All the groups
	 *
	 * @access	public
	 * @param	string 	Category id
	 * @param	string	The sort type
	 * @param	string	Search value
	 * @return	Array	An array of group objects
	 */
	public function getAllGroups( $categoryId = null , $sorting = null , $search = null , $limit = null , $skipDefaultAvatar = false )
	{
		$db		= $this->getDBO();

		$extraSQL	= '';
		$pextra		= '';

		if( is_null( $limit ) )
		{
			$limit		= $this->getState('limit');
		}
		$limit	= ($limit < 0) ? 0 : $limit;
		$limitstart = $this->getState('limitstart');

		// Test if search is parsed
		if( !is_null( $search ) )
		{
			$extraSQL	.= ' AND a.'.$db->quoteName('name').' LIKE ' . $db->Quote( '%' . $search . '%' ) . ' ';
		}

		if( $skipDefaultAvatar )
		{
			$extraSQL	.= ' AND ( a.'.$db->quoteName('thumb').' != ' . $db->Quote( DEFAULT_GROUP_THUMB ) . ' AND a.'.$db->quoteName('avatar').' != ' . $db->Quote( DEFAULT_GROUP_AVATAR ) . ' )';
		}
		$order	='';
		switch ( $sorting )
		{
			case 'alphabetical':
				$order		= ' ORDER BY a.'.$db->quoteName('name').' ASC ';
				break;
			case 'mostdiscussed':
				$order	= ' ORDER BY '.$db->quoteName('discusscount').' DESC ';
				break;
			case 'mostwall':
				$order	= ' ORDER BY '.$db->quoteName('wallcount').' DESC ';
				break;
			case 'mostmembers':
				$order	= ' ORDER BY '.$db->quoteName('membercount').' DESC ';
				break;
			default:
				$order	= 'ORDER BY a.'.$db->quoteName('created').' DESC ';
				break;
// 			case 'mostactive':
// 				$order	= ' ORDER BY count DESC';
// 				break;
		}

		if( !is_null($categoryId) && $categoryId != 0 )
		{
                    if (is_array($categoryId)) {
                        if (count($categoryId) > 0) {
                            $categoryIds = implode(',', $categoryId);
                            $extraSQL .= ' AND a.' . $db->quoteName('categoryid'). ' IN(' . $categoryIds . ')';
                        }
                    } else {
                        $extraSQL .= ' AND a.'.$db->quoteName('categoryid').'=' . $db->Quote($categoryId) . ' ';
                    }
		}

		/*
		// Super slow query
        $query = 'SELECT a.*,'
				. 'COUNT(DISTINCT(b.memberid)) AS membercount,'
				. 'COUNT(DISTINCT(c.id)) AS discussioncount,'
				. 'COUNT(DISTINCT(d.id)) AS wallcount '
				. 'FROM ' . $db->quoteName( '#__community_groups' ) . ' AS a '
        		. 'INNER JOIN ' . $db->quoteName( '#__community_groups_members' ) . ' AS b '
        		. 'ON a.id=b.groupid '
        		. $extraSQL
        		. 'AND b.approved=' . $db->Quote( '1' ) . ' '
        		. 'AND a.published=' . $db->Quote( '1' ) . ' '
        		. 'LEFT JOIN ' . $db->quoteName( '#__community_groups_discuss' ) . ' AS c '
        		. 'ON a.id=c.groupid '
        		. 'AND c.parentid=' . $db->Quote( '0' ) . ' '
        		. 'LEFT JOIN ' . $db->quoteName( '#__community_wall') . ' AS d '
        		. 'ON a.id=d.contentid '
				. 'AND d.type=' . $db->Quote( 'groups' ) . ' '
				. 'AND d.published=' . $db->Quote( '1' ) . ' '
                . 'GROUP BY a.id '
                . $order
				. ' LIMIT ' . $limitstart . ',' . $limit;

		$db->setQuery( $query );
		$result	= $db->loadObjectList();
		*/

		if ($sorting == 'mostactive')
		{
			$query	= 'SELECT *, (a.' . $db->quoteName('discusscount') . ' + a.' . $db->quoteName('wallcount') . ' ) AS count FROM ' . $db->quoteName('#__community_groups') . ' as a'
						. ' INNER JOIN ' . $db->quoteName('#__community_groups_members') . ' AS b ON a.' . $db->quoteName('id') . '= b.' . $db->quoteName('groupid')
						. ' WHERE a.' . $db->quoteName('published') . ' = ' . $db->Quote('1')
						. $extraSQL
						. ' GROUP BY a.' . $db->quoteName('id')
						. ' ORDER BY '.$db->quoteName('count').' DESC '
						. ' LIMIT '.$limitstart .' , '.$limit;
		}
		else
		{
			$query = 'SELECT * FROM '.$db->quoteName('#__community_groups').' as a WHERE a.'.$db->quoteName('published').'='.$db->Quote('1') .' '
					. $extraSQL
					. $order
					. " LIMIT $limitstart , $limit";
		}

		$db->setQuery( $query );
		$rows	= $db->loadObjectList();

		if(!empty($rows)){
			//count members, some might be blocked, so we want to deduct from the total we currently have
			foreach($rows as $k => $r){
				$query = "SELECT COUNT(*)
						  FROM #__community_groups_members a
						  JOIN #__users b ON a.memberid=b.id
						  WHERE `approved`='1' AND b.block=0 AND groupid=".$db->Quote($r->id);
 				$db->setQuery( $query );
 				$rows[$k]->membercount = $db->loadResult();
			}
		}


// 		if(!empty($rows)){
// 			for($i = 0; $i < count($rows); $i++){
//
// 				// Count no of members
// 				$query = "SELECT COUNT(*) FROM #__community_groups_members WHERE `approved`='1' "
// 					. " AND groupid=".$db->Quote($rows[$i]->id);
// 				$db->setQuery( $query );
// 				$rows[$i]->membercount = $db->loadResult();
//
// 				// Count wall post
// 				$query = "SELECT COUNT(*) FROM #__community_wall WHERE "
// 					. " `contentid`=".$db->Quote($rows[$i]->id)
// 					. " AND type=".$db->Quote('groups')
// 					. " AND published=".$db->Quote('1');
//
// 				$db->setQuery( $query );
// 				$rows[$i]->wallcount = $db->loadResult();
//
// 				// Count discussion
// 				$query = "SELECT groupid FROM #__community_groups_discuss "
// 					. " WHERE groupid=".$db->Quote($rows[$i]->id)
// 					. " AND parentid=" . $db->Quote( '0' );
// 				$db->setQuery( $query );
// 				$rows[$i]->discussioncount = $db->loadResult();
// 			}
// 		}

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		$query	= 'SELECT COUNT(*) FROM '.$db->quoteName('#__community_groups').' AS a '
				. 'WHERE a.'.$db->quoteName('published').'=' . $db->Quote( '1' )
				. $extraSQL;

		$db->setQuery( $query );
		$this->total	=  $db->loadResult();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		if( empty($this->_pagination) )
		{
			jimport('joomla.html.pagination');

			$this->_pagination	= new JPagination( $this->total , $limitstart , $limit);
		}

		return $rows;
	}

	/**
	 * Returns an object of group
	 *
	 * @access	public
	 * @param	string 	Group Id
	 * @returns object  An object of the specific group
	 */
	public function & getGroup( $id )
	{
		$db		= $this->getDBO();

		$query	= 'SELECT a.*, b.'.$db->quoteName('name').' AS ownername , c.'.$db->quoteName('name').' AS category FROM '
				. $db->quoteName('#__community_groups') . ' AS a '
				. ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
				. ' INNER JOIN ' . $db->quoteName('#__community_groups_category') . ' AS c '
				. ' WHERE a.'.$db->quoteName('id').'=' . $db->Quote( $id ) . ' '
				. ' AND a.'.$db->quoteName('ownerid').'=b.'.$db->quoteName('id')
				. ' AND a.'.$db->quoteName('categoryid').'=c.'.$db->quoteName('id');

		$db->setQuery( $query );
		$result	= $db->loadObject();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		return $result;
	}

	/**
	 * Loads the categories
	 *
	 * @access	public
	 * @returns Array  An array of categories object
	 */
	public function getCategories( $catId = COMMUNITY_ALL_CATEGORIES )
	{
		$db	=  $this->getDBO();

		$where	=   '';

		if( $catId !== COMMUNITY_ALL_CATEGORIES && ($catId != 0 || !is_null($catId )))
		{
			if( $catId === COMMUNITY_NO_PARENT )
			{
				$where	=   'WHERE a.'.$db->quoteName('parent').'=' . $db->Quote( COMMUNITY_NO_PARENT ) . ' ';
			}
			else
			{
				$where	=   'WHERE a.'.$db->quoteName('parent').'=' . $db->Quote( $catId ) . ' ';
			}
		}

		$query	=   'SELECT a.*, COUNT(b.'.$db->quoteName('id').') AS count '
			    . ' FROM ' . $db->quoteName('#__community_groups_category') . ' AS a '
			    . ' LEFT JOIN ' . $db->quoteName( '#__community_groups' ) . ' AS b '
			    . ' ON a.'.$db->quoteName('id').'=b.'.$db->quoteName('categoryid')
			    . ' AND b.'.$db->quoteName('published').'=' . $db->Quote( '1' ) . ' '
			    . $where
			    . ' GROUP BY a.'.$db->quoteName('id').' ORDER BY a.'.$db->quoteName('name').' ASC';

		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		return $result;
	}

	/**
	* Return all category.
	*
	* @access  public
	* @returns Array  An array of categories object
	* @since   Jomsocial 2.6
	**/
	public function getAllCategories()
	{
		$db     = $this->getDBO();

		$query  = 'SELECT *
					FROM ' . $db->quoteName('#__community_groups_category');

		$db->setQuery( $query );
		$result = $db->loadObjectList();

		// bind to table
		$data = array();
		foreach($result AS $row) {
			$groupCat = JTable::getInstance('GroupCategory', 'CTable');
			$groupCat->bind($row);
			$data[] = $groupCat;
		}

		return $data;
	}

	/**
	 * Returns the category's group count
	 *
	 * @access  public
	 * @returns Array  An array of categories object
	 * @since   Jomsocial 2.4
	 **/
	function getCategoriesCount()
	{
		$db	=  $this->getDBO();

		$query = "SELECT c.id, c.parent, c.name, count(g.id) AS total, c.description
				  FROM " . $db->quoteName('#__community_groups_category') . " AS c
				  LEFT JOIN " . $db->quoteName('#__community_groups'). " AS g ON g.categoryid = c.id
							AND g." . $db->quoteName('published') . "=" . $db->Quote( '1' ) . "
				  GROUP BY c.id
				  ORDER BY c.name";

		$db->setQuery( $query );
		$result	= $db->loadObjectList('id');

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		return $result;
	}
	/**
	 * Returns the category name of the specific category
	 *
	 * @access public
	 * @param	string Category Id
	 * @returns string	Category name
	 **/
	public function getCategoryName( $categoryId )
	{
		CError::assert($categoryId, '', '!empty', __FILE__ , __LINE__ );
		$db		= $this->getDBO();

		$query	= 'SELECT ' . $db->quoteName('name') . ' '
				. 'FROM ' . $db->quoteName('#__community_groups_category') . ' '
				. 'WHERE ' . $db->quoteName('id') . '=' . $db->Quote( $categoryId );
		$db->setQuery( $query );

		$result	= $db->loadResult();

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		CError::assert( $result , '', '!empty', __FILE__ , __LINE__ );
		return $result;
	}

	/**
	 * Returns the members list for the specific groups
	 *
	 * @access public
	 * @param	string Category Id
	 * @returns string	Category name
	 **/
	public function getAdmins( $groupid , $limit = 0 , $randomize = false )
	{
		CError::assert( $groupid , '', '!empty', __FILE__ , __LINE__ );

		$db		= $this->getDBO();

		$limit		= ($limit === 0) ? $this->getState('limit') : $limit;
		$limitstart = $this->getState('limitstart');

		$query	= 'SELECT a.'.$db->quoteName('memberid').' AS id, a.'.$db->quoteName('approved').' , b.'.$db->quoteName('name').' as name FROM '
				. $db->quoteName('#__community_groups_members') . ' AS a '
				. ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
				. ' WHERE b.'.$db->quoteName('id').'=a.'.$db->quoteName('memberid')
				. ' AND a.'.$db->quoteName('groupid').'=' . $db->Quote( $groupid )
				. ' AND a.'.$db->quoteName('permissions').'=' . $db->Quote( '1' );

		if($randomize)
		{
			$query	.= ' ORDER BY RAND() ';
		}

		if( !is_null($limit) )
		{
			$query	.= ' LIMIT ' . $limitstart . ',' . $limit;
		}
		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		$query	= 'SELECT COUNT(*) FROM '
				. $db->quoteName('#__community_groups_members') . ' AS a '
				. ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
				. ' WHERE b.'.$db->quoteName('id').'=a.'.$db->quoteName('memberid')
				. ' AND a.'.$db->quoteName('groupid').'=' . $db->Quote( $groupid )
				. ' AND a.'.$db->quoteName('permissions').'=' . $db->Quote( '1' );

		$db->setQuery( $query );
		$total	= $db->loadResult();
		$this->total	= $total;

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		if( empty($this->_pagination) )
		{
			jimport('joomla.html.pagination');

			$this->_pagination	= new JPagination( $total , $limitstart , $limit);
		}

		return $result;
	}

	/**
	 * Returns the members list for the specific groups
	 *
	 * @access public
	 * @param	string Category Id
	 * @returns string	Category name
	 **/
	public function getAllMember($groupid){
	    CError::assert( $groupid , '', '!empty', __FILE__ , __LINE__ );
	    $db		= $this->getDBO();

	    $query	= 'SELECT a.'.$db->quoteName('memberid').' AS id, a.'.$db->quoteName('approved').' , b.'.$db->quoteName('name').' as name , a.'. $db->quoteName('permissions') .' as permission FROM '
				. $db->quoteName('#__community_groups_members') . ' AS a '
				. ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
				. ' WHERE b.'.$db->quoteName('id').'=a.'.$db->quoteName('memberid')
				. ' AND a.'.$db->quoteName('groupid').'=' . $db->Quote( $groupid )
				. ' AND b.'.$db->quoteName('block').'=' . $db->Quote( '0' ) . ' '
				. ' AND a.'.$db->quoteName('permissions').' !=' . $db->quote( COMMUNITY_GROUP_BANNED );
	    $db->setQuery( $query );
	    $result	= $db->loadObjectList();
		$this->total = count($result);
	    return $result;
	}
	public function getMembers( $groupid , $limit = 0 , $onlyApproved = true , $randomize = false , $loadAdmin = false )
	{
		CError::assert( $groupid , '', '!empty', __FILE__ , __LINE__ );

		$db		= $this->getDBO();
                $config	= CFactory::getConfig();
		$limit		= ($limit === 0) ? $this->getState('limit') : $limit;
		$limitstart = $this->getState('limitstart');

		$query	= 'SELECT a.'.$db->quoteName('memberid').' AS id, a.'.$db->quoteName('approved').' , b.'.$db->quoteName($config->get( 'displayname')).' as name FROM'
				. $db->quoteName('#__community_groups_members') . ' AS a '
				. ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
				. ' WHERE b.'.$db->quoteName('id').'=a.'.$db->quoteName('memberid')
				. ' AND a.'.$db->quoteName('groupid').'=' . $db->Quote( $groupid )
				. ' AND b.'.$db->quoteName('block').'=' . $db->Quote( '0' ) . ' '
				. ' AND a.'.$db->quoteName('permissions').' !=' . $db->quote( COMMUNITY_GROUP_BANNED );

		if( $onlyApproved )
		{
			$query	.= ' AND a.'.$db->quoteName('approved').'=' . $db->Quote( '1' );
		}
		else
		{
			$query	.= ' AND a.'.$db->quoteName('approved').'=' . $db->Quote( '0' );
		}

		if( !$loadAdmin )
		{
			$query	.= ' AND a.'.$db->quoteName('permissions').'=' . $db->Quote( '0' );
		}

		if( $randomize )
		{
			$query	.= ' ORDER BY RAND() ';
		}
		else
		{

			$query	.= ' ORDER BY b.`' . $config->get( 'displayname') . '`';
		}

		if( !is_null($limit) )
		{
			$query	.= ' LIMIT ' . $limitstart . ',' . $limit;
		}

		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		$query	= 'SELECT COUNT(*) FROM '
				. $db->quoteName('#__community_groups_members') . ' AS a '
				. ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
				. ' WHERE b.'.$db->quoteName('id').'=a.'.$db->quoteName('memberid')
				. ' AND a.'.$db->quoteName('groupid').'=' . $db->Quote( $groupid )
				. ' AND b.'.$db->quoteName('block').'=' . $db->Quote( '0' );

		if( $onlyApproved )
		{
			$query	.= ' AND a.'.$db->quoteName('approved').'=' . $db->Quote( '1' );
		}
		else
		{
			$query	.= ' AND a.'.$db->quoteName('approved').'=' . $db->Quote( '0' );
		}

		if( !$loadAdmin )
		{
			$query	.= ' AND a.'.$db->quoteName('permissions').'=' . $db->Quote( '0' );
		}

		$db->setQuery( $query );
		$total		= $db->loadResult();
		$this->total	= $total;

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		if( empty($this->_pagination) )
		{
			jimport('joomla.html.pagination');

			$this->_pagination	= new JPagination( $total , $limitstart , $limit);
		}

		return $result;
	}

	/**
	 * Check if the given group name exist.
	 * if id is specified, only search for those NOT within $id
	 */
	public function groupExist($name, $id=0) {
		$db		= $this->getDBO();

		$strSQL	= 'SELECT COUNT(*) FROM '.$db->quoteName('#__community_groups')
			. ' WHERE '.$db->quoteName('name').'=' . $db->Quote( $name )
			. ' AND '.$db->quoteName('id').'!='. $db->Quote( $id ) ;


		$db->setQuery( $strSQL );
		$result	= $db->loadResult();

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		return $result;
	}

	public function getMembersId( $groupid , $onlyApproved = false )
	{
		$db		= $this->getDBO();

		$query	= 'SELECT a.'.$db->quoteName('memberid').' AS id FROM '
				. $db->quoteName('#__community_groups_members') . ' AS a '
				. 'WHERE a.'.$db->quoteName('groupid').'=' . $db->Quote( $groupid );

		if( $onlyApproved )
			$query	.= ' AND ' . $db->quoteName( 'approved' ) . '=' . $db->Quote( '1' );

		$db->setQuery( $query );
		$result	= $db->loadColumn();

		return $result;
	}

	public function updateGroup($data)
	{
		$db		= $this->getDBO();

		if($data->id == 0)
		{
			// New record, insert it.
			$db->insertObject( '#__community_groups' , $data );

			if($db->getErrorNum()) {
				JError::raiseError( 500, $db->stderr());
			}
			$data->id				= $db->insertid();

			// Insert an object for this user in the #__community_groups_members as well
			$members				= new stdClass();
			$members->groupid		= $data->id;
			$members->memberid		= $data->ownerid;


			// Creator should always be 1 as approved as they are the creator.
			$members->approved		= 1;
			$members->permissions	= 'admin';

			$db->insertObject( '#__community_groups_members' , $members );

			if($db->getErrorNum()) {
				JError::raiseError( 500, $db->stderr());
			}
		}
		else
		{
			// Old record, update it.
			$db->updateObject( '#__community_groups' , $data , 'id');
		}
		return $data->id;
	}

	/**
	 *	Set the avatar for for specific group
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

		$db			= $this->getDBO();

		// Fix the back quotes
		$path		= CString::str_ireplace( '\\' , '/' , $path );
		$type		= JString::strtolower( $type );

		// Test if the record exists.
		$query		= 'SELECT ' . $db->quoteName( $type ) . ' FROM ' . $db->quoteName( '#__community_groups' )
					. 'WHERE ' . $db->quoteName( 'id' ) . '=' . $db->Quote( $id );

		$db->setQuery( $query );
		$oldFile	= $db->loadResult();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
	    }

	    if( !$oldFile )
	    {
	    	$query	= 'UPDATE ' . $db->quoteName( '#__community_groups' ) . ' '
	    			. 'SET ' . $db->quoteName( $type ) . '=' . $db->Quote( $path ) . ' '
	    			. 'WHERE ' . $db->quoteName( 'id' ) . '=' . $db->Quote( $id );
	    	$db->setQuery( $query );
	    	$db->query( $query );

			if($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
		    }
		}
		else
		{

	    	$query	= 'UPDATE ' . $db->quoteName( '#__community_groups' ) . ' '
	    			. 'SET ' . $db->quoteName( $type ) . '=' . $db->Quote( $path ) . ' '
	    			. 'WHERE ' . $db->quoteName( 'id' ) . '=' . $db->Quote( $id );
	    	$db->setQuery( $query );
	    	$db->query( $query );

			if($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
		    }

			// File exists, try to remove old files first.
			$oldFile	= CString::str_ireplace( '/' , '/' , $oldFile );

			// If old file is default_thumb or default, we should not remove it.
			// Need proper way to test it
			if(!JString::stristr( $oldFile , 'group.jpg' ) && !JString::stristr( $oldFile , 'group_thumb.jpg' ) &&
			   !JString::stristr( $oldFile , 'default.jpg' ) && !JString::stristr( $oldFile , 'default_thumb.jpg' ) )
			{
				jimport( 'joomla.filesystem.file' );
				JFile::delete($oldFile);
			}
		}

		return $this;
	}

	public function removeMember( $data )
	{
		$db	= $this->getDBO();

		$strSQL	= 'DELETE FROM ' . $db->quoteName('#__community_groups_members') . ' '
				. 'WHERE ' . $db->quoteName('groupid') . '=' . $db->Quote( $data->groupid ) . ' '
				. 'AND ' . $db->quoteName('memberid') . '=' . $db->Quote( $data->memberid );

		$db->setQuery( $strSQL );
		$db->query();

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
	}

	/**
	 * Check if the user is a group creator
	 */
	public function isCreator( $userId , $groupId )
	{
		// guest is not a member of any group
		if($userId == 0)
			return false;

		$db		= $this->getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_groups' ) . ' '
				. 'WHERE ' . $db->quoteName( 'id' ) . '=' . $db->Quote( $groupId ) . ' '
				. 'AND ' . $db->quoteName( 'ownerid' ) . '=' . $db->Quote( $userId );
		$db->setQuery( $query );

		$isCreator	= ( $db->loadResult() >= 1 ) ? true : false;
		return $isCreator;
	}

	/**
	 * Check if the user is invited in the group
	 */
	public function isInvited($userid, $groupid)
	{
		if($userid == 0)
		{
		    return false;
		}

		$db	=  $this->getDBO();

		$query	=   'SELECT * FROM ' . $db->quoteName('#__community_groups_invite') . ' '
			    . 'WHERE ' . $db->quoteName( 'groupid' ) . '=' . $db->Quote( $groupid ) . ' '
			    . 'AND ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $userid );

		$db->setQuery( $query );

		$isInvited	= ( $db->loadResult() >= 1 ) ? true : false;

		return $isInvited;
	}

	/**
	 * Check if the user is a group admin
	 */
	public function isAdmin($userid, $groupid)
	{
		if($userid == 0)
			return false;

		$db		= $this->getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_groups_members') . ' '
				. ' WHERE ' . $db->quoteName('groupid') . '=' . $db->Quote($groupid) . ' '
				. ' AND ' . $db->quoteName('memberid') . '=' . $db->Quote($userid)
				. ' AND '.$db->quoteName('permissions').'=' . $db->Quote( '1' );

		$db->setQuery( $query );
		$isAdmin	= ( $db->loadResult() >= 1 ) ? true : false;

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		//@remove: in newer version we need to skip this test as we were using 'admin'
		// as the permission for the creator
		if( !$isAdmin )
		{
			$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_groups' ) . ' '
					. 'WHERE ' . $db->quoteName( 'id' ) . '=' . $db->Quote( $groupid ) . ' '
					. 'AND ' . $db->quoteName( 'ownerid' ) . '=' . $db->Quote( $userid );
			$db->setQuery( $query );

			$isAdmin	= ( $db->loadResult() >= 1 ) ? true : false;

			if($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}

			// If user is admin, update necessary records
			if( $isAdmin )
			{
				$members	= JTable::getInstance( 'GroupMembers' , 'CTable' );
				$keys = array('memberId'=>$userid , 'groupId'=>$groupid );
				$members->load( $keys);
				$members->permissions	= '1';
				$members->store();
			}
		}

		return $isAdmin;
	}

	/**
	 * Check if the given user is a member of the group
	 * @param	string	userid
	 * @param	string	groupid
	 */
	public function isMember($userid, $groupid) {

		// guest is not a member of any group
		if($userid == 0)
			return false;

		$db	= $this->getDBO();
		$strSQL	= 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_groups_members') . ' '
				. 'WHERE ' . $db->quoteName('groupid') . '=' . $db->Quote($groupid) . ' '
				. 'AND ' . $db->quoteName('memberid') . '=' . $db->Quote($userid)
				. 'AND ' . $db->quoteName( 'approved' ) .'=' . $db->Quote( '1' );

		$db->setQuery( $strSQL );
		$count	= $db->loadResult();
		return $count;
	}

	/**
	 * See if the given user is waiting authorization for the group
	 * @param	string	userid
	 * @param	string	groupid
	 */
	public function isWaitingAuthorization($userid, $groupid) {
		// guest is not a member of any group
		if($userid == 0)
			return false;

		$db	= $this->getDBO();
		$strSQL	= 'SELECT COUNT(*) FROM `#__community_groups_members` '
				. 'WHERE ' . $db->quoteName('groupid') . '=' . $db->Quote($groupid) . ' '
				. 'AND ' . $db->quoteName('memberid') . '=' . $db->Quote($userid)
				. 'AND ' . $db->quoteName('approved') . '=' . $db->Quote(0);

		$db->setQuery( $strSQL );
		$count	= $db->loadResult();
		return $count;
	}

	/**
	 * Gets the groups property if it requires an approval or not.
	 *
	 * param	string	id The id of the group.
	 *
	 * return	boolean	True if it requires approval and False otherwise
	 **/
	public function needsApproval( $id )
	{
		$db		= $this->getDBO();
		$query	= 'SELECT ' . $db->quoteName( 'approvals' ) . ' FROM '
				. $db->quoteName( '#__community_groups' ) . ' WHERE '
				. $db->quoteName( 'id' ) . '=' . $db->Quote( $id );

		$db->setQuery( $query );
		$result	= $db->loadResult();

		return ( $result == '1' );
	}

	/**
	 * Sets the member data in the group members table
	 *
	 * param	Object	An object that contains the fields value
	 *
	 **/
	public function approveMember( $groupid , $memberid )
	{
		$db		= $this->getDBO();

		$query	= 'UPDATE ' . $db->quoteName( '#__community_groups_members' ) . ' SET '
				. $db->quoteName( 'approved' ) . '=' . $db->Quote( '1' ) . ' '
				. 'WHERE ' . $db->quoteName( 'groupid' ) . '=' . $db->Quote( $groupid ) . ' '
				. 'AND ' . $db->quoteName( 'memberid' ) . '=' . $db->Quote( $memberid );

		$db->setQuery( $query );
		$db->query();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
	}

	/**
	 * Delete group's bulletin
	 *
	 * param	string	id The id of the group.
	 *
	 **/
	static public function deleteGroupBulletins($gid)
	{
		$db = JFactory::getDBO();

		$sql = "DELETE

				FROM
						".$db->quoteName("#__community_groups_bulletins")."
				WHERE
						".$db->quoteName("groupid")." = ".$db->quote($gid);

		$db->setQuery($sql);
		$db->Query();
		if($db->getErrorNum()){
			JError::raiseError( 500, $db->stderr());
		}

		return true;
	}

	/**
	 * Delete group's member
	 *
	 * param	string	id The id of the group.
	 *
	 **/
	static public function deleteGroupMembers($gid)
	{
		$db = JFactory::getDBO();

		$sql = "DELETE

				FROM
						".$db->quoteName("#__community_groups_members")."
				WHERE
						".$db->quoteName("groupid")."=".$db->quote($gid);
		$db->setQuery($sql);
		$db->Query();
		if($db->getErrorNum()){
			JError::raiseError( 500, $db->stderr());
		}

		return true;
	}

	/**
	 * Delete group's wall
	 *
	 * param	string	id The id of the group.
	 *
	 **/
	static public function deleteGroupWall($gid)
	{
		$db = JFactory::getDBO();

		$sql = "DELETE

				FROM
						".$db->quoteName("#__community_wall")."
				WHERE
						".$db->quoteName("contentid")." = ".$db->quote($gid)." AND
						".$db->quoteName("type")." = ".$db->quote('groups');
		$db->setQuery($sql);
		$db->Query();
		if($db->getErrorNum()){
			JError::raiseError( 500, $db->stderr());
		}

                //Remove Group info from activity stream
                $sql = "Delete FROM " .$db->quoteName("#__community_activities"). "
                        WHERE ". $db->quoteName("groupid") . " = ".$db->Quote($gid);

                $db->setQuery($sql);
		$db->Query();
		if($db->getErrorNum()){
			JError::raiseError( 500, $db->stderr());
		}

		return true;
	}

	/**
	 * Delete group's discussion
	 *
	 * param	string	id The id of the group.
	 *
	 **/
	static public function deleteGroupDiscussions($gid)
	{
		$db = JFactory::getDBO();

		$sql = "SELECT
						".$db->quoteName("id")."
				FROM
						".$db->quoteName("#__community_groups_discuss")."
				WHERE
						".$db->quoteName("groupid")." = ".$gid;
		$db->setQuery($sql);
		$row = $db->loadobjectList();
		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		if(!empty($row))
		{
			$ids_array = array();
			foreach($row as $tempid)
			{
				array_push($ids_array, $tempid->id);
			}
			$ids = implode(',', $ids_array);
		}

		$sql = "DELETE

				FROM
						".$db->quoteName("#__community_groups_discuss")."
				WHERE
						".$db->quoteName("groupid")." = ".$gid;
		$db->setQuery($sql);
		$db->Query();
		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		if(!empty($ids))
		{
			$sql = "DELETE

					FROM
							".$db->quoteName("#__community_wall")."
					WHERE
							".$db->quoteName("contentid")." IN (".$ids.") AND
							".$db->quoteName("type")." = ".$db->quote('discussions');
			$db->setQuery($sql);
			$db->Query();
			if($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}
		}

		return true;
	}

	/**
	 * Delete group's media
	 *
	 * param	string	id The id of the group.
	 *
	 **/
	static public function deleteGroupMedia($gid)
	{
		$db 			= JFactory::getDBO();
		$photosModel	= CFactory::getModel( 'photos' );
		$videoModel		= CFactory::getModel( 'videos' );
		$fileModel		= CFactory::getModel( 'files' );

		// group's photos removal.
		$albums			= $photosModel->getGroupAlbums($gid , false, false, 0);
		foreach ($albums as $item)
		{
			$photos			= $photosModel->getAllPhotos($item->id, PHOTOS_GROUP_TYPE);

			foreach ($photos as $row)
			{
				$photo	= JTable::getInstance( 'Photo' , 'CTable' );
				$photo->load($row->id);
				$photo->delete();
			}

			//now we delete group photo album folder
			$album	= JTable::getInstance( 'Album' , 'CTable' );
			$album->load($item->id);
			$album->delete();
		}

		//group's videos


		$featuredVideo	= new CFeatured(FEATURED_VIDEOS);
		$videos			= $videoModel->getGroupVideos($gid);

		foreach($videos as $vitem)
		{
			if (!$vitem) continue;

			$video		= JTable::getInstance( 'Video' , 'CTable' );
			$videaId	= (int) $vitem->id;

			$video->load($videaId);

			if($video->delete())
			{
				// Delete all videos related data
				$videoModel->deleteVideoWalls($videaId);
				$videoModel->deleteVideoActivities($videaId);

				//remove featured video
				$featuredVideo->delete($videaId);

				//remove the physical file
				$storage = CStorage::getStorage($video->storage);
				if ($storage->exists($video->thumb))
				{
					$storage->delete($video->thumb);
				}

				if ($storage->exists($video->path))
				{
					$storage->delete($video->path);
				}
			}

		}

		$fileModel->alldelete($gid,'group');

		return true;
	}

	/* @since 2.6
	 * group category id - int
	 * list of group ids - string, separate by comma
	 * limit - int
	 */
	public function getGroupLatestDiscussion($category = 0, $groupids = '', $limit = '')
	{
	    $db 		= JFactory::getDBO();
	    $config		= CFactory::getConfig();
		$mainframe	= JFactory::getApplication();

		// Get pagination request variables
	    $limit  = (empty($limit)) ? $mainframe->getCfg('list_limit') : $limit;

		// Filter category
		$idswhere = '';
		if( !empty($groupids))
		{
			$idswhere = ' AND b.`id` IN (' . $groupids . ')';
		}

		$query	 = 'SELECT a.'.$db->quoteName('id').', a.'.$db->quoteName('groupid').', a.'.$db->quoteName('creator').', a.'.$db->quoteName('title').',a.'.$db->quoteName('message').', a.'.$db->quoteName('lastreplied');
		$query	.= ' FROM '.$db->quoteName('#__community_groups_discuss').' AS a ';
		$query	.= '	JOIN (';
		$query	.= '	SELECT b.'.$db->quoteName('id');
		$query	.= '	FROM '.$db->quoteName('#__community_groups').' AS b';
		$query	.= '	WHERE ';
		$query	.= '		b.'.$db->quoteName('published').' = 1';
		$query	.= '		AND ';
		$query	.= '		b.'.$db->quoteName('approvals').' = 0';
		$query	.= '		'.$idswhere;
		$query	.= '	) AS c ON c.'.$db->quoteName('id').' = a.'.$db->quoteName('groupid');

		$query  .= ' order by a.'. $db->quoteName('lastreplied'). ' desc';
		if(! empty($limit))
		{
		    $query  .= ' LIMIT '. $limit;
		}

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * @since 2.6
	 * @param user id
	 * @return array of the latest group discussion the user has participated
	 */
	public function getGroupDiscussionLastActive( $userid ){
		$db 		= JFactory::getDBO();

		//first we get the discussion that the user has participated into
		$query = "SELECT contentid FROM ".$db->quoteName('#__community_wall')." WHERE type='discussions' AND post_by=".$db->Quote($userid);
		$db->setQuery( $query );

		$discussionIds = $db->loadAssocList();
		$discussionIds = JArrayHelper::getColumn($discussionIds,'contentid');

		if(count($discussionIds) > 0){
			$discussionIds = implode(',',$discussionIds);

			//set the limit if configurable
			$query = "SELECT g.id AS discussionid, w.post_by, w.comment, w.date, g.groupid, g.title, gr.name AS group_name
						FROM ".$db->quoteName('#__community_wall')." w, ".$db->quoteName('#__community_groups_discuss')."
						g, ".$db->quoteName('#__community_groups')." gr WHERE w.contentid = g.id AND w.type='discussions'
						AND gr.id = g.groupid AND w.contentid IN (".$discussionIds.") ORDER BY w.id DESC LIMIT 9";
			$db->setQuery( $query );

			$discussionUpdates = $db->loadAssocList();

			//generate extra info for latest discussion updates
			foreach($discussionUpdates as &$disc){
				//grouplink
				$disc['group_link'] =  CRoute::_( 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $disc['groupid'] );
				$disc['discussion_link'] = CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $disc['groupid'] . '&topicid=' . $disc['discussionid']);
				$date	= CTimeHelper::getDate($disc['date']);

				$disc['created_interval'] = CTimeHelper::timeLapse($date);

				$user = CFactory::getUser($disc['post_by']);
				$disc['created_by'] = $user->getDisplayName();

				$table	=  JTable::getInstance( 'Group' , 'CTable' );
				$table->load($disc['groupid']);
				$disc['group_avatar'] = $table->getThumbAvatar();
			}
			return $discussionUpdates;
		}

		return null;
	}

	/**
	 * @since 2.6
	 * @param user id
	 * @return array of the latest group announcement the user is in
	 */
	public function getGroupAnnouncementUpdate( $userid, $limit = 3 ){
		$userGroups = $this->getGroupIds( $userid );
		$albumsDetails = array();

		if($limit > 0){
			$extraSQL = ' LIMIT '.$limit;
		}

		if(count($userGroups) > 0){
			$db	= $this->getDBO();

			$groups_id = implode(',',$userGroups);
			$query	= 'SELECT b.*, g.name AS group_name FROM ' . $db->quoteName('#__community_groups_bulletins')
					. ' AS b, '.$db->quoteName('#__community_groups').' AS g WHERE b.groupid IN (' . $groups_id .' ) AND g.id=b.groupid ORDER BY b.id DESC '.$extraSQL;

			$db->setQuery( $query );

			$announcements = $db->loadAssocList();

			foreach($announcements as &$announcement){
				//grouplink
				$announcement['group_link'] =  CRoute::_( 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $announcement['groupid'] );
				$announcement['announcement_link'] = CRoute::_('index.php?option=com_community&view=groups&task=viewbulletin&groupid=' . $announcement['groupid'] . '&bulletinid=' . $announcement['id']);
				$date	= CTimeHelper::getDate($announcement['date']);

				$announcement['created_interval'] = CTimeHelper::timeLapse($date);
				$announcement['user_avatar'] = CFactory::getUser($announcement['created_by'])->getThumbAvatar();                                
				$announcement['user_name'] = CFactory::getUser($announcement['created_by'])->getName();
			}
		}

		return $announcements;
	}

	/*
	 * @since 2.6
	 * To get all the album from the group that the user participated into (only album with new photo updates)
	 **/
	public function getGroupLatestAlbumUpdate( $userId, $limit = 3 ){
		$userGroups = $this->getGroupIds( $userId );
		$albumsDetails = array();

		if($limit > 0){
			$extraSQL = ' LIMIT '.$limit;
		}

		if(count($userGroups) > 0){
			$groups_id = implode(',',$userGroups);
			$db	= $this->getDBO();
			$query	= 'SELECT * FROM (SELECT a.id as photo_id, a.creator as creator_id, b.name as album_name,b.id as album_id, b.groupid FROM ' . $db->quoteName('#__community_photos') . ' AS a '
					. ', ' . $db->quoteName('#__community_photos_albums') . ' AS b '
					. ' WHERE b.groupid IN (' . $groups_id .' ) AND a.albumid = b.id ORDER BY a.id DESC) as tmp GROUP BY album_id'.$extraSQL;

			$db->setQuery( $query );

			$albumsDetails = $db->loadAssocList();

			//merge in the user posted name
			foreach($albumsDetails as &$album){
				$user = CFactory::getUser($album['creator_id']);
				$album['creator_name'] = $user->getDisplayName();

				$table	=  JTable::getInstance( 'Group' , 'CTable' );
				$table->load($album['groupid']);
				$album['group_avatar'] = $table->getThumbAvatar();
				$album['group_name'] = $table->name;

				$table	=  JTable::getInstance( 'Album' , 'CTable' );
				$table->load($album['album_id']);
				$album['album_thumb'] = $table->getCoverThumbURI();
			}
		}

		return $albumsDetails;
	}

	/*
	 * @since 2.6
	 * To get all events update from the group that the user participated
	 **/
	public function getGroupUpcomingEvents($userId, $limit = 3){
		$userGroups = $this->getGroupIds( $userId );
		$eventsDetails = array();

		$extraSQL = '';
		if($limit > 0){
			$extraSQL = ' LIMIT '.$limit;
		}

		if(count($userGroups) > 0){
			$groups_id = implode(',',$userGroups);
			$db	= $this->getDBO();

			$now		=   CTimeHelper::getDate();

			$query = "SELECT * FROM ". $db->quoteName('#__community_events') ." WHERE ".$db->quoteName('contentid')." IN (" . $groups_id ." ) AND
					((parent = 0 AND  (".$db->quoteName('repeat')." IS NULL OR  ".$db->quoteName('repeat')." =  ''))
					OR (parent > 0 AND  ".$db->quoteName('repeat')." IS NOT NULL)) AND ".$db->quoteName('published')." = 1 AND
					".$db->quoteName('type')." =  'group' AND ". $db->quoteName('enddate')." >= " . $db->Quote( $now->toSql() )
					." ORDER by startdate ASC ".$extraSQL;
			$db->setQuery( $query );

			$result = $db->loadObjectList();
			if(!empty($result))
			{
				foreach($result as $row)
				{
					$event = JTable::getInstance('Event', 'CTable');
					$event->bind($row);
					$eventsDetails[] = $event;
				}
			}
		}
		return $eventsDetails;
	}

	/*
	 * @since 2.6
	 * To get all videos update from the group that the user participated
	 **/
	public function getGroupVideosUpdate( $userId, $limit = 3 ){
		$userGroups = $this->getGroupIds( $userId );
		$videoDetails = array();

		if($limit > 0){
			$extraSQL = ' LIMIT '.$limit;
		}

		if(count($userGroups) > 0){
			$groups_id = implode(',',$userGroups);
			$db	= $this->getDBO();
			$query	= "SELECT * FROM " . $db->quoteName('#__community_videos')
					  . " WHERE ".$db->quoteName('creator_type')."='group' AND "
					  . $db->quoteName('groupid')." IN (" . $groups_id .") ORDER BY created DESC ".$extraSQL;
			$db->setQuery( $query );

			$videoDetails = $db->loadObjectList();
			$videos = array();

			if ($videoDetails)
			{
				foreach($videoDetails as $videoEntry)
				{
					$video	= JTable::getInstance('Video','CTable');
					$video->bind( $videoEntry );
					$videos[]	= $video;
				}
			}
		}

		return $videos;
	}

	/**
	 * Return the name of the group id
	 */
	public function getGroupName( $groupid )
	{
		$session = JFactory::getSession();
		$data = $session->get('groups_name_'.$groupid);
		if($data)
		{
			return $data;
		}
		$db	= $this->getDBO();

		$query	=   'SELECT ' . $db->quoteName('name').' FROM ' . $db->quoteName('#__community_groups')
					. " WHERE " . $db->quoteName("id") . "=" . $db->Quote($groupid);

		$db->setQuery( $query );

		$name = $db->loadResult();

		$session->set('groups_name_'.$groupid, $name);
		return $name;
	}


    /**
     * @deprecated Since 2.0
     */
	public function getThumbAvatar($id, $thumb)
	{

		$thumb	= CUrlHelper::avatarURI($thumb, 'group_thumb.png');

		return $thumb;
	}


	public function getBannedMembers( $groupid, $limit=0, $randomize=false )
	{
		CError::assert( $groupid , '', '!empty', __FILE__ , __LINE__ );

		$db	    =	$this->getDBO();

		$limit	    =	($limit === 0) ? $this->getState('limit') : $limit;
		$limitstart =	$this->getState('limitstart');

		$query	    =	'SELECT a.'.$db->quoteName('memberid').' AS id, a.'.$db->quoteName('approved').' , b.'.$db->quoteName('name').' as name '
				. ' FROM '. $db->quoteName('#__community_groups_members') . ' AS a '
				. ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
				. ' WHERE b.'.$db->quoteName('id').'=a.'.$db->quoteName('memberid')
				. ' AND a.'.$db->quoteName('groupid').'=' . $db->Quote( $groupid )
				. ' AND a.'.$db->quoteName('permissions').'=' . $db->Quote( COMMUNITY_GROUP_BANNED );

		if( $randomize )
		{
			$query	.=  ' ORDER BY RAND() ';
		}

		if( !is_null($limit) )
		{
			$query	.=  ' LIMIT ' . $limitstart . ',' . $limit;
		}

		$db->setQuery( $query );

		$result	    =   $db->loadObjectList();

		if( $db->getErrorNum() )
		{
			JError::raiseError( 500, $db->stderr() );
		}

		$query	    =	'SELECT COUNT(*) FROM '
				. $db->quoteName('#__community_groups_members') . ' AS a '
				. ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
				. ' WHERE b.'.$db->quoteName('id').'=a.'.$db->quoteName('memberid')
				. ' AND a.'.$db->quoteName('groupid').'=' . $db->Quote( $groupid ) . ' '
				. ' AND a.'.$db->quoteName('permissions').'=' . $db->Quote( COMMUNITY_GROUP_BANNED );

		$db->setQuery( $query );
		$total		=   $db->loadResult();
		$this->total	=   $total;

		if( $db->getErrorNum() ) {
			JError::raiseError( 500, $db->stderr() );
		}

		if( empty($this->_pagination) )
		{
			jimport( 'joomla.html.pagination' );
			$this->_pagination  =	new JPagination( $total, $limitstart, $limit );
		}

		return $result;
	}

	public function getGroupsSearchTotal()
	{
		return $this->total;
	}

	static public function getGroupChildId($gid){

	    $db = JFactory::getDBO();
	    //CFactory::load( 'libraries' , 'activities' );
	    $sql = "SELECT
						".$db->quoteName("id")."
				FROM
						".$db->quoteName("#__community_groups_discuss")."
				WHERE
						".$db->quoteName("groupid")." = ".$db->Quote($gid);
		$db->setQuery($sql);
		$row = $db->loadobjectList();
		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

	    $sql = "SELECT
						".$db->quoteName("id")."
				FROM
						".$db->quoteName("#__community_groups_bulletins")."
				WHERE
						".$db->quoteName("groupid")." = ".$db->Quote($gid);
		$db->setQuery($sql);
		$bulletin = $db->loadobjectList();
		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
	    $sql = "SELECT
						".$db->quoteName("id")."
				FROM
						".$db->quoteName("#__community_wall")."
				WHERE
						".$db->quoteName("contentid")." = ".$db->Quote($gid);
		$db->setQuery($sql);
		$wall = $db->loadobjectList();
		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		$row = array_merge($row, array_merge($bulletin,$wall));

		if(!empty($row))
		{
			$ids_array = array();
			foreach($row as $tempid)
			{
				array_push($ids_array, $tempid->id);
			}
			$ids = implode(',', $ids_array);
			$ids .= ','.$gid;
			//Remove All groupActivity stream
			CActivityStream::removeGroup($ids);
		}
	}
    public function countPending($userId)
    {

        $db = $this->getDBO();

        $query	= 'SELECT COUNT(*) FROM '
		. $db->quoteName('#__community_groups_invite') . ' AS a '
		. ' INNER JOIN ' . $db->quoteName( '#__community_groups' ) . ' AS b ON a.'.$db->quoteName('groupid').'=b.'.$db->quoteName('id')
                    . ' AND a.' .$db->quoteName('userid'). '=' . $db->Quote($userId);

        $db->setQuery($query);

		if ($db->getErrorNum())
		{
			JError::raiseError(500, $db->stderr());
		}

        return $db->loadResult();
    }

    public function getTotalNotifications( $user )
	{
		if($user->_cparams->get('notif_groups_invite'))
		{
			$privateGroupRequestCount=0;

			if($user->_cparams->get('notif_groups_member_join'))
			{
		        $allGroups      =   $this->getAdminGroups( $user->id , COMMUNITY_PRIVATE_GROUP);

		        foreach($allGroups as $groups)
		        {
		            $member     =    $this->getMembers( $groups->id , 0, false );

		            if(!empty($member))
		            {
		               $privateGroupRequestCount += count($member);
		            }
		        }
		    }

			return (int) $this->countPending( $user->id ) + $privateGroupRequestCount;
		}

		return 0;
	}

	public function getAdminGroups( $userId, $privacy = NULL )
	{
		$extraSQL = NULL;
		$db		= $this->getDBO();

		if( $privacy == COMMUNITY_PRIVATE_GROUP )
		{
			$extraSQL = ' AND a.'.$db->quoteName('approvals').'=' . $db->Quote( '1' );
		}

		if( $privacy == COMMUNITY_PUBLIC_GROUP )
		{
			$extraSQL = ' AND a.'.$db->quoteName('approvals').'=' . $db->Quote( '0' );
		}
		$query	=   'SELECT a.* FROM '
						. $db->quoteName('#__community_groups') . ' AS a '
						. ' INNER JOIN ' . $db->quoteName('#__community_groups_members') . ' AS b '
						. ' ON a.'.$db->quoteName('id').'=b.'.$db->quoteName('groupid')
						. ' AND b.'.$db->quoteName('approved').'=' . $db->Quote( '1' )
						. ' AND b.'.$db->quoteName('permissions').'=' . $db->Quote( '1' )
						. ' AND a.'.$db->quoteName('published').'=' . $db->Quote( '1' )
						. ' AND b.'.$db->quoteName('memberid').'=' . $db->Quote($userId)
						. $extraSQL;

		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		// bind to table
		$data = array();
		foreach($result as $row){
			$groupAdmin	= JTable::getInstance( 'Group' , 'CTable' );
			$groupAdmin->bind( $row );
			$data[] = $groupAdmin;
		}

		if($db->getErrorNum())
		{
					JError::raiseError( 500, $db->stderr());
		}

		return $data;
	}
}