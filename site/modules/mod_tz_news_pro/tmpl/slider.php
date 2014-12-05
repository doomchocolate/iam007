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


//var_dump($auto);die;
// no direct access
defined('_JEXEC') or die;
$document = JFactory::getDocument();
$document->addScript(JUri::base() . 'modules/mod_tz_news_pro/js/jquery.flexslider.js');
$document->addStyleSheet(JUri::base() . 'modules/mod_tz_news_pro/css/flexslider.css');
if ($list):?>
    <div id="slides" class="flexslider">
        <ul class="slides">
            <?php foreach ($list as $item): $media = $item->media; ?>
                <?php if (!$media or ($media != null  AND $media->type != 'quote' AND $media->type != 'link' AND $media->type != 'audio')): ?>
                    <li>
                        <div class="tz_slider_default">
                            <?php if ($title == 1) : ?>
                                <h3 class="tz_slider_title">
                                    <a href="<?php echo $item->link; ?>"
                                       title="<?php echo $item->title; ?>">
                                        <?php echo $item->title; ?>
                                    </a>
                                </h3>
                            <?php endif; ?>
                            <hr class="gach"/>
                            <?php if ($image == 1 or$des == 1): ?>
                                <div class="dv1">
                                    <?php if ($image == 1 AND $item->image != null) : ?>
                                        <div class="tz_slider_image">
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
                                        <span class="tz_slider_description">
                                <?php if ($limittext) {
                                    echo substr($item->intro, 3, $limittext);
                                } else {
                                    echo $item->intro;
                                }?>
                                            <?php if ($readmore == 1) : ?>
                                                <span class="tz_slider_readmore">
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
                                        <span class="tz_slider_date">
                                            <?php echo JText::sprintf('MOD_TZ_NEWS_DATE_ALL', JHtml::_('date', $item->created, JText::_('MOD_TZ_NEWS_DATE_FOMAT'))); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($hits == 1) : ?>
                                        <span class="tz_slider_hits">
                                            <?php echo JText::sprintf('MOD_TZ_NEWS_HIST_LIST', $item->hit) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($author_new == 1): ?>
                                        <span class="tz_slider_author">
                                <?php echo JText::sprintf('MOD_TZ_NEWS_AUTHOR', $item->author); ?>
                            </span>
                                    <?php endif; ?>
                                    <?php if ($cats_new == 1): ?>
                                        <span class="tz_slider_category">
                                <?php echo JText::sprintf('MOD_TZ_NEWS_CATEGORY', $item->category); ?>
                            </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="clearfix"></div>
                        </div>
                    </li>
                <?php endif; ?>
                <!--use  for tz -portfolio-->
                <?php if ($show_quote == 1 AND $media AND $media->type == 'quote'): ?>
                    <?php require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'slider') . '_quote'); ?>
                <?php endif; ?>
                <?php if ($show_link == 1 AND $media AND $media->type == 'link'): ?>
                    <?php require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'slider') . '_link'); ?>
                <?php endif; ?>
                <?php if ($show_audio == 1 AND $media AND $media->type == 'audio'): ?>
                    <?php require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'slider') . '_audio'); ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<script type="text/javascript">
    jQuery(window).load(function () {
        jQuery('.flexslider').flexslider({
            animation: "<?php echo $effect?>",
            animationLoop: <?php echo $animationLoop;?>,
            directionNav: <?php echo $directionNav;?>,
            pausePlay: <?php echo $pausePlay;?>,
            slideshowSpeed: <?php echo $slideSpeed; ?>,
            animationSpeed: <?php echo $animationSpeed?>,
            itemWidth: <?php echo $itemWidth?>,
            minItems: <?php echo $minItems?>,
            maxItems: <?php echo $maxItems?>,
            move: <?php echo $move?>,
            smoothHeight: true
        });
    });
</script>


