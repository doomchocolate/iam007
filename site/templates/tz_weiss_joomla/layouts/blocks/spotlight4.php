<?php
// No direct access.
defined('_JEXEC') or die;
$app = JFactory::getApplication();
?>
<?php if ($this->API->modules('user1 + user2 + user3 + user4')): ?>
    <section class="tz-spotlight4 tz-border-bottom tz-border-shadow">
        <div class="container-fluid">
        <div class="tz-inner">
            <div class="row-fluid">
                <?php if ($this->API->modules('user1')): ?>
                <div class="span3 tz-spotlight">
                <jdoc:include type="modules" name="user1" style="tz_style" />
                </div>
                <?php endif; ?>
                <?php if ($this->API->modules('user2')): ?>
                <div class="span3 tz-spotlight">
                <jdoc:include type="modules" name="user2" style="tz_style" />
                </div>
                <?php endif; ?>
                <?php if ($this->API->modules('user3')): ?>
                <div class="span3 tz-spotlight">
                <jdoc:include type="modules" name="user3" style="tz_style" />
                </div>
                <?php endif; ?>
                <?php if ($this->API->modules('user4')): ?>
                <div class="span3 tz-spotlight">
                <jdoc:include type="modules" name="user4" style="tz_style" />
                </div>
                <?php endif; ?>
                <div class="clr"></div>
            </div>
        </div>
        </div>
    </section>
<?php endif; ?>
