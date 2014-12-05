<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// Dont allow direct linking
defined( '_JEXEC' ) or die('Restricted access');

function com_uninstall() 
{
	require_once JPATH_ROOT.'/components/com_community/defines.community.php';
	
	$asset = JTable::getInstance('Asset');

	if ($asset->loadByName('com_community')) 
	{
		$asset->delete();
	}

	$db = JFactory::getDBO();	
	
	// Remove jomsocialuser plugin during uninstall to prevent error 
	// during login/logout of Joomla.
	$query = 'DELETE FROM '.$db->quoteName(PLUGIN_TABLE_NAME).' '
		 	.'WHERE '.$db->quoteName('element').'='.$db->quote('jomsocialuser').' AND '
		 	.$db->quoteName('folder').'='.$db->quote('user');

	$db->setQuery($query);
	$db->query();

	$pluginPath = JPATH_ROOT.'/plugins/user/jomsocialuser/';	
	
	if (JFile::exists($pluginPath.'jomsocialuser.php'))
	{
		JFile::delete($pluginPath.'jomsocialuser.php');
	}
	
	if (JFile::exists($pluginPath.'jomsocialuser.xml'))
	{
		JFile::delete($pluginPath.'jomsocialuser.xml');
	}
	
	removeBackupTemplate('blueface');
	removeBackupTemplate('bubble');
	removeBackupTemplate('blackout');

	return true;   
}

function removeBackupTemplate($name)
{
	$path = JPATH_ROOT.'/components/com_community/templates/';

	if (JFolder::exists($path))
	{
		$backups = JFolder::folders($path, '^'.$name.'_bak[0-9]');

		foreach($backups as $backup)
		{
			JFolder::delete($path.$backup);
		}
	}
}