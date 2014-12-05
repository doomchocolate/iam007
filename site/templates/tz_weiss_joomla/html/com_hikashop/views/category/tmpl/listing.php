<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
ob_start();
if(version_compare(JVERSION,'1.6','<')){
	$title = 'show_page_title';
}else{
	$title = 'show_page_heading';
}
$titleType='h1';
if($this->module){
	$title = 'showtitle';
	$titleType='h2';
}

if($this->params->get($title) && JRequest::getVar('hikashop_front_end_main',0)){
	if($this->module){
		$heading = $this->params->get('title');
	}else{
		$heading = $this->params->get('page_title');
		if($this->params->get('page_heading')){
			$heading = $this->params->get('page_heading');
		}
	}
	?>
	<<?php echo $titleType; ?>>
	<?php echo $heading; ?>
	</<?php echo $titleType; ?>>
	<?php
}
if(!$this->module){
	if(isset($this->element->category_canonical) && !empty($this->element->category_canonical)){
		$canonicalUrl = hikashop_cleanURL($this->element->category_canonical);

		$doc = JFactory::getDocument();
		$doc->addCustomTag( '<link rel="canonical" href="'.$canonicalUrl.'" />' );
	}
	if(($this->params->get('show_image') && !empty($this->element->file_path))|| ($this->params->get('show_description')&&!empty($this->element->category_description))){
		?>
		<div class="hikashop_category_description">
		<?php
		if($this->params->get('show_image') && !empty($this->element->file_path)){
			jimport('joomla.filesystem.file');
			if(JFile::exists($this->image->getPath($this->element->file_path,false))){
			?>
			<img src="<?php echo $this->image->getPath($this->element->file_path); ?>" class="hikashop_category_image"/>
			<?php
			}
		}
		if($this->params->get('show_description',1)&&!empty($this->element->category_description)){
			?>
			<div class="hikashop_category_description_content">
			<?php echo JHTML::_('content.prepare',$this->element->category_description); ?>
			</div>
			<?php
		}
		?>
		</div>
	<?php
	}
	if(!empty($this->fields)){ ?>
		<div id="hikashop_category_custom_info_main" class="hikashop_category_custom_info_main">
			<h4><?php echo JText::_('CATEGORY_ADDITIONAL_INFORMATION');?></h4>
			<table width="100%">
			<?php
			$this->fieldsClass->prefix = '';
			foreach($this->fields as $fieldName => $oneExtraField) {
				if(!empty($this->element->$fieldName)){ ?>
				<tr class="hikashop_category_custom_<?php echo $oneExtraField->field_namekey;?>_line">
					<td class="key">
						<span id="hikashop_category_custom_name_<?php echo $oneExtraField->field_id;?>" class="hikashop_category_custom_name">
							<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
						</span>
					</td>
					<td>
						<span id="hikashop_category_custom_value_<?php echo $oneExtraField->field_id;?>" class="hikashop_category_custom_value">
							<?php echo $this->fieldsClass->show($oneExtraField,$this->element->$fieldName); ?>
						</span>
					</td>
				</tr>
			<?php }
				}?>
			</table>
		</div>
<?php }
}

$layout_type = $this->params->get('layout_type');
if(empty($layout_type) || $layout_type=='table') $layout_type = 'div';
$html = $this->loadTemplate($layout_type);
if(!empty($html)) echo '<div class="hikashop_subcategories_listing">'.$html.'</div>';


if(!$this->module){
	if(!empty($this->modules)){
		$html = '';
		jimport('joomla.application.module.helper');
		foreach($this->modules as $module){
			$html .= JModuleHelper::renderModule($module);
		}
		if(!empty($html)){
			echo '<div class="hikashop_submodules" style="clear:both">'.$html.'</div>';
		}
	}
}
$html = ob_get_clean();
if(!empty($html)){ ?>
	<div id="<?php echo $this->params->get('main_div_name');?>" class="hikashop_category_information hikashop_categories_listing_main">
		<?php echo $html; ?>
	</div>
<?php }	?>
