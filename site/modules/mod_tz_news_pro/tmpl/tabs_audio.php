<?php
/**
 * Created by PhpStorm.
 * User: TuanMap
 * Date: 2/26/14
 * Time: 7:04 PM
 */
?>
<div class="tz_tabs_introtext" id="tab<?php echo $i; ?>">
    <div class="tz_tab_audio">

        <?php if ($title == 1) : ?>
            <h3 class="tz_title">
                <a href="<?php echo $list[$i]->link; ?>"
                   title="<?php echo $list[$i]->title; ?>">
                    <?php echo $list[$i]->title; ?>
                </a>
            </h3>
        <?php endif; ?>
        <?php if ($image == 1 || $des == 1): ?>
            <div class="dv1">
                <?php if ($image == 1) : ?>
                    <div class="tz_tab_image">
                        <a class="title" href="<?php echo $list[$i]->link; ?>">
                            <img src="<?php echo $list[$i]->image; ?>"
                                 title="<?php echo $media->imagetitle; ?>"
                                 alt="<?php echo $media->imagetitle; ?>"/>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($des == 1) : ?>
                    <span class="tz_description">
            <?php if ($limittext) :
                echo substr($list[$i]->intro, 3, $limittext);
            else :
                echo $list[$i]->intro;
            endif;?>
                        <?php if ($readmore == 1) : ?>
                            <span class="tz_readmore">
                                    <a href="<?php echo $list[$i]->link; ?>"><?php echo JText::_('MOD_TZ_NEWS_READ_MORE') ?></a>
                                </span>
                        <?php endif; ?>
                </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($hits == 1 || $author_new == 1 || $cats_new == 1 || $date == 1): ?>
            <div class="dv2">
                <?php if ($hits == 1) : ?>
                    <span class="tz_tab_hits">
                                    <?php echo JText::sprintf('MOD_TZ_NEWS_HIST_LIST', $list[$i]->hit) ?>
                                </span>
                <?php endif; ?>

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