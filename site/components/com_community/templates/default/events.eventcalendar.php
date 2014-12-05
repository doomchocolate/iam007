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

<div class="community-calendar app-box">
	<h3 class="app-box-header"><?php echo JText::_('COM_COMMUNITY_EVENTS_CAL');?></h3>
	<script>
		joms.jQuery(document).ready(function(){
			init_calendar();
		});

		//return date in - format
		function getDate(day){
			if(day < 10){
					day = '0'+day;
			}
			var raw = joms.jQuery('input.cal-month-year').val().split(';');
			return raw[0] + '-' + raw[1]+ '-' + day ;
		}

		//initialize all listener on calendar
		function init_calendar(){
			// date listener
			joms.jQuery('div#event>table>tbody>tr>td').click(function(){
				joms.jQuery('div#event>table>tbody>tr>td').each(function(){
					joms.jQuery(this).removeClass('selected');
				});
				if(joms.jQuery(this).html() > 0){ // to indicate this is a date
					joms.jQuery(this).addClass('selected');
					var date = getDate(joms.jQuery(this).html());
					date = date.split('-');
					var group_id = '<?php echo $group_id; ?>';
					joms.events.getDayEvent(date[2],date[1],date[0],group_id);
				}
			});

			//next or prev month listener
			joms.jQuery('span.calendar-next').click(function(){
				var raw = joms.jQuery('input.cal-month-year').val().split(';');
				var month = parseFloat(raw[1]) + 1;
				var year = parseFloat(raw[0]);
				if(month > 12){ //month > dec, change to 1(january), add 1 to yr
					month = 1;
					year = year + 1;
				}
				joms.jQuery('.events-list').html('');
				joms.events.getCalendar(month,year);
			});

			joms.jQuery('span.calendar-prev').click(function(){
				var raw = joms.jQuery('input.cal-month-year').val().split(';');
				var month = parseFloat(raw[1]) - 1;
				var year = parseFloat(raw[0]);
				if(month == 0){ //month > dec, change to 1(january), add 1 to yr
					month = 12;
					year = year - 1;
				}
				joms.jQuery('.events-list').html('');
				joms.events.getCalendar(month,year);
			});
		}
	</script>

	<div id="event">
		<?php
				$time = time();
				echo CCalendar::generate_calendar(date('Y', $time), date('n', $time));
		?>
	</div>
	<div class="community-calendar-result">
		<strong class="happening_title" style="display:none"><?php echo JText::_('COM_COMMUNITY_EVENTS_HAPPENING_TITLE'); ?> :</strong>
		<img class="loading-icon" style="display:none" src="<?php echo JURI::root(); ?>components/com_community/assets/ajax-loader.gif"/>
		<div class="small">
			<ul class="cEventNearby cResetList events-list" ></ul>
		</div>
	</div>
</div>