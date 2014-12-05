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

jimport( 'joomla.application.component.view' );

/**
 * Configuration view for JomSocial
 */
class CommunityViewTemplates extends JViewLegacy
{
	/**
	 * The default method that will display the output of this view which is called by
	 * Joomla
	 *
	 * @param	string template	Template file name
	 **/
	public function display( $tpl = null )
	{

		if( $this->getLayout() == 'edit' )
		{
			$this->_displayEditLayout( $tpl );
			return;
		}


		// Set the titlebar text
		JToolBarHelper::title( JText::_('COM_COMMUNITY_TEMPLATES'), 'templates' );

		// Add the necessary buttons
		JToolBarHelper::makeDefault('publish');

		$this->assign( 'config'		, CFactory::getConfig() );
		$this->assign( 'templates' , $this->getTemplates() );
		parent::display( $tpl );
	}

	private function _displayEditLayout( $tpl )
	{
		$element	= JRequest::getString( 'id' );
		$override	= JRequest::getInt( 'override' );

		// @rule: Test if this folder really exists
		$path		= COMMUNITY_BASE_PATH . '/templates/'. $element;

		if( $override )
			$path	= JPATH_ROOT . '/templates/'. $element . '/html/com_community';

		if( !JFolder::exists( $path ) )
		{
			$mainframe	= JFactory::getApplication();
			$mainframe->redirect( 'index.php?option=com_community&view=templates' , JText::_('COM_COMMUNITY_TEMPLATES_INVALID_TEMPLATE') , 'error');
		}

		$params		= $this->getParams( $element , $override );
		$files		= $this->getFiles( $element , $override );

		// Set the titlebar text
		JToolBarHelper::title( JText::_('COM_COMMUNITY_TEMPLATES'), 'templates' );

		// Add the necessary buttons
 		JToolBarHelper::back('Back' , 'index.php?option=com_community&view=templates');
 		JToolBarHelper::divider();
		JToolBarHelper::save();
		JToolBarHelper::apply();

		$this->assign( 'files' , $files );
		$this->assign( 'template' , $this->getTemplate( $element , $override ) );
		$this->assign( 'params' , $params );
		$this->assign( 'override' , $override );
 		parent::display( $tpl );
	}

	public function getParams( $element , $override )
	{
		$templatesPath	= COMMUNITY_BASE_PATH . '/templates';

		if( $override )
		{
			$xml	= JPATH_ROOT . '/templates/'. $element . '/html/com_community/'. COMMUNITY_TEMPLATE_XML;
			$raw	= JPATH_ROOT . '/templates/'. $element . '/html/com_community/params.ini';

			if( !JFile::exists( $xml ) )
			{
				$xml	= JPATH_ROOT . '/templates/'. $element .'/'. COMMUNITY_TEMPLATE_XML;
				$raw	= JPATH_ROOT . '/templates/'. $element . '/params.ini';
			}
		}
		else
		{
			$xml	= $templatesPath .'/'. $element .'/'. COMMUNITY_TEMPLATE_XML;
			$raw	= $templatesPath .'/'. $element . '/params.ini';
		}

		$rawContent		= '';

		if( JFile::exists( $raw ) )
		{
			$rawContent	= JFile::read( $raw );
		}

		return new CParameter( $rawContent , $xml , 'template' );
	}

	public function getTemplates()
	{
		$templatesPath	= COMMUNITY_BASE_PATH . '/templates';
		$templates		= array();
		$folders		= JFolder::folders( $templatesPath );
		$overrideFolders	= JFolder::folders( JPATH_ROOT . '/templates' );

		// @rule: Retrieve template overrides folder
		foreach( $overrideFolders as $overrideFolder )
		{
			// Only add templates that really has overrides
			if( JFolder::exists( JPATH_ROOT . '/templates/'. $overrideFolder . '/html/com_community' ) )
			{
				$path			= JPATH_ROOT . '/templates/'. $overrideFolder . '/html/com_community/'. COMMUNITY_TEMPLATE_XML;
				$obj			= new stdClass();
				$obj->element	= $overrideFolder;
				$obj->override	= true;
				if( JFile::exists( $path ) )
				{
					$obj->info	= JInstaller::parseXMLInstallFile(  $path );
				}
				else
				{
					$obj->info	= false;
				}
				$templates[]	= $obj;
			}
		}

		// @rule: Retrieve jomsocial template folders
		foreach( $folders as $folder )
		{
			$obj			= new stdClass();
			$obj->element	= $folder;
			$obj->override	= false;
			if( JFile::exists( $templatesPath .'/'. $folder .'/'. COMMUNITY_TEMPLATE_XML ) )
			{
				$obj->info	= JInstaller::parseXMLInstallFile(  $templatesPath .'/'. $folder .'/'. COMMUNITY_TEMPLATE_XML );
			}
			else
			{
				$obj->info	= false;
			}
			$templates[]	= $obj;
		}

		return $templates;
	}

	public function getTemplate( $element , $override )
	{
		$templatesPath	= COMMUNITY_BASE_PATH . '/templates';
		if( $override )
		{
			$templatesPath	= JPATH_ROOT . '/templates/'. $element . '/html/com_community';
		}

		$obj			= new stdClass();
		$obj->element	= $element;
		$obj->override	= $override;
		if( JFile::exists( $templatesPath .'/'. $element .'/'. COMMUNITY_TEMPLATE_XML ) )
		{
			$obj->info	= JInstaller::parseXMLInstallFile(  $templatesPath .'/'. $element .'/'. COMMUNITY_TEMPLATE_XML );
		}
		else
		{
			$obj->info	= false;
		}
		return $obj;
	}

	public function getFiles( $element , $override )
	{
		$path	= COMMUNITY_BASE_PATH . '/templates/'. $element;

		if( $override )
		{
			$path	= JPATH_ROOT . '/templates/'. $element . '/html/com_community';
		}
		$files	= JFolder::files( $path );
		sort($files);

		return $files;
	}

	/**
	 * Public method to get the templates listings
	 *
	 * @access private
	 *
	 * @return null
	 **/
	public function getTemplatesListing()
	{
		$templatesPath	= COMMUNITY_BASE_PATH . '/templates';
		$templates		= array();

		if( $handle = @opendir($templatesPath) )
		{
			while( false !== ( $file = readdir( $handle ) ) )
			{
				// Do not get '.' or '..' or '.svn' since we only want folders.
				if( $file != '.' && $file != '..' && $file != '.svn' )
					$templates[]	= $file;
			}
		}


		$html	= '<select name="template" onchange="azcommunity.changeTemplate(this.value);">';

		$html	.= '<option value="none" selected="true">' . JText::_('COM_COMMUNITY_SELECT_TEMPLATE') . '</option>';
		for( $i = 0; $i < count( $templates ); $i++ )
		{
			$html	.= '<option value="' . $templates[$i] . '">' . $templates[$i] . '</option>';
		}
		$html	.= '</select>';

		return $html;
	}



}