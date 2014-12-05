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
$document->addStyleSheet(JUri::base() . 'modules/mod_tz_news_pro/css/default.css');
?>
<div class="mod_tz_news">
    <ul class="tz_news">
        <?php if (isset($list) && !empty($list)) :
            foreach ($list as $item) :
                $media = $item->media; ?>
                <?php if (!$media or ($media != null  AND $media->type != 'quote' AND $media->type != 'link' AND $media->type != 'audio')): ?>
                <li class="tz_item_default">
                    <?php if ($title == 1) : ?>
                        <h6 class="tz_title">
                            <a href="<?php echo $item->link; ?>"
                               title="<?php echo $item->title; ?>">
                                <?php echo $item->title; ?>
                            </a>
                        </h6>
                    <?php endif; ?>
                    <?php if ($image == 1 or$des == 1): ?>
                        <div class="dv1">
                            <?php if ($image == 1 AND $item->image != null) : ?>
                                <div class="tz_image">
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
                                <span class="tz_description">
                                    <?php if ($limittext) :
                                        echo substr($item->intro, 3, $limittext);
                                    else :
                                        echo $item->intro;
                                    endif;?>
                                    <?php if ($readmore == 1) : ?>
                                        <span class="tz_readmore">
                                            <a href="<?php echo $item->link; ?>">
                                                <?php echo JText::_('MOD_TZ_NEWS_READ_MORE') ?>
                                            </a>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($date == 1 or $hits == 1 or $author_new == 1 or $cats_new == 1): ?>
                        <div class="dv2">
                            <?php if ($date == 1) : ?>
                                <span class="tz_date">
                                    <?php echo JText::sprintf('MOD_TZ_NEWS_DATE_ALL', JHtml::_('date', $item->created, JText::_('MOD_TZ_NEWS_DATE_FOMAT'))); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($hits == 1) : ?>
                                <span class="tz_hits">
                                    <?php echo JText::sprintf('MOD_TZ_NEWS_HIST_LIST', $item->hit) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($author_new == 1): ?>
                                <span class="tz_author">
                                    <?php echo JText::sprintf('MOD_TZ_NEWS_AUTHOR', $item->author); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($cats_new == 1): ?>
                                <span class="tz_category">
                                    <?php echo JText::sprintf('MOD_TZ_NEWS_CATEGORY', $item->category); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div class="clearfix"></div>
                </li>
            <?php endif; ?>
                <!--use tz -portfolio-->
                <?php if ($show_quote == 1 AND $media AND $media->type == 'quote'): ?>
                <?php require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'default') . '_quote'); ?>
            <?php endif; ?>

                <?php if ($show_link == 1 AND $media AND $media->type == 'link'): ?>
                <?php require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'default') . '_link'); ?>
            <?php endif; ?>

                <?php if ($show_audio == 1 AND $media AND $media->type == 'audio'): ?>
                <?php require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'default') . '_audio'); ?>
            <?php endif; ?>

            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>