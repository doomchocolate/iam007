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

/**
 *
 */
class CommunityModelStatus extends JCCModel
{
	/**
	 * Update the user status
	 *
	 * @param	int		user id
	 * @param	string	the message. Should be < 140 char (controller check)
	 */
	public function update($id, $status, $access=0){
		$db	= $this->getDBO();
		$my	= CFactory::getUser();
		// @todo: posted_on should be constructed to make sure we take into account
		// of Joomla server offset

		// Current user and update id should always be the same
		//CError::assert( $my->id, $id, 'eq', __FILE__ , __LINE__ );

		// Trigger onStatusUpdate
		require_once( COMMUNITY_COM_PATH.'/libraries/apps.php' );

		$appsLib	= CAppPlugins::getInstance();
		$appsLib->loadApplications();

		$args 	= array();
		$args[]	= $my->id;			// userid
		$args[]	= $my->getStatus();	// old status
		$args[]	= $status;			// new status
		$appsLib->triggerEvent( 'onProfileStatusUpdate' , $args );

		$today	= JFactory::getDate();
		$data	= new stdClass();
		$data->userid		= $id;
		$data->status		= $status;
		$data->posted_on    = $today->toSql();
		$data->status_access= $access;

		$db->updateObject( '#__community_users' , $data , 'userid' );

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		return $this;
	}

	/**
	 * Get the user status
	 *
	 * @param int	userid
	 *
	 * @todo: this should return the status object. Use Jtable for this
	 */
	public function get($id, $limit=1){
		$db	= $this->getDBO();
		$config		= CFactory::getConfig();

		//enforce user's status privacy
		$andWhere = array();
		$andWhere[] = $db->quoteName('userid').'='. $db->Quote($id);
		if ($config->get('respectactivityprivacy')){
			$my	= CFactory::getUser();
			if($my->id == 0){
				// for guest, it is enough to just test access <= 0
				$andWhere[] = '('.$db->quoteName('status_access').' <= 10)';

			}elseif( ! COwnerHelper::isCommunityAdmin($my->id) )
			{
				$orWherePrivacy = array();
				$orWherePrivacy[] = '(' . $db->quoteName('status_access') .' = 0) ';
				$orWherePrivacy[] = '(' . $db->quoteName('status_access') .' = 10) ';
				$orWherePrivacy[] = '((' . $db->quoteName('status_access') .' = 20) AND ( '.$db->Quote($my->id) .' != 0)) ' ;
				if($my->id != 0)
				{
					$orWherePrivacy[] = '((' . $db->quoteName('status_access') .' = ' . $db->Quote(40).') AND (' . $db->Quote($id) .' = ' . $db->Quote($my->id).')) ' ;
					$orWherePrivacy[] = '((' . $db->quoteName('status_access') .' = ' . $db->Quote(30).') AND ((' . $db->Quote($my->id) .'IN (SELECT c.' . $db->quoteName('connect_to')
							.' FROM ' . $db->quoteName('#__community_connection') .' as c'
							.' WHERE c.' . $db->quoteName('connect_from') .' = ' . $db->Quote($id)
							.' AND c.' . $db->quoteName('status') .' = ' . $db->Quote(1) .' ) ) OR (' . $db->Quote($id) .' = ' . $db->Quote($my->id).') )) ';
				}
				$OrPrivacy = implode(' OR ', $orWherePrivacy);
				$andWhere[] = "(".$OrPrivacy.")";
			}
		}
		$whereAnd = implode(' AND ', $andWhere);
		$sql = 'SELECT * from '.$db->quoteName('#__community_users')
			.' WHERE '. $whereAnd
			.' ORDER BY '.$db->quoteName('posted_on').' DESC LIMIT '.$limit;

		$db->setQuery($sql);
		$result = $db->loadObjectList();

		// Return the first row
		if(!empty($result)){
			$result= $result[0];
		} else {
			$result = new stdClass();
			$result->status = '';
		}


		return $result;
	}

}
