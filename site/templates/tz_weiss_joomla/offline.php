<?php

/**
 *
 * offline view
 *
 * @version             1.0.0
 * @package             Plazart Framework
 * @copyright			Copyright (C) 2010 - 2011 TemPlaza. All rights reserved.
 *               
 */
 
// No direct access.
defined('_JEXEC') or die;

$app = JFactory::getApplication();

$uri = JURI::getInstance();
jimport('joomla.factory');

// get necessary template parameters
$templateParams = JFactory::getApplication()->getTemplate(true)->params;

$pageName = JFactory::getDocument()->getTitle();

// get logo configuration
$logo_type = $templateParams->get('logo_type');
$logo_image = $templateParams->get('logo_image');

if(($logo_image == '') || ($templateParams->get('logo_type') == 'css')) {
     $logo_image = JURI::base() .PLAZART_TEMPLATE_REL. '/images/logo.png';
} else {
     $logo_image = JURI::base() . $logo_image;
}
$logo_text = $templateParams->get('logo_text', '');
$logo_slogan = $templateParams->get('logo_slogan', '');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>

	<link rel="stylesheet" type="text/css" href="<?php echo $uri->base(); ?>templates/<?php echo $this->template; ?>/fonts/Colaborate/stylesheet.css" />
	
	<link rel="stylesheet" href="<?php echo PLAZART_TEMPLATE_REL; ?>/css/themes/default/bootstrap.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo PLAZART_TEMPLATE_REL; ?>/css/themes/default/bootstrap-responsive.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo PLAZART_TEMPLATE_REL; ?>/css/themes/default/offline.css" type="text/css" />
    <jdoc:include type="head" />
    <?php
    // include fonts
    $font_iter = 1;
    $inlinecss  =   '';
    while($templateParams->get('font_name_group'.$font_iter, 'tzFontNull') !== 'tzFontNull') {
        $font_data = explode(';', $templateParams->get('font_name_group'.$font_iter, ''));

        if(isset($font_data) && count($font_data) >= 2) {
            $font_type = $font_data[0];
            $font_name = $font_data[1];

            if($templateParams->get('font_rules_group'.$font_iter, '') != ''){
                if($font_type == 'standard') {
                    $this->addStyleDeclaration($templateParams->get('font_rules_group'.$font_iter, '') . ' { font-family: ' . $font_name . '; }'."\n");
                } elseif($font_type == 'google') {
                    $font_link = $font_data[2];
                    $font_family = $font_data[3];
                    $this->addStyleSheet($font_link);
                    $this->addStyleDeclaration($templateParams->get('font_rules_group'.$font_iter, '') . ' { font-family: '.$font_family.', Arial, sans-serif; }'."\n");
                } elseif($font_type == 'squirrel') {
                    $this->addStyleSheet($this->API->URLtemplate() . '/fonts/' . $font_name . '/stylesheet.css');
                    $this->addStyleDeclaration($templateParams->get('font_rules_group'.$font_iter, '') . ' { font-family: ' . $font_name . ', Arial, sans-serif; }'."\n");
                } elseif($font_type == 'edge') {
                    $font_link = $font_data[2];
                    $font_family = $font_data[3];
                    $this->addScript($font_link);
                    $this->addStyleDeclaration($templateParams->get('font_rules_group'.$font_iter, '') . ' { font-family: ' . $font_family . ', sans-serif; }'."\n");
                }
            }
        }

        $font_iter++;
    }
    ?>
</head>
<body>
	<div id="tzPage">
	    <div id="tzPageTop">
		     <?php if ($logo_type !== 'none' && !$app->getCfg('offline_image')): ?>
                  <?php if($logo_type == 'css') : ?>
                 		<a href="./" id="tz-logo" class="css-logo"></a>
                  <?php elseif($logo_type =='text') : ?>
                          <a href="./" id="tz-logo" class="text-logo">
                               <span><?php echo $logo_text; ?></span>
                               <small class="tz-logo-slogan"><?php echo $logo_slogan; ?></small>
                          </a>
                 <?php elseif($logo_type =='image') : ?>
                       <a href="./" id="tz-logo">
                           <img src="<?php echo $logo_image; ?>" alt="<?php echo $pageName; ?>" />
                        </a>
                  <?php endif; ?>
             <?php else : ?>
                  <?php if($app->getCfg('offline_image')) : ?>
                      <a href="./" id="tz-logo">
                            <img src="<?php echo $app->getCfg('offline_image'); ?>" alt="<?php echo $app->getCfg('sitename'); ?>" />
                       </a>
                  <?php endif; ?>
             <?php endif; ?>
        </div>
		<div id="tzPageWrap">     
		     <jdoc:include type="message" />
		      
		     <div id="frame">
		            <h2><?php echo $app->getCfg('offline_message'); ?></h2>
		            <form action="index.php" method="post" name="login" id="form-login">
		                  
		                  <fieldset class="well">
		                        <p id="username">
		                              <label for="username"><?php echo JText::_('JGLOBAL_USERNAME') ?></label>
		                              <input name="username" id="username" type="text" class="inputbox" alt="<?php echo JText::_('JGLOBAL_USERNAME') ?>" size="53" />
		                        </p>
		                        <p id="password">
		                              <label for="passwd"><?php echo JText::_('JGLOBAL_PASSWORD') ?></label>
		                              <input type="password" name="password" class="inputbox" size="53" alt="<?php echo JText::_('JGLOBAL_PASSWORD') ?>" id="passwd" />
		                        </p>
		                        <p id="remember">
		                             <label for="remember">
		                              	<input type="checkbox" name="remember" class="inputbox" value="yes" alt="<?php echo JText::_('JGLOBAL_REMEMBER_ME') ?>" id="remember" />
		                             	<?php echo JText::_('JGLOBAL_REMEMBER_ME') ?>
		                             </label>
		                        </p>
		                        <div class="buttons">
		                              <input type="submit" name="Submit" class="btn" value="<?php echo JText::_('JLOGIN') ?>" />
		                        </div>
		                        <input type="hidden" name="option" value="com_users" />
		                        <input type="hidden" name="task" value="user.login" />
		                        <input type="hidden" name="return" value="<?php echo base64_encode(JURI::base()) ?>" />
		                        <?php echo JHtml::_('form.token'); ?>
		                  </fieldset>
		            </form>
		      </div>
	      </div>
	</div>
</body>
</html>