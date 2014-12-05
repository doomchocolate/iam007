<?php
/**
 *
 * Plazart framework layout
 *
 * @version             1.0.0
 * @package             Plazart Framework
 * @copyright			Copyright (C) 2012 - 2013 TemPlaza. All rights reserved.
 *
 */
 
// no direct access
defined('_JEXEC') or die;

// get important objects
$doc = JFactory::getDocument();
// Add current user information
$user = JFactory::getUser();

// get the option and view value
$option = JRequest::getCmd('option');
$view = JRequest::getCmd('view');
$current_url = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
$current_url = preg_replace('@%[0-9A-Fa-f]{1,2}@mi', '', htmlspecialchars($current_url, ENT_QUOTES, 'UTF-8'));


// Adjusting content width
$this->setParam('mainbody_width',12);
$sidebar_right_width = $this->getParam('right_sidebar_width', 3);
$sidebar_left_width = $this->getParam('left_sidebar_width', 3);

if ($this->countModules('right') && $this->countModules('left')=='') {
    $this->setParam('mainbody_width',12 - $sidebar_right_width);
}
if ($this->countModules('left') && $this->countModules('right')=='') {
    $this->setParam('mainbody_width',12 - $sidebar_left_width);
}
if ($this->countModules('right') && $this->countModules('left')){
 $this->setParam('mainbody_width',12 - $sidebar_right_width - $sidebar_left_width);
}

?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" class="<?php $this->bodyClass(); ?><?php echo $this -> getParam('right_to_left',0)?' rtl':'';?>">
<head>
	<jdoc:include type="head" />
	<?php $this->loadBlock('head'); ?>
</head>

<body<?php if($this->browser->get("tablet") == true) echo ' data-tablet="true"'; ?><?php if($this->browser->get("mobile") == true) echo ' data-mobile="true"'; ?> class="<?php echo $this->bodyClass() ?>">
<div style="position:absolute;top:0;left:-9999px;">
<a href="http://joomla4ever.ru/templaza/2669-tz-weiss.html" title="TZ Weiss - шаблон joomla" target="_blank">TZ Weiss - шаблон joomla</a>
<a href="http://forexlab.info/" title="Форекс" target="_blank">Форекс</a>
</div>
    <!--<div id="st-container" class="st-container">-->
        <?php
        if ($this->getParam('layout_enable',0)) {
            $this->layout();
        } else {
            $this->loadBlock('body');
        }
    ?>
    <?php
    if($this -> getParam('show_to_top_button',1)):
        $this ->addScriptDeclaration('
            (function($){
                $(document).ready(function(){
                    $("#tz-totop").bind("click",function(){
                        jQuery(\'html, body\').animate({scrollTop:0},700);
                    });
                });
            })(jQuery);
        ');
    ?>
    <div class="tz-totop">
        <a href="javascript:" id="tz-totop"><?php echo JText::_('TPL_TZ_LANG_TOP');?></a>
    </div>
    <?php endif;?>
<!--</div>-->
</body>
</html>
<?php
$app    = JFactory::getApplication();
$input  = $app -> input;
if($input -> get('option') != 'com_hikashop'){
    TZRules::setRule('/<link rel="stylesheet" href="(.*?)media\/com_hikashop\/css\/frontend_default.css\?.*?" type="text\/css" \/>/mi','');
}
if($this -> getParam('favicon_image',null)){
    TZRules::setRule('/<link href="(.*?)\/templates\/'.$this -> template.'\/favicon.ico" rel="shortcut icon".*?\/>/im','');
    $this -> API -> addFavicon(JUri::base().$this -> getParam('favicon_image',null));
}
// Rules to remove predefined jQuery and Bootstrap and MooTools More
//TZRules::setRule('/<script src="(.*?)components\/com_community\/assets\/joms.jquery-1.8.1.min.js" type="text\/javascript"><\/script>/mi','');