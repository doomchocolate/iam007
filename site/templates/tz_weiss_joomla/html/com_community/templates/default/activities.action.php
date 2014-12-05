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
<div class="stream-actions">
	<i class="stream-icon joms-icon-user"></i>
	<?php echo $act->created; ?>
	<!-- if no one likes yet, then show: -->
	$this->load('activities.actions');
	<!-- Show if it is explicitly allowed: -->
	<div class="clr"></div>
</div>