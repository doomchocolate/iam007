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
if(!empty($this->filters)){
	$count=0;
	$filterActivated=false;
	$widthPercent=(100/$this->maxColumn)-1;
	$widthPercent=round($widthPercent);
	static $i = 0;
	$i++;
	$filters = array();
	$url = hikashop_currentURL();
	if(!empty($this->params) && $this->params->get('module') == 'mod_hikashop_filter' && ($this->params->get('force_redirect',0) || (empty($this->currentId) && (JRequest::getVar('option','')!='com_hikashop'|| !in_array(JRequest::getVar('ctrl','product'),array('product','category')) ||JRequest::getVar('task','listing')!='listing')))){
		$menuClass = hikashop_get('class.menus');
		$menuData = $menuClass->get($this->params->get('itemid',0));
		$type = 'category';
		if(@$menuData->hikashop_params['content_type']=='product'){
			$type = 'product';
		}
		$url = hikashop_completeLink($type.'&task=listing&Itemid='.$this->params->get('itemid',0));
	}


	foreach($this->filters as $filter){
		if((empty($this->displayedFilters) || in_array($filter->filter_namekey,$this->displayedFilters)) && ($this->filterClass->cleanFilter($filter))){
			$filters[]=$filter;
		}
		$selected[]=$this->filterTypeClass->display($filter, '', $this);
	}

	foreach($selected as $data){
		if(!empty($data)) $filterActivated=true;
	}

	if(!$filterActivated && empty($this->rows) && $this->params->get('module') != 'mod_hikashop_filter') return;

	if(!count($filters)) return; ?>

	<div class="hikashop_filter_main_div hikashop_filter_main_div_<?php $this->params->get('main_div_name'); ?>">
			<?php
		$datas = array();
		if(isset($this->listingQuery)){
			$html=array();
			$datas=$this->filterClass->getProductList($this, $filters);
		}

		foreach($filters as $key => $filter){
			$html[$key]=$this->filterClass->displayFilter($filter, $this->params->get('main_div_name'), $this, $datas);
		}

		if($this->displayFieldset){ ?>
			<fieldset class="hikashop_filter_fieldset">
				<legend><?php echo JText::_('FILTERS'); ?></legend>
		<?php } ?>

		<form action="<?php echo $url; ?>" method="post" name="<?php echo 'hikashop_filter_form_' . $this->params->get('main_div_name'); ?>" enctype="multipart/form-data">
		<?php while($count<$this->maxFilter+1){
			$height='';
			if(!empty($filters[$count]->filter_height)){
				$height='min-height:'.$filters[$count]->filter_height.'px;';
			}else if(!empty($this->heightConfig)){
				$height='min-height:'.$this->heightConfig.'px;';
			}
			if(!empty($html[$count])){
				 ?>
				<div class="hikashop_filter_main hikashop_filter_main_<?php echo $filters[$count]->filter_namekey; ?>" style="<?php echo $height; ?> float:left; width:<?php echo $widthPercent*$filters[$count]->filter_options['column_width']?>%;" >
					<?php //echo $this->filterClass->displayFilter($this->filters[$count], $this->params->get('main_div_name'), $this);
						echo '<div class="hikashop_filter_'.$filters[$count]->filter_namekey.'">'.$html[$count].'</div>';
					?>
				</div>
				<?php
			}
			$count++;
		}
		if($this->buttonPosition=='inside'){
			if($this->showButton ){
				echo '<div class="hikashop_filter_button_inside" style="float:left; margin-right:10px;">';
				echo $this->cart->displayButton(JText::_('FILTER'),'filter',$this->params,$url,'document.getElementById(\'hikashop_filtered_'.$this->params->get('main_div_name').'\').value=\'1\';document.forms[\'hikashop_filter_form_'. $this->params->get('main_div_name').'\'].submit(); return false;','id="hikashop_filter_button_'. $this->params->get('main_div_name').'"');
				echo '</div>';
			}
			if($this->showResetButton ){
				echo '<div class="hikashop_reset_button_inside" style="float:left;">';
				echo $this->cart->displayButton(JText::_('RESET'),'reset_filter',$this->params,$url,'document.getElementById(\'hikashop_reseted_'.$this->params->get('main_div_name').'\').value=\'1\';document.forms[\'hikashop_filter_form_'. $this->params->get('main_div_name').'\'].submit(); return false;','id="hikashop_reset_button_'. $this->params->get('main_div_name').'"');
				echo '</div>';
			}
		}else{
			echo '<div class="hikashop_filter_button_inside" style="display:none">';
			echo $this->cart->displayButton(JText::_('FILTER'),'filter',$this->params,$url,'document.getElementById(\'hikashop_filtered_'.$this->params->get('main_div_name').'\').value=\'1\';document.forms[\'hikashop_filter_form_'. $this->params->get('main_div_name').'\'].submit(); return false;','id="hikashop_filter_button_'. $this->params->get('main_div_name').'_inside"');
			echo '</div>';
		} ?>
		<input type="hidden" name="return_url" value="<?php echo $url;?>"/>
		<input type="hidden" name="filtered" id="hikashop_filtered_<?php echo $this->params->get('main_div_name');?>" value="1" />
		<input type="hidden" name="reseted" id="hikashop_reseted_<?php echo $this->params->get('main_div_name');?>" value="0" />
	</form>
	<?php
	if($this->displayFieldset){ ?>
			</fieldset>
	<?php }
	if($this->buttonPosition!='inside'){
		$style='style="margin-right:10px;"';
		if($this->buttonPosition=='right'){ $style='style="float:right; margin-left:10px;"'; }
		if($this->showButton){
			echo '<span class="hikashop_filter_button_outside" '.$style.'>';
			echo $this->cart->displayButton(JText::_('FILTER'),'filter',$this->params,$url,'document.getElementById(\'hikashop_filtered_'.$this->params->get('main_div_name').'\').value=\'1\';document.forms[\'hikashop_filter_form_'. $this->params->get('main_div_name').'\'].submit(); return false;','id="hikashop_filter_button_'. $this->params->get('main_div_name').'"');
			echo '</span>';
		}
		if($this->showResetButton){
			echo '<span class="hikashop_reset_button_outside" '.$style.' >';
			echo $this->cart->displayButton(JText::_('RESET'),'reset_filter',$this->params,$url,'document.getElementById(\'hikashop_reseted_'.$this->params->get('main_div_name').'\').value=\'1\';document.forms[\'hikashop_filter_form_'. $this->params->get('main_div_name').'\'].submit(); return false;','id="hikashop_reset_button_'. $this->params->get('main_div_name').'"');
			echo '</span>';
		}
	} ?>
	</div>
<?php } ?>
