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

<ul class="cSubmenu-Search cResetList">
	<li>
		<form class="cForm" name="jsform-groups-search" method="get" action="<?php echo $url; ?>">

			<div class="input-append reset-gap">
			  <input type="text" class="input-small" name="search" value="">
			  <input type="submit" value="<?php echo JText::_('COM_COMMUNITY_SEARCH'); ?>" class="btn btn-primary">
			</div>
			<?php echo JHTML::_( 'form.token' ) ?>
			<input type="hidden" value="com_community" name="option" />
			<input type="hidden" value="groups" name="view" />
			<input type="hidden" value="search" name="task" />
			<input type="hidden" value="<?php echo CRoute::getItemId();?>" name="Itemid" />
		</form>
	</li>
</ul>