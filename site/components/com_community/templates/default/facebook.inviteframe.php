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
<html xmlns:fb="http://www.facebook.com/2008/fbml" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb" lang="en-gb" dir="ltr" >
<head>
<?php
$siteRoot = JURI::root();
$siteRoot = rtrim($siteRoot, '/');

$redirect_to = urlencode(CRoute::getExternalUrl('index.php?option=com_community&view=connect&task=inviteend'));
jimport('joomla.filesystem.file');
if(JFile::exists(JPATH_ROOT .'/media/system/js/mootools-core.js') )
{
	// New style media path. (Joomla! 1.6 onwards)	
?>
	<script src="<?php echo $siteRoot; ?>/media/system/js/mootools-core.js" type="text/javascript"></script>
	<script src="<?php echo $siteRoot; ?>/media/system/js/mootools-more.js" type="text/javascript"></script>
<?php
}
else 
{
	// Old style media path. ( Joomla! 1.5 )
?>
	<script src="<?php echo $siteRoot; ?>/media/system/js/mootools.js" type="text/javascript"></script>
<?php
} 
?>
	
<style>
	.fbmlIframe {
		width: 100%;
		height: 460px;
	}
	.fb_iframe_widget iframe{
		width:610px;
	}
</style>

<script src="<?php echo $siteRoot; ?>/components/com_community/assets/cookies-1.0.js" type="text/javascript"></script>
<script src="<?php echo $siteRoot; ?>/components/com_community/assets/joms.jquery-1.8.1.min.js" type="text/javascript"></script>
<script type="text/javascript" src="http://connect.facebook.net/en_US/all.js"></script>
<script type="text/javascript">
	// Delete all numeric cookies from facebook that is causing the stupid
	// "Illegal variable _files ..." error
	// Need to do interval clear cookies as invalid cookie is added only when facebook content is completely loaded.
	joms.jQuery(document).ready(function() {
		setInterval(clearCookies, 1000);
	});
	
	function clearCookies()
	{
		var myCookies = getCookies();
		
		for (cook in myCookies)
		{
			if (isNumber(cook) ){
				eraseCookie(cook);
			}
		}
	}
	
	window.addEvent('load', function()
	{	
		FB.init({
			appId:'<?php echo $config->get('fbconnectkey');?>',
			channelUrl:'<?php echo CRoute::getExternalUrl('index.php?option=com_community&view=connect&task=receiver');?>',
			status : true,
			cookie : true,
			xfbml  : true
		});
		
		// Set cookies for facbook redirect
		// joms.jQuery.cookie('fb_redirect', '<?php echo CRoute::getExternalUrl('index.php?option=com_community&view=connect&task=inviteend');?>');
	});
</script>
<?php
$content	= JText::sprintf( 'COM_COMMUNITY_FBCONNECT_MESSAGE' , '<fb:name uid="' . $facebook->getUser() . '" useyou="false" />', JURI::root() , '<fb:req-choice url="' . CRoute::getExternalUrl( 'index.php?option=com_community&view=register' ) . '" label="Register" />');
?>
</head>
<body style="width:610px; height: 460px; margin:0px; padding:0px; overflow:hidden;background: url(<?php echo $siteRoot; ?>/components/com_community/assets/wait.gif) 50% 50% no-repeat ;">
<fb:serverfbml>
	<script type="text/fbml">
		<fb:fbml>
			<fb:request-form target="_top" action="<?php echo $siteRoot; ?>/components/com_community/controllers/connectend.php?redirect_to=<?php echo $redirect_to; ?>" method="post" type="invite" content="<?php echo JText::_('COM_COMMUNITY_FBCONNECT_CHECKOUT_SITE');?> <?php echo htmlentities($content,ENT_COMPAT,'UTF-8');?>">
				<fb:multi-friend-selector import_external_friends="false" condensed="false" rows="3" email_invite="false" cols="5" showborder="false" actiontext="<?php echo JText::_('COM_COMMUNITY_FBCONNECT_INVITE_FACEBOOK_FRIENDS');?>">
			</fb:request-form>
		</fb:fbml>
	</script>
</fb:serverfbml>
</body>
</html>
