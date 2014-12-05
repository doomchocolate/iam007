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

class CTableTagword extends JTable
{
	var $id 		= null;
	var $tag		= null;
	var $count 		= null;
	var $modified 	= null;

	/**
	 * Constructor
	 */
	public function __construct( &$db )
	{
		parent::__construct( '#__community_tags_words', 'id', $db );

	}

	/**
	 *
	 * @param mixed $tag
	 * @return boolean
	 */
	public function load($tag = null, $reset = true){
		if(is_string($tag)){
			// Search via keyword
			$db		= JFactory::getDBO();

			$query	= 'SELECT * FROM '
				. $db->quoteName( '#__community_tags_words' ) . ' '
				. ' WHERE ' . $db->quoteName( 'tag' ) . ' LIKE ' . $db->Quote( $tag );
			$db->setQuery( $query );
			$result = $db->loadObject();
			if(!empty($result)){
				$this->bind($result);

			} else {

				$this->tag  = $tag;
				$this->store();
			}

		} else {
			return parent::load($tag);
		}
	}

	/**
	 * Recalculate the count and last update time
	 */
	public function update($exclude = array()){
		// Search via keyword
		$db		= JFactory::getDBO();

		$query	= 'SELECT count(*) FROM '
			. $db->quoteName( '#__community_tags' ) . ' '
			. ' WHERE ' . $db->quoteName( 'tag' ) . ' LIKE ' . $db->Quote( $this->tag );
		$db->setQuery( $query );
		$result = $db->loadResult();

		// Only update the stats if the count is not the same
		if($result != $this->count){
			$this->count = $result;

			$query	= 'SELECT * FROM '
			. $db->quoteName( '#__community_tags' ) . ' '
			. ' WHERE ' . $db->quoteName( 'tag' ) . ' LIKE ' . $db->Quote( $this->tag )
			. ' ORDER BY ' . $db->quoteName( 'id' )
			. ' LIMIT 1 ';

			$db->setQuery( $query );
			$result = $db->loadObject();
			if(!empty($result)){
				$this->modified = $result->created;
			}


			$this->store();
		}

	}
}