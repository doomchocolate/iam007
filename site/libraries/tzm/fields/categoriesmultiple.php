<?php


// no direct access
defined('_JEXEC') or die('Restricted access');
if( file_exists( JPATH_SITE .'/components/com_k2/k2.php') )
{

    jimport('joomla.form.formfield');

    class JFormFieldCategoriesMultiple extends JFormField
    {

        var	$type = 'categoriesmultiple';

        function getInput(){
            $params = JComponentHelper::getParams('com_k2');

            $db = JFactory::getDBO();
            $query = 'SELECT m.* FROM #__k2_categories m WHERE published=1 AND trash = 0 ORDER BY parent, ordering';
            $db->setQuery( $query );
            $mitems = $db->loadObjectList();
            $children = array();
            if ($mitems){
                foreach ( $mitems as $v ){
                    $v->title = $v->name;
                    $v->parent_id = $v->parent;
                    $pt = $v->parent;
                    $list = @$children[$pt] ? $children[$pt] : array();
                    array_push( $list, $v );
                    $children[$pt] = $list;
                }
            }
            $attr  = 'class="inputbox chzn-select"';
            $attr .= 'style="width:280px;"';
            $attr .= 'multiple="multiple"';
            $attr .= 'data-placeholder="Click here to select categories"';

            $list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0 );
            $mitems = array();

            foreach ( $list as $item ) {
                $item->treename = JString::str_ireplace('&#160;', '- ', $item->treename);
                $mitems[] = JHTML::_('select.option',  $item->id, '   '.$item->treename );
            }

            $fieldName = $this->name.'[]';

            $output= JHTML::_('select.genericlist',  $mitems, $fieldName, trim($attr), 'value', 'text', $this->value );
            return $output;
        }
    }
}