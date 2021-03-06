<?php
/*------------------------------------------------------------------------

# TZ  Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2012 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access');
?>
<div class="TzHikaCategory <?php echo $moduleclass_sfx;?> swiper">
    <div id="tz-hikashop-swiper<?php echo $module -> id;?>" class="swiper-container"<?php echo ($itemHeight = $params -> get('image_height',250))?' style="height:'.$itemHeight.'px; min-height: 0;"':'';?>>
        <div class="swiper-wrapper">
            <?php foreach($list as $i => $item):?>
            <?php
            $image_options = array('default' => true,'forcesize'=>$params -> get('image_force_size',0),'scale'=>$params -> get('image_scale_mode','inside'));
            $img = $image->getThumbnail($item->file_path, array('width' => $image->main_thumbnail_x, 'height' => $image ->main_thumbnail_y), $image_options);
//                if($i == 1){
//                    var_dump($item -> file_path);
//                    die;
//                }
            if($img->success) {
            ?>

            <?php
            $itemWidth  = null;
//            $imgSize      = JImage::getImageFileProperties($img -> url);
            $itemWidth  = (int)($itemHeight * $img -> orig_width / $img -> orig_height);
            ?>
            <div class="swiper-slide"<?php echo ($itemWidth)?' style="width:'.$itemWidth.'px;"':'';?>>
                <div class="heading bg-overlay-30">
                    <div class="top">
                        <h3 class="title">
                            <a href="<?php echo $item ->link;?>"><?php echo $item -> category_name; ?></a>
                        </h3>
                    </div>
                </div>
                    <img src="<?php echo $img -> url; ?>"
                         title="<?php echo $item -> file_description; ?>"
                         alt="<?php echo $item -> file_description; ?>"/>
            </div>
            <?php }?>
            <?php endforeach; ?>
        </div>
        <div class="pagination"></div>
    </div>
</div>
<script>
    jQuery(function(){
        var tzSwiper<?php echo $module -> id;?> = function(){
            var section = jQuery('#tz-hikashop-swiper<?php echo $module -> id;?>').parents('section');
            var itemH   = section.height();
            var itemL   = 0;
            <?php if($params -> get('image_height',250)):?>
            itemH   = <?php echo $params -> get('image_height',250);?>;
            <?php endif;?>
            section.find('.module-title').height('none');
            var mheaderH   = jQuery(window).width() *0.06;
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
            var orgH    = <?php echo $params -> get('image_height',250)?>;
            if(jQuery(window).width() <= 767){
                if(orgH > 200){
                    var newH    = orgH* 2 / 3;
                        jQuery('#tz-hikashop-swiper<?php echo $module -> id;?>').height(newH);
                    jQuery('#tz-hikashop-swiper<?php echo $module -> id;?>').find('.swiper-slide').each(function(){
                       jQuery(this).width(jQuery(this).width() * newH / orgH)
                    });
                }
            }
            var mySwiper = new Swiper('#tz-hikashop-swiper<?php echo $module -> id;?>',{
                pagination: '.pagination',
                paginationClickable: true,
                slidesPerView: 'auto'
            });

            tzSwiper<?php echo $module -> id;?>();
        });
        jQuery(window).bind('resize',function(){
            var orgH    = <?php echo $params -> get('image_height',250)?>;
            if(jQuery(window).width() <= 767){
                if(orgH > 200){
                    var newH    = orgH* 2 / 3;
                    jQuery('#tz-hikashop-swiper<?php echo $module -> id;?>').height(orgH * 2 / 3);
                    jQuery('#tz-hikashop-swiper<?php echo $module -> id;?>').find('.swiper-slide').each(function(){
                        var img = new Image();
                        img.src = jQuery(this).find('img').attr('src');
                        jQuery(this).width((orgH * img.width/img.height) * newH / orgH)
                    });
                }
            }else{
                jQuery('#tz-hikashop-swiper<?php echo $module -> id;?>').height(orgH);
                jQuery('#tz-hikashop-swiper<?php echo $module -> id;?>').find('.swiper-slide').each(function(){
                    var img = new Image();
                    img.src = jQuery(this).find('img').attr('src');
                    jQuery(this).width(orgH * img.width/img.height);
                });
            }
            tzSwiper<?php echo $module -> id;?>();
        });
    });
</script>