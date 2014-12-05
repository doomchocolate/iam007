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

jimport('joomla.application.component.model');

require_once( JPATH_ROOT .'/components/com_community/models/models.php' );

class CommunityModelRegister extends JCCModel
{
	/* public array to retrieve return value */
	public $return_value = array();

	/*
	 * adding temporary user details
	 */
    public function addTempUser($data)
	{
	    $db    = $this->getDBO();

		//get current session id.
		$mySess 	= JFactory::getSession();
		$token		= $mySess->get('JS_REG_TOKEN','');

		$nowDate = JFactory::getDate();
		$nowDate = $nowDate->toSql();

	    // Combine firsname and last name as full name
		if (empty($data['jsname']))
		{
			$data['jsname'] =  $data['jsfirstname'] . ' ' . $data['jslastname'];
		}

		$obj = new stdClass();
		$obj->name			= $data['jsname'];
		$obj->firstname		= isset( $data['jsfirstname'] ) ? $data['jsfirstname'] : '';
		$obj->lastname		= isset( $data['jslastname'] ) ? $data['jslastname'] : '';
		$obj->token			= $token;
		$obj->username		= $data['jsusername'];
		$obj->email			= $data['jsemail'];
		$obj->password		= $data['jspassword'];
		$obj->created		= $nowDate;
		$obj->ip			= isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

		//@todo Temp fix for joomla 3.2.0.Remove in the future.
		if(!version_compare(JVERSION,'3.2.0','='))
		{
			//no clear text password store in db
			jimport('joomla.user.helper');
			$salt			= JUserHelper::genRandomPassword(32);
			$crypt			= JUserHelper::getCryptedPassword($obj->password, $salt);
			$obj->password	= $crypt.':'.$salt;
		}

		$db->insertObject('#__community_register', $obj);

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		$this->return_value[__FUNCTION__] = true;
		return $this;
	}

    /**
     * @param $token
     * @return int activation_type where 1 = activate by admin, 0 = self activation
     */
    public function activate($token)
    {
        $config = CFactory::getConfig();
        $db = $this->getDbo();
        $activation_type = 0;

        // Find the user with the token supplied
        $query = $db->getQuery(true);
        $query->select($db->quoteName('id'))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('activation') . ' = ' . $db->quote($token))
            ->where($db->quoteName('block') . ' = ' . 1);
        $db->setQuery($query);

        try{
            $userId = (int) $db->loadResult();
        }catch (RuntimeException $e){
            return false;
        }

        // Check for a valid user id.
        if (!$userId){
            $this->setError(JText::_('COM_COMMUNITY_INVALID_TOKEN'));
            return false;
        }

        // Load the users plugin group.
        JPluginHelper::importPlugin('user');

        // Activate the user.
        $user = CFactory::getUser($userId);

        $user->set('activation', '');
        $user->set('block', '0');

        if($user->_profile_id > 0){
            $multiprofile = JTable::getInstance('MultiProfile', 'CTable');
            $multiprofile->load( $user->_profile_id );

            $com_user_config = JComponentHelper::getParams( 'com_users' );
            $com_user_activation_type = $com_user_config->get( 'useractivation' );

            //lets send an email to the user notifying their account has been activated
            if($multiprofile->approvals == 1 || $com_user_activation_type == 2){
                // Compile the user activated notification mail values.
                $activation_type = 1; // set this to admin activation type
                $data = $user->getProperties();
                $user->setParam('activate', 0);
                $data['fromname'] = $config->get('fromname');
                $data['mailfrom'] = $config->get('mailfrom');
                $data['sitename'] = $config->get('sitename');
                $data['siteurl'] = JUri::base();
                $emailSubject = JText::sprintf(
                    'COM_COMMUNITY_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_SUBJECT',
                    $data['name'],
                    $data['sitename']
                );

                $emailBody = JText::sprintf(
                    'COM_COMMUNITY_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_BODY',
                    $data['name'],
                    $data['siteurl'],
                    $data['username']
                );

                $message        = html_entity_decode($emailBody, ENT_QUOTES);
                $sendashtml     = false;
                $copyrightemail = JString::trim($config->get( 'copyrightemail' ));

                //check if HTML emails are set to ON
                if ($config->get('htmlemail'))
                {
                    $sendashtml = true;
                    $tmpl       = new CTemplate();
                    $message    = CString::str_ireplace(array("\r\n", "\r", "\n"), '<br />', $message );

                    $tmpl->set('name', $data['username']);
                    $tmpl->set('email', $data['email']);

                    $message = $tmpl->set('unsubscribeLink', CRoute::getExternalURL('index.php?option=com_community&view=profile&task=email'), false)
                        ->set('content', $message)
                        ->set('copyrightemail', $copyrightemail)
                        ->set('sitename', $config->get('sitename'))
                        ->fetch('email.html');
                }

                $return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $message, $sendashtml);

                // Check for an error.
                if ($return !== true)
                {
                    $this->setError(JText::_('COM_USERS_REGISTRATION_ACTIVATION_NOTIFY_SEND_MAIL_FAILED'));
                    return false;
                }
            }
        }

        // Store the user object.
        if (!$user->save())
        {
            $this->setError(JText::sprintf('COM_USERS_REGISTRATION_ACTIVATION_SAVE_FAILED', $user->getError()));
            return false;
        }

        return $activation_type;
    }

	/*
	 * Get temporary user details based on token string.
	 */
	public function getTempUser($token) {
		$db    = $this->getDBO();

		//the password2 is for JUser binding purpose.

		$query = 'SELECT *, '.$db->quoteName('password').' as '.$db->quoteName('password2')
				.' FROM '.$db->quoteName('#__community_register');
		$query .= ' WHERE '.$db->quoteName('token').' = '.$db->Quote($token);
		$db->setQuery($query);
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		$result = $db->loadObject();

		$user	= new JObject;
		$user->setProperties($result);

		return $user;
	}

	/*
	 * remove the temporary user from register table.
	 */
	public function removeTempUser($token){
		$db    = $this->getDBO();

		$query = 'DELETE FROM '.$db->quoteName('#__community_register');
		$query .= ' WHERE '.$db->quoteName('token').' = '.$db->Quote($token);

		$db->setQuery($query);
		$db->query();
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
	}

	public function cleanTempUser(){
		$nowDate		= JFactory::getDate();
		$nowDateMysql	= $nowDate->toSql();
		$app = JFactory::getApplication();

		//$jConfig	= JFactory::getConfig();
		//$lifetime	= $jConfig->getValue('lifetime');
		$lifetime	= $app->getCfg('lifetime');

		$db    = $this->getDBO();

		$query = 'DELETE FROM '.$db->quoteName('#__community_register');
		$query .= ' WHERE '.$db->quoteName('created').' <= DATE_SUB('.$db->Quote($nowDateMysql).',  INTERVAL '.$lifetime.' MINUTE)';

		$db->setQuery($query);
		$db->query();

		//
		$query = 'DELETE FROM '.$db->quoteName('#__community_register_auth_token');
		$query .= ' WHERE '.$db->quoteName('created').' <= DATE_SUB('.$db->Quote($nowDateMysql).',  INTERVAL '.$lifetime.' MINUTE)';

		$db->setQuery($query);
		$db->query();

	}


	/**
	 * Adding user extra custom profile
	 */
	public function addCustomProfile($data){

		$db    = $this->getDBO();

		$ok   = false;
		$user = $data['user'];
		$post = $data['post'];

		$query = "SELECT * FROM " . $db->quoteName('#__community_fields')
			. ' WHERE '.$db->quoteName('published').'='.$db->Quote('1')
			. ' AND '.$db->quoteName('type').' != '.$db->Quote('group')
			. ' ORDER BY '.$db->quoteName('ordering');
		$db->setQuery($query);
		$fields = $db->loadObjectList();

// 		echo "<pre>";
// 		print_r($post);
// 		echo "</pre>";
// 		echo "<br/>";

		// Bind result from previous post into the field object
		if(! empty($post)){
			for($i = 0; $i <count($fields); $i++){
				$fieldid = $fields[$i]->id;

// 				echo "<pre>";
// 				print_r($post['field'.$fieldid]);
// 				echo "</pre>";
// 				echo "<br/>";

				if(! empty($post['field'.$fieldid])){
					$fields[$i]->value = $post['field'.$fieldid];
				} else {
				    $fields[$i]->value = '';
				}
			}

			foreach ($fields as $field){
				$rcd = new stdClass();
				$rcd->user_id  = $user->id;
				$rcd->field_id = $field->id;

				if(is_array($field->value)){
				    $tmp	= '';

					// Now we need to test for 'date' specific fields as we need to convert the value
					// to unix timestamp
					$query	= 'SELECT ' . $db->quoteName('type') . ' FROM ' . $db->quoteName('#__community_fields') . ' '
							. 'WHERE ' . $db->quoteName('id') . '=' . $db->Quote( $field->id );
					$db->setQuery( $query );
					$type	= $db->loadResult();

                	if( $type == 'date' )
					{
					    $values = $field->value;
						$day	= intval($values[0]);
						$month	= intval($values[1]);
						$year	= intval($values[2]);

						$day 	= !empty($day) 		? $day 		: 1;
						$month 	= !empty($month) 	? $month 	: 1;

						$tmp	= gmmktime( 0 , 0 , 0 , $month , $day , $year );
					} else {
						foreach($field->value as $val)
						{
							$tmp .= $val . ',';
						}//end foreach
					}
					$rcd->value = $tmp;
				} else {
				    $rcd->value	   = $field->value;
				}//end if

				$db->insertObject('#__community_fields_values', $rcd);
			}//end foreach

			$ok = true;
		}//end if

	    return $ok;
	}

	/*
	 *
     */
	public function isUserNameExists($filter = array()){
		$db			= $this->getDBO();
		$found		= false;

// 		$query = "(SELECT `username`";
// 		$query .= " FROM #__users";
// 		$query .= " WHERE UCASE(`username`) = UCASE(".$db->Quote($filter['username'])."))";
// 		$query .= " UNION ";
// 		$query .= "(SELECT `username`";
// 		$query .= " FROM #__community_register";
// 		$query .= " WHERE UCASE(`username`) = UCASE(".$db->Quote($filter['username'])."))";

		/*
		 * DO NOT USE UNION. It will failed if the user joomla table's collation type was
		 * diferent from jomsocial tables's collation type
		 */

		$query = 'SELECT '.$db->quoteName('username');
		$query .= ' FROM '.$db->quoteName('#__users');
		$query .= ' WHERE UCASE('.$db->quoteName('username').') = UCASE('.$db->Quote($filter['username']).')';

		$db->setQuery( $query );
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		$result = $db->loadObjectList();
		$found = (count($result) == 0) ? false : true;

		if(! $found && isset( $filter['ip'] ) ){

			$query = 'SELECT '.$db->quoteName('username');
			$query .= ' FROM '.$db->quoteName('#__community_register');
			$query .= ' WHERE UCASE('.$db->quoteName('username').') = UCASE('.$db->Quote($filter['username']).')';
			$query .= ' AND '.$db->quoteName('ip').' != '.$db->Quote($filter['ip']);

			$db->setQuery( $query );
			if($db->getErrorNum()) {
				JError::raiseError( 500, $db->stderr());
			}
			$result = $db->loadObjectList();
			$found = (count($result) == 0) ? false : true;
		}

		return $found;

	}

	/*
	 * Method to check for exsisting email registered in jomsocial
     */
	public function isEmailExists($filter = array()){
		$db			= $this->getDBO();
		$found		= false;

// 		$query = "(SELECT `email`";
// 		$query .= " FROM #__users";
// 		$query .= " WHERE UCASE(`email`) = UCASE(".$db->Quote($filter['email'])."))";
// 		$query .= " UNION";
// 		$query .= "(SELECT `email`";
// 		$query .= " FROM #__community_register";
// 		$query .= " WHERE UCASE(`email`) = UCASE(".$db->Quote($filter['email'])."))";

		$query = 'SELECT '.$db->quoteName('email');
		$query .= ' FROM '.$db->quoteName('#__users');
		$query .= ' WHERE UCASE('.$db->quoteName('email').') = UCASE('.$db->Quote($filter['email']).')';

		$db->setQuery( $query );
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		$result = $db->loadObjectList();
		$found = (count($result) == 0) ? false : true;

		if(! $found){

			$query = 'SELECT '.$db->quoteName('email');
			$query .= ' FROM '.$db->quoteName('#__community_register');
			$query .= ' WHERE UCASE('.$db->quoteName('email').') = UCASE('.$db->Quote($filter['email']).')';
			if((isset($filter['ip'])) && (! empty($filter['ip'])))
				$query .= ' AND '.$db->quoteName('ip').' != '.$db->Quote($filter['ip']);

			$db->setQuery( $query );
			if($db->getErrorNum()) {
				JError::raiseError( 500, $db->stderr());
			}
			$result = $db->loadObjectList();
			$found = (count($result) == 0) ? false : true;
		}

		return $found;

	}
	/*
	 * Method to check for allowed email in jomsocial
     */
	public function isEmailAllowed($email){
		$config	= CFactory::getConfig();
		//CFactory::load( 'helpers' , 'validate' );
		$allowed_domains = $config->get('alloweddomains');
		if(!empty($allowed_domains)){
			$delimiter = ';';
			$allowed_list = explode($delimiter,$allowed_domains);
			$valid = false;
			if(count($allowed_list) > 0 ){
				foreach($allowed_list as $domain){
					if(CValidateHelper::domain( $email, $domain))
					{
						$valid = true;
					}
				}
			}
			if(!$valid){
				return false;
			}
		}
		return true;
	}
	/*
	 * Method to check for denied email in jomsocial
     */
	public function isEmailDenied($email){
		$config	= CFactory::getConfig();
		//CFactory::load( 'helpers' , 'validate' );
		$denied_domains = $config->get('denieddomains');
		if(!empty($denied_domains)){
			$delimiter = ';';
			$blacklists = explode($delimiter,$denied_domains);
			if(count($blacklists) > 0 ){
				foreach($blacklists as $domain){
					if(CValidateHelper::domain( $email, $domain))
					{
						return true;
					}
				}
			}
		}
		return false;
	}
	/**
	 * Function used to add new auth key
	 * param : new auth key - string
	 * return : boolean
	 */
	public function addAuthKey ($authKey='')
	{
	    $db    = $this->getDBO();

		//get current session id.
		$mySess 	= JFactory::getSession();
		$token		= $mySess->get('JS_REG_TOKEN','');

		$nowDate = JFactory::getDate();
		$nowDate = $nowDate->toSql();

		$obj = new stdClass();
		$obj->token			= $token;
		$obj->auth_key		= $authKey;
		$obj->created		= $nowDate;
		$obj->ip			= isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

		$db->insertObject('#__community_register_auth_token', $obj);

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		$this->return_value[__FUNCTION__] = true;
		return $this;
	}


	/**
	 * Function used to remove the assigned auth key.
	 *  param : current token - string
	 */
	public function removeAuthKey ($token='')
	{
		$db    = $this->getDBO();

		$query = 'DELETE FROM '.$db->quoteName('#__community_register_auth_token');
		$query .= ' WHERE '.$db->quoteName('token').' = '.$db->Quote($token);

		$db->setQuery($query);
		$db->query();
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
	}

	/**
	 * Function used to get the valid auth key
	 * param : current token - string
	 *       : user ip address - string
	 * return : auth key - string
	 */
	public function getAuthKey ($token='', $ip='')
	{
		$authKey		= "";
		$curDate		= JFactory::getDate();
		$curDateMysql	= $curDate->toSql();

		$db    = $this->getDBO();

		$config			= CFactory::getConfig();
		$expiryPeriod	= $config->get( 'sessionexpiryperiod' );
	    $expiryPeriod	= (empty($expiryPeriod)) ? "600" : $expiryPeriod;

		$query = 'SELECT '.$db->quoteName('auth_key').' FROM '.$db->quoteName('#__community_register_auth_token');
		$query .= ' WHERE '.$db->quoteName('created').' >= DATE_SUB('.$db->Quote($curDateMysql).', INTERVAL '. $expiryPeriod . ' SECOND)';
		$query .= ' AND '.$db->quoteName('token') .' = ' . $db->Quote($token);
		$query .= ' AND '.$db->quoteName('ip').' = ' . $db->Quote($ip);

		$db->setQuery($query);

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		$authKey	= $db->loadResult();
		return $authKey;
	}

	/**
	 * Function used to get the existing assigned auth key.
	 * param : current token - string
	 *       : user ip address - string
	 * return : auth key - string
	 */
	public function getAssignedAuthKey ($token='', $ip='')
	{
		$authKey		= "";
		$curDate		= JFactory::getDate();
		$curDateMysql	= $curDate->toSql();

	    $db    = $this->getDBO();

		$query = 'SELECT '.$db->quoteName('auth_key').' FROM '.$db->quoteName('#__community_register_auth_token');
		$query .= ' WHERE '.$db->quoteName('token').' = ' . $db->Quote($token);
		$query .= ' AND '.$db->quoteName('ip').' = ' . $db->Quote($ip);

		$db->setQuery($query);

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		$authKey	= $db->loadResult();
		return $authKey;
	}


	/**
	 * Function used to extend the auth key life span. Current set to 180 second.
	 * param : current token - string
	 *       : current authentication key - string
	 *       : user ip address - string
	 * return : boolean
	 */
	public function updateAuthKey ($token='', $authKey='',$ip='')
	{
		$authKey	= "";
		$db    		= $this->getDBO();

		$config			= CFactory::getConfig();
		$expiryPeriod	= $config->get( 'sessionexpiryperiod' );
		$expiryPeriod	= (empty($expiryPeriod)) ? "600" : $expiryPeriod;

		$query = 'UPDATE '.$db->quoteName('#__community_register_auth_token');
		$query .= ' SET '.$db->quoteName('created').' = DATE_ADD('.$db->quoteName('created').', INTERVAL '. $expiryPeriod . ' SECOND)';
		$query .= ' WHERE '.$db->quoteName('token').' = ' . $db->Quote($token);
		$query .= ' AND '.$db->quoteName('auth_key').' = ' . $db->Quote($authKey);
		$query .= ' AND '.$db->quoteName('ip').' = ' . $db->Quote($ip);

		$db->setQuery($query);
		$db->query();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		return $this;
	}

	public function getUserByEmail($email)
	{
		$db    		= $this->getDBO();

		$query	= 'SELECT * FROM '.$db->quoteName('#__users');
		$query	.= ' WHERE '.$db->quoteName('email').' = ' . $db->Quote($email);
		$db->setQuery($query);

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		$result = $db->loadObject();
		return $result;

	}

	/**
	 * Return administrators emails
	 */
	public function getSuperAdministratorEmail()
	{
		$db    		= $this->getDBO();

		$query		= 'SELECT a.' . $db->quoteName('name').', a.'.$db->quoteName('email').', a.'.$db->quoteName('sendEmail')
						. ' FROM ' . $db->quoteName('#__users') . ' as a, '
						. $db->quoteName('#__user_usergroup_map') . ' as b'
						. ' WHERE a.' . $db->quoteName('id') . '= b.' . $db->quoteName('user_id')
						. ' AND b.' . $db->quoteName( 'group_id' ) . '=' . $db->Quote( 8 ) ;

		$db->setQuery( $query );

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		$result = $db->loadObjectList();
		return $result;

	}
}
