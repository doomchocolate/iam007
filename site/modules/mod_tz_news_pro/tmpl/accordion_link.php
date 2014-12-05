<?php
/**
 * Created by PhpStorm.
 * User: TuanMap
 * Date: 2/27/14
 * Time: 10:43 AM
 */
?>
<div class="tz_accordion" id="section<?php echo $i; ?>">
    <h3 class="tz_title">
        <?php echo $item->title; ?>
    </h3>
    <span></span>
</div>
<div class="tz_accordion_link info_accordion">
    <div class="icon-link"></div>
    <a class="title" href="<?php echo $media->link_url; ?>"
       target="<?php echo $media->link_target; ?>"
       rel="<?php echo $media->link_follow; ?>">
        <?php echo $media->link_title ?>
    </a>
    <?php if ($des == 1) : ?>
        <div class="dv1">
            <div class="tz_accordion_description">
                <?php if ($limittext) :
                    echo substr($item->intro, 3, $limittext);
                else :
                    echo $item->intro;
                endif;?>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($date == 1 or $hits == 1 or $author_new == 1 or $cats_new == 1): ?>
        <div class="dv2">
            <?php if ($hits == 1) : ?>
                <div class="tz_accordion_hits">
                    <?php echo JText::sprintf('MOD_TZ_NEWS_HIST_LIST', $list[$i]->hit) ?>
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
            <?php if ($date == 1) : ?>
                <div class="tz_accordion_date">
                    <?php echo JText::sprintf('MOD_TZ_NEWS_DATE_ALL', JHtml::_('date', $item->created, JText::_('MOD_TZ_NEWS_DATE_FOMAT'))); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<div class="clearfix"></div>