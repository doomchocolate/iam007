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

class CTableMessage extends JTable
{
	var $id 		= null;
	var $from 		= null;
	var $parent		= null;
	var $deleted	= null;
	var $from_name	= null;
	var $posted_on	= null;
	var $subject	= null;
	var $body		= null;

	/**
	 * Constructor
	 */
	public function __construct( &$db ) {
		parent::__construct( '#__community_msg', 'id', $db );
	}
}
