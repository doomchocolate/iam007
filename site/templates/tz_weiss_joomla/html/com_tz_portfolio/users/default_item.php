<?php
/*------------------------------------------------------------------------

# TZ Portfolio Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2012 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

//no direct access
defined('_JEXEC') or die('Restricted access');

$row            = $this -> item;
$params         = $row -> params;
$media          = $this -> media;
$extraFields    = $this -> extraFields;
$canEdit        = $params -> get('access-edit');

$listMedia      = $media -> getMedia($row -> id);

$this -> assign('listMedia',$listMedia);
?>
<?php if( (isset($listMedia[0] -> type) AND $listMedia[0] -> type != 'quote'
        AND $listMedia[0] -> type != 'link') OR !isset($listMedia[0] -> type)):?>

    <?php if ($canEdit ||  $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
        <div class="TzIcon">
            <div class="btn-group pull-right">
                <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:">
                    <i class="fa fa-cog"></i><i class="fa fa-caret-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <?php if ($params->get('show_print_icon')) : ?>
                        <li class="print-icon"> <?php echo JHtml::_('icon.print_popup', $row, $params); ?> </li>
                    <?php endif; ?>
                    <?php if ($params->get('show_email_icon')) : ?>
                        <li class="email-icon"> <?php echo JHtml::_('icon.email', $row, $params); ?> </li>
                    <?php endif; ?>
                    <?php if ($canEdit) : ?>
                        <li class="edit-icon"> <?php echo JHtml::_('icon.edit', $row, $params); ?> </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <?php if (($params->get('show_author')) or ($params->get('show_category'))
        or ($params->get('show_create_date')) or ($params->get('show_modify_date'))
        or ($params->get('show_publish_date')) or ($params->get('show_parent_category'))
        or ($params->get('show_hits')) or ($params -> get('show_vote'))) : ?>
        <div class="muted TzArticleBlogInfo TzTagArticleInfo">

            <?php if ($params->get('show_author') && !empty($row->author )) : ?>
                <span class="TzTagCreatedby hasTooltip" title="<?php echo JText::_('TPL_TZ_WEISS_WRITTEN_BY');?>">
            <i class="fa fa-user"></i>
                    <?php $author =  $row->author; ?>
                    <?php $author = ($row->created_by_alias ? $row->created_by_alias : $author);?>
                    <?php
                    if(!$userItemid   = '&Itemid='.$this -> FindUserItemId($row->created_by)){
                        $userItemid = null;
                    }
                    ?>

                    <?php if ($params->get('link_author') == true):?>
                        <?php 	echo
                        JHtml::_('link', JRoute::_('index.php?option=com_tz_portfolio&view=users&created_by='.$row -> created_by.$userItemid), $author); ?>

                    <?php else :?>
                        <?php echo $author; ?>
                    <?php endif; ?>
        </span>
            <?php endif; ?>

            <?php if ($params->get('show_category')) : ?>
                <span class="TzTagCategoryName hasTooltip" title="<?php echo JText::_('JCATEGORY')?>">
            <i class="fa fa-folder-o"></i>
                    <?php $title = $this->escape($row->category_title);
                    $url = '<a href="' . JRoute::_(TZ_PortfolioHelperRoute::getCategoryRoute($row->catid)) . '">' . $title . '</a>'; ?>
                    <?php if ($params->get('link_category')) : ?>
                        <?php echo $url; ?>
                    <?php else : ?>
                        <?php echo $title; ?>
                    <?php endif; ?>
        </span>
            <?php endif; ?>

            <?php if($params -> get('tz_show_count_comment',1) == 1):?>
                <span class="TzPortfolioCommentCount hasTooltip" title="<?php echo JText::_('TPL_TZ_WEISS_COMMENT_COUNT');?>">
            <i class="fa fa-comment-o"></i>
                    <?php if($params -> get('comment_function_type','js') == 'js'):?>
                        <?php if($params -> get('tz_comment_type') == 'disqus'): ?>
                            <a href="<?php echo $row -> fullLink;?>#disqus_thread"><?php echo $row -> commentCount;?></a>
                        <?php elseif($params -> get('tz_comment_type') == 'facebook'):?>
                            <span class="fb-comments-count" data-href="<?php echo $row -> fullLink;?>"></span>
                        <?php endif;?>
                    <?php else:?>
                        <?php if($params -> get('tz_comment_type') == 'facebook'): ?>
                            <?php if(isset($row -> commentCount)):?>
                                <span><?php echo $row -> commentCount;?></span>
                            <?php endif;?>
                        <?php endif;?>
                <?php if($params -> get('tz_comment_type') == 'disqus'):?>
                            <?php if(isset($row -> commentCount)):?>
                                <span><?php echo $row -> commentCount;?></span>
                            <?php endif;?>
                        <?php endif;?>
                    <?php endif;?>

                    <?php if($params -> get('tz_comment_type') == 'jcomment'): ?>
                        <?php
                        $comments = JPATH_SITE.'/components/com_jcomments/jcomments.php';
                        if (file_exists($comments)){
                            require_once($comments);
                            if(class_exists('JComments')){?>
                                <span><?php echo JComments::getCommentsCount((int) $row -> id,'com_tz_portfolio');?></span>
                            <?php
                            }
                        }
                        ?>
                    <?php endif;?>
        </span>
            <?php endif;?>

            <?php if ($params->get('show_hits')) : ?>
                <span class="TzTagHits hasTooltip" title="<?php echo JText::_('ARTICLE_HITS')?>">
                <i class="fa fa-eye"></i>
              <span class="numbers"><?php echo  $row->hits; ?></span>
              <span class="hits"><?php echo JText::_('ARTICLE_HITS'); ?></span>
          </span>
            <?php endif; ?>

            <?php if ($params->get('show_create_date')) : ?>
                <span class="TzTagDate hasTooltip" title="<?php echo JText::_('TPL_TZ_WEISS_CREATED_DATE')?>">
          <span class="date"><i class="fa fa-clock-o"></i>
              <?php echo JHtml::_('date', $row->created, JText::_('TPL_TZ_WEISS_DATE_FORMAT_LC3')); ?>
            </span>
        </span>
            <?php endif; ?>

            <?php if ($params->get('show_modify_date')) : ?>
                <span class="TzTagModified hasTooltip" title="<?php echo JText::_('TPL_TZ_WEISS_LAST_UPDATED');?>">
            <i class="fa fa-clock-o"></i>
                    <?php echo JHtml::_('date', $row->modified, JText::_('DATE_FORMAT_LC2')); ?>
        </span>
            <?php endif; ?>

            <?php if ($params->get('show_publish_date')) : ?>
                <span class="TzTagPublished hasTooltip" title="<?php echo JText::_('TPL_TZ_WEISS_PUBLISHED_DATE');?>">
            <i class="fa fa-clock-o"></i>
                    <?php echo JHtml::_('date', $row->publish_up, JText::_('DATE_FORMAT_LC2')); ?>
        </span>
            <?php endif; ?>

            <?php if($params -> get('show_vote',1) AND $row -> event -> TZPortfolioVote):?>
                <span class="TzVote">
            <?php echo $row -> event -> TZPortfolioVote; ?>
        </span>
            <?php endif;?>

        </div>
    <?php endif; ?>

    <?php if($params -> get('show_title')): ?>
        <h3 class="TzBlogTitle TzTagTitle">
            <?php if($params->get('link_titles')) : ?>
                <a href="<?php echo $row ->link; ?>"
                    <?php if($params -> get('tz_use_lightbox') == 1){echo ' class="fancybox fancybox.iframe"';}?>>
                    <?php echo $this->escape($row -> title); ?>
                </a>
            <?php else : ?>
                <?php echo $this->escape($row -> title); ?>
            <?php endif; ?>
            <?php if($row -> featured == 1):?>
                <span class="label label-important TzFeature"><?php echo JText::_('COM_TZ_PORTFOLIO_FEATURE');?></span>
            <?php endif;?>
        </h3>
    <?php endif;?>

    <?php
    if($params -> get('show_image',1) OR $params -> get('show_image_gallery',1)
        OR $params -> get('show_video',1) OR $params -> get('show_audio',1)):
        ?>

        <?php
        echo $this -> loadTemplate('media');
        ?>
    <?php endif;?>

    <?php if (!$params->get('show_intro',1)) : ?>
        <?php //Call event onContentAfterTitle and TZPluginDisplayTitle on plugin?>
        <?php echo $row -> event -> afterDisplayTitle; ?>
        <?php echo $row -> event -> TZafterDisplayTitle; ?>
    <?php endif; ?>

    <?php

    $extraFields -> setState('article.id',$row -> id);

    $extraFields -> setState('params',$row -> params);

    $this -> assign('tagFields',$extraFields -> getExtraFields());
    ?>
    <?php echo $this -> loadTemplate('extrafields');?>

    <?php //Call event onContentBeforeDisplay and onTZPluginBeforeDisplay on plugin?>
    <?php echo $row -> event -> beforeDisplayContent; ?>
    <?php echo $row -> event -> TZbeforeDisplayContent; ?>


    <?php if($params -> get('show_intro',1) AND $this -> item -> introtext):?>
        <div class="TzDescription">
    <?php endif;?>

    <?php $titleReadmore    = null;?>

    <?php if ($params->get('show_readmore') && $row->readmore) :
        if ($params->get('access-view')) :
            $link   = $row ->link;
        else :
            $menu = JFactory::getApplication()->getMenu();
            $active = $menu->getActive();
            $itemId = $active->id;
            $link1 = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId);
            $link = new JURI($link1);
            $link->setVar('return', base64_encode($row ->link));
        endif;
        ?>
        <?php if($params -> get('show_readmore',1) == 1):
        ob_start();
        ?>
        <a class="TzReadmore<?php if($params -> get('tz_use_lightbox') == 1){echo ' fancybox fancybox.iframe';}?> hasTooltip"
           title="<?php echo JText::_('COM_TZ_READ_MORE');?>" href="<?php echo $link; ?>">
            <?php if (!$params->get('access-view')) :
                echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
            elseif ($readmore = $params -> get('alternative_readmore')) :
                echo $readmore;
                if ($params->get('show_readmore_title', 0) != 0) :
                    echo JHtml::_('string.truncate', ($row->title), $params->get('readmore_limit'));
                endif;
            elseif ($params->get('show_readmore_title', 0) == 0) :
                echo JText::_('TPL_TZ_WEISS_READ_MORE');
            else :
                echo JText::_('TPL_TZ_WEISS_READ_MORE');
                echo JHtml::_('string.truncate', ($row->title), $params->get('readmore_limit'));
            endif; ?></a>
        <?php
        $titleReadmore  = ob_get_contents();
        ob_end_clean();
        ?>
    <?php endif;?>
    <?php endif; ?>

    <?php
    if($params -> get('show_intro',1)){
        if($row -> introtext){
            if(preg_match('/(.*?)(<\/([a-z]+|h[1-6])>)$/msi',trim($row -> introtext),$match)){
                echo preg_replace('/(.*?)(<\/([a-z]+|h[1-6])>)$/msi','$1'.$titleReadmore.'$2',trim($row -> introtext));
            }else{
                echo $row->introtext.$titleReadmore;
            }
        }
    }else{
        echo $titleReadmore;
    }
    ?>

    <?php if($params -> get('show_intro',1) AND $this -> item -> introtext):?>
        </div>
    <?php endif;?>

    <?php echo $this -> loadTemplate('tags');?>

    <?php //Call event onContentAfterDisplay and onTZPluginAfterDisplay on plugin?>
    <?php echo $row -> event -> afterDisplayContent; ?>
    <?php echo $row -> event -> TZafterDisplayContent; ?>
<?php else: // Begin quote or link?>
    <?php if ($canEdit) : ?>
        <div class="TzIcon">
            <div class="btn-group pull-right"> <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:">
                    <i class="fa fa-cog"></i><span class="fa fa-caret-down"></span>
                </a>
                <ul class="dropdown-menu">
                    <?php if ($canEdit) : ?>
                        <li class="edit-icon"> <?php echo JHtml::_('icon.edit', $row, $params); ?> </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <?php echo $this -> loadTemplate('link');?>

    <?php echo $this -> loadTemplate('quote');?>
<?php endif;?>