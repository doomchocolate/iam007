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

class CTableOauth extends JTable
{
	var $userid			= null;
	var $requesttoken	= null;
	var $accesstoken 	= null;
	var $app            = null;

	public function __construct( &$db )
	{
		parent::__construct( '#__community_oauth', 'id', $db );
	}

	public function load( $userId=null , $app=true )
	{
		$db		= JFactory::getDBO();
		$query	= 'SELECT * FROM ' . $db->quoteName( $this->_tbl ) . ' '
				. 'WHERE ' . $db->quoteName('userid') . '=' . $db->Quote( $userId ) . ' '
				. 'AND ' . $db->quoteName('app') . '=' . $db->Quote( $app );
		$db->setQuery( $query );
		$result	= $db->loadAssoc();

		if( $result )
		{
			$this->bind( $db->loadAssoc() );
			return true;
		}

		return false;
	}

	public function delete($pk = null)
	{
		$db		=  $this->getDBO();
		$query  = 'DELETE FROM ' . $db->quoteName( $this->_tbl ) . ' '
				. 'WHERE ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $this->userid );
		$db->setQuery( $query );
		return $db->Query();
	}

	public function store( $updateNulls = false )
	{
		$db		=  $this->getDBO();

		$query	= 'SELECT COUNT(1) FROM ' . $db->quoteName( $this->_tbl ) . ' '
				. 'WHERE ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $this->userid );

		$db->setQuery($query);
		$result	= $db->loadResult();

		if( !$result )
		{
			$obj			= new stdClass();

			$obj->userid		= $this->userid;
			$obj->requesttoken	= $this->requesttoken;
			$obj->accesstoken   = $this->accesstoken;
			$obj->app           = $this->app;
			return $db->insertObject( $this->_tbl , $obj );
		}

 		// Existing table, just need to update
		return $db->updateObject( $this->_tbl , $this, 'userid' , false );
	}
}
