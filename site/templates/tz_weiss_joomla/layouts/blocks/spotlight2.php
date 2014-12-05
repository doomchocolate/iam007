<?php
// No direct access.
defined('_JEXEC') or die;
$app = JFactory::getApplication();
?>
<?php if ($this->API->modules('user5 + user6')): ?>
    <section class="tz-spotlight2 tz-border-bottom tz-border-shadow">
        <div class="container-fluid">
        <div class="tz-inner">
            <div class="row-fluid">
                <?php if ($this->API->modules('user5')): ?>
                <div class="span6 tz-spotlight">
                <jdoc:include type="modules" name="user5" style="tz_style" />
                </div>
                <?php endif; ?>
                <?php if ($this->API->modules('user6')): ?>
                <div class="span6 tz-spotlight">
                <jdoc:include type="modules" name="user6" style="tz_style" />
                </div>
                <?php endif; ?>
                <div class="clr"></div>
            </div>
        </div>
        </div>
    </section>
<?php endif; ?>
