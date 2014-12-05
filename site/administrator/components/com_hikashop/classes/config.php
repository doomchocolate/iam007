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
class hikashopConfigClass extends hikashopClass{
	var $toggle = array('config_value'=>'config_namekey');
	function load(){
		$query = 'SELECT * FROM '.hikashop_table('config');
		$this->database->setQuery($query);
		$this->values = $this->database->loadObjectList('config_namekey');
		if(!empty($this->values['default_params']->config_value)){
			$this->values['default_params']->config_value = unserialize(base64_decode($this->values['default_params']->config_value));
		}
	}

	function set($namekey,$value=null){
		if(!isset($this->values[$namekey]) || !is_object($this->values[$namekey])) $this->values[$namekey] = new stdClass();
		$this->values[$namekey]->config_value=$value;
		$this->values[$namekey]->config_namekey=$namekey;
		return true;
	}

	function get($namekey,$default = ''){
		if(empty($this->values)){
			$this->load();
		}

		if(isset($this->values[$namekey])){
			if(preg_match('#^(menu_|params_)[0-9]+$#',$namekey) && !empty($this->values[$namekey]->config_value) && is_string($this->values[$namekey]->config_value)){
				$this->values[$namekey]->config_value = unserialize(base64_decode($this->values[$namekey]->config_value));
			}
			if($namekey=='main_currency'){
				return $this->_checkMainCurrency($this->values[$namekey]->config_value);
			}
			return $this->values[$namekey]->config_value;
		}
		return $default;
	}

	function _checkMainCurrency($value){
		if(!is_numeric($value)){
			$this->database->setQuery('SELECT currency_id FROM '.hikashop_table('currency').' WHERE currency_code ='.$this->database->Quote($value));
			$value = (int) $this->database->loadResult();
		}

		if(empty($value)){
			return 1;
		}
		return $value;
	}

	function save(&$configObject,$default=false){

		if(empty($this->values)){
			$this->load();
		}
		$previous_stars = isset($this->values['vote_star_number']->config_value)?$this->values['vote_star_number']->config_value:5;
		$query = 'REPLACE INTO '.hikashop_table('config').' (config_namekey,config_value'.($default?',config_default':'').') VALUES ';
		$params = array();
		if(is_object($configObject)){
			$configObject = get_object_vars($configObject);
		}
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
		foreach($configObject as $namekey => $value){
			if($namekey == 'configClassInit') continue;
			if($namekey == 'simplified_registration' && is_array($value)){
				$value = implode($value,',');
			}
			if($namekey=='default_params' || preg_match('#^(menu_|params_)[0-9]+$#',$namekey)){
				$value=base64_encode(serialize($value));
			}elseif($namekey=='main_currency'){
				if(!empty($this->values[$namekey]->config_value)){
					$currencyClass = hikashop_get('class.currency');
					$currencyClass->updateRatesWithNewMainCurrency($this->values[$namekey]->config_value,$value);
				}
			}
			if(!isset($this->values[$namekey]))$this->values[$namekey]= new stdClass();
			$this->values[$namekey]->config_value = $value;
			if(!isset($this->values[$namekey]->config_default)){
				$this->values[$namekey]->config_default = $this->values[$namekey]->config_value;
			}
			$cleaned_var = $safeHtmlFilter->clean($value, 'string');
			if($namekey=='order_number_format')$cleaned_var=str_replace('&quot;}"','"}',$cleaned_var);
			$params[] = '('.$this->database->Quote(strip_tags($namekey)).','.$this->database->Quote($cleaned_var).($default?','.$this->database->Quote($this->values[$namekey]->config_default):'').')';
			$new_stars = isset($this->values['vote_star_number']->config_value)?$this->values['vote_star_number']->config_value:5;
		}
		if($previous_stars != $new_stars){
			$this->update_average_rate($previous_stars, $new_stars);
		}
		$query .= implode(',',$params);
		$this->database->setQuery($query);
		return $this->database->query();
	}

	function reset(){
		$query = 'UPDATE '.hikashop_table('config').' SET config_value = config_default';
		$this->database->setQuery($query);
		$this->database->query();
		$this->load();
	}

	function update_average_rate($previous_stars, $new_stars){
		$query=
		'SELECT product_id, product_average_score FROM '.hikashop_table('product').' WHERE product_average_score != 0';
		$this->database->setQuery($query);
		$scores = $this->database->loadObjectList();
		foreach($scores as $score){
			$average_score =($new_stars * $score->product_average_score)/$previous_stars;
			$query=	'UPDATE '.hikashop_table('product').' SET product_average_score = '.$average_score.' WHERE product_id = '.(int)$score->product_id;
			$this->database->setQuery($query);
			$this->database->query();
		}
		$query=
		'SELECT vote_id, vote_rating FROM '.hikashop_table('vote').' WHERE vote_rating != 0';
		$this->database->setQuery($query);
		$scores = $this->database->loadObjectList();
		foreach($scores as $score){
			$vote =($new_stars * $score->vote_rating)/$previous_stars;
			$query=	'UPDATE '.hikashop_table('vote').' SET vote_rating = '.$vote.' WHERE vote_id = '.(int)$score->vote_id;
			$this->database->setQuery($query);
			$this->database->query();
		}
	}
}
