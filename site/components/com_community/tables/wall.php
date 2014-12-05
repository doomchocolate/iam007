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

/**
 * Wall object
 */
class CTableWall extends JTable
{
	/** Primary key **/
	var $id 			= null;

	/** The unique id of the specific app type **/
	var $contentid		= null;

	/** The user id that posted **/
	var $post_by		= null;

	/** The IP address of the poster **/
	var $ip				= null;

	/** Message **/
	var $comment		= null;

	/** Date the comment is posted **/
	var $date			= null;

	/** Publish status of the wall **/
	var $published		= null;

	/** Application type **/
	var $type			= null;

	/**
	 * Constructor
	 */
	public function __construct( &$db )
	{
		parent::__construct( '#__community_wall', 'id', $db );
	}


	/**
	 * Store the wall data
	 *
	 */
	public function store($updateNulls = false)
	{
		// Set the defaul data if they are empty

		if( empty($this->ip) )
		{
			$this->ip = $_SERVER['REMOTE_ADDR'];
		}

		if( empty($this->date) )
		{
			$now = JFactory::getDate();
			$this->date = $now->toSql();
		}

		if( empty($this->published) ){
			$this->published = 1;
		}

		parent::store();
	}
}
