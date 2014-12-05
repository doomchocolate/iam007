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
class CFieldsList extends CProfileField
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

		array_walk($fieldArray, array('CFieldsList', '_translateValue'));

		$fieldValue = implode('<br />', $fieldArray);
		return $fieldValue;
	}

	public function getFieldHTML( $field , $required )
	{
		$class	= ($field->required == 1) ? ' required' : '';
		$class	.= !empty( $field->tips ) ? ' jomNameTips tipRight' : '';
		//a fix for wrong data
		$field->value= JString::trim($field->value);
		if(JString::strrpos($field->value,',') == (JString::strlen($field->value) - 1)) {
			$field->value = JString::substr($field->value,0,-1);
		}

		$lists	 = explode(',', $field->value);
		//CFactory::load( 'helpers' , 'string' );
		$html	= '<select id="field'.$field->id.'" name="field' . $field->id . '[]" type="select-multiple" multiple="multiple" class="jomNameTips tipRight select'.$class.'" title="' . CStringHelper::escape( JText::_( $field->tips ) ) . '">';

		$elementSelected	= 0;

		foreach( $field->options as $option )
		{
			$selected	= in_array( $option, $lists ) ? ' selected="selected"' : '';

			if( empty($selected) )
			{
				$elementSelected++;
			}
			$html	.= '<option value="' . $option . '"' . $selected . '>' . JText::_( $option ) . '</option>';
		}

		if($elementSelected == 0)
		{
			//if nothing is selected, we default the 1st option to be selected.
			$elementName = 'field'.$field->id;
			$html .=<<< HTML

				   <script type='text/javascript'>
					   var slt = document.getElementById('$elementName');
					   if(slt != null){
					      slt.options[0].selected = true;
					   }
				   </script>
HTML;
		}
		$html	.= '</select>';
		$html   .= '<span id="errfield'.$field->id.'msg" style="display:none;">&nbsp;</span>';
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