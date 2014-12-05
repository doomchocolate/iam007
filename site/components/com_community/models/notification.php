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

require_once( JPATH_ROOT .'/components/com_community/models/models.php' );

class CommunityModelNotification extends JCCModel
implements CNotificationsInterface
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
	 * Add new notification
	 */

    public function CommunityModelNotification()
    {
        parent::JCCModel();
        global $option;
        $mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		// Get pagination request variables
		$limit		= ($mainframe->getCfg('list_limit') == 0)?5:$mainframe->getCfg('list_limit');
		$limitstart	= $jinput->request->get('limitstart', 0, 'INT'); //JRequest::getVar('limitstart', 0, 'REQUEST');

		if(empty($limitstart))
 	 	{
 	 		$limitstart = $jinput->get('start', 0, 'INT');
 	 	}
		// In case limit has been changed, adjust it
		$limitstart	= ($limit != 0 ? (floor($limitstart/$limit)*$limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Add new notification
	 * @return  true
	 */

	public function add($from, $to , $content, $cmd='', $type='', $params='')
	{
		jimport('joomla.utilities.date');

		$db		= $this->getDBO();
		$date	= JFactory::getDate();
		$config	= CFactory::getConfig();

		$notification		= JTable::getInstance( 'notification' , 'CTable' );
		//respect notification setting
		//filter result by cmd_type
		$validate		= true;
		$user			= CFactory::getUser($to);
		$user_params	= $user->getParams();

		if (!empty($cmd))
		{
			$validate = ($user_params->get($cmd,$config->get($cmd)) == 1 ) ? true : false;
		}
		if($validate)
		{
			$notification->actor	= $from;
			$notification->target	= $to;
			$notification->content	= $content;
			$notification->created	= $date->toSql();
			$notification->params	= ( is_object( $params ) && method_exists( $params , 'toString' ) ) ? $params->toString() : '';
			$notification->cmd_type	= $cmd;
			$notification->type		= $type;

			$notification->store();
		}

		//delete the oldest notification
		$this->deleteOldest($to);
		return true;
	}

	/**
	 * Delete Oldest notification
	 */
	public function deleteOldest($userid,$type='0')
	{
		jimport('joomla.utilities.date');

		$config = CFactory::getConfig();
		$maxNotification = $config->get('maxnotification',20);
		$now = new JDate();

		$db	=$this->getDBO();
		$date = JFactory::getDate();
		$query	= 'DELETE  FROM '. $db->quoteName('#__community_notifications')
				. 'WHERE '.$db->quoteName('type').'=' . $db->Quote( $type )
				. ' AND DATEDIFF(' . $db->Quote($now->toSql()) . ' , ' . $db->quoteName('created') . ') >' . $db->Quote( $maxNotification );
		$db->setQuery( $query );
		$db->query();
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		return true;
	}
	/**
	 * return array of notification items
	 */
	public function getNotification($userid, $type='0',$limit = 10, $since='')
	{
		$db			= $this->getDBO();
		$limit		= ($limit === 0) ? $this->getState('limit') : $limit;
		$limitstart	= $this->getState('limitstart');
		$sinceWhere	= '';

		if(empty($limitstart))
		{
			$limitstart = 0;
		}

		if(!empty($since))
		{
			$sinceWhere = ' AND ' . $db->quoteName('created') . ' >= ' . $db->Quote($since);
			//rule: if no new notification, load the first 5th latest notification
			$query	= 'SELECT COUNT(*)  FROM '. $db->quoteName('#__community_notifications') . ' AS a '
					. 'WHERE a.'.$db->quoteName('target').'=' . $db->Quote( $userid )
					. $sinceWhere
					. ' AND a.'.$db->quoteName('type').'=' . $db->Quote( $type );

			$db->setQuery( $query );
			$total		= $db->loadResult();
			if($total == 0)
			{
				$sinceWhere = '';
				$limit = 5;
			}

		}

		if(!CFactory::getConfig()->get('enablepm')){
			$sinceWhere .=' AND a.'.$db->quoteName('cmd_type'). 'NOT LIKE '.$db->quote('%inbox%');
		}

		$date	= CTimeHelper::getDate(); //we need to compare where both date with offset so that the day diff correctly.

		$query	= 'SELECT *,'
				. ' TO_DAYS('.$db->Quote($date->format('Y-m-d H:i:s', true, false)).') -  TO_DAYS( DATE_ADD(a.' . $db->quoteName('created').', INTERVAL '.$date->getOffset().' HOUR ) ) as _daydiff'
				. ' FROM '. $db->quoteName('#__community_notifications') . ' AS a '
				. 'WHERE a.'.$db->quoteName('target').'=' . $db->Quote( $userid )
				. ' AND a.'.$db->quoteName('type').'=' . $db->Quote( $type )
				. $sinceWhere
				. ' ORDER BY a.'.$db->quoteName('created') . ' DESC';

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
		//Pagination
		$query	= 'SELECT COUNT(*)  FROM '. $db->quoteName('#__community_notifications') . ' AS a '
				. 'WHERE a.'.$db->quoteName('target').'=' . $db->Quote( $userid )
				. $sinceWhere
				. ' AND a.'.$db->quoteName('type').'=' . $db->Quote( $type );
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
	 * return notification count
	 */
	public function getNotificationCount($userid, $type='0',$since='')
	{
		$db	=$this->getDBO();
		$sinceWhere = '';

		if(!empty($since))
		{
			$sinceWhere = ' AND ' . $db->quoteName('created') . ' >= ' . $db->Quote($since);
		}

		if(!CFactory::getConfig()->get('enablepm')){
			$sinceWhere .=' AND '.$db->quoteName('cmd_type'). 'NOT LIKE '.$db->quote('%inbox%');
		}

		$query	= 'SELECT COUNT(*)  FROM '. $db->quoteName('#__community_notifications') . ' AS a '
				. 'WHERE a.'.$db->quoteName('target').'=' . $db->Quote( $userid )
				. $sinceWhere
				. ' AND a.'.$db->quoteName('type').'=' . $db->Quote( $type );

		$db->setQuery( $query );
		$total		= $db->loadResult();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		return $total;
	}
	/**
	 * function get pagination
	 * @return  pagination value
	 */
	public function & getPagination()
	{
		return $this->_pagination;
	}

	/**
	 * Get total notification count
	 * @param  int $userid
	 * @return int notification count
	 */
	public function getTotalNotifications($userid){
		return (int) $this->getNotificationCount($userid);
	}
}
