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

class CLinkGeneratorHelper
{
	/**
	 * Deprecated since 1.8
	 * Use CLinkGeneratorHelper::replaceHyperlinks instead.
	 */
	static public function linkURL( $message )
	{
		return self::replaceURL( $message );
	}

	/**
	 * Replaces a given link to it's html format
	 *
	 * @param	$link	A valid link
	 *
	 * return	$url	HTML formatted codes that links to given link
	 */
	static public function getURL( $link )
	{
		$url		= JString::trim($link);
		$pattern	= "/^(https?|ftp)\:\/\/?(.*)?/i";

		if( preg_match($pattern, $url))
		{
			return '<a href="'.$url.'" target="_blank" rel="nofollow">'.$url.'</a>';
		}

		return '<a href="http://'.$url.'" target="_blank" rel="nofollow">'.$url.'</a>';
	}

	/**
	 * Replaces a given email to a hyperlinked html format
	 *
	 * @param	$email	A valid email
	 *
	 * return	$link	HTML formatted codes that links to given email
	 */
	static public function getEmailURL( $email )
	{
		$email		= JString::trim( $email );

		// @rule: Process email cloaking by Joomla. If it fails,
		// we will need to find another solution.
		//$link		= JHTML::_( 'email.cloak', $email );

		if( empty($link) )
		{
			$link	= '<a href="mailto:'.$email.'">'.$email.'</a>';
		}

		return $link;
	}

	/**
	 * Replaces a given link be it email or hyperlink and return the proper
	 * counterparts.
	 *
	 * @param	$url	A valid url
	 *
	 * return	$link	HTML formatted codes that links to given email
	 */
	static public function getHyperLink( $url )
	{
		$link = JString::trim( $url );

		//CFactory::load( 'helpers' , 'validate' );


		if(CValidateHelper::email($link))
		{
			return self::getEmailURL($link);
		}

		if(CValidateHelper::url($link))
		{
			return self::getURL( $link );
		}

		// Since the link is really not a link, we just return the original format.
		return $link;
	}

	/**
	 * Automatically hyperlink a user's link
	 *
	 * @param	$userId		User's id.
	 * @param	$userName	Username of user.
	 *
	 * return	$urlLink	HTML codes that hyperlink to users profile.
	 **/
	static public function getUserURL( $userId , $userName )
	{
		$url		= CRoute::_('index.php?option=com_community&view=profile&userid=' . $userId );

		$urlLink	= '<a href="'.$url.'" rel="nofollow">'. $userName .'</a>';

		return $urlLink;
	}

	/**
	 * Automatically link urls in the provided message
	 *
	 * @param	$message	A string of message that may or may not contain a url.
	 *
	 * return	$message	A modified copy of the message with the proper hyperlinks.
         *
         * Link Test Case
         * Working:-
         * http://google.com
         * http://google.com.my
         * http://google.co
         * http://google.co.my
         * http://mail.google.com
         * http://mail.google.com.my
         * http://mail.google.co
         * http://mail.google.co.my
         * http://google.com?something=something
         * http://google.com.my?something=something
         * http://google.co?something=something
         * http://google.co.my?something=something
         * www.google.com
         * www.google.com?something=something
         * www.google.co
         * www.google.co?something=something
         * http://www.google.com
         * http://www.google.co
         *
         * None Workining:-
         * google.com
         *
	 **/
	static public function replaceURL( $message , $noFollow = false , $newWindow = false )
	{
		if ($message == '')
		{
			return '';
		}

		/*
		List of test url it need to pass

		http://www.dailypress.com/entertainment/galleriesandmuseums/dp-fea-mark-0508-20110506,0,5689988.story


		*/

		$http="";

		if(strpos($message,'www')!== false && strpos($message,'http://') === false && strpos($message,'https://') === false){
			$http="http://";
		}

		$replace	= ($noFollow) ? ' rel="nofollow"' : '';
		$replace	.= ($newWindow) ? ' target="_blank"' : '';

		$pattern 	= "/(((http[s]?:\/\/)|(www\.))(([a-z][-a-z0-9]+\.)?[a-z][-a-z0-9]+|\.[a-z]+(\.[a-z]{2,2})?)\/?[,a-z0-9._\/~#&=;%+?-]+[a-z0-9\/#=?_()]+)/is";
		$message = preg_replace($pattern, " <a href=\"".$http."$1\"".$replace.">$1</a>", $message);
  		// fix URLs without protocols
	  	$message = preg_replace("/href='www/", "href='http://www", $message);

		return $message;
	}

	/**
	 * Automatically link username in the provided message when message contains @username
	 *
	 * @param	$message	A string of message that may or may not contain @username
	 *
	 * return	$message	A modified copy of the message with the proper hyperlinks.
	 **/
	static public function replaceAliasURL( $message, $email = false )
	{
		$pattern	= '/@\[\[(\d+):([a-z]+):([^\]]+)\]\]/';
		preg_match_all( $pattern , $message , $matches );

        if( isset($matches[1]) && count($matches[1]) > 0 ){
            foreach( $matches[1] as $key=>$uid ){
                $id			= CFactory::getUser($uid)->get('id');
                $username	= $matches[3][$key];
				if( $id != 0 ){
					if ($email) {
					     $profileLink =  CRoute::emailLink('index.php?option=com_community&view=profile&userid=' . $id, true);
					     $message	  = CString::str_ireplace( '@' . $username , $profileLink, $message );
					} else {
					     $message	  = CString::str_ireplace( $matches[0][$key] , CLinkGeneratorHelper::getUserURL($id,$username) , $message );
					}
				}
			}
		}

		return $message;
	}
}

/**
 * Deprecated since 1.8
 * Use CLinkGeneratorHelper::validateURL instead.
 */
function cValidateURL($url)
{
	//CFactory::load( 'helpers' , 'validate' );
	return CValidateHelper::url( $url );
}

/**
 * Deprecated since 1.8
 * Use CLinkGeneratorHelper::validateEmail instead.
 */
function cValidateEmails($email)
{
	//CFactory::load( 'helpers' , 'validate');
	return CValidateHelper::email( $data, $strict );
}

/**
 * Deprecated since 1.8
 * Use CLinkGeneratorHelper::replaceURL instead.
 */
function cAutoLinkUrl( $message )
{
	return CLinkGeneratorHelper::replaceURL( $message );
}

/**
 * Deprecated since 1.8
 * Use CLinkGeneratorHelper::getURL instead.
 */
function cGenerateUrlLink( $url )
{
	return CLinkGeneratorHelper::getURL( $url );
}

/**
 * Deprecated since 1.8
 * Use CLinkGeneratorHelper::getEmailURL instead.
 */
function cGenerateEmailLink( $email )
{
	return CLinkGeneratorHelper::getEmailURL( $email );
}

/**
 * Deprecated since 1.8
 * Use CLinkGeneratorHelper::getHyperLink instead.
 */
function cGenerateHyperLink($hyperlink)
{
	return CLinkGeneratorHelper::getHyperLink( $hyperlink );
}

/**
 * Deprecated since 1.8
 * Use CLinkGeneratorHelper::getUserURL instead.
 */
function cGenerateUserLink( $userId , $userName )
{
	return CLinkGeneratorHelper::getUserURL( $userId , $userName );
}

/**
 * Deprecated since 1.8
 * Use CLinkGeneratorHelper::replaceURL instead.
 */
function cGenerateURILinks( $message )
{
	return CLinkGeneratorHelper::replaceURL( $message );
}

/**
 * Deprecated since 1.8
 * Use CLinkGeneratorHelper::replaceAliasURL instead.
 */
function cGenerateUserAliasLinks( $message )
{
	return CLinkGeneratorHelper::replaceAliasURL( $message );
}
