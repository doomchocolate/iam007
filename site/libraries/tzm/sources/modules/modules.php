<?php


defined('_JEXEC') or die();

// Require XEF helper class
require_once JPATH_LIBRARIES . '/tzm/tzm.php';

class XEFSourceModules extends XEFHelper
{
    public function getItems()
    {

        //module specific
        $mods       = $this->get('modules');


        $options 	= array('style' => 'none');
        $items      = array();


        for ($i=0;$i<count($mods);$i++)
        {

            $items[$i]->order 	= $this->getModule($mods[$i])->ordering;
            $items[$i]->title 	= $this->getModule($mods[$i])->title;
            $items[$i]->content = $items[$i]->introtext = JModuleHelper::renderModule( $this->getModule($mods[$i]), $options);

        }

        return $items;
    }

    //fetch module by id
    public function getModule( $id ){

        $db		= JFactory::getDBO();
        $where  = ' AND ( m.id='.$id.' ) ';

        $query  = 'SELECT *'.
            ' FROM #__modules AS m'.
            ' WHERE m.client_id = 0'.
            $where.
            ' ORDER BY ordering'.
            ' LIMIT 1';

        $db->setQuery( $query );
        $module = $db->loadObject();

        if (!$module) return null;

        $file				= $module->module;
        $custom				= substr($file, 0, 4) == 'mod_' ?  0 : 1;
        $module->user		= $custom;
        $module->name		= $custom ? $module->title : substr($file, 4);
        $module->style		= null;
        $module->position	= strtolower($module->position);
        $clean[$module->id]	= $module;

        return $module;
    }

}