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

require_once (COMMUNITY_COM_PATH.'/libraries/fields/profilefield.php');
class CFieldsLabel extends CProfileField
{
	public function getFieldHTML( $field , $required )
	{
		//CFactory::load( 'helpers' , 'string' );
		$class	= !empty( $field->tips ) ? ' jomNameTips tipRight' : '';

		$html	= '<textarea title="' . CStringHelper::escape( JText::_( $field->tips ) ) .'" id="field' . $field->id . '" name="field' . $field->id . '"  class="textarea' . $class . '" cols="20" rows="5" readonly="readonly">' . CStringHelper::escape( $field->tips ) . '</textarea>';
		$html   .= '<span id="errfield'.$field->id.'msg" style="display:none;">&nbsp;</span>';

		return $html;
	}
}
