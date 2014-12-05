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

$doc    = JFactory::getDocument();
$doc -> addScript('modules/mod_tz_news_pro/js/idangerous.swiper-2.1.min.js');
$doc -> addStyleSheet('modules/mod_tz_news_pro/css/idangerous.swiper.css');
if ($list):
    $imgObject  = new JImage();
?>
<div class="tz-news-pros<?php echo htmlspecialchars($params -> get('moduleclass_sfx'));?> swiper">
    <div id="tz-swiper<?php echo $module -> id;?>" class="swiper-container"<?php echo ($itemHeight = $params -> get('itemHeight',250))?' style="height:'.$itemHeight.'px; min-height: 0;"':'';?>>
        <div class="swiper-wrapper">
            <?php foreach ($list as $item):
            $media = $item->media; ?>
            <?php if (!$media or ($media != null  AND $media->type != 'quote' AND $media->type != 'link' AND $media->type != 'none')): ?>
            <?php
                $itemWidth  = null;
                $imgObject ->destroy();
                $imgObject -> loadFile($item -> image);
                $itemWidth  = (int)($itemHeight * $imgObject -> getWidth() / $imgObject -> getHeight());
            ?>
            <div class="swiper-slide"<?php echo ($itemWidth)?' style="width:'.$itemWidth.'px;"':'';?>>
                <?php if ($title == 1 or $date == 1 or $hits == 1 or $author_new == 1 or $cats_new == 1) : ?>
                <div class="heading bg-overlay-30">
                    <div class="top">
                        <?php if ($title == 1) : ?>
                            <h3 class="title">
                                <a href="<?php echo $item->link; ?>"
                                   title="<?php echo $item->title; ?>">
                                    <?php echo $item->title; ?>
                                </a>
                            </h3>
                        <?php endif; ?>
                        <?php if ($date == 1 or $hits == 1 or $author_new == 1 or $cats_new == 1): ?>
                            <div class="information">

                                <?php if ($author_new == 1): ?>
                                    <span class="author">
                                    <?php echo JText::sprintf('TPL_TZ_WEISS_AUTHOR', $item->author); ?>
                                </span>
                                <?php endif; ?>

                                <?php if ($date == 1) : ?>
                                    <span class="created">
                                    <?php echo JText::sprintf( JHtml::_('date', $item->created, JText::_('TPL_TZ_WEISS_DATE_FOMAT'))); ?>
                                </span>
                                <?php endif; ?>

                                <?php if ($hits == 1) : ?>
                                    <span class="hits">
                                    <?php echo JText::sprintf('MOD_TZ_NEWS_HIST_LIST', $item->hit) ?>
                                </span>
                                <?php endif; ?>


                                <?php if ($cats_new == 1): ?>
                                    <span class="category">
                                        <?php echo JText::sprintf('MOD_TZ_NEWS_CATEGORY', $item->category); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($image == 1 or $des == 1):?>
                    <?php if ($image == 1 AND $item->image != null) : ?>
                        <?php if ($media) :
                            $title_image = $media->imagetitle;
                        else :
                            $title_image = $item->title;
                        endif;
                        ?>
                        <!--                                            <a href="--><?php //echo $item->link; ?><!--">-->
                        <img src="<?php echo $item->image; ?>"
                             title="<?php echo $title_image; ?>"
                             alt="<?php echo $title_image; ?>"/>
                        <!--                                            </a>-->
                    <?php endif; ?>

                    <?php if ($des == 1) : ?>
                    <span class="description">
                        <?php if ($limittext) :
                            echo substr($item->intro, 3, $limittext);
                        else :
                            echo $item->intro;
                        endif;?>
                        <?php if ($readmore == 1) : ?>
                        <span class="readmore">
                            <a href="<?php echo $item->link; ?>"><?php echo JText::_('MOD_TZ_NEWS_READ_MORE') ?></a>
                        </span>
                        <?php endif; ?>
                    </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endif;?>
            <?php endforeach;?>
        </div>
        <?php if($params -> get('pagination',0)):?>
        <div class="pagination"></div>
        <?php endif;?>
    </div>
</div>
<script>
    jQuery(function(){
        var tzSwiper<?php echo $module -> id;?> = function(){
            var section = jQuery('#tz-swiper<?php echo $module -> id;?>').parents('section');
            var itemH   = section.height();
            var itemL   = 0;
            <?php if($params -> get('itemHeight')):?>
            itemH   = <?php echo $params -> get('itemHeight');?>;
            <?php endif;?>

            section.find('.module-title').height('none');
//            var mheaderH   = section.find('.module-title').outerHeight();
            var mheaderH   = jQuery(window).width() * 0.06;
            if(section.find('> .container-fluid').length){
                if(section.width() > section.find('> .container-fluid').width()){
                    mheaderH    = section.width() - section.find('> .container-fluid').width();
                    itemL       = -(itemH + mheaderH);
                }
            }else{
                section.find('#Mod<?php echo $module -> id;?>').css({
                   'padding-left': mheaderH
                });
                itemL       = (mheaderH - itemH);
            }
            if(jQuery(window).width() > 767){
                if(!jQuery.browser.msie || (jQuery.browser.msie && Math.floor(jQuery.browser.version) > 8)){
                    section.find('.module-title')
                        .css({
                            'width': itemH,
                            height: mheaderH,
                            'top': Math.ceil(itemH - mheaderH)/2,
                            'left': itemL/2
                        })
                        .find('span').css({
                            height: Math.ceil(mheaderH),
                            'line-height': Math.floor(mheaderH/2) + 'px'
                        });
                }else{
                    section.find('.module-title')
                        .css({
                            'width': itemH,
                            height: mheaderH,
                            'top': 0,
                            'left': - section.find('.module-title').outerWidth()
                        });
                }
            }else{
                section.find('#Mod<?php echo $module -> id;?>').css({
                    'padding-left': 'none'
                });
                section.find('.module-title')
                    .css({
                        'width': itemH,
                        height: mheaderH,
                        'top': 0,
                        'left': 0
                    });
            }
        };
        jQuery(document).ready(function(){
            var orgH    = <?php echo $params -> get('itemHeight',250)?>;
            if(jQuery(window).width() <= 767){
                if(orgH > 200){
                    var newH    = orgH* 2 / 3;
                    jQuery('#tz-swiper<?php echo $module -> id;?>').height(newH);
                    jQuery('#tz-swiper<?php echo $module -> id;?>').find('.swiper-slide').each(function(){
                        jQuery(this).width(jQuery(this).width() * newH / orgH)
                    });
                }
            }
            var mySwiper = new Swiper('#tz-swiper<?php echo $module -> id;?>',{
                <?php if($params -> get('pagination',0)):?>
                pagination: '.pagination',
                paginationClickable: true,
                <?php endif;?>
                slidesPerView: 'auto'
            });

            tzSwiper<?php echo $module -> id;?>();
        });
        jQuery(window).bind('resize',function(){
            var orgH    = <?php echo $params -> get('itemHeight',250)?>;
            if(jQuery(window).width() <= 767){
                if(orgH > 200){
                    var newH    = orgH* 2 / 3;
                    jQuery('#tz-swiper<?php echo $module -> id;?>').height(orgH * 2 / 3);
                    jQuery('#tz-swiper<?php echo $module -> id;?>').find('.swiper-slide').each(function(){
                        var img = new Image();
                        img.src = jQuery(this).find('img').attr('src');
                        jQuery(this).width((orgH * img.width/img.height) * newH / orgH)
                    });
                }
            }else{
                jQuery('#tz-swiper<?php echo $module -> id;?>').height(orgH);
                jQuery('#tz-swiper<?php echo $module -> id;?>').find('.swiper-slide').each(function(){
                    var img = new Image();
                    img.src = jQuery(this).find('img').attr('src');
                    jQuery(this).width(orgH * img.width/img.height);
                });
            }
            tzSwiper<?php echo $module -> id;?>();
        });
    });
</script>
<?php endif;?>