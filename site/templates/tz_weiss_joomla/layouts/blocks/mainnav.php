<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

//$this -> loadBlock('mainnav_canvas');
?>
<!-- MAIN NAVIGATION -->
<nav id="plazart-mainnav" class="wrap plazart-mainnav navbar-collapse-fixed-top">
    <div class="container navbar">
        <div class="navbar-inner">
            <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <i class="fa fa-bars"></i>
            </button>
            <div class="nav-collapse collapse<?php echo $this->getParam('navigation_collapse_showsub', 1) ? ' always-show' : '' ?>">
                <?php
                if($this -> getParam('theme') == 'canvas'):
                    if($logo = $this -> getParam('logo_image')):
                ?>
                    <!--            <a href="--><?php //echo JUri::root(true);?><!--">-->
                    <img class="tz-logo" src="<?php echo $logo;?>" alt="">
                    <!--            </a>-->
                <?php endif;
                endif;
                ?>
                <?php if ($this->getParam('navigation_type') == 'megamenu') : ?>
                    <?php $this->megamenu($this->getParam('mm_type', 'mainmenu')) ?>
                <?php else : ?>
                    <jdoc:include type="modules" name="<?php $this->_p('mainnav') ?>" style="raw" />
                <?php endif ?>
            </div>
        </div>
    </div>
</nav>
<!-- //MAIN NAVIGATION -->