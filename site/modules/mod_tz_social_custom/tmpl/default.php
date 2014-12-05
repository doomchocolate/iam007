<?php
/*------------------------------------------------------------------------

# TZ Portfolio Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2012-2013 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

//no direct access
defined('_JEXEC') or die();
$doc =  JFactory::getDocument();

if( ($params -> get('show_facebook_button',0) OR $params -> get('show_twitter_button',0)
OR $params -> get('show_google_plus_button',0) OR $params -> get('show_pinterest_button',0)
OR $params -> get('show_linkedin_button',0)) AND $list):
?>
<div class="social-custom<?php echo $moduleclass_sfx; ?>">
<?php endif;?>
    <?php if($params -> get('show_description',0) AND $description = $params -> get('description')
        AND !empty($description)):?>
    <p><?php echo $params -> get('description');?></p>
    <?php endif;?>

    <?php require JModuleHelper::getLayoutPath('mod_tz_social_custom', 'default_buttons');?>

    <?php
    if($list):
    $columCount = 12/$params -> get('column_width',4);
        $image  = new JImage();
    ?>
    <ul class="social-link <?php echo $moduleclass_sfx; ?>">
        <?php foreach ($list as $i => $item) :	?>
        <li>
            <a<?php //echo ($socialClass = $item -> social_icon_class)?' class="'.$socialClass.'"':'';?>
                href="<?php echo $item -> social_link;?>"<?php echo ($follow = $params -> get('follow','nofollow'))?' rel="nofollow"':''?>

                <?php echo ($params -> get('show_social_title',0))?' title="'.$item -> social_title.'"':'';?>>
                <?php if($socialClass = $item -> social_icon_class):?>
                    <span class="<?php echo $socialClass;?>"></span>
                <?php endif;?>

                <?php if($socialIcon = $item -> social_image):?>
                <img class="item<?php echo $i;?>" src="<?php echo JUri::root().$socialIcon?>" alt="<?php echo $item -> social_title;?>">
                <?php if($socialIconHover = $item-> social_image_hover):
                        $image -> loadFile($socialIcon);
                    $doc -> addStyleDeclaration('
                    .social-link a:hover .item'.$i.',.social-link a:active .item'.$i.',.social-link a:focus .item'.$i.'{
                        background:url("'.$socialIconHover.'") no-repeat;
                        width: '.$image -> getWidth().'px;
                        height: '.$image -> getHeight().'px;
                        padding-left: '.$image -> getWidth().'px;
                        box-sizing: border-box;
                        -moz-box-sizing: border-box;
                        -webkit-box-sizing: border-box;
                    }
                    ');
                endif;?>
                <?php endif;?>

                <?php if($params -> get('show_social_title',0)):?>
                <span class="title"><?php echo $item -> social_title;?></span>
                <?php endif;?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif;?>
<?php if( ($params -> get('show_facebook_button',1) OR $params -> get('show_twitter_button',1)
    OR $params -> get('show_google_plus_button',1) OR $params -> get('show_pinterest_button')
    OR $params -> get('show_linkedin_button',1)) AND $list):?>
</div>
<?php endif;?>