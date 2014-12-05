<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') OR DIE();

$my = CFactory::getUser();
?>

<?php $this->renderModules( 'js_noaccess_top' ); ?>

<?php if( isset( $notice ) && !empty( $notice ) ){ ?>
<div class="cAlert alert-error">
	<?php echo $notice;?>
</div>
<?php } ?>

<?php if($my->id == 0) { ?>
<div class="cAlert alert-error">
	<?php echo JText::sprintf('COM_COMMUNITY_NOTICE_NO_ACCESS' , CRoute::_('index.php?option=com_community&view=frontpage') , CRoute::_('index.php?option=com_community&view=register') );?>
</div>
<?php } ?>

<?php $this->renderModules( 'js_noaccess_bottom' ); ?>