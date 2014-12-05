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

class CTableLike extends JTable
{

	// Tables' fileds
	var $id		=   null;
	var $element	=   null;
	var $uid	=   null;
	var $like	=   null;
	var $dislike	=   null;

	/**
	 * Constructor
	 */
	public function __construct( &$db )
	{
		parent::__construct( '#__community_likes', 'id', $db );
	}

	public function store($updateNulls=null)
	{
		return parent::store();
	}

	public function loadInfo( $element, $itemId )
	{

		$db	= JFactory::getDBO();

		$query	=   'SELECT * FROM ' . $db->quoteName('#__community_likes') . ' '
			    . 'WHERE ' . $db->quoteName('element') . '=' . $db->Quote( $element ) . ' '
			    . 'AND ' . $db->quoteName('uid') . '=' . $db->Quote( $itemId );

		$db->setQuery( $query );

		$result	=   $db->loadObject();

		if ($result)
		{
			$this->bind( $result );
		}

		return;
	}
}