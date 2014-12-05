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

require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );

class JElementGroups extends JElement
{
	var	$_name = 'Groups';

	public function fetchElement($name, $value, &$node, $control_name)
	{
		$lang = JFactory::getLanguage();
		$lang->load( 'com_community', JPATH_ROOT);
		
		$model		= CFactory::getModel( 'Groups' );
		$groups		= $model->getAllGroups();
		$fieldName	= $control_name.'['.$name.']';

	    ob_start();
		?>
		<select name="<?php echo $fieldName;?>">
			<?php foreach( $groups as $group ){ ?>
			<option value="<?php echo $group->id;?>"<?php echo $value == $group->id ? ' selected="selected"' : '';?>><?php echo $group->name;?></option>
			<?php } ?>
		</select>
		<?php
		$html   = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
}
