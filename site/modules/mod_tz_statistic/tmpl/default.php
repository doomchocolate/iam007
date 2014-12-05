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
$columnCount = ceil(12/$params -> get('column_width',4));
    $background = $params -> get('background','');
    $doc    = JFactory::getDocument();
    if($params -> get('textColor')){
        $doc -> addStyleDeclaration('#TzStatistic'.$module -> id.'.TzStatistic{ color: '.$params -> get('textColor').';}');
        $doc -> addStyleDeclaration('#TzStatistic'.$module -> id.' .percent{ color: '.
            $params -> get('textColor').';}');
    }
    if($params -> get('bgColor')){
        $doc -> addStyleDeclaration('#TzStatistic'.$module -> id.' .chart-inner{ background: '.
            $params -> get('bgColor').';}');
    }
?>
<div id="TzStatistic<?php echo $module -> id;?>" class="TzStatistic<?php echo $moduleclass_sfx;?>">


    <?php if(!$params -> get('bg_type') AND ($videoMp4 = $params -> get('video_mp4') OR
            $videoWebm = $params -> get('video_webm') OR $videoOgg = $params -> get('video_ogg')) ):?>
    <video autobuffer="autobuffer"
        <?php echo ($params -> get('muted'))?' muted="muted"':'';?>
        <?php echo ($params -> get('loop'))?' loop="loop"':'';?>
        <?php echo ($params -> get('loop'))?' autoplay="autoplay"':'';?> >
        <source type="video/mp4" src="<?php echo JUri::base().'/images/'.$videoMp4;?>"/>
        <source type="video/webm" src="<?php echo JUri::base().'/images/'.$videoWebm;?>"/>
        <source type="video/ogg" src="<?php echo JUri::base().'/images/'.$videoOgg;?>"/>
        <object data="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf?volume_level=0" type="application/x-shockwave-flash">
            <param value="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf?volume_level=0" name="movie">
            <param value="true" name="allowFullScreen">
            <param value="transparent" name="wmode">
            <param value="config={'playlist':[{'url':'<?php echo JUri::base().$videoMp4;?>'
            <?php echo ($params -> get('muted'))?',"muted"= true':'';?>
            <?php echo ($params -> get('loop'))?',"loop"= true':'';?>
            <?php echo ($params -> get('loop'))?',"autoplay"= true':'';?>}]}" name="flashVars">
<!--            <img title="No video playback capabilities, please download the video below" src="http://sandbox.thewikies.com/vfe-generator/images/big-buck-bunny_poster.jpg" alt="Big Buck Bunny">-->
        </object>
    </video>
    <?php endif;?>

    <?php if($params -> get('bg_type') AND $background = $params -> get('background')):?>
    <img src="<?php echo JUri::base().$background;?>" alt="" class="bg-image"/>
    <?php endif;?>

    <?php if($params -> get('show_bg_over',1)):?>
    <div class="bg-overlay"></div>
    <?php endif;?>
    <div class="statistic-inner">
        <?php if($params -> get('show_heading') AND ($heading = $params -> get('heading',null))):?>
            <h4 class="heading">
                <?php echo $heading;?>
                <?php if($params -> get('show_introtext',1) AND $introtext = $params -> get('description')):?>
                <small class="description"><?php echo $introtext;?></small>
                <?php endif;?>
            </h4>
        <?php endif; ?>

        <?php foreach ($list as $i => $item) :	?>
            <?php if($i % $columnCount == 0):?>
            <div class="row-fluid">
            <?php endif;?>
                <div class="span<?php echo $params -> get('column_width',4);?>">
                    <div class="chart" data-percent="<?php echo $item -> statistic_percent; ?>">
                        <div class="chart-inner">
                            <span class="percent">
                                <?php if(!$params -> get('animate',1)) echo $item -> statistic_percent;?>
                            </span>
                        </div>
                    </div>
                    <span class="title"><?php echo $item -> statistic_title;?></span>
                </div>
            <?php if($i % $columnCount == ($columnCount - 1) OR $i == (count($list) - 1)): ?>
            </div>
            <?php endif;?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif;?>