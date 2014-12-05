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
<div class="cLayout cSearch Events">

	<?php
	if($posted)
	{
	?>
	<div class="cSearch-Result">
		<p>
			<span>
				<?php echo (!empty($search)) ? JText::sprintf( 'COM_COMMUNITY_SEARCH_RESULT' , $search ) : ''; ?>
			</span>
			<span class="cFloat-R">
				<?php echo JText::sprintf( (CStringHelper::isPlural($eventsCount)) ? 'COM_COMMUNITY_EVENTS_SEARCH_RESULT_TOTAL_MANY' : 'COM_COMMUNITY_EVENTS_SEARCH_RESULT_TOTAL' , $eventsCount ); ?>
			</span>
		</p>

		<?php echo $eventsHTML; ?>

	</div>
	<?php
	}
	?>

	<?php
		if( $searchLinks )
		{
		?>
		<div class="cSearch-Jumper top-gap bottom-gap">
			<?php
			echo JText::_('COM_COMMUNITY_SEARCH_FOR');
			foreach ($searchLinks as $key => $value)
			{
			?>
			<a href="<?php echo $value; ?>"><?php echo ucwords($key); ?></a>
			<?php
			}
			?>
		</div>
		<?php
		}
	?>

	<form name="jsform-events-search" method="get" action="" class="form-horizontal top-gap">

	<?php if(!empty($beforeFormDisplay)){ ?>
	<?php echo $beforeFormDisplay; ?>
	<?php } ?>

  <div class="control-group">
    <label class="control-label" for="search"><?php echo JText::_('COM_COMMUNITY_SEARCH_FOR'); ?></label>
    <div class="controls">
      <input type="text"  name="search" class="span3" value="<?php echo $this->escape($search); ?>" />
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="catid"><?php echo JText::_('COM_COMMUNITY_EVENTS_CATEGORY');?></label>
    <div class="controls">
			<select name="catid" id="catid" class="span2">
				<option value="0" selected></option>
				<?php
				foreach( $categories as $category )
				{
				?>
					<option value="<?php echo $category->id; ?>" <?php if( $category->id == $catId ) { ?>selected<?php } ?>><?php echo JText::_( $this->escape($category->name) ); ?></option>
				<?php
				}
				?>
			</select>
			<span class="help-block small"><?php echo JText::_('COM_COMMUNITY_EVENTS_CATEGORY_TIPS');?></span>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label"><?php echo JText::_('COM_COMMUNITY_EVENTS_START_DATE'); ?></label>
	<label for="startdate"></label>
    <div class="controls">
		<input type="text" name="startdate" id="startdate" style="width:auto;" size="10" class="required input-medium" readonly/>						
		<script>
			joms.jQuery("#startdate" ).datepicker
				({
					minDate: 0,
					changeMonth: true,
					changeYear: true,
					dateFormat: 'yy-mm-dd',
					onClose: function ( selectedDate ) {									
						var startDate = new Date(selectedDate);
						var endDate = new Date(joms.jQuery('#enddate').datepicker('getDate'));										
						if ( startDate > endDate ) {											
							joms.jQuery(joms.jQuery('#enddate').datepicker('setDate',selectedDate)); /* reset endDate same as startDate */
							joms.jQuery(joms.jQuery('#enddate').datepicker('option','minDate',selectedDate)); /* and endDate can't past of startDate */
						}
					}									
				});
		</script>
		<span class="help-block small"><?php echo JText::_('COM_COMMUNITY_EVENTS_START_TIME_TIPS'); ?></span>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label"><?php echo JText::_('COM_COMMUNITY_EVENTS_END_DATE'); ?></label>
	<label for="enddate"></label>
    <div class="controls">
		<input type="text" name="enddate" id="enddate" style="width:auto;" size="10" class="required input-medium" readonly/>												
		<script>
			joms.jQuery("#enddate" ).datepicker
				({
					minDate: 0,
					changeMonth: true,
					changeYear: true,
					dateFormat: 'yy-mm-dd',								
				});
		</script>
		<span class="help-block small"><?php echo JText::_('COM_COMMUNITY_EVENTS_END_TIME_TIPS'); ?></span>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label"><?php echo JText::_('COM_COMMUNITY_EVENTS_FROM'); ?></label>
    <div class="controls">

			<script type="text/javascript">
				joms.jQuery('document').ready(function()
					{
				    validateFormValue();
				    // Check if the browsers support W3C Geolocation API
				    // If yes, show the auto-detect link
				    if( navigator.geolocation )
				    {
					    joms.jQuery('#proto__detectButton').show();
				    }
				});
				function get_current_location()
				{
				    joms.jQuery('#proto__currentLocationValue').hide();
				    joms.jQuery('#proto__detectButton').hide();
				    joms.jQuery('#proto__detectingCurrentLocation').show();
				    navigator.geolocation.getCurrentPosition(function(location)
				    {
					var lat	=   location.coords.latitude;
					var lng	=   location.coords.longitude;
					// Reverse Geocoding
					geocoder    =   new google.maps.Geocoder();
					var latlng  =   new google.maps.LatLng( lat, lng );
					geocoder.geocode({'latLng': latlng}, function(results, status){
					    if( status == google.maps.GeocoderStatus.OK ){
						if ( results[4] ){
						    var newLocation = results[4].formatted_address;
						    if( newLocation.length != 0 )
						    {
					    joms.jQuery("#proto_selectRadius").removeAttr("disabled");
					    joms.jQuery("#distance_unit1").removeAttr("disabled");
					    joms.jQuery("#distance_unit2").removeAttr("disabled");
						    }
						    joms.jQuery("#proto__detectingCurrentLocation").hide();
						    joms.jQuery("#proto__currentLocationValue").attr("value", newLocation).show();
						}
					    } else {
						alert("Geocoder failed due to: " + status);
					    }
					});
					joms.jQuery("#proto__detectButton").show();
				    });
				}
				function validateFormValue()
				{
				    var input = joms.jQuery("#proto__currentLocationValue").val();
				    if( input.length != 0 )
				    {
					joms.jQuery("#proto_selectRadius").removeAttr("disabled");
					joms.jQuery("#distance_unit1").removeAttr("disabled");
					joms.jQuery("#distance_unit2").removeAttr("disabled");
				    }
				    else
				    {
					joms.jQuery("#proto_selectRadius").attr("disabled", "disabled");
					joms.jQuery("#distance_unit1").attr("disabled", "disabled");
					joms.jQuery("#distance_unit2").attr("disabled", "disabled");
				    }
				}
			</script>

			<div class="input-append">
				<input type="text" name="location" id="proto__currentLocationValue" class="span2" value="<?php echo $this->escape($advance['fromlocation']); ?>" onkeyup="validateFormValue();" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_SEARCH_FROM_TIPS'); ?>" />
				<a id="proto__detectButton" href="javascript: void(0)" style="display: none;" onclick="get_current_location();" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_AUTODETECT_LOCATION'); ?>" class="btn"><?php echo JText::_('COM_COMMUNITY_EVENTS_AUTODETECT_LOCATION'); ?></a>
			</div>

			<span id="proto__detectingCurrentLocation" class="loading pull-left"></span>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label"><?php echo JText::_('COM_COMMUNITY_EVENTS_WITHIN'); ?></label>
    <div class="controls">

			<select id="proto_selectRadius" class="span3" name="radius" class="required" disabled="disabled" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_WITHIN_TIPS'); ?>">
				<option value="<?php echo null; ?>" <?php if( empty($advance['radius']) ){ ?>selected<?php } ?>></option>
				<option value="<?php echo COMMUNITY_EVENT_WITHIN_5; ?>" <?php if( $advance['radius'] == COMMUNITY_EVENT_WITHIN_5 ){ ?>selected<?php } ?>><?php echo COMMUNITY_EVENT_WITHIN_5; ?></option>
				<option value="<?php echo COMMUNITY_EVENT_WITHIN_10; ?>" <?php if( $advance['radius'] == COMMUNITY_EVENT_WITHIN_10 ){ ?>selected<?php } ?>><?php echo COMMUNITY_EVENT_WITHIN_10; ?></option>
				<option value="<?php echo COMMUNITY_EVENT_WITHIN_20; ?>" <?php if( $advance['radius'] == COMMUNITY_EVENT_WITHIN_20 ){ ?>selected<?php } ?>><?php echo COMMUNITY_EVENT_WITHIN_20; ?></option>
				<option value="<?php echo COMMUNITY_EVENT_WITHIN_50; ?>" <?php if( $advance['radius'] == COMMUNITY_EVENT_WITHIN_50 ){ ?>selected<?php } ?>><?php echo COMMUNITY_EVENT_WITHIN_50; ?></option>
			</select>

		<div class="help-block">
			<label class="radio inline">
				<input id="distance_unit1" type="radio" name="unit" value="<?php echo COMMUNITY_EVENT_UNIT_KM; ?>" disabled="disabled" <?php if( $unit === COMMUNITY_EVENT_UNIT_KM ){ ?>checked<?php } ?>> <?php echo JText::_('COM_COMMUNITY_EVENTS_KILOMETER'); ?>
			</label>

			<label class="radio inline">
				<input id="distance_unit2" type="radio" name="unit" value="<?php echo COMMUNITY_EVENT_UNIT_MILES; ?>" disabled="disabled" <?php if( $unit === COMMUNITY_EVENT_UNIT_MILES || empty($unit) ){ ?>checked <?php } ?>> <?php echo JText::_('COM_COMMUNITY_EVENTS_MILES'); ?>
			</label>
		</div>

    </div>
  </div>

	<?php if(!empty($afterFormDisplay)){ ?>
	<?php echo $afterFormDisplay; ?>
	<?php } ?>

  <div class="control-group">
    <label class="control-label"></label>
    <div class="controls">
      <input type="submit" value="<?php echo JText::_('COM_COMMUNITY_SEARCH_BUTTON');?> <?php echo JText::_('COM_COMMUNITY_EVENTS');?>" class="btn btn-primary" />
    </div>
  </div>

		<?php echo JHTML::_( 'form.token' ); ?>
		<input type="hidden" value="com_community" name="option" />
		<input type="hidden" value="events" name="view" />
		<input type="hidden" value="search" name="task" />
		<input type="hidden" value="<?php echo CRoute::getItemId();?>" name="Itemid" />
        <input type="hidden" name="posted" value="1">
	</form>

</div>