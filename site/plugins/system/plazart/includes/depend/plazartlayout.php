<?php
/**
 *------------------------------------------------------------------------------
 * @package       Plazart Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2012-2013 TemPlaza.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       TemPlaza
 * @Link:         http://templaza.com
 *------------------------------------------------------------------------------
 */
	/**
	 * @package Helix Framework
	 * @author JoomShaper http://www.joomshaper.com
	 * @copyright Copyright (c) 2010 - 2013 JoomShaper
	 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
	*/
defined('JPATH_PLATFORM') or die;

class JFormFieldPlazartLayout extends JFormField
{
    public $type = 'PlazartLayout';

    protected function getInput()
    {
        $template = $this->form->getValue('template');

        $theme_path = JPATH_SITE . '/templates/' . $template . '/';
        $plazart_layout_path = JPATH_SITE . '/plugins/system/plazart/base/generate/';

        $layoutsettings = $this->value;

        $modChromes = array();
        if (file_exists($theme_path . 'html/modules.php')) {
            include_once($theme_path . 'html/modules.php');
        }

        $positions = $this->getPositions();
        $data   =   '<input type="hidden" name='.$this->name.' />';

        if (is_array($layoutsettings)) {
            $data .= $this->generateLayout($plazart_layout_path, $layoutsettings, $positions, $modChromes);
            return $data;
        } else {
            $layoutsettings = json_decode(file_get_contents($plazart_layout_path . 'default.json'));
            $data   .=   $this->generateLayout($plazart_layout_path, $layoutsettings, $positions, $modChromes);
            return $data;
        }
    }


    private function generateLayout($path, $layout, $positions, $modChromes)
    {
        ob_start();
        if (file_exists($path . 'generated.php')) {
            include_once($path . 'generated.php');
        }
        $items = ob_get_clean();
        return $items;

    }


    public function getLabel()
    {
        return false;
    }


    public function getPositions()
    {

        $db = JFactory::getDBO();
        $query = 'SELECT `position` FROM `#__modules` WHERE  `client_id`=0 AND ( `published` !=-2 AND `published` !=0 ) GROUP BY `position` ORDER BY `position` ASC';

        $db->setQuery($query);
        $dbpositions = (array)$db->loadAssocList();


        $template = $this->form->getValue('template');
        $templateXML = JPATH_SITE . '/templates/' . $template . '/templateDetails.xml';
        $template = simplexml_load_file($templateXML);
        $options = array();

        foreach ($dbpositions as $positions) $options[] = $positions['position'];

        foreach ($template->positions[0] as $position) $options[] = (string)$position;

        $options = array_unique($options);

        $selectOption = array();
        sort($selectOption);

        foreach ($options as $option) $selectOption[] = $option;

        return $selectOption;


    }
}