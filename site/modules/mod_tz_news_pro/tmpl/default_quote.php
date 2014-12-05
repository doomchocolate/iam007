<?php
/**
 * Created by PhpStorm.
 * User: TuanMap
 * Date: 2/27/14
 * Time: 11:20 AM
 */
?>
<li class="tz_item_default">
    <div class="tz_quote">

        <i class="icon-quote"></i>

        <?php echo $media->quote_text ?>
        <div class="dv1">
            <div class="muted author"><?php echo $media->quote_author; ?></div>
        </div>
        <?php if ($date == 1 or $hits == 1 or $author_new == 1 or $cats_new == 1): ?>
            <div class="dv2">
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
            </div>
        <?php endif; ?>
    </div>
    <div class="clearfix"></div>
</li>