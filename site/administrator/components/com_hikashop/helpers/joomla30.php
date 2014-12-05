<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class JHtmlHikaselect extends JHTMLSelect {
	static $event = false;

	public static function booleanlist($name, $attribs = null, $selected = null, $yes = 'JYES', $no = 'JNO', $id = false){
		$arr = array(
			JHtml::_('select.option', '1', JText::_($yes)),
			JHtml::_('select.option', '0', JText::_($no))
		);
		$arr[0]->booleanlist = true;
		$arr[1]->booleanlist = true;
		return JHtml::_('hikaselect.radiolist', $arr, $name, $attribs, 'value', 'text', (int) $selected, $id);
	}

	public static function radiolist($data, $name, $attribs = null, $optKey = 'value', $optText = 'text', $selected = null, $idtag = false, $translate = false, $vertical = false){
		reset($data);
		$app = JFactory::getApplication();

		if(!self::$event) {
			self::$event = true;
			$doc = JFactory::getDocument();

			if($app->isAdmin()) {
				$doc->addScriptDeclaration('
(function($){
$.propHooks.checked = {
	set: function(elem, value, name) {
		var ret = (elem[ name ] = value);
		$(elem).trigger("change");
		return ret;
	}
};
})(jQuery);');
			} else {
				JHtml::_('jquery.framework');
				$doc->addScriptDeclaration('
(function($){
if(!window.hikashopLocal)
	window.hikashopLocal = {};
window.hikashopLocal.radioEvent = function(el) {
	var id = $(el).attr("id"), c = $(el).attr("class"), lbl = $("label[for=\"" + id + "\"]");
	if(c !== undefined && c.length > 0)
		lbl.addClass(c);
	lbl.addClass("active");
	$("input[name=\"" + $(el).attr("name") + "\"]").each(function() {
		if($(this).attr("id") != id) {
			c = $(this).attr("class");
			lbl = $("label[for=\"" + jQuery(this).attr("id") + "\"]");
			if(c !== undefined && c.length > 0)
				lbl.removeClass(c);
			lbl.removeClass("active");
		}
	});
}
$(document).ready(function() {
	setTimeout(function(){ $(".hikaradios .btn-group label").off("click"); }, 200);
});
})(jQuery);');
			}
		}

		if (is_array($attribs))	{
			$attribs = JArrayHelper::toString($attribs);
		}

		$id_text = str_replace(array('[',']'),array('_',''),$idtag ? $idtag : $name);

		$backend = $app->isAdmin();
		$htmlLabels = '';

		if($backend) {
			$html = '<div class="controls"><fieldset id="'.$id_text.'" class="radio btn-group'. ($vertical?' btn-group-vertical':'').'">';
		} else {
			$html = '<div class="hikaradios" id="'.$id_text.'">';
		}

		foreach ($data as $obj) {
			$k = $obj->$optKey;
			$t = $translate ? JText::_($obj->$optText) : $obj->$optText;
			$class = isset($obj->class) ? $obj->class : '';
			$sel = false;
			$extra = $attribs;
			$currId = $id_text . $k;
			if(isset($obj->id))
				$currId = $obj->id;

			if (is_array($selected)) {
				foreach ($selected as $val) {
					$k2 = is_object($val) ? $val->$optKey : $val;
					if ($k == $k2) {
						$extra .= ' selected="selected"';
						$sel = true;
						break;
					}
				}
			} elseif((string) $k == (string) $selected) {
				$extra .= ' checked="checked"';
				$sel = true;
			}

			if($backend) {
				$html .= "\n\t" . "\n\t" . '<input type="radio" name="' . $name . '"' . ' id="' . $currId . '" value="' . $k . '"' . ' ' . $extra . '/>';
				$html .= "\n\t" . '<label for="' . $currId . '"' . '>' . $t . '</label>';
			} else {
				$extra = ' '.$extra;
				if(strpos($extra, ' style="') !== false) {
					$extra = str_replace(' style="', ' style="display:none;', $extra);
				} elseif(strpos($extra, 'style=\'') !== false) {
					$extra = str_replace(' style=\'', ' style=\'display:none;', $extra);
				} else {
					$extra .= ' style="display:none;"';
				}
				if(strpos($extra, ' onchange="') !== false) {
					$extra = str_replace(' onchange="', ' onchange="hikashopLocal.radioEvent(this);', $extra);
				} elseif(strpos($extra, 'onchange=\'') !== false) {
					$extra = str_replace(' onchange=\'', ' onchange=\'hikashopLocal.radioEvent(this);', $extra);
				} else {
					$extra .= ' onchange="hikashopLocal.radioEvent(this);"';
				}
				if(!empty($obj->class)) {
					$extra .= ' class="'.$obj->class.'"';
				}
				$html .= "\n\t" . '<input type="radio" name="' . $name . '"' . ' id="' . $currId . '" value="' . $k . '"' . ' ' . $extra . ' ' . $attribs . '/>';

				$htmlLabels .= "\n\t"."\n\t" . '<label for="' . $currId . '"' . ' class="btn'. ($sel ? ' active '.$class : '') .'">' . $t . '</label>';
			}
		}
		if($backend) {
			$html .= '</fieldset></div>';
		} else {
			$html .= "\n" . '<div class="btn-group'. ($vertical?' btn-group-vertical':'').'" data-toggle="buttons-radio">' . $htmlLabels . "\n" . '</div>';
			$html .= "\n" . '</div>';
		}
		$html .= "\n";
		return $html;
	}

}
