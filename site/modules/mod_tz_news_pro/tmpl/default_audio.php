<?php
/**
 * Created by PhpStorm.
 * User: TuanMap
 * Date: 2/27/14
 * Time: 11:21 AM
 */
?>
<li class="tz_item_default">
    <div class="tz_audio">

        <?php if ($title == 1) : ?>
            <h6 class="tz_title">
                <a href="<?php echo $item->link; ?>"
                   title="<?php echo $item->title; ?>">
                    <?php echo $item->title; ?>
                </a>
            </h6>
        <?php endif; ?>
        <?php if ($image == 1 or$des == 1): ?>
            <div class="dv1">
                <?php if ($image == 1) : ?>
                    <div class="tz_image">
                        <a class="title" href="<?php echo $item->link; ?>">
                            <img src="<?php echo $item->image; ?>"
                                 title="<?php echo $media->imagetitle; ?>"
                                 alt="<?php echo $media->imagetitle; ?>"/>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if ($des == 1) : ?>
                    <span class="tz_description">
                        <?php if ($limittext) :
                            echo substr($item->intro, 3, $limittext);
                        else :
                            echo $item->intro;
                        endif;?>
                        <?php if ($readmore == 1) : ?>
                            <span class="tz_readmore">
                                <a href="<?php echo $item->link; ?>"><?php echo JText::_('MOD_TZ_NEWS_READ_MORE') ?></a>
                            </span>
                        <?php endif; ?>`
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