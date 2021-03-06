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

$item       = $this -> item;
$media      = $this -> listMedia;
$params     = $this -> item -> params;

if($params -> get('show_quote_text',1) OR $params -> get('show_quote_author',1)):
    if(count($media)):
        if($media[0] -> type == 'quote'):
?>
    <div class="TzQuote media">
        <?php if($params -> get('show_quote_text',1)):?>
        <i class="fa fa-quote-left pull-left"></i>
        <div class="media-body">
            <div class="text">
                <?php echo $media[0] -> quote_text;?>
                <?php if($item -> featured == 1):?>
                    <span class="TzFeature"><?php echo JText::_('COM_TZ_PORTFOLIO_FEATURE');?></span>
                <?php endif;?>
            </div>
            <?php endif;?>

            <?php if($params -> get('show_quote_author',1)):?>
                <span class="muted author"><i class="fa fa-user"></i><?php echo $media[0] -> quote_author;?></span>
            <?php endif;?>
        </div>
    </div>
        <?php endif;?>
    <?php endif;?>
<?php endif;?>