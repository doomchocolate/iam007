<?php

/*------------------------------------------------------------------------

# MOD_TZ_NEW_PRO Extension

# ------------------------------------------------------------------------

# author    tuyennv

# copyright Copyright (C) 2013 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die;

if (isset($list) && !empty($list)) :
    $doc    = JFactory::getDocument();

    $doc -> addScript(JUri::base() . 'modules/mod_tz_news_pro/js/resizeimage.js');
    $doc -> addScriptDeclaration('
    jQuery(function(){
        var tzdefault'.$module -> id.'  = function(){
        var itemper = 22.4916 / 100;
            if(jQuery(window).width() > 767){

                if(jQuery(window).width() <= 979){
                    itemper = 48.3278 /100;
                }

                var itemW   = Math.floor(jQuery("#tz_news_pro_default2'.$module -> id.'").width()  * itemper);
                var margin  = jQuery("#tz_news_pro_default2'.$module -> id.' ul li:last-child").outerWidth(true) - jQuery("#tz_news_pro_default2'.$module -> id.' ul li:last-child").outerWidth();
                itemW   +=  margin/4;

                jQuery("#tz_news_pro_default2'.$module -> id.' ul li:first-child").css("padding-right", itemW);
                jQuery("#tz_news_pro_default2'.$module -> id.' li .item").css({
                    "width":itemW
                });
                jQuery("#tz_news_pro_default2'.$module -> id.' li .tz-content").css({
                    "width":itemW - margin,
                    "left":itemW
                });

                jQuery("#tz_news_pro_default2'.$module -> id.' li").each(function(){
//                    if(jQuery(window).width() > 767){
                        if(jQuery(this).css("padding-right")){
                            jQuery(this).siblings("li").css("padding-right","").end()
                                .find(".tz-content").css("opacity",1).end().css("padding-right",itemW);
                        }
                        jQuery(this).find(".tz-content").css("left",itemW);
                        jQuery(this).hover(function(){
                            jQuery(this).siblings("li").css("padding-right","").end()
                                .find(".tz-content").css("opacity",1).end().css("padding-right",itemW);
                        });
                        var img = new Image();
                        img.src = jQuery(this).find(".image img").attr("src");
                        var imgsize = resizeImage(img.width,img.height,itemW,jQuery(this).height());
                        jQuery(this).find(".image img").css({
                            width: imgsize.width,
                            height: imgsize.height,
                            left: imgsize.left,
                            top: imgsize.top
                        });
//                    }
                });
            }
        };
        jQuery(document).ready(function(){
            tzdefault'.$module -> id.'();
        });
        jQuery(window).bind("resize",function(){
            tzdefault'.$module -> id.'();
        });
    });
    ');
    if($itemH = $params -> get('item_height',219)){
        $doc -> addStyleDeclaration('
            #tz_news_pro_default2'.$module -> id.' li,
            #tz_news_pro_default2'.$module -> id.' .image{
                height: '.$itemH.'px;
            }
        ');
    }
?>
    <div id="tz_news_pro_default2<?php echo $module -> id;?>" class="tz-news-pro<?php echo htmlspecialchars($params -> get('moduleclass_sfx'));?>">
            <?php foreach ($list as $i => $item) : ?>
        <?php if($i % 3 == 0):?>
        <ul>
        <?php endif;?>
            <li>
                <div class="item image">
                    <div class="bg-overlay"></div>
                <?php if ($readmore == 1) : ?>
                    <a href="<?php echo $item->link; ?>" class="readmore">
                        <i class="icon-plus-3"></i>
                    </a>
                <?php endif; ?>
                <?php if ($image == 1 or$des == 1): ?>
                    <?php if ($image == 1 AND $item->image != null) : ?>

                    <?php if (isset($media)) :
                        $title_image = $media->imagetitle;
                    else :
                        $title_image = $item->title;
                    endif; ?>
                    <a href="<?php echo $item->link; ?>">
                        <img src="<?php echo $item->image; ?>" title="<?php echo $title_image; ?>"
                             alt="<?php echo $title_image; ?>"/>
                    </a>
                    <?php endif;?>
                <?php endif;?>
                </div>
                <div class="item tz-content">
                    <?php if ($date == 1 or $hits == 1 or $author_new == 1 or $cats_new == 1): ?>
                    <div class="info">
                        <?php if ($date == 1) : ?>
                        <span class="tz_date">
                            <?php echo JHtml::_('date', $item->created, JText::_('MOD_TZ_NEWS_DATE_FOMAT')); ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($hits == 1) : ?>
                        <span class="tz_hits">
                            <?php echo $item->hit ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($cats_new == 1): ?>
                            <span class="tz_category">
                            <?php echo $item->category; ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($author_new == 1): ?>
                        <span class="tz_author pull-right">
                            <?php echo $item->author; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($title == 1) : ?>
                    <h4 class="title">
                        <a href="<?php echo $item->link; ?>"
                           title="<?php echo $item->title; ?>">
                            <?php echo $item->title; ?>
                        </a>
                    </h4>
                    <?php endif;?>
                    <div class="description">
                        <?php if ($limittext) :
                            echo substr($item->intro, 3, $limittext);
                        else :
                            echo $item->intro;
                        endif;?>
                    </div>
                </div>
            </li>
        <?php if($i % 3 == 2 OR $i == (count($list) -1) ):?>
        </ul>
        <?php endif;?>
            <?php endforeach;?>
    </div>
<?php endif;?>