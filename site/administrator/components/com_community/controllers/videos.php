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
class CommunityControllerVideos extends CommunityController
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

		$model		= $this->getModel( $viewName, 'CommunityAdminModel' );

		if( $model )
		{
			$view->setModel( $model , $viewName );
		}

		// Set the layout
		$view->setLayout( $layout );

		// Display the view
		$view->display();
	}

	public function ajaxTogglePublish($id , $type, $viewName = false )
	{
		$video	= JTable::getInstance( 'Video' , 'CTable' );
		$video->load( $id );

		return parent::ajaxTogglePublish( $id , $type , 'videos' );
	}

	public function ajaxEditVideo($id)
	{
		$response = new JAXResponse();

		$model = $this->getModel('videoscategories');
		$config = CFactory::getConfig();

		$categories = $model->getCategories();
		$video = JTable::getInstance('Video', 'CTable');

		$video->load($id);

		$video->title = CStringHelper::escape($video->title);
		$video->description = CStringHelper::escape($video->description);

		ob_start();
	?>
	<form name="editvideo" action="" method="post" id="editvideo">
	<div style="background-color: #F9F9F9; border: 1px solid #D5D5D5; margin-bottom: 10px; padding: 5px;font-weight: bold;">
		<?php echo JText::_('Edit Video Detail');?>
	</div>
	<table cellspacing="0" class="admintable" border="0" width="100%">
		<tbody>
		<tr>
			<td class="key" valign="top"><?php echo JText::_('COM_COMMUNITY_TITLE');?></td>
			<td><input type="text" id="title" name="title" class="input text" value="<?php echo $video->title;?>" style="width: 90%;"  maxlength="255"  /></tD>
		</tr>
		<tr>
			<td class="key"><?php echo JText::_('COM_COMMUNITY_DESCRIPTION');?></td>
			<td><textarea name="description" style="width: 90%;" rows="8" id="description"><?php echo $video->description; ?></textarea></td>
		</tr>
		<tr>
			<td class="key"><?php echo JText::_('COM_COMMUNITY_CATEGORY');?></td>
			<td>
				<select name="category_id">
					<?php
					for ($i = 0; $i < count($categories); $i++) {
						$selected = ($video->category_id == $categories[$i]->id) ? ' selected="selected"' : '';
						?>
						<option value="<?php echo $categories[$i]->id;?>"<?php echo $selected;?>><?php echo $categories[$i]->name;?></option>
						<?php
					}
					?>
				</select>
			</td>
		</tr>
		<?php if($config->get('videosmapdefault')) {?>
		<tr>
			<td class="key"><?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LOCATION');?></td>
			<td><input type="text" id="title" name="location" class="input text" value="<?php echo $video->location;?>" style="width: 90%;" /></td>
		</tr>
		<?php }?>
		<tr>
			<td class="key"><?php echo JText::_('COM_COMMUNITY_VIDEOS_WHO_CAN_SEE');?></td>
			<td><?php echo CPrivacy::getHTML( 'permissions', $video->permissions, COMMUNITY_PRIVACY_BUTTON_LARGE ); ?></td>
		</tr>
		</tbody>
	</table>
	<input type="hidden" name="id" value="<?php echo $video->id;?>"/>
	<input type="hidden" name="option" value="com_community"/>
	<input type="hidden" name="task" value="savevideos"/>
	<input type="hidden" name="view" value="videos"/>
	<?php

		$contents = ob_get_contents();
		ob_end_clean();

		$response->addAssign('cWindowContent', 'innerHTML', $contents);

		$action = '<input type="button" class="btn btn-small btn-info pull-right" onclick="azcommunity.saveVideo();" name="' . JText::_('COM_COMMUNITY_SAVE') . '" value="' . JText::_('COM_COMMUNITY_SAVE') . '" />';
		$action .= '&nbsp;<input type="button" class="btn btn-small pull-left" onclick="cWindowHide();" name="' . JText::_('COM_COMMUNITY_CLOSE') . '" value="' . JText::_('COM_COMMUNITY_CLOSE') . '" />';
		$response->addScriptCall('cWindowActions', $action);

		return $response->sendResponse();
	}

	public function saveVideos()
	{
		$video	= JTable::getInstance('Videos', 'CommunityTable');
		$id		= JRequest::getInt('id', '', 'post');

		if (empty($id)) {
			JError::raiseError('500', JText::_('COM_COMMUNITY_INVALID_ID'));
		}

		$postData = JRequest::get('post');

		$video->load($id);
		$video->bind($postData);

		$message = '';
		if ($video->store()) {
			$message = JText::_('COM_COMMUNITY_VIDEO_SUCCESSFULLY_SAVED');
		} else {
			$message = JText::_('COM_COMMUNITY_VIDEO_ERROR_WHILE_SAVING');
		}

		$mainframe = JFactory::getApplication();
		$mainframe->redirect('index.php?option=com_community&view=videos', $message, 'message');
	}

	public function ajaxviewVideo($id)
	{
		$response = new JAXResponse();

		$video = JTable::getInstance('Videos','CommunityTable');
		$video->load($id);

		$notiHtml = '<div class="cVideo-Player video-player text-center">
							' . $video->getPlayerHTML('560px','400px') . '
						</div>';

		$response->addScriptCall('cWindowAddContent', $notiHtml);

		//$response->addAssign('cWindowContent', 'innerHTML', $contents);

		return $response->sendResponse();
	}

	public function delete()
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$model		= $this->getModel( 'Videos' ,'CommunityAdminModel' );

		$id			= $jinput->post->get( 'cid' , '', 'array' );
		$errors		= false;
		$message	= JText::_('Videos has ben deleted');

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
			$message	= JText::_('Error deleting video');
		}
		$mainframe->redirect( 'index.php?option=com_community&view=videos' , $message ,'message');
	}
}
