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
class plgHikashopShippingmanual_prices extends JPlugin {

	function plgHikashopShippingmanual_prices(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	function onProductBlocksDisplay(&$product, &$html) {

		if(empty($product->product_id)) {
			$product_id = 0;
		} else {
			$product_id = $product->product_id;
		}

		$db = JFactory::getDBO();
		$query = 'SELECT b.*, a.*, c.currency_symbol FROM ' . hikashop_table('shipping') . ' AS a LEFT JOIN '.
			hikashop_table('shipping_price').' AS b ON a.shipping_id = b.shipping_id AND b.shipping_price_ref_id = '.$product_id.' INNER JOIN '.
			hikashop_table('currency').' AS c ON c.currency_id = a.shipping_currency_id '.
			'WHERE a.shipping_params LIKE '.
			$db->Quote('%s:20:"shipping_per_product";s:1:"1"%') . ' AND (b.shipping_price_ref_id IS NULL OR (b.shipping_price_ref_id = ' . $product_id . ' AND b.shipping_price_ref_type = \'product\')) '.
			'ORDER BY a.shipping_id, b.shipping_price_min_quantity';
		$db->setQuery($query);
		$shippings = $db->loadObjectList();

		if(!empty($shippings)) {
			$currencyHelper = hikashop_get('class.currency');

			$data = '<fieldset class="adminform"><legend>'.JText::_('SHIPPING_PRICES').'</legend>'.
				'<table class="adminlist table table-striped hikashop_product_prices_table" width="100%">'.
				'<thead><tr><th class="title">'.JText::_('HIKA_NAME').'</th><th class="title" width="10px">'.JText::_('MINIMUM_QUANTITY').'</th><th class="title">'.JText::_('PRICE').'</th><th class="title">'.JText::_('FEE').'</th><th class="title">'.JText::_('BLOCKED').'</th><th class="title">'.JText::_('ACTIONS').'</th></thead><tbody>';

			$i = 0;
			$previous_shipping_id = -1;
			foreach($shippings as &$shipping) {
				$shipping->shipping_params = unserialize($shipping->shipping_params);

				$shipping_data = $shipping->shipping_name . ' - ' . $currencyHelper->displayPrices(array($shipping), 'shipping_price', 'shipping_currency_id');
				if(isset($shipping->shipping_params->shipping_percentage) && bccomp($shipping->shipping_params->shipping_percentage,0,3)) {
					$shipping_data .= ' +'.$shipping->shipping_params->shipping_percentage.'%';
				}

				$rest = array();
				if(!empty($shipping->shipping_params->shipping_min_volume)){ $rest[]=JText::_('SHIPPING_MIN_VOLUME').':'.$shipping->shipping_params->shipping_min_volume.$shipping->shipping_params->shipping_size_unit; }
				if(!empty($shipping->shipping_params->shipping_max_volume)){ $rest[]=JText::_('SHIPPING_MAX_VOLUME').':'.$shipping->shipping_params->shipping_max_volume.$shipping->shipping_params->shipping_size_unit; }
				if(!empty($shipping->shipping_params->shipping_min_weight)){ $rest[]=JText::_('SHIPPING_MIN_WEIGHT').':'.$shipping->shipping_params->shipping_min_weight.$shipping->shipping_params->shipping_weight_unit; }
				if(!empty($shipping->shipping_params->shipping_max_weight)){ $rest[]=JText::_('SHIPPING_MAX_WEIGHT').':'.$shipping->shipping_params->shipping_max_weight.$shipping->shipping_params->shipping_weight_unit; }

				if(isset($shipping->shipping_params->shipping_min_price) && bccomp($shipping->shipping_params->shipping_min_price,0,5)){
					$shipping->shipping_min_price=$shipping->shipping_params->shipping_min_price;
					$rest[]=JText::_('SHIPPING_MIN_PRICE').':'.$currencyHelper->displayPrices(array($shipping),'shipping_min_price','shipping_currency_id');
				}
				if(isset($shipping->shipping_params->shipping_max_price) && bccomp($shipping->shipping_params->shipping_max_price,0,5)){
					$shipping->shipping_max_price=$shipping->shipping_params->shipping_max_price;
					$rest[]=JText::_('SHIPPING_MAX_PRICE').':'.$currencyHelper->displayPrices(array($shipping),'shipping_max_price','shipping_currency_id');
				}
				if(!empty($shipping->shipping_params->shipping_zip_prefix)){ $rest[]=JText::_('SHIPPING_PREFIX').':'.$shipping->shipping_params->shipping_zip_prefix; }
				if(!empty($shipping->shipping_params->shipping_min_zip)){ $rest[]=JText::_('SHIPPING_MIN_ZIP').':'.$shipping->shipping_params->shipping_min_zip; }
				if(!empty($shipping->shipping_params->shipping_max_zip)){ $rest[]=JText::_('SHIPPING_MAX_ZIP').':'.$shipping->shipping_params->shipping_max_zip; }
				if(!empty($shipping->shipping_params->shipping_zip_suffix)){ $rest[]=JText::_('SHIPPING_SUFFIX').':'.$shipping->shipping_params->shipping_zip_suffix; }
				if(!empty($shipping->zone_name_english)){ $rest[]=JText::_('ZONE').':'.$shipping->zone_name_english; }
				if(!empty($rest)) {
					$shipping_data .= '<div style="margin-left:10px">'.implode('<br/>', $rest).'</div>';
				}


				if($previous_shipping_id != $shipping->shipping_id) {
					$data .= "\r\n".'<tr class="hikashop_shipping_price_category"><td colspan="5">'.$shipping_data.'</td><td align="center">'.
						'<a href="#" onclick="return hikashop_addline_shippingprice(this,'.$shipping->shipping_id.',\''.str_replace(array('"',"'"),array('&quot;','\''),$shipping->shipping_name).'\',\''.$shipping->currency_symbol.'\');"><img src="'.HIKASHOP_IMAGES.'add.png" alt="+"/></a>'.
						'</td></tr>';
				}
				$previous_shipping_id = $shipping->shipping_id;


				if(!empty($shipping->shipping_price_value) || !empty($shipping->shipping_fee_value)) {
					if($shipping->shipping_price_min_quantity < 1)
						$shipping->shipping_price_min_quantity = 1;
					if($shipping->shipping_price_value < 0 || $shipping->shipping_fee_value < 0) {
						$blocked_checked = 'checked="checked"';
						$attribute = 'readonly="readonly"';
						$shipping->shipping_price_value = -1;
						$shipping->shipping_fee_value = -1;
					}else{
						$blocked_checked = '';
						$attribute = '';
					}
					$data .= '<tr><td>'.
						'<input type="hidden" name="shipping_prices['.$i.'][id]" value="'.$shipping->shipping_price_id.'"/>'.
						'<input type="hidden" name="shipping_prices['.$i.'][shipping_id]" value="'.$shipping->shipping_id.'"/>'.
						'</td><td><input type="text" name="shipping_prices['.$i.'][qty]" value="'.$shipping->shipping_price_min_quantity.'" size="3"/></td>'.
						'<td style="text-align:center"><input type="text" id="shipping_prices_value_'.$i.'" '.$attribute.' name="shipping_prices['.$i.'][value]" value="'.$shipping->shipping_price_value.'" size="7"/> '.$shipping->currency_symbol.'</td>'.
						'<td style="text-align:center"><input type="text" id="shipping_prices_fee_'.$i.'" '.$attribute.' name="shipping_prices['.$i.'][fee]" value="'.$shipping->shipping_fee_value.'" size="7"/> '.$shipping->currency_symbol.'</td>'.
						'<td><input type="checkbox" onchange="hikashop_shippingprice_blocked_change('.$i.', this)" '.$blocked_checked.'/></td>'.
						'<td align="center">'.
						'<a href="#" onclick="return hikashop_remline_shippingprice(this);"><img src="'.HIKASHOP_IMAGES.'delete.png" alt="-"/></a>'.
						'</td></tr>';
				}

				$i++;
				unset($shipping);
			}

			$data .= "\r\n".'<tr id="hikashop_shipping_price_tpl_line" style="display:none"><td>'.
				'<input type="hidden" name="{field_id}" value="{shipping_id}"/>'.
				'</td><td><input type="text" name="{field_qty}" value="" size="3"/></td>'.
				'<td style="text-align:center"><input id="shipping_prices_value_{cpt}" type="text" name="{field_value}" value="" size="7"/> {currency}</td>'.
				'<td style="text-align:center"><input id="shipping_prices_fee_{cpt}" type="text" name="{field_fee}" value="" size="7"/> {currency}</td>'.
				'<td><input type="checkbox" onchange="hikashop_shippingprice_blocked_change({cpt}, this)" /></td>'.
				'<td align="center"><a href="#" onclick="return hikashop_remline_shippingprice(this);"><img src="'.HIKASHOP_IMAGES.'delete.png" alt="-"/></a></td></tr>';

			$data .= '</tbody></table>
<input type="hidden" name="shipping_prices[init]" value=""/>
<script type="text/javascript">
var hikashop_shippingprice_cpt = '.$i.';
function hikashop_addline_shippingprice(el,id,name,currency) {
	var d = document, tplLine = d.getElementById("hikashop_shipping_price_tpl_line"),
		tableUser = tplLine.parentNode,
		htmlblocks = {
			cpt: hikashop_shippingprice_cpt,
			field_id: "shipping_prices["+hikashop_shippingprice_cpt+"][shipping_id]",
			field_qty: "shipping_prices["+hikashop_shippingprice_cpt+"][qty]",
			field_fee: "shipping_prices["+hikashop_shippingprice_cpt+"][fee]",
			field_value: "shipping_prices["+hikashop_shippingprice_cpt+"][value]",
			shipping_id: id,
			name: name,
			currency: currency
		};
	if(!tplLine) return;
	var trLine = tplLine.cloneNode(true);
	trLine.id = "";
	while(el != null && el.tagName.toLowerCase() != "tr") { el = el.parentNode; }
	if(el == null || !el.nextSibling) {
		tableUser.appendChild(trLine);
	} else {
		while(el.nextSibling && el.nextSibling.tagName && el.nextSibling.tagName.toLowerCase() == "tr" && el.nextSibling.class != "hikashop_shipping_price_category") { el = el.nextSibling; }
		tableUser.insertBefore(trLine, el.nextSibling);
	}
	trLine.style.display = "";
	for (var i = tplLine.cells.length - 1; i >= 0; i--) {
		for(var k in htmlblocks) {
			if(trLine.cells[i])
				trLine.cells[i].innerHTML = trLine.cells[i].innerHTML.replace(new RegExp("{"+k+"}","g"), htmlblocks[k]);
		}
	}
	hikashop_shippingprice_cpt++;
	return false;
}
function hikashop_remline_shippingprice(el) {
	while(el != null && el.tagName.toLowerCase() != "tr") { el = el.parentNode; }
	if(!el) return;
	var table = el.parentNode;
	table.removeChild(el);
	return false;
}
function hikashop_shippingprice_blocked_change(id, el) {
	var d = document,
		elValue = d.getElementById("shipping_prices_value_"+id),
		elFee = d.getElementById("shipping_prices_fee_"+id);
	if(!elValue || !elFee)
		return false;
	if(el.checked) {
		elValue.setAttribute("readonly", "readonly");
		elValue.value= "-1";
		elFee.setAttribute("readonly", "readonly");
		elFee.value= "-1";
	} else {
		elValue.removeAttribute("readonly", "readonly");
		elValue.value= "";
		elFee.removeAttribute("readonly", "readonly");
		elFee.value= "";
	}
}
</script>
</fieldset>';

			$html[] = $data;
		}
	}


	function onAfterProductCreate(&$product) {
		return $this->onAfterProductUpdate($product);
	}


	function onAfterProductUpdate(&$product) {
		$app = JFactory::getApplication();
		if(!$app->isAdmin())
			return;

		$formData = JRequest::getVar('shipping_prices', array(), '', 'array');
		if(empty($formData))
			return;

		if(empty($product->product_id))
			return;

		$db = JFactory::getDBO();
		$query = 'SELECT b.*, a.*, c.currency_symbol FROM ' . hikashop_table('shipping') . ' AS a INNER JOIN '.
			hikashop_table('shipping_price').' AS b ON a.shipping_id = b.shipping_id INNER JOIN '.
			hikashop_table('currency').' AS c ON c.currency_id = a.shipping_currency_id '.
			'WHERE a.shipping_params LIKE '.
			$db->Quote('%s:20:"shipping_per_product";s:1:"1"%') . ' AND b.shipping_price_ref_id = ' . $product->product_id . ' AND b.shipping_price_ref_type = \'product\' '.
			'ORDER BY a.shipping_id, b.shipping_price_min_quantity';
		$db->setQuery($query);
		$shippings = $db->loadObjectList('shipping_price_id');

		$toRemove = array_keys($shippings);
		if(!empty($toRemove)) {
			$toRemove = array_combine($toRemove, $toRemove);
		}
		$toInsert = array();


		$checks = array();
		foreach($formData as &$data) {
			if(is_string($data)) {
				$data = null;
			} else {
				if(empty($checks[$data['shipping_id']])) {
					$checks[$data['shipping_id']] = array();
				}
				if(!isset($checks[$data['shipping_id']][$data['qty']])) {
					$checks[$data['shipping_id']][$data['qty']] = true;
				} else {
					$data = null;
				}
			}
			unset($data);
		}
		unset($checks);

		foreach($formData as $data) {
			if($data == null)
				continue;
			$shipping = null;
			if(!empty($data['id']) && isset($shippings[$data['id']]) ) {
				if(empty($data['value']) && empty($data['fee']))
					continue;

				$shipping = $shippings[$data['id']];
				unset($toRemove[$data['id']]);

				if(empty($data['qty']) || (int)$data['qty'] < 1)
					$data['qty'] = 1;

				if( (int)$shipping->shipping_price_min_quantity != (int)$data['qty'] || (float)$shipping->shipping_price_value != (float)$data['value'] || (float)$shipping->shipping_fee_value != (float)$data['fee']) {
					$query = 'UPDATE ' . hikashop_table('shipping_price') .
						' SET shipping_price_min_quantity = ' . (int)$data['qty'] . ', shipping_price_value = ' . (float)$data['value'] . ', shipping_fee_value = ' . (float)$data['fee'] .
						' WHERE shipping_price_id = ' . $data['id'] . ' AND shipping_price_ref_id = ' . $product->product_id . ' AND shipping_price_ref_type = \'product\'';
					$db->setQuery($query);
					$db->query();
				}
			} else {
				if((!empty($data['value']) || !empty($data['fee'])) && !empty($data['shipping_id']) ) {
					if(empty($data['qty']) || (int)$data['qty'] < 1)
						$data['qty'] = 1;
					$toInsert[] = (int)$data['shipping_id'].','.$product->product_id.',\'product\','.(int)$data['qty'].','.(float)$data['value'].','.(float)$data['fee'];
				}
			}
		}


		if(!empty($toRemove)) {
			$db->setQuery('DELETE FROM ' . hikashop_table('shipping_price') . ' WHERE shipping_price_ref_id = ' . $product->product_id . ' AND shipping_price_ref_type = \'product\' AND shipping_price_id IN ('.implode(',',$toRemove).')');
			$db->query();
		}
		if(!empty($toInsert)) {
			$db->setQuery('INSERT IGNORE INTO ' . hikashop_table('shipping_price') . ' (`shipping_id`,`shipping_price_ref_id`,`shipping_price_ref_type`,`shipping_price_min_quantity`,`shipping_price_value`,`shipping_fee_value`) VALUES ('.implode('),(',$toInsert).')');
			$db->query();
		}
	}
}
