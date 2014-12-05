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

require_once( JPATH_ROOT .'/components/com_community/libraries/core.php');

if(!class_exists('plgCommunityInput'))
{
	class plgCommunityInput extends CApplications
	{
		var $name		= 'Walls';
		var $_name		= 'input';
	
	    function plgCommunityInput(& $subject, $config)
	    {	
			parent::__construct($subject, $config);
	    }
	    
	    function _filterText(&$text) {
			$text = $this->_nl2br2($text);
	
			$text = preg_replace("/(<br\s*\/?>\s*){3,}/", "<br /><br />", $text);
	
			return $text;
		}
		
		/**
		 * ->title
		 * ->comment 	 
		 */	 	
		function onWallDisplay( &$row )
		{
			CError::assert( $row->comment, '', '!empty', __FILE__ , __LINE__ );
			$row->comment = $this->_filterText($row->comment);
		}
		
		/**
		 * ->message
		 */
		function onMessageDisplay( &$row )
		{
			CError::assert( $row->body, '', '!empty', __FILE__ , __LINE__ );
			$row->body = $this->_filterText($row->body);
		} 
		
		function onDiscussionDisplay( &$row )
		{
			CError::assert( $row->message, '', '!empty', __FILE__ , __LINE__ );
			$config	= CFactory::getConfig();
			
			// If editor is disabled, we only want to replace newlines with BR otherwise it doesn't make any sense to replace so many br
			if( $config->get('editor') == '0' )
			{
				$row->message = $this->_filterText($row->message);
			}
		} 
		
		function onWallSave(&$row)
		{
		}
		
		function _nl2br2($string)
		{
			$string = CString::str_ireplace(array("\r\n", "\r", "\n"), "<br />", $string);
			return $string;
		}
	
	}	
}

