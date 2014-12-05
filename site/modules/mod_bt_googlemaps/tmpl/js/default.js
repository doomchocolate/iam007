
function initializeMap(configs, markersCode, stylesCode, boxStyles){
	var myLatlng;
	
	configs.width = configs.width =='auto' || configs.width =='100%' ? "100%":configs.width + 'px';
	configs.height = configs.height =='auto' || configs.height =='100%' ? "100%":configs.height + 'px';
	
	document.getElementById(configs.cavas_id).style.width=configs.width;
	document.getElementById(configs.cavas_id).style.height=configs.height;
	
	var markersJson = new BT.Base64().base64Decode(markersCode);
	var markers 	= JSON.decode(markersJson);
	var max 		= markers.length;	
	
	var stylesJson = new BT.Base64().base64Decode(stylesCode);
	var styles 	= JSON.decode(stylesJson);
	var infowindowData = Array();
	
	/** create map  */
	var map = createMapCenter(configs, markers ,styles, boxStyles);

/**
 * Create Map center
 * @param configs
 * @param markers
 * @returns {Latitude,Longitude}
 */
function createMapCenter(configs,markers,styles, boxStyles){
	if(configs.mapCenterType == 'address' && configs.mapCenterAddress == '' || configs.mapCenterType == 'coordinate' && configs.mapCenterCoordinate == '' ){
		if(markers.length == 0){
			var geocoder = new google.maps.Geocoder();
			geocoder.geocode( { 'address': 'A2DN3 Nguyen Khanh Toan, Cau Giay, Ha Noi' }, function(results, status) {
			  if (status == google.maps.GeocoderStatus.OK){
				mapCenter = results[0].geometry.location;
				return createMap(configs,styles,markers, mapCenter,boxStyles);
			  }else{
				  alert("Geocode map center was not successful for the following reason: " + status);
			  }
			 });
		}else {
			if(markers[0].markerType == 'address'){
				var geocoder = new google.maps.Geocoder();
				geocoder.geocode( { 'address': markers[0].markerValue }, function(results, status) {
				  if (status == google.maps.GeocoderStatus.OK){
					mapCenter = results[0].geometry.location;
					return createMap(configs,styles,markers, mapCenter,boxStyles);
				  }else{
					  alert("Geocode was not successful for the following reason: " + status + '! Map address: ' + markers[0].markerValue);
				  }
				})
			}else{
				mapCenterCoordinate = markers[0].markerValue.split(',');
				mapCenter = new google.maps.LatLng(mapCenterCoordinate[0],mapCenterCoordinate[1]);
				return createMap(configs,styles,markers, mapCenter,boxStyles);
			}
		}
	}else{
		if(configs.mapCenterType == 'address'){
			var geocoder = new google.maps.Geocoder();
			geocoder.geocode( { 'address': configs.mapCenterAddress }, function(results, status) {
			  if (status == google.maps.GeocoderStatus.OK){ 
				mapCenter = results[0].geometry.location;
				return createMap(configs,styles,markers, mapCenter,boxStyles);
			  }else{
				  alert("Geocode was not successful for the following reason: " + status + '! Map address: ' + configs.mapCenterAddress);
			  }
			})
		}else{
			mapCenterCoordinate = configs.mapCenterCoordinate.split(',');
			mapCenter = new google.maps.LatLng(mapCenterCoordinate[0],mapCenterCoordinate[1]);
			return createMap(configs,styles,markers, mapCenter,boxStyles);
		}
	}
}
/**
 * Create Map
 * @param Object configs
 * @param Array Object styles
 * @param Array Object markers
 * @returns {google.maps.Map}
 */
 
function createMap(configs,styles,markers, mapCenter,boxStyles){
	
	if(configs.enableStyle == 1 ||configs.enableStyle == '1'){
		var stylesArr = [];
		
		for(var j = 0; j < styles.length; j++)
		{	
			var style = {};
			style.stylers = [];
			if(styles[j].featureType!='all'){
				style.featureType = styles[j].featureType;

			}
			if(styles[j].elementType!='all'){
				style.elementType = styles[j].elementType;
			}
			if(styles[j].invertLightness == 'true'){
				style.stylers.push({"invert_lightness":true});
			}
			if(styles[j].visibility){
				style.stylers.push({"visibility":styles[j].visibility});
			}
			if(styles[j].mapColor){
				style.stylers.push({"color":styles[j].mapColor});
			}
			if(styles[j].weight){
				style.stylers.push({"weight":styles[j].weight});
			}
			if(styles[j].hue){
				style.stylers.push({"hue":styles[j].hue});
			}
			if(styles[j].saturation){
				style.stylers.push({"saturation":styles[j].saturation});
			}
			if(styles[j].lightness){
				style.stylers.push({"lightness":styles[j].lightness});
			}
			if(styles[j].gamma){
				style.stylers.push({"gamma":styles[j].gamma});
			}
			/*
			var style = {
				"featureType": styles[j].featureType,
				"elementType": styles[j].elementType,
				"stylers": [
					{ "visibility": styles[j].visibility },
					{ "invert_lightness": invertLightness },
					{ "color": styles[j].mapColor },
					{ "weight": styles[j].weight },
					{ "hue": styles[j].hue },
					{ "saturation": styles[j].saturation },
					{ "lightness": styles[j].lightness },
					{ "gamma": styles[j].gamma }
				]
			};
			*/
			stylesArr.push(style);
		}
		
		if(configs.createNewOrDefault == "applyDefault"){
			if(stylesArr.length != 0){
				var mapOptions = {
						zoom					: configs.zoom,
						zoomControl				: configs.zoomControl,
						scaleControl			: configs.scaleControl,
						mapTypeControl			: configs.mapTypeControl,
						panControl				: configs.panControl,
						streetViewControl		: configs.streetViewControl,
						overviewMapControl		: configs.overviewMapControl,
						draggable				: configs.draggable,
						disableDoubleClickZoom	: configs.disableDoubleClickZoom,
						scrollwheel				: configs.scrollwheel,
						center					: mapCenter,
						 mapTypeId				: configs.mapType,
						styles					: stylesArr
				    }
			}else{
				var mapOptions = {
						zoom					: configs.zoom,
						zoomControl				: configs.zoomControl,
						scaleControl			: configs.scaleControl,
						mapTypeControl			: configs.mapTypeControl,
						panControl				: configs.panControl,
						streetViewControl		: configs.streetViewControl,
						overviewMapControl		: configs.overviewMapControl,
						draggable				: configs.draggable,
						disableDoubleClickZoom	: configs.disableDoubleClickZoom,
						scrollwheel				: configs.scrollwheel,
						center					: mapCenter,
					    mapTypeId				: configs.mapType
				    }
			}
		}else{
			var mapOptions = {
					zoom					: configs.zoom,
					zoomControl				: configs.zoomControl,
					scaleControl			: configs.scaleControl,
					mapTypeControl			: configs.mapTypeControl,
					panControl				: configs.panControl,
					streetViewControl		: configs.streetViewControl,
					overviewMapControl		: configs.overviewMapControl,
					draggable				: configs.draggable,
					disableDoubleClickZoom	: configs.disableDoubleClickZoom,
					scrollwheel				: configs.scrollwheel,
					center					: mapCenter,
					mapTypeControlOptions	: {mapTypeIds: [configs.mapType, 'map_style']}
			    }
			var styledMap = new google.maps.StyledMapType(stylesArr,{name: configs.styleTitle});
		}
	}else{
		var mapOptions = {
				zoom					: configs.zoom,
				zoomControl				: configs.zoomControl,
				scaleControl			: configs.scaleControl,
				mapTypeControl			: configs.mapTypeControl,
				panControl				: configs.panControl,
				streetViewControl		: configs.streetViewControl,
				overviewMapControl		: configs.overviewMapControl,
				draggable				: configs.draggable,
				disableDoubleClickZoom	: configs.disableDoubleClickZoom,
				scrollwheel				: configs.scrollwheel,
				center					: mapCenter,
			    mapTypeId				: configs.mapType
		    }
	}
	var map = new google.maps.Map(document.getElementById(configs.cavas_id), mapOptions);
	
	// set style for map 
	if((configs.enableStyle == 1 ||configs.enableStyle == '1') && configs.createNewOrDefault == "createNew" ){
		//Associate the styled map with the MapTypeId and set it to display.
		map.mapTypes.set('map_style', styledMap);
		map.setMapTypeId('map_style');
	}
	
	// setting weather
	if(configs.weather){
		var weatherLayer = new google.maps.weather.WeatherLayer({
		  temperatureUnits: configs.temperatureUnit
		});
		weatherLayer.setMap(map);
		if(configs.cloud){
			var cloudLayer = new google.maps.weather.CloudLayer();
			cloudLayer.setMap(map);
		}
	}
	
	/** create marker in map*/
	 for (i = 0; i < markers.length; i++) {
	 	 getMarker(configs,markers[i],map ,boxStyles);
	 }
}

/**
 * Get Coordinate of maker and create marker
 * @param markerSource
 */
function getMarker(configs,markerSource, map,boxStyles){
	if(markerSource.markerType == 'coordinate'){
		coordinate = markerSource.markerValue.split(',');
		var pos = new google.maps.LatLng(coordinate[0], coordinate[1]);
		createMarker(configs,markerSource,map,pos,boxStyles);
	}else{
		var geocoder = new google.maps.Geocoder();
		geocoder.geocode({ 'address': markerSource.markerValue}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				var pos = results[0].geometry.location;
				createMarker(configs, markerSource, map, pos,boxStyles);
			}else{
				 alert("Geocode was not successful for the following reason: " + status + '! Map address: ' + markerSource.markerValue);
			}
		})
	}
}

/**
 * function create marker for map
 * @param ObjectJS marker
 * @param {google.maps.Map}
 * @param pos: Coordinate of marker
 */
function createMarker(configs,markerSource,map, pos,boxStyles){
	/** set option of marker */
    var marker, image, shadow;
	if(markerSource.markerIcon == ''){
		markerSource.markerIcon = configs.url + 'modules/mod_bt_googlemaps/tmpl/images/marker.png';
	}else{
		markerSource.markerIcon = configs.url + markerSource.markerIcon;
	}
	
	if(markerSource.markerShadowImage == ''){
		markerSource.markerShadowImage = configs.url + 'modules/mod_bt_googlemaps/tmpl/images/shadow.png';
	}else{
		markerSource.markerShadowImage = configs.url + markerSource.markerShadowImage;
	}
	image = new google.maps.MarkerImage(markerSource.markerIcon);		
	shadow = new google.maps.MarkerImage(
		  markerSource.markerShadowImage,
		  new google.maps.Size(41,32),
		  new google.maps.Point(0,0),
		  new google.maps.Point(11,32)
	);
	marker = new google.maps.Marker({
		position: pos,
		map: map,
		shadow: shadow,
		icon: image,
		title: markerSource.markerTitle						
	});	
	/** create infoWindow */
	if(configs.enableCustomInfoBox == 1 ||configs.enableCustomInfoBox == '1'){
		var pixelOffset = configs.boxPosition.split(',');
		if(configs.closeBoxImage == ''){ configs.closeBoxImage = 'modules/mod_bt_googlemaps/tmpl/images/close.gif';}
		var infoBoxOption = {
				content: markerSource.markerInfoWindow,
				disableAutoPan: true,
	            maxWidth: 0,
	            pixelOffset: new google.maps.Size(Number(pixelOffset[0]),Number(pixelOffset[1])),                     
			    zIndex: null,
			    boxStyle: boxStyles ,
			    closeBoxMargin: configs.closeBoxMargin,
			    closeBoxURL: configs.url + '/'+ configs.closeBoxImage,
			    infoBoxClearance: new google.maps.Size(1, 1),
			    isHidden: false,
			    pane: "floatPane",
			    enableEventPropagation: false
		}
		
		var infowindow = new InfoBox(infoBoxOption);
	}else{
		var infowindow = new google.maps.InfoWindow({
	    	content: markerSource.markerInfoWindow
		});
	}
	infowindowData.push(infowindow);
	if(markerSource.markerShowInfoWindow == 1){
		infowindow.open(map,marker);
	}
	google.maps.event.addListener(marker, 'click', function() {				
			infowindowData.each(function(el){
				el.close();
			});
			infowindow.open(map,marker);
			//map.setCenter(pos);
	});
}
}