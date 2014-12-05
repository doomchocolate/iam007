<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=com_hikashop&amp;ctrl=field" method="post"  name="adminForm" id="adminForm" >
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-field">
	<table style="width:100%">
		<tr>
			<td valign="top" width="50%">
<?php } else { ?>
<div id="page-field" class="row-fluid">
	<div class="span6">
<?php } ?>
	<table class="paramlist admintable table">
		<tr>
			<td class="key"><?php
				echo JText::_( 'FIELD_LABEL' );
			?></td>
			<td>
				<input type="text" name="data[field][field_realname]" id="name" class="inputbox" size="40" value="<?php echo $this->escape(@$this->field->field_realname); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key"><?php
				echo JText::_( 'FIELD_TABLE' );
			?></td>
			<td><?php
				if(hikashop_level(1) && empty($this->field->field_id)){
					echo $this->tabletype->display('data[field][field_table]',$this->field->field_table,true, 'onchange="setVisible(this.value);"');
				}else{
					echo $this->field->field_table.'<input type="hidden" name="data[field][field_table]" value="'.$this->field->field_table.'" />';
				}
			?></td>
		</tr>
		<tr class="columnname">
			<td class="key"><?php
				echo JText::_( 'FIELD_COLUMN' );
			?></td>
			<td>
			<?php if(empty($this->field->field_id)){?>
				<input type="text" name="data[field][field_namekey]" id="namekey" class="inputbox" size="40" value="" />
			<?php }else { echo $this->field->field_namekey; } ?>
			</td>
		</tr>
		<tr>
			<td class="key"><?php
				echo JText::_( 'FIELD_TYPE' );
			?></td>
			<td><?php
				if(!empty($this->field->field_type) && $this->field->field_type=='customtext'){
					$this->fieldtype->addJS();
					echo $this->field->field_type.'<input type="hidden" id="fieldtype" name="data[field][field_type]" value="'.$this->field->field_type.'" />';
				}else{
					echo $this->fieldtype->display('data[field][field_type]',@$this->field->field_type,@$this->field->field_table);
				}
			?></td>
		</tr>
		<tr id="fieldopt_required_0">
			<td class="key"><?php
				echo JText::_( 'REQUIRED' );
			?></td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "data[field][field_required]" , '',@$this->field->field_required); ?>
			</td>
		</tr>
		<tr id="fieldopt_required_1">
			<td class="key">
				<?php echo JText::_( 'FIELD_ERROR' ); ?>
			</td>
			<td>
				<input type="text" id="errormessage" size="80" name="field_options[errormessage]" value="<?php echo $this->escape(@$this->field->field_options['errormessage']); ?>"/>
			</td>
		</tr>
		<tr id="fieldopt_default">
			<td class="key">
				<?php echo JText::_( 'FIELD_DEFAULT' ); ?>
			</td>
			<td><?php
				echo $this->fieldsClass->display($this->field,@$this->field->field_default,'data[field][field_default]',false,'',true,$this->allFields);
			?></td>
		</tr>
		<tr id="fieldopt_multivalues">
			<td class="key" valign="top">
				<?php echo JText::_( 'FIELD_VALUES' ); ?>
			</td>
			<td>
				<table id="hikashop_field_values_table" class="hikaspanleft table table-striped table-hover"><tbody id="tablevalues">
				<tr>
					<td><?php echo JText::_('FIELD_VALUE')?></td>
					<td><?php echo JText::_('FIELD_TITLE'); ?></td>
					<td><?php echo JText::_('FIELD_DISABLED'); ?></td>
				</tr>
<?php
	if(!empty($this->field->field_value) && is_array($this->field->field_value) && $this->field->field_type!='zone'){
		foreach($this->field->field_value as $title => $value){
			$no_selected = 'selected="selected"';
			$yes_selected = '';
			if((int)$value->disabled){
				$no_selected = '';
				$yes_selected = 'selected="selected"';
			}
?>
				<tr>
					<td><input type="text" name="field_values[title][]" value="<?php echo $this->escape($title); ?>" /></td>
					<td><input type="text" name="field_values[value][]" value="<?php echo $this->escape($value->value); ?>" /></td>
					<td><select name="field_values[disabled][]" class="inputbox">
						<option <?php echo $no_selected; ?> value="0"><?php echo JText::_('HIKASHOP_NO'); ?></option>
						<option <?php echo $yes_selected; ?> value="1"><?php echo JText::_('HIKASHOP_YES'); ?></option>
					</select></td>
				</tr>
<?php } }?>
				<tr>
					<td><input type="text" name="field_values[title][]" value="" /></td>
					<td><input type="text" name="field_values[value][]" value="" /></td>
					<td>
						<select name="field_values[disabled][]" class="inputbox">
							<option selected="selected" value="0"><?php echo JText::_('HIKASHOP_NO'); ?></option>
							<option value="1"><?php echo JText::_('HIKASHOP_YES'); ?></option>
						</select>
					</td>
				</tr>
				</tbody></table>
				<a onclick="addLine();return false;" href='#' title="<?php echo $this->escape(JText::_('FIELD_ADDVALUE')); ?>"><?php echo JText::_('FIELD_ADDVALUE'); ?></a>
			</td>
		</tr>
		<tr id="fieldopt_cols">
			<td class="key">
				<?php echo JText::_( 'FIELD_COLUMNS' ); ?>
			</td>
			<td>
				<input type="text"  size="10" name="field_options[cols]" id="cols" class="inputbox" value="<?php echo $this->escape(@$this->field->field_options['cols']); ?>"/>
			</td>
		</tr>
		<tr id="fieldopt_filtering">
			<td class="key">
				<?php echo JText::_( 'INPUT_FILTERING' ); ?>
			</td>
			<td>
				<?php
				if(!isset($this->field->field_options['filtering'])) $this->field->field_options['filtering'] = 1;
				echo JHTML::_('hikaselect.booleanlist', "field_options[filtering]" , '',$this->field->field_options['filtering']); ?>
			</td>
		</tr>
		<tr id="fieldopt_maxlength">
			<td class="key">
				<?php echo JText::_( 'MAXLENGTH' ); ?>
			</td>
			<td>
				<input type="text"  size="10" name="field_options[maxlength]" id="cols" class="inputbox" value="<?php echo (int)@$this->field->field_options['maxlength']; ?>"/>
			</td>
		</tr>
		<tr id="fieldopt_rows">
			<td class="key">
				<?php echo JText::_( 'FIELD_ROWS' ); ?>
			</td>
			<td>
				<input type="text"  size="10" name="field_options[rows]" id="rows" class="inputbox" value="<?php echo $this->escape(@$this->field->field_options['rows']); ?>"/>
			</td>
		</tr>
		<tr id="fieldopt_zone">
			<td class="key">
				<?php echo JText::_( 'FIELD_ZONE' ); ?>
			</td>
			<td>
				<?php echo $this->zoneType->display("field_options[zone_type]",@$this->field->field_options['zone_type'],true);?>
			</td>
		</tr>
		<tr id="fieldopt_pleaseselect">
			<td class="key"><?php
				echo JText::_( 'ADD_SELECT_VALUE' );
			?></td>
			<td><?php
				echo JHTML::_('hikaselect.booleanlist', "field_options[pleaseselect]" , '', @$this->field->field_options['pleaseselect']);
			?></td>
		</tr>
		<tr id="fieldopt_size">
			<td class="key">
				<?php echo JText::_( 'FIELD_SIZE' ); ?>
			</td>
			<td>
				<input type="text" id="size" size="10" name="field_options[size]" value="<?php echo $this->escape(@$this->field->field_options['size']); ?>"/>
			</td>
		</tr>
		<tr id="fieldopt_format">
			<td class="key">
				<?php echo JText::_( 'FORMAT' ); ?>
			</td>
			<td>
				<input type="text" id="format" name="field_options[format]" value="<?php echo $this->escape(@$this->field->field_options['format']); ?>"/>
			</td>
		</tr>
		<tr id="fieldopt_customtext">
			<td class="key">
				<?php echo JText::_( 'CUSTOM_TEXT' ); ?>
			</td>
			<td>
				<textarea cols="50" rows="10" name="fieldcustomtext"><?php echo @$this->field->field_options['customtext']; ?></textarea>
			</td>
		</tr>
		<tr id="fieldopt_allow">
			<td class="key">
				<?php echo JText::_( 'ALLOW' ); ?>
			</td>
			<td>
				<?php echo $this->allowType->display("field_options[allow]",@$this->field->field_options['allow']);?>
			</td>
		</tr>
		<tr id="fieldopt_readonly">
			<td class="key">
				<?php echo JText::_( 'READONLY' ); ?>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "field_options[readonly]" , '',@$this->field->field_options['readonly']); ?>
			</td>
		</tr>
<?php
	if(!empty($this->fieldtype->externalOptions)) {
		foreach($this->fieldtype->externalOptions as $key => $extraOption) {
			if(is_numeric($key)) {
				if(is_array($extraOption) && isset($extraOption['name']))
					$key = $extraOption['name'];
				else
					$key = @$extraOption->name;
			}
			if(empty($key) || is_numeric($key))
				continue;

?>		<tr id="fieldopt_<?php echo $key ;?>">
			<td class="key"><?php
				if(is_array($extraOption) && isset($extraOption['text']))
					echo $extraOption['text'];
				else
					echo @$extraOption->text;
			?></td>
			<td><?php
				if((is_array($extraOption) && isset($extraOption['content'])) || isset($extraOption->content)) {
					if(is_array($extraOption))
						echo $extraOption['content'];
					else
						echo $extraOption->content;
				}
				if((is_array($extraOption) && isset($extraOption['obj'])) || isset($extraOption->obj)) {
					$o = is_array($extraOption) ? $extraOption['obj'] : $extraOption->obj;
					if(is_string($o))
						$o = new $o();

					echo $o->show( @$this->field->field_options[$key] );
				}
			?></td>
		</tr>
<?php
		}
	}
?>
	</table>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
			<td valign="top" width="50%">
<?php } else { ?>
	</div>
	<div class="span6">
<?php } ?>
	<table class="paramlist admintable table">
		<tr>
			<td class="key"><?php echo JText::_( 'HIKA_PUBLISHED' ); ?></td>
			<td><?php echo JHTML::_('hikaselect.booleanlist', "data[field][field_published]" , '',@$this->field->field_published); ?></td>
		</tr>
		<tr class="limit_to">
			<td class="key">
					<?php echo JText::_( 'DISPLAY_LIMITED_TO' ); ?>
			</td>
			<td>
				<?php
				if(hikashop_level(2)){
					if(empty($this->field->field_table)){
						echo JText::_( 'SAVE_THE_FIELD_FIRST_BEFORE' );
					}else{
						echo $this->limitParent->display("field_options[limit_to_parent]",@$this->field->field_options['limit_to_parent'],$this->field->field_table,@$this->field->field_options['parent_value'],$this->field);
					}
				}else{
					echo hikashop_getUpgradeLink('business');;
				}
				?>
				<span id="parent_value"></span>
			</td>
		</tr>
<?php if(hikashop_level(2) && $this->field->field_table=='entry'){ ?>
		<tr class="product_link">
			<td class="key">
					<?php echo JText::_( 'CORRESPOND_TO_PRODUCT' ); ?>
			</td>
			<td>
				<span id="product_id" >
					<?php echo (int)@$this->field->field_options['product_id'].' '.@$this->element->product_name; ?>
					<input type="hidden" name="field_options[product_id]" value="<?php echo @$this->field->field_options['product_id']; ?>" />
				</span>
				<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("product&task=selectrelated&select_type=field",true ); ?>">
					<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit"/>
				</a>
				<a href="#" onclick="document.getElementById('product_id').innerHTML='<input type=\'hidden\' name=\'field_options[product_id]\' value=\'0\' />';return false;" >
					<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="delete"/>
				</a>
				<br/>
				<?php echo JText::_( 'FOR_THE_VALUE' ).' '; $this->fieldsClass->suffix='_corresponding'; ?>
				<?php echo $this->fieldsClass->display($this->field,@$this->field->field_options['product_value'],'field_options[product_value]',false,'',true); ?>
			</td>
		</tr>
<?php }?>
	</table>
	<fieldset class="adminform">
		<legend><?php echo JText::_('DISPLAY'); ?></legend>
		<table class="paramlist admintable table">
			<tr>
				<td class="key"><?php echo JText::_( 'DISPLAY_FRONTCOMP' ); ?></td>
				<td><?php echo JHTML::_('hikaselect.booleanlist', "data[field][field_frontcomp]" , '',@$this->field->field_frontcomp); ?></td>
			</tr>
			<tr>
				<td class="key"><?php echo JText::_( 'DISPLAY_BACKEND_FORM' ); ?></td>
				<td><?php echo JHTML::_('hikaselect.booleanlist', "data[field][field_backend]" , '',@$this->field->field_backend); ?></td>
			</tr>
			<tr>
				<td class="key"><?php echo JText::_( 'DISPLAY_BACKEND_LISTING' ); ?></td>
				<td><?php echo JHTML::_('hikaselect.booleanlist', "data[field][field_backend_listing]" , '',@$this->field->field_backend_listing); ?></td>
			</tr>
			<?php
			if(!empty($this->displayOptions)) {
				if(is_string($this->field->field_display)) {
					$fields_display = explode(';', trim($this->field->field_display, ';'));
					$this->field->field_display = new stdClass();
					foreach($fields_display as $f) {
						if(empty($f) || strpos($f, '=') === false)
							continue;
						list($k,$v) = explode('=', $f, 2);
						$this->field->field_display->$k = $v;
					}
				}
				foreach($this->displayOptions as $displayOption) {
					$displayOptionName = '';
					if(is_string($displayOption)) {
						$displayOptionName = $displayOption;
					} else if(!empty($displayOption->name)) {
						$displayOptionName = $displayOption->name;
						$displayOptionTitle = @$displayOption->title;
					} else if(!empty($displayOption['name'])) {
						$displayOptionName = $displayOption['name'];
						$displayOptionTitle = @$displayOption['title'];
					}

					if(empty($displayOptionName))
						continue;

					if(empty($displayOptionTitle))
						$displayOptionTitle = JText::_($displayOptionName);
			?>
			<tr>
				<td class="key"><?php echo $displayOptionTitle; ?></td>
				<td><?php echo JHTML::_('hikaselect.booleanlist', 'field_display['.$displayOptionName.']' , '', @$this->field->field_display->$displayOptionName); ?></td>
			</tr>
			<?php
				}
			}
			?>
		</table>
	</fieldset>
	<?php
		$fieldsetDisplay = 'style="display:none"';
		if($this->field->field_table == "product" || $this->field->field_table == "item" || $this->field->field_table == "category") {
			$fieldsetDisplay = '';
		}
	?>
	<fieldset <?php echo $fieldsetDisplay; ?> style="width:50%;" id="category_field" class="adminform">
		<legend><?php echo JText::_( 'HIKA_CATEGORIES' ); ?></legend>
		<div style="text-align:right;">
			<?php
				echo $this->popup->display(
					'<img src="'.HIKASHOP_IMAGES.'add.png"/>'.JText::_('ADD'),
					'ADD',
					hikashop_completeLink("product&task=selectcategory",true ),
					'category_add_button',
					860, 480, '', '', 'button'
				);
			?>
		</div>
		<br/>
		<table class="adminlist table table-striped table-hover" cellpadding="1" width="100%">
			<thead>
				<tr>
					<th class="title"><?php echo JText::_('HIKA_NAME'); ?></th>
					<th class="title"><?php echo JText::_('HIKA_DELETE'); ?></th>
					<th class="title"><?php echo JText::_('ID'); ?></th>
				</tr>
			</thead>
			<tbody id="category_listing"><?php
			if(!empty($this->categories)) {
				$k = 0;
				for($i = 1, $a = count($this->categories) + 1; $i < $a; $i++) {
					$row =& $this->categories[$i];
					if(!empty($row->category_id)) {
			?>
				<tr id="category_<?php echo $row->category_id;?>">
					<td>
						<div id="category_<?php echo $row->category_id; ?>_id">
						<a href="<?php echo hikashop_completeLink('category&task=edit&cid='.$row->category_id); ?>"><?php echo $row->category_name; ?></a>
					</td>
					<td align="center">
						<a href="#" onclick="return deleteRow('category_div_<?php echo $row->category_id;?>','category[<?php echo $row->category_id;?>]','category_<?php echo $row->category_id; ?>');">
							<img src="../media/com_hikashop/images/delete.png"/>
						</a>
					</td>
					<td width="1%" align="center">
						<?php echo $row->category_id; ?>
						<div id="category_div_<?php echo $row->category_id;?>">
							<input style="width: 50px; background-color:#e8f9db;" type	="hidden" name="category[<?php echo $row->category_id;?>]" id="category[<?php echo $row->category_id;?>]" value="<?php echo $row->category_id;?>"/>
						</div>
					</td>
				</tr>
			<?php
					}
					$k = 1-$k;
				}
			}
			?></tbody>
		</table>
		<br/>
		<table class="paramlist admintable table">
			<tr>
				<td class="key">
					<label for="data[field][field_with_sub_categories]"><?php echo JText::_( 'INCLUDING_SUB_CATEGORIES' ); ?></label>
				</td>
				<td><?php echo JHTML::_('hikaselect.booleanlist', "data[field][field_with_sub_categories]" , '',@$this->field->field_with_sub_categories); ?></td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_('ACCESS_LEVEL'); ?></legend>
		<?php
		if(hikashop_level(2)){
			$acltype = hikashop_get('type.acl');
			echo $acltype->display('field_access',@$this->field->field_access,'field');
		}else{
			echo hikashop_getUpgradeLink('business');;
		} ?>
	</fieldset>
<?php if(!empty($this->field->field_id)) { ?>
	<br/><br/>
	<fieldset class="adminform">
		<legend><?php echo JText::_('PREVIEW'); ?></legend>
		<table class="admintable table">
			<tr>
				<td class="key"><?php $this->fieldsClass->suffix='_preview'; echo $this->fieldsClass->getFieldName($this->field); ?></td>
				<td><?php echo $this->fieldsClass->display($this->field,$this->field->field_default,'data['.$this->field->field_table.']['.$this->field->field_namekey.']',false,'',true,$this->allFields); ?></td>
			</tr>
		</table>
	</fieldset>
<?php } ?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
		</tr>
	</table>
</div>
<?php } else { ?>
	</div>
</div>
<?php } ?>
<?php
	if(hikashop_level(2) && !empty($this->field->field_id) && in_array($this->field->field_type,array('radio','singledropdown','zone'))){
		$this->fieldsClass->chart($this->field->field_table,$this->field);
	}
?>
	<input type="hidden" name="cid[]" value="<?php echo @$this->field->field_id; ?>" />
	<input type="hidden" name="option" value="com_hikashop" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="field" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<div class="clr" style="<?php if(hikashop_level(2) && !empty($this->field->field_id) && in_array($this->field->field_type,array('radio','singledropdown','zone'))){ echo 'height:400px;';} ?>width:100%"></div>
