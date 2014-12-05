<?php
// No direct access.
defined('_JEXEC') or die;
?>
<?php if($this->API->modules('social')) : ?>
<div id="tz-social-icons" class="<?php echo $this->API->get('social_pos', 'left'); ?>">
    <jdoc:include type="modules" name="social" style="none" />
</div>
<?php endif; ?>

<script type="text/javascript">
    jQuery('*[rel=tooltip]').tooltip();
    jQuery('*[rel=popover]').popover();
    jQuery('.tip-bottom').tooltip({placement: "bottom"});
</script>

<?php $this->loadBlock('social'); ?>
<jdoc:include type="modules" name="debug" style="none" />
<?php
if($this->API->get("js_lazyload", '0')) {
    echo '<script type="text/javascript">jQuery("img.lazy").lazyload();</script>';
}
?>