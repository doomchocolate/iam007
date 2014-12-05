<?php
// No direct access.
defined('_JEXEC') or die;
$app = JFactory::getApplication();
?>
<?php if ($this->countModules('bottom')): ?>
    <section class="tz-bottom tz-border-bottom tz-border-shadow">
        <div class="container-fluid">
        <div class="tz-inner">
            <div class="row-fluid">
                <?php if ($this->countModules('bottom')): ?>
                <div class="span12 tz-bottom">
                <jdoc:include type="modules" name="bottom" style="tz_style" />
                </div>
                <?php endif; ?>

                <div class="clr"></div>
            </div>
        </div>
        </div>
    </section>
<?php endif; ?>
