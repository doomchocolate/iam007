<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die();
?>
<script type="text/JavaScript">

jsAdvanceSearch = {
			action: {
				keynum : 0,
				dateFormatDesc : '<?php echo JText::_("COM_COMMUNITY_DATE_FORMAT_DESCRIPTION"); ?>',
				addCriteria: function ( ) {
					var criteria = "";
					var keynum = jsAdvanceSearch.action.keynum;

					criteria +='<div id="criteria'+keynum+'" class="criteria-row clearfix">';
						criteria +='<div id="removelink'+keynum+'" class="delete-row">';
							criteria +='<a class="remove" href="javascript:void(0);" onclick="jsAdvanceSearch.action.removeCriteria(\''+keynum+'\');">';
								// criteria +='<?php echo JText::_('COM_COMMUNITY_HIDE_CRITERIA');?>';
								criteria +='<i class="com-icon-block"></i>';
							criteria +='</a>';
						criteria +='</div>';
						criteria +='<div id="selectfield'+keynum+'">';
							criteria +='<select name="field'+keynum+'" id="field'+keynum+'" onchange="jsAdvanceSearch.action.changeField(\''+keynum+'\');" style="width:150px;">';
								<?php
								foreach($fields as $label=>$data)
								{
									if($data->published && $data->visible)
									{
								?>
										criteria +='<optgroup label="<?php echo addslashes( JText::_($label) );?>">';
											<?php
											foreach($data->fields as $key=>$field)
											{
												if($field->published && $field->visible && $field->searchable)
												{
													$selected = "";
													if($field->fieldcode == 'username')
													{
														$selected = "SELECTED";
													}
											?>
													criteria +='<option value="<?php echo addslashes($field->fieldcode); ?>" <?php echo $selected; ?>><?php echo JText::_(addslashes(JString::trim($field->name)));?></option>';
											<?php
												}
											}
											?>
										criteria +='</optgroup>';
								<?php
									}
								}
								?>
							criteria +='</select>';
						criteria +='</div>';
						criteria +='<div id="selectcondition'+keynum+'">';
							criteria +='<select name="condition'+keynum+'" id="condition'+keynum+'" style="width:150px;">';
								criteria +='<option value=""></option>';
							criteria +='</select>';
						criteria +='</div>';
						criteria +='<div id="valueinput'+keynum+'">';
							criteria +='<input type="text" name="value'+keynum+'" id="value'+keynum+'" style="width:150px;"/>';
						criteria +='</div>';
						criteria +='<div id="valueinput'+keynum+'_2">';
						criteria +='</div>';
						criteria +='<div id="typeinput'+keynum+'" style="display:none;">';
							criteria +='<input type="hidden" name="fieldType'+keynum+'" id="fieldType'+keynum+'" value="" style="width:150px;"/>';
						criteria +='</div>';
						criteria +='<div style="clear:both"></div>';
					criteria +='</div>';

					var comma = '';
					if(joms.jQuery('#key-list').val()!="")
					{
						var comma = ',';
					}
					joms.jQuery('#key-list').val(joms.jQuery('#key-list').val()+comma+keynum);



					joms.jQuery('#criteriaContainer').append(criteria);
					jsAdvanceSearch.action.changeField(keynum);
					jsAdvanceSearch.action.keynum++;
				},
				removeCriteria: function ( id ) {
					var inputs = [];
					var _id, _id2;
					_id = joms.jQuery('#key-list').val();
					_id2 = _id.split(',');

					joms.jQuery(_id2).each(function() {
						if ( this != id && this != "") {
							// re-populate
							inputs.push(this);
						}
					});

					joms.jQuery("#criteria"+id).remove();
					joms.jQuery('#key-list').val(inputs.join(','));
				},
				getFieldType: function ( fieldcode ) {
					var type;
					switch(fieldcode)
					{
						<?php
						foreach($fields as $label=>$data)
						{
							if($data->published && $data->visible)
							{
								foreach($data->fields as $key=>$field)
								{
									if($field->published && $field->visible)
									{
								?>
										case "<?php echo $field->fieldcode; ?>":
											type = "<?php echo $field->type; ?>";
											break;
								<?php
									}
								}
							}
						}
						?>
						default :
							type = "default";
					}
					return type;
				},
				getListValue: function ( id, fieldcode ) {
					var list;
					switch(fieldcode)
					{
						<?php
						foreach($fields as $label=>$data)
						{
							if($data->published && $data->visible)
							{
								foreach($data->fields as $key=>$field)
								{
									if($field->published && $field->visible)
									{
										if(!empty($field->options))
										{
									?>
											case "<?php echo $field->fieldcode; ?>":
												<?php if ($field->type == 'checkbox') { ?>
													list	= '<div class="clr"></div>';
													list	+= '<div style="padding: 0px; margin-left: 20px; margin-top: 5px;">';
													<?php
													foreach($field->options as $data)
													{
													?>
														list += '<div style="padding:0 10px 0 0;float: left;"><input type="checkbox" name="value'+id+'[]" value="<?php echo addslashes(JString::trim($data)); ?>"><?php echo addslashes(JText::_(JString::trim($data))); ?></input></div>';
													<?php
													}
													?>
													list	+= '<div class="clr"></div>';
													list	+= '</div>'
												<?php } else { ?>
													list = '<select name="value'+id+'" id="value'+id+'" style="width:157px;">';
													<?php
													foreach($field->options as $data)
													{
													?>
														list +='<option value="<?php echo addslashes(JString::trim($data)); ?>"><?php echo addslashes(JText::_(JString::trim($data))); ?></option>';
													<?php
													}
													?>
													list +='</select>';

												<?php } ?>
												break;
									<?php
										}
									}
								}
							}
						}
						?>
						default :
							list = '<input type="text" name="value'+id+'" id="value'+id+'" style="width:145px;"/>';
					}
					return list;
				},
				changeField: function ( id ) {
					var value, type, condHTML, listValue;
					var cond = [];
					var conditions = new Array();
					conditions['contain']			= "<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_CONTAIN'))); ?>";
					conditions['between']			= "<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_BETWEEN'))); ?>";
					conditions['equal']				= "<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_EQUAL'))); ?>";
					conditions['notequal']			= "<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_NOT_EQUAL'))); ?>";
					conditions['lessthanorequal']	= "<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_LESS_THAN_OR_EQUAL'))); ?>";
					conditions['greaterthanorequal']	= "<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_GREATER_THAN_OR_EQUAL'))); ?>";

					value	= joms.jQuery('#field'+id).val();
					type 	= jsAdvanceSearch.action.getFieldType(value);
					this.changeFieldType(type, id);

					switch(type)
					{
						case 'date'		:
							cond		= ['between', 'equal', 'notequal', 'lessthanorequal', 'greaterthanorequal'];
							listValue	= 0;
							break;
						case 'birthdate':
							cond		= ['between', 'equal', 'lessthanorequal', 'greaterthanorequal'];
							listValue	= 0;
							break;
						case 'checkbox'	:
						case 'radio'	:
						case 'singleselect'	:
						case 'select'	:
						case 'list'		:
							cond	  = ['equal', 'notequal'];
							listValue = this.getListValue(id, value);
							break;
						case 'email'	:
							cond	  = ['equal'];
							listValue = 0;
							break;
						case 'country' :
							cond = ['equal', 'notequal'];
							listValue = this.getListValue(id, value);
							break;
                        case 'gender' :
							cond = ['equal', 'notequal'];
							listValue = this.getListValue(id, value);
							break;
						case 'textarea'	:
						case 'text'		:
						default	:
							if(value == 'useremail')
							{
								cond	= ['equal'];
							}
							else
							{
								cond	= ['contain', 'equal', 'notequal'];
							}
							listValue = 0;
							break;
					}

					condHTML = '<select class="input select" name="condition'+id+'" id="condition'+id+'" style="width:150px;" onchange="jsAdvanceSearch.action.changeCondition('+id+');">';
					joms.jQuery(cond).each(function(){
						condHTML +='<option value="'+this+'">'+conditions[this]+'</option>';
					});
					condHTML +='</select>';

					joms.jQuery('#selectcondition'+id).html(condHTML);
					jsAdvanceSearch.action.changeCondition(id);
					jsAdvanceSearch.action.calendar(type, id);
					if(listValue!=0){
						joms.jQuery('#valueinput'+id).html(listValue);
					}
				},
				addAltInputField: function(type, id) {
					var cond = joms.jQuery('#condition'+id).val();
					var inputField;
					if(cond == "between"){
						if(type=='birthdate' || type=='date'){
							inputField  = '<input type="text" name="value'+id+'_2" id="value'+id+'_2" style="width:125px; margin-right:4px" value="" title="'+this.dateFormatDesc+'" readonly="true"/>';
						}else{
							inputField  = '<input type="text" name="value'+id+'_2" id="value'+id+'_2" style="width:125px; margin-right:4px" value=""/>';
						}
					}else{
						inputField = '';
					}
					joms.jQuery('#valueinput'+id+'_2').html(inputField);
					if(cond == "between"){
						if(type=='birthdate' || type=='date'){
                                                         joms.jQuery( '#value'+id+'_2' ).datepicker({
                                                            showOn: "button",
                                                            buttonImage: "components/com_community/assets/calendar.png",
                                                            buttonImageOnly: true
                                                        });							
						}
					}
				},
				calendar: function(type, id) {
					var inputField;
					if(type=='birthdate' || type=='date'){
						inputField  = '<a onclick="jsAdvanceSearch.action.toggleAgeSearch('+id+',1);" href="javascript:void(0);" title="<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_ADVSEARCH_AGE_TITLE'))); ?>"> <?php echo JText::_('COM_COMMUNITY_ADVSEARCH_DATE'); ?> </a><input type="text" name="value'+id+'" id="value'+id+'" style="width:100px; margin-right:4px" value="" title="'+this.dateFormatDesc+'" readonly="false"/>';
					}else{
						inputField  = '<input type="text" name="value'+id+'" id="value'+id+'" style="width:145px;"/>';
					}
					joms.jQuery('#valueinput'+id).html(inputField);
					//setup calendar widget if type is 'date'
					if(type=='birthdate' || type=='date'){
     
                                                joms.jQuery( '#value'+id ).datepicker({
                                                  showOn: "button",
                                                  buttonImage: "components/com_community/assets/calendar.png",
                                                  buttonImageOnly: true
                                                });
  
						
					}
				},
				changeFieldType: function(type, id) {
					joms.jQuery('#fieldType'+id).val(type);
				},
				changeCondition: function(id) {
					var type = joms.jQuery('#fieldType'+id).val();
					this.addAltInputField(type, id);
				},
				toggleAgeSearch: function(id,mode) {
					var cond = joms.jQuery('#condition'+id).val();
					if(mode == 1){
						inputField  = '<a onclick="jsAdvanceSearch.action.toggleAgeSearch('+id+',0);" href="javascript:void(0);" title="<?php echo addslashes(JString::trim(JText::_('COM_COMMUNITY_ADVSEARCH_DATE_TITLE'))); ?>"> <?php echo JText::_('COM_COMMUNITY_ADVSEARCH_AGE'); ?></a><input type="text" name="value'+id+'" id="value'+id+'" style="width:100px; margin-right:4px" value="" />';
						joms.jQuery('#valueinput'+id).html(inputField);
						if(cond == "between"){
							inputField  = '<input type="text" name="value'+id+'_2" id="value'+id+'_2" style="width:100px; margin-right:4px" value="" />';
							joms.jQuery('#valueinput'+id+'_2').html(inputField);
						}
					} else {
						jsAdvanceSearch.action.calendar('birthdate',id);
						jsAdvanceSearch.action.addAltInputField('birthdate',id);
					}
				}
			}
		}

	joms.jQuery(document).ready( function() {
		var searchHistory, operator;
	<?php if(!empty($filterJson)){?>
		searchHistory = eval(<?php echo $filterJson; ?>);
	<?php }else{?>
		searchHistory = '';
	<?php }?>

		joms.jQuery('#memberlist-save').click( function(){
			joms.memberlist.showSaveForm('<?php echo $keyList;?>' , searchHistory );
		});

		if(searchHistory != ''){
			var keylist = searchHistory['key-list'].split(',');
			var num;

			joms.jQuery(keylist).each(function(){
				num = jsAdvanceSearch.action.keynum;
				jsAdvanceSearch.action.addCriteria();
				joms.jQuery('#field'+num).val(searchHistory['field'+this]);
				jsAdvanceSearch.action.changeField(num);
				joms.jQuery('#condition'+num).val(searchHistory['condition'+this]);
				jsAdvanceSearch.action.changeCondition(num);

				if(searchHistory['datingsearch_agefrom'] && searchHistory['datingsearch_ageto'] )
				{
					jsAdvanceSearch.action.toggleAgeSearch(num,1);
				}

				if(searchHistory['fieldType'+this] == 'checkbox')
				{
					var myVal	= searchHistory['value'+this];
					if(joms.jQuery.isArray(myVal))
					{
						joms.jQuery.each(myVal, function(i, chkVal) {
							joms.jQuery('input[name=value'+num+'[]]').each(function() {
								if(this.value == chkVal)
								{
									this.checked = "checked";
								}
							});
						});

					}
				}
				else
				{
					joms.jQuery('#value'+num).val(searchHistory['value'+this]);
				}

				if(searchHistory['condition'+this] == 'between'){
					joms.jQuery('#value'+num+'_2').val(searchHistory['value'+this+'_2']);
				}
			})

			if(searchHistory.operator == 'and'){
				operator = 'operator_all';
			}else{
				operator = 'operator_any';
			}
		}else{
			operator = 'operator_all';
			jsAdvanceSearch.action.addCriteria();
		}
		joms.jQuery('#'+operator).attr("checked", true);
	});

</script>
<div class="advance-search">
<form name="jsform-search-advancesearch" action="<?php echo CRoute::getURI(); ?>" method="GET">
	<p id="criteriaTitle">
		<b><?php echo JText::_("COM_COMMUNITY_CRITERIA"); ?></b>
	</p>
	<div id="criteriaContainer">
	</div>
	<div id="optionContainer">
		<div class="criteria-option-top clearfull">
			<a class="add" href="javascript:void(0);" onclick="jsAdvanceSearch.action.addCriteria();">
				<i class="com-icon-add"></i>
				<span><?php echo JText::_("COM_COMMUNITY_ADD_CRITERIA"); ?></span>
			</a>
		</div>
		<div class="criteria-option-btm clearfull">
			<label class="lblradio" style="padding-right: 20px;"><input type="radio" name="operator" id="operator_all" value="and" class="input radio"> <?php echo JText::_("COM_COMMUNITY_MATCH_ALL_CRITERIA"); ?></label>
			<label class="lblradio" style="padding-right: 20px;"><input type="radio" name="operator" id="operator_any" value="or" class="input radio"> <?php echo JText::_("COM_COMMUNITY_MATCH_ANY_CRITERIA"); ?></label>
			<label class="lblradio" style="padding-right: 20px;"><input type="checkbox" name="avatar" id="avatar" style="margin-right: 5px;" value="1" class="input radio"<?php echo ($avatarOnly) ? ' checked="checked"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_EVENTS_AVATAR_ONLY'); ?></label>
			<input type="hidden" name="option" value="com_community" />
			<?php
			// If non-sef url used, we'd need to add these
			$app = JFactory::getApplication();
			$isSef = $app->getCfg( 'sef' );
			if(!$isSef):
			?>
			<input type="hidden" name="view" value="search" />
			<input type="hidden" name="task" value="advancesearch" />
			<input type="hidden" name="Itemid" value="<?php echo CRoute::getItemId(); ?>" />
			<?php endif; ?>
			<input type="submit" class="btn btn-primary cFloat-R" value="<?php echo JText::_("COM_COMMUNITY_SEARCH_BUTTON_TEMP");?>">
			<?php
			if( $postresult && COwnerHelper::isCommunityAdmin() )
			{
			?>
			<a href="javascript:void(0);" id="memberlist-save"><?php echo JText::_('COM_COMMUNITY_MEMBERLIST_SAVE_SEARCH');?></a>
			<?php
			}
			?>
			<input type="hidden" id="key-list" name="key-list" value="" />
		</div>
	</div>
	<div id="criteriaList"></div>
</form>
</div>
<br />