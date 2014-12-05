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
class CFieldsCheckbox extends CProfileField
{
	public function _translateValue( &$string )
	{
		$string	= JText::_( $string );
	}

	/**
	 * Method to format the specified value for text type
	 **/
	public function getFieldData( $field )
	{
		$value = $field['value'];

		// Since multiple select has values separated by commas, we need to replace it with <br />.
		$fieldArray	= explode ( ',' , $value );

		array_walk($fieldArray, array('CFieldsCheckbox', '_translateValue'));

		$fieldValue = implode('<br />', $fieldArray);
		return $fieldValue;
	}

	public function getFieldHTML( $field , $required )
	{
		$class				= ($field->required == 1) ? ' required validate-custom-checkbox' : '';
		$lists = array();
		//a fix for wrong data input
		$field->value= JString::trim($field->value);
		if(is_array($field->value)){
			$tmplist = $field->value;
		} else {
			if(JString::strrpos($field->value,',') == (JString::strlen($field->value) - 1)) {
				$field->value = JString::substr($field->value,0,-1);
			}
			$tmplist	 = explode(',', $field->value);
		}
		if($tmplist){
			foreach($tmplist as $value){
				$lists[] = JString::trim( $value );
			}
		}
		$html				= '';
		$elementSelected	= 0;
		$elementCnt	        = 0;
		$style 				= ' style="margin: 0 5px 5px 0;' .$this->getStyle() . '" ';
		$cnt = 0;
		//CFactory::load( 'helpers' , 'string' );
                //Removed tooltip
		//$class	.= !empty( $field->tips ) ? ' jomNameTips tipRight' : '';
                //title="' . CStringHelper::escape( JText::_( $field->tips ) ). '"
		$html	.= '<div class="' . $class . '" style="display: inline-block;">';
		if( is_array( $field->options ) )
		{
			foreach( $field->options as $option )
			{
				if(JString::trim($option)==''){
					//do not display blank options
					continue;
				}
				$selected	= in_array( JString::trim( $option ) , $lists ) ? ' checked="checked"' : '';

				if( empty( $selected ) )
				{
					$elementSelected++;
				}


				$html .= '<label class="lblradio-block">';
				$html .= '<input type="checkbox" name="field' . $field->id . '[]" value="' . $option . '"' . $selected . ' class="checkbox '.$class . '"' .$style.' />';
				$html .= JText::_( $option ) . '</label>';
				$elementCnt++;

			}
		}

		$html   .= '<span id="errfield'.$field->id.'msg" style="display: none;">&nbsp;</span>';
		$html	.= '</div>';

		return $html;
	}

	public function isValid( $value , $required )
	{
		if( $required && empty($value))
		{
			return false;
		}
		return true;
	}

	public function formatdata( $value )
	{
		$finalvalue = '';
		if(!empty($value))
		{
			foreach($value as $listValue){
				$finalvalue	.= $listValue . ',';
			}
		}
		return $finalvalue;
	}
}