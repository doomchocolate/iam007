<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// no direct access
defined('_JEXEC') or die('Restricted access');

require_once( JPATH_ROOT .'/components/com_community/libraries/core.php');

if(!class_exists('plgCommunityFriendsLocation'))
{
	class plgCommunityFriendsLocation extends CApplications
	{
		var $_user		= null;
		var $name		= "Friend's Location";
		var $_name 		= 'friendslocation';

	    function plgCommunityFriendsLocation(& $subject, $config)
		{
			$this->_user	= CFactory::getRequestUser();
			parent::__construct($subject, $config);
	    }

		private function _getLocationFieldId($town_field_code, $state_field_code, $country_field_code)
		{
			$db = JFactory::getDBO();
			$sql = "SELECT
							".$db->quoteName("fieldcode").",
							".$db->quoteName("id")."
					FROM
							".$db->quoteName("#__community_fields")."
					WHERE
							".$db->quoteName("fieldcode")." IN (".$db->Quote($town_field_code).", ".$db->Quote($state_field_code).", ".$db->Quote($country_field_code).")";

			$db->setQuery($sql);
			$row = $db->loadObjectList();

			return $row;
		}

		private function _getFriends($userid, $limit)
		{
			$db = JFactory::getDBO();

			$query	= 'SELECT ' . $db->quoteName( 'connect_from' ) . ' AS ids '
					. 'FROM ' . $db->quoteName( '#__community_connection' ) . ' '
					. 'WHERE ' . $db->quoteName( 'connect_to' ) . '=' . $db->Quote( $userid ) . ' '
					. 'AND ' . $db->quoteName( 'status' ) . '=' . $db->Quote( 1 );

			if($limit != 0)
			{
				$query .=" LIMIT ".$limit;
			}

			$db->setQuery($query);

			$friends	= $db->loadColumn();

			$result		= array();
			$my			= CFactory::getUser();
			$model		= CFactory::getModel( 'Friends' );
			$userFriends	= $model->getFriendIds( $userid );

			foreach( $friends as $friendId )
			{
				$user	= CFactory::getUser( $friendId );
				$params	= $user->getParams();
				$privacy	= $params->get( 'privacyProfileView' );

				if( $my->id == $userid )
				{
					$result[]	= $friendId;
				}

				if( $privacy == 0 )
				{
					$result[]	= $friendId;
				}
				else if( $privacy == 20 && $my->id != 0 )
				{
					$result[]	= $friendId;
				}
				else if( $privacy == 30 && in_array( $my->id , $userFriends ) )
				{
					$result[]	= $friendId;
				}
			}

			return array_unique($result);
		}


		/**
		 *
		 */
		private function _getFriendsLocation($friends, $town_field_id, $state_field_id, $country_field_id, $show_karma)
		{
			require_once( JPATH_ROOT .'/components/com_community/libraries/core.php');

			$db = JFactory::getDBO();
			$friends_id = implode(',', $friends);

			$sql = 'SELECT 	a.'.$db->quoteName('user_id').',
				      		a.'.$db->quoteName('value').' AS country,
				      		b.'.$db->quoteName('value').' AS state,
							c.'.$db->quoteName('value').'	AS town'
				    .' FROM '.$db->quoteName('#__community_fields_values').' AS a'
				    .' LEFT JOIN '.$db->quoteName('#__community_fields_values').' AS b'
				    .' ON a.'.$db->quoteName('user_id').'=b.'.$db->quoteName('user_id').' AND b.'.$db->quoteName('field_id').' = '.$db->Quote($state_field_id)
				    .' LEFT JOIN '.$db->quoteName('#__community_fields_values').' AS c'
				    .' ON a.'.$db->quoteName('user_id').'=c.'.$db->quoteName('user_id').' AND c.'.$db->quoteName('field_id').' = '.$db->Quote($town_field_id)
				    .' WHERE a.'.$db->quoteName('field_id').' = '.$db->Quote($country_field_id)
				    .' AND	a.'.$db->quoteName('user_id').' IN ('.$friends_id.')';

			$db->setQuery($sql);
			$row = $db->loadObjectList();

			// preload all users
			$CFactoryMethod = get_class_methods('CFactory');
			if(in_array('loadUsers', $CFactoryMethod))
			{
				$uids = array();
				foreach($row as $m)
				{
					$uids[] = $m->user_id;
				}
				CFactory::loadUsers($uids);
			}

			$location = JArrayHelper::toObject($location);
			$location =  new stdClass();
			foreach($row as $data){
				$user = CFactory::getUser($data->user_id);

				$location->{$data->town.", ".$data->state.", ".$data->country} = new stdClass();
				$location->{$data->town.", ".$data->state.", ".$data->country}->{$data->user_id} = new stdClass();
				$location->{$data->town.", ".$data->state.", ".$data->country}->{$data->user_id}->username = $user->getDisplayName();
				$location->{$data->town.", ".$data->state.", ".$data->country}->{$data->user_id}->avatar = $user->getThumbAvatar();
				$location->{$data->town.", ".$data->state.", ".$data->country}->{$data->user_id}->link = CRoute::_('index.php?option=com_community&view=profile&userid='.$data->user_id);

				switch($show_karma){
					case 1:
						$location->{$data->town.", ".$data->state.", ".$data->country}->{$data->user_id}->karma_points = "<div><img src='".CUserPoints::getPointsImage($user)."' alt=''/></div>";
						break;
					case 2:
						$location->{$data->town.", ".$data->state.", ".$data->country}->{$data->user_id}->karma_points = "<div><small>".JText::_('MOD_TOPMEMBERS_POINTS').": ".$user->_points."</small></div>";
						break;
					default :
						$location->{$data->town.", ".$data->state.", ".$data->country}->{$data->user_id}->karma_points = "<div></div>";
				}
			}

			return $location;
		}

	 	/**
	 	 *
	 	 */
		function onProfileDisplay()
		{
			JPlugin::loadLanguage( 'plg_community_friendslocation', JPATH_ADMINISTRATOR );

			$config	= CFactory::getConfig();

			// Attach CSS
			$document	= JFactory::getDocument();
			$css		= JURI::base() . 'plugins/community/friendslocation/friendslocation/style.css';
			$document->addStyleSheet($css);

            $user = CFactory::getRequestUser();
            $userid = $user->id;
            $def_limit 		= $this->params->get('count', 0);
			$mapkey 		= $this->params->get('mapkey', '');
			$width 			= $this->params->get('width', '480');		// @todo: remove
			$height 		= $this->params->get('height', '340');

			$show_karma = 0;
			if($config->get('enablekarma'))
			{
				$show_karma = $this->params->get('show_karma', '1');
			}

			$mouse_scroll_zoom = $this->params->get('mouse_scroll_zoom', '1');
			$continuous_zoom = $this->params->get('continuous_zoom', '1');

			$column = $this->_getLocationFieldId($this->params->get("town_field_code", "FIELD_CITY"), $this->params->get("state_field_code", "FIELD_STATE"), $this->params->get("country_field_code", "FIELD_COUNTRY"));

			$town_field_id = "";
			$country_field_id = "";

			foreach($column as $field){
				switch($field->fieldcode){
					case $this->params->get("town_field_code", "FIELD_CITY"):
						$town_field_id = $field->id;
						break;
					case $this->params->get("country_field_code", "FIELD_COUNTRY"):
						$country_field_id = $field->id;
						break;
					case $this->params->get("state_field_code", "FIELD_STATE"):
						$state_field_id = $field->id;
						break;
					default:
						break;
				}
			}

			if(!empty($town_field_id) && !empty($state_field_id) && !empty($country_field_id)){

				$mainframe = JFactory::getApplication();
				$caching = $this->params->get('cache', 1);
				if($caching)
				{
					$caching = $mainframe->getCfg('caching');
				}

				$layout = $this->getLayout();

				$cache = JFactory::getCache('plgCommunityFriendsLocation');
				$cache->setCaching($caching);
				$content = $this->_getFriendsLocationHTML($mapkey, $width, $height, $show_karma, $town_field_id, $state_field_id, $country_field_id, $userid, $def_limit, $layout);

			} else {
				$content = "<div>".JText::_("PLG_FRIENDSLOCATION_FIELD_CODE_NOT_FOUND")."</div>";
			}

			return $content;
		}

		private function _getFriendsLocationHTML($mapkey, $width, $height, $show_karma, $town_field_id, $state_field_id, $country_field_id, $userid, $def_limit, $layout)
		{
			ob_start();

			$friends = $this->_getFriends($userid, $def_limit);
			if(!empty($friends))
			{
				$friends_location = $this->_getFriendsLocation($friends, $town_field_id, $state_field_id, $country_field_id, $show_karma);
				$script='
			    	var geocoder = null;
			    	var map = null;
			    	var bounds = null;
			    	var baseIcon = null;
				';

				// Convert to array of address
				$fl			= array();
				$fLocation	= array();
				if(!empty($friends_location))
				{
					foreach($friends_location as $key => $val){
						$val->address = $key;
						$fl[] = $val;
					}

					// reformat $friends_location
					foreach($fl as &$val){

						$obj = new stdclass();
						foreach($val as $key=>$value){

							if(is_object($value)){
								$value->userid		= $key;
								$obj->userdetails	= $value;
							}else{
								$obj->address		= $value;
							}

						}

						$fLocation[] = $obj;
					}
				}

				$json = new Services_JSON();
				$addr = $json->encode($fLocation);

				// Ourput the list of address
				$script.='var address = '. $addr;

                                $scheme = JUri::getInstance()->getScheme();
				$script .='

					function plgFriendsLocLoadScript() {
						var script = document.createElement("script");
						script.type = "text/javascript";
						script.src = "'.$scheme.'://maps.google.com/maps/api/js?sensor=false&callback=plgFriendsLocInitializeMap";
						document.body.appendChild(script);
					}

					function plgFriendsLocInitializeMap()
					{

						var myLatlng = new google.maps.LatLng(-34.397, 150.644);
						var myOptions = {
							mapTypeId: google.maps.MapTypeId.ROADMAP
						}
						map 	 = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
						bounds 	 = new google.maps.LatLngBounds();
						geocoder = new google.maps.Geocoder();
						retry	 = 10;
						// Geocode and add marker on the map
						joms.jQuery(address).each(function(index) {
						    plgFriendsLocCodeAddress(address[index].address,address[index].userdetails,retry);
						});

						return;
						baseIcon = new GIcon(G_DEFAULT_ICON);
						baseIcon.iconSize = new GSize(20, 34);
						baseIcon.iconAnchor = new GPoint(9, 34);
						baseIcon.infoWindowAnchor = new GPoint(9, 2);
					}


					function plgFriendsLocCodeAddress(address,userdetails,retry) {
					    if (retry >= 0)
						{
							geocoder.geocode( { "address": address}, function(results, status) {
							  if (status == google.maps.GeocoderStatus.OK) {

								var contentString = "<div style=\'float: left; width: 32px; margin-top: 4px;\'><img src=\'"+userdetails.avatar+"\' width=40 height=40 alt=\'\'></div><div style=\'margin-left: 45px; margin-top: 3px;overflow:hidden;\'><a href=\'"+userdetails.link+"\'>"+userdetails.username+"</a>"+userdetails.karma_points+"<div style=\'clear: both; height: 1px;\'>&nbsp;</div></div>";
								var infowindow = new google.maps.InfoWindow({
									content: contentString
								});

								var marker = new google.maps.Marker({
									map: map,
									position: results[0].geometry.location
								});

								google.maps.event.addListener(marker, "click", function() {
								  infowindow.open(map,marker);
								});

								// Extends the map bounds
								var point = new google.maps.LatLng(
									results[0].geometry.location.lat,
									results[0].geometry.location.lng
									);

								bounds.extend(results[0].geometry.location);
								map.fitBounds(bounds);
								map.panToBounds(bounds);
							  } else if (status == google.maps.GeocoderStatus.OVER_QUERY_LIMIT){
									retry = retry - 1;
									setTimeout(function(){plgFriendsLocCodeAddress(address,userdetails,retry);
															address=null;
															userdetails=null;
															retry=null;},1000);
								}else {
								//alert("Geocode was not successful for the following reason: " + status);
							  }
							});
						}
					}

					function addAddressToMap(response){
						if (!response || response.Status.code != 200){
							//alert("Sorry, we were unable to geocode that address");
						}else{
							var total_ppl = 0;
							for (var l in address[response.name]){
								total_ppl++;
							}

							var marker_temp = total_ppl + "'.JText::_('PLG_FRIENDSLOCATION_FRIEND_STAY').'<br />";

							for (var j in address[response.name]) {
								marker_temp += "<div style=\'float: left; width: 32px; margin-top: 4px;\'><img src=\'"+address[response.name][j]["avatar"][0]+"\' width=40 height=40 alt=\'\'></div><div style=\'margin-left: 45px; margin-top: 3px;\'><a href=\'"+address[response.name][j]["link"][0]+"\'>"+address[response.name][j]["username"][0]+"</a>"+address[response.name][j]["karma_points"][0]+"<div style=\'clear: both; height: 1px;\'>&nbsp;</div></div>";
							}

							place = response.Placemark[0];
							point = new GLatLng(place.Point.coordinates[1],
							                    place.Point.coordinates[0]);

		 					var marker = new GMarker(point);
		 					//bounds.extend(point);
		 					GEvent.addListener( marker, "click" , function(){
		 						marker.openInfoWindowHtml( "<B>'.JText::_('PLG_FRIENDSLOCATION_LOCATION').'</B> : " + response.Placemark[0].address + "<br /><br />" + marker_temp);
							 } );

							map.addOverlay(marker);

							if(auto_zoom == "1"){
				 				//map.setZoom(map.getBoundsZoomLevel(bounds));
				 			}

				 			if(auto_center == "1"){
				 				//map.setCenter(bounds.getCenter());
				 			}
						}
					}

				    joms.jQuery(document).ready( function() {
				    	plgFriendsLocLoadScript();
					});
					';

				$document	= JFactory::getDocument();
				$document->addScriptDeclaration($script);


				$content = '<div id="map_canvas" style="width:100%; height:'.$height.'px"></div>';
				echo $content;
			}
			else
			{

			?>
	            <div id="application-flocations">
	                <div><?php echo JText::_("PLG_FRIENDSLOCATION_NO_FRIENDS_YET")?></div>
	            </div>
			<?php
			}

			$html = ob_get_contents();
			@ob_end_clean();

			return $html;
		}

		function onAppDisplay()
		{
			ob_start();
			$limit=0;
			$html= $this->onProfileDisplay($limit);
			echo $html;

			$content	= ob_get_contents();
			ob_end_clean();

			return $content;

		}

	}
}
