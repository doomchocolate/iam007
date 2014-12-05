<?php
// No direct access.
defined('_JEXEC') or die;
$app = JFactory::getApplication();
?>
<section id="tz-main">
    <section class="tz-main-body tz-border-bottom tz-border-shadow">
        <div class="container-fluid">
        <div class="tz-inner">
        <?php if($this->API->modules('top')) : ?>
        <section id="tz-top" class="row-fluid">
            <jdoc:include type="modules" name="top" modpos="top" modnum="<?php echo $this->API->modules('top'); ?>" modamount="4" style="tz_style" />
        </section>
        <?php endif; ?>


        <section class="tz-content-wrap row-fluid">
           <?php  if(count($app->getMessageQueue())) :
            ?>
            <div class="container-fluid tz-message">
                <jdoc:include type="message" />
            </div>
            <?php endif; ?>
            <?php  if ($this->API->modules('left')): ?>
            <aside id="sidebar-left" class="span<?php echo $this->API->get('left-sidebar', 3); ?> left-sidebar pull-left">
                <div class="sidebar-nav">
                    <jdoc:include type="modules" name="left" style="tz_style" />
                </div>
            </aside>
            <?php endif; ?>
            <section id="tz-content" class="span<?php echo $this->API->get('mainbody_width', 12);?>">
                <?php if($this->API->modules('breadcrumb')) : ?>
                <section id="tz-breadcrumb">
                    <jdoc:include type="modules" name="breadcrumb" style="none" />
                </section>
                <?php endif; ?>
                <?php if($this->API->modules('mass-top')) : ?>
                <section id="tz-mass-top">
                    <jdoc:include type="modules" name="mass-top" style="tz_style" />
                </section>
                <?php endif; ?>

                <?php if($this->isFrontpage() && $this->API->modules('mainbody')) : ?>
                <section id="tz-mainbody">
                    <jdoc:include type="modules" name="mainbody" style="tz_style" />
                </section>
                <?php else : ?>
                <section id="tz-component">
                    <jdoc:include type="component" />
                </section>
                <?php endif; ?>

                <?php if($this->API->modules('mass-bottom')) : ?>
                <section id="tz-mass-bottom">
                    <jdoc:include type="modules" name="mass-bottom" style="tz_style" />
                </section>
                <?php endif; ?>
            </section>

            <?php if ($this->API->modules('right')): ?>
            <aside id="right-sidebar" class="span<?php echo $this->API->get('right-sidebar', 3); ?> right-sidebar">

                <div class="sidebar-nav">
                    <jdoc:include type="modules" name="right" style="tz_style" />
                </div>
            </aside>
            <?php endif; ?>
            <div class="clr"></div>
        </section>

        <?php if($this->API->modules('mass-bottom')) : ?>
        <section id="tz-bottom">
            <jdoc:include type="modules" name="mass-bottom" modpos="bottom" modnum="<?php echo $this->API->modules('mass-bottom'); ?>" style="tz_style" />
        </section>
        <?php endif; ?>
        </div>
        </div>
    </section>
</section>