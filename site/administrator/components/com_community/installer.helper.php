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
define('DBVERSION', '13');

require_once JPATH_ROOT.'/administrator/components/com_community/defaultItems.php';

define("JOOMLA_MENU_PARENT", 'parent_id');
define("JOOMLA_MENU_COMPONENT_ID", 'component_id');
define("JOOMLA_MENU_LEVEL", 'level');
define('JOOMLA_MENU_NAME' , 'title');
define('JOOMLA_MENU_ROOT_PARENT', 1);
define('JOOMLA_MENU_LEVEL_PARENT', 1);
define('JOOMLA_PLG_TABLE', '#__extensions');
define('DEFAULT_TEMPLATE_ADMIN','bluestork');


/**
 * This is the helper file of the installer
 * during the installation process
 **/
class CommunityInstallerHelper
{
	/**
	 * Get error message.
	 *
	 * @param  string $error     [description]
	 * @param  string $extraInfo [description]
	 * @return [type]            [description]
	 */
	public static function getErrorMessage($error = "", $extraInfo = "")
	{
		switch ($error)
		{
			case 0:
				$errorWarning = $error.'-'.$extraInfo.' : '.JText::_('COM_COMMUNITY_INSTALLATION_WARN');
				break;
			case 1:
				$errorWarning = $error.'-'.$extraInfo.' : '.JText::_('COM_COMMUNITY_INSTALLATION_MISSING_FILE_WARN');
				break;
			case 2:
				$errorWarning = $error.'-'.$extraInfo.' : '.JText::_('COM_COMMUNITY_INSTALLATION_BACKEND_EXTRACT_FAILED_WARN');
				break;
			case 3:
				$errorWarning = $error.'-'.$extraInfo.' : '.JText::_('COM_COMMUNITY_INSTALLATION_INSTALL_AJAX_FAILED_WARN');
				break;
			case 4:
				$errorWarning = $error.'-'.$extraInfo.' : '.JText::_('COM_COMMUNITY_INSTALLATION_FRONTEND_EXTRACT_FAILED_WARN');
				break;
			case 5:
				$errorWarning = $error.'-'.$extraInfo.' : '.JText::_('COM_COMMUNITY_INSTALLATION_TEMPLATE_EXTRACT_FAILED_WARN');
				break;
			case 6:
				$errorWarning = $error.'-'.$extraInfo.' : '.JText::_('COM_COMMUNITY_INSTALLATION_DB_PREPARATION_FAILED_WARN');
				break;
			case 7:
				$errorWarning = $error.'-'.$extraInfo.' : '.JText::_('COM_COMMUNITY_INSTALLATION_DB_UPDATE_FAILED_WARN');
				break;
			case 101:
				$errorWarning = $error.' : '.JText::sprintf('COM_COMMUNITY_INSTALLATION_UNSUPPORTED_PHP_VERSION', $extraInfo);
				break;
			default:
				$error        = (!empty($error))? $error : '99';
				$errorWarning = $error.'-'.$extraInfo.' : '.JText::_('COM_COMMUNITY_INSTALLATION_UNEXPECTED_ERROR_WARN');
				break;
		}

		ob_start();
		?>
		<div style="font-weight: 700; color: red; padding-top:10px">
			<?php echo $errorWarning; ?>
		</div>
		<div id="communityContainer" style="margin-top:10px">
			<div><?php echo JText::_('COM_COMMUNITY_INSTALLATION_ERROR_HELP'); ?></div>
			<div><a href="http://www.jomsocial.com/support/docs/item/724-installation-troubleshooting-a-faq.html">http://www.jomsocial.com/support/docs/item/724-installation-troubleshooting-a-faq.html</a></div>
		</div>
		<?php
		$errorMsg = ob_get_contents();
		@ob_end_clean();

		return $errorMsg;
	}

	public $backendPath;
	public $frontendPath;
	public $successStatus;
	public $failedStatus;
	public $notApplicable;
	public $totalStep;
	public $pageTitle;
	public $verifier;
	public $display;
	public $dbhelper;

	public function __construct()
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.archive');

		$this->backendPath   = JPATH_ROOT.'/administrator/components/com_community/';
		$this->frontendPath  = JPATH_ROOT.'/components/com_community/';

		$this->successStatus = '<div style="float:left;">.....&nbsp;</div><div style="color:#009900;">'.JText::_('COM_COMMUNITY_INSTALLATION_DONE').'</div><div style="clear:both;"></div>';
		$this->failedStatus  = '<div style="float:left;">.....&nbsp;</div><div style="color:red;">'.JText::_('COM_COMMUNITY_INSTALLATION_FAILED').'</div><div style="clear:both;"></div>';
		$this->notApplicable = '<div style="float:left;">.....&nbsp;</div><div>'.JText::_('COM_COMMUNITY_INSTALLATION_NOT_APPLICABLE').'</div><div style="clear:both;"></div>';
		$this->totalStep     = 11;

		$this->verifier = new CommunityInstallerVerifier();
		$this->display	= new CommunityInstallerDisplay();
		$this->dbhelper = new CommunityInstallerDBAction();
		$this->template	= new CommunityInstallerTemplate();
	}

	public function getVersion()
	{
		// Load the local XML file first to get the local version
		$fileXml = JPATH_ROOT.'/administrator/components/com_community/community.xml';
		$parser = new SimpleXMLElement($fileXml, NULL, FALSE);
		$version = $parser->version;

		return $version;
	}

	public function getAutoSubmitFunction()
	{
		ob_start();
		JHTML::_('behavior.mootools');
		?>
		<script type="text/javascript">
		var i = 3;

		function countDown()
		{
			if (i >= 0)
			{
				document.getElementById("timer").innerHTML = i;
				i = i-1;
				var c = window.setTimeout("countDown()", 1000);
			}
			else
			{
				document.getElementById("div-button-next").removeAttribute("onclick");
				document.getElementById("input-button-next").setAttribute("disabled","disabled");
				document.installform.submit();
			}
		}

		window.addEvent('domready', function() {
			countDown();
		});

		</script>
		<?php
		$autoSubmit = ob_get_contents();
		@ob_end_clean();

		return $autoSubmit;
	}

	protected function stepCheckRequirement($step)
	{
		$status          = true;
		$this->pageTitle = JText::_('COM_COMMUNITY_INSTALLATION_CHECKING_REQUIREMENT');

		$html = '<div style="width:100px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_BACKEND_ARCHIVE').'</div>';

		if ( ! $this->verifier->isFileExist($this->backendPath.'backend.zip'))
		{
			$html      .= $this->failedStatus;
			$status    = false;
			$errorCode = '1a';
		}
		else
		{
			$html .= $this->successStatus;
		}

		$html .= '<div style="width:100px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_AJAX_ARCHIVE').'</div>';

		if ( ! $this->verifier->isFileExist($this->frontendPath.'azrul.zip'))
		{
			$html      .= $this->failedStatus;
			$status    = false;
			$errorCode = '1b';
		}
		else
		{
			$html .= $this->successStatus;
		}

		$html .= '<div style="width:100px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_FRONTEND_ARCHIVE').'</div>';

		if ( ! $this->verifier->isFileExist($this->frontendPath.'frontend.zip'))
		{
			$html      .= $this->failedStatus;
			$status    = false;
			$errorCode = '1c';
		}
		else
		{
			$html .= $this->successStatus;
		}

		$html .= '<div style="width:100px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_TEMPLATE_ARCHIVE').'</div>';

		if ( ! $this->verifier->isFileExist($this->frontendPath.'templates.zip'))
		{
			$html      .= $this->failedStatus;
			$status    = false;
			$errorCode = '1d';
		}
		else
		{
			$html .= $this->successStatus;
		}

		$html .= '<div style="width:100px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_CORE_PLUGIN_ARCHIVE').'</div>';

		if ( ! $this->verifier->isFileExist($this->frontendPath.'ai_plugin.zip'))
		{
			$html      .= $this->failedStatus;
			$status    = false;
			$errorCode = '1e';
		}
		else
		{
			$html .= $this->successStatus;
		}

		if ($status)
		{
			$autoSubmit = $this->getAutoSubmitFunction();
			$message    = $autoSubmit.$html;
		}
		else
		{
			$errorMsg = $this->getErrorMessage(1, $errorCode);
			$message  = $html.$errorMsg;
			$step     = $step - 1;
		}

		$drawdata          = new stdClass();
		$drawdata->message = $message;
		$drawdata->status  = $status;
		$drawdata->step    = $step;
		$drawdata->title   = JText::_('COM_COMMUNITY_INSTALLATION_CHECKING_REQUIREMENT');
		$drawdata->install = 1;

		return $drawdata;
	}

	protected function installBackend($step)
	{
		$html        = '<div style="width:100px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_INSTALLATION').'</div>';

		$zip         = $this->backendPath.'backend.zip';
		$destination = $this->backendPath;

		if ($this->extractArchive($zip, $destination))
		{
			$html       .= $this->successStatus;
			$autoSubmit = $this->getAutoSubmitFunction();
			$message    = $autoSubmit.$html;
			$status     = true;
		}
		else
		{
			$html     .= $this->failedStatus;
			$errorMsg = $this->getErrorMessage(2, '2');
			$message  = $html.$errorMsg;
			$status   = false;
			$step     = $step - 1;
		}

		$drawdata          = new stdClass();
		$drawdata->message = $message;
		$drawdata->status  = $status;
		$drawdata->step    = $step;
		$drawdata->title   = JText::_('COM_COMMUNITY_INSTALLATION_INSTALLING_BACKEND_SYSTEM');
		$drawdata->install = 1;

		return $drawdata;
	}

	protected function installAjax($step)
	{
		$status = true;
		$html   = '<div style="width:100px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_EXTRACTION').'</div>';
		$db     = JFactory::getDBO();

		if ($this->azrulSystemNeedsUpdate())
		{
			$zip         = $this->frontendPath.'azrul.zip';
			$destination = JPATH_PLUGINS.'/system';

			jimport('joomla.installer.installer');
			jimport('joomla.installer.helper');

			$package   = JInstallerHelper::unpack($zip);
			$installer = JInstaller::getInstance();

			if ( ! $installer->install($package['dir']))
			{
				// There was an error installing the package
				$errorCode	= '3c '.JText::sprintf('COM_INSTALLER_INSTALL_ERROR', $package['type']);
				$status		= false;
			}

			// Cleanup the install files
			if ( ! is_file($package['packagefile']))
			{
				//$config                 = JFactory::getConfig();
				//$package['packagefile'] = $config->get('tmp_path').'/'.$package['packagefile'];

				$app = JFactory::getApplication();
				$package['packagefile'] = $app->getCfg('tmp_path').'/'.$package['packagefile'];
			}

			JInstallerHelper::cleanupInstall('', $package['extractdir']);

			//enable plugin
			$this->enablePlugin('azrul.system');
		}

		if ($status)
		{
			$html       .= $this->successStatus;
			$autoSubmit = $this->getAutoSubmitFunction();
			$message    = $autoSubmit.$html;
		}
		else
		{
			$html     .= $this->failedStatus;
			$errorMsg = $this->getErrorMessage(3, $errorCode);
			$message  = $html.$errorMsg;
			$step     = $step - 1;
		}

		$drawdata          = new stdClass();
		$drawdata->message = $message;
		$drawdata->status  = $status;
		$drawdata->step    = $step;
		$drawdata->title   = JText::_('COM_COMMUNITY_INSTALLATION_INSTALLING_AJAX_SYSTEM');
		$drawdata->install = 1;

		return $drawdata;
	}

	protected function installFrontend($step)
	{
		$html        = '<div style="width:100px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_INSTALLATION').'</div>';

		$zip         = $this->frontendPath.'frontend.zip';
		$destination = $this->frontendPath;

		if ($this->extractArchive($zip , $destination))
		{
			$html .= $this->successStatus;

			if ( ! JFolder::exists(JPATH_ROOT.'/images/photos'))
			{
				if ( ! JFolder::create( JPATH_ROOT.'/images/photos'))
				{
					$html .= '<div>There was an error when creating the default photos folder due to permission issues. Please ensure that the folder <strong>'.JPATH_ROOT.'/images/photos</strong> is created manually.</div>';
				}
			}

			if ( ! JFolder::exists(JPATH_ROOT.'/images/avatar'))
			{
				if ( ! JFolder::create(JPATH_ROOT.'/images/avatar'))
				{
					$html .= '<div>There was an error when creating the avatar folder due to permission issues. Please ensure that the folder <strong>'.JPATH_ROOT.'/images/avatar</strong> is created manually.</div>';
				}
			}

			if ( ! JFolder::exists(JPATH_ROOT.'/images/originalphotos'))
			{
				if ( ! JFolder::create( JPATH_ROOT.'/images/originalphotos'))
				{
					$html .= '<div>There was an error when creating the original photos folder due to permission issues. Please ensure that the folder <strong>'.JPATH_ROOT.'/images/originalphotos</strong> is created manually.</div>';
				}
			}

			if ( ! JFolder::exists(JPATH_ROOT.'/images/watermarks'))
			{
				if ( ! JFolder::create(JPATH_ROOT.'/images/watermarks'))
				{
					$html .= '<div>There was an error when creating the watermarks folder due to permission issues. Please ensure that the folder <strong>'.JPATH_ROOT.'/images/watermarks</strong> is created manually.</div>';
				}
			}

			if ( ! JFolder::exists(JPATH_ROOT.'/images/watermarks/original'))
			{
				if ( ! JFolder::create( JPATH_ROOT.'/images/watermarks/original'))
				{
					$html .= '<div>There was an error when creating the original watermarks folder due to permission issues. Please ensure that the folder <strong>'.JPATH_ROOT.'/images/watermarks/original</strong> is created manually.</div>';
				}
			}

			if ( ! JFolder::exists(JPATH_ROOT.'/images/avatar/groups'))
			{
				if ( ! JFolder::create( JPATH_ROOT.'/images/avatar/groups'))
				{
					$html .= '<div>There was an error when creating the groups avatar folder due to permission issues. Please ensure that the folder <strong>'.JPATH_ROOT.'/images/avatar/groups</strong> is created manually.</div>';
				}
			}

			if ( ! JFolder::exists(JPATH_ROOT.'/images/avatar/events'))
			{
				if ( ! JFolder::create(JPATH_ROOT.'/images/avatar/events'))
				{
					$html .= '<div>There was an error when creating the groups avatar folder due to permission issues. Please ensure that the folder <strong>'.JPATH_ROOT.'/images/avatar/events</strong> is created manually.</div>';
				}
			}

			$autoSubmit = $this->getAutoSubmitFunction();
			$message    = $autoSubmit.$html;
			$status     = true;
		}
		else
		{
			$html     .= $this->failedStatus;
			$errorMsg = $this->getErrorMessage(4, '4');
			$message  = $html.$errorMsg;
			$status   = false;
			$step     = $step - 1;
		}

		$drawdata          = new stdClass();
		$drawdata->message = $message;
		$drawdata->status  = $status;
		$drawdata->step    = $step;
		$drawdata->title   = JText::_('COM_COMMUNITY_INSTALLATION_FRONTEND_SYSTEM');
		$drawdata->install = 1;

		return $drawdata;
	}

	public function backupTemplate($templateName)
	{
		$templatesPath = JPATH_ROOT.'/components/com_community/templates/';
		$templatePath  = $templatesPath.$templateName.'/';

		if (JFolder::exists($templatePath))
		{
			$backups  = JFolder::folders($templatesPath, '^'.$templateName.'_bak[0-9]');
			$newIndex = 0;

			foreach ($backups as $backup)
			{
				$currentIndex = str_replace($templateName.'_bak', '', $backup);
				$newIndex     = max($newIndex, $currentIndex);
			}

			$newIndex           += 1;
			$templateBackupPath = $templatesPath.$templateName.'_bak'.$newIndex.'/';

			JFolder::move($templatePath, $templateBackupPath);
		}
	}

	protected function installTemplate($step)
	{
		$html = '<div style="width:100px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_INSTALLATION').'</div>';

		// If "templates" folder exist,
		// indicates that the installation may be an upgrade
		if (JFolder::exists($this->frontendPath.'templates/'))
		{
			// Backup templates
			CommunityInstallerHelper::backupTemplate('bubble');
			CommunityInstallerHelper::backupTemplate('blackout');
			CommunityInstallerHelper::backupTemplate('blueface');
		}

		$zip         = $this->frontendPath.'templates.zip';
		$destination = $this->frontendPath;

		if ($this->extractArchive($zip , $destination))
		{
			$html       .= $this->successStatus;
			$autoSubmit = $this->getAutoSubmitFunction();
			$message    = $autoSubmit.$html;
			$status     = true;
		}
		else
		{
			$html     .= $this->failedStatus;
			$errorMsg = $this->getErrorMessage(5, '5');
			$message  = $html.$errorMsg;
			$status   = false;
			$step     = $step - 1;
		}

		$drawdata          = new stdClass();
		$drawdata->message = $message;
		$drawdata->status  = $status;
		$drawdata->step    = $step;
		$drawdata->title   = JText::_('COM_COMMUNITY_INSTALLATION_TEMPLATE');
		$drawdata->install = 1;

		return $drawdata;
	}

	protected function prepareDatabase($step)
	{
		$html        = '<div style="width:100px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_PREPARATION').'</div>';
		$queryResult = $this->dbhelper->createDefaultTable();

		if (empty($queryResult))
		{
			$html       .= $this->successStatus;
			$autoSubmit = $this->getAutoSubmitFunction();
			$message    = $autoSubmit.$html;
			$status     = true;
		}
		else
		{
			$html     .= $this->failedStatus;
			$errorMsg = $this->getErrorMessage(6, $queryResult);
			$message  = $html.$errorMsg;
			$status   = false;
			$step     = $step - 1;
		}

		$drawdata          = new stdClass();
		$drawdata->message = $message;
		$drawdata->status  = $status;
		$drawdata->step    = $step;
		$drawdata->title   = JText::_('COM_COMMUNITY_INSTALLATION_PREPARING_DATABASE');
		$drawdata->install = 1;

		return $drawdata;
	}

	protected function updateDatabase($step)
	{
		$db         = JFactory::getDBO();
		$html       = '';
		$status     = true;
		$stopUpdate = false;
		$continue   = false;

		// Insert configuration codes if needed
		$hasConfig = $this->dbhelper->_isExistDefaultConfig();

		if ( ! $hasConfig)
		{
			$html        .= '<div style="width:150px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_UPDATE_CONFIG').'</div>';

			$obj         = new stdClass();
			$obj->name   = 'dbversion';
			$obj->params = DBVERSION;

			if ( ! $db->insertObject('#__community_config' , $obj))
			{
				$html      .= $this->failedStatus;
				$status    = false;
				$errorCode = '7a';
			}
			else
			{
				$default = JPATH_BASE.'/components/com_community/default.ini';

				$registry = JRegistry::getInstance('community');
				$registry->loadFile($default , 'INI' , 'community');

				// Set the site name
				$app = JFactory::getApplication();
				$registry->setValue('community.sitename' , $app->getCfg('sitename'));

				// Set the photos path
				$photoPath = rtrim( dirname( JPATH_BASE ) , '/' );
				$registry->setValue('community.photospath' , $photoPath.'/images');

				// Set the videos folder
				$registry->setValue( 'community.videofolder' , 'images' );

				// Store the config
				$obj         = new stdClass();
				$obj->name   = 'config';
				$obj->params = $registry->toString('INI', 'community');

				if ( ! $this->dbhelper->insertTableEntry('#__community_config' , $obj))
				{
					$html .= $this->failedStatus;
					ob_start();
					?>
					<div>
						Error when trying to create default configurations.
						Please proceed to the configuration and set your own configuration instead.
					</div>
					<?php
					$html .= ob_get_contents();
					@ob_end_clean();
				}
				else
				{
					$html .= $this->successStatus;
				}
			}
		}
		else
		{
			$dbversionConfig = $this->dbhelper->getDBVersion();
			$dbversion       = (empty($dbversionConfig))? 0 : $dbversionConfig;

			if ($dbversion < DBVERSION)
			{
				$updater      =  new CommunityInstallerUpdate();

				$html         .= '<div style="width:150px; float:left;">'.JText::_('Updating DB from version '.$dbversion).'</div>';
				$updateResult = call_user_func(array( $updater , 'update_'.$dbversion ) );
				$stopUpdate   = (empty($updateResult->stopUpdate))? false : true;

				if ($updateResult->status)
				{
					$html   .= $this->successStatus;
					$status = true;

					$dbversion++;

					if (($dbversionConfig === null) && ($dbversionConfig !== 0))
					{
						$this->dbhelper->insertDBVersion($dbversion);
					}
					else
					{
						$this->dbhelper->updateDBVersion($dbversion);
					}

					if($dbversion < DBVERSION)
					{
						$continue = true;
					}
				}
				else
				{
					$html      .= $this->failedStatus;
					$status    = false;
					$errorCode = $updateResult->errorCode;
				}

				$html .= $updateResult->html;
			}
		}

		if ( ! $stopUpdate)
		{
			if ( ! $continue)
			{
				// Need to update the menu's component id if this is a reinstall
				if (menuExist())
				{
					$html .= '<div style="width:150px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_UPDATE_MENU_ITEMS').'</div>';

					if ( ! updateMenuItems())
					{
						ob_start();
						?>
						<p style="font-weight: 700; color: red;">
							System encountered an error while trying to update the existing menu items. You will need
							to update the existing menu structure manually.
						</p>
						<?php
						$html .= ob_get_contents();
						@ob_end_clean();
						$html .= $this->failedStatus;
					}
					else
					{
						$html .= $this->successStatus;
					}
				}
				else
				{
					$html .= '<div style="width:150px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_CREATE_MENU_ITEMS').'</div>';

					if ( ! addMenuItems())
					{
						ob_start();
						?>
						<p style="font-weight: 700; color: red;">
							System encountered an error while trying to create a menu item. You will need
							to create your menu item manually.
						</p>
						<?php
						$html .= ob_get_contents();
						@ob_end_clean();
						$html .= $this->failedStatus;;
					}
					else
					{
						$html .= $this->successStatus;
					}
				}

				// Jomsocial menu types
				if ( ! menuTypesExist())
				{
					$html .= '<div style="width:150px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_CREATE_TOOLBAR_MENU_ITEM').'</div>';

					if ( ! addDefaultMenuTypes())
					{
						ob_start();
						?>
						<p style="font-weight: 700; color: red;">
							System encountered an error while trying to create a menu type item. You will need
							to create your toolbar menu type item manually.
						</p>
						<?php
						$html .= ob_get_contents();
						@ob_end_clean();
						$html .= $this->failedStatus;;
					}
					else
					{
						$html .= $this->successStatus;
					}
				}

				//clean up registration table if the table installed previously.
				$this->dbhelper->cleanRegistrationTable();

				// Test if we are required to add default custom fields
				$html .= '<div style="width:150px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_ADD_DEFAULT_CUSTOM_FIELD').'</div>';

				if (needsDefaultCustomFields())
				{
					addDefaultCustomFields();
					$html .= $this->successStatus;
				}
				else
				{
					$html .= $this->notApplicable;
				}

				// Test if we are required to add default group categories
				$html .= '<div style="width:150px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_ADD_DEFAULT_GROUP_CATEGORIES').'</div>';

				if (needsDefaultGroupCategories() )
				{
					addDefaultGroupCategories();
					$html .= $this->successStatus;
				}
				else
				{
					$html .= $this->notApplicable;
				}

				// Test if we are required to add default videos categories
				$html .= '<div style="width:150px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_ADD_DEFAULT_VIDEO_CATEGORIES').'</div>';

				if (needsDefaultVideosCategories())
				{
					addDefaultVideosCategories();
					$html .= $this->successStatus;
				}
				else
				{
					$html .= $this->notApplicable;
				}

				// Test if we are required to add default event categories
				$html .= '<div style="width:150px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_ADD_DEFAULT_EVENT_CATEGORIES').'</div>';

				if (needsDefaultEventsCategories())
				{
					addDefaultEventsCategories();
					$html .= $this->successStatus;
				}
				else
				{
					$html .= $this->notApplicable;
				}

				// Test if we are required to add default user points
				$html .= '<div style="width:150px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_ADD_DEFAULT_USERPOINTS').'</div>';

				if (needsDefaultUserPoints())
				{
					//clean up userpoints table if the table installed from previous version of 1.0.128
					$this->dbhelper->cleanUserPointsTable();
					addDefaultUserPoints();
					$html .= $this->successStatus;
				}
				else
				{
					//cleanup some unused action rules.
					$this->dbhelper->cleanUserPointsTable(array('friends.request.add','friends.request.reject','friends.request.cancel','friends.invite'));
					$html .= $this->notApplicable;
				}
			}

			if ($status)
			{
				if ( ! empty($continue))
				{
					$step = $step - 1;
				}

				$autoSubmit = $this->getAutoSubmitFunction();
				$message    = $autoSubmit.$html;
			}
			else
			{
				$errorMsg = $this->getErrorMessage(7, $errorCode);
				$message  = $html.$errorMsg;
				$step     = $step - 1;
			}
		}
		else
		{
			$message = $html;
		}

		$drawdata          = new stdClass();
		$drawdata->message = $message;
		$drawdata->status  = $status;
		$drawdata->step    = $step;
		$drawdata->title   = JText::_('COM_COMMUNITY_INSTALLATION_UPDATING_DATABASE');
		$drawdata->install = 1;

		return $drawdata;
	}

	protected function installPlugin($step)
	{
		jimport('joomla.filesystem.file');

		$db = JFactory::getDBO();

		// @rule: Rename community in xml file to JomSocial
		$file    = JPATH_ROOT.'/administrator/components/com_community/community.xml';
		$content = JFile::read($file);
		$content = JString::str_ireplace( '<name>Community<', '<name>JomSocial<', $content);

		JFile::write( $file , $content );

		$html  = '';
		$html .= '<div style="width:150px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_EXTRACTING_PLUGIN').'</div>';

		$pluginFolder = $this->frontendPath.'ai_plugin';

		if ( ! JFolder::exists($pluginFolder))
		{
			JFolder::create($pluginFolder);
		}

		$zip         = $this->frontendPath.'ai_plugin.zip';
		$destination = $pluginFolder;

		if ($this->extractArchive($zip , $destination))
		{
			$html          .= $this->successStatus;

			$plugins       = array();
			$response      = new stdClass();
			$response->msg = '';
			$miscMsg       = '';

			$plugins[]     = $this->frontendPath.'ai_plugin/plg_jomsocialuser.zip';
			$plugins[]     = $this->frontendPath.'ai_plugin/plg_walls.zip';
			$plugins[]     = $this->frontendPath.'ai_plugin/plg_jomsocialconnect.zip';
			$plugins[]     = $this->frontendPath.'ai_plugin/plg_jomsocialupdate.zip';

			jimport('joomla.installer.installer');
			jimport('joomla.installer.helper');

			$app = JFactory::getApplication();

			foreach ($plugins as $plugin)
			{
				$package   = JInstallerHelper::unpack($plugin);
				$installer = JInstaller::getInstance();

				// @TODO to be removed!
				if ( ! $installer->install($package['dir']))
				{
					// There was an error installing the package
					//...
				}

				// Cleanup the install files
				if ( ! is_file($package['packagefile']))
				{
					//$config		= JFactory::getConfig();
					//$package['packagefile'] = $config->get('tmp_path').'/'.$package['packagefile'];
					$package['packagefile'] = $app->getCfg('tmp_path').'/'.$package['packagefile'];
				}
			}

			//JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
			JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

			//enable plugins
			$this->enablePlugin('jomsocialuser');
			$this->enablePlugin('walls');
			$this->enablePlugin('jomsocialconnect');
			$this->enablePlugin('jomsocialupdate');

			//remove deleteuser plugin if exist as it is deprecated
			$sql = 'DELETE FROM '.$db->quoteName(JOOMLA_PLG_TABLE)
				.' WHERE '.$db->quoteName('element').'='.$db->quote('deleteuser')
				.' AND '.$db->quoteName('folder').'='.$db->quote('user');

			$db->setQuery($sql);
			$db->Query();

			if (JFile::exists(JPATH_ROOT.'/plugins/user'.'deleteuser.php'))
			{
				JFile::delete(JPATH_ROOT.'/plugins/user'.'deleteuser.php');
			}

			if (JFile::exists(JPATH_ROOT.'/plugins/user'.'deleteuser.xml'))
			{
				JFile::delete(JPATH_ROOT.'/plugins/user'.'deleteuser.xml');
			}
		}
		else
		{
			$html .= $this->failedStatus;
		}

		JFolder::delete($pluginFolder);

		$autoSubmit        = $this->getAutoSubmitFunction();
		$message           = $autoSubmit.$html;

		$drawdata          = new stdClass();
		$drawdata->message = $message;
		$drawdata->status  = true;
		$drawdata->step    = $step;
		$drawdata->title   = JText::_('COM_COMMUNITY_INSTALLATION_INSTALLING_PLUGINS');
		$drawdata->install = 1;

		return $drawdata;
	}

	protected function installationComplete($step)
	{
		$cache = JFactory::getCache();
		$cache->clean();

		$version    = CommunityInstallerHelper::getVersion();
		$successImg = 'http://www.jomsocial.com/images/install/success.png?url='.urlencode( JURI::root() ).'&version='.$version;

		$file       = JPATH_ROOT.'/administrator/components/com_community/installer.dummy.ini';

		if (JFile::exists($file) && JFile::delete($file))
		{
			$html  = '<div style="height: 96px"><img src='.$successImg.' /></div>';
			$html .= '<div style="margin: 0px 0 30px; padding: 10px; background: #edffb7; border: solid 1px #8ba638; width: 50%; -moz-border-radius: 5px; -webkit-border-radius: 5px;">
			<div style="background: #edffb7 url(templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/toolbar/icon-32-apply.png) no-repeat 0 0;width: 32px; height: 32px; float: left; margin-right: 10px;"></div>
			<h3 style="padding: 0; margin: 0 0 5px;">Installation has been completed</h3>Please upgrade your Modules and Plugins too.</div>';
		}
		else
		{
			$html  = '<div style="height: 96px"><img src='.$successImg.' /></div>';
			$html .= '<div style="margin: 0px 0 30px; padding: 10px; background: #edffb7; border: solid 1px #8ba638; width: 50%; -moz-border-radius: 5px; -webkit-border-radius: 5px;">
			<div style="background: #edffb7 url(templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/toolbar/icon-32-apply.png) no-repeat 0 0;width: 32px; height: 32px; float: left; margin-right: 10px;"></div>
			<h3 style="padding: 0; margin: 0 0 5px;">Installation has been completed</h3>However we were unable to remove the file <b>installer.dummy.ini</b> located in the backend folder. Please remove it manually in order to completed the installation.</div>';
		}

		ob_start(); ?>
		<div style="margin: 30px 0; padding: 10px; background: #fbfbfb; border: solid 1px #ccc; width: 50%; -moz-border-radius: 5px; -webkit-border-radius: 5px;">
			<h3 style="color: red;">IMPORTANT!!</h3>
			<div>Before you begin, you might want to take a look at the following documentations first</div>
			<ul style="background: none;padding: 0; margin-left: 15px;">
				<li style="background: none;padding: 0;margin:0;"><a href="http://documentation.jomsocial.com/wiki/Setting_up_Cron_Job" target="_blank">Setting up scheduled task to process emails.</a></li>
				<li style="background: none;padding: 0;margin:0;"><a href="http://documentation.jomsocial.com/wiki/Installing_Plugin" target="_blank">Installing applications for JomSocial</a></li>
				<li style="background: none;padding: 0;margin:0;"><a href="http://documentation.jomsocial.com/wiki/Installing_Module" target="_blank">Installing modules for JomSocial</a></li>
		</ul>
			<div>You can read the full documentation at <a href="http://documentation.jomsocial.com" target="_blank">JomSocial Documentation</a></div>
		</div>

	<?php
		$content = ob_get_contents();
		ob_end_clean();

		$html              .= $content;
		$message           = $html;

		$drawdata          = new stdClass();
		$drawdata->message = $message;
		$drawdata->status  = true;
		$drawdata->step    = $step;
		$drawdata->title   = JText::_('COM_COMMUNITY_INSTALLATION_COMPLETED');
		$drawdata->install = 0;

		return $drawdata;
	}

	public function install($step = 1)
	{
		$db = JFactory::getDBO();

		switch($step)
		{
			case 1:
				//check requirement
				$status = $this->stepCheckRequirement(2);
				break;
			case 2:
				//install backend system
				$status = $this->installBackend(3);
				break;
			case 3:
				//install ajax system
				$status = $this->installAjax(4);
				break;
			case 4:
				//install frontend system
				$status = $this->installFrontend(5);
				break;
			case 5:
				//install template
				$status = $this->installTemplate(6);
				break;
			case 6:
				//prepare database
				$status = $this->prepareDatabase(7);
				break;
			case 7:
			case 'UPDATE_DB':
				//update database
				$status = $this->updateDatabase(8);
				break;
			case 8:
				//install basic plugins
				$status = $this->installPlugin(100);
				break;
			case 100:
				//show success message
				$status = $this->installationComplete(0);
				break;
			default:
				$status          = new stdClass();
				$status->message = $this->getErrorMessage(0, '0a');
				$status->step    = '-99';
				$status->title   = JText::_('COM_COMMUNITY_INSTALLATION_JOMSOCIAL');
				$status->install = 1;
				break;
		}

		return $status;
	}

	/**
	 * Method to extract archive out
	 *
	 * @returns	boolean	True on success false otherwise.
	 **/
	function extractArchive($source, $destination)
	{
		// Cleanup path
		$destination	= JPath::clean( $destination );
		$source			= JPath::clean( $source );

		return JArchive::extract($source, $destination);
	}

	/**
	 * Method to check if the system plugins exists
	 *
	 * @returns boolean	True if system plugin needs update, false otherwise.
	 **/
	function azrulSystemNeedsUpdate()
	{
		$xml	= JPATH_PLUGINS.'/system/azrul.system.xml';

		// Check if the record also exists in the database.
		$db		= JFactory::getDBO();

		$query	= 'SELECT COUNT(1) FROM '.$db->quoteName(JOOMLA_PLG_TABLE) .' WHERE '
				. $db->quoteName( 'element' ).'='.$db->Quote( 'azrul.system' );
		$db->setQuery( $query );
		$dbExists	= $db->loadResult() > 0;

		if( !$dbExists )
		{
			return true;
		}

		// Test if file exists
		if( file_exists( $xml ) )
		{
			// Load the parser and the XML file
			$parser = new SimpleXMLElement($xml, NULL, TRUE);
			$version = $parser->version;

			if( $version >= '3.2' && $version != 0 )
				return false;
		}

		return true;
	}

	// install with PHP CURL
	function _remoteInstaller($url)
	{
		jimport('joomla.installer.helper');
		jimport('joomla.installer.installer');
		if (!$url) return false;
		$filename = JInstallerHelper::downloadPackage($url);

		//$config = JFactory::getConfig();
		//$target	= $config->getValue('config.tmp_path').'/'.basename($filename);
		$app = JFactory::getApplication();
		$target = $app->getCfg('config.tmp_path').'/'.basename($filename);

		// Unpack
		$package	= JInstallerHelper::unpack($target);
		if (!$package)
		{
			// unable to find install package
		}

		// Install the package
		$msg		= '';
		$installer	= JInstaller::getInstance();

		if (!$installer->install($package['dir'])) {
			// There was an error installing the package
			$msg = JText::sprintf('INSTALLEXT', JText::_($package['type']), JText::_('Error'));
			$result = false;
		} else {
			// Package installed sucessfully
			$msg = JText::sprintf('INSTALLEXT', JText::_($package['type']), JText::_('Success'));
			$result = true;
		}

		// Clean up the install files
		if (!is_file($package['packagefile']))
		{
			//$package['packagefile'] = $config->getValue('config.tmp_path').'/'.$package['packagefile'];
			$package['packagefile'] = $app->getCfg('config.tmp_path').'/'.$package['packagefile'];
		}
		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

		return $result;
	}

	public function enablePlugin($plugin)
	{
		$db         = JFactory::getDBO();
		$version    = new JVersion();
		$joomla_ver = $version->getHelpVersion();

		$query	= 'UPDATE '.$db->quoteName('#__extensions').' SET '.$db->quoteName('enabled').' = '.$db->quote(1)
					.' WHERE '.$db->quoteName('element').' = '.$db->quote($plugin);

		$db->setQuery($query);

		if ( ! $db->query())
		{
			return $db->getErrorNum().':'.$db->getErrorMsg();
		}
		else
		{
			return null;
		}
	}
}

class CommunityInstallerDBAction
{
	function _getFields( $table = '#__community_groups' )
	{
		$result	= array();
		$db		= JFactory::getDBO();

		$query	= 'SHOW FIELDS FROM '.$db->quoteName( $table );

		$db->setQuery( $query );

		$fields	= $db->loadObjectList();

		foreach( $fields as $field )
		{
			$result[ $field->Field ]	= preg_replace( '/[(0-9)]/' , '' , $field->Type );
		}

		return $result;
	}

	function _isExistMenu()
	{
		$db		= JFactory::getDBO();
		$query	= 'SELECT COUNT(*) FROM '.$db->quoteName( '#__menu_types' ).' WHERE '
				. $db->quoteName( 'menutype' ).'='.$db->Quote( 'jomsocial' );
		$db->setQuery( $query );

		return $db->loadResult() > 0;
	}

	/*
	 * Check table column index whether exists or not.
	 * index name == column name.
	 */
	function _isExistTableColumn($tablename, $columnname)
	{
		$fields	= $this->_getFields($tablename);
		if(array_key_exists($columnname, $fields))
		{
			return true;
		}
		return false;
	}

	/*
	 * Check table index whether exists or not.
	 * index name.
	 */
	function _isExistTableIndex($tablename, $indexname)
	{
		$db		= JFactory::getDBO();

		$query	= 'SHOW INDEX FROM '.$db->quoteName( $tablename );

		$db->setQuery( $query );

		$indexes	= $db->loadObjectList();

		foreach( $indexes as $index )
		{
			$result[ $index->Key_name ]	= $index->Column_name;
		}

		if(array_key_exists($indexname, $result)){
			return true;
		}

		return false;
	}

	function _isExistDefaultConfig()
	{
		$db		= JFactory::getDBO();

		$query	= 'SELECT COUNT(*) FROM '
				. $db->quoteName( '#__community_config' ).' '
				. 'WHERE '.$db->quoteName( 'name' ).'='.$db->Quote( 'config' );
		$db->setQuery( $query );
		return $db->loadResult();
	}

	function cleanRegistrationTable()
	{
		$db	= JFactory::getDBO();

		$query = 'TRUNCATE TABLE '.$db->quoteName('#__community_register');

		$db->setQuery( $query );
		$db->query();
	}

	function cleanUserPointsTable($ruleArr = null)
	{
		$db	= JFactory::getDBO();

		if(is_null($ruleArr))
		{
			//this delete sql was cater for version prior to JomSocial 1.1
			$query = 'DELETE FROM '.$db->quoteName('#__community_userpoints') .' where '.$db->quoteName('rule_plugin') .' = '.$db->Quote('com_community') .' and '.$db->quoteName('action_string') .' in (
						'.$db->Quote('application.remove'). ','.$db->Quote('group.create') .','.$db->Quote('group.leave') .','.$db->Quote('discussion.create') .','.$db->Quote('friends.add') .','.$db->Quote('album.create')
						.','.$db->Quote('group.join') .','.$db->Quote('discussion.reply') .','.$db->Quote('group.wall.create') .','.$db->Quote('wall.create') .','.$db->Quote('profile.status.update') .','.$db->Quote('photo.upload')
						.','.$db->Quote('application.add') .')';
		}
		else
		{
			$fieldName	= implode('\',\'', $ruleArr);
			$query = 'DELETE FROM '.$db->quoteName('#__community_userpoints') .' where '.$db->quoteName('rule_plugin') .' = '.$db->Quote('com_community') .' and '.$db->quoteName('action_string') .' in ('.$db->Quote($fieldName) .')';
		}

		$db->setQuery( $query );
		$db->query();
	}

	function checkPhotoPrivacyUpdated()
	{
		$db		= JFactory::getDBO();

		$query	= 'SELECT COUNT(*) FROM '.$db->quoteName( '#__community_photos_albums' );
		$query	.= ' WHERE '.$db->quoteName('permissions') .' = '.$db->Quote('all');
		$db->setQuery( $query );

		$isUpdated	= ( $db->loadResult() > 0 ) ? false : true;

		return $isUpdated;
	}

	function deleteTableEntry($table, $column, $element)
	{
		$db		= JFactory::getDBO();

		// Try to remove the old record.
		$query	= 'DELETE FROM '.$db->quoteName( $table ).' '
		. 'WHERE '.$db->quoteName( $column ).'='.$db->quote($element);
		$db->setQuery( $query );
		$db->query();

		return $db->getErrorNum();
	}

	function insertTableEntry($table, $object)
	{
		$db		= JFactory::getDBO();
		return $db->insertObject( $table , $object );
	}

	function createDefaultTable()
	{
		$db		= JFactory::getDBO();

		$buffer = file_get_contents(JPATH_ROOT.'/administrator/components/com_community/install.mysql.utf8.sql');
		jimport('joomla.installer.helper');
		$queries = JInstallerHelper::splitSql($buffer);

		if (count($queries) != 0)
		{
			// Process each query in the $queries array (split out of sql file).
			foreach ($queries as $query)
			{
				$query = trim($query);
				if ($query != '' && $query{0} != '#')
				{
					$db->setQuery($query);
					if (!$db->query())
					{
						return $db->getErrorNum().':'.$db->getErrorMsg();
					}
				}
			}
		}

		return false;
	}

	function getDBVersion()
	{
		$db		= JFactory::getDBO();

		$sql = 'SELECT '.$db->quoteName('params').' '
			.'FROM '.$db->quoteName('#__community_config').' '
			.'WHERE '.$db->quoteName('name').' = '.$db->quote('dbversion') .' '
			.'LIMIT 1';
		$db->setQuery($sql);
		$result = $db->loadResult();

		return $result;
	}

	function insertDBVersion($dbversion)
	{
		$db		= JFactory::getDBO();

		$query	= 'INSERT INTO '.$db->quoteName( '#__community_config' )
				. '('
						. $db->quoteName( 'name' ).', '
						. $db->quoteName( 'params' )
				. ')'
				. 'VALUES('
						. $db->quote( 'dbversion' ).', '
						. $db->quote( $dbversion )
				. ')';
		$db->setQuery( $query );
		$db->Query();
	}

	function updateDBVersion($dbversion)
	{
		$db		= JFactory::getDBO();

		$query	= 'UPDATE '.$db->quoteName( '#__community_config' )
				. 'SET '
						. $db->quoteName( 'params' ).' = '.$db->quote( $dbversion ).' '
				. 'WHERE'
						. $db->quoteName( 'name' ).' = '.$db->quote( 'dbversion' ).' ';

		$db->setQuery( $query );
		$db->Query();
	}

	function updateGroupMembersTable()
	{
		$db				= JFactory::getDBO();

		// Update older admin values first.
		$query	= 'UPDATE '.$db->quoteName( '#__community_groups_members' ).' '
				. 'SET '.$db->quoteName( 'permissions' ).'='.$db->Quote( '1' ).' '
				. 'WHERE '.$db->quoteName( 'permissions' ) .'='.$db->Quote( 'admin' );
		$db->setQuery( $query );
		$db->query();

		// Update older member values first.
		$query	= 'UPDATE '.$db->quoteName( '#__community_groups_members' ).' '
				. 'SET '.$db->quoteName( 'permissions' ).'='.$db->Quote( '0' ).' '
				. 'WHERE '.$db->quoteName( 'permissions' ) .'='.$db->Quote( 'member' );
		$db->setQuery( $query );
		$db->query();

		// Modify the column type
		$query	= 'ALTER TABLE '.$db->quoteName('#__community_groups_members' ).' '
				. 'CHANGE '.$db->quoteName('permissions').' '.$db->quoteName('permissions').' INT(1) NOT NULL';
		$db->setQuery( $query );
		$db->query();

		return true;
	}
}

class CommunityInstallerVerifier
{
	var $display;
	var $dbhelper;

	function __construct()
	{
		$this->display	= new communityInstallerDisplay();
		$this->dbhelper	= new communityInstallerDBAction();
	}

	function isLatestFriendTable()
	{
		$fields	= $this->dbhelper->_isExistTableColumn( '#__community_users', 'friendcount' );
		return $fields;
	}

	function isLatestGroupMembersTable()
	{
		$fields			= $this->dbhelper->_getFields( '#__community_groups_members' );
		$result			= array();
		if( array_key_exists('permissions' , $fields) )
		{
			if( $fields['permissions'] == 'varchar' )
			{
				return false;
			}
		}
		return true;
	}

	function isPhotoPrivacyUpdated()
	{
		return $this->dbhelper->checkPhotoPrivacyUpdated();
	}

	function isLatestGroupTable()
	{
		$fields	= $this->dbhelper->_getFields();

		if(!array_key_exists( 'membercount' , $fields ) )
		{
			return false;
		}

		if(!array_key_exists( 'wallcount' , $fields ) )
		{
			return false;
		}

		if(!array_key_exists( 'discusscount' , $fields ) )
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to check if the GD library exist
	 *
	 * @returns boolean	return check status
	 **/
	function testImage()
	{
		$msg = '
			<style type="text/css">
			.Yes {
				color:#46882B;
				font-weight:bold;
			}
			.No {
				color:#CC0000;
				font-weight:bold;
			}
			.jomsocial_install tr {

			}
			.jomsocial_install td {
				color: #888;
				padding: 3px;
			}
			.jomsocial_install td.item {
				color: #333;
			}
			</style>
			<div class="install-body" style="background: #fbfbfb; border: solid 1px #ccc; -moz-border-radius: 5px; -webkit-border-radius: 5px; padding: 20px; width: 50%;">
				<p>If any of these items are not supported (marked as <span class="No">No</span>), your system does not meet the requirements for installation. Some features might not be available. Please take appropriate actions to correct the errors.</p>
					<table class="content jomsocial_install" style="width: 100%; background">
						<tbody>';

		// @rule: Test for JPG image extensions
		$type = 'JPEG';
		if( function_exists( 'imagecreatefromjpeg' ) )
		{
			$msg .= $this->display->testImageMessage($type, true);
		}
		else
		{
			$msg .= $this->display->testImageMessage($type, false);
		}

		// @rule: Test for png image extensions
		$type = 'PNG';
		if( function_exists( 'imagecreatefrompng' ) )
		{
			$msg .= $this->display->testImageMessage($type, true);
		}
		else
		{
			$msg .= $this->display->testImageMessage($type, false);
		}

		// @rule: Test for gif image extensions
		$type = 'GIF';
		if( function_exists( 'imagecreatefromgif' ) )
		{
			$msg .= $this->display->testImageMessage($type, true);
		}
		else
		{
			$msg .= $this->display->testImageMessage($type, false);
		}

		$type = 'GD';
		if( function_exists( 'imagecreatefromgd' ) )
		{
			$msg .= $this->display->testImageMessage($type, true);
		}
		else
		{
			$msg .= $this->display->testImageMessage($type, false);
		}

		$type = 'GD2';
		if( function_exists( 'imagecreatefromgd2' ) )
		{
			$msg .= $this->display->testImageMessage($type, true);
		}
		else
		{
			$msg .= $this->display->testImageMessage($type, false);
		}

		$type = 'cURL';
		if( in_array  ('curl', get_loaded_extensions()))
		{
			$msg .= $this->display->testImageMessage($type, true);
		}
		else
		{
			$msg .= $this->display->testImageMessage($type, false);
		}

		$msg .= '
						</tbody>
					</table>

			</div>';

		return $msg;
	}

	function isFileExist($file)
	{
		return file_exists($file);
	}
}

class CommunityInstallerUpdate
{
	var $verifier;
	var $dbhelper;
	var $helper;

	function __construct()
	{
		$this->verifier = new communityInstallerVerifier();
		$this->dbhelper = new communityInstallerDBAction();
		$this->helper 	= new communityInstallerHelper();
	}

	function update_0()
	{
		$db = JFactory::getDBO();
		$result = new stdClass();
		$status = true;
		$html = "";

		// Patch for groups.
		$html .= '<div style="width:150px; float:left;">'.JText::_('COM_COMMUNITY_INSTALLATION_PATCHING_DATABASE').'</div>';
		if( !$this->verifier->isLatestGroupTable() || !$this->verifier->isLatestFriendTable() || !$this->verifier->isPhotoPrivacyUpdated())
		{
			$html	.= $this->helper->failedStatus;
			ob_start();
			?>
			<div style="font-weight: 700; color: red;">
				Looks like you are upgrading from an older version of JomSocial. There is an update
				in the newer version of JomSocial that requires a maintenance to be carried out. Kindly please
				proceed to the maintenance section at <a href="index.php?option=com_community&view=maintenance">HERE</a>.
			</div>
			<?php
			$html .= ob_get_contents();
			@ob_end_clean();

			$result->html = $html;
			$result->status = false;
			$result->errorCode = '7b';
			$result->stopUpdate = true;
			return $result;
		}
		else
		{
			$html .= $this->helper->successStatus;
		}

		// Test if need to update the field 'permissions' in #__community_groups_members
		if( !$this->verifier->isLatestGroupMembersTable() )
		{
			$this->dbhelper->updateGroupMembersTable();
		}

		// add new path column.
		if(!$this->dbhelper->_isExistTableColumn( '#__community_photos_albums' , 'path' ) )
		{
			$sql = 'ALTER TABLE '.$db->quoteName('#__community_photos_albums') .' ADD '.$db->quoteName('path') .' VARCHAR( 255 ) NULL';
			$db->setQuery($sql);
			$db->query();
		}

		// add ip to register table
		if(!$this->dbhelper->_isExistTableColumn( '#__community_register' , 'ip' ) )
		{
			$sql = 'ALTER TABLE '.$db->quoteName('#__community_register') .' ADD '.$db->quoteName('ip') .' VARCHAR( 25 ) NULL';
			$db->setQuery($sql);
			$db->query();
		}

		// add last replied column
		if(!$this->dbhelper->_isExistTableColumn( '#__community_groups_discuss' , 'lastreplied' ) )
		{
			$sql = 'ALTER TABLE '.$db->quoteName('#__community_groups_discuss') .' ADD '.$db->quoteName('lastreplied') .' DATETIME NOT NULL AFTER '.$db->quoteName('message') ;
			$db->setQuery($sql);
			$db->query();
		}

		$result->html	= $html;
		$result->status = $status;

		if(!$status)
		{
			$result->errorCode = '7b';
		}
		return $result;
	}

	function update_1()
	{
		$db = JFactory::getDBO();
		$result = new stdClass();
		$status = true;
		$html = "";
		$errorCode = "";

		if(!$this->dbhelper->_isExistTableIndex('#__community_msg_recepient', 'idx_isread_to_deleted'))
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_msg_recepient' ).' ADD INDEX '.$db->quoteName('idx_isread_to_deleted') .' ('.$db->quoteName('is_read') .', '.$db->quoteName('to') .', '.$db->quoteName('deleted') .')';
			$db->setQuery( $query );
			$db->query();
		}

		if(!$this->dbhelper->_isExistTableIndex('#__community_apps', 'idx_userid'))
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_apps' ).' ADD INDEX '.$db->quoteName('idx_userid') .' ('.$db->quoteName('userid') .')';
			$db->setQuery( $query );
			$db->query();
		}

		if(!$this->dbhelper->_isExistTableIndex('#__community_apps', 'idx_user_apps'))
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_apps' ).' ADD INDEX '.$db->quoteName('idx_user_apps') .' ('.$db->quoteName('userid') .', '.$db->quoteName('apps') .')';
			$db->setQuery( $query );
			$db->query();
		}

		if(!$this->dbhelper->_isExistTableIndex('#__community_connection', 'idx_connect_to'))
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_connection' ).' ADD INDEX '.$db->quoteName('idx_connect_to') .' ('.$db->quoteName('connect_to') .')';
			$db->setQuery( $query );
			$db->query();
		}

		if(!$this->dbhelper->_isExistTableIndex('#__community_groups_members', 'idx_memberid'))
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_groups_members' ).' ADD INDEX '.$db->quoteName('idx_memberid') .' ('.$db->quoteName('memberid') .')';
			$db->setQuery( $query );
			$db->query();
		}

		if(!$this->dbhelper->_isExistTableIndex('#__community_fields_values', 'idx_user_fieldid'))
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_fields_values' ).' ADD INDEX '.$db->quoteName('idx_user_fieldid').' ('.$db->quoteName('user_id').', '.$db->quoteName('field_id') .')';
			$db->setQuery( $query );
			$db->query();
		}

		$result->html	= $html;
		$result->status = $status;
		if(!$status)
		{
			$result->errorCode = $errorCode;
		}
		return $result;
	}

	function update_2()
	{
		$db = JFactory::getDBO();
		$result = new stdClass();
		$status = true;
		$html = "";
		$errorCode = "";

		if(!$this->dbhelper->_isExistTableColumn( '#__community_photos_albums', 'type' ) )
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_photos_albums' ).' ADD '.$db->quoteName('type') .' VARCHAR(255) NOT NULL DEFAULT '.$db->Quote('user');
			$db->setQuery( $query );
			$db->query();
		}

		if(!$this->dbhelper->_isExistTableIndex('#__community_photos_albums', 'idx_type'))
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_photos_albums' ).' ADD INDEX '.$db->quoteName('idx_type') .' ('.$db->quoteName('type').')';
			$db->setQuery( $query );
			$db->query();
		}

		if(!$this->dbhelper->_isExistTableColumn( '#__community_photos_albums', 'groupid' ) )
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_photos_albums' ).' ADD '.$db->quoteName('groupid') .' INT( 11 ) NOT NULL DEFAULT '.$db->Quote('0') .' AFTER '.$db->quoteName('type');
			$db->setQuery( $query );
			$db->query();
		}

		if(!$this->dbhelper->_isExistTableIndex('#__community_photos_albums', 'idx_groupid'))
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_photos_albums' ).' ADD INDEX '.$db->quoteName('idx_groupid') .' ('.$db->quoteName('groupid') .')';
			$db->setQuery( $query );
			$db->query();
		}

		if(!$this->dbhelper->_isExistTableIndex('#__community_photos_albums', 'idx_albumtype'))
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_photos_albums' ).' ADD INDEX '.$db->quoteName('idx_albumtype') .' ('.$db->quoteName('id') .','.$db->quoteName('type') .')';
			$db->setQuery( $query );
			$db->query();
		}

		if(!$this->dbhelper->_isExistTableIndex('#__community_photos_albums', 'idx_creatortype'))
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_photos_albums' ).' ADD INDEX '.$db->quoteName('idx_creatortype') .' ('.$db->quoteName('creator') .','.$db->quoteName('type') .')';
			$db->setQuery( $query );
			$db->query();
		}

		if(!$this->dbhelper->_isExistTableColumn( '#__community_videos', 'groupid' ) )
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_videos' ).' ADD '.$db->quoteName('groupid') .' INT( 11 ) UNSIGNED NOT NULL DEFAULT '.$db->Quote(0);
			$db->setQuery( $query );
			$db->query();
		}

		if(!$this->dbhelper->_isExistTableIndex('#__community_videos', 'idx_groupid'))
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_videos' ).' ADD INDEX '.$db->quoteName('idx_groupid') .' ('.$db->quoteName('groupid') .')';
			$db->setQuery( $query );
			$db->query();
		}

		if(!$this->dbhelper->_isExistTableColumn( '#__community_groups', 'params' ) )
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_groups' ).' ADD '.$db->quoteName('params') .' TEXT NOT NULL AFTER '.$db->quoteName('membercount') ;
			$db->setQuery( $query );
			$db->query();
		}

		if(!$this->dbhelper->_isExistTableColumn( '#__community_connection', 'created' ) )
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_connection' ).' ADD '.$db->quoteName('created') .' DATETIME DEFAULT NULL';
			$db->setQuery( $query );
			$db->query();
		}

		if(!$this->dbhelper->_isExistTableColumn( '#__community_fields', 'registration' ) )
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_fields' ).' ADD '.$db->quoteName('registration') .' tinyint(1) DEFAULT 1';
			$db->setQuery( $query );
			$db->query();
		}

		$result->html	= $html;
		$result->status = $status;
		if(!$status)
		{
			$result->errorCode = $errorCode;
		}
		return $result;
	}

	function update_3()
	{
		$db = JFactory::getDBO();
		$result = new stdClass();
		$status = true;
		$html = "";
		$errorCode = "";

		if(!$this->dbhelper->_isExistTableIndex('#__community_connection', 'idx_connect_from'))
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_connection' ).' ADD INDEX '.$db->quoteName('idx_connect_from') .' ('.$db->quoteName('connect_from') .')';
			$db->setQuery( $query );
			$db->query();
		}

		if(!$this->dbhelper->_isExistTableIndex('#__community_connection', 'idx_connect_tofrom'))
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_connection' ).' ADD INDEX '.$db->quoteName('idx_connect_tofrom') .' ('.$db->quoteName('connect_to') .', '.$db->quoteName('connect_from') .')';
			$db->setQuery( $query );
			$db->query();
		}

		if(!$this->dbhelper->_isExistTableIndex('#__community_activities', 'idx_activities_like'))
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_activities' ).' ADD INDEX '.$db->quoteName('idx_activities_like') .' ('.$db->quoteName('like_id') .', '.$db->quoteName('like_type') .')';
			$db->setQuery( $query );
			$db->query();
		}

		if(!$this->dbhelper->_isExistTableIndex('#__community_activities', 'idx_activities_comment'))
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_activities' ).' ADD INDEX '.$db->quoteName('idx_activities_comment') .' ('.$db->quoteName('comment_id') .', '.$db->quoteName('comment_type') .')';
			$db->setQuery( $query );
			$db->query();
		}

                 if( !$this->dbhelper->_isExistTableIndex('#__community_users', 'alias') )
                {
                        $query	= 'ALTER TABLE '.$db->quoteName( '#__community_users' ).' ADD INDEX '.$db->quoteName( 'alias' );
                        $db->setQuery( $query );
                        $db->query();
                }

		$result->html	= $html;
		$result->status = $status;
		if(!$status)
		{
			$result->errorCode = $errorCode;
		}
		return $result;
	}

	function update_4()
	{
		$db = JFactory::getDBO();
		$result = new stdClass();
		$status = true;
		$html = "";
		$errorCode = "";

		$query	= 'UPDATE '.$db->quoteName( '#__community_groups_discuss' ).' SET '.$db->quoteName( 'lastreplied' ).' =  '.$db->quoteName( 'created' ).' WHERE  '.$db->quoteName( 'lastreplied' ).' = '.$db->quote( '0000-00-00 00:00:00' );
		$db->setQuery( $query );
		$db->query();

                $query  =   'INSERT INTO '.$db->quoteName('#__community_userpoints').' ( '.$db->quoteName('rule_name').', '.$db->quoteName('rule_description').', '.$db->quoteName('rule_plugin').', '.$db->quoteName('action_string').', '.$db->quoteName('component').', '.$db->quoteName('access').', '.$db->quoteName('points').', '.$db->quoteName('published').', '.$db->quoteName('system')
                           .') VALUES ('.$db->Quote('Update Event').', '.$db->Quote('Give points when registered user update the event.').', '.$db->Quote('com_community').', '.$db->Quote('events.update').', '.$db->Quote('').', '.$db->Quote('1').', '.$db->Quote('1').', '.$db->Quote('1').', '.$db->Quote('1').')';
                $db->setQuery( $query );
		$db->query();

		$result->html	= $html;
		$result->status = $status;
		if(!$status)
		{
			$result->errorCode = $errorCode;
		}
		return $result;
	}

	function update_5()
	{
		$db = JFactory::getDBO();
		$result = new stdClass();
		$status = true;
		$html = "";
		$errorCode = "";

		if( !$this->dbhelper->_isExistTableColumn('#__community_connection', 'msg') )
		{
    		$query	= 'ALTER TABLE '.$db->quoteName( '#__community_connection' ).' ADD '.$db->quoteName( 'msg' ).' TEXT NOT NULL ';
    		$db->setQuery( $query );
    		$db->query();
		}

		if( !$this->dbhelper->_isExistTableColumn('#__community_photos', 'filesize') )
		{
    		$query	= 'ALTER TABLE '.$db->quoteName( '#__community_photos' ).' ADD '.$db->quoteName( 'filesize' ).' INT(11) NOT NULL DEFAULT '.$db->Quote(0);
    		$db->setQuery( $query );
    		$db->query();
		}

		if( !$this->dbhelper->_isExistTableColumn('#__community_photos', 'storage') )
		{
    		$query	= 'ALTER TABLE '.$db->quoteName( '#__community_photos' ).' ADD '.$db->quoteName( 'storage' ).' VARCHAR( 64 ) NOT NULL DEFAULT '.$db->Quote('file') .', ADD INDEX '.$db->quoteName('idx_storage') .' ( '.$db->quoteName('storage') .' )';
    		$db->setQuery( $query );
    		$db->query();
		}

		if( !$this->dbhelper->_isExistTableColumn('#__community_videos', 'filesize') )
		{
    		$query	= 'ALTER TABLE '.$db->quoteName( '#__community_videos' ).' ADD '.$db->quoteName( 'filesize' ).' INT(11) NOT NULL DEFAULT '.$db->Quote(0);
    		$db->setQuery( $query );
    		$db->query();
		}

        if( !$this->dbhelper->_isExistTableColumn('#__community_videos', 'storage') )
        {
    		$query	= 'ALTER TABLE '.$db->quoteName( '#__community_videos' ).' ADD '.$db->quoteName( 'storage' ).' VARCHAR( 64 ) NOT NULL DEFAULT '.$db->Quote('file') .', ADD INDEX '.$db->quoteName('idx_storage') .' ( '.$db->quoteName('storage') .' ) ';
    		$db->setQuery( $query );
    		$db->query();
		}


		//get video folder
		$query	= 'SELECT  '.$db->quoteName( 'params' ).' FROM '.$db->quoteName( '#__community_config' ).' WHERE '.$db->quoteName( 'name' ).' = '.$db->quote('config');
		$db->setQuery( $query );
		$row = $db->loadResult();
		$params	= new JRegistry( $row );
		$videofolder = $params->get('videofolder', 'images');

		$query	= 'UPDATE '.$db->quoteName( '#__community_videos' ).' SET '.$db->quoteName( 'thumb' ).' = CONCAT('.$db->quote( $videofolder.'/' ).', '.$db->quoteName( 'thumb' ).') ';
		$db->setQuery( $query );
		$db->query();

		$query	= 'UPDATE '.$db->quoteName( '#__community_videos' ).' SET '.$db->quoteName( 'path' ).' = CONCAT('.$db->quote( $videofolder.'/' ).', '.$db->quoteName( 'path' ).') WHERE '.$db->quoteName( 'type' ).' = '.$db->quote( 'file' );
		$db->setQuery( $query );
		$db->query();

		$result->html	= $html;
		$result->status = $status;
		if(!$status)
		{
			$result->errorCode = $errorCode;
		}
		return $result;
	}

	function update_6()
	{
		$db = JFactory::getDBO();
		$result = new stdClass();
		$status = true;
		$html = "";
		$errorCode = "";

        if( !$this->dbhelper->_isExistTableColumn('#__community_photos', 'ordering') )
        {
    		$query	= 'ALTER TABLE '.$db->quoteName( '#__community_photos' ).' ADD '.$db->quoteName( 'ordering' ).' INT( 11 ) NOT NULL DEFAULT '.$db->Quote(0);
    		$db->setQuery( $query );
    		$db->query();
    		}

        if( !$this->dbhelper->_isExistTableColumn('#__community_events', 'latitude') )
        {
    		$query	= 'ALTER TABLE '.$db->quoteName( '#__community_events' ).' ADD '.$db->quoteName( 'latitude' ).' float NOT NULL DEFAULT '.$db->Quote(255);
    		$db->setQuery( $query );
    		$db->query();
    		}

        if( !$this->dbhelper->_isExistTableColumn('#__community_events', 'longitude') )
        {
    		$query	= 'ALTER TABLE '.$db->quoteName( '#__community_events' ).' ADD '.$db->quoteName( 'longitude' ).' float NOT NULL DEFAULT '.$db->Quote(255);
    		$db->setQuery( $query );
    		$db->query();
    		}

        if( !$this->dbhelper->_isExistTableColumn('#__community_users', 'alias') )
        {
    		$query	= 'ALTER TABLE '.$db->quoteName( '#__community_users' ).' ADD '.$db->quoteName( 'alias' ).' VARCHAR(255) NOT NULL';
    		$db->setQuery( $query );
    		$db->query();
    		}

        if( !$this->dbhelper->_isExistTableColumn('#__community_users', 'latitude') )
        {
    		$query	= 'ALTER TABLE '.$db->quoteName( '#__community_users' ).' ADD '.$db->quoteName( 'latitude' ).' float NOT NULL DEFAULT '.$db->Quote(255);
    		$db->setQuery( $query );
    		$db->query();
    		}

        if( !$this->dbhelper->_isExistTableColumn('#__community_users', 'longitude') )
        {
    		$query	= 'ALTER TABLE '.$db->quoteName( '#__community_users' ).' ADD '.$db->quoteName( 'longitude' ).' float NOT NULL DEFAULT '.$db->Quote(255);
    		$db->setQuery( $query );
    		$db->query();
    		}

        if( !$this->dbhelper->_isExistTableColumn('#__community_apps', 'position') )
        {
    		$query	= 'ALTER TABLE '.$db->quoteName( '#__community_apps' ).' ADD '.$db->quoteName( 'position' ).' VARCHAR(50) NOT NULL DEFAULT '.$db->Quote('content');
    		$db->setQuery( $query );
    		$db->query();
    		}

        if( !$this->dbhelper->_isExistTableColumn('#__community_mailq', 'template') )
        {
    		$query	= 'ALTER TABLE '.$db->quoteName( '#__community_mailq' ).' ADD '.$db->quoteName( 'template' ).' VARCHAR(255) NOT NULL';
    		$db->setQuery( $query );
    		$db->query();
    		}

        if( !$this->dbhelper->_isExistTableColumn('#__community_mailq', 'params') )
        {
    		$query	= 'ALTER TABLE '.$db->quoteName( '#__community_mailq' ).' ADD '.$db->quoteName( 'params' ).' TEXT NOT NULL';
    		$db->setQuery( $query );
    		$db->query();
    	}

		$result->html	= $html;
		$result->status = $status;
		if(!$status)
		{
			$result->errorCode = $errorCode;
		}
		return $result;
	}

	function update_7()
	{
		$db = JFactory::getDBO();
		$result = new stdClass();
		$status = true;
		$html = "";
		$errorCode = "";

        if( !$this->dbhelper->_isExistTableColumn('#__community_photos', 'hits') )
        {
    		$query	= 'ALTER TABLE '.$db->quoteName( '#__community_photos' ).' ADD '.$db->quoteName( 'hits' ).' INT( 11 ) NOT NULL DEFAULT '.$db->Quote(0);
    		$db->setQuery( $query );
    		$db->query();
    	}

    	if( !$this->dbhelper->_isExistTableColumn('#__community_events_category', 'parent') )
    	{
    		$query = 'ALTER TABLE '.$db->quoteName('#__community_events_category')
    			. ' ADD '.$db->quoteName('parent')
    			. ' INT( 11 ) NOT NULL DEFAULT '.$db->Quote(0) .' AFTER '.$db->quoteName('id');
    		$db->setQuery($query);
    		$db->query();
		}

		if( !$this->dbhelper->_isExistTableColumn('#__community_groups_category', 'parent') )
    	{
    		$query = 'ALTER TABLE '.$db->quoteName('#__community_groups_category')
    			. ' ADD '.$db->quoteName('parent')
    			. ' INT( 11 ) NOT NULL DEFAULT '.$db->Quote(0) .' AFTER '.$db->quoteName('id');
    		$db->setQuery($query);
    		$db->query();
		}

		if( !$this->dbhelper->_isExistTableColumn('#__community_photos_albums', 'hits') )
    	{
    		$query = 'ALTER TABLE '.$db->quoteName('#__community_photos_albums')
    			. ' ADD '.$db->quoteName('hits')
    			. ' INT( 11 ) NOT NULL DEFAULT '.$db->Quote(0);
    		$db->setQuery($query);
    		$db->query();
		}

		if( !$this->dbhelper->_isExistTableColumn('#__community_users', 'profile_id') )
    	{
    		$query = 'ALTER TABLE '.$db->quoteName('#__community_users')
    			. ' ADD '.$db->quoteName('profile_id')
    			. ' INT( 11 ) NOT NULL DEFAULT '.$db->Quote(0);
    		$db->setQuery($query);
    		$db->query();
		}

		if( !$this->dbhelper->_isExistTableColumn('#__community_users', 'watermark_hash') )
    	{
    		$query = 'ALTER TABLE '.$db->quoteName('#__community_users')
    			. ' ADD '.$db->quoteName('watermark_hash')
    			. ' VARCHAR( 255 ) NOT NULL';
    		$db->setQuery($query);
    		$db->query();
		}

		if( !$this->dbhelper->_isExistTableColumn('#__community_users', 'storage') )
    	{
    		$query = 'ALTER TABLE '.$db->quoteName('#__community_users')
    			. ' ADD '.$db->quoteName('storage')
    			. ' VARCHAR( 64 ) NOT NULL DEFAULT '.$db->Quote('file');
    		$db->setQuery($query);
    		$db->query();
		}

		if( !$this->dbhelper->_isExistTableColumn('#__community_register', 'firstname') )
    	{
    		$query = 'ALTER TABLE '.$db->quoteName('#__community_register')
    			. ' ADD '.$db->quoteName('firstname')
    			. ' VARCHAR( 180 ) NOT NULL AFTER '.$db->quoteName('name');
    		$db->setQuery($query);
    		$db->query();
		}

		if( !$this->dbhelper->_isExistTableColumn('#__community_register', 'lastname') )
    	{
    		$query = 'ALTER TABLE '.$db->quoteName('#__community_register')
    			. ' ADD '.$db->quoteName('lastname')
    			. ' VARCHAR( 180 ) NOT NULL AFTER '.$db->quoteName('firstname');
    		$db->setQuery($query);
    		$db->query();
		}

		if( !$this->dbhelper->_isExistTableColumn('#__community_events', 'offset') )
    	{
    		$query = 'ALTER TABLE '.$db->quoteName('#__community_events')
    			. ' ADD '.$db->quoteName('offset')
    			. ' VARCHAR(5) DEFAULT NULL';

    		$db->setQuery($query);
    		$db->query();
		}

		if( !$this->dbhelper->_isExistTableColumn('#__community_groups_discuss', 'lock') )
    	{
    		$query = 'ALTER TABLE '.$db->quoteName('#__community_groups_discuss')
    			. ' ADD '.$db->quoteName('lock')
    			. ' TINYINT(1) DEFAULT '.$db->Quote(0);

    		$db->setQuery($query);
    		$db->query();
		}

		if( !$this->dbhelper->_isExistTableColumn('#__community_fields', 'params') )
    	{
    		$query = 'ALTER TABLE '.$db->quoteName('#__community_fields')
    			. ' ADD '.$db->quoteName('params')
    			. ' TEXT NOT NULL';

    		$db->setQuery($query);
    		$db->query();
		}

		$result->html	= $html;
		$result->status = $status;
		if(!$status)
		{
			$result->errorCode = $errorCode;
		}
		return $result;

    }

    function update_8()
    {
    	$db		= JFactory::getDBO();
		$result = new stdClass();
		$status = true;
    	$errorCode ='';
    	$html ='';
    	// Menu system now is integrated with Joomla
    	if( !$this->dbhelper->_isExistMenu() )
    	{
	    	$query	= 'INSERT INTO '.$db->quoteName( '#__menu_types' ).' ('.$db->quoteName('menutype') .','.$db->quoteName('title') .','.$db->quoteName('description') .') VALUES '
	    			. '( '.$db->Quote( 'jomsocial' ).','.$db->Quote( 'JomSocial toolbar' ).','.$db->Quote( 'Toolbar items for JomSocial toolbar').')';
			$db->setQuery( $query );
			$db->Query();
			$menuId	= $db->insertid();

			// Create default toolbar menu's since the jomsocial toolbar menu doesn't exist.
			$status = addDefaultToolbarMenus();
		}
  		$result->html	= $html;
		$result->status = $status;
		if(!$status)
		{
			$result->errorCode = '8f';
		}
		return $result;
	}

	function update_9()
	{
		$db		= JFactory::getDBO();
		$result = new stdClass();
		$status = true;
		$html = "";
		$errorCode = "";

		//ALTER TABLE `jos_community_users` ADD `search_email` TINYINT( 1 ) NOT NULL DEFAULT '1';
        if( !$this->dbhelper->_isExistTableColumn('#__community_users', 'search_email') )
        {
			$query	= 'ALTER TABLE '.$db->quoteName('#__community_users')
					. ' ADD '.$db->quoteName('search_email')
					. ' TINYINT( 1 ) NOT NULL DEFAULT '.$db->quote(1);
    		$db->setQuery( $query );
    		$db->query();
    	}



		//ALTER TABLE `jos_community_fields_values` ADD `access` TINYINT( 3 ) NOT NULL DEFAULT '0' AFTER `value` ;
        if( !$this->dbhelper->_isExistTableColumn('#__community_fields_values', 'access') )
        {
			$query	= 'ALTER TABLE '.$db->quoteName('#__community_fields_values')
					. ' ADD '.$db->quoteName('access')
					. ' TINYINT( 3 ) NOT NULL DEFAULT '.$db->quote(0);
    		$db->setQuery( $query );
    		$db->query();
		//ALTER TABLE `jos_community_fields_values` ADD INDEX ( `access` ) ;
			$query	= 'ALTER TABLE '.$db->quoteName('#__community_fields_values')
					. ' ADD INDEX ('.$db->quoteName('access').')';
    		$db->setQuery( $query );
    		$db->query();
    	}


		//ALTER TABLE `jos_community_photos_albums` ADD `location` TEXT NOT NULL DEFAULT '',
		//ADD `latitude` FLOAT NOT NULL DEFAULT '255',
		//ADD `longitude` FLOAT NOT NULL DEFAULT '255';
        if( !$this->dbhelper->_isExistTableColumn('#__community_photos_albums', 'location') )
        {
			$query	= 'ALTER TABLE '.$db->quoteName('#__community_photos_albums')
					. ' ADD '.$db->quoteName('location')
					. ' TEXT NOT NULL DEFAULT '.$db->quote('').','
					. ' ADD '.$db->quoteName('latitude')
					. ' FLOAT NOT NULL DEFAULT '.$db->quote(255).','
					. ' ADD '.$db->quoteName('longitude')
					. ' FLOAT NOT NULL DEFAULT '.$db->quote(255);
    		$db->setQuery( $query );
    		$db->query();
    	}


		//ALTER TABLE `jos_community_videos` ADD `location` TEXT NOT NULL DEFAULT '',
		//ADD `latitude` FLOAT NOT NULL DEFAULT '255',
		//ADD `longitude` FLOAT NOT NULL DEFAULT '255';
        if( !$this->dbhelper->_isExistTableColumn('#__community_videos', 'location') )
        {
			$query	= 'ALTER TABLE '.$db->quoteName('#__community_videos')
					. ' ADD '.$db->quoteName('location')
					. ' TEXT NOT NULL DEFAULT '.$db->quote('').','
					. ' ADD '.$db->quoteName('latitude')
					. ' FLOAT NOT NULL DEFAULT '.$db->quote(255).','
					. ' ADD '.$db->quoteName('longitude')
					. ' FLOAT NOT NULL DEFAULT '.$db->quote(255);
    		$db->setQuery( $query );
    		$db->query();
    	}


		//ALTER TABLE `jos_community_activities` ADD `location` TEXT NOT NULL DEFAULT '',
		//ADD `latitude` FLOAT NOT NULL DEFAULT '255',
		//ADD `longitude` FLOAT NOT NULL DEFAULT '255';
        if( !$this->dbhelper->_isExistTableColumn('#__community_activities', 'location') )
        {
			$query	= 'ALTER TABLE '.$db->quoteName('#__community_activities')
					. ' ADD '.$db->quoteName('location')
					. ' TEXT NOT NULL DEFAULT '.$db->quote('').','
					. ' ADD '.$db->quoteName('latitude')
					. ' FLOAT NOT NULL DEFAULT '.$db->quote(255).','
					. ' ADD '.$db->quoteName('longitude')
					. ' FLOAT NOT NULL DEFAULT '.$db->quote(255);
    		$db->setQuery( $query );
    		$db->query();
    	}
		//ALTER TABLE `jos_community_photos` ADD `status` VARCHAR( 200 ) NOT NULL;
        if( !$this->dbhelper->_isExistTableColumn('#__community_photos', 'status') )
        {
			$query	= 'ALTER TABLE'.$db->quoteName('#__community_photos')
					. ' ADD '.$db->quoteName('status')
					. ' VARCHAR( 200 ) NOT NULL';
    		$db->setQuery( $query );
    		$db->query();
    	}

		//ALTER TABLE `jos_community_photos_albums` ADD `default` TINYINT( 1 ) NOT NULL DEFAULT '0';
        if( !$this->dbhelper->_isExistTableColumn('#__community_photos_albums', 'default') )
        {
			$query	= 'ALTER TABLE'.$db->quoteName('#__community_photos_albums')
					. ' ADD '.$db->quoteName('default')
					. ' TINYINT( 1 ) NOT NULL DEFAULT '.$db->quote(0);
    		$db->setQuery( $query );
    		$db->query();
    	}

		//ALTER TABLE `jos_community_activities`
		//ADD `comment_id` INT( 10 ) NOT NULL ,
		//ADD `comment_type` VARCHAR( 200 ) NOT NULL;
        if( !$this->dbhelper->_isExistTableColumn('#__community_activities', 'comment_id') )
        {
			$query	= 'ALTER TABLE '.$db->quoteName('#__community_activities')
					. ' ADD '.$db->quoteName('comment_id')
					. ' INT( 10 ) NOT NULL,'
					. ' ADD '.$db->quoteName('comment_type')
					. ' VARCHAR( 200 ) NOT NULL';
    		$db->setQuery( $query );
    		$db->query();
    	}

		//ALTER TABLE `jos_community_activities`
		//ADD `like_id` INT( 10 ) NOT NULL ,
		//ADD `like_type` VARCHAR( 200 ) NOT NULL;
        if( !$this->dbhelper->_isExistTableColumn('#__community_activities', 'like_id') )
        {
			$query	= 'ALTER TABLE '.$db->quoteName('#__community_activities')
					. ' ADD '.$db->quoteName('like_id')
					. ' INT( 10 ) NOT NULL,'
					. ' ADD '.$db->quoteName('like_type')
					. ' VARCHAR( 200 ) NOT NULL';
    		$db->setQuery( $query );
    		$db->query();
    	}

		//ALTER TABLE `jos_community_likes` CHANGE `element` `element` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
		$query	= 'ALTER TABLE '.$db->quoteName('#__community_likes')
				. ' CHANGE '.$db->quoteName('element').' '.$db->quoteName('element')
				. ' VARCHAR( 200 ) NOT NULL';
		$db->setQuery( $query );
		$db->query();

		//ALTER TABLE `jos_community_likes` CHANGE `uid` `uid` INT( 10 ) NOT NULL ;
		$query	= 'ALTER TABLE '.$db->quoteName('#__community_likes')
				. ' CHANGE '.$db->quoteName('uid').' '.$db->quoteName('uid')
				. ' INT( 10 ) NOT NULL';
		$db->setQuery( $query );
		$db->query();

		//ALTER TABLE `jos_community_likes` ADD INDEX ( `element` , `uid` ) ;
		$query	= 'ALTER TABLE '.$db->quoteName('#__community_likes')
				. ' ADD INDEX ('.$db->quoteName('element').', '.$db->quoteName('uid').')';
				$result->html	= $html;
				$result->status = $status;
		$db->setQuery( $query );
		$db->query();

		/* Update wall post acitivities */
		$query	= 'UPDATE '.$db->quoteName('#__community_activities')
				. ' SET '.$db->quoteName('app').'='.$db->Quote('groups.wall')
				. ' WHERE '.$db->quoteName('params') .' LIKE '.$db->Quote('%action=group.wall.create%');
		$db->setQuery( $query );
		$db->query();

		/*Add parent for videos category to support sub-category*/
	    if( !$this->dbhelper->_isExistTableColumn('#__community_videos_category', 'parent') )
        {
			$query	= 'ALTER TABLE '.$db->quoteName('#__community_videos_category')
					. ' ADD '.$db->quoteName('parent')
					. 'INT NOT NULL AFTER '.$db->quoteName('id') ;
    		$db->setQuery( $query );
    		$db->query();
    	}

		/* Add storage column for group */
	    if( !$this->dbhelper->_isExistTableColumn('#__community_groups', 'storage') )
	    {
	    	$query	= 'ALTER TABLE '.$db->quoteName( '#__community_groups').' '
					. 'ADD '.$db->quoteName( 'storage').' VARCHAR( 64 ) NOT NULL DEFAULT '.$db->Quote( 'file' );
			$db->setQuery( $query );
			$db->Query();
		}
        if( !$this->dbhelper->_isExistTableColumn('#__community_profiles', 'ordering') )
        {
			//ALTER TABLE `jos_community_profiles` ADD `ordering` INT( 11 ) NOT NULL;
        	$query	= 'ALTER TABLE '.$db->quoteName('#__community_profiles')
					. ' ADD '.$db->quoteName('ordering')
					. ' INT( 11 ) NOT NULL';
    		$db->setQuery( $query );
    		$db->query();
    	}

    	/* Fix current activities for photos */
		$query	= 'UPDATE '.$db->quoteName( '#__community_activities').' as a'
					.' SET'.$db->quoteName( 'comment_type').'='.$db->Quote( 'photos.album')
					.','.$db->quoteName('comment_id').'= a.'.$db->quoteName( 'cid')
					.','.$db->quoteName( 'like_type').'='.$db->Quote( 'photos.album')
					.','.$db->quoteName( 'like_id').'= a.'.$db->quoteName( 'cid')
					.' WHERE '.$db->quoteName( 'params').' LIKE '.$db->Quote( '%action=upload%')
					.' AND '.$db->quoteName( 'app').' = '.$db->Quote( 'photos');
		$db->setQuery( $query );
		$db->Query();

		/* Fix current activities for profile status */
		$query	= 'UPDATE '.$db->quoteName( '#__community_activities').' as a'
					.' SET'.$db->quoteName( 'comment_type').'='.$db->Quote( 'profile.status')
					.','.$db->quoteName('comment_id').'= a.'.$db->quoteName( 'cid')
					.','.$db->quoteName( 'like_type').'='.$db->Quote( 'profile.status')
					.','.$db->quoteName( 'like_id').'= a.'.$db->quoteName( 'cid')
					.' WHERE '.$db->quoteName( 'app').' = '.$db->Quote( 'profile');
		$db->setQuery( $query );
		$db->Query();

		/* Fix current activities for new event */
		$query	= 'UPDATE '.$db->quoteName( '#__community_activities').' as a'
					.' SET'.$db->quoteName( 'comment_type').'='.$db->Quote( 'events')
					.','.$db->quoteName('comment_id').'= a.'.$db->quoteName( 'cid')
					.','.$db->quoteName( 'like_type').'='.$db->Quote( 'events')
					.','.$db->quoteName( 'like_id').'= a.'.$db->quoteName( 'cid')
					.' WHERE '.$db->quoteName( 'params').' LIKE '.$db->Quote( '%action=events.create%')
					.' AND '.$db->quoteName( 'app').' = '.$db->Quote( 'events');
		$db->setQuery( $query );
		$db->Query();

		//UPDATE `jos_community_activities` AS a SET a.comment_id = a.cid,
		//a.comment_type = 'videos' WHERE `app` = 'videos';
		$query	= 'UPDATE '.$db->quoteName('#__community_activities'). ' AS a'
				. ' SET a.'.$db->quoteName('comment_id').' = a.'.$db->quoteName('cid')
				. ' , a.'.$db->quoteName('comment_type').' = '.$db->quote('videos')
				. ' , a.'.$db->quoteName('like_id').' = a.'.$db->quoteName('cid')
				. ' , a.'.$db->quoteName('like_type').' = '.$db->quote('videos')
				. ' WHERE '.$db->quoteName('app').' = '.$db->quote('videos');
    	$db->setQuery( $query );
    	$db->query();

		if( !$this->dbhelper->_isExistTableColumn('#__community_users', 'friends') )
        {
			//ALTER TABLE `jos_community_users`
			//ADD `friends` TEXT NOT NULL ,
			//ADD `groups` TEXT NOT NULL ;

        	$query	= 'ALTER TABLE '.$db->quoteName( '#__community_users')
					.' ADD '.$db->quoteName( 'friends').' TEXT NOT NULL ,'
					.' ADD '.$db->quoteName( 'groups').' TEXT NOT NULL ';

    		$db->setQuery( $query );
    		$db->query();
    	}
		/* ALTER TABLE `jos_community_users` ADD `status_access` INT NOT NULL DEFAULT '0' AFTER `status` ; */
	    if( !$this->dbhelper->_isExistTableColumn('#__community_users', 'status_access') )
	    {
	    	$query	= 'ALTER TABLE '.$db->quoteName( '#__community_users').' '
					. 'ADD '.$db->quoteName('status_access').' INT NOT NULL DEFAULT '.$db->Quote(0) .' AFTER '.$db->quoteName( 'status') ;
			$db->setQuery( $query );
			$db->Query();
		}

		if(!$status)
		{
			$result->errorCode = $errorCode;
		}
		return $result;
	}
        function update_10()
        {
            $db		= JFactory::getDBO();
            $result = new stdClass();
            $status = true;
            $errorCode ='';
            $html ='';

            //Add Summary fiedl in event table
            if( !$this->dbhelper->_isExistTableColumn('#__community_events', 'summary') )
            {
    		$query	= 'ALTER TABLE '.$db->quoteName( '#__community_events' ).' ADD '.$db->quoteName( 'summary' ).' TEXT NOT NULL ';
    		$db->setQuery( $query );
    		$db->query();
            }

            //Add Email type in mail table
            if( !$this->dbhelper->_isExistTableColumn('#__community_mailq', 'email_type') )
            {
    		$query	= 'ALTER TABLE '.$db->quoteName( '#__community_mailq' ).' ADD '.$db->quoteName( 'email_type' ).' TEXT';
    		$db->setQuery( $query );
    		$db->query();
            }

            //Add verb,group_access,event_access in activities table
            if( !$this->dbhelper->_isExistTableColumn('#__community_activities', 'verb') )
            {
                 $query = 'ALTER TABLE '.$db->quoteName('#__community_activities')
                        .' ADD '.$db->quoteName('groupid')
			.' INT( 10 ) NULL AFTER '.$db->quoteName('cid'). ' , '
			.' ADD '.$db->quoteName('eventid')
			.' INT( 10 ) NULL AFTER '.$db->quoteName('groupid'). ' , '
                        .' ADD '.$db->quoteName('verb')
                        .' VARCHAR(200) NOT NULL AFTER '.$db->quoteName('app').' , '
                        .' ADD '.$db->quoteName('group_access')
                        .' TINYINT NOT NULL DEFAULT '.$db->quote(0).' AFTER '.$db->quoteName('eventid').' , '
                        .' ADD '.$db->quoteName('event_access')
                        .' TINYINT NOT NULL DEFAULT '.$db->quote(0).' AFTER '.$db->quoteName('group_access');
                $db->setQuery( $query );
		$db->query();
            }

            //add create_events at profile table
            if( !$this->dbhelper->_isExistTableColumn('#__community_profiles', 'create_events') )
            {

        	$query	= 'ALTER TABLE '.$db->quoteName('#__community_profiles')
					. ' ADD '.$db->quoteName('create_events')
					. ' INT NULL DEFAULT '.$db->quote(1)
                                       .' AFTER '.$db->quoteName('create_groups');
    		$db->setQuery( $query );
    		$db->query();
            }

            //add events field in users table
            if( !$this->dbhelper->_isExistTableColumn('#__community_users', 'events') )
            {

        	$query	= 'ALTER TABLE '.$db->quoteName('#__community_users')
					. ' ADD '.$db->quoteName('events')
					. ' TEXT NOT NULL AFTER '.$db->quoteName('groups');
    		$db->setQuery( $query );
    		$db->query();
            }

            $result->html	= $html;
            $result->status = $status;
            if(!$status)
            {
		$result->errorCode = '10f';
            }
            return $result;
        }

	function update_11()
	{
		$db		   = JFactory::getDBO();
		$result    = new stdClass();
		$status	   = true;
		$html	   = "";
		$errorCode = "";

		if( !$this->dbhelper->_isExistTableColumn('#__community_events', 'allday') )
		{
			$query	= 'ALTER TABLE '.$db->quoteName( '#__community_events' ).' ADD '.$db->quoteName( 'allday' ).' TINYINT( 11 ) NOT NULL DEFAULT '.$db->quote(0);
			$query .= ' , ADD '.$db->quoteName( 'repeat' ).' VARCHAR( 50 ) DEFAULT NULL COMMENT '.$db->Quote('null,daily,weekly,monthly');
			$query .= ' , ADD '.$db->quoteName( 'repeatend' ).' DATE NOT NULL';
			$query .= ' , ADD '.$db->quoteName( 'parent' ).' INT( 11 ) NOT NULL COMMENT '.$db->Quote('parent for recurring event').' AFTER '.$db->quoteName('id');
                        $query .= ' , ADD KEY `idx_catid` (`catid`)';
                        $query .= ' , ADD KEY `idx_published` (`published`)';

			$db->setQuery( $query );
			$db->query();
		}

		if( $this->dbhelper->_isExistMenu() )
		{
			$query	= 'UPDATE '.$db->quoteName('#__menu').' SET '.$db->quoteName('link').' = '.$db->quote('index.php?option=com_community&view=groups&task=mygroupupdate')
			. ' WHERE '.$db->quoteName('menutype').' = '.$db->quote('jomsocial')
			. ' AND  '. $db->quoteName('link').' = '.$db->quote('index.php?option=com_community&view=groups&task=mygroups')
			. ' AND  '. $db->quoteName('alias').' = '.$db->quote('groups');
			$db->setQuery( $query );
			$db->query();
		}

		//add profile_lock at profile table
		if( !$this->dbhelper->_isExistTableColumn('#__community_profiles', 'profile_lock') ){
			$query	= 'ALTER TABLE '.$db->quoteName('#__community_profiles')
			. ' ADD '.$db->quoteName('profile_lock')
			. ' TINYINT (1) NULL DEFAULT '.$db->quote(0)
			. ' AFTER '.$db->quoteName('create_events');
			$db->setQuery( $query );
			$db->query();
		}

		// Clean up profile upload stream title
		// Run this query in mysql that will delete all the {actor}
		$query	= 'UPDATE '.$db->quoteName('#__community_activities') .
				' SET '. $db->quoteName('title').' = REPLACE(title,"{actor} ","") '.
				' WHERE '. $db->quoteName('app').'= '.$db->quote('profile');
		$db->setQuery( $query );
		$db->query();

		if( !$this->dbhelper->_isExistTableColumn('#__community_groups_discuss', 'params') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_groups_discuss') .' ADD '. $db->quoteName('params') .' TEXT NOT NULL ';

			$db->setQuery( $query );
			$db->query();
		}

		if( !$this->dbhelper->_isExistTableColumn('#__community_groups_bulletins', 'params') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_groups_bulletins') .' ADD '. $db->quoteName('params') .' TEXT NOT NULL ';

			$db->setQuery( $query );
			$db->query();
		}

		if( $this->dbhelper->_isExistTableColumn('#__community_register', 'ip') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_register') .' CHANGE '. $db->quoteName('ip').' '. $db->quoteName('ip').' VARCHAR( 39 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; ';

			$db->setQuery( $query );
			$db->query();
		}

		if( $this->dbhelper->_isExistTableColumn('#__community_register_auth_token', 'ip') )
		{
			$query	= 'ALTER TABLE '. $db->quoteName('#__community_register_auth_token') .' CHANGE '. $db->quoteName('ip').' '. $db->quoteName('ip').' VARCHAR( 39 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; ';

			$db->setQuery( $query );
			$db->query();
		}

		$result->html	= $html;
		$result->status = $status;
		if(!$status)
		{
			$result->errorCode = '11f';
		}
		return $result;
	}
}

class CommunityInstallerDisplay
{
	function testImageMessage($type, $status=false)
	{
		$msg  = '';

		if( $status )
		{
			switch($type)
			{
				case 'GD':
				case 'GD2':
					$msg .= '<tr><td valign="top" class="item" width="200">'.$type.' library</td><td valign="top"><span class="Yes">Yes</span></td><td>You will be able to use '.$type.' library to manipulate images.</td></tr>';
					break;
				default:
					$msg .= '<tr><td valign="top" class="item" width="200">'.$type.' library</td><td valign="top"><span class="Yes">Yes</span></td><td>You will be able to upload '.$type.' images.</td></tr>';
					break;
			}
		}
		else
		{
			switch($type)
			{
				case 'GD':
				case 'GD2':
					$msg .= '<tr><td valign="top" class="item" width="200">'.$type.' library</td><td valign="top"><span class="No">No</span></td><td>You will <b>NOT</b> be able to use '.$type.' library to manipulate images.</td></tr>';
					break;
				default:
					$msg .= '<tr><td valign="top" class="item" width="200">'.$type.' library</td><td valign="top"><span class="No">No</span></td><td>You will <b>NOT</b> be able to upload '.$type.' images.</td></tr>';
					break;
			}
		}

		return $msg;
	}

	// Some installer code
	function cInstallDraw($output, $step, $title, $status, $install= 1, $substep=0)
	{
		$html 		= '';
		$version	= CommunityInstallerHelper::getVersion();

		$html .= '
	<script type="text/javascript">
	/* jQuery("span.version").html("Version '.$version.'"); */
	var DOM = document.getElementById("element-box");
	DOM.setAttribute("id","element-box1");
	</script>

	<style type="text/css">
	/**
	 * Reset Joomla! styles
	 */
	div.t, div.b {
		height: 0;
		margin: 0;
		background: none;
	}

	body #content-box div.padding {
		padding: 0;
	}

	body div.m {
		padding: 0;
		border: 0;
	}

	.button1-left {
		background: transparent url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_button1_left.png) no-repeat scroll 0 0;
		float: left;
		margin-left: 5px;
		cursor: pointer;
	}

	.button1-left .next {
		background: transparent url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_button1_next.png) no-repeat scroll 100% 0;
		float: left;
		cursor: pointer;
	}

	.button-next,
	.button-next:focus {
		border: 0;
		background: none;
		font-size: 11px;
		height: 26px;
		line-height: 24px;
		cursor: pointer;
		font-weight: 700;
	}

	h1.steps{
		color:#0B55C4;
		font-size:20px;
		font-weight:bold;
		margin:0;
		padding-bottom:8px;
	}

	div.steps {
		font-size: 12px;
		font-weight: bold;
		padding-bottom: 12px;
		padding-top: 10px;
		background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_divider.png) 0 100% repeat-x;
	}

	div.on {
		color:#0B55C4;
	}

	#toolbar-box,
	#submenu-box,
	#header-box {
		display: none;
	}

	div#cElement-box div.m {
		padding: 5px 10px;
	}

	div#cElement-box div.t, div#cElement-box div.b {
		height: 6px;
		padding: 0;
		margin: 0;
		overflow: hidden;
	}

	div#cElement-box div.m {
		border-left: 1px solid #ccc;
		border-right: 1px solid #ccc;
		padding: 0 8px;
	}

	div#cElement-box div.t {
		background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_border.png) 0 0 repeat-x;
	}

	div#cElement-box div.t div.t {
		background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_crn_tr_light.png) 100% 0 no-repeat;
	}

	div#cElement-box div.t div.t div.t {
		background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_crn_tl_light.png) 0 0 no-repeat;
	}

	div#cElement-box div.b {
		background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_border.png) 0 100% repeat-x;
	}

	div#cElement-box div.b div.b {
		background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_crn_br_light.png) 100% 0 no-repeat;
	}

	div#cElement-box div.b div.b div.b {
		background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_crn_bl_light.png) 0 0 no-repeat;
	}
	#stepbar {
		float: left;
		width: 170px;
	}

	#stepbar div.box {
		background: url('.JURI::root().'administrator/components/com_community/box.jpg) 0 0 no-repeat;
		height: 140px;
	}

	#stepbar h1 {
		margin: 0;
		padding-bottom: 8px;
		font-size: 20px;
		color: #0B55C4;
		font-weight: bold;
		background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_divider.png) 0 100% repeat-x;
	}

	div#stepbar {
	  background: #f7f7f7;
	}

	div#stepbar div.t {
	  background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_border.png) 0 0 repeat-x;
	}

	div#stepbar div.t div.t {
	   background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_crn_tr_dark.png) 100% 0 no-repeat;
	}

	div#stepbar div.t div.t div.t {
	   background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_crn_tl_dark.png) 0 0 no-repeat;
	}

	div#stepbar div.b {
	  background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_border.png) 0 100% repeat-x;
	}

	div#stepbar div.b div.b {
	   background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_crn_br_dark.png) 100% 0 no-repeat;
	}

	div#stepbar div.b div.b div.b {
	   background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_crn_bl_dark.png) 0 0 no-repeat;
	}

	div#stepbar div.t, div#stepbar div.b {
		height: 6px;
		margin: 0;
		overflow: hidden;
		padding: 0;
	}

	div#stepbar div.m,
	div#cToolbar-box div.m {
		padding: 0 8px;
		border-left: 1px solid #ccc;
		border-right: 1px solid #ccc;
	}

	div#cToolbar-box {
		background: #f7f7f7;
		position: relative;
	}

	div#cToolbar-box div.m {
		padding: 0;
		height: 30px;
	}

	div#cToolbar-box {
		background: #fbfbfb;
	}

	div#cToolbar-box div.t,
	div#cToolbar-box div.b {
		height: 6px;
	}

	div#cToolbar-box span.title {
		color: #0B55C4;
		font-size: 20px;
		font-weight: bold;
		line-height: 30px;
		padding-left: 6px;
	}

	div#cToolbar-box div.t {
	  background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_border.png) 0 0 repeat-x;
	}

	div#cToolbar-box div.t div.t {
	   background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_crn_tr_med.png) 100% 0 no-repeat;
	}

	div#cToolbar-box div.t div.t div.t {
	   background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_crn_tl_med.png) 0 0 no-repeat;
	}

	div#cToolbar-box div.b {
	  background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_border.png) 0 100% repeat-x;
	}

	div#cToolbar-box div.b div.b {
	   background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_crn_br_med.png) 100% 0 no-repeat;
	}

	div#cToolbar-box div.b div.b div.b {
	   background: url('.JURI::root().'administrator/templates/'.DEFAULT_TEMPLATE_ADMIN.'/images/j_crn_bl_med.png) 0 0 no-repeat;
	}
	</style>


	<table cellpadding="6" width="100%">
		<tr>
			<td rowspan="2" valign="top" width="10%">'.$this->cInstallDrawSidebar($step).'</td>
			<td valign="top" height="30">'.$this->cInstallDrawTitle($title, $step, $status, $install, $substep).'</td>
		</tr>
		<tr>
			<td valign="top">
				<div id="cElement-box" class="cInstaller-border">
					<div style="height: 487px; padding: 0 10px;">
					'. $output.'
					</div>
				</div>
			</td>
		</tr>
	</table>';

		echo $html;
	}

	function cInstallDrawSidebar($activeSteps)
	{
		ob_start();
		?>

		<div id="stepbar" class="cInstaller-border">
			<h1 class="steps">Steps</h1>
			<div id="stepFirst" class="steps<?php if($activeSteps == 1) echo " on"; ?>">1 : Welcome</div>
			<div class="steps<?php if($activeSteps == 2) echo " on"; ?>">2 : Checking Requirement</div>
			<div class="steps<?php if($activeSteps == 3) echo " on"; ?>">3 : Installing Jomsocial Backend</div>
			<div class="steps<?php if($activeSteps == 4) echo " on"; ?>">4 : Installing Jomsocial Ajax</div>
			<div class="steps<?php if($activeSteps == 5) echo " on"; ?>">5 : Installing Jomsocial Frontend</div>
			<div class="steps<?php if($activeSteps == 6) echo " on"; ?>">6 : Installing Jomsocial Templates</div>
			<div class="steps<?php if($activeSteps == 7) echo " on"; ?>">7 : Preparing Jomsocial Database</div>
			<div class="steps<?php if($activeSteps == 8) echo " on"; ?>">8 : Updating Jomsocial Database</div>
			<div class="steps<?php if($activeSteps == 100) echo " on"; ?>">9 : Installing Jomsocial Plugins</div>
			<div id="stepLast" class="steps<?php if($activeSteps == 0) echo " on"; ?>">10 : Done!</div>
			<div class="box"></div>
		</div>

		<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function cInstallDrawTitle($title, $step, $status, $install = 1, $substep = 0)
	{
		ob_start();
		?>
			<div id="cToolbar-box" class="cInstaller-border">
					<span class="title">
						<?php echo $title; ?>
					</span>

					<div style="position: absolute; top: 8px; right: 10px;">
						<div id="communityContainer">
							<?php
							if($status)
							{
							?>
							<form action="?option=com_community" method="POST" name="installform" id="installform">
								<input type="hidden" name="install" value="<?php echo $install; ?>"/>
								<input type="hidden" name="step" value="<?php echo $step; ?>"/>
								<input type="hidden" name="substep" value="<?php echo $substep; ?>"/>
								<div class="button1-left">
									<div class="next" onclick="document.installform.submit();">
										<input type="submit" class="button-next" onclick="" value="Next"/> <span style="margin-right: 30px;" id="timer"></span>
									</div>
								</div>
							</form>
							<?php
							}
							?>
						</div>
					</div>
	  		</div>

		<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}