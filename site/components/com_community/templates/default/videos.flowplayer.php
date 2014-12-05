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

$playerFile		= JURI::root() . 'components/com_community/assets/flowplayer/flowplayer-3.2.7.swf';
$playerScript	= JURI::root() . 'components/com_community/assets/flowplayer/flowplayer-3.2.6.min.js';
$playerPlugin	= JURI::root() . 'components/com_community/assets/flowplayer/flowplayer.pseudostreaming-3.2.7.swf';
$playerControl	= JURI::root() . 'components/com_community/assets/flowplayer/flowplayer.controls-3.2.5.swf';

/*** End Configuration ***/

// Switching of the Pure Object Tag or Default Method
// Pure Obeject Tag is needed to play the video on cWindow since it
// doesn't load the javascript.
if( $switch == 'pureObjectTag'){ ?>

<object id="flowplayer" width="<?php echo $video->getWidth(); ?>" height="<?php echo $video->getHeight(); ?>" 
	data="<?php echo $playerFile; ?>" 
	type="application/x-shockwave-flash">
	
	<param name="movie" value="<?php echo $playerFile; ?>" /> 
	<param name="allowfullscreen" value="true" />
	<param name="flashvars" 
		value="config={
			'playlist':[
				{'url':'<?php echo $video->getThumbnail(); ?>',
				'scaling':'scale'},
				{'url':'<?php echo $video->getFlv(); ?>',
				'title':'<?php echo CString::str_ireplace("'", "", $video->title); ?>',
				'autoPlay':false,
				'autoBuffering':true,
				'provider':'lighttpd',
				'scaling':'scale'}
			],
			'plugins':{'lighttpd':{'url':'<?php echo $playerPlugin; ?>',
			'queryString':'%3Ftarget%3D%24%7Bstart%7D'},
			'controls':{'url':'<?php echo $playerControl; ?>'}},
			'playerId':'player',
			'clip':{}}">
</object>

<?php } else { ?>

<script type="text/javascript" src="<?php echo $playerScript; ?>"></script>

<div 
	id="player" 
	style="
		width:<?php echo $video->getWidth(); ?>px;
		height:<?php echo $video->getHeight(); ?>px;
		display:block;
		margin:0 auto;
		"
></div>

<script type="text/javascript">
	flowplayer("player", {src: "<?php echo $playerFile; ?>", wmode:'opaque' },
		{
			streamingServer: 'lighttpd',

			playlist: [
				{
					url: '<?php echo $video->getThumbnail(); ?>', 
                	scaling: 'scale'
				},
				{
					url: '<?php echo $video->getFlv(); ?>',
			    	title: '<?php echo CString::str_ireplace("'", "", $video->title); ?>',
			        autoPlay: false,
			        autoBuffering: true,
			        provider: 'lighttpd',
			        scaling: "scale"
				} 
			],

		    plugins: {
		        lighttpd: {
		            url: '<?php echo $playerPlugin; ?>',
		            queryString: escape('?target=${start}')
		        },
		        controls: {
		        	url: '<?php echo $playerControl; ?>'
		        }
		    }
		    
		}
	);
</script>

<?php }; ?>