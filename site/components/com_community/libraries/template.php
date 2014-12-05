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

jimport( 'joomla.application.component.view');


require_once(COMMUNITY_COM_PATH.'/libraries/browser.php');

$browser = CBrowser::getInstanceStatic();
$screen  = 'desktop';

$mainframe	= JFactory::getApplication();
$jinput 	= $mainframe->input;

if( $browser->isMobile() )
{
	// Determine whether to display
	// desktop or mobile screen.
	$mySess = JFactory::getSession();

	// If a screen was given from the URL,
	// it means we are switching screens,
	// so we'll save preferred screen to session data.
	$screen = $jinput->get->get('screen', NULL, 'STRING'); //JRequest::getVar('screen', null, 'GET');
	if(!empty($screen))
	{
		$mySess->set('screen', $screen);
	}

	// Get preferred screen from session data
	$screen = $mySess->get('screen');

	// If preferred screen was not found in session data,
	// get it from user preferences.
	if (empty($screen))
	{
		$my     = CFactory::getUser();
		if( $my->id==0 )
		{
			// Use 'mobile' as default screen for guests
			$screen = 'mobile';
		} else {
			$params = $my->getParams();
			$screen = ($params->get('mobileView')) ? 'mobile' : 'desktop';
		}
	}
}
define('COMMUNITY_TEMPLATE_SCREEN', $screen);

/**
 * Templating system for JomSocial
 */
class CTemplate
{
    var $vars; // Holds all the template variables

	// The name is intentionally long to avoid collision
	var $_internalView;
    /**
     * Constructor
     *
     * @param $file string the file name you want to load
     */
    public function __construct($file=null)
    {
		// $file can also be a view object. If it is an object, assign to internal object
		if( is_object($file) )
		{
			$this->_internalView = $file;
		}
		else
		{

			$this->file = $file;
		}

	    @ini_set('short_open_tag', 'On');

	    // Extract template parameters for template providers.
	    if( !isset($this->params) && empty($this->params) )
	    {
		    $this->params = $this->getTemplateParams();
	    }
    }

	public function view( $type = null )
	{
		if($type == null)
		{
			return $this->_internalView;
		}
		else
		{
			$view = CFactory::getView($type);
			return $view;
		}
	}

    /**
     * Set a template variable.
     */
    public function set($name, $value)
    {
	    $this->vars[$name] = $value;

	    // Return this object
	    return $this;
    }

    /**
     * Set a template variable by reference
     */
    public function setRef($name, &$value)
    {
	    $this->vars[$name] =  $value;

	    // Return this object
	    return $this;
    }

    /**
     * Return template variables
     */
    public function __get($varname)
	{
		if(isset($this->vars[$varname]))
		{
			return $this->vars[$varname];
		}

		return FALSE;
	}

    /**
     * Open, parse, and return the template file.
     *
     * @param $file string the template file name
     */
    public function fetch($file=null)
    {
	    $template 	= new CTemplateHelper();
	    $tmpFile	= $file;

	    if(empty($file))
	    {
		    $file = $this->file;
	    }

	    if($this->mobileTemplate())
	    {
		    $file = $template->getMobileTemplateFile($file);
	    }
	    else
	    {
		    $file = $template->getTemplateFile($file);
	    }

	    // Template variable: $my;
	    $my = CFactory::getUser();
	    $this->setRef( 'my' , $my );

	    // set Up admin var
	    $this->set( 'isCommunityAdmin', COwnerHelper::isCommunityAdmin());

	    // Template variable: $config;
	    if( !isset( $this->vars['config'] ) && empty($this->vars['config']) )
	    {
		    $this->vars['config'] = CFactory::getConfig();
	    }

	    // Template variable: the rest.
	    if($this->vars)
	    {
	    extract($this->vars, EXTR_REFS);
	    }

	    if( !JFile::exists( $file ) )
	    {
		    $mainframe	= JFactory::getApplication();
		    $mainframe->enqueueMessage( JText::sprintf('COM_COMMUNITY_TEMPLATE_FILE_NOT_FOUND' , $tmpFile . '.php' ) , 'error' );
		    return;
	    }

	    ob_start();                    // Start output buffering
	    require($file);                // Include the file
	    $contents = ob_get_contents(); // Get the contents of the buffer
	    ob_end_clean();                // End buffering and discard

	    // Replace all _QQQ_ to "
	    // Language file now uses new _QQQ_ to maintain Joomla 1.6 compatibility
	    $contents = CTemplate::quote($contents);

	    return $contents;              // Return the contents
    }

    /***
     * Allow a template to include other template and inherit all the variable
     */
    public function load($file)
    {
	    $template = new CTemplateHelper;

	    if($this->vars)
	    {
		    extract($this->vars, EXTR_REFS);
	    }

	    $file = $template->getTemplateFile($file);

	    include($file);

	    return $this;
    }

	/**
	 * Alias to $document->countModules function
	 */
	public function countModules($condition)
	{
		$document	= JFactory::getDocument();
		return $document->countModules($condition);
	}

    public static function getTemplateParams()
    {
	    $template = new CTemplateHelper();

	    $defaultParam  = $template->getTemplatePath('params.ini', 'default');
	    $templateParam = $template->getTemplatePath('params.ini');
	    $overrideParam = $template->getOverrideTemplatePath('params.ini');

	    $params	 = new CParameter('');

	    if(JFile::exists($defaultParam))
	    {
			$params->bind( JFile::read($defaultParam) );
	    }

	    if(JFile::exists($templateParam))
	    {
			$params->bind( JFile::read($templateParam) );
	    }

	    if(JFile::exists($overrideParam))
	    {
			$params->bind( JFile::read($overrideParam) );
	    }

	    return $params;
	}

    public static function getTemplateEnvironment()
    {
	    jimport('joomla.environment.browser');

	    $app     = JFactory::getApplication();
	    $browser =  JBrowser::getInstance();

	    $environment = new stdClass();
	    $environment->joomlaTemplate = $app->getTemplate();
	    $environment->browserName    = $browser->getBrowser();

	    return $environment;
    }

    public static function addStylesheet($file)
    {
    	if(!strpos($file,'green'))
    	{
		    $template = new CTemplateHelper();
		    $asset = $template->getTemplateAsset($file, 'css');

		    CAssets::attach($asset->filename, 'css', $asset->path);
		}
    }

    static public function addScript($file)
    {
	    $template = new CTemplateHelper();
	    $asset = $template->getTemplateAsset($file, 'js');

	    CAssets::attach($asset->filename, 'js', $asset->path);

	    // Return this object
	    return;
    }

    public static function renderModules($position, $attribs = array())
    {
	    jimport( 'joomla.application.module.helper' );

	    $modules 	= JModuleHelper::getModules( $position );
	    $modulehtml = '';

	    foreach($modules as $module)
	    {
		    // If style attributes are not given or set, we enforce it to use the xhtml style
		    // so the title will display correctly.
		    if( !isset($attribs['style'] ) )
			    $attribs['style']	= 'xhtml';

		    $modulehtml .= JModuleHelper::renderModule($module, $attribs);
	    }

	    // Add placholder code for onModuleRender search/replace
	    $modulehtml .= '<!-- '.$position. ' -->';
	    echo $modulehtml;
    }

    static public function escape( $text )
    {

	    return CStringHelper::escape( $text );
    }

    public function mobileTemplate()
    {
	    return COMMUNITY_TEMPLATE_SCREEN=='mobile';
    }

    public static function getPoweredByLink()
    {
		$powerBy = '';

		if (!COMMUNITY_PRO_VERSION) {
			 //POWERED BY LINK//
			$app = JFactory::getApplication();
			//$jConfig  = JFactory::getConfig();
			//$siteName = $jConfig->getValue( 'sitename' );
			$siteName = $app->getCfg( 'sitename' );
			$powerBy  = 'Powered by <a href="http://www.jomsocial.com/">JomSocial</a> for <a href="' . JURI::root() . '">' . $siteName . '</a>';
		}

	    return $powerBy;
    }

    /**
     * Replace all _QQQ_ to "
     * Language file now uses new _QQQ_ to maintain Joomla 1.6 compatibility
     */
    public static function quote($str)
    {
	    $str = str_replace('_QQQ_','"', $str);
	    return $str;
    }

    public function setMetaTags( $app, $data )
    {
	    $document	= JFactory::getDocument();
	    $config		= CFactory::getConfig();

	    $description	=   '';
	    $groupName	=   '';

	    if( isset( $data->description ) )
	    {
		    $description	= strip_tags( $data->description );
	    }

	    switch ($app)
	    {
		    case 'event' :
			    $description	=   JHTML::_('string.truncate', CStringHelper::escape( $description ), $config->getInt('streamcontentlength') );
			    $document->addHeadLink( $data->getThumbAvatar(), 'image_src', 'rel' );
                            /* set head meta */                            
                            CHeadHelper::setTitle(JText::sprintf('COM_COMMUNITY_EVENT_PAGE_TITLE', $data->title));
			    break;
		    case 'video' :
			    $description	=   JHTML::_('string.truncate', CStringHelper::escape( $description ), $config->getInt('streamcontentlength') );
			    $document->setMetaData('medium', 'video');
			    $document->addHeadLink( $data->getThumbnail(), 'image_src', 'rel' ); //cannot exceed 130x110 pixels (facebook)
                            /* set head meta */                            
                            CHeadHelper::setTitle($data->title);
			    break;
		    case 'group' :
			    $groupName	=   $data->approvals == COMMUNITY_PRIVATE_GROUP ? $data->name . ' (' . JText::_( 'COM_COMMUNITY_GROUPS_PRIVATE') . ')' : $data->name;
			    $data->title	=   $groupName;
			    $description	=   JText::sprintf( 'COM_COMMUNITY_GROUP_META_DESCRIPTION', CStringHelper::escape($data->name), $config->get('sitename') , CStringHelper::escape( $description ) );
			    $document->addHeadLink( $data->getThumbAvatar(), 'image_src', 'rel' );
                            /* set head meta */                            
                            CHeadHelper::setTitle(JText::sprintf('COM_COMMUNITY_GROUP_PAGE_TITLE', $data->title));
			    break;
		    default :
			    $description	=   JHTML::_('string.truncate', CStringHelper::escape($description), $config->getInt('streamcontentlength') );
                            CHeadHelper::setTitle($data->title); // JDocument will perform htmlspecialchars escape
	    }
	    
	    $document->setMetaData('title', CStringHelper::escape( $data->title )); // hack the above line	    
            CHeadHelper::setDescription($description);
	    // Return this object
	    return $this;
    }

    public function object_to_array($obj)
    {
	   $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
	   $arr = array();
	   foreach ($_arr as $key => $val) {
		   $val = (is_array($val) || is_object($val)) ? $this->object_to_array($val) : $val;
		   $arr[$key] = $val;
	   }
	   return $arr;
    }

    /*
     * Deprecated since 2.0, use:
     * getFile($file) or getFolder() from CTemplateHelper
     */
    private function _getTemplateFolder( $file )
    {
	    $folder = dirname($this->_getTemplateFullpath($file));
	    return $folder;
    }

    /*
     * Deprecated since 2.0, use:
     * getFile($file) from CTemplateHelper
     */
    private function _getTemplateFullpath($file)
    {
	    $template = new CTemplateHelper();
	    $file = $template->getFile($file);
	    return $file;
    }

    /*
     * Deprecated since 2.0, use:
     * getTemplateParams();
     */
    private function _getTemplateParams( $currentFolder )
    {
	    $params = $this->getTemplateParams();

	    $currentParam = $currentFolder .'/params.ini';
	    if (JFile::exists($currentParam))
	    {
		    $params->bind( JFile::read($currentParam) );
	    }

	    return $params;
    }


}