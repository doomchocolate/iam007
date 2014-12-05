<?php
/*------------------------------------------------------------------------

# TZ Portfolio Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2012 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die;

if($this -> blogtags):
    $params = $this -> item -> params;

    $Itemid = null;
    $menu       = JFactory::getApplication() -> getMenu();
    $menuActive = $menu -> getItems('link','index.php?option=com_tz_portfolio&view=tags');
    if($menuActive && count($menuActive)){
        $Itemid = '&Itemid='.$menuActive[0] -> id;
    }
    if($mtagId = $params -> get('menu_active') && $params -> get('menu_active') != 'auto'){
        $Itemid = '&Itemid='.$mtagId;
    }
    $contentids = array();
    foreach($this -> blogtags as $_item){
        $contentids[]   = $_item -> contentid;
    }
    if(count($contentids) AND in_array($this -> item -> id,$contentids)):
?>
<div class="TzTags">
    <i class="fa fa-tag"></i>
    <?php
    foreach($this -> blogtags as $i => $item):
        if($this -> item -> id == $item -> contentid):
    ?>
    <a href="<?php echo JRoute::_('index.php?option=com_tz_portfolio&view=tags&id='.$item -> id.$Itemid,true,-1);?>"><?php echo JText::sprintf('TPL_TZ_WEISS_TAGS',$item -> name);?></a>
        <?php endif;?>
    <?php endforeach;?>
</div>
    <?php endif;?>
<?php endif;?>
