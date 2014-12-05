<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

Class CEventsAccess implements CAccessInterface
{
    	/**
	 * Method to check if a user is authorised to perform an action in this class
	 *
	 * @param	integer	$userId	Id of the user for which to check authorisation.
	 * @param	string	$action	The name of the action to authorise.
	 * @param	mixed	$asset	Name of the asset as a string.
	 *
	 * @return	boolean	True if authorised.
	 * @since	Jomsocial 2.6
	 */
	static public function authorise()
	{
            $args      = func_get_args();
            $assetName = array_shift ( $args );

            if (method_exists(__CLASS__,$assetName)) {
                    return call_user_func_array(array(__CLASS__, $assetName), $args);
            } else {
                    return null;
            }
        }

        /*
         * This function will get the permission to invite list
         * @param type $userId
         * @return : bool
         */
        static public function EventsRepeatView($userId)
        {
            $config = CFactory::getConfig();

            if( !$config->get('enablerepeat') )
            {
                    return false;
            } else {
                    return true;
            }
        }

}



?>