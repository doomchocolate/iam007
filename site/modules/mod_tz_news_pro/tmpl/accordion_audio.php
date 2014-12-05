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
    <span class=""></span>
</div>
<div class="tz_accordion_audio info_accordion">
    <?php if ($image == 1 or$des == 1): ?>
        <div class="dv1">
            <?php if ($image == 1 AND $item->image != null) : ?>
                <div class="tz_accordion_image">
                    <a class="title" href="<?php echo $item->link; ?>">
                        <img src="<?php echo $item->image; ?>"
                             title="<?php echo $media->imagetitle; ?>"
                             alt="<?php echo $media->imagetitle; ?>"/>
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
                        <div class="tz_accordion_readmore">
                            <a href="<?php echo $item->link; ?>"><?php echo JText::_('MOD_TZ_NEWS_READ_MORE') ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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