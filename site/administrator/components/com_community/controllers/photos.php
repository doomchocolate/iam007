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

require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );

/**
 * JomSocial Component Controller
 */
class CommunityControllerPhotos extends CommunityController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('publish', 'savePublish');
		$this->registerTask('unpublish', 'savePublish');
	}

	public function display( $cachable = false, $urlparams = array() )
	{
		$viewName	= JRequest::getCmd( 'view' , 'community' );

		// Set the default layout and view name
		$layout		= JRequest::getCmd( 'layout' , 'default' );

		// Get the document object
		$document	= JFactory::getDocument();

		// Get the view type
		$viewType	= $document->getType();

		// Get the view
		$view		= $this->getView( $viewName , $viewType );

		$model		= $this->getModel( $viewName ,'CommunityAdminModel' );

		if( $model )
		{
			$view->setModel( $model , $viewName );

			$Users	= $this->getModel( 'Users' );
			$view->setModel( $Users  , false );
		}

		// Set the layout
		$view->setLayout( $layout );

		// Display the view
		$view->display();
	}

	public function ajaxViewPhoto($id)
	{
		$response = new JAXResponse();

		$photo = JTable::getInstance('Photos','CommunityTable');
		$photo->load($id);

		$notiHtml = '<img src="'.JUri::root().$photo->image.'" />';

		$response->addScriptCall('cWindowAddContent', $notiHtml);
		$response->addScriptCall('joms.jQuery("#cWindowContent").addClass("text-center");');

		return $response->sendResponse();
	}

	public function ajaxEditPhoto($id)
	{
		$response = new JAXResponse();

		$photo = JTable::getInstance('Photos','CommunityTable');
		$photo->load($id);
		//var_dump($photo);exit;

		ob_start();
	?>
	<form name="editphoto" action="" method="post" id="editphoto">
		<table cellspacing="0" class="admintable" border="0" width="100%">
			<tbody>
			<tr>
				<td class="key"><?php echo JText::_('COM_COMMUNITY_TITLE');?></td>
				<td><input type="text" id="caption" name="caption" class="input text" value="<?php echo $photo->caption;?>" style="width: 90%;"  maxlength="255"  /></tD>
			</tr>
			</tbody>
		</table>
		<input type="hidden" name="id" value="<?php echo $photo->id;?>"/>
		<input type="hidden" name="option" value="com_community"/>
		<input type="hidden" name="task" value="savephotos"/>
		<input type="hidden" name="view" value="photos"/>
	</form>
	<?php
		$contents = ob_get_contents();
		ob_end_clean();

		$response->addAssign('cWindowContent', 'innerHTML', $contents);

		$action = '<input type="button" class="btn btn-small btn-primary pull-right" onclick="azcommunity.savePhoto();" name="' . JText::_('COM_COMMUNITY_SAVE') . '" value="' . JText::_('COM_COMMUNITY_SAVE') . '" />';
		$action .= '&nbsp;<input type="button" class="btn btn-small pull-left" onclick="cWindowHide();" name="' . JText::_('COM_COMMUNITY_CLOSE') . '" value="' . JText::_('COM_COMMUNITY_CLOSE') . '" />';
		$response->addScriptCall('cWindowActions', $action);

		return $response->sendResponse();
	}

	public function savephotos()
	{
		$photo	= JTable::getInstance( 'Photos' , 'CommunityTable' );
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$id			= $jinput->post->get('id' , '', 'INT');

		if( empty($id) )
		{
			JError::raiseError( '500' , JText::_('COM_COMMUNITY_INVALID_ID') );
		}

		$postData	= JRequest::get( 'post' );
		$photo->load( $id );

		$photo->bind( $postData );

		$message	= '';
		if( $photo->store() )
		{
			$message	= JText::_('Photo is saved');
		}
		else
		{
			$message	= JText::_('Failed to save photo');
		}

		$mainframe->redirect( 'index.php?option=com_community&view=photos' , $message , 'message');
	}

	public function ajaxTogglePublish($id , $type, $viewName = false )
	{
		$video	= JTable::getInstance( 'Photo' , 'CTable' );
		$video->load( $id );

		return parent::ajaxTogglePublish( $id , $type , 'photos' );
	}

	public function delete()
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$model		= $this->getModel( 'Photos' ,'CommunityAdminModel');
		$id			= $jinput->post->get( 'cid' , '', 'array' );
		$errors		= false;
		$message	= JText::_('Photos has ben deleted');

		if( empty($id) )
		{
			JError::raiseError( '500' , JText::_('COM_COMMUNITY_INVALID_ID') );
		}

		for( $i = 0; $i < count($id); $i++ )
		{
			if( !$model->delete( $id[ $i ] ) )
			{
				$errors	= true;
			}
		}

		if( $errors )
		{
			$message	= JText::_('Error deleting photos');
		}
		$mainframe->redirect( 'index.php?option=com_community&view=photos' , $message , ($error) ? 'error' : 'warning' );
	}
}
