<?php
/**
 * Created by PhpStorm.
 * User: TuanMap
 * Date: 2/26/14
 * Time: 7:01 PM
 */
?>
<div class="tz_tabs_introtext" id="tab<?php echo $i; ?>">
    <div class="tz_tab_quote">

        <i class="icon-quote"></i>
        <?php echo $media->quote_text; ?>

        <div class="dv1">
            <div class="muted author"><?php echo $media->quote_author; ?></div>
        </div>
        <?php if ($hits == 1 || $author_new == 1 || $cats_new == 1 || $date == 1): ?>
            <div class="dv2">
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

                <?php if ($hits == 1) : ?>
                    <span class="tz_tab_hits">
                                    <?php echo JText::sprintf('MOD_TZ_NEWS_HIST_LIST', $list[$i]->hit) ?>
                                </span>
                <?php endif; ?>

                <?php if ($date == 1) : ?>
                    <span class="tz_tab_date">
                                    <?php echo JText::sprintf('MOD_TZ_NEWS_DATE_ALL', JHtml::_('date', $list[$i]->created, JText::_('MOD_TZ_NEWS_DATE_FOMAT'))); ?>
                                </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="clearfix"></div>
</div>