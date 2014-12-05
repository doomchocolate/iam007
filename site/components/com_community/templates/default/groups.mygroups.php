<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die();
?>

<?php echo $sortings; ?>

<div class="row-fluid">


<?php if(!empty($discussionsHTML)) { ?>

  <div class="span8">
    <!-- ALL MY GROUP LIST -->
    <div class="cMain clrfix">
      <?php echo $groupsHTML; ?>
    </div>
  </div>
  <div class="span4">
    <div class="cSidebar clrfix">
      <div class="clrfix">
        <?php echo $this->view('groups')->modUserGroupPending($my->id); ?>
      </div>
        <?php echo $discussionsHTML; ?>
    </div>
  </div>

<?php } else { ?>
  <div class="span12">
    <!-- ALL MY GROUP LIST -->
    <div class="clrfix">
      <?php echo $groupsHTML; ?>
    </div>
  </div>
<?php } ?>

</div>