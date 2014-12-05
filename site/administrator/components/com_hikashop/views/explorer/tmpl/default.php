<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div style="border-top: 1px solid rgb(204, 204, 204); border-bottom: 1px solid rgb(204, 204, 204); background: rgb(221, 225, 230) none repeat scroll 0% 0%; -moz-background-clip: border; -moz-background-origin: padding; -moz-background-inline-policy: continuous; font-weight: bold;margin-bottom:1px"><?php echo JText::_( 'EXPLORER' ); ?></div>

<script type="text/javascript">
	fpath = '<?php echo HIKASHOP_IMAGES; ?>folder.gif';
	d = new dTree('d');
	d.config.closeSameLevel = true; 
	d.icon.root = '<?php echo HIKASHOP_IMAGES . 'icons/icon-16-zone.png'; ?>',
	d.icon.folder = '<?php echo HIKASHOP_IMAGES; ?>folder.gif',
	d.icon.folderOpen = '<?php echo HIKASHOP_IMAGES; ?>folderopen.gif',
	d.icon.node = '<?php echo HIKASHOP_IMAGES; ?>page.gif',
	d.icon.empty = '<?php echo HIKASHOP_IMAGES; ?>empty.gif',
	d.icon.line = '<?php echo HIKASHOP_IMAGES; ?>line.gif',
	d.icon.join = '<?php echo HIKASHOP_IMAGES; ?>join.gif',
	d.icon.joinBottom = '<?php echo HIKASHOP_IMAGES; ?>joinbottom.gif',
	d.icon.plus = '<?php echo HIKASHOP_IMAGES; ?>plus.gif',
	d.icon.plusBottom = '<?php echo HIKASHOP_IMAGES; ?>plusbottom.gif',
	d.icon.minus = '<?php echo HIKASHOP_IMAGES; ?>minus.gif',
	d.icon.minusBottom = '<?php echo HIKASHOP_IMAGES; ?>minusbottom.gif',
	d.icon.nlPlus = '<?php echo HIKASHOP_IMAGES; ?>nolines_plus.gif',
	d.icon.nlMinus = '<?php echo HIKASHOP_IMAGES; ?>nolines_minus.gif'
	<?php
	foreach( $this->elements AS $row ) {
			echo "\nd.add(".$row->category_id.",".$row->category_parent_id.",'".addslashes(htmlspecialchars($row->category_name, ENT_QUOTES ))."',pp(".$row->category_id."),'','',fpath);";
	}
	?>
	document.write(d);
	d.closeAll();
	d.openTo(<?php echo $this->defaultId; ?>, true);

	function pp(cid) {
			<?php
			$control = JRequest::getCmd('control');
			if(!empty($control)){
			$control='&control='.$control;
			}?>
			return '<?php echo hikashop_completeLink($this->task.'&type='.$this->type.$control,$this->popup)?>&filter_id='+cid;
	}
	//-->
</script>
