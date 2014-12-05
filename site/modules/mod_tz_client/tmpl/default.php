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
defined('_JEXEC') or die();

if($list):
    $title  = $params -> get('title','');
?>
<div class="tzclients<?php echo $moduleclass_sfx; ?>">
<!--    <h2>--><?php //echo $category -> title;?><!--</h2>-->
<!--    <hr>-->
    <?php if($params -> get('show_title',1) AND !empty($title)): ?>
    <h4 class="title">
        <span><?php echo $params -> get('title');?></span>
    </h4>
    <?php endif;?>
    <?php if ($params->get('show_description', 1) AND $params -> get('description')) : ?>
    <div class="description">
        <?php echo $params -> get('description');?>
    </div>
    <?php endif;?>
        <?php foreach ($list as $i => $item) :	?>
        <?php if($i == 0 || $i %4 == 0):?>
        <div class="row-fluid">
        <?php endif;?>
            <div class="span3">
            <?php

            $link = $item->client_link;
            if(!preg_match('/^http\:\/\/.*?/',$link,$match)){
                if(!preg_match('/^http:\/\/www\..*?/',$link)){
                    $link   = 'http://www.'.$link;
                }
                else{
                    $link   = 'http://'.$link;
                }
            }
            switch ($params->get('target', 3))
            {
                case 1:
                    // open in a new window
    ?>
            <a href="<?php echo $link;?>" target="_blank" rel="<?php echo $params->get('follow', 'nofollow');?>">
                <img src="<?php echo JUri::base(true).'/'.$item -> client_image;?>"
                     alt="<?php echo htmlspecialchars($item -> client_title, ENT_COMPAT, 'UTF-8')?>"
                     title="<?php echo htmlspecialchars($item -> client_title, ENT_COMPAT, 'UTF-8')?>"/>
            </a>
    <?php
                    break;

                case 2:
                    // open in a popup window
    ?>
                    <a href="#" target="_blank" rel="<?php echo $params->get('follow', 'nofollow');?>"
                       onclick="window.open('\"<?php echo $link; ?>\"', '', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550'); return false">
                        <img src="<?php echo JUri::base(true).'/'.$item -> client_image;?>"
                             alt="<?php echo htmlspecialchars($item->client_title, ENT_COMPAT, 'UTF-8')?>"
                             title="<?php echo htmlspecialchars($item->client_title, ENT_COMPAT, 'UTF-8')?>"/>
                    </a>
    <?php
                    break;

                default:
                    // open in parent window
    ?>
                    <a href="#" target="_blank" rel="<?php echo $params->get('follow', 'nofollow');?>">
                        <img src="<?php echo JUri::base(true).'/'.$item -> client_image;?>"
                             alt="<?php echo htmlspecialchars($item->client_title, ENT_COMPAT, 'UTF-8')?>"
                             title="<?php echo htmlspecialchars($item->client_title, ENT_COMPAT, 'UTF-8')?>"/>
                    </a>
    <?php
                    break;
            }
            ?>
            </div>
        <?php if($i == (count($list) -1) || $i %4 == 3):?>
        </div>
        <?php endif;?>
        <?php endforeach; ?>
</div>
<?php endif;?>