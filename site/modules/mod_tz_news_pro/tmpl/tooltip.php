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
$document->addStyleSheet(JUri::base() . 'modules/mod_tz_news_pro/css/stickytooltip.css');
?>

<script type="text/javascript">

    var stickytooltip = {
        tooltipoffsets: [20, -30], //additional x and y offset from mouse cursor for tooltips
        fadeinspeed: "<?php echo $fadeinspeed ; ?>", //duration of fade effect in milliseconds
        rightclickstick: true, //sticky tooltip when user right clicks over the triggering element (apart from pressing "s" key) ?
        stickybordercolors: ["<?php echo $border_s; ?>", "<?php echo $border_out; ?>"], //border color of tooltip depending on sticky state
        stickynotice1: ["<?php echo JText::_('TZ_MOD_NEWS_PRESS_S_CLICK'); ?>"], //customize tooltip status message
        stickynotice2: "<?php echo JText::_('TZ_MOD_NEWS_PRESS_OUT_CLICK'); ?>", //customize tooltip status message

        //***** NO NEED TO EDIT BEYOND HERE
        isdocked: false,

        positiontooltip: function ($, $tooltip, e) {
            var x = e.pageX + this.tooltipoffsets[0], y = e.pageY + this.tooltipoffsets[1]
            var tipw = $tooltip.outerWidth(), tiph = $tooltip.outerHeight(),
                x = (x + tipw > $(document).scrollLeft() + $(window).width()) ? x - tipw - (stickytooltip.tooltipoffsets[0] * 2) : x
            y = (y + tiph > $(document).scrollTop() + $(window).height()) ? $(document).scrollTop() + $(window).height() - tiph - 10 : y
            $tooltip.css({left: x, top: y})
        },

        showbox: function ($, $tooltip, e) {
            $tooltip.fadeIn(this.fadeinspeed)
            this.positiontooltip($, $tooltip, e)
        },

        hidebox: function ($, $tooltip) {
            if (!this.isdocked) {
                $tooltip.stop(false, true).hide()
                $tooltip.css({borderColor: '<?php echo $background_s; ?>'}).find('.stickystatus:eq(0)').css({background: this.stickybordercolors[0]}).html(this.stickynotice1)
            }
        },

        docktooltip: function ($, $tooltip, e) {
            this.isdocked = true
            $tooltip.css({borderColor: '<?php echo $background_out; ?>'}).find('.stickystatus:eq(0)').css({background: this.stickybordercolors[1]}).html(this.stickynotice2)
        },

        init: function (targetselector, tipid) {
            jQuery(document).ready(function ($) {
                var $targets = $(targetselector)
                var $tooltip = $('#' + tipid).appendTo(document.body)
                if ($targets.length == 0)
                    return
                var $alltips = $tooltip.find('div.atip')
                if (!stickytooltip.rightclickstick)
                    stickytooltip.stickynotice1[1] = ''
                stickytooltip.stickynotice1 = stickytooltip.stickynotice1.join(' ');
                stickytooltip.hidebox($, $tooltip)
                $targets.bind('mouseenter', function (e) {
                    $alltips.hide().filter('#' + $(this).attr('data-tooltip')).show()
                    stickytooltip.showbox($, $tooltip, e)
                })
                $targets.bind('mouseleave', function (e) {
                    stickytooltip.hidebox($, $tooltip)
                })
                $targets.bind('mousemove', function (e) {
                    if (!stickytooltip.isdocked) {
                        stickytooltip.positiontooltip($, $tooltip, e)
                    }
                })
                $tooltip.bind("mouseenter", function () {
                    stickytooltip.hidebox($, $tooltip)
                })
                $tooltip.bind("click", function (e) {
                    e.stopPropagation()
                })
                $(this).bind("click", function (e) {
                    if (e.button == 0) {
                        stickytooltip.isdocked = false
                        stickytooltip.hidebox($, $tooltip)
                    }
                })
                $(this).bind("contextmenu", function (e) {
                    if (stickytooltip.rightclickstick && $(e.target).parents().andSelf().filter(targetselector).length == 1) { //if oncontextmenu over a target element
                        stickytooltip.docktooltip($, $tooltip, e)
                        return false
                    }
                })
                $(this).bind('keypress', function (e) {
                    var keyunicode = e.charCode || e.keyCode
                    if (keyunicode == 115) { //if "s" key was pressed
                        stickytooltip.docktooltip($, $tooltip, e)
                    }
                })
            }) //end dom ready
        }
    }

    //stickytooltip.init("targetElementSelector", "tooltipcontainer")
    stickytooltip.init("*[data-tooltip]", "mystickytooltip");
</script>

<div id="tz_tooltip">
    <?php if (isset($list) && !empty($list)) :
        foreach ($list as $i => $item) : $media = $item->media; ?>
            <?php if (!$media or ($media != null  AND $media->type != 'quote' AND $media->type != 'link' AND $media->type != 'audio')): ?>
                <h6 class="tz_tooltip_title">
                    <a class="tz_title_like" data-tooltip="sticky<?php echo $i ?>" href="<?php echo $item->link; ?>">
                        <?php echo $item->title; ?>
                    </a>
                </h6>
            <?php endif; ?>
            <?php if ($show_quote == 1 AND $media AND $media->type == 'quote'): ?>
                <h6 class="tz_tooltip_title">
                    <a class="tz_title_like" data-tooltip="sticky<?php echo $i ?>" href="<?php echo $item->link; ?>">
                        <?php echo $item->title; ?>
                    </a>
                </h6>
            <?php endif; ?>
            <?php if ($show_link == 1 AND $media AND $media->type == 'link'): ?>
                <h6 class="tz_tooltip_title">
                    <a class="tz_title_like" data-tooltip="sticky<?php echo $i ?>" href="<?php echo $item->link; ?>">
                        <?php echo $item->title; ?>
                    </a>
                </h6>
            <?php endif; ?>
            <?php if ($show_audio == 1 AND $media AND $media->type == 'audio'): ?>
                <h6 class="tz_tooltip_title">
                    <a class="tz_title_like" data-tooltip="sticky<?php echo $i ?>" href="<?php echo $item->link; ?>">
                        <?php echo $item->title; ?>
                    </a>
                </h6>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!--HTML for the tooltips-->

<div id="mystickytooltip" class="stickytooltip">
    <?php if (isset($list) && !empty($list)) :
        foreach ($list as $i => $item) :$media = $item->media; ?>
            <?php if (!$media or ($media != null  AND $media->type != 'quote' AND $media->type != 'link' AND $media->type != 'audio')): ?>
                <div id="sticky<?php echo $i; ?>" class="atip tz_stichky">
                    <div class="info_slide tz_tooltip_default">
                        <?php if ($title == 1): ?>
                            <h6 class="tz_tooltip_title">
                                <a href="<?php echo $item->link; ?>"
                                   title="<?php echo $item->title; ?>">
                                    <?php echo $item->title; ?>
                                </a>
                            </h6>
                        <?php endif; ?>
                        <?php if ($image == 1 AND $item->image != null) : ?>
                            <span class="tz_tooltip_image">
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
                        </span>
                        <?php endif; ?>

                        <?php if ($des == 1) : ?>
                            <span class="tz_tooltip_description">
                            <?php if ($limittext) {
                                echo substr($item->intro, 3, $limittext);
                            } else {
                                echo $item->intro;
                            }?>
                        </span>
                        <?php endif; ?>

                        <?php if ($date == 1) : ?>
                            <span class="tz_tooltip_date">
                            <?php echo JText::sprintf('MOD_TZ_NEWS_DATE_ALL', JHtml::_('date', $item->created, JText::_('MOD_TZ_NEWS_DATE_FOMAT'))); ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($hits == 1) : ?>
                            <span class="tz_tooltip_hits">
                            <?php echo JText::sprintf('MOD_TZ_NEWS_HIST_LIST', $item->hit) ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($author_new == 1): ?>
                            <span class="tz_tooltip_author">
                            <?php echo JText::sprintf('MOD_TZ_NEWS_AUTHOR', $item->author); ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($cats_new == 1): ?>
                            <span class="tz_tooltip_category">
                            <?php echo JText::sprintf('MOD_TZ_NEWS_CATEGORY', $item->category); ?>
                        </span>
                        <?php endif; ?>

                        <?php if ($readmore == 1) : ?>
                            <span class="tz_tooltip_readmore">
                            <a href="<?php echo $item->link; ?>"><?php echo JText::_('MOD_TZ_NEWS_READ_MORE') ?></a>
                        </span>
                        <?php endif; ?>
                        <div class="clearfix"></div>
                    </div>
                </div>
            <?php endif; ?>
            <!--use tz -portfolio-->
            <?php if ($show_quote == 1 AND $media AND $media->type == 'quote'): ?>
                <?php require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'tooltip') . '_quote'); ?>
            <?php endif; ?>

            <?php if ($show_link == 1 AND $media AND $media->type == 'link'): ?>
                <?php require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'tooltip') . '_link'); ?>
            <?php endif; ?>

            <?php if ($show_audio == 1 AND $media AND $media->type == 'audio'): ?>
                <?php require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'tooltip') . '_audio'); ?>
            <?php endif; ?>

        <?php endforeach; ?>

        <?php if (isset($tooltipStatus) && $tooltipStatus == 1) : ?>
        <div class="stickystatus"></div>
    <?php endif; ?>
    <?php endif; ?>
</div>