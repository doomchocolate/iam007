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
class CommunityControllerGroupCategories extends CommunityController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask( 'publish' , 'savePublish' );
		$this->registerTask( 'unpublish' , 'savePublish' );
	}

	public function ajaxTogglePublish( $id , $type, $viewName = false )
	{
		return parent::ajaxTogglePublish( $id , $type , 'groupcategories' );
	}

	public function ajaxSaveCategory( $data )
	{
		$response	= new JAXResponse();

		$row		= JTable::getInstance( 'GroupCategory', 'CTable' );
		$row->load( $data['id'] );
		$row->parent	    =   $data['parent'];
		$row->name	    =   $data['name'];
		$row->description   =   $data['description'];

		$row->store();

		if( $data['id'] != 0 )
		{
			// Update the rows in the table at the page.
			$response->addAssign( 'group-title-' . $data['id'] , 'innerHTML' , $row->name );
			$response->addAssign( 'group-description-' . $data['id'] , 'innerHTML' , $row->description );
		}
		else
		{
			$response->addScriptCall('azcommunity.redirect', JURI::base() . 'index.php?option=com_community&view=groupcategories');
		}
		$response->addScriptCall('cWindowHide');
		$this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS_CAT));
		return $response->sendResponse();
	}

	public function ajaxEditCategory( $id )
	{
		$response	=   new JAXResponse();
		$uri		=   JURI::base();
		$db		= JFactory::getDBO();
		$data		=   '';

		// Get the event categories
		$model		= $this->getModel( 'groupcategories' );
		$categories	= $model->getCategories();

		$row		= JTable::getInstance( 'groupcategories', 'CommunityTable' );
		$row->load( $id );

		// Escape the output
		//CFactory::load( 'helpers' , 'string' );
		$row->name	    =	CStringHelper::escape($row->name);
		$row->description   =	CStringHelper::escape($row->description);

		ob_start();
?>

		<div class="alert notice">
			<?php echo JText::_('COM_COMMUNITY_GROUPS_CATEGORY_DESC');?>
		</div>

		<form action="#" method="post" name="editGroupCategory" id="editGroupCategory">
		<table cellspacing="0" class="admintable" border="0" width="100%">
			<tbody>
				<tr>
					<td class="key" width="150" ><span class="js-tooltip"  title="<?php echo JText::_('COM_COMMUNITY_PARENT_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_PARENT');?></span></td>
					<td>
					    <select name="parent">
						    <option value="<?php echo COMMUNITY_NO_PARENT; ?>"><?php echo JText::_('COM_COMMUNITY_NO_PARENT'); ?></option>
						    <?php
						    for( $i = 0; $i < count( $categories ); $i++ )
						    {
							if($categories[$i]->id != $id):
								$selected	= ($row->parent == $categories[$i]->id ) ? ' selected="selected"' : '';
						    ?>
						    <option value="<?php echo $categories[$i]->id; ?>"<?php echo $selected; ?>><?php echo $categories[$i]->name; ?></option>
						    <?php endif;
							}
							?>
					    </select>
					</td>
				</tr>
				<tr>
					<td class="key"><span class="js-tooltip"  title="<?php echo JText::_('COM_COMMUNITY_NAME_CATEGORY_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_NAME');?></span></td>
					<td><input type="text" name="name" size="35" value="<?php echo ($id) ? $row->name : ''; ?>" /></td>
				</tr>
				<tr>
					<td class="key"><span class="js-tooltip"  title="<?php echo JText::_('COM_COMMUNITY_DESC_CATEGORY_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_DESCRIPTION');?></span></td>
					<td>
						<textarea name="description" rows="5" cols="30"><?php echo ($id) ? $row->description : ''; ?></textarea>
					</td>
				</tr>
			</tbody>

			<input type="hidden" name="id" value="<?php echo ($id) ? $row->id : 0; ?>" />
		</table>
		</form>

<?php
		$contents	= ob_get_contents();
		ob_end_clean();

		$buttons	= '<input type="button" class="btn btn-small btn-primary pull-right" onclick="javascript:azcommunity.saveGroupCategory();return false;" value="' . JText::_('COM_COMMUNITY_SAVE') . '"/>';
		$buttons	.= '<input type="button" class="btn btn-small pull-left" onclick="javascript:cWindowHide();" value="' . JText::_('COM_COMMUNITY_CANCEL') . '"/>';

		$this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS_CAT));
		$response->addAssign('cWindowContent', 'innerHTML' , $contents);
		$response->addScriptCall( 'cWindowActions' , $buttons );
		return $response->sendResponse();
	}

	/**
	 * Remove a category
	 **/
	public function removecategory()
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$ids	= $jinput->post->get( 'cid', array(), 'array'); //JRequest::getVar( 'cid', array(), 'post', 'array' );
		$count	= count($ids);

		$row		= JTable::getInstance( 'GroupCategory', 'CTable' );

		foreach( $ids as $id )
		{
			if(!$row->delete( $id ))
			{
				// If there are any error when deleting, we just stop and redirect user with error.
				$message	= JText::_('COM_COMMUNITY_GROUPS_ASSIGNED_CATEGORIES');
				$mainframe->redirect( 'index.php?option=com_community&view=groupcategories' , $message , 'error');
				exit;
			}
		}
		$message	= JText::sprintf( '%1$s Category(s) successfully removed.' , $count );
		$this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS_CAT));
		$mainframe->redirect( 'index.php?option=com_community&view=groupcategories' , $message ,'message' );
	}
}