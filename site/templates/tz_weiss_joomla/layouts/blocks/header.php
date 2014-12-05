<?php
// No direct access.
defined('_JEXEC') or die;
$app = JFactory::getApplication();
?>

	<header class="tz-header tz-border-bottom tz-border-shadow">
        <div class="container-fluid">
        <div class="tz-inner">
            <h1 class="tz-logo pull-left">
                <?php $this->loadBlock('logo'); ?>
            </h1>
            <?php $this->loadBlock('mainnav'); ?>
            <?php if ($this->API->modules('search')): ?>
            <div class="sidebar-search pull-right hidden-phone" >
                <jdoc:include type="modules" name="search" style="tz_style" />
            </div>
            <?php endif; ?>
            <div class="clr"></div>

        </div>
        </div>
    </header>

	<?php if($this->countModules('header')) : ?>
<section id="tz-header">
    <div class="container-fluid">
        <jdoc:include type="modules" name="header" style="none" />
    </div>
</section>

<?php endif; ?>