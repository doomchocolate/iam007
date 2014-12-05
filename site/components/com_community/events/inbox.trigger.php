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

class CInboxTrigger
{
	
	public function onMessageDisplay( $row )
	{
		//CFactory::load( 'helpers' , 'string' );
		CError::assert( $row->body, '', '!empty', __FILE__ , __LINE__ );
		
		// @rule: Only nl2br text that doesn't contain html tags
		if( !CStringHelper::isHTML( $row->body ) )
		{			
			$row->body	= CStringHelper::nl2br( $row->body );
		}
	}
}