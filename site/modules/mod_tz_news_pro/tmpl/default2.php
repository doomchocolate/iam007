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
    ?>
    <div class="tz-news-pro<?php echo htmlspecialchars($params -> get('moduleclass_sfx'));?>">
<!--        <ul>-->
            <?php foreach ($list as $i => $item) : ?>
        <?php if($i % 3 == 0):?>
        <ul>
        <?php endif;?>
            <li>
                <div class="item image">
                    <div class="bg-overlay"></div>
                <?php if ($readmore == 1) : ?>
                    <a href="<?php echo $item->link; ?>" class="readmore">
                        <?php echo JText::_('MOD_TZ_NEWS_READ_MORE') ?>
                    </a>
                <?php endif; ?>
                <?php if ($image == 1 or$des == 1): ?>
                    <?php if ($image == 1 AND $item->image != null) : ?>

                    <?php if ($media) :
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
                    <h4>
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
    <script type="text/javascript">
        jQuery(function(){
            var tzfunction  = function(){
                if(jQuery(window).width() > 767){

                    var itemW   = (jQuery('.tz-news-pro').width() *22.4916) / 100;

                    jQuery('.tz-news-pro ul li:first-child').css('padding-right', itemW);
                    jQuery('.tz-news-pro li .item').css({
                        'width':itemW
                    });
                    jQuery('.tz-news-pro li .tz-content').css({
                        'width':itemW - 60,
                        'left':itemW
                    });

                    jQuery('.tz-news-pro li').each(function(){
                        jQuery(this).find('.tz-content').css('left',itemW);
                        jQuery(this).hover(function(){
                            jQuery(this).siblings('li').css('padding-right','').end()
                                .find('.tz-content').css('opacity',1).end().css('padding-right',itemW);
                        });
                    });
                }
            };
            jQuery(document).ready(function(){
                tzfunction();
            });
        });
    </script>
<?php endif;?>