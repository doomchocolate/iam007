<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
require_once ( JPATH_ROOT .'/components/com_community/models/models.php');

// Deprecated since 1.8.x to support older modules / plugins
//CFactory::load( 'tables' , 'tag' );
//CFactory::load( 'tables' , 'tagword' );

interface CTaggable_Item
{
	public function tagGetTitle();			// Return the title of the object
	public function tagGetHtml();			// Return the HTML summary of the object
	public function tagGetLink();			// return the internal link of the object
	public function	tagAllow($userid);		// return true/false if the user can add the tag
}

class CommunityModelTags extends JCCModel
{

	public function CommunityModelPhotos()
	{
		parent::JCCModel();
	}

	/**
	 *
	 * @param string $element
	 * @param int $cid
	 * @param int $uid
	 * @return array CTableTag
	 */
	public function getTags($element, $cid, $uid = 0){
		$db		= JFactory::getDBO();

		$query	= 'SELECT a.*,b.'.$db->quoteName('count').' FROM '
				. $db->quoteName( '#__community_tags' ) . ' as a '
				. 'LEFT JOIN ( '.$db->quoteName( '#__community_tags_words' ) .' AS b ) '
				. ' ON ( a.'.$db->quoteName('tag').' = b.'.$db->quoteName('tag').' ) '
				. ' WHERE a.' . $db->quoteName( 'element' ) . '=' . $db->Quote( $element )
				. ' AND a.' . $db->quoteName( 'cid' ) . '=' . $db->Quote( $cid );

		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		// Update their correct Thumbnails and check album permissions
		$tags = array();
		if( !empty($result) )
		{
			foreach( $result as &$row )
			{
				$tag	= JTable::getInstance( 'Tag' , 'CTable' );
				$tag->bind($row);
				$tag->rank = $row->count;
				$tags[] = $tag;
			}
		}

		return $tags;
	}

	/**
	 * Return total count of tags
	 */
	public function getTagsCount(){
		$db		= JFactory::getDBO();
		$query	= 'SELECT SUM(count) FROM '.$db->quoteName( '#__community_tags_words' );
		$db->setQuery( $query );
		$result = $db->loadResult();

		return $result;
	}


	public function getRecentTags($limit)
	{
		$db		= JFactory::getDBO();

		$query	= 'SELECT DISTINCT '.$db->quoteName('tag').' FROM '
				. $db->quoteName( '#__community_tags' ) . ' '
				. ' ORDER BY ' . $db->quoteName( 'created' ) . ' DESC '
				. ' LIMIT ' . $limit;

		$db->setQuery( $query );
		$result	= $db->loadColumn();
		return $result;
	}

	/**
	 *
	 * @param string $tag
	 */
	public function getItems($tag)
	{
		$db		= JFactory::getDBO();

		$query	= 'SELECT * FROM '
				. $db->quoteName( '#__community_tags' ) . ' '
				. ' WHERE ' . $db->quoteName( 'tag' ) . ' LIKE ' . $db->Quote( $tag )
				. ' ORDER BY ' . $db->quoteName( 'created' ) . ' DESC '
				. ' LIMIT 10';

		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		// Update their correct Thumbnails and check album permissions
		$tags = array();
		if( !empty($result) )
		{
			foreach( $result as &$row )
			{
				$tag	= JTable::getInstance( 'Tag' , 'CTable' );
				$tag->bind($row);
				$tags[] = $tag;
			}
		}

		return $tags;
	}
}