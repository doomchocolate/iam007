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
class CommunityModelUserPoints extends JCCModel
{

	public function getPointData($action)
	{
		$db	 = $this->getDBO();

		$query = 'SELECT * FROM '.$db->quoteName('#__community_userpoints');
		$query .= ' WHERE '.$db->quoteName('action_string').' = '.$db->Quote($action);
		$db->setQuery($query);

		$result	= $db->loadObject();
// 		$point	= 0;
//
// 		if(! empty($result))
// 		{
// 			$published	= $result->published;
// 			$point		= $result->points;
//
// 			if ($published == '0')
// 				$point = 0;
//
// 		}

		return $result;
	}
}
