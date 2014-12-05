<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
?>

<div class="row-fluid">
	<div class="span18">
		<img src="<?php echo COMMUNITY_ASSETS_URL . '/logo.png'; ?>" style="margin-top:24px;" />
		<div class="label label-info">
			<?php echo JText::sprintf( 'Version: %1$s', $this->version ); ?>
		</div>
	</div>
</div>

<div class="row-fluid">
	<div class="span18">
		<a class="btn btn-primary" href="javascript:void(0);" onclick="azcommunity.checkVersion();">
			<?php echo JText::_('COM_COMMUNITY_ABOUT_CHECK_LATEST_VERSION'); ?>
		</a>
	</div>
</div>