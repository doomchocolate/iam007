<?php
defined('JPATH_PLATFORM') or die;

class CHTMLInput
{
	public static function checkbox($name, $class, $attribs = array(), $selected = null, $id = false)
	{
		$selectedHtml = '';
		$html = '';

		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString($attribs);
		}

		if($selected) {
			$selectedHtml .= " checked=\"checked\"";
		}

		$html .= "\n<input type='hidden' value='0' name=\"$name\">"; // Self destruct
		$html .= "\n<input type=\"checkbox\" name=\"$name\" class=\"$class\" value=\"1\" $attribs $selectedHtml />";
		$html .= "\n<span class=\"lbl\"></span>";
		
		return $html;
	}
}