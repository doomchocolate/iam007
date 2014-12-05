<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die('Restricted access');
?>
<script type="text/javascript">
	function getBirthday(years)
	{
		if(years=="")
		{
			return false;
		}

		var now 		= new Date();
		var birthday	= new Date(now.getTime()-years*365*24*60*60*1000);
		var date 		= birthday.getDate()+"/"+birthday.getMonth()+"/"+birthday.getFullYear();

		return date;
	}

	function submitSearchForm()
	{
		var gender 			= joms.jQuery("#datingsearch_gender").val();
		var agefrom 		= joms.jQuery("#datingsearch_agefrom").val();
		var ageto 			= joms.jQuery("#datingsearch_ageto").val();
		var city 			= joms.jQuery("#datingsearch_city").val();
		var state 			= joms.jQuery("#datingsearch_state").val();
		var country 		= joms.jQuery("#datingsearch_country").val();
		var birthdaystop 	= agefrom;
		var birthdaystart	= ageto;
		var keylist			= "";

		joms.jQuery("#datingsearch_search").attr("disabled", true);

		if(gender)
		{
			joms.jQuery("#datingsearch_hidden").append('<input type="hidden" name="field0" value="<?php echo $field->code->gender; ?>"><input type="hidden" name="condition0" value="equal"><input type="hidden" name="value0" value="'+joms.jQuery("#datingsearch_gender").val()+'"><input type="hidden" name="fieldType0" value="<?php echo $field->type->gender;?>">');

			if(keylist!="")
			{
				keylist += ","
			}
			keylist += "0";
		}

		if(birthdaystop || birthdaystart)
		{
			var extraAppend = "";

			if(birthdaystop && birthdaystart)
			{
				extraAppend = '<input type="hidden" name="condition1" value="between"><input type="hidden" name="value1" value="'+birthdaystop+'"><input type="hidden" name="value1_2" value="'+birthdaystart+'">';
                        }
			else if(birthdaystop && !birthdaystart)
			{
				extraAppend = '<input type="hidden" name="condition1" value="lessthanorequal"><input type="hidden" name="value1" value="'+birthdaystop+'">';
			}
			else if(!birthdaystop && birthdaystart)
			{
				extraAppend = '<input type="hidden" name="condition1" value="greaterthanorequal"><input type="hidden" name="value1" value="'+birthdaystart+'">';
			}

			<?php if(isset($field->type->birthdate)):?>
			joms.jQuery("#datingsearch_hidden").append('<input type="hidden" name="field1" value="<?php echo $field->code->birthdate; ?>">'+extraAppend+'<input type="hidden" name="fieldType1" value="<?php echo $field->type->birthdate;?>">');
			<?php endif?>
			if(keylist!="")
			{
				keylist += ","
			}
			keylist += "1";
		}

		if(city)
		{
			joms.jQuery("#datingsearch_hidden").append('<input type="hidden" name="field2" value="<?php echo $field->code->city; ?>"><input type="hidden" name="condition2" value="equal"><input type="hidden" name="value2" value="'+city+'"><input type="hidden" name="fieldType2" value="<?php echo $field->type->city;?>">');

			if(keylist!="")
			{
				keylist += ","
			}
			keylist += "2";
		}

		if(state)
		{
			joms.jQuery("#datingsearch_hidden").append('<input type="hidden" name="field3" value="<?php echo $field->code->state; ?>"><input type="hidden" name="condition3" value="equal"><input type="hidden" name="value3" value="'+state+'"><input type="hidden" name="fieldType3" value="<?php echo $field->type->state;?>">');

			if(keylist!="")
			{
				keylist += ","
			}
			keylist += "3";
		}

		if(country)
		{
			joms.jQuery("#datingsearch_hidden").append('<input type="hidden" name="field4" value="<?php echo $field->code->country; ?>"><input type="hidden" name="condition4" value="equal"><input type="hidden" name="value4" value="'+country+'"><input type="hidden" name="fieldType4" value="<?php echo $field->type->country;?>">');

			if(keylist!="")
			{
				keylist += ","
			}
			keylist += "4";
		}

		if(keylist)
		{
			joms.jQuery("#datingsearch_hidden").append('<input type="hidden" id="key-list" name="key-list" value="'+keylist+'" />');
			joms.jQuery("#datingsearch").submit();
		}
		else
		{
			joms.jQuery("#datingsearch_search").attr("disabled", false);
		}
	}
</script>
<div class="<?php echo $params->get('moduleclass_sfx'); ?> js-module">
	<form name="datingsearch" id="datingsearch" method="GET" action="<?php echo CRoute::_('index.php?option=com_community&view=search&task=advancesearch&Itemid=' . $itemid); ?>">

			<p><?php echo JText::_('MOD_DATINGSEARCH_LOOKING_FOR'); ?></p>
			<select id="datingsearch_gender" name="datingsearch_gender" >
				<option value="" <?php if($gender=='')echo 'selected'?>><?php echo JText::_('MOD_DATINGSEARCH_GENDER'); ?></option>
				<?php
				foreach($field->value->gender as $key=>$data) { ?>
					<option value="<?php echo $key; ?>" <?php if($gender==$data)echo 'selected'?>><?php echo JText::_($data); ?></option>
				<?php } ?>
			</select>

			<p><?php echo JText::_('MOD_DATINGSEARCH_AGE_BETWEEN'); ?></p>
			<input type="text" style="width:50px;" id="datingsearch_agefrom" name="datingsearch_agefrom" value="<?php echo $agefrom; ?>"/> <?php echo JText::_('MOD_DATINGSEARCH_TO');?>
			<input type="text" style="width:50px;" id="datingsearch_ageto" name="datingsearch_ageto" value="<?php echo $ageto;?>"/>

			<!-- 		<div><?php echo JText::_('MOD_DATINGSEARCH_LOCATION'); ?> : </div> -->

			<p><?php echo JText::_('MOD_DATINGSEARCH_CITY'); ?></p>
			<input type="text" id="datingsearch_city" name="datingsearch_city" value="<?php echo $city;?>"/>
			<p><?php echo JText::_('MOD_DATINGSEARCH_STATE'); ?></p>
			<input type="text" id="datingsearch_state" name="datingsearch_state" value="<?php echo $state;?>"/>
			<select id="datingsearch_country" name="datingsearch_country" >
			<option value="" <?php if($country=='')echo 'selected'?>><?php echo JText::_('MOD_DATINGSEARCH_COUNTRY'); ?></option>
			<?php foreach($field->value->country as $data) { ?>
				<option value="<?php echo $data; ?>" <?php if($country==$data)echo 'selected'?>><?php echo JText::_($data); ?></option>
			<?php } ?>
			</select>

			<div>
				<input type="button" id="datingsearch_search" name="datingsearch_search" value="<?php echo JText::_('MOD_DATINGSEARCH_SEARCH'); ?>" onclick="submitSearchForm();"/>
			</div>

			<div id="datingsearch_hidden">
				<input type="hidden" name="operator" value="and" />
				<input type="hidden" name="option" value="com_community" />
				<input type="hidden" name="view" value="search" />
				<input type="hidden" name="task" value="advancesearch" />
				<input type="hidden" name="Itemid" value="<?php echo $itemid; ?>" />
			</div>
	</form>
</div>
