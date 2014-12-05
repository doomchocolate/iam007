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

class CTableLocationCache extends JTable
{
	var $id		= null;
	var $address =	null;
	var $latitude=	null;
	var $longitude=	null;
	var $data=	null;
	var $status=	null;
	var $created=	null;

	/**
	 * Constructor
	 */
	public function __construct( &$db ) {
		parent::__construct( '#__community_location_cache', 'id', $db );
	}

	/**
	 * Onload, we try to load existing data, if any. If not, query from Google
	 */
	public function load($address=null, $resets=null)
	{
		// lowercase the incoming address
		$address = JString::strtolower( $address );

		$db = JFactory::getDBO();

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_location_cache');
		$query	.= ' WHERE ';
		$query	.= $db->quoteName('address') . '= ' . $db->quote($address);
		$query	.= ' LIMIT 1';
		$db->setQuery( $query );
		$obj = $db->loadObject();

		if($obj){
			$this->bind($obj);
		}
		else
		{

			$data = CMapping::getAddressData($address);

			$this->address 	= $address;
			$this->latitude 	= COMMUNITY_LOCATION_NULL;
			$this->longitude 	= COMMUNITY_LOCATION_NULL;
			$this->data = '';
			$this->status = 'ZERO_RESULTS';

			if($data != null)
			{
				require_once (AZRUL_SYSTEM_PATH.'/pc_includes/JSON.php');

				$json = new Services_JSON();
				$content = $json->encode($data);

				if($data->status == 'OK'){
					$this->latitude 	= $data->results[0]->geometry->location->lat;
					$this->longitude 	= $data->results[0]->geometry->location->lng;
					$this->data		=  $content;
					$this->status = $data->status;
				}
			}

			$date = new JDate();
			$this->created = $date->toSql(true);

			$this->store();
		}

		return true;
	}
}