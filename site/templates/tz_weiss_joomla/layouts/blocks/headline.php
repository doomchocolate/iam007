<?php
// No direct access.
defined('_JEXEC') or die;
$app = JFactory::getApplication();
?>
<?php if ($this->API->modules('headline')): ?>
    <section class="tz-headline tz-border-bottom tz-border-shadow">
        <div class="container-fluid">
        <div class="tz-inner">
            <jdoc:include type="modules" name="headline" style="tz_style" />
        </div>
        </div>
    </section>
<?php endif; ?>
