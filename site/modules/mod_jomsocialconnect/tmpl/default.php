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
<div class="<?php echo $params->get('moduleclass_sfx'); ?>">
<?php
// Only display login form here.
if( $my->id == 0 )
{
	if( $config->get('fbconnectkey') && $config->get('fbconnectsecret') )
	{
?>
	<div id="fb-root"></div>
	<script type="text/javascript">
	var count	= 1;
	window.fbAsyncInit = function() {
	    FB.init({appId: '<?php echo $config->get('fbconnectkey');?>', status: true, oauth:true, cookie: true, xfbml: true});
	
	    /* All the events registered */
	    FB.Event.subscribe('auth.login', function(response) {
	    	//if( count == 1 )
				//joms.connect.update();
				
			//count++;
	    });
	};
	(function() {
	    var e = document.createElement('script');
	    e.type = 'text/javascript';
	    e.src = document.location.protocol +
	        '//connect.facebook.net/en_US/all.js';
	    e.async = true;
	    document.getElementById('fb-root').appendChild(e);
	}());
	</script>
	<fb:login-button autologoutlink="true" onlogin="joms.connect.update();" scope="read_stream,publish_stream,offline_access,email,user_birthday,status_update,user_status"><?php echo JText::_('COM_COMMUNITY_SIGN_IN_WITH_FACEBOOK');?></fb:login-button>
<?php
	}
	else
	{
?>
	<div>
		<?php echo JText::_('Facebook API keys need to be provided to have Facebook connect work'); ?>
	</div>
<?php
	}
}
else
{
	if( $fbUser )
	{
?>
	<div id="fb-root"></div>
	<script type="text/javascript">
	var count	= 1;
	window.fbAsyncInit = function() {
	    FB.init({appId: '<?php echo $config->get('fbconnectkey');?>', status: true, oauth:true, cookie: true, xfbml: true});
	         FB.Event.subscribe('auth.logout', function(response) {
	         	document.location.href='<?php echo CRoute::_('index.php?option=com_community&view=connect&task=logout' , false );?>';
	         });
	};
	(function() {
	    var e = document.createElement('script');
	    e.type = 'text/javascript';
	    e.src = document.location.protocol +
	        '//connect.facebook.net/en_US/all.js';
	    e.async = true;
	    document.getElementById('fb-root').appendChild(e);
	}());
	</script>
	<fb:login-button autologoutlink="true" size="small" background="light"><?php echo JText::_('Logout');?></fb:login-button>
<?php
	}
}
?>
</div>