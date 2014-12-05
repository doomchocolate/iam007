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
class CommunityTableNetwork extends JTable
{
	var $name		= null;
	var $params		= null;

	public function __construct(&$db)
	{
		parent::__construct( '#__community_config' , 'name' , $db );
	}

	/**
	 * Save the configuration
	 **/
	public function store()
	{
		$db		=& $this->getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_config') . ' '
				. 'WHERE ' . $db->quoteName( 'name' ) . '=' . $db->Quote( 'network' );
		$db->setQuery( $query );

		$count	= $db->loadResult();

		$data	= new stdClass();
		$data->name		= 'network';
		$data->params	= $this->params;

		if( $count > 0 )
		{
			return $db->updateObject( '#__community_config' , $data , 'name' );
		}

		return $db->insertObject( '#__community_config' , $data, 'name' );
	}

}