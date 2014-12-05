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
jimport('joomla.utilities.date');
require_once (COMMUNITY_COM_PATH . '/libraries/fields/profilefield.php');

class CFieldsDate extends CProfileField {

    /**
     * The max year old can choose ( the low year )
     * @var array 
     */
    protected $_maxRange = null;

    /**
     * The min year old can choose ( the max year )
     * @var int 
     */
    protected $_minRange = null;

    /**
     * Construction
     * @param type $fieldId
     */
    public function __construct($fieldId = null) {
        parent::__construct($fieldId);
    }

    /**
     * Method to format the specified value for text type
     * @param type $field
     * @return type
     */
    public function getFieldData($field) {
        $value = $field['value'];
        if (empty($value))
            return $value;

        if (!class_exists('CFactory')) {
            require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );
        }
        require_once( JPATH_ROOT . '/components/com_community/models/profile.php' );
        $params = new CParameter($field['params']);
        $format = $params->get('date_format');
        $model = CFactory::getModel('profile');
        $myDate = $model->formatDate($value, $format);

        return $myDate;
    }

    /**
     * 
     * @param type $field
     * @param type $required
     * @return string
     */
    public function getFieldHTML($field, $required) {
        /**
         * @todo For now year can't larger than 2038 we can provide another solution for this later
         * @link http://www.php.net/mktime
         */
        /* Do parse max & min range into valid data */
        $this->_maxRange = $this->_getRange($this->params->get('maxrange', 100));
        $this->_minRange = $this->_getRange($this->params->get('minrange', -10)); /* maximum year can choose is +10 year from current year */

        $params = new CParameter($field->params);

        $html = '';

        $day = '';
        $month = 0;
        $year = '';

        $datepickerID = 'datePickerField' . $field->id;
        $showdate = '';

        $readonly = $params->get('readonly') && !COwnerHelper::isCommunityAdmin() ? ' disabled=""' : ' readonly=""';
        $style = $this->getStyle() ? ' style="' . $this->getStyle() . '" ' : '';
        if (!empty($field->value)) {
            if (!is_array($field->value)) {
                $myDateArr = explode(' ', $field->value);
            } else {
                $myDateArr[0] = $field->value[2] . '-' . $field->value[1] . '-' . $field->value[0];
            }

            if (is_array($myDateArr) && count($myDateArr) > 0) {
                $myDate = explode('-', $myDateArr[0]);

                if (strlen($myDate[0]) > 2) {
                    $year = !empty($myDate[0]) ? $myDate[0] : '';
                    $day = !empty($myDate[2]) ? $myDate[2] : '';
                } else {
                    $day = !empty($myDate[0]) ? $myDate[0] : '';
                    $year = !empty($myDate[2]) ? $myDate[2] : '';
                }

                $month = !empty($myDate[1]) ? $myDate[1] : '';
            }
        }

        if (empty($day) || empty($month) || empty($year)) {
            $showdate = '';
        } else {
            $showdate = $this->_fillZero($year, 4) . '-' . $this->_fillZero($month, 2) . '-' . $this->_fillZero($day, 2);
            $initData = "joms.jQuery(\"#" . $datepickerID . "\" ).datepicker (\"option\"," . $showdate . ");";
        }

        $class = ($field->required == 1) ? ' required' : '';
        $class .=!empty($field->tips) ? ' jomNameTips tipRight' : '';
        $title = ' title="' . CStringHelper::escape(JText::_($field->tips)) . '"';
        //CFactory::load( 'helpers' , 'string' );
        //$class	= !empty( $field->tips ) ? ' jomNameTips tipRight' : '';
        $html .= '<div style="display: inline-block;">';

        // Individual field should not have a tooltip
        //$class	= ($required) ? 'required' : '' ;

        $html .= '<input type="hidden" id="dpField' . $field->id . 'day" name="field' . $field->id . '[]" value="' . $day . '"  />';
        $html .= '<input type="hidden" id="dpField' . $field->id . 'month" name="field' . $field->id . '[]" value="' . $month . '" />';
        $html .= '<input type="hidden" id="dpField' . $field->id . 'year" name="field' . $field->id . '[]" value="' . $year . '" />';
        $html .= '<span id="errfield' . $field->id . 'msg" style="display:none;">&nbsp;</span>';
        $html .= "<input type=\"text\" id=\"" . $datepickerID . "\" style=\"width:auto; cursor: pointer;\" size=\"10\" class=\"" . $class . " input-medium\" value=\"" . $showdate . "\" class=\"input-small validate-custom-date " . $class . "\"" . $title . $style . $readonly . " />";
        $html .= "<script type=\"text/javascript\">\n";
        $html .= "joms.jQuery(\"#" . $datepickerID . "\" ).datepicker({\n";
        if (( isset($this->_maxRange) && $this->_maxRange != false ) && ( isset($this->_minRange) && $this->_minRange != false )) {
            $html .= "yearRange: \"" . $this->_maxRange['year'] . ':' . $this->_minRange['year'] . "\",\n";
            $html .= "maxDate: \"" . $this->_minRange['date'] . "\",\n";
            $html .= "minDate: \"" . $this->_maxRange['date'] . "\",\n";
        } else {
            if (( isset($this->_minRange) && $this->_minRange != false)) {
                $html .= "maxDate: \"" . $this->_minRange['date'] . "\",\n";
            }
            if (( isset($this->_maxRange) && $this->_maxRange != false)) {
                $html .= "minDate: \"" . $this->_maxRange['date'] . "\",\n";
            }
        }
        $html .= "changeMonth: true,\n";
        $html .= "changeYear: true,\n";
        $html .= "dateFormat: 'yy-mm-dd',\n";
        $html .= "showButtonPanel: true, \n";

        // Override datepicker text with Joomla language setting.
        $days = array(
            JText::_('COM_COMMUNITY_DATEPICKER_DAY_1'),
            JText::_('COM_COMMUNITY_DATEPICKER_DAY_2'),
            JText::_('COM_COMMUNITY_DATEPICKER_DAY_3'),
            JText::_('COM_COMMUNITY_DATEPICKER_DAY_4'),
            JText::_('COM_COMMUNITY_DATEPICKER_DAY_5'),
            JText::_('COM_COMMUNITY_DATEPICKER_DAY_6'),
            JText::_('COM_COMMUNITY_DATEPICKER_DAY_7')
        );

        $months = array(
            JText::_('COM_COMMUNITY_DATEPICKER_MONTH_1'),
            JText::_('COM_COMMUNITY_DATEPICKER_MONTH_2'),
            JText::_('COM_COMMUNITY_DATEPICKER_MONTH_3'),
            JText::_('COM_COMMUNITY_DATEPICKER_MONTH_4'),
            JText::_('COM_COMMUNITY_DATEPICKER_MONTH_5'),
            JText::_('COM_COMMUNITY_DATEPICKER_MONTH_6'),
            JText::_('COM_COMMUNITY_DATEPICKER_MONTH_7'),
            JText::_('COM_COMMUNITY_DATEPICKER_MONTH_8'),
            JText::_('COM_COMMUNITY_DATEPICKER_MONTH_9'),
            JText::_('COM_COMMUNITY_DATEPICKER_MONTH_10'),
            JText::_('COM_COMMUNITY_DATEPICKER_MONTH_11'),
            JText::_('COM_COMMUNITY_DATEPICKER_MONTH_12')
        );

        $monthNames = array_map(function ($item) {
            return "'" . $item . "'";
        }, $months);
        $monthNamesShort = array_map(function ($item) {
            return "'" . substr($item, 0, 3) . "'";
        }, $months);
        $dayNames = array_map(function ($item) {
            return "'" . $item . "'";
        }, $days);
        $dayNamesShort = array_map(function ($item) {
            return "'" . substr($item, 0, 3) . "'";
        }, $days);
        $dayNamesMin = array_map(function ($item) {
            return "'" . substr($item, 0, 2) . "'";
        }, $days);

        $html .= "closeText: '" . JText::_('COM_COMMUNITY_DATEPICKER_CLOSE') . "',\n";
        $html .= "prevText: '" . JText::_('COM_COMMUNITY_DATEPICKER_PREV') . "',\n";
        $html .= "nextText: '" . JText::_('COM_COMMUNITY_DATEPICKER_NEXT') . "',\n";
        $html .= "currentText: '" . JText::_('COM_COMMUNITY_DATEPICKER_CURRENT') . "',\n";
        $html .= "weekHeader: '" . JText::_('COM_COMMUNITY_DATEPICKER_WEEKHEADER') . "',\n";
        $html .= "monthNames: [ " . implode(", ", $monthNames) . " ],\n";
        $html .= "monthNamesShort: [ " . implode(", ", $monthNamesShort) . " ],\n";
        $html .= "dayNames: [ " . implode(", ", $dayNames) . " ],\n";
        $html .= "dayNamesShort: [ " . implode(", ", $dayNamesShort) . " ],\n";
        $html .= "dayNamesMin: [ " . implode(", ", $dayNamesMin) . " ],\n";
        $html .= "onClose: function ( selectedDate ) {\n";
        $html .= "var sDate = new Date(selectedDate);";
        $html .= "joms.jQuery(\"#dpField" . $field->id . "day\").val(sDate.getDate());\n";
        $html .= "joms.jQuery(\"#dpField" . $field->id . "month\").val(sDate.getMonth() + 1);\n";
        $html .= "joms.jQuery(\"#dpField" . $field->id . "year\").val(sDate.getUTCFullYear());\n";
        $html .= "}\n";
        $html .= "});\n";
        $html .= "</script>";
        $html .= '</div>';
        if (isset($initData)) {
            $html .= '<script>' . $initData . '</script>';
        }
        return $html;
    }

    public function isValid($value, $required) {
        if (($required && empty($value)) || !isset($this->fieldId)) {
            return false;
        }

        $db = JFactory::getDBO();
        $query = 'SELECT * FROM ' . $db->quoteName('#__community_fields')
                . ' WHERE ' . $db->quoteName('id') . '=' . $db->quote($this->fieldId);
        $db->setQuery($query);
        $field = $db->loadAssoc();

        $params = new CParameter($field['params']);
        $max_range = $params->get('maxrange');
        $min_range = $params->get('minrange');
        $value = JFactory::getDate(strtotime($value))->toUnix();
        $max_ok = true;
        $min_ok = true;

        //$ret = true;

        if ($max_range) {
            $max_range = JFactory::getDate(strtotime($max_range))->toUnix();
            $max_ok = ($value < $max_range);
        }
        if ($min_range) {
            $min_range = JFactory::getDate(strtotime($min_range))->toUnix();
            $min_ok = ($value > $min_range);
        }

        return ($max_ok && $min_ok) ? true : false;
        //return $ret;
    }

    public function formatdata($value) {
        $finalvalue = '';

        if (is_array($value)) {
            if (empty($value[0]) || empty($value[1]) || empty($value[2])) {
                $finalvalue = '';
            } else {
                $day = intval($value[0]);
                $month = intval($value[1]);
                $year = intval($value[2]);

                $day = !empty($day) ? $day : 1;
                $month = !empty($month) ? $month : 1;
                $year = !empty($year) ? $year : 1970;

                if (!checkdate($month, $day, $year)) {
                    return $finalvalue;
                }

                $finalvalue = $year . '-' . $month . '-' . $day . ' 23:59:59';
            }
        }

        return $finalvalue;
    }

    public function getType() {
        return 'date';
    }

    /**
     * Fill string with zeros until touch limit
     * @param any $val
     * @param int $limit
     * @return string
     */
    private function _fillZero($val, $limit) {
        /* Convert to string */
        $val = (string) $val;
        /* While strlen untouch limit */
        while (strlen($val) < $limit) {
            $val = '0' . $val;
        }
        return $val;
    }

    /**
     * 
     * @param type $value
     * @return string
     */
    protected function _getRange($value) {
        $range = array();
        /* We did enter age */
        if (is_numeric($value) || $value == '') {
            /* Convert into YYYY-MM-DD */
            $value = date("Y") - (int) $value . '-' . date('m') . '-' . date('d');
        }

        /* Extract YYYY-MM-DD */
        $parts = explode('-', $value);
        /* Make sure it's valid */
        if (is_array($parts) && count($parts) == 3) {
            /* Convert into timestamp */
            $now = time();
            $unixTimestamp = mktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
            /* Calc different time from now */
            $diffUnixTimestamp = $now - $unixTimestamp;
            /* Store data */
            $range['value'] = $value;
            $range['unix'] = $unixTimestamp;
            $range['now'] = $now;
            $range['year'] = $parts[0];
            $range['date'] = round($diffUnixTimestamp / 60 / 60 / 24, 0);

            if ($range['date'] > 0) /* past */
                $range['date'] = '-' . $range['date'] . 'd';
            else { /* future */
                if ($range['date'] == 0) {
                    $range['date'] = '+' . ( $range['date'] ) . 'd';
                } else {
                    $range['date'] = '+' . ( $range['date'] * -1 ) . 'd';
                }
            }
            return $range;
        }
    }

}
