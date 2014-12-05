<?php
/**
 * @package Unite Revolution Slider for Joomla 1.7-2.5
 * @author UniteCMS.net
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

defined('JPATH_BASE') or die;

/**
 * Supports a modal article picker.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @since		1.6
 */
class JFormFieldSlider extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Slider';

	/**
	 * 
	 * include all the files needed
	 */
	protected function requireFramework(){
		
		$pathComponent = JPATH_ADMINISTRATOR."/components/com_uniterevolution2/";
		require_once $pathComponent."includes.php";
	}
	
	
	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		$this->requireFramework();
				
		$slider = new RevSlider();
		$arrSliders = $slider->getArrSlidersShort();
		
		$selectedID = $this->value;
		if(empty($selectedID))
			$selectedID = JRequest::getCmd("sliderid");
	
		$html = "<select id='{$this->id}_id' name='{$this->name}'>";
		foreach($arrSliders as $id=>$title){
			
			$selected = "";				
			if($id == $selectedID)
				$selected = 'selected="selected"';
			
			$html .= "<option value='$id' $selected>$title</option>";
		}		
		$html .= "</select>";
		
		return $html;
	}
	
	
}
