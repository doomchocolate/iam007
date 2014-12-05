<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class CTwitter
{
	var	$_name = 'Twitter';

	static public function getOAuthRequest()
	{

		if(!JPluginHelper::importPlugin('community' , 'twitter' ) )
		{
		    return JText::sprintf('COM_COMMUNITY_PLUGIN_FAIL_LOAD', 'Twitter' );
		}

	    $my         = CFactory::getUser();
	    $consumer   = plgCommunityTwitter::getConsumer();
	    $oauth    	= JTable::getInstance( 'Oauth' , 'CTable' );

	    ob_start();

		if( !$oauth->load( $my->id , 'twitter') || empty($oauth->accesstoken) )
		{
			$callback      = JURI::root().'index.php?option=com_community&view=oauth&task=callback&app=twitter';

		    $oauth->userid        = $my->id;
		    $oauth->app             = 'twitter';
		    $code = $consumer->request(
			    'POST',
			    $consumer->url('oauth/request_token', ''),
			    array(
			      'oauth_callback' => $callback
			    )
			  );

			if ($code == 200) {
			    $_SESSION['oauth'] = $consumer->extract_params($consumer->response['response']);
			    $temp_credentials = $_SESSION['oauth']['oauth_token'];
			    $authurl = $consumer->url("oauth/authorize", '') .  "?oauth_token={$_SESSION['oauth']['oauth_token']}";
			  } else {
			  	$temp_credentials = null;
			  	$authurl = null;
			    //echo 'false;';//outputError($tmhOAuth);
			  }

		    //$temp_credentials = $consumer->getRequestToken($callback);
			$oauth->requesttoken	= serialize( $temp_credentials );

			$oauth->store();
		?>
		<?php if($code==200){?>
		<div><?php echo JText::_('COM_COMMUNITY_TWITTER_LOGIN');?></div>
		<a href="<?php echo $authurl;?>"><img src="<?php echo JURI::root();?>components/com_community/assets/twitter.png" border="0" alt="here" /></a>
		<?php }else{?>
		<div><?php echo JText::_('COM_COMMUNITY_TWITTER_FAILED_REQUEST_TOKEN');?></div>
		<?php }?>
		<?php
		}
		else
		{
		    //User is already authenticated and we have the proper tokens to fetch data.
		    $url    = CRoute::_( 'index.php?option=com_community&view=oauth&task=remove&app=twitter' );
		?>
		    <div><?php echo JText::sprintf('COM_COMMUNITY_TWITTER_REMOVE_ACCESS' , $url );?></div>
		<?php
		}
		$html   = ob_get_contents();
		ob_end_clean();

		return $html;
	}
}
