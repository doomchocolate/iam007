<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

?>

<div class="joms-stream-content">
    <p><i class="joms-icon-users"></i>
    <?php echo CLikesHelper::generateHTML($act, $likedContent) ?></p>
    <?php if ($likedContent !== null) { ?>
        <div class="liked-content">
            <a href="<?php echo $likedContent->url_link; ?>"><img src="<?php echo $likedContent->thumb; ?>" /></a>
        </div>
    <?php } ?>
</div>
