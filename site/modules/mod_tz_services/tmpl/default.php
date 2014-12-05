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

//no direct access
defined('_JEXEC') or die();
if($list):
//$doc    = JFactory::getDocument();
//$doc -> addStyleSheet(JUri::root().'modules/mod_tz_services/css/style.css');
$columnCount = ceil(12/$params -> get('column_width',4));
    $background = $params -> get('background','');
?>
<div class="TzServices<?php echo $moduleclass_sfx;?>">

<?php if(!empty($background)):?>
    <div class="row-fluid">
        <div class="span6">
<?php endif;?>
    <?php if($params -> get('show_heading') AND ($heading = $params -> get('heading',null))):?>
        <h4 class="heading"><span><?php echo $heading;?></span></h4>
    <?php endif; ?>
<?php foreach ($list as $i => $item) :	?>
    <?php if($i % $columnCount == 0):?>
    <div class="row-fluid">
    <?php endif;?>
        <div class="span<?php echo $params -> get('column_width',4);?>">
            <div class="media">
                <?php if($item -> services_icon_font AND !empty($item -> services_icon_font)):?>
                    <span class="media-object <?php echo $item -> services_icon_font; ?>"></span>
                <?php else: ?>
                    <?php if($item -> services_image AND !empty($item -> services_image)):?>
                        <img src="<?php echo JUri::root().$item -> services_image;?>"
                             class="media-object"
                             alt="<?php echo $item -> services_title;?>">
                    <?php endif;?>
                <?php endif;?>
                <div class="media-body">
                    <h3 class="media-heading title"><?php echo $item -> services_title;?></h3>
                    <div class="description">
                        <?php echo $item -> services_description;?>
                    </div>
                </div>
            </div>
        </div>
    <?php if($i % $columnCount == ($columnCount - 1) OR $i == (count($list) - 1)): ?>
    </div>
    <?php endif;?>
<?php endforeach; ?>

<?php if(!empty($background)):?>
    </div>
    <div class="span6">
        <img src="<?php echo JUri::base().$background;?>" alt=""/>
    </div>
</div>
<?php endif;?>
</div>
<?php endif;?>