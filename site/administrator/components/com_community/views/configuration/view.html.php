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
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );
/**
 * Configuration view for JomSocial
 */
class CommunityViewConfiguration extends JViewLegacy
{
	/**
	 * The default method that will display the output of this view which is called by
	 * Joomla
	 *
	 * @param	string template	Template file name
	 **/
	public function display( $tpl = null )
	{
		$document	= JFactory::getDocument();

		$params	= $this->get( 'Params' );
		//user's email privacy setting
		//CFactory::load( 'libraries' , 'notificationtypes' );
		$notificationTypes = new CNotificationTypes();

		$lists = array();

		for ($i=1; $i<=31; $i++) {
			$qscale[]	= JHTML::_('select.option', $i, $i);
		}

		$lists['qscale'] = JHTML::_('select.genericlist',  $qscale, 'qscale', 'class="inputbox" size="1"', 'value', 'text', $params->get('qscale', '11'));

		$videosSize = array
		(
			JHTML::_('select.option', '320x240', '320x240 (QVGA 4:3)'),
			JHTML::_('select.option', '400x240', '400x240 (WQVGA 5:3)'),
			JHTML::_('select.option', '400x300', '400x300 (Quarter SVGA 4:3)'),
			JHTML::_('select.option', '480x272', '480x272 (Sony PSP 30:17)'),
			JHTML::_('select.option', '480x320', '480x320 (iPhone 3:2)'),
			JHTML::_('select.option', '480x360', '480x360 (4:3)'),
			JHTML::_('select.option', '512x384', '512x384 (4:3)'),
			JHTML::_('select.option', '600x480', '600x480 (4:3)'),
			JHTML::_('select.option', '640x360', '640x360 (16:9)'),
			JHTML::_('select.option', '640x480', '640x480 (VCA 4:3)'),
			JHTML::_('select.option', '800x600', '800x600 (SVGA 4:3)'),
		);

		$lists['videosSize'] = JHTML::_('select.genericlist',  $videosSize, 'videosSize', 'class="inputbox" size="1"', 'value', 'text', $params->get('videosSize'));


		$imgQuality = array
		(
			JHTML::_('select.option', '60', 'Low'),
			JHTML::_('select.option', '80', 'Medium'),
			JHTML::_('select.option', '90', 'High'),
			JHTML::_('select.option', '95', 'Very High'),
		);

		$lists['imgQuality'] = JHTML::_('select.genericlist',  $imgQuality, 'output_image_quality', 'class="inputbox" size="1"', 'value', 'text', $params->get('output_image_quality'));

		// Group discussion order option
		$groupDiscussionOrder = array(
			JHTML::_('select.option', 'ASC', 'Older first'),
			JHTML::_('select.option', 'DESC', 'Newer first'),
		);
		$lists['groupDicussOrder'] = JHTML::_('select.genericlist',  $groupDiscussionOrder, 'group_discuss_order', 'class="inputbox" size="1"', 'value', 'text', $params->get('group_discuss_order'));

		$videoThumbSize = array(
				JHTML::_('select.option','320x180','320x180'),
				JHTML::_('select.option', '640x360', '640x360'),
				JHTML::_('select.option', '1280x720', '1280x720'),
		);

		$lists['videoThumbSize'] = JHTML::_('select.genericlist',  $videoThumbSize, 'group_discuss_order', 'class="inputbox" size="1"', 'value', 'text', $params->get('videosThumbSize'));
		$dstOffset	= array();
		$counter = -4;
		for($i=0; $i <= 8; $i++ ){
			$dstOffset[] = 	JHTML::_('select.option', $counter, $counter);
			$counter++;
		}

		$lists['dstOffset']	= JHTML::_('select.genericlist',  $dstOffset, 'daylightsavingoffset', 'class="inputbox" size="1"', 'value', 'text', $params->get('daylightsavingoffset'));
		$networkModel		= $this->getModel( 'network' , false );
		$JSNInfo			= $networkModel->getJSNInfo();
		$JSON_output		= $networkModel->getJSON();
		$lists['enable']	= JHTML::_('select.booleanlist',  'network_enable', 'class="inputbox"', $JSNInfo['network_enable'] );
		$uploadLimit		= ini_get('upload_max_filesize');
		$uploadLimit		= CString::str_ireplace('M', ' MB', $uploadLimit);

		require_once( JPATH_ROOT.'/administrator/components/com_community/libraries/autoupdate.php' );
		$isuptodate = CAutoUpdate::checkUpdate();

		$this->assign( 'JSNInfo', $JSNInfo );
		$this->assign( 'JSON_output', $JSON_output );
		$this->assign( 'lists', $lists );
		$this->assign( 'uploadLimit' , $uploadLimit );
		$this->assign( 'config' , $params );
		$this->assign( 'isuptodate' , $isuptodate );

		$this->assign('notificationTypes',$notificationTypes);

		parent::display( $tpl );
	}

	public function getTemplatesList( $name , $default = '' )
	{
		$path	= dirname(JPATH_BASE) . '/components/com_community/templates';

		if( $handle = @opendir($path) )
		{
			while( false !== ( $file = readdir( $handle ) ) )
			{
				// Do not get '.' or '..' or '.svn' since we only want folders.
				if( $file != '.' && $file != '..' && $file != '.svn' && JFolder::exists( $path .'/'. $file) )
					$templates[]	= $file;
			}
		}

		$html	= '<select name="' . $name . '">';

		foreach( $templates as $template )
		{
			if( $template )
			if( !empty( $default ) )
			{
				$selected	= ( $default == $template ) ? ' selected="true"' : '';
			}
			$html	.= '<option value="' . $template . '"' . $selected . '>' . $template . '</option>';
		}
		$html	.= '</select>';

		return $html;
	}

	public function getKarmaHTML( $name , $value, $readonly=false, $updateTarget='')
	{
		$isReadOnly	= ($readonly) ? ' readonly="readonly"' : '';
		$requiredTargetUpdate = (! empty($updateTarget)) ? 'onblur="azcommunity.updateField(\''.$name.'\', \''.$updateTarget.'\')"' : '';

		$html	= '<table>';
		$html	.= '<tr>';
		$html	.= '	<td>';
		if ($readonly) {
			$html .= '<span class="karma_readonly" id="' . $name . '">' . $value . '</span> ';
		} else {
			$html	.= '<input type="text" value="' . $value . '" name="' . $name . '" id="'.$name.'" '.$isReadOnly.' '.$requiredTargetUpdate.' /> ';
		}
		// $html	.= JText::_('COM_COMMUNITY_CONFIGURATION_KARMA_USE_IMAGE');
		$html	.= '	</td>';
		$html	.= '	<td>';
		$html	.= '	&nbsp;&nbsp;&nbsp;<img class="com_karmaImage" src="' . $this->_getKarmaImage( $name ) . '" />';
		$html	.= '	</td>';
		$html	.= '</tr>';
		$html	.= '</table>';
		return $html;
	}

	public function getNotifyTypeHTML( $selected )
	{
		$types	= array();

		$types[]	= array( 'key' => '1' , 'value' => JText::_('COM_COMMUNITY_EMAIL') );
		$types[]	= array( 'key' => '2' , 'value' => JText::_('COM_COMMUNITY_PRIVATE_MESSAGE') );

		$html		= '<select name="notifyby">';

		foreach( $types as $type => $option )
		{
			$selectedData	= '';
			if( $option['key'] == $selected )
			{
				$selectedData	= ' selected="true"';
			}
			$html	.= '<option value="' . $option['key'] . '"' . $selectedData . '>' . $option['value'] . '</option>';
		}
		$html	.= '</select>';

		return $html;
	}

	public function getPrivacyHTML( $name , $selected , $showSelf = false )
	{
		$public		= ( $selected == 0 ) ? 'btn-success' : 'btn-light';
		$members	= ( $selected == 20 ) ? 'btn-success' : 'btn-light';
		$friends	= ( $selected == 30 ) ? 'btn-success' : 'btn-light';
		$self		= ( $selected == 40 ) ? 'btn-success' : 'btn-light';

		$html = '<div class="btn-group" data-toggle-name="' . $name . '" data-toggle="buttons-radio">';

		$html .= '<button type="button" value="0" class="btn btn-small ' . $public . '" />' . JText::_('COM_COMMUNITY_PUBLIC').'</button>';
		$html .= '<button type="button" value="20" class="btn btn-small ' . $members . '" /> ' . JText::_( 'COM_COMMUNITY_MEMBERS').'</button>';
		$html .= '<button type="button" value="30" class="btn btn-small ' . $friends . '" /> ' . JText::_('COM_COMMUNITY_FRIENDS').'</button>';

		if( $showSelf )
		{
			$html .= '<button type="button" value="40" class="btn btn-small ' . $self . '" />' . JText::_('COM_COMMUNITY_SELF').'</button>';
		}

		$html .= '</div>';

		$html .= '<input type="hidden" name="' . $name . '" value="' . $selected . '" />';

		return $html;
	}

	/**
	 * Method to return the image path for specific elements
	 * @access	private
	 *
	 * @return	string	$image	The path to the image.
	 */
	public function _getKarmaImage( $name )
	{
		$image	= '';

		switch( $name )
		{
			case 'point0':
				$image	= JURI::root() . 'components/com_community/templates/default/images/karma-0.5-5.png';
				break;
			case 'point1':
				$image	= JURI::root() . 'components/com_community/templates/default/images/karma-1-5.png';
				break;
			case 'point2':
				$image	= JURI::root() . 'components/com_community/templates/default/images/karma-2-5.png';
				break;
			case 'point3':
				$image	= JURI::root() . 'components/com_community/templates/default/images/karma-3-5.png';
				break;
			case 'point4':
				$image	= JURI::root() . 'components/com_community/templates/default/images/karma-4-5.png';
				break;
			case 'point5':
				$image	= JURI::root() . 'components/com_community/templates/default/images/karma-5-5.png';
				break;
			default:
				$image	= JURI::root() . 'components/com_community/templates/default/images/karma-0-5.png';
				break;
		}
		return $image;
	}

	public function setToolBar()
	{
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		// Set the titlebar text
		JToolBarHelper::title( JText::sprintf( 'COM_COMMUNITY_CONFIGURATION', ucwords(str_replace('-',' ',JRequest::getCmd( 'cfgSection' , 'site' ))) ), 'configuration');

		// Add the necessary buttons
		JToolBarHelper::save( 'saveconfig', JText::_('COM_COMMUNITY_SAVE') );
		JToolBarHelper::cancel();
	}

	public function getEditors()
	{
		$db		= JFactory::getDBO();

		// compile list of the editors
		$query = 'SELECT ' . $db->quoteName('element') . ' AS ' . $db->quoteName('value') . ', ' . $db->quoteName('name') . ' AS ' . $db->quoteName('text')
				. ' FROM ' . $db->quoteName(PLUGIN_TABLE_NAME)
				. ' WHERE ' . $db->quoteName('folder') . ' = ' . $db->Quote('editors')
				. ' AND ' . $db->quoteName(EXTENSION_ENABLE_COL_NAME) . ' = ' . $db->Quote(1)
				. ' ORDER BY ' . $db->quoteName('ordering') . ', ' . $db->quoteName('name');
		$db->setQuery( $query );
		$editors = $db->loadObjectList();

		// Add JomSocial's Editor
		$editor    =	new stdClass();
		$editor->value	=   'jomsocial';
		$editor->text	=   'plg_editor_jomsocial';

		array_push( $editors, $editor );

		return $editors;
	}

	public function getFieldCodes( $elementName , $selected = '' )
	{
		$db		= JFactory::getDBO();
		$query	= 'SELECT DISTINCT ' . $db->quoteName('fieldcode') . ' FROM ' . $db->quoteName('#__community_fields');
		$db->setQuery( $query );
		$fieldcodes	= $db->loadObjectList();

		$html		= '<select name="'. $elementName . '">';

		foreach( $fieldcodes as $fieldcode )
		{
			if( !empty($fieldcode->fieldcode ) )
			{
				$selectedData	= '';

				if( $fieldcode->fieldcode == $selected )
				{
					$selectedData	= ' selected="true"';
				}
				$html	.= '<option value="' . $fieldcode->fieldcode . '"' . $selectedData . '>' . $fieldcode->fieldcode . '</option>';
			}
		}
		$html	.= '</select>';

		return $html;
	}

	public function getFolderPermissionsPhoto( $name , $selected )
	{
		$all		= ( $selected == '0777' ) ? 'checked="true" ' : '';
		$default	= ( $selected == '0755' ) ? 'checked="true" ' : '';

		$html	 = '<input type="radio" value="0777" name="' . $name . '" ' . $all . '/> ' . JText::_('COM_COMMUNITY_CHMOD777');
		$html	.= '<input type="radio" value="0755" name="' . $name . '" ' . $default . '/> ' . JText::_('COM_COMMUNITY_SYSTEM_DEFAULT');

		return $html;
	}

	public function getFolderPermissionsVideo( $name , $selected )
	{
		$all		= ( $selected == '0777' ) ? 'checked="true" ' : '';
		$default	= ( $selected == '0755' ) ? 'checked="true" ' : '';

		$html	 = '<input type="radio" value="0777" name="' . $name . '" ' . $all . '/> ' . JText::_('COM_COMMUNITY_CHMOD777');
		$html	.= '<input type="radio" value="0755" name="' . $name . '" ' . $default . '/> ' . JText::_('COM_COMMUNITY_SYSTEM_DEFAULT');

		return $html;
	}
}
