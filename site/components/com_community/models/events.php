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

require_once JPATH_ROOT .'/components/com_community/models/models.php';

jimport('joomla.utilities.date');
class CommunityModelEvents extends JCCModel
	implements CGeolocationSearchInterface, CNotificationsInterface
{
	/**
	 * Configuration data
	 *
	 * @var object    JPagination object
	 **/
	var $_pagination = '';

	/**
	 * Configuration data
	 *
	 * @var object    JPagination object
	 **/
	var $total = '';

	/**
	 * member count data
	 *
	 * @var int
	 **/
	var $membersCount = array();

	/**
	 * Constructor
	 */
	public function CommunityModelEvents()
	{
		parent::JCCModel();

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		// Get pagination request variables
		$limit = ($mainframe->getCfg('list_limit') == 0) ? 5 : $mainframe->getCfg('list_limit');
		$limitstart = $jinput->request->get('limitstart', 0, 'INT'); //JRequest::getVar('limitstart', 0, 'REQUEST');

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
	 * Method to retrieve total events for a specific group
	 *
	 * @param    int        $groupId    The unique group id.
	 * @return    array    $result        An array of result.
	 **/
	public function getTotalGroupEvents($groupId)
	{

		$db = $this->getDBO();

		$pastDate = CTimeHelper::getLocaleDate();

		$extraSQL = ' AND ((' . $db->quoteName('parent') . '=' . $db->quote(0) . ' && (' . $db->quoteName('repeat') . '=' . $db->quote('') . ' || ' . $db->quoteName('repeat') . ' IS NULL)) || (' . $db->quoteName('parent') . '!= 0 && ' . $db->quoteName('repeat') . ' IS NOT NULL))';

		$query = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_events') . ' WHERE '
			. $db->quoteName('type') . '=' . $db->Quote(CEventHelper::GROUP_TYPE) . ' AND '
			. $db->quoteName('contentid') . '=' . $db->Quote($groupId)
			. ' AND ' . $db->quoteName('enddate') . ' > ' . $db->Quote($pastDate->toSql(true))
			. ' AND ' . $db->quoteName('published') . '=' . $db->Quote('1')
			. $extraSQL;
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Method to retrieve events for a specific group
	 *
	 * @param    int        $groupId    The unique group id.
	 * @return    array    $result        An array of result.
	 **/
	public function getGroupEvents($groupId, $limit = 0)
	{

		$db = $this->getDBO();

		$pastDate = CTimeHelper::getLocaleDate();

		// Filtering on repeat event's parent
		$extraSQL = ' AND ((' . $db->quoteName('parent') . '=' . $db->quote(0) . ' && (' . $db->quoteName('repeat') . '=' . $db->quote('') . ' || ' . $db->quoteName('repeat') . ' IS NULL)) || (' . $db->quoteName('parent') . '!= 0 && ' . $db->quoteName('repeat') . ' IS NOT NULL))';

		$query = 'SELECT * FROM ' . $db->quoteName('#__community_events') . ' WHERE '
			. $db->quoteName('type') . '=' . $db->Quote(CEventHelper::GROUP_TYPE) . ' AND '
			. $db->quoteName('contentid') . '=' . $db->Quote($groupId) . ' '
			. ' AND ' . $db->quoteName('enddate') . ' > ' . $db->Quote($pastDate->toSql(true))
			. ' AND ' . $db->quoteName('published') . '=' . $db->Quote('1')
			. $extraSQL
			. ' ORDER BY ' . $db->quoteName('startdate') . ' ASC ';

		if ($limit != 0) {
			$query .= 'LIMIT 0,' . $limit;
		}

		$db->setQuery($query);
		$result = $db->loadObjectList();

		// bind result to JTable
		$data = array();
		foreach($result AS $row) {
			$event = JTable::getInstance('Event','CTable');
			$event->bind($row);
			$data[] = $event;
		}

		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
		}

		//update the event member numbers to exclude the blocked ones
		if (!empty($data)) {
			foreach ($data as $k => $r) {
				$query = "SELECT COUNT(*)
						FROM #__community_events_members a
						JOIN #__users b ON a.memberid=b.id
						WHERE status=1 AND b.block=0 AND eventid=" . $db->Quote($r->id);
				$db->setQuery($query);
				$data[$k]->confirmedcount = $db->loadResult();
			}
		}

		$query = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_events') . ' WHERE '
			. $db->quoteName('type') . '=' . $db->Quote(CEventHelper::GROUP_TYPE) . ' AND '
			. $db->quoteName('contentid') . '=' . $db->Quote($groupId)
			. $extraSQL;

		$db->setQuery($query);
		$total = $db->loadObjectList();

		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
		}

		if (empty($this->_pagination)) {
			$limit = $this->getState('limit');
			$limitstart = $this->getState('limitstart');
			jimport('joomla.html.pagination');

			$this->_pagination = new JPagination($total, $limitstart, $limit);
		}

		return $data;
	}

	/**
	 * Returns an object of events which the user has registered.
	 *
	 * @access    public
	 * @param    string     User's id.
	 * @param    string     sorting criteria.
	 * @returns array  An objects of event fields.
	 */
	public function getEvents($categoryId = null, $userId = null, $sorting = null, $search = null, $hideOldEvent = true, $showOnlyOldEvent = false, $pending = null, $advance = null, $type = CEventHelper::ALL_TYPES, $contentid = 0, $limit = null)
	{
		$db = $this->getDBO();
		$join = '';
		$extraSQL = '';

		if (!empty($userId)) {
			$join = 'LEFT JOIN ' . $db->quoteName('#__community_events_members') . ' AS b ON a.' . $db->quoteName('id') . '=b.' . $db->quoteName('eventid');
			$extraSQL .= ' AND b.' . $db->quoteName('memberid') . '=' . $db->Quote($userId)
				. 'AND b.' . $db->quoteName('status') . '<' . $db->Quote(2);
		}

		if (!empty($search)) {
			$extraSQL .= ' AND a.' . $db->quoteName('title') . ' LIKE ' . $db->Quote('%' . $search . '%');
		}

		if (!empty($categoryId) && $categoryId != 0) {
			if (is_array($categoryId)) {
				$categoryIds = implode(',', $categoryId);
				$extraSQL .= ' AND a.' . $db->quoteName('catid') . ' IN(' . $categoryIds . ')';
			} else {
				$extraSQL .= ' AND a.' . $db->quoteName('catid') . '=' . $db->Quote($categoryId);
			}
		}

		if (!is_null($pending) && !empty($userId)) {
			$extraSQL .= ' AND b.' . $db->quoteName('status') . '=' . $db->Quote($pending);
		}

		/* Begin : ADVANCE SEARCH */
		if (!empty($advance)) {
			if (!empty($advance['startdate'])) {
				$startDate = CTimeHelper::getDate(strtotime($advance['startdate']));

				$extraSQL .= ' AND a.' . $db->quoteName('startdate') . ' >= ' . $db->Quote($startDate->toSql());

			} else if (!isset($advance['date'])) // If empty, don't select the past event
			{
				$now = CTimeHelper::getDate();
				$extraSQL .= ' AND a.' . $db->quoteName('enddate') . ' >= ' . $db->Quote($now->toSql());
			}

			if (!empty($advance['date'])) { // to get event within this date
				$between_date = date('Ymd', strtotime($advance['date']));
				$extraSQL .= ' AND DATE_FORMAT(a.' . $db->quoteName('startdate') . ',"%Y%m%d") <= ' . $db->Quote($between_date) .
					' AND DATE_FORMAT(a.' . $db->quoteName('enddate') . ',"%Y%m%d") >= ' . $db->Quote($between_date);

				$hideOldEvent = false; //show old event as well
			}

			if (!empty($advance['enddate'])) {
				$endDate = CTimeHelper::getDate(strtotime($advance['enddate']));

				$extraSQL .= ' AND a.' . $db->quoteName('startdate') . ' <= ' . $db->Quote($endDate->toSql());
			}

			/* Begin : SEARCH WITHIN */
			if (!empty($advance['radius']) && !empty($advance['fromlocation'])) {

				$longitude = null;
				$latitude = null;


				$data = CMapping::getAddressData($advance['fromlocation']);

				if ($data) {
					if ($data->status == 'OK') {
						$latitude = (float)$data->results[0]->geometry->location->lat;
						$longitude = (float)$data->results[0]->geometry->location->lng;
					}
				}

				$now = new JDate();

				$lng_min = $longitude - $advance['radius'] / abs(cos(deg2rad($latitude)) * 69);
				$lng_max = $longitude + $advance['radius'] / abs(cos(deg2rad($latitude)) * 69);
				$lat_min = $latitude - ($advance['radius'] / 69);
				$lat_max = $latitude + ($advance['radius'] / 69);

				$extraSQL .= ' AND a.' . $db->quoteName('longitude') . ' > ' . $db->quote($lng_min)
					. ' AND a.' . $db->quoteName('longitude') . ' < ' . $db->quote($lng_max)
					. ' AND a.' . $db->quoteName('latitude') . ' > ' . $db->quote($lat_min)
					. ' AND a.' . $db->quoteName('latitude') . ' < ' . $db->quote($lat_max);

			}

			if (!empty($advance['parent'])) {
				$extraSQL .= ' AND a.' . $db->quoteName('parent') . ' = ' . $db->Quote($advance['parent']);

			}

			if (!empty($advance['id'])) {
				if (is_array($advance['id']) && count($advance['id']) > 0) {
					$ids = implode(',', $advance['id']);
					$extraSQL .= ' AND a.' . $db->quoteName('id') . 'IN (' . $ids . ')';
				}
			}
			/* End : SEARCH WITHIN */
		}
		/* End : ADVANCE SEARCH */

		$limitstart = $this->getState('limitstart');
		$limit = $limit === null ? $this->getState('limit') : $limit;

		if ($type != CEventHelper::ALL_TYPES) {
			$extraSQL .= ' AND a.' . $db->quoteName('type') . '=' . $db->Quote($type);

			if (is_array($contentid)) {
				$contentids = implode(',', $contentid);
				$extraSQL .= ' AND a.' . $db->quoteName('contentid') . ' IN(' . $contentids . ')';
			} elseif ($contentid > 0) {
				$extraSQL .= ' AND a.' . $db->quoteName('contentid') . '=' . $contentid;
			}
		}

		if ($type == CEventHelper::GROUP_TYPE || $type == CEventHelper::ALL_TYPES) {
			// @rule: Respect group privacy
			$join .= ' LEFT JOIN ' . $db->quoteName('#__community_groups') . ' AS g';
			$join .= ' ON g.' . $db->quoteName('id') . ' = a.' . $db->quoteName('contentid');

			if ($type != CEventHelper::GROUP_TYPE) {
				$extraSQL .= ' AND (g.' . $db->quoteName('approvals') . ' = ' . $db->Quote('0') . ' OR g.' . $db->quoteName('approvals') . ' IS NULL';

				if (!empty($userId)) {
					$extraSQL .= ' OR b.' . $db->quoteName('memberid') . '=' . $db->Quote($userId);
				}
				$extraSQL .= ')';
			}
		}

		$orderBy = '';
		$total = 0;

		switch ($sorting) {
			case 'latest':
				if (empty($orderBy))
					$orderBy = ' ORDER BY a.' . $db->quoteName('created') . ' DESC';
				break;
			case 'alphabetical':
				if (empty($orderBy))
					$orderBy = ' ORDER BY a.' . $db->quoteName('title') . ' ASC';
				break;
			case 'startdate':
				if (empty($orderBy))
					$orderBy = ' ORDER BY a.startdate ASC';
				break;
			default:
				$orderBy = ' ORDER BY a.' . $db->quoteName('startdate') . ' ASC';
				break;
		}

		$now = new JDate();


		$CTimeHelper = new CTimeHelper();
		$pastDate = $CTimeHelper->getLocaleDate();

		if ($hideOldEvent) {
			$extraSQL .= ' AND a.' . $db->quoteName('enddate') . ' > ' . $db->Quote($pastDate->format('Y-m-d H:i:s', true, false));
		}

		if ($showOnlyOldEvent) {
			$extraSQL .= ' AND a.' . $db->quoteName('enddate') . ' < ' . $db->Quote($pastDate->format('Y-m-d H:i:s', true, false));
		}

		// Filtering on repeat event's parent
		$extraSQL .= ' AND ((a.' . $db->quoteName('parent') . '=' . $db->quote(0) . ' && (a.' . $db->quoteName('repeat') . '=' . $db->quote('') . ' || a.' . $db->quoteName('repeat') . ' IS NULL)) || (a.' . $db->quoteName('parent') . '!= 0 && a.' . $db->quoteName('repeat') . ' IS NOT NULL))';

		$limit = empty($limit) ? 0 : $limit;

		$query = 'SELECT DISTINCT a.* FROM '
			. $db->quoteName('#__community_events') . ' AS a '
			. $join
			. 'WHERE a.' . $db->quoteName('published') . '=' . $db->Quote('1')
			. $extraSQL
			. $orderBy
			. ' LIMIT ' . $limitstart . ', ' . $limit;

		$db->setQuery($query);
		$result = $db->loadObjectList();

		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
		}

		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
		}

		$query = 'SELECT COUNT(DISTINCT(a.' . $db->quoteName('id') . ')) FROM ' . $db->quoteName('#__community_events') . ' AS a '
			. $join
			. 'WHERE a.' . $db->quoteName('published') . '=' . $db->Quote('1') . ' '
			. $extraSQL;

		$db->setQuery($query);
		$total = $db->loadResult();

		$this->total = $total;
		
		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
		}

		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');

			$this->_pagination = new JPagination($total, $limitstart, $limit);
		}

		return $result;
	}


	/**
	 * Return an array of ids the user responded to
	 * @param type $userid
	 * @return type
	 */
	public function getEventIds($userId)
	{
		$db = $this->getDBO();
		$query = 'SELECT DISTINCT a.' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__community_events') . ' AS a '
			. ' LEFT JOIN ' . $db->quoteName('#__community_events_members') . ' AS b '
			. ' ON a.' . $db->quoteName('id') . '=b.' . $db->quoteName('eventid')
			. ' WHERE '
			. ' ( '
			. '   b.' . $db->quoteName('status') . '=' . $db->Quote('1')
			. '		OR '
			. '	  b.' . $db->quoteName('status') . '=' . $db->Quote('2')
			. '		OR '
			. '	  b.' . $db->quoteName('status') . '=' . $db->Quote('3')
			. ' ) '
			. ' AND b.memberid=' . $db->Quote($userId);

		$db->setQuery($query);
		$eventsid = $db->loadColumn();

		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
		}

		return $eventsid;
	}

	/**
	 * Return the number of groups count for specific user
	 **/
	public function getEventsCount($userId)
	{
		// guest obviously has no group
		if ($userId == 0)
			return 0;

		$now = JFactory::getDate();
		$db = $this->getDBO();
		$query = 'SELECT COUNT(*) FROM '
			. $db->quoteName('#__community_events_members') . ' AS a '
			. ' INNER JOIN ' . $db->quoteName('#__community_events') . ' AS b '
			. ' ON b.' . $db->quoteName('id') . '=a.' . $db->quoteName('eventid')
			. ' AND b.' . $db->quoteName('enddate') . ' > ' . $db->Quote($now->toSql())
			. ' WHERE a.' . $db->quoteName('memberid') . '=' . $db->Quote($userId) . ' '
			. ' AND a.' . $db->quoteName('status') . ' IN (' . $db->Quote(COMMUNITY_EVENT_STATUS_ATTEND) . ',' . $db->Quote(COMMUNITY_EVENT_STATUS_INVITED) . ')';

		$db->setQuery($query);
		$count = $db->loadResult();

		return $count;
	}

	/**
	 * Return the number of groups cretion count for specific user
	 **/
	public function getEventsCreationCount($userId)
	{
		// guest obviously has no events
		if ($userId == 0)
			return 0;

		$db = $this->getDBO();

		$query = 'SELECT COUNT(*) FROM '
			. $db->quoteName('#__community_events') . ' '
			. 'WHERE ' . $db->quoteName('creator') . '=' . $db->Quote($userId)
			. ' AND ' . $db->quoteName('parent') . '=' . $db->Quote(0);
		$db->setQuery($query);

		$count = $db->loadResult();

		return $count;
	}

	/**
	 * Returns the count of the members of a specific group
	 *
	 * @access    public
	 * @param    string     Group's id.
	 * @return    int    Count of members
	 */
	public function getMembersCount($id)
	{
		$db = $this->getDBO();

		if (!isset($this->membersCount[$id])) {
			$query = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_events_members') . ' '
				. 'WHERE ' . $db->quoteName('eventid') . '=' . $db->Quote($id) . ' '
				. 'AND ' . $db->quoteName('status') . ' IN (' . COMMUNITY_EVENT_STATUS_INVITED . ',' . COMMUNITY_EVENT_STATUS_ATTEND . ',' . COMMUNITY_EVENT_STATUS_MAYBE . ')';

			$db->setQuery($query);
			$this->membersCount[$id] = $db->loadResult();

			if ($db->getErrorNum()) {
				JError::raiseError(500, $db->stderr());
			}
		}

		return $this->membersCount[$id];
	}

	/**
	 * Loads the categories
	 *
	 * @access    public
	 * @returns Array  An array of categories object
	 */
	public function getCategories($type = CEventHelper::PROFILE_TYPE, $catId = COMMUNITY_ALL_CATEGORIES)
	{
		$db = $this->getDBO();
		$where = '';
		$join = '';

		if ($catId !== COMMUNITY_ALL_CATEGORIES) {
			if ($catId === COMMUNITY_NO_PARENT) {
				$where = 'WHERE a.' . $db->quoteName('parent') . '=' . $db->Quote(COMMUNITY_NO_PARENT) . ' ';
			} else {
				$where = 'WHERE a.' . $db->quoteName('parent') . '=' . $db->Quote($catId) . ' ';
			}
		}

		if ($type != CEventHelper::ALL_TYPES) {
			$where .= ' AND b.' . $db->quoteName('type') . '=' . $db->Quote($type) . ' ';
		} else {
			// @rule: Respect group privacy
			$join = ' LEFT JOIN ' . $db->quoteName('#__community_groups') . ' AS g';
			$join .= ' ON b.' . $db->quoteName('contentid') . ' = g.' . $db->quoteName('id');
			$where .= ' AND (g.' . $db->quoteName('approvals') . ' = ' . $db->Quote(0) . ' OR g.' . $db->quoteName('approvals') . ' IS NULL) ';
		}

		$now = new JDate();
		$query = 'SELECT a.*, COUNT(b.' . $db->quoteName('id') . ') AS count '
			. 'FROM ' . $db->quoteName('#__community_events_category') . ' AS a '
			. ' LEFT OUTER JOIN ' . $db->quoteName('#__community_events') . ' AS b '
			. ' ON a.' . $db->quoteName('id') . '=b.' . $db->quoteName('catid')
			. ' AND b.' . $db->quoteName('enddate') . ' > ' . $db->Quote($now->toSql())
			. ' AND b.' . $db->quoteName('published') . '=' . $db->Quote('1')
			. $join
			. $where
			. 'GROUP BY a.' . $db->quoteName('id') . ' ORDER BY a.' . $db->quoteName('name') . ' ASC';

		$db->setQuery($query);
		$result = $db->loadObjectList();

		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
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
		$db = $this->getDBO();

		$query = 'SELECT * FROM ' . $db->quoteName('#__community_events_category');

		$db->setQuery($query);
		$result = $db->loadObjectList();

		// bind data to table
		$data = array();
		foreach($result AS $row) {
			$eventCat =	JTable::getInstance('EventCategory', 'CTable');
			$eventCat->bind($row);
			$data[] = $eventCat;
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
		$db = $this->getDBO();
		$now = new JDate();

		$query = "SELECT c.id, c.parent, c.name, count(e.id) AS total, c.description
				  FROM " . $db->quoteName('#__community_events_category') . " AS c
				  LEFT JOIN " . $db->quoteName('#__community_events') . " AS e ON e.catid = c.id
							AND e." . $db->quoteName('published') . "=" . $db->Quote('1') . "
							AND	e." . $db->quoteName('enddate') . " > " . $db->Quote($now->toSql()) . "
				  GROUP BY c.id
				  ORDER BY c.name";

		$db->setQuery($query);
		$result = $db->loadObjectList('id');

		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
		}

		return $result;
	}

	/**
	 * Returns the category name of the specific category
	 *
	 * @access public
	 * @param    string Category Id
	 * @returns string    Category name
	 **/
	public function getCategoryName($categoryId)
	{
		CError::assert($categoryId, '', '!empty', __FILE__, __LINE__);
		$db = $this->getDBO();

		$query = 'SELECT ' . $db->quoteName('name') . ' '
			. 'FROM ' . $db->quoteName('#__community_events_category') . ' '
			. 'WHERE ' . $db->quoteName('id') . '=' . $db->Quote($categoryId);
		$db->setQuery($query);

		$result = $db->loadResult();

		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
		}

		CError::assert($result, '', '!empty', __FILE__, __LINE__);

		return $result;
	}

	/**
	 * Return the number of groups count for specific user
	 **/
	public function getEventLastChild($parent)
	{
		if ($parent == 0)
			return 0;

		$db = $this->getDBO();
		$query = 'SELECT id'
			. ' FROM ' . $db->quoteName('#__community_events')
			. ' WHERE ' . $db->quoteName('parent') . '=' . $db->Quote($parent)
			. ' ORDER BY id DESC'
			. ' LIMIT 1';

		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Check if the given group name exist.
	 * if id is specified, only search for those NOT within $id
	 */
	public function isEventExist($title, $location, $startdate, $enddate, $id = 0, $parent = 0)
	{
		$db = $this->getDBO();

		$starttime = CTimeHelper::getDate($startdate);
		$endtime = CTimeHelper::getDate($enddate);

		$strSQL = 'SELECT count(*) FROM ' . $db->quoteName('#__community_events')
			. ' WHERE ' . $db->quoteName('title') . ' = ' . $db->Quote($title)
			. ' AND ' . $db->quoteName('location') . ' = ' . $db->Quote($location)
			. ' AND ' . $db->quoteName('startdate') . ' = ' . $db->Quote($starttime->toSql())
			. ' AND ' . $db->quoteName('enddate') . ' = ' . $db->Quote($endtime->toSql())
			. ' AND ' . $db->quoteName('id') . ' != ' . $db->Quote($id)
			. ' AND ' . $db->quoteName('id') . ' != ' . $db->Quote($parent)
			. ' AND ' . $db->quoteName('parent') . '=' . $db->Quote(0);

		$db->setQuery($strSQL);
		$result = $db->loadResult();

		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
		}

		return $result;
	}

	/**
	 * Delete group's wall
	 *
	 * param    string    id The id of the group.
	 *
	 **/
	public function deleteGroupWall($gid)
	{
		$db = JFactory::getDBO();

		$sql = 'DELETE FROM ' . $db->quoteName("#__community_wall") . "
				WHERE
						" . $db->quoteName("contentid") . " = " . $db->quote($gid) . " AND
						" . $db->quoteName("type") . " = " . $db->quote('groups');
		$db->setQuery($sql);
		$db->Query();

		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
		}

		return true;
	}

	/* Implement interfaces */

	/**
	 * caller should verify that the address is valid
	 */
	public function searchWithin($address, $distance)
	{
		$db = JFactory::getDBO();

		$longitude = null;
		$latitude = null;


		$data = CMapping::getAddressData($address);

		if ($data) {
			if ($data->status == 'OK') {
				$latitude = (float)$data->results[0]->geometry->location->lat;
				$longitude = (float)$data->results[0]->geometry->location->lng;
			}
		}

		if (is_null($latitude) || is_null($longitude)) {
			return null;
		}

		/*
		 * code from
		 * http://blog.fedecarg.com/2009/02/08/geo-proximity-search-the-haversine-equation/
		 */

		$radius = 20; // in miles

		$lng_min = $longitude - $radius / abs(cos(deg2rad($latitude)) * 69);
		$lng_max = $longitude + $radius / abs(cos(deg2rad($latitude)) * 69);
		$lat_min = $latitude - ($radius / 69);
		$lat_max = $latitude + ($radius / 69);

		$now = new JDate();
		$sql = "SELECT * FROM "
			. $db->quoteName("#__community_events")
			. " WHERE " . $db->quoteName("longitude") . " > " . $db->quote($lng_min)
			. " AND " . $db->quoteName("longitude") . " < " . $db->quote($lng_max)
			. " AND " . $db->quoteName("latitude") . " > " . $db->quote($lat_min)
			. " AND " . $db->quoteName("latitude") . " < " . $db->quote($lat_max)
			. " AND " . $db->quoteName("enddate") . " > " . $db->quote($now->toSql());

		$db->setQuery($sql);
		$results = $db->loadObjectList();

		return $results;
	}

	/**
	 *    Get the pending invitations
	 */
	public function getPending($userId)
	{
		if ($userId == 0) {
			return null;
		}

		$limit = $this->getState('limit');
		$limitstart = $this->getState('limitstart');

		$db = JFactory::getDBO();

		$query = 'SELECT a.*, b.' . $db->quoteName('title') . ', b.' . $db->quoteName('thumb')
			. ' FROM ' . $db->quoteName("#__community_events_members") . ' AS a, '
			. $db->quoteName("#__community_events") . ' AS b'
			. ' WHERE a.' . $db->quoteName('memberid') . '=' . $db->Quote($userId)
			. ' AND a.' . $db->quoteName('eventid') . '=b.' . $db->quoteName('id')
			. ' AND b.' . $db->quoteName('published') . '=' . $db->Quote(1)
			. ' AND a.' . $db->quoteName('status') . '=' . $db->Quote(COMMUNITY_EVENT_STATUS_INVITED)
			. ' AND b.' . $db->quoteName('enddate') . '>= NOW()'
			. ' ORDER BY a.' . $db->quoteName('id') . ' DESC'
			. " LIMIT {$limitstart}, {$limit}";

		$db->setQuery($query);

		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
		}

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Check if I was invited and if yes return true
	 * If Event Id is provided, will return the invitation informations
	 *
	 */
	public function isInvitedMe($invitationId = 0, $userId = 0, $eventId = 0)
	{
		$db = $this->getDBO();

		if ($eventId == 0) {
			$query = "SELECT COUNT(*) FROM "
				. $db->quoteName("#__community_events_members")
				. " WHERE " . $db->quoteName("id") . "=" . $db->Quote($invitationId)
				. " AND " . $db->quoteName("memberid") . "=" . $db->Quote($userId)
				. " AND " . $db->quoteName("status") . "=" . $db->Quote(COMMUNITY_EVENT_STATUS_INVITED);

			$db->setQuery($query);

			$status = ($db->loadResult() > 0) ? true : false;

			if ($db->getErrorNum()) {
				JError::raiseError(500, $db->stderr());
			}

			return $status;
		} else {
			$query = "SELECT * FROM "
				. $db->quoteName("#__community_events_members")
				. " WHERE " . $db->quoteName("memberid") . "=" . $db->Quote($userId)
				. " AND " . $db->quoteName("eventid") . "=" . $db->Quote($eventId)
				. " AND " . $db->quoteName("status") . "=" . $db->Quote(COMMUNITY_EVENT_STATUS_INVITED)
				. " AND " . $db->quoteName("invited_by") . "!=" . $db->Quote($userId)
				. " AND " . $db->quoteName("invited_by") . "!=" . $db->Quote(0);

			$db->setQuery($query);

			$result = $db->loadObjectList();

			// bind data to table
			$data = array();
			foreach($result AS $row) {
				$member = JTable::getInstance('EventMembers', 'CTable');
				$member->bind($row);
				$data[] = $member;
			}

			return $data;
		}
	}

	/**
	 * Return the count of the user's friend of a specific event
	 */
	public function getFriendsCount($userid, $eventid)
	{
		$db = $this->getDBO();

		$query = 'SELECT COUNT(DISTINCT(a.' . $db->quoteName('connect_to') . ')) AS id  FROM ' . $db->quoteName('#__community_connection') . ' AS a '
			. ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
			. ' INNER JOIN ' . $db->quoteName('#__community_events_members') . ' AS c '
			. ' ON a.' . $db->quoteName('connect_from') . '=' . $db->Quote($userid)
			. ' AND a.' . $db->quoteName('connect_to') . '=b.' . $db->quoteName('id')
			. ' AND c.' . $db->quoteName('eventid') . '=' . $db->Quote($eventid) . ' '
			. ' AND a.' . $db->quoteName('connect_to') . '=c.' . $db->quoteName('memberid')
			. ' AND a.' . $db->quoteName('status') . '=' . $db->Quote('1') . ' '
			. ' AND c.' . $db->quoteName('status') . '=' . $db->Quote('1');

		$db->setQuery($query);

		$total = $db->loadResult();

		return $total;
	}

	public function getInviteListByName($namePrefix, $userid, $cid, $limitstart = 0, $limit = 8)
	{
		$db = $this->getDBO();

		$andName = '';
		$config = CFactory::getConfig();
		$nameField = $config->getString('displayname');

		if (!empty($namePrefix)) {
			$andName = ' AND b.' . $db->quoteName($nameField) . ' LIKE ' . $db->Quote('%' . $namePrefix . '%');
		}

		$query = 'SELECT DISTINCT(a.' . $db->quoteName('connect_to') . ') AS id  FROM ' . $db->quoteName('#__community_connection') . ' AS a '
			. ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
			. ' ON a.' . $db->quoteName('connect_from') . '=' . $db->Quote($userid)
			. ' AND a.' . $db->quoteName('connect_to') . '=b.' . $db->quoteName('id')
			. ' AND a.' . $db->quoteName('status') . '=' . $db->Quote('1')
			. ' AND b.' . $db->quoteName('block') . '=' . $db->Quote('0')
			. ' WHERE NOT EXISTS ( SELECT d.' . $db->quoteName('blocked_userid') . ' as id'
			. ' FROM ' . $db->quoteName('#__community_blocklist') . ' AS d  '
			. ' WHERE d.' . $db->quoteName('userid') . ' = ' . $db->Quote($userid)
			. ' AND d.' . $db->quoteName('blocked_userid') . ' = a.' . $db->quoteName('connect_to') . ')'
			. ' AND NOT EXISTS (SELECT e.' . $db->quoteName('memberid') . ' as id'
			. ' FROM ' . $db->quoteName('#__community_events_members') . ' AS e  '
			. ' WHERE e.' . $db->quoteName('eventid') . ' = ' . $db->Quote($cid)
			. ' AND e.' . $db->quoteName('status') . ' = ' . $db->Quote(COMMUNITY_EVENT_STATUS_ATTEND)
			. ' AND e.' . $db->quoteName('memberid') . ' = a.' . $db->quoteName('connect_to')
			. ')'
			. $andName
			. ' ORDER BY b.' . $db->quoteName($nameField)
			. ' LIMIT ' . $limitstart . ',' . $limit;

		$db->setQuery($query);
		$friends = $db->loadColumn();

		//calculate total
		$query = 'SELECT COUNT(DISTINCT(a.' . $db->quoteName('connect_to') . '))  FROM ' . $db->quoteName('#__community_connection') . ' AS a '
			. ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
			. ' ON a.' . $db->quoteName('connect_from') . '=' . $db->Quote($userid)
			. ' AND a.' . $db->quoteName('connect_to') . '=b.' . $db->quoteName('id')
			. ' AND a.' . $db->quoteName('status') . '=' . $db->Quote('1')
			. ' AND b.' . $db->quoteName('block') . '=' . $db->Quote('0')
			. ' WHERE NOT EXISTS ( SELECT d.' . $db->quoteName('blocked_userid') . ' as id'
			. ' FROM ' . $db->quoteName('#__community_blocklist') . ' AS d  '
			. ' WHERE d.' . $db->quoteName('userid') . ' = ' . $db->Quote($userid)
			. ' AND d.' . $db->quoteName('blocked_userid') . ' = a.' . $db->quoteName('connect_to') . ')'
			. ' AND NOT EXISTS (SELECT e.' . $db->quoteName('memberid') . ' as id'
			. ' FROM ' . $db->quoteName('#__community_events_members') . ' AS e  '
			. ' WHERE e.' . $db->quoteName('eventid') . ' = ' . $db->Quote($cid)
			. ' AND e.' . $db->quoteName('status') . ' = ' . $db->Quote(COMMUNITY_EVENT_STATUS_ATTEND)
			. ' AND e.' . $db->quoteName('memberid') . ' = a.' . $db->quoteName('connect_to')
			. ')'
			. $andName;

		$db->setQuery($query);
		$this->total = $db->loadResult();

		return $friends;
	}

	/**
	 * Return the title of the event id
	 */
	public function getTitle($eventid)
	{
		$db = $this->getDBO();

		$query = 'SELECT ' . $db->quoteName('title') . ' FROM ' . $db->quoteName('#__community_events')
			. " WHERE " . $db->quoteName("id") . "=" . $db->Quote($eventid);

		$db->setQuery($query);

		$title = $db->loadResult();

		return $title;
	}

	/**
	 * Count total pending event invitations.
	 *
	 **/
	public function countPending($id)
	{
		$db =  $this->getDBO();

		$query = 'SELECT COUNT(*) FROM '
			. $db->quoteName('#__community_events_members') . ' AS a '
			. ' INNER JOIN ' . $db->quoteName('#__community_events') . ' AS b '
			. ' ON b.' . $db->quoteName('id') . '=a.' . $db->quoteName('eventid')
			. ' AND b.' . $db->quoteName('published') . '=' . $db->Quote(1)
			. ' WHERE a.' . $db->quoteName('memberid') . '=' . $db->Quote($id)
			. ' AND a.' . $db->quoteName('status') . '=' . $db->Quote(COMMUNITY_EVENT_STATUS_INVITED) . ' '
			. ' AND b.' . $db->quoteName('enddate') . '>= NOW()'
			. ' ORDER BY a.' . $db->quoteName('id') . ' DESC';

		$db->setQuery($query);

		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
		}

		return $db->loadResult();
	}

	/*
	 * get days with event within the month
	 */
	public function getMonthlyEvents($month, $year)
	{
		$db = $this->getDBO();
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$gid = $jinput->request->get('groupid', '', 'INT'); //only display
		$query = '';
		//since 2.6, only display group events if groupid param exist in the GET
		if ($gid) {
			$query = "	SELECT DISTINCT DATE( " . $db->quoteName('startdate') . " ) AS date_start,
						DATE( " . $db->quoteName('enddate') . " ) AS date_end
						FROM #__community_events
						WHERE DATE_FORMAT( " . $db->quoteName('startdate') . ", '%Y%c' ) = " . $db->Quote($year . $month)
				. " AND " . $db->quoteName('contentid') . "=" . $db->Quote($gid)
				. " AND " . $db->quoteName('published') . "=" . $db->Quote(1);
		} else {
			$query = "	SELECT DISTINCT DATE( " . $db->quoteName('startdate') . " ) AS date_start,
						DATE( " . $db->quoteName('enddate') . " ) AS date_end
						FROM #__community_events
						WHERE DATE_FORMAT( " . $db->quoteName('startdate') . ", '%Y%c' ) = " . $db->Quote($year . $month)
						."OR DATE_FORMAT( " . $db->quoteName('enddate') . ", '%Y%c' ) = " . $db->Quote($year . $month)
				. " AND " . $db->quoteName('published') . "=" . $db->Quote(1);
		}
		$db->setQuery($query);

		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
		}

		return $db->loadObjectList();
	}

	/**
	 * @deprecated Since 2.0
	 */
	public function getThumbAvatar($id, $thumb)
	{

		$thumb = CUrlHelper::avatarURI($thumb, 'event_thumb.png');

		return $thumb;
	}

	/**
	 * Return events search total
	 */
	public function getEventsSearchTotal()
	{
		return $this->total;
	}

	/**
	 * Returns a list of pending event invites
	 *
	 * @param    int    $userId    The number of event invites to lookup for this user.
	 *
	 * @return    int Total number of invites
	 **/
	public function getTotalNotifications($user)
	{
		if($user->_cparams->get('notif_events_invite') == 1)
		{
			return (int)$this->countPending($user->id);
		}

		return 0;
	}

	/**
	 * Delete event child
	 * @param array    $id
	 **/
	public function deleteExpiredEvent($id)
	{
		$db = JFactory::getDBO();

		$id = is_array($id) ? implode(',', $id) : $id;

		$sql = 'DELETE
				FROM ' . $db->quoteName('#__community_events') . '
				WHERE ' . $db->quoteName('id') . ' IN (' . $id . ')';

		$db->setQuery($sql);
		$db->Query();

		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
		}

		return true;
	}

	/**
	 * Get event child
	 *
	 * @param int    $parent
	 * @param array    filtering condition
	 * @return array/object
	 **/
	public function getEventChilds($parent, $advance)
	{
		if ($parent == 0)
			return 0;

		$db = $this->getDBO();

		$extraSQL = '';

		// Having id greater than
		if (isset($advance['id']) && $advance['id'] > 0) {
			$extraSQL .= ' AND ' . $db->quoteName('id') . '> ' . $db->Quote($advance['id']);
		}

		// Having id greater than or same
		if (isset($advance['eventid']) && $advance['eventid'] > 0) {
			$extraSQL .= ' AND ' . $db->quoteName('id') . '>= ' . $db->Quote($advance['eventid']);
		}

		// Having diffent id
		if (isset($advance['exclude'])) {
			$extraSQL .= ' AND ' . $db->quoteName('id') . ' NOT IN (' . $db->Quote($advance['exclude']) . ')';
		}

		// non expired event
		if (isset($advance['expired']) && !$advance['expired']) {
			$pastDate = CTimeHelper::getLocaleDate();
			$extraSQL .= ' AND ' . $db->quoteName('enddate') . ' > ' . $db->Quote($pastDate->toSql(true));
		}

		// Published
		if (isset($advance['published'])) {
			$extraSQL .= ' AND ' . $db->quoteName('published') . '= ' . $db->Quote($advance['published']);
		}

		$limit = '';
		if (isset($advance['limit']) && $advance['limit'] > 0) {
			$limit = ' LIMIT ' . $advance['limit'];
		}

		$query = 'SELECT * '
			. ' FROM ' . $db->quoteName('#__community_events')
			. ' WHERE ' . $db->quoteName('parent') . '=' . $db->Quote($parent)
			. $extraSQL
			. ' ORDER BY id ASC'
			. $limit;

		$db->setQuery($query);

		if (isset($advance['return']) && $advance['return'] == 'object') {
			$result = $db->loadObjectList();
		} else {
			$result = $db->loadAssocList();
		}

		return $result;
	}

	/**
	 * Get event child count
	 *
	 * @param int    $parent
	 * @return int total
	 *
	 **/
	public function getEventChildsCount($parent)
	{
		$db =  $this->getDBO();

		$pastDate = CTimeHelper::getLocaleDate();

		$query = 'SELECT COUNT(*)
			      FROM ' . $db->quoteName('#__community_events') . '
				  WHERE ' . $db->quoteName('parent') . '=' . $db->Quote($parent) . '
				  AND ' . $db->quoteName('enddate') . ' > ' . $db->Quote($pastDate->toSql(true)) . '
				  AND ' . $db->quoteName('published') . '=' . $db->Quote(1);

		$db->setQuery($query);

		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
		}

		return $db->loadResult();
	}

	public function updateRecurringImage($path, $type, $parent, $id)
	{
		$db = $this->getDBO();

		$query = ' UPDATE ' . $db->quoteName('#__community_events') .
			' SET ' . $db->quoteName($type) . ' = ' . $db->Quote($path) .
			' WHERE ' . $db->quoteName('parent') . ' = ' . $db->Quote($parent) .
			' AND' . $db->quoteName('id') . ' > ' . $db->Quote($id);

		$db->setQuery($query);
		$db->query();

		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
		}

		return $this;
	}

	public function isImageInUsed($path, $type, $id, $series = false)
	{
		$db = $this->getDBO();
		$extra = '';

		if ($series) {
			$extra = ' AND ' . $db->quoteName('id') . ' < ' . $db->Quote($id);
		}

		$strSQL =  'SELECT count(*)
				    FROM  ' . $db->quoteName('#__community_events') . '
					WHERE ' . $db->quoteName($type) . ' = ' . $db->Quote($path) . ' AND '
					. $db->quoteName('id') . ' != ' . $db->Quote($id) . ' AND '
					. $db->quoteName('published') . ' != ' . $db->Quote(2)
					. $extra .
					' LIMIT 1';

		$db->setQuery($strSQL);
		$result = $db->loadResult();

		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->stderr());
		}

		return $result;
	}
}

