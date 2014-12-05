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

defined('_JEXEC') or die;
$doc = JFactory::getDocument();
$doc->addScript(JUri::base() . 'modules/mod_tz_news_pro/js/owl.carousel.js');
$doc->addStyleSheet(JUri::base() . 'modules/mod_tz_news_pro/css/owl.theme.css');
$doc->addStyleSheet(JUri::base() . 'modules/mod_tz_news_pro/css/owl.carousel.css');
$doc->addStyleSheet(JUri::base() . 'modules/mod_tz_news_pro/css/style.css');
if ($list):?>
    <div class="carousel">
        <?php if ($params->get('navigation', 1) == 1) : ?>
            <div class="navigation">
                <a id="showbiz_left_<?php echo $module->id; ?>" class="sb-navigation-left tz_new_pro_left"></a>
                <a id="showbiz_right_<?php echo $module->id; ?>" class="sb-navigation-right tz_new_pro_right"></a>
            </div>
        <?php endif; ?>
        <div id="TzNewPro<?php echo $module->id; ?>" class="owl-carousel owl-theme ">
            <?php foreach ($list as $item):
                $media = $item->media; ?>
                <?php if (!$media or ($media != null  AND $media->type != 'quote' AND $media->type != 'link' AND $media->type != 'audio')): ?>
                <div class="slide">
                    <div class="tz_carousel_default">
                        <?php if ($title == 1) : ?>
                            <h3 class="tz_carousel_title">
                                <a href="<?php echo $item->link; ?>"
                                   title="<?php echo $item->title; ?>">
                                    <?php echo $item->title; ?>
                                </a>
                            </h3>
                        <?php endif; ?>
                        <div class="infomation">
                            <?php if ($image == 1 or$des == 1): ?>
                                <div class="dv1">
                                    <?php if ($image == 1 AND $item->image != null) : ?>
                                        <div class="tz_carousel_image">
                                            <?php if ($media) :
                                                $title_image = $media->imagetitle;
                                            else :
                                                $title_image = $item->title;
                                            endif; ?>
                                            <a href="<?php echo $item->link; ?>">
                                                <img src="<?php echo $item->image; ?>"
                                                     title="<?php echo $title_image; ?>"
                                                     alt="<?php echo $title_image; ?>"/>
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($des == 1) : ?>
                                        <span class="tz_carousel_description">
                                <?php if ($limittext) :
                                    echo substr($item->intro, 3, $limittext);
                                else :
                                    echo $item->intro;
                                endif;?>
                                            <?php if ($readmore == 1) : ?>
                                                <span class="tz_carousel_readmore">
                                    <a href="<?php echo $item->link; ?>"><?php echo JText::_('MOD_TZ_NEWS_READ_MORE') ?></a>
                                </span>
                                            <?php endif; ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($date == 1 or $hits == 1 or $author_new == 1 or $cats_new == 1): ?>
                                <div class="dv2">
                                    <?php if ($date == 1) : ?>
                                        <span class="tz_carousel_date">
                                    <?php echo JText::sprintf('MOD_TZ_NEWS_DATE_ALL', JHtml::_('date', $item->created, JText::_('MOD_TZ_NEWS_DATE_FOMAT'))); ?>
                                </span>
                                    <?php endif; ?>

                                    <?php if ($hits == 1) : ?>
                                        <span class="tz_carousel_hits">
                                    <?php echo JText::sprintf('MOD_TZ_NEWS_HIST_LIST', $item->hit) ?>
                                </span>
                                    <?php endif; ?>

                                    <?php if ($author_new == 1): ?>
                                        <span class="tz_carousel_author">
                                <?php echo JText::sprintf('MOD_TZ_NEWS_AUTHOR', $item->author); ?>
                            </span>
                                    <?php endif; ?>

                                    <?php if ($cats_new == 1): ?>
                                        <span class="tz_carousel_category">
                                <?php echo JText::sprintf('MOD_TZ_NEWS_CATEGORY', $item->category); ?>
                            </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
                <!--use tz -portfolio-->
                <?php if ($show_quote == 1 AND $media AND $media->type == 'quote'): ?>
                <?php require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'carousel') . '_quote'); ?>
            <?php endif; ?>

                <?php if ($show_link == 1 AND $media AND $media->type == 'link'): ?>
                <?php require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'carousel') . '_link'); ?>
            <?php endif; ?>

                <?php if ($show_audio == 1 AND $media AND $media->type == 'audio'): ?>
                <?php require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'carousel') . '_audio'); ?>
            <?php endif; ?>

            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
<?php
$itemDesktop = round($options->items / 1.224);
$itemDesktopSmall = round($options->items / 1.563);
$itemTablet = round($options->items / 2.503);
if ($itemDesktop == 0):  $itemDesktop = 1;
else:  $itemDesktop = $itemDesktop;
endif;

if ($itemDesktopSmall == 0):  $itemDesktopSmall = 1;
else:  $itemDesktopSmall = $itemDesktopSmall;
endif;

if ($itemTablet == 0):  $itemTablet = 1;
else:  $itemTablet = $itemTablet;
endif;
?>
<script type="text/javascript">

    jQuery(document).ready(function () {
        var owl = jQuery("#TzNewPro<?php echo $module -> id;?>");
        owl.owlCarousel({
            items: <?php echo $options->items;?>,
            itemsDesktop: [1199, <?php echo $itemDesktop;?>],
            itemsDesktopSmall: [979, <?php echo $itemDesktopSmall;?>],
            itemsTablet: [768, <?php echo $itemTablet;?>],
            itemsMobile: [479, 1],
            slideSpeed:<?php echo $options -> slideSpeed;?>,
            paginationSpeed:<?php echo $options -> paginationSpeed; ?>,
            rewindSpeed:<?php echo  $options -> rewindSpeed;?>,
            autoPlay:<?php echo   $options -> autoPlay; ?>,
            stopOnHover: <?php echo  $options-> stopOnHover;?>,
            singleItem:<?php echo   $options -> singleItem;?>,
            rewindNav:<?php echo $options->rewindNav;?>,
            pagination:<?php echo   $options -> pagination;?>,
            paginationNumbers:<?php echo $options -> paginationNumbers; ?>,
            itemsScaleUp:<?php echo  $options -> itemsScaleUp;?>
        });
        // Custom Navigation Events
        jQuery("#showbiz_right_<?php echo $module->id;?>").click(function () {
            owl.trigger('owl.next');
        })
        jQuery("#showbiz_left_<?php echo $module->id;?>").click(function () {
            owl.trigger('owl.prev');
        })
    });
</script>


