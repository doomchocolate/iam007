<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
JTable::addIncludePath( JPATH_ROOT . '/components/com_community/tables' );
class JElementMemberlist extends JElement
{
	var	$_name = 'MemberList';

	public function fetchElement($name, $value, &$node, $control_name)
	{
		$mainframe 	= JFactory::getApplication();

		$db			= JFactory::getDBO();
		$doc 		= JFactory::getDocument();
		$fieldName	= $control_name.'['.$name.']';
		$memberlist	= JTable::getInstance('MemberList' , 'CTable' );

		if ($value)
		{
			$memberlist->load($value);
		}
		else
		{
			$memberlist->title = JText::_('COM_COMMUNITY_USERS_SELECT_A_MEMBERLIST');
		}

		$js = "
		function jSelectMemberList(id, title, object) {
			document.getElementById(object + '_id').value = id;
			document.getElementById(object + '_name').value = title;
			document.getElementById('sbox-window').close();
		}";
		$doc->addScriptDeclaration($js);

		$link = 'index.php?option=com_community&amp;view=memberlist&task=element&amp;tmpl=component&amp;object='.$name;

		JHTML::_('behavior.modal', 'a.modal');
		$html = "\n".'<div style="float: left;"><input style="background: #ffffff;" type="text" id="'.$name.'_name" value="'.htmlspecialchars($memberlist->title, ENT_QUOTES, 'UTF-8').'" disabled="disabled" /></div>';
		$html .= '<div class="button2-left"><div class="blank"><a class="modal" title="'.JText::_('COM_COMMUNITY_USERS_SELECT_A_MEMBERLIST').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}">'.JText::_('Select').'</a></div></div>'."\n";
		$html .= "\n".'<input type="hidden" id="'.$name.'_id" name="'.$fieldName.'" value="'.(int)$value.'" />';

		return $html;
	}
}
