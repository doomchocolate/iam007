<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * This file and method will automatically get called by Joomla
 * during the installation process
 **/
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

if ( ! class_exists('JURI'))
{
	jimport('joomla.environment.uri');
}


function com_install()
{
	// get the installing com_community version
	$installer        = JInstaller::getInstance();
	$path             = $installer->getPath('manifest');
	$communityVersion = $installer->getManifest()->version;

	if ( version_compare(JVERSION,'2.5.6','<') && $communityVersion >= '2.8.0')
	{
		JError::raiseNotice(1, 'JomSocial 2.8.x require minimum Joomla! CMS 2.5.6');
		return false;
	}

	$lang = JFactory::getLanguage();
	$lang->load('com_community', JPATH_ROOT.'/administrator');

	$destination = JPATH_ROOT.'/administrator/components/com_community/';
	$buffer      = "installing";

	if( ! JFile::write($destination.'installer.dummy.ini', $buffer))
	{
		ob_start();
		?>
		<table width="100%" border="0">
			<tr>
				<td>
					There was an error while trying to create an installation file.
					Please ensure that the path <strong><?php echo $destination; ?></strong> has correct permissions and try again.
				</td>
			</tr>
		</table>
		<?php
		$html = ob_get_contents();
		@ob_end_clean();
	}
	else
	{
		$link = rtrim(JURI::root(), '/').'/administrator/index.php?option=com_community';

		ob_start();
		?>
		<style type="text/css">
			.adminform {text-align:left;}
			.adminform > tbody > tr > th { font-size:20px;}
			.button-next
			{
				margin:20px 0px 40px;
				padding:10px 20px;
				color:#fefefe;
				font-size:16px;
				line-height:16px;
				text-align: center;
				font-weight: normal;
				color: #333;
				background: #9c3;
				border: solid 1px #690;
				cursor: pointer;
			}
		</style>
		<table width="100%" border="0">
			<tr>
				<td>
					Thank you for choosing JomSocial, please click on the following button to complete your installation.
				</td>
			</tr>
			<tr>
				<td>
					<input type="button" class="button-next" onclick="window.location = '<?php echo $link; ?>'" value="<?php echo JText::_('COM_COMMUNITY_INSTALLATION_COMPLETE_YOUR_INSTALLATION');?>"/>
				</td>
			</tr>
		</table>
		<?php
		$html = ob_get_contents();
		@ob_end_clean();
	}

	echo $html;
}