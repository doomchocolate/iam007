<?php

// This is the code which will be placed in the head section

// No direct access.
defined('_JEXEC') or die;
?>
<?php if($this->browser->get('browser') == 'ie8' || $this->browser->get('browser') == 'ie7' || $this->browser->get('browser') == 'ie6') : ?>
<meta http-equiv="X-UA-Compatible" content="IE=9">
<?php endif; ?>
<?php if($this->getParam("chrome_frame_support", '0') == '1' && ($this->browser->get('browser') == 'ie8' || $this->browser->get('browser') == 'ie7' || $this->browser->get('browser') == 'ie6')) : ?>
<meta http-equiv="X-UA-Compatible" content="chrome=1"/>
<?php endif; ?>

<meta name="HandheldFriendly" content="true" />
<meta name="apple-mobile-web-app-capable" content="YES" />

<?php if($this -> getParam('use_zoom_mobile',0)): ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php else: ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=1">
<?php endif; ?>
<?php
//
$doc = JFactory::getDocument();
// PLAZART BASE HEAD
$this->addHead();
// generate the max-width rules
$max_page_width =   $this->getParam('max_page_width', 0);
$theme  =   $this->getParam('theme', 'default');

if ($max_page_width) {
    $this->addStyleDeclaration('.container-fluid { max-width: '.$this->getParam('max_page_width', '1200').$this->getParam('max_page_width_value', 'px').'!important; }');
}

// CSS override on two methods

if($this->getParam("css_override", '0')) {
	$this->addCSS('override', false);
}

$this->addStyleDeclaration($this->getParam('css_custom', ''));

// include fonts
$font_iter = 1;
$inlinecss  =   '';
while($this->getParam('font_name_group'.$font_iter, 'tzFontNull') !== 'tzFontNull') {
	$font_data = explode(';', $this->getParam('font_name_group'.$font_iter, ''));

	if(isset($font_data) && count($font_data) >= 2) {
		$font_type = $font_data[0];
		$font_name = $font_data[1];

		if($this->getParam('font_rules_group'.$font_iter, '') != ''){
			if($font_type == 'standard') {
                $this->addStyleDeclaration($this->getParam('font_rules_group'.$font_iter, '') . ' { font-family: ' . $font_name . '; }'."\n");
			} elseif($font_type == 'google') {
				$font_link = $font_data[2];
				$font_family = $font_data[3];
				$this->addStyleSheet($font_link);
                $this->addStyleDeclaration($this->getParam('font_rules_group'.$font_iter, '') . ' { font-family: '.$font_family.', Arial, sans-serif; }'."\n");
			} elseif($font_type == 'squirrel') {
				$this->addStyleSheet($this->API->URLtemplate() . '/fonts/' . $font_name . '/stylesheet.css');
				$this->addStyleDeclaration($this->getParam('font_rules_group'.$font_iter, '') . ' { font-family: ' . $font_name . ', Arial, sans-serif; }'."\n");
			} elseif($font_type == 'edge') {
	            $font_link = $font_data[2];
	            $font_family = $font_data[3];
	            $this->addScript($font_link);
	            $this->addStyleDeclaration($this->getParam('font_rules_group'.$font_iter, '') . ' { font-family: ' . $font_family . ', sans-serif; }'."\n");
	        }
		}
	}
	
	$font_iter++;
}

// load prefixer
if($this->getParam("css_prefixer", '0')) {
	$this->addScript(PLAZART_TEMPLATE_REL . '/libraries/js/prefixfree.js');
}

// load lazyload
if($this->getParam("js_lazyload", '0')) {
    $this->addScript(PLAZART_TEMPLATE_REL . '/libraries/js/jquery.lazyload.min.js');
}

$this->addScript(PLAZART_TEMPLATE_REL.'/js/weiss.js');

$this->addScript(PLAZART_TEMPLATE_REL.'/js/page.js');
//$this->addScript(PLAZART_TEMPLATE_REL.'/js/jquery-ui-1.10.3.custom.min.js');
//$this->addScript(PLAZART_TEMPLATE_REL.'/js/jquery.ui.touch-punch.min.js');
//$this->addScript(PLAZART_TEMPLATE_REL.'/js/flatui-checkbox.js');
//$this->addScript(PLAZART_TEMPLATE_REL.'/js/flatui-radio.js');
//$this->addScript(PLAZART_TEMPLATE_REL.'/js/bootstrap-select.js');
//$this->addScript(PLAZART_TEMPLATE_REL.'/js/bootstrap-switch.js');
//$this->addScript(PLAZART_TEMPLATE_REL.'/js/jquery.tagsinput.js');
//$this->addScript(PLAZART_TEMPLATE_REL.'/js/jquery.placeholder.js');
//$this->addScript(PLAZART_TEMPLATE_REL.'/js/jquery.stacktable.js');
//$this->addScript(PLAZART_TEMPLATE_REL.'/js/application.js');


//$this->addScript(PLAZART_TEMPLATE_REL.'/js/modernizr.custom.js');
JHtml::_('bootstrap.tooltip');
?>

<!--<link rel="stylesheet" href="--><?php //echo PLAZART_TEMPLATE_REL.'/css/themes/'.$theme; ?><!--/normalize.css" type="text/css" />-->
<!--<link rel="stylesheet" href="--><?php //echo PLAZART_TEMPLATE_REL.'/css/themes/'.$theme; ?><!--/component.css" type="text/css" />-->

<!--[if IE 9]>
<link rel="stylesheet" href="<?php echo PLAZART_TEMPLATE_REL.'/css/themes/'.$theme; ?>/ie9.css" type="text/css" />
<![endif]-->

<!--[if IE 8]>
<link rel="stylesheet" href="<?php echo PLAZART_TEMPLATE_REL.'/css/themes/'.$theme; ?>/ie8.css" type="text/css" />
<![endif]-->

<!--[if lte IE 7]>
<link rel="stylesheet" href="<?php echo PLAZART_TEMPLATE_REL.'/css/themes/'.$theme; ?>/css/ie7.css" type="text/css" />
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

<script>
    jQuery(document).ready(function(){
        jQuery('.accordion-group').on('shown', function () {
            jQuery(this).find('.accordion-heading').addClass('accordion-collapse');
        });
        jQuery('.accordion-group').on('hidden', function () {
            if(!jQuery(this).find('.accordion-body').hasClass('in')){
                jQuery(this).find('.accordion-heading').removeClass('accordion-collapse');
            }
        });
    });
</script>