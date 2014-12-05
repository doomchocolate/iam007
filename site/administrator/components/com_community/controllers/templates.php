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

jimport( 'joomla.application.component.controller' );

/**
 * JomSocial Component Controller
 */
class CommunityControllerTemplates extends CommunityController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function publish()
	{
	    JRequest::checkToken() or jexit( 'Invalid Token' );

		$mainframe  = JFactory::getApplication();
		$jinput 	= $mainframe->input;
	    $template   = $jinput->post->get('template',NULL,'NONE'); //JRequest::getVar( 'template' , 'POST' );
	    $model		= $this->getModel( 'Configuration' );
	    $model->updateTemplate( $template );


	    $mainframe->redirect( JRoute::_('index.php?option=com_community&view=templates' , false ) , JText::sprintf( 'COM_COMMUNITY_TEMPLATE_CONFIGURATION_UPDATED' , $template ), 'message' );
	}

	public function ajaxChangeTemplate( $templateName )
	{
		$response	= new JAXResponse();

		if( $templateName == 'none' )
		{
			// Previously user might already selected a template, hide the files
			$response->addScriptCall( 'azcommunity.resetTemplateFiles();' );

			// Close all files if it is already editing
			$response->addScriptCall( 'azcommunity.resetTemplateForm();' );
		}
		else
		{
			$html	= '<div id="template-files">';
			$html	.= '<h3>' . JText::_('COM_COMMUNITY_SELECT_FILE') . '</h3>';


			$templatePath	= COMMUNITY_BASE_PATH . '/templates/'. JString::strtolower( $templateName );

			$files			= array();

			if( $handle = @opendir($templatePath) )
			{
				while( false !== ( $file = readdir( $handle ) ) )
				{
					$filePath	= $templatePath .'/'. $file;

					// Do not get '.' or '..' or '.svn' since we only want folders.
					if( $file != '.' && $file != '..' && $file != '.svn' && !(JString::stristr( $file , '.js')) && !is_dir($filePath) )
					{
						$files[]	= $file;
					}
				}
			}
			sort($files);

			$html	.= '<select name="file" onchange="azcommunity.editTemplate(\'' . $templateName . '\',this.value);">';
			$html	.= '<option value="none" selected="true">' . JText::_('COM_COMMUNITY_SELECT_FILE') . '</option>';
			for( $i = 0; $i < count( $files ); $i++ )
			{
				$html .= '<option value="' . $files[$i] . '">' . $files[$i] . '</option>';
			}
			$html	.= '</select>';

			$html	.= '</div>';
			$response->addAssign( 'templates-files-container' , 'innerHTML' , $html );
		}

		return $response->sendResponse();
	}

	/**
	 * Ajax method to load a template file
	 *
	 * @param	$templateName	The template name
	 * @param	$fileName	The file name
	 **/
	public function ajaxLoadTemplateFile( $templateName , $fileName , $override )
	{
		$response	= new JAXResponse();

		if( $fileName == 'none')
		{
			$response->addScriptCall( 'azcommunity.resetTemplateForm();' );
		}
		else
		{
			$filePath	= COMMUNITY_BASE_PATH . '/templates/'. JString::strtolower( $templateName ) .'/'. JString::strtolower( $fileName );

			if( $override )
				$filePath	= JPATH_ROOT . '/templates/'. JString::strtolower( $templateName ) . '/html/com_community/'. JString::strtolower( $fileName );

			jimport('joomla.filesystem.file');

			$contents	= JFile::read( $filePath );

			$response->addAssign( 'data' , 'value' , $contents );
			$response->addAssign( 'fileName' , 'value' , $fileName );
			$response->addAssign( 'templateName' , 'value' , $templateName );
			$response->addAssign( 'filePath' , 'innerHTML' , $filePath );
		}

		return $response->sendResponse();
	}

	public function ajaxSaveTemplateFile( $templateName , $fileName , $fileData , $override )
	{
		$response	= new JAXResponse();

		$filePath	= COMMUNITY_BASE_PATH . '/templates/'. JString::strtolower( $templateName ) .'/'. JString::strtolower( $fileName );

		if( $override )
			$filePath	= JPATH_ROOT . '/templates/'. JString::strtolower( $templateName ) . '/html/com_community/'. JString::strtolower( $fileName );

		jimport( 'joomla.filesystem.file' );

		if( JFile::write( $filePath , $fileData ) )
		{
			$response->addScriptCall('joms.jQuery("#status").remove();');
			$response->addScriptCall('joms.jQuery("<div id=\'status\'></div>")
				.html("' . JText::sprintf('%1$s saved successfully.' , $fileName ) . '")
				.attr("class","alert alert-success")
				.css({"float": "left", "width": "97%"})
				.insertAfter("textarea");');
		}
		else
		{
			$response->addScriptCall( 'alert' , JText::_('COM_COMMUNITY_TEMPLATES_FILE_SAVE_ERROR') );
		}

		return $response->sendResponse();
	}

	public function save()
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$params		= $jinput->post->get('params', array(), 'array') ; //JRequest::getVar('params', array(), 'post', 'array');
		$element	= JRequest::getString( 'id' );
		$override	= $jinput->get('override',NULL,'NONE') ; //JRequest::getVar( 'override' );
        $task       = JRequest::getCmd( 'task' );

		if( $override )
		{
			$xml	= JPATH_ROOT . '/templates/'. $element . '/html/com_community/'. COMMUNITY_TEMPLATE_XML;
			$file	= JPATH_ROOT . '/templates/'. $element . '/html/com_community/params.ini';

			if( !JFile::exists( $xml ) )
			{
				$file	= JPATH_ROOT . '/templates/'. $element . '/params.ini';
			}
		}
		else
		{
			$file		= JPATH_ROOT . '/components/com_community/templates/'. $element . '/params.ini';
		}

		jimport('joomla.filesystem.file');

		$registry	= new JRegistry();
		$registry->loadArray($params);
		$raw		= $registry->toString();

		if( !empty( $raw ) )
		{
			if( !JFile::write( $file , $raw ) )
			{
				$mainframe->redirect( 'index.php?option=com_community&view=templates&layout=edit&id=' . $element , JText::_('COM_COMMUNITY_TEMPLATES_PARAMETERS_SAVE_ERROR') , 'error' );
			}
		}

		switch($task){
            case 'apply';
                $link   = 'index.php?option=com_community&view=templates&layout=edit&override='.$override.'&id='.$element;
                break;
            case 'save';
            default:
                $link   = 'index.php?option=com_community&view=templates';
                break;
        }

		$mainframe->redirect( $link , JText::_('COM_COMMUNITY_TEMPLATES_PARAMETERS_SAVED'), 'message' );
	}

	public function apply()
	{
        $this->save();
    }

	public function edit()
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$id			= $jinput->get('cid',NULL,'NONE'); //JRequest::getVar( 'cid' );

		$mainframe->redirect( 'index.php?option=com_community&view=templates&layout=edit&id=' . $id[0] );
	}
}