<?php
defined('_JEXEC') or die('Restricted access');
if(empty($this->row->has_options) && ($this->row->product_quantity==-1 || $this->row->product_quantity>0)&& !$this->config->get('catalogue') && ($this->config->get('display_add_to_cart_for_free_products') || !empty($this->row->prices))){
    $itemFields = $this->fieldsClass->getFields('frontcomp',$this->row,'item','checkout&task=state');
    if(!empty($itemFields) && !$this->params->get('display_custom_item_fields',0)){
        $this->row->has_options = true;
        $itemFields = array();
    }
    $null=array();
    $this->fieldsClass->addJS($null,$null,$null);
    $this->fieldsClass->jsToggle($itemFields,$this->row,0);
    $extraFields = array('item'=>&$itemFields);
    $requiredFields = array();
    $validMessages = array();
    $values = array('item'=>$this->row);
    $this->fieldsClass->checkFieldsForJS($extraFields,$requiredFields,$validMessages,$values);
    $this->fieldsClass->addJS($requiredFields,$validMessages,array('item'));

    if($this->params->get('display_custom_item_fields',0) && !empty($itemFields)){
        ?>
        <!-- CUSTOM ITEM FIELDS -->
        <div id="hikashop_product_custom_item_info_for_product_<?php echo $this->row->product_id; ?>" class="hikashop_product_custom_item_info hikashop_product_listing_custom_item">
            <table class="hikashop_product_custom_item_info_table hikashop_product_listing_custom_item_table" width="100%">
                <?php
                foreach($itemFields as $fieldName => $oneExtraField) {
                    $itemData = JRequest::getString('item_data_'.$fieldName,$this->row->$fieldName);  ?>
                    <tr id="hikashop_item_<?php echo $oneExtraField->field_namekey; ?>" class="hikashop_item_<?php echo $oneExtraField->field_namekey;?>_line">
                        <td class="key">
					<span id="hikashop_product_custom_item_name_<?php echo $oneExtraField->field_id;?>_for_product_<?php echo $this->row->product_id; ?>" class="hikashop_product_custom_item_name">
						<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
					</span>
                        </td>
                        <td>
					<span id="hikashop_product_custom_item_value_<?php echo $oneExtraField->field_id;?>_for_product_<?php echo $this->row->product_id; ?>" class="hikashop_product_custom_item_value"><?php
                        $onWhat='onchange';
                        if($oneExtraField->field_type=='radio')
                            $onWhat='onclick';
                        $oneExtraField->product_id = $this->row->product_id;
                        $this->fieldsClass->prefix='product_'.$this->row->product_id.'_';
                        echo $this->fieldsClass->display($oneExtraField,$itemData,'data[item]['.$oneExtraField->field_namekey.']',false,' '.$onWhat.'="if (\'function\' == typeof window.hikashopToggleFields) { hikashopToggleFields(this.value,\''.$fieldName.'\',\'item\',0); }"');
                        ?></span>
                        </td>
                    </tr>
                <?php }
                $this->fieldsClass->prefix=''; ?>
            </table>
        </div>
        <!-- EO CUSTOM ITEM FIELDS -->
    <?php }
}