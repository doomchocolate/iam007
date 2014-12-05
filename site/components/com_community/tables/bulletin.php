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

class CTableBulletin extends JTable
{

	var $id 		= null;
	var $groupid	= null;
	var $created_by	= null;
	var $published	= null;
	var $title		= null;
	var $message	= null;
	var $date		= null;
	var $params		= null;

	/**
	 * Constructor
	 */
	public function __construct( &$db )
	{
		parent::__construct( '#__community_groups_bulletins', 'id', $db );
	}

	public function store($updateNulls = false)
	{
		if (!$this->check())
		{
			return false;
		}
		return parent::store();
	}

	public function check()
	{
		$config = CFactory::getConfig();
		$safeHtmlFilter = CFactory::getInputFilter( $config->get('allowhtml'));
		$this->title	= $safeHtmlFilter->clean($this->title);
		$this->message	= $safeHtmlFilter->clean($this->message);

		return true;
	}

	public function getParams()
	{
		$params	= new CParameter( $this->params );

		return $params;
	}
}