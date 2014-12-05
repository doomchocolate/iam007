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
		<form name="jsform-videos-search" method="get" action="<?php echo $url; ?>" class="clearfix">

			<div class="input-append">
			  <input type="text" class="input text" name="search-text" value="" />
				<input class="btn btn-primary" type="submit" value="<?php echo JText::_('COM_COMMUNITY_SEARCH'); ?>">
			</div>

			<?php echo JHTML::_( 'form.token' ) ?>
			<input type="hidden" name="option" value="com_community" />
			<input type="hidden" name="view" value="videos" />
			<input type="hidden" name="task" value="search" />
			<input type="hidden" name="Itemid" value="<?php echo CRoute::getItemId();?>" />
		</form>
	</li>
</ul>