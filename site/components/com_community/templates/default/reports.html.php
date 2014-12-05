<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die( 'Unauthorized Access');
?>
<div id="report-this" class="cPageReport page-action">
	<a href="javascript:void(0);" onclick="joms.report.emptyMessage = '<?php echo JText::_('COM_COMMUNITY_REPORT_MESSAGE_CANNOT_BE_EMPTY'); ?>';joms.report.showWindow('<?php echo $reportFunc;?>','<?php echo $argsData;?>');">
		<i class="joms-icon-warning-sign"></i>
		<span><?php echo $reportText;?></span>
	</a>
</div>