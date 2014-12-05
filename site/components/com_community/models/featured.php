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

require_once ( JPATH_ROOT .'/components/com_community/models/models.php');

jimport( 'joomla.filesystem.file');

// Deprecated since 1.8.x to support older modules / plugins
//CFactory::load( 'tables' , 'featured' );

class CommunityModelFeatured extends JCCModel
{
	public function isExists( $type, $cid )
	{
		$db		= $this->getDBO();

		$query	= 'SELECT COUNT(1) FROM '. $db->quoteName('#__community_featured')
				. ' WHERE '. $db->quoteName('type').'=' . $db->Quote( $type ) . ' '
				. ' AND '. $db->quoteName('cid').'=' . $db->Quote( $cid );
		$db->setQuery($query);
		$exists	= ( $db->loadResult() >= 1) ? true : false;
		return $exists;
	}
}