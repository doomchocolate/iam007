<?php

// No direct access.
defined('_JEXEC') or die;

$option = JRequest::getCmd('option', '');
$view = JRequest::getCmd('view', '');

?>

<?php if($this->API->get('fb_like', '0') == 1) : ?>
<plazart:social>
<div id="fb-root"></div>
<?php if($this->API->get('cookie_consent', '0') == 0) : ?>
<script type="text/javascript">
<?php else : ?>
<script type="text/plain" class="cc-onconsent-social">
<?php endif; ?>

        (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/<?php echo $this->API->get('fb_lang','en_US');?>/all.js#xfbml=1";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>
</plazart:social>
<?php endif; ?>

<!-- +1 button -->
<?php if($this->API->get('google_plus', '1') == 1 && $option == 'com_tz_portfolio' && $view == 'article') : ?>
<plazart:social>
<?php if($this->API->get('cookie_consent', '0') == 0) : ?>
<script type="text/javascript">
<?php else : ?>
<script type="text/plain" class="cc-onconsent-social">
<?php endif; ?>
  window.___gcfg = {lang: '<?php echo $this->API->get("google_plus_lang", "en-GB"); ?>'};
  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>
</plazart:social>
<?php endif; ?>

<!-- twitter -->
<?php if($this->API->get('tweet_btn', '0') == 1 && $option == 'com_tz_portfolio' && $view == 'article') : ?>
     <?php if($this->API->get('cookie_consent', '0') == 0) : ?>
     <script type="text/javascript" src="//platform.twitter.com/widgets.js"></script>
     <?php else : ?>
     <script type="text/plain" class="cc-onconsent-social" src="//platform.twitter.com/widgets.js"></script>
     <?php endif; ?>
<?php endif; ?>


<!-- Pinterest script --> 
<?php if($this->API->get('pinterest_btn', '1') == 1 && $option == 'com_tz_portfolio' && $view == 'article') : ?><plazart:social>
<?php if($this->API->get('cookie_consent', '0') == 0) : ?>
<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>
<?php else : ?>
<script type="text/plain" class="cc-onconsent-social" src="//assets.pinterest.com/js/pinit.js"></script>
<?php endif; ?>

</plazart:social>
<?php endif; ?>

<?php 
	// put Google Analytics code
	echo $this->social->googleAnalyticsParser();

// EOF