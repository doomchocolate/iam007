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

if(count($media)):
    if($media[0] -> type == 'link'):
        ?>
        <div class="TzLink">
            <?php if ($params->get('show_category',1)) : ?>
                <div class="TZcategory-name">
                    <i class="fa fa-external-link"></i>
                    <?php $title = $this->escape($item->category_title);
                    $url = '<a href="' . JRoute::_(TZ_PortfolioHelperRoute::getCategoryRoute($item->catid)) . '">' . $title . '</a>'; ?>
                    <?php if ($params->get('link_category',1)) : ?>
                        <?php echo $url; ?>
                    <?php else : ?>
                        <?php echo $title; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <h3 class="title">
                <a href="<?php echo $media[0] -> link_url?>"
                   rel="<?php echo $media[0] -> link_follow;?>"
                   target="<?php echo $media[0] -> link_target?>"><?php echo $media[0] -> link_title;?></a>
            </h3>
            <?php  if ($params->get('show_intro',1) AND !empty($item -> introtext)) :?>
                <div class="introtext">
                    <?php echo $item -> introtext;?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif;?>
<?php endif;?>