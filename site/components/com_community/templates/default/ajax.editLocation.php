<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
?>

<div id="editlocation">
	<div id="editlocation-map" data-lat="<?php echo $latitude ?>" data-lng="<?php echo $longitude ?>" style="height:110px"></div>
	<div id="editlocation-selector" style="padding-top: 10px">
		<input type="text" value="<?php echo $address ?>" style="display:block; width: 100%; -webkit-box-sizing:border-box; -moz-box-sizing:border-box; -box-sizing:border-box">
		<ul class="joms-postbox-locations" style="overflow: hidden; width: auto; height: 150px;">
			<li><em><?php echo JText::_('COM_COMMUNITY_LOCATING_PLEASE_WAIT');?></em></li>
		</ul>
	</div>
</div>

<script>
joms.jQuery(function( $ ) {
	if ( window.google && google.map )
		return;

	var el = $('#editlocation-map'),
		lat = el.data('lat'),
		lng = el.data('lng'),
		position = new google.maps.LatLng( lat, lng ),
		options;

	options = {
		center: position,
		zoom: 14,
		mapTypeId: google.maps.MapTypeId.ROADMAD,
		mapTypeControl: false,
		disableDefaultUI: true,
		draggable: false,
		scaleControl: false,
		scrollwheel: false,
		navigationControl: false,
		streetViewControl: false,
		disableDoubleClickZoom: true
	};

	var map = new google.maps.Map( el[0], options );
	var marker = new google.maps.Marker({
		draggable: false,
		map: map
	});

	marker.setPosition( position );
	map.panTo( position );

	var ct = $('#editlocation-selector'),
		input = ct.children('input'),
		list = ct.children('ul');

	// scrollable
	list.slimScroll({
		height: '150px',
		alwaysVisible: true
	});

	// find nearby locations
	var service = new google.maps.places.PlacesService( map );
	var request = {
		location: position,
		radius: 100
	};

	service.nearbySearch( request, function( results, status ) {
		if ( status != google.maps.places.PlacesServiceStatus.OK ) {
			list.html( '<li><em>Unable to find your nearest location.</em></li>' );
			return;
		}

		if ( !results || !results.length ) {
			list.html( '<li><em>Unable to find your nearest location.</em></li>' );
			return;
		}

		var html = '';
		for ( var i = 0, loc; i < results.length; i++ ) {
			loc = results[i];
			html += [
				'<li data-lat="', loc.geometry.location.lat(), '" data-lng="', 
				loc.geometry.location.lng(), '" style="line-height:18px;padding:4px 0;cursor:pointer"><strong>', 
				loc.name, '</strong><br><span>',  loc.vicinity, '</span></a></li>'
			].join('');
		}

		input.attr( 'placeholder', 'Select your location.' );
		list.html( html );
	});

	list.on( 'click', 'li', function() {
		var elem = $( this ),
			data = elem.data(),
			name = elem.find('strong').text(),
			position;

		if ( data.lat && data.lng ) {
			input.val( name );
			el.data( 'lat', data.lat );
			el.data( 'lng', data.lng );
			position = new google.maps.LatLng( data.lat, data.lng );
			marker.setPosition( position );
			map.panTo( position );
		}

	});

	// adjust window size
	cWindowResize();

});
</script>