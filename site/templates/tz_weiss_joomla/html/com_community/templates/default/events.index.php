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

<div id="community-events-wrap" class="cIndex">

	<?php if( $featuredHTML ) { ?>
		<?php echo $featuredHTML; ?><!--call events.featured.php -->
	<?php } ?>

	<div class="row-fluid">
		<div class="span8">
			<div class="cMain">
				<!-- EVENT SORTINGS-->
				<?php echo $sortings; ?>
				<!-- EVENT LISTINGS -->
				<?php echo $eventsHTML;?>
			</div>
		</div>
		<div class="span4">
			<div class="cSidebar">
				<!-- START nearby event search -->
				<?php echo $this->view('events')->modEventNearby(); ?>
				<!-- Categories -->
				<?php
				if ( $index && $handler->showCategories() ) :
					echo $this->view('events')->modEventCategories($category, $categories);
				endif;
				?>
				<!-- START event calendar -->
				<?php echo $this->view('events')->modEventCalendar(); ?>
			</div>
		</div>
	</div>

	<script type="text/javascript">
	joms.jQuery(document).ready(function(){
			// Get the Current Location from cookie
			var location =	joms.geolocation.getCookie( 'currentLocation' );
			if( location.length != 0 )
			{
					joms.jQuery('#showNearByEventsLoading').show();
					joms.geolocation.showNearByEvents( location );
			}
			// Check if the browsers support W3C Geolocation API
			// If yes, show the auto-detect link
			if( navigator.geolocation )
			{
				joms.jQuery('#autodetectLocation').show();
			}
	});
	</script>

</div>