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

class CFileHelper
{
	/**
	 * Upload a file
	 * @param	string	$source			File to upload
	 * @param	string	$destination	Upload to here
	 * @return True on success
	 */
	static public function upload( $source , $destination )
	{
		$err		= null;
		$ret		= false;

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		// Load configurations.
		$config		= CFactory::getConfig();

		// Make the filename safe
		jimport('joomla.filesystem.file');

		if (!isset($source['name']))
		{
			JError::raiseNotice(100, JText::_('COM_COMMUNITY_INVALID_FILE_REQUEST'));
			return $ret;
		}

		$source['name']	= JFile::makeSafe($source['name']);

		if (is_dir($destination)) {
			jimport('joomla.filesystem.folder');
			JFolder::create( $destination, (int) octdec($config->get('folderpermissionsvideo')));
			JFile::copy( JPATH_ROOT .'/components/com_community/index.html' , $destination  .'/index.html' );
			$destination = JPath::clean($destination .'/'. strtolower($source['name']));
		}

		if (JFile::exists($destination))
		{
			JError::raiseNotice(100, JText::_('COM_COMMUNITY_FILE_EXISTS'));
			return $ret;
		}

		if (!JFile::upload($source['tmp_name'], $destination))
		{
			JError::raiseWarning(100, JText::_('COM_COMMUNITY_UNABLE_TO_UPLOAD_FILE'));
			return $ret;
		}
		else
		{
			$ret = true;
			return $ret;
		}

	}

	static public function getRandomFilename( $directory, $filename = '' , $extension = '', $length = 11 )
	{
		if( JString::strlen($directory) < 1)
			return false;

		$directory = JPath::clean($directory);

		// Load configurations.
		$config		= CFactory::getConfig();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		if (!JFile::exists($directory))
		{
			JFolder::create( $directory, (int) octdec($config->get('folderpermissionsvideo')) );
			JFile::copy( JPATH_ROOT .'/components/com_community/index.html' , $directory .'/index.html' );
		}

		if (strlen($filename) > 0)
			$filename	= JFile::makeSafe($filename);

		if (!strlen($extension) > 0)
			$extension	= '';

		$dotExtension 	= $filename ? JFile::getExt($filename) : $extension;
		$dotExtension 	= $dotExtension ? '.' . $dotExtension : '';

		$map			= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$len 			= strlen($map);
		$stat			= stat(__FILE__);
		$randFilename	= '';

		if(empty($stat) || !is_array($stat))
			$stat = array(php_uname());

		mt_srand(crc32(microtime() . implode('|', $stat)));
		for ($i = 0; $i < $length; $i ++) {
			$randFilename .= $map[mt_rand(0, $len -1)];
		}

		$randFilename .= $dotExtension;

		if (JFile::exists($directory .'/'. $randFilename)) {
			cGenRandomFilename($directory, $filename, $extension, $length);
		}

		return $randFilename;
	}

	static public function getFileExtension($fileName)
	{
	    $file = pathinfo($fileName);

	    return $file['extension'];
	}

	static public function getExtensionIcon($extension)
	{
	    $type = array(
	                    'bmp'=>'images',
	                    'gif'=>'images',
	                    'jpg'=>'images',
	                    'jpeg'=>'images',
	                    'png'=>'images',
	                    'psd'=>'images',
	                    'pdf'=>'document',
	                    'doc'=>'document',
	                    'docx'=>'document',
	                    'log'=>'document',
	                    'txt'=>'document',
	                    'rtf'=>'document',
	                    'wpd'=>'document',
	                    'wps'=>'document',
	                    'csv'=>'document',
	                    'xls'=>'document',
	                    'xlr'=>'document',
	                    'xlsx'=>'document',
	                    'zip'=>'archive',
	                    'deb'=>'archive',
	                    'gz'=>'archive',
	                    'pkg'=>'archive',
	                    'rar'=>'archive',
	                    'rpm'=>'archive',
	                    'zip'=>'archive',
	                    'zipx'=>'archive',
	                    'mp3'=>'multimedia',
	                    'mp4'=>'multimedia',
	                    'wma'=>'multimedia',
	                    'midi'=>'multimedia',
	                    'wav'=>'multimedia',
	                    'avi'=>'multimedia',
	                    'flv'=>'multimedia',
	                    'mov'=>'multimedia',
	                    'mp4'=>'multimedia',
	                    'rm'=>'multimedia',
	                    'wmv'=>'multimedia',
	    );

	    if(empty($type[$extension]))
	    {
	        return 'miscellaneous';
	    }

	    return $type[$extension];
	}

}

/**
 * Deprecated since 1.8.x
 * Use CFileHelper::upload instead
 **/
function cUploadFile($source, $destination)
{
	return CFileHelper::upload( $source , $destination );
}

/**
 * Deprecated since 1.8.x
 * Use CFileHelper::getRandomFilename instead
 **/
function cGenRandomFilename($directory, $filename = '' , $extension = '', $length = 11)
{
	return CFileHelper::getRandomFilename( $directory , $filename , $extension , $length );
}