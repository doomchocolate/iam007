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

require_once COMMUNITY_COM_PATH . '/libraries/fields/profilefield.php';

class CFieldsTextarea extends CProfileField {

    public function getFieldHTML($field, $required) {

        $params = new CParameter($field->params);
        $readonly = $params->get('readonly') && !COwnerHelper::isCommunityAdmin() ? ' readonly=""' : '';
        $style = $this->getStyle() ? ' style="' . $this->getStyle() . '" ' : '';

        //extract the max char since the settings is in params
        $max_char = $params->get('max_char');
        $config = CFactory::getConfig();
        $js = 'assets/validate-1.5.min.js';

        CAssets::attach($js, 'js');

        // If maximum is not set, we define it to a default		
        $class = ($field->required == 1) ? ' required' : '';
        $class .=!empty($field->tips) ? ' jomNameTips tipRight' : '';
        $html = '<textarea id="field' . $field->id . '" name="field' . $field->id . '" class="textarea' . $class . '" title="' . CStringHelper::escape(JText::_($field->tips)) . '"' . $style . $readonly . '>' . $field->value . '</textarea>';
        $html .= '<span id="errfield' . $field->id . 'msg" style="display:none;">&nbsp;</span>';
		if ( !empty($max_char) ) {
			$html .= '<script type="text/javascript">cvalidate.setMaxLength("#field' . $field->id . '", "' . $max_char . '");</script>';
		}        

        return $html;
    }

    public function isValid($value, $required) {
        if ($required && empty($value)) {
            return false;
        }
        /* if not empty than we'll validate no matter what is it required or not */
        if (!empty($value)) {
            return $this->validLength($value);
        }
        return true;
    }

}