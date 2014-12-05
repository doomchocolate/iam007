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

if($params -> get('show_facebook_button',0) OR $params -> get('show_twitter_button',0)
    OR $params -> get('show_google_plus_button',0) OR $params -> get('show_pinterest_button',0)
    OR $params -> get('show_linkedin_button',0)):
?>
    <?php if($params -> get('show_facebook_button',0)):?>
<!--    <div class="TzFacebookButton">-->
    <!-- Facebook Button -->
    <div id="fb-root"></div>
    <script type="text/javascript">
        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/en_US/all.js#appId=177111755694317&xfbml=1";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>
    <div class="fb-like" data-send="false" data-width="200" data-show-faces="true"
         data-layout="button_count" data-href="<?php echo JUri::base();?>"></div>
<!--    </div>-->
    <?php endif; ?>

    <?php if($params -> get('show_twitter_button',0)):?>
<!--    <div class="TzTwitterButton">-->
    <!-- Twitter Button -->
    <a href="<?php echo JUri::base();?>" class="twitter-share-button"
       data-count="horizontal" data-size="small"></a>
    <script type="text/javascript" src="//platform.twitter.com/widgets.js"></script>
<!--    </div>-->
    <?php endif; ?>

    <?php if($params -> get('show_google_plus_button',0)):?>
    <!-- Google +1 Button -->
    <!-- Place this tag where you want the +1 button to render. -->
    <div class="g-plusone" data-size="medium" data-href="<?php echo JUri::base();?>"></div>

    <!-- Place this tag after the last +1 button tag. -->
    <script type="text/javascript">
        (function() {
            var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
            po.src = 'https://apis.google.com/js/plusone.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
        })();
    </script>
    <?php endif;?>

    <?php if($params -> get('show_pinterest_button',0)):?>
    <?php
    $template   = JFactory::getApplication() -> getTemplate(true);
    $tplparams  = $template -> params;
    $image      = null;
    if($tplparams -> get('logo_image')){
        $image  = $tplparams -> get('logo_image');
    }
    if(JFile::exists(JPATH_SITE.'/templates/'.$template -> template.'/images/logo.png')){
        $image  = JUri::root().'templates/'.$template -> template.'/images/logo.png';
    }
    $config = JFactory::getConfig();
    ?>
<!--    <div class="TzPinterestButton">-->
    <!-- Pinterest Button -->
    <a href="http://pinterest.com/pin/create/button/?url=<?php echo JUri::base();?>&amp;media=<?php echo $image;?>&amp;description=<?php echo urlencode($config -> get('sitename'));?>"
       data-pin-do="buttonPin" data-pin-config="beside">
        <img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" alt="" />
    </a>
    <script type="text/javascript">
        (function(d){
            var f = d.getElementsByTagName('SCRIPT')[0], p = d.createElement('SCRIPT');
            p.type = 'text/javascript';
            p.async = true;
            p.src = '//assets.pinterest.com/js/pinit.js';
            f.parentNode.insertBefore(p, f);
        }(document));
    </script>
<!--    </div>-->
    <?php endif;?>

    <?php if($params -> get('show_linkedin_button',0)):?>
    <!-- Linkedin Button -->
    <script src="//platform.linkedin.com/in.js" type="text/javascript"></script>
    <script type="IN/Share" data-url="<?php echo JUri::base();?>" data-counter="right"></script>
    <?php endif;?>
<?php endif; ?>