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

require_once( JPATH_ROOT .'/components/com_community/libraries/core.php' );
//CFactory::load( 'libraries' , 'comment' );

class CProfile implements CCommentInterface
{
	static public function sendCommentNotification( CTableWall $wall , $message )
	{
		//CFactory::load( 'libraries' , 'notification' );

		$my				= CFactory::getUser();
		$targetUser		= CFactory::getUser( $wall->post_by );
		$url			= 'index.php?option=com_community&view=profile&userid=' . $wall->contentid;
		$userParams 	= $targetUser->getParams();

		$params		= new CParameter( '' );
		$params->set( 'url' , $url );
		$params->set( 'message' , $message );

		if( $my->id != $targetUser->id && $userParams->get('notifyWallComment') )
		{
			CNotificationLibrary::add( 'profile_submit_wall_comment' , $my->id , $targetUser->id , JText::sprintf('PLG_WALLS_WALL_COMMENT_EMAIL_SUBJECT' , $my->getDisplayName() ) , '' , 'profile.wallcomment' , $params );
			return true;
		}
		return false;
	}

	/**
	 * Return profile data
	 * @param  [type] $field [description]
	 * @return [type]        [description]
	 */
	static public function getFieldData( $field )
	{
		$fieldType	= strtolower( $field['type'] );
		$value		= $field['value'];

		//CFactory::load( 'libraries/fields' , $fieldType );

		$class		= 'CFields' . ucfirst( $fieldType );

		if( class_exists( $class ) )
		{
			$object		= new $class();

			if( method_exists( $object , 'getFieldData' ) )
			{
				return $object->getFieldData( $field );
			}
		}
		if($fieldType == 'select' || $fieldType == 'singleselect' || $fieldType == 'radio')
		{
			return JText::_( $value );
		}
		else
		{
			return $value;
		}
	}

	/**
	 * Method to get the HTML output for specific fields
	 **/
	static public function getFieldHTML( $field , $showRequired = '&nbsp; *',$tooltip=true )
	{
		$fieldType	= strtolower( $field->type);
		$field->tips = $tooltip==false?'':$field->tips;
		if(is_array($field))
		{
			jimport( 'joomla.utilities.arrayhelper');
			$field = JArrayHelper::toObject($field);
		}

		$class	= 'CFields' . ucfirst( $fieldType );

		if(is_object($field->options))
		{
			$field->options = JArrayHelper::fromObject($field->options);
		}

		// Clean the options
		if( !empty( $field->options ) && !is_array( $field->options ) )
		{
			array_walk( $field->options , array( 'JString' , 'trim' ) );
		}

		// Escape the field name
		//$cViewUser 		= new CommunityViewUsers();
		$field->name	= CStringHelper::escape($field->name);

		if( !isset($field->value) )
		{
			$field->value	= '';
		}
		// max value
		if (isset($field->params)) {
			$params = json_decode($field->params);
			if (isset($params->max_char) && $params->max_char != null) {
				$field->max = $params->max_char;
			}
		}
		if( class_exists( $class ) )
		{
			$object	= new $class($field->id);

			if( method_exists( $object, 'getFieldHTML' ) )
			{
				$html	= $object->getFieldHTML( $field , $showRequired );
				return $html;
			}
		}
		return JText::sprintf('COM_COMMUNITY_UNKNOWN_USER_PROFILE_TYPE' , $class , $fieldType );
	}

	/**
	 * Method to validate any custom field in PHP. Javascript validation is not sufficient enough.
	 * We also need to validate fields in PHP since if the user knows how to send POST data, then they
	 * will bypass javascript validations.
	 **/
	static public function validateField( $fieldId, $fieldType , $value , $required, $userAccess = 0 )
	{
		// @ since 2.4.2, only admin can change this from the backend, hence, no validation is required
		if($userAccess == 2){
			return true;
		}

		$fieldType	= strtolower( $fieldType );

		//CFactory::load( 'libraries/fields' , $fieldType );

		$class	= 'CFields' . ucfirst( $fieldType );

		$default_status = true;
		/* === extra validations for fields based on field params === */
		/*
		$profilemodel	= CFactory::getModel('profile');
		$raw_param = $profilemodel->getFieldParams($fieldId);

		$params = new CParameter($raw_param);

		//validate the extra param first
		//CFactory::load( 'helpers' , 'validate' );


		//only check if there is any parameter in the param field of that field
		if(is_object($params)){
			//check for string limit
			if($params->get('min_char') != '' && $params->get('max_char') != '' && $params->get('min_char') >= 0 && $params->get('max_char') >= 0){
				$default_status = CValidateHelper::characterLength( $params->get('min_char'), $params->get('max_char'), $value);
			}

			//additional checking here:
		}*/
		/* === End of extra validation === */

		if( class_exists( $class ) && $default_status)
		{
			$object	= new $class($fieldId);
			$object->fieldId = $fieldId;
			if( method_exists( $object, 'isValid' ) )
			{
				$default_status = $object->isValid( $value , $required );
			}
		}

		// Assuming there is no need for validation in these subclasses.
		return $default_status;
	}

	static public function formatData( $fieldType , $value )
	{
		$fieldType	= strtolower( $fieldType );

		//CFactory::load( 'libraries/fields' , $fieldType );

		$class	= 'CFields' . ucfirst( $fieldType );

		if( class_exists( $class ) )
		{
			$object	= new $class();

			if( method_exists( $object, 'formatData' ) )
			{
				return $object->formatData( $value );
			}
		}
		// Assuming there is no need for formatting in subclasses.
		return $value;
	}

	static public function getCountryList()
	{
		if (!defined('COUNTRY_LANG_AVAILABLE'))
		{
			define('COUNTRY_LANG_AVAILABLE', 1);
		}

		$lang					= JFactory::getLanguage();
		$locale					= $lang->getLocale();
		$countryCode			= $locale[2];
		$countryLangExtension	= "";

		$countryListLanguage =   explode(',', trim(COUNTRY_LIST_LANGUAGE) );

		if(in_array($countryCode,$countryListLanguage)==COUNTRY_LANG_AVAILABLE)
		{
			$countryLangExtension = "_".$countryCode;
		}

		jimport( 'joomla.filesystem.file' );
		$file	= JPATH_ROOT .'/components/com_community/libraries/fields/countries'.$countryLangExtension.'.xml';

		if( JFile::exists( $file ) )
		{
			$parser	= new SimpleXMLElement( $file , NULL , true );

			$countries = $parser->countries;

			foreach($countries->children() as $country )
			{
				$name[] = $country->name;
			}
			return $name;
		}

	}

	static public function getErrorMessage($field)
	{
		$class	= 'CFields' . ucfirst( strtolower( $field['type'] ) );

		if(class_exists( $class ))
		{
			$object		= new $class($field['id']);
			return $object->getMessage($field);
		}
	}
}

/**
 * Maintain classname compatibility with JomSocial 1.6 below
 */
class CProfileLibrary extends CProfile
{}
