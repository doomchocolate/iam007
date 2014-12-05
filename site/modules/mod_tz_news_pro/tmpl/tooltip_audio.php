<?php
/**
 * Created by PhpStorm.
 * User: TuanMap
 * Date: 2/27/14
 * Time: 6:40 PM
 */
?>
<div id="sticky<?php echo $i; ?>" class="atip tz_stichky">
    <div class="info_slide tz_tooltip_audio">

        <?php if ($title == 1): ?>
            <h6 class="tz_tooltip_title">
                <a href="<?php echo $item->link; ?>"
                   title="<?php echo $item->title; ?>">
                    <?php echo $item->title; ?>
                </a>
            </h6>
        <?php endif; ?>

        <?php if ($image == 1) : ?>
            <div class="tz_tooltip_image">
                <a class="title" href="<?php echo $item->link; ?>">
                    <img src="<?php echo $item->image; ?>"
                         title="<?php echo $media->imagetitle; ?>"
                         alt="<?php echo $media->imagetitle; ?>"/>
                </a>
            </div>
        <?php endif; ?>

        <?php if ($des == 1) : ?>
            <span class="tz_tooltip_description">
                <?php if ($limittext) {
                    echo substr($item->intro, 3, $limittext);
                } else {
                    echo $item->intro;
                }?>
                <?php if ($readmore == 1) : ?>
                    <span class="tz_tooltip_readmore">
                <a href="<?php echo $item->link; ?>"><?php echo JText::_('MOD_TZ_NEWS_READ_MORE') ?></a>
            </span>
                <?php endif; ?>
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

        <?php if ($date == 1) : ?>
            <span class="tz_tooltip_date">
                <?php echo JText::sprintf('MOD_TZ_NEWS_DATE_ALL', JHtml::_('date', $item->created, JText::_('MOD_TZ_NEWS_DATE_FOMAT'))); ?>
            </span>
        <?php endif; ?>
        <div class="clearfix"></div>
    </div>
</div>