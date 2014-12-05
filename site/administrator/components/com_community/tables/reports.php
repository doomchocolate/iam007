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

/**
 * JomSocial Table Model
 */
class CommunityTableReports extends JTable
{
	var $id				= null;
	var $uniquestring	= null;
	var $link			= null;
	var $status			= null;
	var $created		= null;

	public function __construct(&$db)
	{
		parent::__construct('#__community_reports','id', $db);
	}

	public function deleteChilds()
	{
		$db		= $this->getDBO();

		$query	= 'DELETE FROM ' . $db->quoteName( '#__community_reports_actions' ) . ' '
				. 'WHERE ' . $db->quoteName( 'reportid' ) . '=' . $db->Quote( $this->id );

		$db->setQuery( $query );
		if(!$db->query() )
		{
			return false;
		}

		$query	= 'DELETE FROM ' . $db->quoteName( '#__community_reports_reporter' ) . ' '
				. 'WHERE ' . $db->quoteName( 'reportid' ) . '=' . $db->Quote( $this->id );

		$db->setQuery( $query );
		if(!$db->query() )
		{
			return false;
		}

		return true;
	}

	/**
	 * Overrides Joomla load method
	 *
	 * @param	$uniqueString	The unique string for the current report.
	 */
	public function getId( $uniqueString )
	{
		$db		= $this->getDBO();

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_reports' ) . ' '
				. 'WHERE ' . $db->quoteName( 'uniquestring' ) . '=' . $db->Quote( $uniqueString );

		$db->setQuery( $query );
		$row	= $db->loadObject();

		if( !$row )
			return false;

		return $row->id;
	}

	/**
	 * Tests if the report is a new object
	 */
	public function isNew()
	{
		return ( $this->id == 0 ) ? true : false;
	}

	/**
	 * Adds a reporter and the text that is reported
	 *
	 * @param	$reportId	The parent's id
	 * @param	$authorId	The reporter's id
	 * @param	$message	The text that have been submitted by reporter.
	 * @param	$created	Datetime representation value.
	 * @param	$ip			The reporter's ip address
	 */
	public function addReporter( $reportId , $authorId , $message , $created , $ip )
	{
		$db		= $this->getDBO();

		$data				= new stdClass();

		$data->reportid		= $reportId;
		$data->message		= $message;
		$data->created_by	= $authorId;
		$data->created		= $created;
		$data->ip			= $ip;
		// Inser the new object
		return $db->insertObject( '#__community_reports_reporter' , $data , 'reportid' );
	}

	public function getReportersCount()
	{
		$db		= $this->getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_reports_reporter' ) . ' '
				. 'WHERE ' . $db->quoteName( 'reportid' ) . '=' . $db->Quote( $this->id );

		$db->setQuery( $query );
		return $db->loadResult();
	}

// 	/**
// 	 * Add actions for the current report
// 	 *
// 	 * @param	$label	The label for the report action that will appear at the back end.
// 	 * @param	$method	The method that should be executed.
// 	 * @param	$parameters	The method parameters to be parsed.
// 	 * @param	$defaultAction	Whether this is the default action to be executed when threshold is reached.
// 	 */
// 	function addAction( $label = '' , $method , $parameters , $defaultAction )
// 	{
// 		// Test if the record exists previously, as we do not want to re-add them
// 		$db		= $this->getDBO();
//
// 		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_reports_actions' ) . ' '
// 				. 'WHERE ' . $db->quoteName( 'reportid' ) . '=' . $db->Quote( $this->id ) . ' '
// 				. 'AND ' . $db->quoteName( 'method' ) . '=' . $db->Quote( $method ) . ' '
// 				. 'AND ' . $db->quoteName( 'parameters' ) . '=' . $db->Quote( $parameters );
//
// 		$db->setQuery( $query );
// 		$exists	= ( $db->loadResult() ) ? true : false;
//
// 		if( !$exists )
// 		{
// 			$data				= new stdClass();
//
// 			$data->reportid			= $this->id;
// 			$data->label			= $label;
// 			$data->method			= $method;
// 			$data->parameters		= $parameters;
// 			$data->defaultaction	= $defaultAction;
//
// 			// Insert the new object
// 			return $db->insertObject( '#__community_reports_actions' , $data , 'id' );
// 		}
//
//
// 		return true;
// 	}

	/**
	 * Add actions for the current report
	 *
	 * @param	Array	An Array of stdclass objects that defines each actions.
	 */
	public function addActions( $actions )
	{
		if( is_array($actions ) )
		{
			// Test if the record exists previously, as we do not want to re-add them
			$db		= $this->getDBO();

			// Remove existing report actions
			$query	= 'DELETE FROM ' . $db->quoteName( '#__community_reports_actions' ) . ' '
					. 'WHERE ' . $db->quoteName( 'reportid' ) . '=' . $db->Quote( $this->id );

			$db->setQuery( $query );
			$db->query();

			for($i = 0; $i < count( $actions ); $i++ )
			{
				$action	= $actions[ $i ];

				// Reformat the parameters.
				$argsData	= '';

				if( is_array( $action->parameters ) )
				{
					$argsCount	= count( $action->parameters );
					for($i = 0; $i < $argsCount; $i++ )
					{
						$argsData	.= $action->parameters[ $i ];
						$argsData	.= ( $i != ( $argsCount - 1 ) ) ? ',' : '';
					}
				}
				else
				{
					$argsData	= $action->parameters;
				}

				$data					= new stdClass();
				$data->reportid			= $this->id;
				$data->label			= $action->label;
				$data->method			= $action->method;
				$data->parameters		= $argsData;
				$data->defaultAction	= ( $action->defaultAction ) ? 1 : 0;

				// Insert the new object
				$db->insertObject( '#__community_reports_actions' , $data , 'id' );
			}
			return true;
		}
		return false;
	}
}