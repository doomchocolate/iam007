<?php
/**
 * Created by PhpStorm.
 * User: TuanMap
 * Date: 2/27/14
 * Time: 11:21 AM
 */
?>
<li class="tz_item_default">
    <div class="tz_link">

        <span class="icon-link"></span>

        <a class="title" href="<?php echo $media->link_url; ?>"
           target="<?php echo $media->link_target; ?>"
           rel="<?php echo $media->link_follow; ?>">
            <?php echo $media->link_title ?>
        </a>
        <?php if ($image == 1 or$des == 1): ?>
            <div class="dv1">
                <?php if ($des == 1) : ?>
                    <span class="tz_description">
                        <?php if ($limittext) :
                            echo substr($item->intro, 3, $limittext);
                        else :
                            echo $item->intro;
                        endif;?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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