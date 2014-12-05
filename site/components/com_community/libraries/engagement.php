<?php
defined('JPATH_PLATFORM') or die;

class CTableEngagement extends JTable
{
	var $id      = null;
	var $action  = null;
	var $user_id = null;
	var $created = null;
	var $week    = null;

	public function __construct( &$db )
	{
		parent::__construct( '#__community_engagement', 'id', $db );
	}
}

class CEngagement 
{
	public static function log($action, $user_id = null)
	{
		$table          = JTable::getInstance('', 'CTableEngagement');
		$table->action  = $action;
		$table->user_id = $user_id;
		
		$date           = new JDate();
		$table->created = $date->toSql();
		$table->week    = $date->format('W');
		
		$table->store();
	}
		
	public static function getData($actions, $time, $user_id = null, $range = null)
	{		
		$db = JFactory::getDbo();
		$actions = "'". implode("','", $actions). "'";

		switch ($time)
		{
			case 'week':
				$query = 'SELECT DATE('.$db->quoteName('created').') as created, COUNT('.$db->quoteName('created').') as count FROM '.$db->quoteName('#__community_engagement')
				.' WHERE ' . $db->quoteName('action') ." IN (". $actions .") "
				.' AND WEEK('.$db->quoteName('created').') = WEEK(curdate())'
				.' GROUP BY DATE('.$db->quoteName('created').')';
				break;

			case 'lastweek':
				$query = 'SELECT DATE('.$db->quoteName('created').') as created, COUNT('.$db->quoteName('created').') as count FROM '.$db->quoteName('#__community_engagement')
				.' WHERE ' . $db->quoteName('action') ." IN (". $actions .") "
				.' AND WEEK('.$db->quoteName('created').') = WEEK(curdate() - INTERVAL 7 DAY)'
				.' GROUP BY DATE('.$db->quoteName('created').')';
				break;

			case 'month':
				$query = 'SELECT DATE('.$db->quoteName('created').') as created, COUNT('.$db->quoteName('created').') as count FROM '.$db->quoteName('#__community_engagement')
				.' WHERE ' . $db->quoteName('action') ." IN (". $actions .") "
				.' AND YEAR('.$db->quoteName('created').') = YEAR(CURDATE()) AND MONTH('.$db->quoteName('created').') = MONTH(CURDATE())'
				.' GROUP BY DATE('.$db->quoteName('created').')';
				break;

			case 'lastmonth':
				$query = 'SELECT DATE('.$db->quoteName('created').') as created, COUNT('.$db->quoteName('created').') as count FROM '.$db->quoteName('#__community_engagement')
				.' WHERE ' . $db->quoteName('action') ." IN (". $actions .") "
				.' AND YEAR('.$db->quoteName('created').') = YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH('.$db->quoteName('created').') = MONTH(CURDATE() - INTERVAL 1 MONTH)'
				.' GROUP BY DATE('.$db->quoteName('created').')';
				break;

			default:
				$query = '';
		}

		$db->setQuery($query);

		$result = $db->loadObjectList();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		return $result;
	}
}