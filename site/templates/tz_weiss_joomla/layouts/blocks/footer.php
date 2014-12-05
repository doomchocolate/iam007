<?php
// No direct access.
defined('_JEXEC') or die;
?>
<footer id="tz-footer" class="tz-footer">

    <div class="container-fluid">
        <div class="tz-inner">
            <?php if ($this->countModules('bottom1 + bottom2 + bottom3 + bottom4 + bottom5 + bottom6')): ?>
            <div class="tz-bottom6 ">
                <div class="row-fluid">
                    <?php if ($this->countModules('bottom1')): ?>
                    <div class="span2 tz-bottom">
                        <jdoc:include type="modules" name="bottom1" style="tz_style" />
                    </div>
                    <?php endif; ?>
                    <?php if ($this->countModules('bottom2')): ?>
                    <div class="span2 tz-bottom">
                        <jdoc:include type="modules" name="bottom2" style="tz_style" />
                    </div>
                    <?php endif; ?>
                    <?php if ($this->countModules('bottom3')): ?>
                    <div class="span2 tz-bottom">
                        <jdoc:include type="modules" name="bottom3" style="tz_style" />
                    </div>
                    <?php endif; ?>
                    <?php if ($this->countModules('bottom4')): ?>
                    <div class="span2 tz-bottom">
                        <jdoc:include type="modules" name="bottom4" style="tz_style" />
                    </div>
                    <?php endif; ?>
                    <?php if ($this->countModules('bottom5')): ?>
                    <div class="span2 tz-bottom">
                        <jdoc:include type="modules" name="bottom5" style="tz_style" />
                    </div>
                    <?php endif; ?>
                    <?php if ($this->countModules('bottom6')): ?>
                    <div class="span2 tz-bottom">
                        <jdoc:include type="modules" name="bottom6" style="tz_style" />
                    </div>
                    <?php endif; ?>
                    <div class="clr"></div>
                </div>
            </div>
            <?php endif; ?>
            </div>
        </div>
        <div class="footer-bottom">
        <div class="container-fluid">
            <div class="tz-inner">
        <?php if($this->countModules('footer')) : ?>
        <jdoc:include type="modules" name="footer" style="none" />
        <?php endif; ?>

        <p class="pull-left tz-copyrights">
            <?php if($this->API->get('copyrights', '') == '') : ?>
            &copy; Blank Plazart - <a href="http://www.templaza.com" title="Free Joomla! 3.0 Template">Free Joomla! 3.0 Template</a> <?php echo date('Y');?>
            <?php else : ?>
            <?php echo $this->API->get('copyrights', ''); ?>
            <?php endif; ?>
        </p>

        <?php if($this->API->get('framework_logo', 1)) : ?>
        <a rel="nofollow" href="http://www.templaza.com" id="tz-framework-logo">Plazart Framework</a>
        <?php endif; ?>
                <div class="clr"></div>

<!--        <p class="tz-disclaimer">TemPlaza is not affiliated with or endorsed by Open Source Matters or the Joomla! Project.<br />-->
<!--            The Joomla! logo is used under a limited license granted by Open Source Matters the trademark holder in the United States and other countries.<br />Icons from <a href="http://glyphicons.com">Glyphicons Free</a>, licensed under <a href="http://creativecommons.org/licenses/by/3.0/">CC BY 3.0</a>.</p>-->
<!--        </div>-->
    </div>
    </div>
    </div>
</footer>