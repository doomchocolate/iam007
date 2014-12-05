<?php
/**
 * Created by PhpStorm.
 * User: TuanMap
 * Date: 2/27/14
 * Time: 6:40 PM
 */ ?>
<div id="sticky<?php echo $i; ?>" class="atip tz_stichky">
    <div class="info_slide tz_tooltip_link">

        <a class="title" href="<?php echo $media->link_url; ?>"
           target="<?php echo $media->link_target; ?>"
           rel="<?php echo $media->link_follow; ?>">
            <?php echo $media->link_title ?>
        </a>

        <?php if ($des == 1) : ?>
            <span class="tz_description">
                <?php if ($limittext) {
                    echo substr($item->intro, 3, $limittext);
                } else {
                    echo $item->intro;
                }?>
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

        <?php if ($date == 1) : ?>
            <span class="tz_date">
                <?php echo JText::sprintf('MOD_TZ_NEWS_DATE_ALL', JHtml::_('date', $item->created, JText::_('MOD_TZ_NEWS_DATE_FOMAT'))); ?>
            </span>
        <?php endif; ?>

        <div class="clearfix"></div>
    </div>
</div>