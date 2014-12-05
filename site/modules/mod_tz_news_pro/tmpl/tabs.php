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
require_once(JPATH_SITE . '/libraries/tzm/sources/modules/modules.php');
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::base() . 'modules/mod_tz_news_pro/css/tab.css');
//var_dump(JPATH_SITE.'/libraries/tzm/sources/modules/modules.php');die;
//var_dump($list);die;
?>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('ul.tabs').each(function () {
            // For each set of tabs, we want to keep track of
            // which tab is active and it's associated content
            var $active, $content, $links = jQuery(this).find('a');
            // If the location.hash matches one of the links, use that as the active tab.
            // If no match is found, use the first link as the initial active tab.
            $active = jQuery($links.filter('[href="' + location.hash + '"]')[0] || $links[0]);
            $active.addClass('active');
            $content = jQuery($active.attr('href'));
            // Hide the remaining content
            $links.not($active).each(function () {
                jQuery(jQuery(this).attr('href')).hide();
            });
            // Bind the click event handler
            jQuery(this).on('click', 'a', function (e) {
                // Make the old tab inactive.
                $active.removeClass('active');
                $content.slideUp(450, function () {
                    $content.hide();
                });
                // Update the variables with the new link and content
                $active = jQuery(this);
                $content = jQuery(jQuery(this).attr('href'));

                // Make the tab active.
                $active.addClass('active');
                $content.slideDown(450, function () {
                    $content.show();
                });
                // Prevent the anchor's default click action
                e.preventDefault();
            });
        });

    })
</script>

<div class="tz-wrap">
    <?php if ($title_type == 'custom') :
        $titles = explode(",", $params->get('tabs_title_custom'));
    endif;
    if ($type_tab == 'modules') :
        if ($tabs < count($module_tab)) :
            $count = $tabs;
        else:
            $count = count($module_tab);
        endif; ?>
        <ul class="tabs">
            <?php for ($i = 0; $i < $count; $i++) :
                $a = XEFSourceModules::getModule($module_tab[$i]);
                if ($a->published != 0) :
                    if ($title_type == 'custom') :
                        $title = (isset($titles[$i])) ? $titles[$i] : '';
                    else :
                        $title = $a->title;
                    endif; ?>
                    <li class="tab_li">
                        <a clas="tab_a" href="#tab<?php echo $i; ?>">
                            <span class="tab_span">
                                <?php echo $title; ?>
                            </span>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endfor; ?>
        </ul>
        <?php for ($i = 0; $i < $count; $i++) :
        $a = XEFSourceModules::getModule($module_tab[$i]);
        if ($a->published == 1) : ?>
            <div class="tz_tabs_introtext" id="tab<?php echo $i; ?>">
                <?php echo JModuleHelper::renderModule($a); ?>
            </div>
        <?php endif; ?>
    <?php endfor; ?>
    <?php else: ?>
        <?php if ($tabs < count($list)) :
            $count = $tabs;
        else :
            $count = count($list);
        endif; ?>
        <ul class="tabs">
            <?php for ($i = 0; $i < $count; $i++) :$media = $list[$i]->media;
                if ($title_type == 'custom') :
                    $title = (isset($titles[$i])) ? $titles[$i] : '';
                else :
                    $title = $list[$i]->title;
                endif;?>
                <?php if (!$media or ($media AND $media->type != 'quote' AND $media->type != 'link' AND $media->type != 'audio')) : ?>
                    <li class="tab_li">
                        <a class="tab_a" href="#tab<?php echo $i; ?>">
                            <span class="tab_span"><?php echo $title; ?></span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($show_quote == 1 AND $media AND $media->type == 'quote'): ?>
                    <li class="tab_li">
                        <a class="tab_a" href="#tab<?php echo $i; ?>">
                            <span class="tab_span"><?php echo $title; ?></span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($show_link == 1 AND $media AND $media->type == 'link'): ?>
                    <li class="tab_li">
                        <a class="tab_a" href="#tab<?php echo $i; ?>">
                            <span class="tab_span"><?php echo $title; ?></span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($show_audio == 1 AND $media AND $media->type == 'audio'): ?>
                    <li class="tab_li">
                        <a class="tab_a" href="#tab<?php echo $i; ?>">
                            <span class="tab_span"><?php echo $title; ?></span>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endfor; ?>
        </ul>
        <?php for ($i = 0;
                   $i < $count;
                   $i++):
            $media = $list[$i]->media; ?>
            <?php if (!$media or ($media AND $media->type != 'quote' AND $media->type != 'link' AND $media->type != 'audio')): ?>
            <div class="tz_tabs_introtext" id="tab<?php echo $i; ?>">
                <div class="tz_tab_default">
                    <?php if ($title == 1) : ?>
                        <h3 class="tz_title">
                            <a href="<?php echo $list[$i]->link; ?>"
                               title="<?php echo $list[$i]->title; ?>">
                                <?php echo $list[$i]->title; ?>
                            </a>
                        </h3>
                    <?php endif; ?>
                    <?php if ($image == 1 || $des == 1): ?>
                        <div class="dv1">
                            <?php if ($image == 1 AND $list[$i]->image != null) : ?>
                                <div class="tz_tab_image">
                                    <?php if ($media) :
                                        $title_image = $media->imagetitle;
                                    else :
                                        $title_image = $list[$i]->title;
                                    endif; ?>
                                    <a href="<?php echo $list[$i]->link; ?>">
                                        <img src="<?php echo $list[$i]->image; ?>" title="<?php echo $title_image; ?>"/>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if ($des == 1) : ?>
                                <span class="tz_description">
                        <?php if ($limittext) :
                            echo substr($list[$i]->intro, 3, $limittext);
                        else :
                            echo $list[$i]->intro;
                        endif;?>
                                    <?php if ($readmore == 1) : ?>
                                        <span class="tz_readmore">
                                    <a href="<?php echo $list[$i]->link; ?>"><?php echo JText::_('MOD_TZ_NEWS_READ_MORE') ?></a>
                                </span>
                                    <?php endif; ?>
                            </span>
                            <?php endif; ?>

                        </div>
                    <?php endif; ?>
                    <?php if ($hits == 1 || $author_new == 1 || $cats_new == 1 || $date == 1): ?>
                        <div class="dv2">
                            <?php if ($date == 1) : ?>
                                <span class="tz_tab_date">
                                    <?php echo JText::sprintf('MOD_TZ_NEWS_DATE_ALL', JHtml::_('date', $list[$i]->created, JText::_('MOD_TZ_NEWS_DATE_FOMAT'))); ?>
                                </span>
                            <?php endif; ?>

                            <?php if ($hits == 1) : ?>
                                <span class="tz_tab_hits">
                                    <?php echo JText::sprintf('MOD_TZ_NEWS_HIST_LIST', $list[$i]->hit) ?>
                                </span>
                            <?php endif; ?>

                            <?php if ($author_new == 1): ?>
                                <span class="tz_tab_author">
                                <?php echo JText::sprintf('MOD_TZ_NEWS_AUTHOR', $list[$i]->author); ?>
                            </span>
                            <?php endif; ?>

                            <?php if ($cats_new == 1): ?>
                                <span class="tz_tab_category">
                                <?php echo JText::sprintf('MOD_TZ_NEWS_CATEGORY', $list[$i]->category); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div class="clearfix"></div>
                </div>
            </div>
        <?php endif; ?>
            <!--use tz -portfolio-->
            <?php if ($show_quote == 1 AND $media AND $media->type == 'quote'): ?>
            <?php require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'tabs') . '_quote'); ?>
        <?php endif; ?>

            <?php if ($show_link == 1 AND $media AND $media->type == 'link'): ?>
            <?php require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'tabs') . '_link'); ?>
        <?php endif; ?>

            <?php if ($show_audio == 1 AND $media AND $media->type == 'audio'): ?>
            <?php require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'tabs') . '_audio'); ?>
        <?php endif; ?>

        <?php endfor; ?>
    <?php endif; ?>
</div>




