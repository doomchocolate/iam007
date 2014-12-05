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

class CMyBlog extends JObject
{
	var $config	= null;

	/**
	 * Return reference to the current object
	 *
	 * @return	object		JParams object
	 */
	static public function &getInstance()
	{
		static $instance = false;

		if(!$instance)
		{
			jimport('joomla.filesystem.file');

			// @rule: Test if My Blog really exists.
			$file	= JPATH_ROOT .'/administrator/components/com_myblog/config.myblog.php';

			if( JFile::exists($file) )
			{
				require_once( $file );

				$instance			= new CMyBlog();
				$instance->config	= new MYBLOG_Config();
			}
		}

		return $instance;
	}

	/**
	 * Get the status if user is allowed to post
	 * Return: boolean
	 **/
	static public function userCanPost( $id = 0 )
	{
		require_once( JPATH_ROOT .'/components/com_myblog/functions.myblog.php' );
		return myGetUserCanPost( $id );
	}

	/**
	 * Retrieves the correct Itemid for My Blog
	 **/
	static public function getItemId()
	{
		require_once( JPATH_ROOT .'/components/com_myblog/functions.myblog.php' );

		return myGetItemId();
	}
}
