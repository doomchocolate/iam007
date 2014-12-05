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

class CProfileField
{
	var $fieldId = null;
	var $params = null;
	public function __construct($fieldId=null){
		if ($fieldId!==null) {
			$this->load($fieldId);
		}
	}
	public function load($fieldId){
		if ($fieldId!==null) {
			$this->fieldId = $fieldId;
			$db		= JFactory::getDBO();
			$query	= 'SELECT * FROM '.$db->quoteName('#__community_fields')
					. ' WHERE '.$db->quoteName('id').'='.$db->quote($this->fieldId);
			$db->setQuery($query);
			if($db->getErrorNum()) {
				JError::raiseError( 500, $db->stderr());
			}
			$field	= $db->loadObject();
			$this->params	= new CParameter($field->params);
		}
	}
	public function validLength( $value )
	{
		if(isset($this->params)){
			$max_char = $this->params->get('max_char');
			$min_char = $this->params->get('min_char');
			$len = strlen((string) $value);
			if($min_char && $len < $min_char ){
				return false;
			}
			if($max_char && $len > $max_char ){
				return false;
			}
		}
		return true;
	}
	public function getStyle(){
		if(isset($this->params)){
			$style = $this->params->get('style');
			return $style;
		}
		return '';
	}

	public function getMessage($field)
	{
		$params = new CParameter($field['params']);

		if($params->get('min_char') && $params->get('max_char') && !$this->validLength($field['value']))
		{
			return JText::sprintf('COM_COMMUNITY_FIELD_CONTAIN_OUT_OF_RANGE', $field['name'],$params->get('max_char'),$params->get('min_char') );
		}

		return JText::sprintf('COM_COMMUNITY_FIELD_CONTAIN_IMPROPER_VALUES',$field['name']);

	}
}
