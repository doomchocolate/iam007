 <?php
/**
 *------------------------------------------------------------------------------
 * @package       Plazart Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2012-2013 TemPlaza.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       TemPlaza
 * @Link:         http://templaza.com
 *------------------------------------------------------------------------------
 */
/**
 *------------------------------------------------------------------------------
 * @package       T3 Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2013 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github
 *                & Google group to become co-author)
 * @Google group: https://groups.google.com/forum/#!forum/t3fw
 * @Link:         http://t3-framework.org
 *------------------------------------------------------------------------------
 */

// Define constant
class PlazartAdmin {

	protected $langs = array();
	
	/**
	 * function render
	 * render Plazart administrator configuration form
	 *
	 * @return render success or not
	 */
	public function render(){
		$body = JResponse::getBody();
		$layout = PLAZART_ADMIN_PATH . '/admin/tpls/default.php';
		if(file_exists($layout) && JFactory::getApplication()->input->getCmd('view') == 'style'){
			ob_start();
			$this->loadParams();
			$buffer = ob_get_clean();

			$body = preg_replace('@<form\s[^>]*name="adminForm"[^>]*>?.*</form>@siu', $buffer, $body);
		}

		$body = $this->replaceToolbar($body);
		$body = $this->replaceDoctype($body);

		JResponse::setBody($body);
	}

	public function addAssets(){

		// load template language
		JFactory::getLanguage()->load(PLAZART_PLUGIN, JPATH_ADMINISTRATOR);
		JFactory::getLanguage()->load ('tpl_'.PLAZART_TEMPLATE.'.sys', JPATH_ROOT, null, true);

        $tplXml = PLAZART_TEMPLATE_PATH . '/templateDetails.xml';
        if (file_exists($tplXml)) $xml = JFactory::getXML($tplXml);

		$langs = array(
			'lblCompile' => JText::_('PLAZART_LBL_RECOMPILE'),
			'lblThemer' => JText::_('PLAZART_LBL_VIEWTHEMER'),
			'enableThemeMagic' => JText::_('PLAZART_MSG_ENABLE_THEMEMAGIC'),
			'unknownError' => JText::_('PLAZART_MSG_UNKNOWN_ERROR'),

			'logoPresent' => JText::_('PLAZART_LAYOUT_LOGO_TEXT'),
			'emptyLayoutPosition' => JText::_('PLAZART_LAYOUT_EMPTY_POSITION'),
			'defaultLayoutPosition' => JText::_('PLAZART_LAYOUT_DEFAULT_POSITION'),
			
			'layoutConfig' => JText::_('PLAZART_LAYOUT_CONFIG_TITLE'),
			'layoutConfigDesc' => JText::_('PLAZART_LAYOUT_CONFIG_DESC'),
			'layoutUnknownWidth' => JText::_('PLAZART_LAYOUT_UNKN_WIDTH'),
			'layoutPosWidth' => JText::_('PLAZART_LAYOUT_POS_WIDTH'),
			'layoutPosName' => JText::_('PLAZART_LAYOUT_POS_NAME'),

			'layoutCanNotLoad' => JText::_('PLAZART_LAYOUT_LOAD_ERROR'),

			'askCloneLayout' => JText::_('PLAZART_LAYOUT_ASK_ADD_LAYOUT'),
			'correctLayoutName' => JText::_('PLAZART_LAYOUT_ASK_CORRECT_NAME'),
			'askDeleteLayout' => JText::_('PLAZART_LAYOUT_ASK_DEL_LAYOUT'),

			'lblDeleteIt' => JText::_('PLAZART_LAYOUT_LABEL_DELETEIT'),
			'lblCloneIt' => JText::_('PLAZART_LAYOUT_LABEL_CLONEIT'),

			'layoutEditPosition' => JText::_('PLAZART_LAYOUT_EDIT_POSITION'),
			'layoutShowPosition' => JText::_('PLAZART_LAYOUT_SHOW_POSITION'),
			'layoutHidePosition' => JText::_('PLAZART_LAYOUT_HIDE_POSITION'),
			'layoutChangeNumpos' => JText::_('PLAZART_LAYOUT_CHANGE_NUMPOS'),
			'layoutDragResize' => JText::_('PLAZART_LAYOUT_DRAG_RESIZE'),
			'layoutHiddenposDesc' => JText::_('PLAZART_LAYOUT_HIDDEN_POS_DESC'),
			
			'updateFailedGetList' => JText::_('PLAZART_OVERVIEW_FAILED_GETLIST'),
			'updateDownLatest' => JText::_('PLAZART_OVERVIEW_GO_DOWNLOAD'),
			'updateCheckUpdate' => JText::_('PLAZART_OVERVIEW_CHECK_UPDATE'),
			'updateChkComplete' => JText::_('PLAZART_OVERVIEW_CHK_UPDATE_OK'),
            'updateLatestVersion' => JText::_('PLAZART_OVERVIEW_TPL_VERSION_MSG'),
			'updateHasNew' => JText::sprintf('PLAZART_OVERVIEW_TPL_NEW', $xml->name),
            'updateHasNewMsg' => JText::sprintf('PLAZART_OVERVIEW_TPL_NEW_MSG', $xml->version, $xml->name),
			'updateCompare' => JText::_('PLAZART_OVERVIEW_TPL_COMPARE')
		);
		
		$japp = JFactory::getApplication();
		$jdoc = JFactory::getDocument();

		$params = new JRegistry;
		$db = JFactory::getDbo();

		//get params of templates
		$query = $db->getQuery(true);
		$query
			->select('params')
			->from('#__template_styles')
			->where('template='. $db->quote(PLAZART_TEMPLATE));
		
		$db->setQuery($query);
		$params->loadString($db->loadResult());

		//get extension id of framework and template
		$query = $db->getQuery(true);
		$query
			->select('extension_id')
			->from('#__extensions')
			->where('(element='. $db->quote(PLAZART_TEMPLATE) . ' AND type=' . $db->quote('template') . ')
					OR (element=' . $db->quote(PLAZART_ADMIN) . ' AND type=' . $db->quote('plugin'). ')');

		$db->setQuery($query);
		$results = $db->loadRowList();
		$eids = array();
		foreach ($results as $eid) {
			$eids[] = $eid[0];
		}

		//check for version compactible
		$jversion  = new JVersion;
		if(!$jversion->isCompatible('3.0')){
			$jdoc->addStyleSheet(PLAZART_ADMIN_URL . '/admin/bootstrap/css/bootstrap.css');
			
			$jdoc->addScript(PLAZART_ADMIN_URL . '/admin/js/jquery-1.8.0.min.js');
			$jdoc->addScript(PLAZART_ADMIN_URL . '/admin/bootstrap/js/bootstrap.js');
			$jdoc->addScript(PLAZART_ADMIN_URL . '/admin/js/jquery.noconflict.js');
		}

		$jdoc->addStyleSheet(PLAZART_ADMIN_URL . '/admin/plugins/chosen/chosen.css');
		$jdoc->addStyleSheet(PLAZART_ADMIN_URL . '/includes/depend/css/depend.css');
		$jdoc->addStyleSheet(PLAZART_ADMIN_URL . '/admin/css/admin.css');
		if(!$jversion->isCompatible('3.0')){
			$jdoc->addStyleSheet(PLAZART_ADMIN_URL . '/admin/css/admin-j25.css');
		} else {
			$jdoc->addStyleSheet(PLAZART_ADMIN_URL . '/admin/css/admin-j30.css');
		}

        $jdoc->addStyleSheet(PLAZART_ADMIN_URL . '/admin/css/admin-layout.css');
        $jdoc->addStyleSheet(PLAZART_ADMIN_URL . '/admin/css/spectrum.css');

		$jdoc->addScript(PLAZART_ADMIN_URL . '/admin/plugins/chosen/chosen.jquery.min.js');
		$jdoc->addScript(PLAZART_ADMIN_URL . '/includes/depend/js/depend.js');
		$jdoc->addScript(PLAZART_ADMIN_URL . '/admin/js/json2.js');
		$jdoc->addScript(PLAZART_ADMIN_URL . '/admin/js/jimgload.js');
		$jdoc->addScript(PLAZART_ADMIN_URL . '/admin/js/admin.js');
        $jdoc->addScript(PLAZART_ADMIN_URL . '/admin/js/jquery-ui.min.js ');
        $jdoc->addScript(PLAZART_ADMIN_URL . '/admin/js/layout.admin.js');
        $jdoc->addScript(PLAZART_ADMIN_URL . '/admin/js/spectrum.js');

        $token = JSession::getFormToken();
		JFactory::getDocument()->addScriptDeclaration ( '
			var PlazartAdmin = window.PlazartAdmin || {};
			PlazartAdmin.adminurl = \'' . JFactory::getURI()->toString() . '\';
			PlazartAdmin.plazartadminurl = \'' . PLAZART_ADMIN_URL . '\';
			PlazartAdmin.baseurl = \'' . JURI::base(true) . '\';
			PlazartAdmin.rooturl = \'' . JURI::root() . '\';
			PlazartAdmin.template = \'' . PLAZART_TEMPLATE . '\';
			PlazartAdmin.templateid = \'' . JFactory::getApplication()->input->get('id') . '\';
			PlazartAdmin.langs = ' . json_encode($langs) . ';
			PlazartAdmin.devmode = ' . $params->get('devmode', 0) . ';
			PlazartAdmin.eids = [' . implode($eids, ',') .'];
			PlazartAdmin.telement = \'' . PLAZART_TEMPLATE . '\';
			PlazartAdmin.felement = \'' . PLAZART_ADMIN . '\';
			PlazartAdmin.plazartupdateurl = \'' . JURI::base() . 'index.php?option=com_installer&view=update&task=update.ajax' . '\';
			PlazartAdmin.jupdateUrl = \'' . JURI::base() . 'index.php?option=com_installer&view=update' . '\';

			var tzclient = new Object();
			tzclient.name = \''.$xml->name.'\';
			tzclient.uri  = \''.base64_encode(JURI::root()).'\';
			tzclient.version  = \''.$xml->version.'\';
			tzclient.tzupdate   =   \''.$xml->tzupdate.'\';

			var pluginPath = \''.PLAZART_ADMIN_URL.'\';
                var fieldName = \'jform[params][generate]\';

            // function to load/save settings
                function loadSaveOperation() {
                    var current_url = window.location;
                    current_url = current_url+"";
                    if((current_url + "").indexOf("#", 0) === -1) {
                        current_url = current_url + "&tz_template_task=load&tz_template_file=" + jQuery("#config_manager_load_filename").val() + "&'.$token.'=1";
                    } else {
                        current_url = current_url.substr(0, (current_url + "").indexOf("#", 0));
                        current_url = current_url + "&tz_template_task=load&tz_template_file=" + jQuery("#config_manager_load_filename").val() + "&'.$token.'=1";
                    }
                    window.location = current_url;
                }
            // function to load/save settings
                function deleteOperation() {
                    var current_url = window.location;
                    current_url = current_url+"";
                    if((current_url + "").indexOf("#", 0) === -1) {
                        current_url = current_url + "&tz_template_task=delete&tz_template_file=" + jQuery("#config_manager_load_filename").val() + "&'.$token.'=1";
                    } else {
                        current_url = current_url.substr(0, (current_url + "").indexOf("#", 0));
                        current_url = current_url + "&tz_template_task=delete&tz_template_file=" + jQuery("#config_manager_load_filename").val() + "&'.$token.'=1";
                    }

                    window.location = current_url;
                }
            // compare version
                function compareVersion(v1,v2) {
                    $arri   =   v1.split(".");
					$arrj   =   v2.split(".");
					$less   =   0;

					for ($k = 0; $k< $arri.length && $k<$arrj.length; $k++) {
						if (parseInt($arri[$k]) < parseInt($arrj[$k])) {
							$less  =   1;
							return 1;
						}
					}

					if (!$less && $arri.length< $arrj.length) {
						$less   =   1;
					}
					return !$less ? 0 : 1;
                }
			'
		);
	}

	public function addJSLang($key = '', $value = '', $overwrite = true){
		if($key && $value && ($overwrite || !array_key_exists($key, $this->langs))){
			$this->langs[$key] = $value ? $value : JText::_($key);
		}
	}
	
	/**
	 * function loadParam
	 * load and re-render parameters
	 *
	 * @return render success or not
	 */
	function loadParams(){
		$frwXml = PLAZART_ADMIN_PATH . '/'. PLAZART_ADMIN . '.xml';
		$tplXml = PLAZART_TEMPLATE_PATH . '/templateDetails.xml';
		$jtpl = PLAZART_ADMIN_PATH . '/admin/tpls/default.php';
		
		if(file_exists($tplXml) && file_exists($jtpl)){
			
			//get the current joomla default instance
			$form = JForm::getInstance('com_templates.style', 'style', array('control' => 'jform', 'load_data' => true));
			
			//remove all fields from group 'params' and reload them again in right other base on template.xml
			$form->removeGroup('params');
            $form->loadFile(PLAZART_PATH . '/params/' . 'template.xml');
			$form->loadFile(PLAZART_TEMPLATE_PATH . DIRECTORY_SEPARATOR . 'templateDetails.xml', true, '//config');

			$xml = JFactory::getXML($tplXml);
			$fxml = JFactory::getXML($frwXml);

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('id, title')
				->from('#__template_styles')
				->where('template='. $db->quote(PLAZART_TEMPLATE));
			
			$db->setQuery($query);
			$styles = $db->loadObjectList();
			foreach ($styles as $key => &$style) {
				$style->title = ucwords(str_replace('_', ' ', $style->title));
			}
			
			$session = JFactory::getSession();
			$plazartlock = $session->get('Plazart.plazartlock', 'overview_params');
			$session->set('Plazart.plazartlock', null);
			$input = JFactory::getApplication()->input;

            // $profile

			include $jtpl;
			
			//search for global parameters
			$japp = JFactory::getApplication();
			$pglobals = array();
			foreach($form->getGroup('params') as $param){
				if($form->getFieldAttribute($param->fieldname, 'global', 0, 'params')){
					$pglobals[] = array('name' => $param->fieldname, 'value' => $form->getValue($param->fieldname, 'params')); 
				}
			}
			$japp->setUserState('oparams', $pglobals);

			return true;
		}
		
		return false;
	}

	function replaceToolbar($body){
		$plazarttoolbar = PLAZART_ADMIN_PATH . '/admin/tpls/toolbar.php';
		$input = JFactory::getApplication()->input;

		if(file_exists($plazarttoolbar) && class_exists('JToolBar')){
			//get the existing toolbar html
			jimport('joomla.language.help');
			$toolbar = JToolBar::getInstance('toolbar')->render('toolbar');
			$helpurl = JHelp::createURL($input->getCmd('view') == 'template' ? 'JHELP_EXTENSIONS_TEMPLATE_MANAGER_TEMPLATES_EDIT' : 'JHELP_EXTENSIONS_TEMPLATE_MANAGER_STYLES_EDIT');
			$helpurl = htmlspecialchars($helpurl, ENT_QUOTES);
		
			//render our toolbar
			ob_start();
			include $plazarttoolbar;
			$plazarttoolbar = ob_get_clean();

			//replace it
			$body = str_replace($toolbar, $plazarttoolbar, $body);
		}

		return $body;
	}

	function replaceDoctype($body){
		return preg_replace('@<!DOCTYPE\s(.*?)>@', '<!DOCTYPE html>', $body);
	}

    function configmanager() {
        $uri = JURI::getInstance();
        // variables from URL
        $tpl_id = $uri->getVar('id', 'none');
        $task = $uri->getVar('tz_template_task', 'none');
        $file = JFilterOutput::stringURLSafe($uri->getVar('tz_template_file', 'none'));

        // if the URL contains proper variables
        if($tpl_id !== 'none' && is_numeric($tpl_id) && $task !== 'none') {
            // Check for request forgeries.
            JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));
            jimport('joomla.filesystem.file');
            // necessary Joomla! classes
            $db = JFactory::getDBO();
            $base_path = PLAZART_TEMPLATE_PATH.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR;
            // message
            $msg = '';
            // helping variables
            $redirectUrl = $uri->root() . 'administrator/index.php?option=com_templates&view=style&layout=edit&id=' . $tpl_id.'#preset';
            if($task == 'load') {
                $file   .=  '.json';

                if(JFile::exists($base_path . $file)) {
                    //
                    $query = '
						UPDATE
							#__template_styles
						SET
							params = '.$db->quote(file_get_contents($base_path . $file)).'
						WHERE
						 	id = '.$tpl_id.'
						LIMIT 1
						';
                    // Executing SQL Query
                    $db->setQuery($query);
                    $result = $db->query();
                    // check the result
                    if($result) {
                        // make an redirect
                        $app = JFactory::getApplication();
                        $app->redirect($redirectUrl, JText::_('TPL_TZ_LANG_CONFIG_LOADED_AND_SAVED'), 'message');
                    } else {
                        // make an redirect
                        $app = JFactory::getApplication();
                        $app->redirect($redirectUrl, JText::_('TPL_TZ_LANG_CONFIG_SQL_ERROR'), 'error');
                    }
                } else {
                    // make an redirect
                    $app = JFactory::getApplication();
                    $app->redirect($redirectUrl, JText::_('TPL_TZ_LANG_CONFIG_SELECTED_FILE_DOESNT_EXIST'), 'error');
                }
            } else if($task == 'save') {
                if($file == '') {
                    $file = date('d_m_Y_h_s');
                }
                // variable used to detect if the specified file exists
                $i = 0;
                // check if the file to save doesn't exist
                if(JFile::exists($base_path . $file . '.json')) {
                    // find the proper name for the file by incrementing
                    $i = 1;
                    while(JFile::exists($base_path . $file . $i . '.json')) { $i++; }
                }
                // get the settings from the database
                $query = '
					SELECT
						params AS params
					FROM
						#__template_styles
					WHERE
					 	id = '.$tpl_id.'
					LIMIT 1
					';
                // Executing SQL Query
                $db->setQuery($query);
                $row = $db->loadObject();
                // write it
                if(JFile::write($base_path . $file . (($i != 0) ? $i : '') . '.json' , $row->params)) {
                    // make an redirect
                    $app = JFactory::getApplication();
                    $app->redirect($redirectUrl, JText::_('TPL_TZ_LANG_CONFIG_FILE_SAVED_AS'). ' '. $file . (($i == 0) ? '' : $i) .'.json', 'message');
                } else {
                    // make an redirect
                    $app = JFactory::getApplication();
                    $app->redirect($redirectUrl, JText::_('TPL_TZ_LANG_CONFIG_FILE_WASNT_SAVED_PLEASE_CHECK_PERM'), 'error');
                }
            } else if($task == 'delete') {
                // Check if file exists before deleting
                $file .= '.json';
                if(JFile::exists($base_path . $file)) {
                    if(JFile::delete($base_path . $file)) {
                        $app = JFactory::getApplication();
                        $app->redirect($redirectUrl, $file . ' ' . JText::_('TPL_TZ_LANG_CONFIG_FILE_DELETED_AS'), 'message');
                    } else {
                        $app = JFactory::getApplication();
                        $app->redirect($redirectUrl, $file . ' ' . JText::_('TPL_TZ_LANG_CONFIG_FILE_WASNT_DELETED_PLEASE_CHECK_PERM'), 'error');
                    }
                } else {
                    $app = JFactory::getApplication();
                    $app->redirect($redirectUrl, $file . ' ' . JText::_('TPL_TZ_LANG_CONFIG_FILE_WASNT_DELETED_PLEASE_CHECK_FILE'), 'error');
                }
            }
        }
    }
}

?>