<?php
/**
 * Created by PhpStorm.
 * User: TuanMap
 * Date: 2/27/14
 * Time: 3:10 PM
 */
?>
<li class="<?php if (isset($orientation) && $orientation == 'horizontal'):echo "tz-scroll-list";endif; ?> tz_item_default ">
    <div class="tz_marquee_quote">
        <div class="quote_text">
            <?php echo $media->quote_text ?>
        </div>
        <div class="muted_author">
            <?php echo $media->quote_author; ?>
        </div>
        <?php if ($date == 1 or $hits == 1 or $author_new == 1 or $cats_new == 1): ?>
            <div class="dv1">
                <?php if ($hits == 1) : ?>
                    <span class="tz_marquee_hits">
                <?php echo JText::sprintf('MOD_TZ_NEWS_HIST_LIST', $item->hit) ?>
            </span>
                <?php endif; ?>

                <?php if ($author_new == 1): ?>
                    <span class="tz_marquee_author">
                <?php echo JText::sprintf('MOD_TZ_NEWS_AUTHOR', $item->author); ?>
            </span>
                <?php endif; ?>

                <?php if ($cats_new == 1): ?>
                    <span class="tz_marquee_category">
                <?php echo JText::sprintf('MOD_TZ_NEWS_CATEGORY', $item->category); ?>
            </span>
                <?php endif; ?>

                <?php if ($date == 1) : ?>
                    <span class="tz_marquee_date">
                <?php echo JText::sprintf('MOD_TZ_NEWS_DATE_ALL', JHtml::_('date', $item->created, JText::_('MOD_TZ_NEWS_DATE_FOMAT'))); ?>
            </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="clearfix"></div>
</li>