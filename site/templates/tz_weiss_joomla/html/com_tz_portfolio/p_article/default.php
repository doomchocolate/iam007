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
$doc    = JFactory::getDocument();
$doc -> addStyleSheet(PLAZART_TEMPLATE_REL.'/css/themes/default/component.css');
$doc -> addStyleSheet(PLAZART_TEMPLATE_REL.'/css/themes/default/normalize.css');
$doc -> addStyleDeclaration('
    body div, body section, body header{display:none;}
    body #tz-main-body-wrapper,
    body #tz-main-body-wrapper section,
    body #tz-main-body-wrapper footer,
    body #tz-main-body-wrapper div{
    display:block;
    }
    .TzItemPageInner{
        top:0;
        right:0;
        left:0;
        z-index:9999;
        background:#fff;
    }
');
$tmpl   = JRequest::getString('tmpl');
if($tmpl){
    $tmpl   = '&tmpl=component';
}

$media  = $this -> listMedia;
$mediaType='';
if(!empty($media[0] -> images)):
    $mediaType =  $media[0]->type;
endif;
?>

<div class="TzPortfolioItemPage item-page<?php echo $this->pageclass_sfx?>">
    <div class="TzItemPageInner TzItemPortfolioInner <?php if($mediaType=='' || $mediaType=='video' || $mediaType=='audio'){echo "TzPortfolioNoneMedia"; } ?>">

        <?php if ($this->params->get('show_page_heading', 1)) : ?>
<!--            <h1 class="page-heading">-->
<!--            --><?php //echo $this->escape($this->params->get('page_heading')); ?>
<!--            </h1>-->
        <?php endif; ?>
        <?php
        if (!empty($this->item->pagination) AND $this->item->pagination && !$this->item->paginationposition && $this->item->paginationrelative)
        {
         echo $this->item->pagination;
        }
         ?>
        <div class="PortfolioDetailMedia intro-effect-fadeout" id="DetailImage">
            <div class="PortfolioDetailTop">
                <a class="lefttop" href="<?php echo JRoute::_(TZ_PortfolioHelperRoute::getCategoryRoute($this->item->catid));?>"><?php echo JText::_('TPL_TZ_WEISS_BACK_TO_PORTFOLIO');?></a>
                <?php
                if (!empty($this->item->pagination) AND $this->item->pagination AND $this->item->paginationposition AND !$this->item->paginationrelative):
                    echo $this->item->pagination;?>
                <?php endif; ?>
            </div>
        <?php
            if($params -> get('show_image',1) OR $params -> get('show_image_gallery',1)
                 OR $params -> get('show_video',1) OR $params -> get('show_audio',1)):
        ?>
            <?php echo $this -> loadTemplate('media');?>


        <?php endif;?>
        <div class="PortfolioDetailInfo">
            <?php if ($params->get('show_title',1)) : ?>
                <h1 class="TzArticleTitle">
                    <?php if ($params->get('link_titles',1) AND !empty($this->item->readmore_link)) : ?>
                        <?php
                        if($params -> get('tz_use_lightbox') == 1):
                            $titleLink = JRoute::_(TZ_PortfolioHelperRoute::getPortfolioArticleRoute($this -> item -> slug, $this -> item -> catid).$tmpl);
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
                </h1>
            <?php endif; ?>
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
                            $menu   = JMenu::getInstance('site');
                            $item = $menu->getItems('link', $needle, true);
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
                            <?php echo JText::sprintf('COM_CONTENT_PARENT', $url); ?>
                        <?php else : ?>
                            <?php echo JText::sprintf('COM_CONTENT_PARENT', $title); ?>
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
                    <i class="fa fa-clock-o"></i>
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
                    </div>
                <?php endif; ?>
            <?php echo $this -> loadTemplate('tag');?>

        </div>


        </div>

        <div class="PortfolioDetailContent">


                <?php if (!$this->print) : ?>
                    <?php if ($canEdit ||  $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
                        <div class="TzIcon">
                            <div class="btn-group pull-right">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:">
                                    <i class="fa fa-cog"></i> <span class="fa fa-caret-down"></span>
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

                <?php if (!$this->print) : ?>
                    <?php echo $this -> loadTemplate('social_network');?>
                <?php endif;?>





            <?php echo $this -> loadTemplate('extra_fields');?>

            <?php echo $this -> loadTemplate('attachments');?>

            <?php echo $this -> loadTemplate('gmap');?>

            <?php
            echo $this -> loadTemplate('author');
            ?>

            <?php if (!$this->print) : ?>

                <?php echo $this -> item -> event -> onTZPortfolioCommentDisplay;?>

                <?php echo $this -> loadTemplate('related');?>
            <?php endif;?>
        </div>

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


<script type="text/javascript" src="<?php  echo JUri::base().PLAZART_TEMPLATE_REL;?>/js/classie.js"></script>

<script type="text/javascript">
    (function() {
        var win_height = jQuery(window).height();
        if(jQuery('div').hasClass('TzArticleMedia')){
            var MediaPosition = jQuery('.TzArticleMedia').position();
            var MediaTop = MediaPosition.top;
            jQuery('.TzArticleMedia').css({
                'margin-top':'-'+MediaTop+'px',
                height:win_height
            });
        }
        if(jQuery('div').hasClass('flexslider')){
            jQuery('.flexslider').css({
                height:win_height
            });
        }


        // detect if IE : from http://stackoverflow.com/a/16657946
        var ie = (function(){
            var undef,rv = -1; // Return value assumes failure.
            var ua = window.navigator.userAgent;
            var msie = ua.indexOf('MSIE ');
            var trident = ua.indexOf('Trident/');

            if (msie > 0) {
                // IE 10 or older => return version number
                rv = parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
            } else if (trident > 0) {
                // IE 11 (or newer) => return version number
                var rvNum = ua.indexOf('rv:');
                rv = parseInt(ua.substring(rvNum + 3, ua.indexOf('.', rvNum)), 10);
            }

            return ((rv > -1) ? rv : undef);
        }());


        // disable/enable scroll (mousewheel and keys) from http://stackoverflow.com/a/4770179
        // left: 37, up: 38, right: 39, down: 40,
        // spacebar: 32, pageup: 33, pagedown: 34, end: 35, home: 36
        var keys = [32, 37, 38, 39, 40], wheelIter = 0;

        function preventDefault(e) {
            e = e || window.event;
            if (e.preventDefault)
                e.preventDefault();
            e.returnValue = false;
        }

        function keydown(e) {
            for (var i = keys.length; i--;) {
                if (e.keyCode === keys[i]) {
                    preventDefault(e);
                    return;
                }
            }
        }

        function touchmove(e) {
            preventDefault(e);
        }

        function wheel(e) {
            // for IE
            //if( ie ) {
            //preventDefault(e);
            //}
        }

        function disable_scroll() {
            window.onmousewheel = document.onmousewheel = wheel;
            document.onkeydown = keydown;
            document.body.ontouchmove = touchmove;
        }

        function enable_scroll() {
            window.onmousewheel = document.onmousewheel = document.onkeydown = document.body.ontouchmove = null;
        }

        var docElem = window.document.documentElement,
            scrollVal,
            isRevealed,
            noscroll,
            isAnimating,
            Parentcontainer = document.getElementById( 'tz-component' ),
            container = document.getElementById( 'DetailImage' );

        function scrollY() {
            return window.pageYOffset || docElem.scrollTop;
        }

        function scrollPage() {
            scrollVal = scrollY();

            if( noscroll && !ie ) {
                if( scrollVal < 0 ) return false;
                // keep it that way
                window.scrollTo( 0, 0 );
            }

            if( classie.has( container, 'notrans' ) ) {
                classie.remove( container, 'notrans' );
                return false;
            }

            if( isAnimating ) {
                return false;
            }

            if( scrollVal <= 0 && isRevealed ) {
                toggle(0);
            }
            else if( scrollVal > 0 && !isRevealed ){
                toggle(1);
            }
        }

        function toggle( reveal ) {
            isAnimating = true;

            if( reveal ) {
                classie.add( container, 'modify' );
                classie.add( Parentcontainer, 'scrolling' );
            }
            else {
                noscroll = true;
                disable_scroll();
                classie.remove( container, 'modify' );
                classie.remove( Parentcontainer, 'scrolling' );
            }

            // simulating the end of the transition:
            setTimeout( function() {
                isRevealed = !isRevealed;
                isAnimating = false;
                if( reveal ) {
                    noscroll = false;
                    enable_scroll();
                }
            }, 600 );
        }

        // refreshing the page...
        var pageScroll = scrollY();
        noscroll = pageScroll === 0;

        disable_scroll();

        if( pageScroll ) {
            isRevealed = true;
            classie.add( container, 'notrans' );
            classie.add( container, 'modify' );
            classie.add( Parentcontainer, 'scrolling' );
        }

        window.addEventListener( 'scroll', scrollPage );
    })();
</script>

