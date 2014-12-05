<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*
* "Patch" class to handle the change JParameter to JForm in 1.6
* Both classes handle Form generation, but obviously there are JParameter functions that are not present in JForm
* This causes function call like render() to fail in 1.6
* The aim of this class is to attach old JParameter functions into the JForm and allow necessary modifications to be applied on the functions
* More functions from JParameter could be included in the future.
* This is a temporary solution only
*
*/

if(!jimport('joomla.form.form')) exit('Class only available on Joomla 1.6');

class CJForm extends JForm{

	/**
	* Constructor (similar to JForm)
	*/
	public function __construct($name, array $options = array()){
		parent::__construct($name, $options);
	}

	/**
	* Get an instance of CJForm. Taken from JForm's getInstance()
	*/
	public static function getInstance($name, $data = null, $options = array(), $replace = true, $xpath = false){

		// Reference to array with form instances
		$forms = self::$forms;

		// Only instantiate the form if it does not already exist.
		if (!isset($forms[$name])) {

			$data = trim($data);

			if (empty($data)) {
				throw new Exception(JText::_('JLIB_FORM_ERROR_NO_DATA'));
			}

			// Instantiate the form.
			$forms[$name] = new CJForm($name, $options);

			// Load the data.
			if (substr(trim($data), 0, 1) == '<') {
				if ($forms[$name]->load($data, $replace, $xpath) == false) {
					throw new Exception(JText::_('JLIB_FORM_ERROR_XML_FILE_DID_NOT_LOAD'));

					return false;
				}
			}
			else {
				if ($forms[$name]->loadFile($data, $replace, $xpath) == false) {
					throw new Exception(JText::_('JLIB_FORM_ERROR_XML_FILE_DID_NOT_LOAD'));

					return false;
				}
			}
		}

		return $forms[$name];
	}

	/**
	* Render function from JParameter in 1.5 but not available in JForm 1.6
	*
	* @param string name
	* @param string group [currently not used, being put there to imitate JParameter render()]
	* @return string html
	*/
	public function render($name = 'params', $group = '_default'){

		$group = $this->getGroup($name);

		$html = array();

		//simulate what's happening on JParameter

		foreach($group as $field)
		{
			$objName = get_class($field);
			switch ($objName) {
				case 'JFormFieldTimezone':
				case 'JFormFieldLanguage':
						if ( method_exists ($field,'getAttribute') ) {
						
							if($field->getAttribute('client') != 'administrator' ){
								$separtor = (count($html) == 0) ? 'has-seperator' : '';

								$html[] = '<li class="'.$separtor.'">';
								$html[] = JString::str_ireplace('class="hasTip"', 'class="hasTip form-label"',$field->label);
								$html[] = '<div class="form-field">';
								$html[] = $field->input;
								$html[] = '</div>';
								$html[] = '</li>';
							}
							
						}
                                    
					break;

				default:
					# code...
					break;
			}

		}

		return implode("\n", $html);
	}

	/**
	* Render Table function from JParameter in 1.5 but not available in JForm 1.6
	*
	* @param string name
	* @param string group [currently not used, being put there to imitate JParameter render()]
	* @return string html
	*/
	public function renderTable($name = 'params', $group = '_default'){
		$group = $this->getGroup($name);
		$html = array();
		$html[] = '<table width="100%" class="admintable"><tbody>';
		foreach($group as $field)
		{
			$html[] = '<tr><td class="key">'.$field->label.'</td>';
			$html[] = '<td class="paramlist_value"><div class="form-field">'.$field->input.'</div></td></tr>';
		}
		$html[] = "</tbody></table>";
		return implode("\n", $html);
	}

}