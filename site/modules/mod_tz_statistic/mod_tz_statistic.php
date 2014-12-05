<?php
/*------------------------------------------------------------------------

# TZ Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2012 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

defined('_JEXEC') or die();

require_once dirname(__FILE__).'/helper.php';

$list            = modTZStatistcHelper::getList($params);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

$doc    = JFactory::getDocument();
$doc -> addStyleSheet(JUri::root().'modules/mod_tz_statistic/css/style.css');
$doc -> addScript('//html5shiv.googlecode.com/svn/trunk/html5.js');
$doc -> addScript(JUri::root().'modules/mod_tz_statistic/js/canvas.js');
$doc -> addScript(JUri::root().'modules/mod_tz_statistic/js/jquery.easing.min.js');
$doc -> addScript(JUri::root().'modules/mod_tz_statistic/js/jquery.easypiechart.min.js');
$doc -> addScript(JUri::root().'modules/mod_tz_statistic/js/resizeimage.js');

$scaleColor = 'false';
if($params -> get('scaleColor')){
    $scaleColor = '"'.$params -> get('scaleColor').'"';
}

$doc -> addStyleDeclaration('
    #TzStatistic'.$module -> id.'.TzStatistic .chart{
        width: '.$params -> get('size',110).'px;
        height: '.$params -> get('size',110).'px;
    }
');


$doc -> addScriptDeclaration('
    var chartfunc'.$module -> id.'   = function(){
        var rtop    = jQuery("#TzStatistic'.$module -> id.' .chart").offset().top,
            wheight = jQuery(this).height(),
            wtop    = jQuery(this).scrollTop() + wheight/3;
        if((jQuery(this).scrollTop() >= (rtop - wheight/1.2)) && (jQuery(this).scrollTop() <= ((rtop + wheight/3)*2))){
            jQuery("#TzStatistic'.$module -> id.' .chart").easyPieChart({
                easing: "'.$params -> get('easing','defaultEasing').'",
                trackColor: "'.$params -> get('trackColor','#f9f9f9').'",
                barColor: "'.$params -> get('barColor','#ef1e25').'",
                scaleColor:'.$scaleColor.',
                lineWidth:'.$params -> get('lineWidth',3).',
                size: '.$params -> get('size',110).',
                lineCap: "'.$params -> get('lineCap','butt').'",
                rotate: '.$params -> get('rotate',0).',
                animate: {
                    duration: '.$params -> get('duration',1000).',
                    enabled: '.$params -> get('animate',1).'
                },
                onStart: function(from, to){},
                onStep: function(from, to, percent) {
                    jQuery(this.el).find(".percent").text(Math.round(percent));
                },
                onStop: function(from, to){}
            });
		}
    }
    jQuery(document).ready(function(){
        var elchart = jQuery("#TzStatistic'.$module -> id.' .chart");
        elchart.find(".percent").css({
            "margin-top":(elchart.height() - elchart.find(".percent").height())/2
        });
//        chartfunc'.$module -> id.'();
    });
    jQuery(window).scroll(function(){
        chartfunc'.$module -> id.'();
    });
');

$ratio =  explode(':',$params -> get('video_ratio','360:640'));
$doc -> addScriptDeclaration('jQuery(function(){
        var videoW  = '.$ratio[1].',
            videoH  = '. $ratio[0].';

        jQuery(window).bind("load resize",function(){
            var imgsize = resizeImage(videoW,videoH,jQuery("#tz-skill-wrapper").outerWidth(),jQuery("#tz-skill-wrapper").outerHeight());
            jQuery(".TzStatistic").find("video").css({
               width: imgsize.width,
                height: imgsize.height,
                top: imgsize.top,
                left: imgsize.left
            });
        });
    });');

require JModuleHelper::getLayoutPath('mod_tz_statistic',$params -> get('layout','default'));
