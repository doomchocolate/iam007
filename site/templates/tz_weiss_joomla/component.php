<?php
/**
 * Plazart Framework
 * Author: Sonle
 * Version: 1.1
 * @copyright   Copyright (C) 2012 - 2013 TemPlaza.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

// no direct access
defined('_JEXEC') or die;

// getting document object
$doc = JFactory::getDocument();

$app    = JFactory::getApplication();
$template   = $app -> getTemplate(true);
$tplparams  = $template -> params;

$theme  =   $tplparams->get('theme', 'default');
// Check for the print page
$print = JRequest::getCmd('print');
// Check for the mail page
$mailto = JRequest::getCmd('option') == 'com_mailto';
$config = new JConfig();
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>

    <jdoc:include type="head" />

    <?php
    $this->addStyleSheet(PLAZART_TEMPLATE_REL.'/css/themes/'.$theme.'/bootstrap.css');
    ?>
    <?php
    if(version_compare(JVERSION,'3.0','>=')){
        JHtml::_('bootstrap.tooltip');
    }
    ?>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="HandheldFriendly" content="true" />
    <meta name="apple-mobile-web-app-capable" content="YES" />

    <?php
    // include fonts
    $font_iter = 1;
    $inlinecss  =   '';
    while($tplparams->get('font_name_group'.$font_iter, 'tzFontNull') !== 'tzFontNull') {
        $font_data = explode(';', $tplparams->get('font_name_group'.$font_iter, ''));

        if(isset($font_data) && count($font_data) >= 2) {
            $font_type = $font_data[0];
            $font_name = $font_data[1];

            if($tplparams->get('font_rules_group'.$font_iter, '') != ''){
                if($font_type == 'standard') {
                    $this->addStyleDeclaration($tplparams->get('font_rules_group'.$font_iter, '') . ' { font-family: ' . $font_name . '; }'."\n");
                } elseif($font_type == 'google') {
                    $font_link = $font_data[2];
                    $font_family = $font_data[3];
                    $this->addStyleSheet($font_link);
                    $this->addStyleDeclaration($tplparams->get('font_rules_group'.$font_iter, '') . ' { font-family: '.$font_family.', Arial, sans-serif; }'."\n");
                } elseif($font_type == 'squirrel') {
                    $this->addStyleSheet(PLAZART_TEMPLATE_REL. '/fonts/' . $font_name . '/stylesheet.css');
                    $this->addStyleDeclaration($tplparams->get('font_rules_group'.$font_iter, '') . ' { font-family: ' . $font_name . ', Arial, sans-serif; }'."\n");
                } elseif($font_type == 'edge') {
                    $font_link = $font_data[2];
                    $font_family = $font_data[3];
                    $this->addScript($font_link);
                    $this->addStyleDeclaration($tplparams->get('font_rules_group'.$font_iter, '') . ' { font-family: ' . $font_family . ', sans-serif; }'."\n");
                }
            }
        }

        $font_iter++;
    }

    // load prefixer
    if($tplparams->get("css_prefixer",'0')) {
        $this->addScript(PLAZART_TEMPLATE_REL . '/libraries/js/prefixfree.js');
    }

    $this->addScript(PLAZART_TEMPLATE_REL.'/js/page.js');
//    $this->addScript(PLAZART_TEMPLATE_REL.'/js/flatui-checkbox.js');
//    $this->addScript(PLAZART_TEMPLATE_REL.'/js/flatui-radio.js');

    // PLAZART BASE HEAD
    $this -> addStyleSheet(PLAZART_TEMPLATE_REL.'/css/themes/'.$theme.'/template.css');
    //    $template->addHead();
    ?>
    <!--[if IE 9]>
    <link rel="stylesheet" href="<?php echo PLAZART_TEMPLATE_REL.'/css/'.$theme; ?>/ie9.css" type="text/css" />
    <![endif]-->

    <!--[if IE 8]>
    <link rel="stylesheet" href="<?php echo PLAZART_TEMPLATE_REL.'/css/'.$theme; ?>/ie8.css" type="text/css" />
    <![endif]-->

    <!--[if lte IE 7]>
    <link rel="stylesheet" href="<?php echo PLAZART_TEMPLATE_REL.'/css/'.$theme; ?>/css/ie7.css" type="text/css" />
    <script src="<?php echo PLAZART_TEMPLATE_REL.'/js/icon-font-ie7.js'; ?>"></script>
    <![endif]-->

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- For IE6-8 support of media query -->
    <!--[if lt IE 9]>

    <script type="text/javascript" src="<?php echo PLAZART_URL ?>/js/respond.min.js"></script>
    <![endif]-->
    <?php if($mailto == true) : ?>
	<?php $this->addStyleSheet(PLAZART_TEMPLATE_REL.'/css/mail.css'); ?>
	<?php endif; ?>
	
	<?php if($print == 1) : ?>     
	<link rel="stylesheet" href="<?php echo PLAZART_TEMPLATE_REL.'/css/print.css'; ?>" type="text/css" media="screen" />
	<link rel="stylesheet" href="<?php echo PLAZART_TEMPLATE_REL.'/css/print.css'; ?>" type="text/css" media="print" />
	<?php endif; ?>
</head>
<body class="contentpane">
	<?php 
		if($print == 1) : 
			$params = JFactory::getApplication()->getTemplate(true)->params;
			$logo_text = $params->get('logo_text', '') != '' ? $params->get('logo_text', '') : $config->sitename;
			$logo_slogan = $params->get('logo_slogan', '');
	?>    
	<div id="tz-print-top">
		<img src="<?php echo JURI::base(); ?>templates/<?php echo $this->template; ?>/images/logo_print.png" alt="<?php echo $logo_text . ' - ' . $logo_slogan; ?>" />
	</div>
	<?php endif; ?>
	
	<jdoc:include type="message" />
	<jdoc:include type="component" />
	
	<?php 
	
		if($print == 1) : 
		
		function TZParserEmbed() {
			$body = JResponse::getBody();
			$body = preg_replace('/<plazart:fblogin(.*?)plazart:fblogin>/mis', '', $body);
			$body = preg_replace('/<plazart:social><fb:like(.*?)fb:like><\/plazart:social>/mi', '', $body);
			$body = preg_replace('/<plazart:social><g:plusone(.*?)g:plusone><\/plazart:social>/mi', '', $body);
			$body = preg_replace('/<plazart:social><a href="http:\/\/twitter.com\/share"(.*?)\/a><\/plazart:social>/mi', '', $body);
			$body = preg_replace('/<plazart:social><a href="http:\/\/pinterest.com\/pin\/create\/button\/(.*?)\/a><\/plazart:social>/mi', '', $body);
			$body = preg_replace('/<plazart:social>/mi', '', $body);
			$body = preg_replace('/<\/plazart:social>/mi', '', $body);
			$body = preg_replace('/<plazart:socialAPI>/mi', '', $body);
			$body = preg_replace('/<\/plazart:socialAPI>/mi', '', $body);
			
			JResponse::setBody($body);
		}
		
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->register('onAfterRender', 'TZParserEmbed');
		
	?>    
	<div id="tz-print-bottom">
		<?php if($params->get('copyrights', '') == '') : ?>
			&copy; Blank Plazart - <a href="http://www.templaza.com" title="Free Joomla! 3.0 Template">Free Joomla! 3.0 Template</a> <?php echo date('Y');?>
		<?php else : ?>
			<?php echo $params->get('copyrights', ''); ?>
		<?php endif; ?> 
	</div>
	<?php endif; ?>
</body>
</html>
