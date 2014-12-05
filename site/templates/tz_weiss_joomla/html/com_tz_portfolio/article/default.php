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

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
JHtml::addIncludePath(PLAZART_TEMPLATE_REL.'/libraries/helpers');

$app    = JFactory::getApplication();

// Create shortcuts to some parameters.
$params		= $this->item->params;
$images     = json_decode($this->item->images);
$urls       = json_decode($this->item->urls);
$canEdit	= $this->item->params->get('access-edit');
JHtml::_('behavior.caption');
$user		= JFactory::getUser();
$doc        = JFactory::getDocument();

$tmpl    = JRequest::getString('tmpl',null);
if($tmpl){
    $tmpl   = '&tmpl=component';
}
?>

<div class="TzItemPage item-page<?php echo $this->pageclass_sfx?>" >
    <div class="TzItemPageInner">
        <?php if ($this->params->get('show_page_heading', 1)) : ?>
            <h1 class="page-heading">
            <?php echo $this->escape($this->params->get('page_heading')); ?>
            </h1>
        <?php endif; ?>
        <?php
        if (!empty($this->item->pagination) AND $this->item->pagination && !$this->item->paginationposition && $this->item->paginationrelative)
        {
         echo $this->item->pagination;
        }
         ?>

        <?php $useDefList = (($params->get('show_author',1)) or ($params->get('show_category',1)) or ($params->get('show_parent_category',1))
        or ($params->get('show_create_date',1)) or ($params->get('show_modify_date',1)) or ($params->get('show_publish_date',1))
        or ($params->get('show_hits',1))); ?>

        <?php if ($useDefList) : ?>
        <div class="muted TzArticleInfo">
        <?php endif; ?>

            <?php if ($params->get('show_author',1) && !empty($this->item->author )) : ?>
                <span class="TzCreatedby hasTooltip" title="<?php echo JText::_('TPL_TZ_WEISS_WRITTEN_BY');?>">
                <i class="fa fa-user"></i>
                    <?php $author = $this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author; ?>
                    <?php if ($params->get('link_author') == true): ?>
                        <?php
                        $target = '';
                        if(isset($tmpl) AND !empty($tmpl)):
                            $target = ' target="_blank"';
                        endif;
                        $needle = 'index.php?option=com_tz_portfolio&view=users&created_by=' . $this->item->created_by;
                        $item = JMenu::getInstance('site')->getItems('link', $needle, true);
                        if(!$userItemid = '&Itemid='.$this -> FindUserItemId($this->item->created_by)){
                            $userItemid = null;
                        }
                        $cntlink = $needle.$userItemid;
                        ?>
                        <?php echo JHtml::_('link', JRoute::_($cntlink), $author,$target); ?>
                    <?php else: ?>
                        <?php echo $author; ?>
                    <?php endif; ?>
                </span>
            <?php endif; ?>

            <?php if ($params->get('show_category',1)) : ?>
                <span class="TzArticleCategory hasTooltip" title="<?php echo JText::_('JCATEGORY')?>">
                <i class="fa fa-folder-o"></i>
                    <?php
                    $title = $this->escape($this->item->category_title);
                    $url    = $title;
                    $target = '';
                    if(isset($tmpl) AND !empty($tmpl)):
                        $target = ' target="_blank"';
                    endif;
                    $url = '<a href="'.JRoute::_(TZ_PortfolioHelperRoute::getCategoryRoute($this->item->catslug)).'"'.$target.'>'.$title.'</a>';

                    ?>
                    <?php if ($params->get('link_category',1) and $this->item->catslug) : ?>
                        <?php echo $url; ?>
                    <?php else : ?>
                        <?php echo $title; ?>
                    <?php endif; ?>
            </span>
            <?php endif; ?>

            <?php if ($params->get('show_parent_category') && $this->item->parent_slug != '1:root') : ?>
                <span class="TzArticleParentCategory hasTooltip" title="<?php echo JText::_('TPL_TZ_PARENT_CATEGORY')?>">
                <i class="fa fa-folder-open-o"></i>
                    <?php
                    $title = $this->escape($this->item->parent_title);
                    $url    = $title;
                    $target = '';
                    if(isset($tmpl) AND !empty($tmpl)):
                        $target = ' target="_blank"';
                    endif;
                    $url = '<a href="'.JRoute::_(TZ_PortfolioHelperRoute::getCategoryRoute($this->item->parent_slug)).'"'.$target.'>'.$title.'</a>';
                    ?>
                    <?php if ($params->get('link_parent_category') and $this->item->parent_slug) : ?>
                        <?php echo $url; ?>
                    <?php else : ?>
                        <?php echo $title; ?>
                    <?php endif; ?>
            </span>
            <?php endif; ?>

            <?php if($params -> get('tz_show_count_comment',1) == 1):?>
                <span class="TZCommentCount hasTooltip" title="<?php echo JText::_('TPL_TZ_WEISS_COMMENT_COUNT');?>">
                <i class="fa fa-comment-o"></i>
                    <?php if($params -> get('comment_function_type','js') == 'js'):?>
                        <?php if($params -> get('tz_comment_type') == 'disqus'): ?>
                            <a href="<?php echo $this -> item ->link;?>#disqus_thread"><?php echo $this -> item -> commentCount;?></a>
                        <?php elseif($params -> get('tz_comment_type') == 'facebook'):?>
                            <span class="fb-comments-count" data-href="<?php echo $this -> item ->link;?>"></span>
                        <?php endif;?>
                    <?php else:?>
                        <?php if($params -> get('tz_comment_type') == 'facebook'):?>
                           <?php if(isset($this -> item -> commentCount)):?>
                               <span><?php echo $this -> item -> commentCount;?></span>
                           <?php endif;?>
                        <?php endif;?>
                        <?php if($params -> get('tz_comment_type') == 'disqus'):?>
                           <?php if(isset($this -> item -> commentCount)):?>
                               <span><?php echo $this -> item -> commentCount;?></span>
                           <?php endif;?>
                        <?php endif;?>
                    <?php endif;?>


                    <?php if($params -> get('tz_comment_type') == 'jcomment'): ?>
                        <?php
                            $comments = JPATH_SITE.'/components/com_jcomments/jcomments.php';
                            if (file_exists($comments)){
                                require_once($comments);
                                if(class_exists('JComments')){
                        ?>
                            <span><?php echo JComments::getCommentsCount((int) $this -> item -> id,'com_tz_portfolio');?></span>
                        <?php
                                }
                            }
                        ?>
                    <?php endif;?>
                    <?php echo JText::_('TPL_TZ_WEISS_COMMENTS');?>

                </span>
            <?php endif;?>

            <?php if ($params->get('show_hits',1)) : ?>
                <span class="TzHits hasTooltip" title="<?php echo JText::_('ARTICLE_HITS')?>">
                <i class="fa fa-eye"></i>
                    <?php echo JText::sprintf('COM_CONTENT_ARTICLE_HITS',$this->item->hits); ?>
                </span>
            <?php endif; ?>

            <?php if ($params->get('show_create_date',1)) : ?>
                <span class="TzCreate hasTooltip" title="<?php echo JText::_('TPL_TZ_WEISS_CREATED_DATE')?>">
                    <i class="fa fa-clock-o"></i>
                    <?php echo JHtml::_('date', $this->item->created, JText::_('TPL_TZ_WEISS_DATE_FORMAT_LC3')); ?>
                </span>
            <?php endif; ?>

            <?php if ($params->get('show_publish_date')) : ?>
            <span class="TZPublished">
                <i class="fa fa-clock-o"></i>
                <?php echo JText::sprintf( JHtml::_('date', $this->item->publish_up, JText::_('TPL_TZ_WEISS_DATE_FORMAT_LC3'))); ?>
            </span>
            <?php endif; ?>

            <?php if ($params->get('show_modify_date',1)) : ?>
            <span class="TzModified hasTooltip" title="<?php echo JText::_('TPL_TZ_WEISS_LAST_UPDATED');?>">
                <?php echo JHtml::_('date', $this->item->modified, JText::_('DATE_FORMAT_LC2')); ?>
            </span>
            <?php endif; ?>

            <?php if($params -> get('show_vote',1)):?>
                <span class="TzVote">
                    <span><?php echo JText::_('COM_TZ_PORTFOLIO_RATING');?></span>
                    <?php echo $this->item->event->TZPortfolioVote; ?>
                </span>
            <?php endif;?>

        <?php if ($useDefList) : ?>
          <div class="clearfix"></div>
            </div>
        <?php endif; ?>

        <?php if (!$this->print) : ?>
            <?php if ($canEdit ||  $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
                <div class="TzIcon">
                    <div class="btn-group pull-right">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:">
                            <i class="fa fa-cog"></i>
                            <span class="fa fa-caret-down"></span>
                        </a>
                        <?php // Note the actions class is deprecated. Use dropdown-menu instead. ?>
                        <ul class="dropdown-menu actions">
                            <?php if ($params->get('show_print_icon')) : ?>
                                <li class="print-icon"> <?php echo JHtml::_('icon.print_popup',  $this->item, $params); ?> </li>
                            <?php endif; ?>
                            <?php if ($params->get('show_email_icon')) : ?>
                                <li class="email-icon"> <?php echo JHtml::_('icon.email',  $this->item, $params); ?> </li>
                            <?php endif; ?>
                            <?php if ($canEdit) : ?>
                                <li class="edit-icon"> <?php echo JHtml::_('icon.edit', $this->item, $params); ?> </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="pull-right">
                <?php echo JHtml::_('icon.print_screen',  $this->item, $params); ?>
            </div>
        <?php endif; ?>

        <?php if ($params->get('show_title',1)) : ?>
            <h2 class="TzArticleTitle">
            <?php if ($params->get('link_titles',1) AND !empty($this->item->readmore_link)) : ?>
                <?php
                    if($params -> get('tz_use_lightbox') == 1):
                        $titleLink = JRoute::_(TZ_PortfolioHelperRoute::getArticleRoute($this -> item -> slug, $this -> item -> catid).$tmpl);
                    else:
                        $titleLink  = $this->item->readmore_link;
                    endif;
                ?>
                <a href="<?php echo $titleLink; ?>">
                    <?php echo $this->escape($this->item->title); ?>
                </a>
            <?php else : ?>
                <?php echo $this->escape($this->item->title); ?>
            <?php endif; ?>
            </h2>
        <?php endif; ?>

        <?php
        if($params -> get('show_image',1) == 1 OR $params -> get('show_image_gallery',1) == 1
            OR $params -> get('show_video',1) == 1 OR $params -> get('show_audio',1)):
            ?>
            <?php echo $this -> loadTemplate('media');?>
        <?php endif;?>

        <?php if (!$params->get('show_intro',1)) : ?>
            <?php
                //Call event onContentAfterTitle and TZPluginDisplayTitle on plugin
                echo $this->item->event->afterDisplayTitle;
                echo $this->item->event->TZafterDisplayTitle;
            ?>
        <?php endif; ?>

        <?php if (isset ($this->item->toc)) : ?>
            <?php echo $this->item->toc; ?>
        <?php endif; ?>

        <?php if (isset($urls) AND ((!empty($urls->urls_position) AND ($urls->urls_position=='0')) OR  ($params->get('urls_position')=='0' AND empty($urls->urls_position) ))
                OR (empty($urls->urls_position) AND (!$params->get('urls_position')))): ?>
            <?php echo $this->loadTemplate('links'); ?>
        <?php endif; ?>

    <?php if ($params->get('access-view')):?>

        <?php
        if (!empty($this->item->pagination) AND $this->item->pagination AND !$this->item->paginationposition AND !$this->item->paginationrelative):
            echo $this->item->pagination;
         endif;
        ?>

        <?php
            //Call event onContentBeforeDisplay and onTZPluginBeforeDisplay on plugin
            echo $this->item->event->beforeDisplayContent;
            echo $this->item->event->TZbeforeDisplayContent;
        ?>

        <?php $this -> item -> text = trim($this -> item -> text);
            $this -> item -> fulltext = trim($this -> item -> fulltext);?>
        <?php if(!empty($this -> item -> text)):?>
        <div class="TzArticleDescription">
              <?php echo $this -> item -> text;?>
        </div>
        <?php endif;?>

        <?php echo $this -> loadTemplate('extra_fields');?>

        <?php echo $this -> loadTemplate('attachments');?>

        <?php echo $this -> loadTemplate('tag');?>

        <?php if (!$this->print) : ?>
            <?php echo $this -> loadTemplate('social_network');?>
        <?php endif;?>

        <?php echo $this -> loadTemplate('gmap');?>

        <?php
            echo $this -> loadTemplate('author');
        ?>

        <?php if (!$this->print) : ?>

            <?php echo $this -> loadTemplate('related');?>

            <?php echo $this -> item -> event -> onTZPortfolioCommentDisplay;?>
        <?php endif;?>
                
        <?php
        if (!empty($this->item->pagination) AND $this->item->pagination AND $this->item->paginationposition AND!$this->item->paginationrelative):
             echo $this->item->pagination;?>
        <?php endif; ?>

        <?php if (isset($urls) AND ((!empty($urls->urls_position)  AND ($urls->urls_position=='1')) OR ( $params->get('urls_position')=='1') )): ?>
            <?php echo $this->loadTemplate('links'); ?>
        <?php endif; ?>
            <?php //optional teaser intro text for guests ?>
        <?php elseif ($params->get('show_noauth') == true and  $user->get('guest') ) : ?>
            <?php echo $this->item->introtext; ?>
            <?php //Optional link to let them register to see the whole article. ?>
            <?php if ($params->get('show_readmore') && $this->item->fulltext != null) :
                $link1 = JRoute::_('index.php?option=com_users&view=login');
                $link = new JURI($link1);?>
                <p class="readmore">
                <?php if($params -> get('tz_use_lightbox') == 1):?>
                <a href="<?php echo $link; ?>">
                <?php endif;?>

                <?php $attribs = json_decode($this->item->attribs);  ?>
                <?php
                if ($attribs->alternative_readmore == null) :
                    echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
                elseif ($readmore = $this->item->alternative_readmore) :
                    echo $readmore;
                    if ($params->get('show_readmore_title', 0) != 0) :
                        echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
                    endif;
                elseif ($params->get('show_readmore_title', 0) == 0) :
                    echo JText::sprintf('COM_CONTENT_READ_MORE_TITLE');
                else :
                    echo JText::_('COM_CONTENT_READ_MORE');
                    echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
                endif; ?>
                <?php if($params -> get('tz_use_lightbox') == 1):?>
                </a>
                <?php endif;?>
                </p>
            <?php endif; ?>
        <?php endif; ?>

        <?php
        if (!empty($this->item->pagination) AND $this->item->pagination AND $this->item->paginationposition AND $this->item->paginationrelative):
             echo $this->item->pagination;?>
        <?php endif; ?>
        
        <?php
            //Call event onContentAfterDisplay and onTZPluginAfterDisplay on plugin
            echo $this->item->event->afterDisplayContent;
            echo $this->item->event->TZafterDisplayContent;
        ?>

    </div>
</div>
