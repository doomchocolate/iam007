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
class hikashopProductdisplayType {
	var $default = array(
		'show_default',
		'show_reversed',
		'show_tabular'
	);

	function load(){
		$this->values = array();
		if(JRequest::getCmd('from_display',false) == false)
			$this->values[] = JHTML::_('select.option', '', JText::_('HIKA_INHERIT'));
		$this->values[] = JHTML::_('select.optgroup', '-- '.JText::_('FROM_HIKASHOP').' --');
		foreach($this->default as $d) {
			$this->values[] = JHTML::_('select.option', $d, JText::_(strtoupper($d)));
		}
		if(version_compare(JVERSION,'1.6.0','>=')){
			$this->values[] = JHTML::_('select.optgroup', '-- '.JText::_('FROM_HIKASHOP').' --');
		}

		$closeOpt = '';
		$values = $this->getLayout();
		foreach($values as $value) {
			if(substr($value,0,1) == '#') {
				if(version_compare(JVERSION,'1.6.0','>=') && !empty($closeOpt)){
					$this->values[] = JHTML::_('select.optgroup', $closeOpt);
				}
				$value = substr($value,1);
				$closeOpt = '-- ' . JText::sprintf('FROM_TEMPLATE',basename($value)) . ' --';
				$this->values[] = JHTML::_('select.optgroup', $closeOpt);
			} else {
				$this->values[] = JHTML::_('select.option', $value, $value);
			}
		}
		if(version_compare(JVERSION,'1.6.0','>=') && !empty($closeOpt)){
			$this->values[] = JHTML::_('select.optgroup', $closeOpt);
		}
	}

	function display($map,$value) {
		$this->load();
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value );
	}

	function check($name,$template) {
		if($name == '' || in_array($name, $this->default))
			return true;
		$values = $this->getLayout($template);
		return in_array($name,$values);
	}

	function getLayout($template = '') {
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		static $values = null;
		if($values !== null)
			return $values;
		$client	= JApplicationHelper::getClientInfo(0); // 0: Front client
		$tplDir = $client->path.DS.'templates'.DS;
		$values = array();
		if(empty($template)) {
			$templates = JFolder::folders($tplDir);
			if(empty($templates))
				return null;
		} else {
			$templates = array($template);
		}
		$groupAdded = false;
		foreach($templates as $tpl) {
			$t = $tplDir.$tpl.DS.'html'.DS.HIKASHOP_COMPONENT.DS;
			if(!JFolder::exists($t))
				continue;
			$folders = JFolder::folders($t);
			if(empty($folders))
				continue;
			foreach($folders as $folder) {
				$files = JFolder::files($t.$folder.DS);
				if(empty($files))
					continue;
				foreach($files as $file) {
					if(substr($file,-4) == '.php')
						$file = substr($file,0,-4);
					if(substr($file,0,5) == 'show_' && !in_array($file,$this->default)) {
						if(!$groupAdded) {
							$values[] = '#'.$tpl;
							$groupAdded = true;
						}
						$values[] = $file;
					}
				}
			}
		}
		return $values;
	}

	public function displaySingle($map, $value, $display = '', $root = 0, $delete = false) {
		hikashop_loadJslib('jquery');
		hikashop_loadJslib('otree');
		$id = str_replace(array('[',']'),array('_',''),$map);

		$key = '';
		$name = '<em>'.JText::_('HIKA_NONE').'</em>';
		$cleanText = '<em>'.str_replace("'", "\\'", JText::_('HIKA_NONE')).'</em>';
		$productClass = hikashop_get('class.product');
		if((int)$value > 0) {
			$product = $productClass->get((int)$value);
			if($product) {
				$key = (int)$value;
				$name = $product->product_name;
			}
		}

		$displayParam = '';
		if(!empty($display)) {
			$displayParam = '&display=' . urlencode($display);
		}
		$shopConfig = hikashop_config(false);
		$minSearch = $shopConfig->get('product_search_min_lenght', 3);
		$jsEvent = 'window.nameboxes.advSearch(\''.$id.'\', this, \''. hikashop_completeLink('product&task=findTree'.$displayParam .'&search=HIKASEARCH', false, false, true) . '\', \'HIKASEARCH\', '.$minSearch.');';

		$elements = $productClass->getTreeList((int)$root, 2, true, $display);

		$ret = '
<div class="nameboxes" id="'.$id.'" onclick="window.nameboxes.focus(\''.$id.'\',\''.$id.'_text\');">
	<div class="namebox" id="'.$id.'_namebox">
		<input type="hidden" name="'.$map.'" id="'.$id.'_valuehidden" value="'.$key.'"/><span id="'.$id.'_valuetext">'.$name.'</span>
		'.(!$delete?'<a class="editbutton" href="#" onclick="return false;"><span>-</span></a>':
		'<a class="closebutton" href="#" onclick="window.nameboxes.clean(\''.$id.'\',this,\''.$cleanText.'\');return false;"><span>X</span></a>').'
	</div>
	<div class="nametext">
		<input id="'.$id.'_text" type="text" style="width:50px;min-width:60px" onfocus="window.nameboxes.focus(\''.$id.'\',this);" onblur="" onkeyup="'.$jsEvent.'" onchange="'.$jsEvent.'"/>
		<span style="position:absolute;top:0px;left:-2000px;visibility:hidden" id="'.$id.'_span">span</span>
	</div>
	<div class="hikaclear" style="clear:both;float:none;"></div>
</div>
<div class="namebox-popup">
	<div id="'.$id.'_otree" style="display:none;" class="oTree namebox-popup-content"></div>
</div>
<script type="text/javascript">
var options = {rootImg:"'.HIKASHOP_IMAGES.'otree/", showLoading:true};
var data_'.$id.' = ' . json_encode($elements) . ', orign_data_'.$id.' = true;
var '.$id.' = new window.oTree("'.$id.'",options,null,data_'.$id.',false);
'.$id.'.addIcon("world","world.png");
'.$id.'.callbackFct = function(tree,node,ev) {
	return window.nameboxes.callbackFct(this, "'. hikashop_completeLink('product&task=getTree'.$displayParam .'&category_id=HIKACATID', false, false, true) . '", "HIKACATID", tree, node, ev);
};
'.$id.'.callbackSelection = function(tree,id) {
	var d = document, node = tree.get(id);
	if(node.state == 0) {
		if( node.value && node.name ) {
			var e = d.getElementById("'.$id.'_valuehidden");
			if(e) e.value = node.value;
			e = d.getElementById("'.$id.'_valuetext");
			if(e) e.innerHTML = node.name;
		}
	} else if(node.state >= 1 && node.state <= 4) {
		tree.s(node);
		return;
	}
	var c = d.getElementById("'.$id.'_otree");
	if(c) c.style.display = "none";
	c = d.getElementById("'.$id.'_text");
	if(c) c.value = "";
	tree.sel(0);
};
'.$id.'.render(true);
</script>';

		return $ret;
	}

	public function displayMultiple($map, $values, $display = '', $root = 0) {
		if(substr($map,-2) == '[]')
			$map = substr($map,0,-2);
		$id = str_replace(array('[',']'),array('_',''),$map);
		$ret = '<div class="nameboxes" id="'.$id.'" onclick="'.$id.'_focus(\''.$id.'_text\');">';
		if(!empty($values)) {
			foreach($values as $key => $name) {
				$obj = null;
				if(is_object($name)) {
					$obj = $name;
					$name = $obj->product_name;
					$key = $obj->product_id;
				}
				$ret .= '<div class="namebox" id="'.$id.'_'.$key.'">'.
					'<input type="hidden" name="'.$map.'[]" value="'.$key.'"/>'.$name.
					' <a class="closebutton" href="#" onclick="window.hikashop.deleteId(\''.$id.'_'.$key.'\');window.Oby.cancelEvent(event);return false;"><span>X</span></a>'.
					'</div>';
			}
		}

		$ret .= '<div class="namebox" style="display:none;" id="'.$id.'tpl">'.
				'<input type="hidden" name="{map}" value="{key}"/>{name}'.
				' <a class="closebutton" href="#" onclick="window.hikashop.deleteId(this.parentNode);window.Oby.cancelEvent(event);return false;"><span>X</span></a>'.
				'</div>';

		$jsEvent = $id.'_tree';
		$ret .= '<div class="nametext">'.
			'<input id="'.$id.'_text" type="text" style="width:50px;min-width:60px" onfocus="'.$id.'_focus(this);" onblur="'.$id.'_blur(this);" onkeyup="'.$jsEvent.'(this);" onchange="'.$jsEvent.'(this);"/>'.
			'<span style="position:absolute;top:0px;left:-2000px;visibility:hidden" id="'.$id.'_span">span</span>'.
			'</div>';

		hikashop_loadJslib('jquery');
		hikashop_loadJslib('otree');

		$config = hikashop_config(false);
		$minSearch = $config->get('product_search_min_lenght', 3);

		$productClass = hikashop_get('class.product');
		$elements = $productClass->getTreeList((int)$root, 2, true, $display);

		$displayParam = '';
		if(!empty($display)) {
			$displayParam = '&display=' . urlencode($display);
		}

		$ret .= '<div class="hikaclear" style="clear:both;float:none;"></div></div>
<div class="namebox-popup">
<div id="'.$id.'_otree" style="display:none;" class="oTree namebox-popup-content"></div>
</div>
<script type="text/javascript">
var options = {rootImg:"'.HIKASHOP_IMAGES.'otree/", showLoading:true};
var data_'.$id.' = ' . json_encode($elements) . ', orign_data_'.$id.' = true;
var '.$id.' = new window.oTree("'.$id.'",options,null,data_'.$id.',false);
'.$id.'.addIcon("world","world.png");
'.$id.'.callbackFct = function(tree,node,ev) {
	var t = this, o = window.Oby, n = null;
	o.xRequest(
		"'. hikashop_completeLink('product&task=getTree'.$displayParam .'&category_id=HIKACATID', false, false, true) . '".replace("HIKACATID", node.value),
		null,
		function(xhr,params) {
			var json = o.evalJSON(xhr.responseText);
			if(json.length > 0) {
				var s = json.length;
				for(var i = 0; i < s; i++) {
					n = json[i];
					t.add(node.id, n.status, n.name, n.value, n.url, n.icon);
				}
				t.update(node);
				if(t.selectOnOpen) {
					var n = t.find(t.selectOnOpen);
					if(n) { t.sel(n); }
					t.selectOnOpen = null;
				}
			} else {
				t.emptyDirectory(node);
			}
		},
		function(xhr, params) {
			t.add(node.id, 0, "error");
			t.update(node);
		}
	);
	return false;
};
'.$id.'.callbackSelection = function(tree,id) {
	var d = document, node = tree.get(id);
	if(node.state == 0) {
		if(node.value && node.name) {
			var blocks = {map: "'.$map.'[]", key: node.value, name: node.name}, cur = d.getElementById("'.$id.'_"+node.value);
			if(!cur) {
				window.hikashop.dup("'.$id.'tpl", blocks, "'.$id.'_"+node.value);
			}
		}
	} else if(node.state >= 1 && node.state <= 4) {
		tree.s(node);
		return;
	}
	var c = d.getElementById("'.$id.'_otree");
	if(c) c.style.display = "none";
	c = d.getElementById("'.$id.'_text");
	if(c) c.value = "";
	tree.sel(0);
};
'.$id.'.render(true);

function '.$id.'_tree(el) {
	var d = document, s = d.getElementById("'.$id.'_span");
	s.innerHTML = el.value;
	if(el.value.length < '.$minSearch.') {
		if(!orign_data_'.$id.') {
			window.oTrees["'.$id.'"].lNodes = [];
			window.oTrees["'.$id.'"].lNodes[0] = new window.oNode(0,-1);
			window.oTrees["'.$id.'"].load(data_'.$id.');
			window.oTrees["'.$id.'"].render();
			orign_data_'.$id.' = true;
		}
		window.oTrees["'.$id.'"].search(el.value);
	} else {
		window.Oby.xRequest(
			"'. hikashop_completeLink('product&task=findTree'.$displayParam .'&search=HIKASEARCH', false, false, true) . '".replace("HIKASEARCH", el.value),
			null,
			function(xhr,params) {
				orign_data_'.$id.' = false;
				window.oTrees["'.$id.'"].lNodes = [];
				window.oTrees["'.$id.'"].lNodes[0] = new window.oNode(0,-1);
				var json = window.Oby.evalJSON(xhr.responseText);
				window.oTrees["'.$id.'"].load(json);
				window.oTrees["'.$id.'"].render();
			},
			function(xhr, params) { }
		);
	}
	el.style.width = s.offsetWidth + 30 + "px";
}
function '.$id.'_focus(el) {
	var d = document, c = d.getElementById("'.$id.'"); e = d.getElementById("'.$id.'_otree");
	if(typeof(el) == "string")
		el = d.getElementById(el);
	el.focus();
	window.oTrees["'.$id.'"].search(el.value);
	if(e) {
		e.style.display = "";
		var f = function(evt) {
			var e = d.getElementById("'.$id.'_otree");
			if (!evt) var evt = window.event;
			var trg = (window.event) ? evt.srcElement : evt.target;
			while(trg != null) {
				if(trg == el || trg == e || trg == c)
					return;
				trg = trg.parentNode;
			}
			e.style.display = "none";
			window.Oby.removeEvent(document, "mousedown", f);
		};
		window.Oby.addEvent(document, "mousedown", f);
	}
}
function '.$id.'_blur(el) {
	return;
}

if(!window.hkjQuery) window.hkjQuery = window.jQuery;
hkjQuery(document).ready(function($) {
	$("#'.$id.'").sortable({
		cursor: "move",
		items: "div",
		stop: function(event, ui) {
			$("#'.$id.' .nametext").appendTo("#'.$id.'");
			$("#'.$id.' .hikaclear").appendTo("#'.$id.'");
		}
	});
	$("#'.$id.'").disableSelection();
});
</script>';

		return $ret;
	}
}
