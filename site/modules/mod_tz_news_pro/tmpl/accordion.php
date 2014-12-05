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
$document = JFactory::getDocument();
$document->addScript(JUri::base() . 'modules/mod_tz_news_pro/js/jquery-ui-1.10.3.custom.js');
$document->addScript(JUri::base() . 'modules/mod_tz_news_pro/js/jquery.accordion.js');
$document->addStyleSheet(JUri::base() . 'modules/mod_tz_news_pro/css/accordion.css');
?>
<div class="mod_tz_news ">
    <div class="tz_news">
        <?php if (isset($list) && !empty($list)) : ?>
            <?php foreach ($list as $i => $item) :
                $media = $item->media;?>
                <?php if (!$media or ($media != null AND $media->type != 'quote' AND $media->type != 'link' AND $media->type != 'audio')): ?>
                <div class="tz_accordion" id="section<?php echo $i; ?>">
                    <h3 class="tz_accordion_title">
                        <?php echo $item->title; ?>
                    </h3>
                    <span class=""></span>
                </div>
                <div class="tz_accordion_default info_accordion">
                    <?php if ($image == 1 or$des == 1): ?>
                        <div class="dv1">
                            <?php if ($image == 1 AND $item->image != null) : ?>
                                <?php if ($media) :
                                    $title_image = $media->imagetitle;
                                else :
                                    $title_image = $item->title;
                                endif; ?>
                                <div class="tz_accordion_image">
                                    <a href="<?php echo $item->link; ?>">
                                        <img src="<?php echo $item->image; ?>"
                                             title="<?php echo $title_image; ?>"
                                             alt="<?php echo $title_image; ?>"/>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if ($des == 1) : ?>
                                <div class="tz_accordion_description">
                                    <?php if ($limittext) :
                                        echo substr($item->intro, 3, $limittext);
                                    else :
                                        echo $item->intro;
                                    endif;?>
                                    <?php if ($readmore == 1) : ?>
                                        <div class="tz_readmore">
                                            <a href="<?php echo $item->link; ?>"><?php echo JText::_('MOD_TZ_NEWS_READ_MORE') ?></a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($date == 1 or $hits == 1 or $author_new == 1 or $cats_new == 1): ?>
                    <div class="option dv2">
                        <?php endif; ?>
                        <?php if ($date == 1) : ?>
                            <div class="tz_accordion_date">
                                <?php echo JText::sprintf('MOD_TZ_NEWS_DATE_ALL', JHtml::_('date', $item->created, JText::_('MOD_TZ_NEWS_DATE_FOMAT'))); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($hits == 1) : ?>
                            <div class="tz_accordion_hits">
                                <?php echo JText::sprintf('MOD_TZ_NEWS_HIST_LIST', $item->hit) ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($author_new == 1): ?>
                            <div class="tz_accordion_author">
                                <?php echo JText::sprintf('MOD_TZ_NEWS_AUTHOR', $item->author); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($cats_new == 1): ?>
                            <div class="tz_accordion_category">
                                <?php echo JText::sprintf('MOD_TZ_NEWS_CATEGORY', $item->category); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($date == 1 or $hits == 1 or $author_new == 1 or $cats_new == 1): ?>
                    </div>
                <?php endif; ?>
                </div>
                <div class="clearfix"></div>
            <?php endif; ?>
                <!--use tz -portfolio-->
                <?php if ($show_quote == 1 AND $media AND $media->type == 'quote'): ?>
                <?php require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'accordion') . '_quote'); ?>
            <?php endif; ?>
                <?php if ($show_link == 1 AND $media AND $media->type == 'link'): ?>
                <?php require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'accordion') . '_link'); ?>
            <?php endif; ?>
                <?php if ($show_audio == 1 AND $media AND $media->type == 'audio'): ?>
                <?php require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'accordion') . '_audio'); ?>
            <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function () {

        jQuery('.tz_accordion ').accordion({
            defaultOpen: 'section0'

        });

    });
</script>