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

class CTableConnect extends JTable
{
	var $connectid	= null;
	var $type		= null;
	var $userid		= null;

	/**
	 * Constructor
	 */
	public function __construct( &$db )
	{
		parent::__construct( '#__community_connect_users', 'connectid', $db );
	}

	/**
	 * Handle all sorts of load error
	 */
	public function load( $id=null, $reset = true )
	{
		parent::load( $id , $reset);

		// Once we get the id, check if the user exist. He might have been deleted
		// If not delete this info and
		// reset everything
		$user = JFactory::getUser( $this->userid );
		if ( is_null($user->id) ){
			//echo $id;
			//print_r($user);exit;
			$this->delete();

			// Reset everything to null
			$this->userid 	= null;
			$this->type 	= null;
		}

		return;
	}

	public function store($updateNulls = false)
	{
		$db		=  $this->getDBO();

		$query	= 'SELECT COUNT(1) FROM ' . $db->quoteName('#__community_connect_users')
				. ' WHERE ' . $db->quoteName( 'connectid' ) . '=' . $db->Quote( $this->connectid );

		$db->setQuery($query);
		$result	= $db->loadResult();

		if( !$result )
		{
			$obj			= new stdClass();
			$obj->connectid	= $this->connectid;
			$obj->type		= $this->type;
			$obj->userid	= $this->userid;
			return $db->insertObject( '#__community_connect_users' , $obj );
		}

		// Existing table, just need to update
		return $db->updateObject( '#__community_connect_users', $this, 'connectid' , false );
	}

        public function delete($id = null)
        {
            if (is_null($id)) {
                $id = $this->userid;
            }

            $db		=  $this->getDBO();

            $query	= 'DELETE
                           FROM ' . $db->quoteName( '#__community_connect_users' ) . ' '
				  . 'WHERE ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $id );

            $db->setQuery( $query );
            $db->Query();

            return true;

        }
}
