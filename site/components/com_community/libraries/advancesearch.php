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

require_once JPATH_ROOT .'/components/com_community/libraries/core.php';

class CAdvanceSearch
{
	var $_filter = null;
	var $_data = null;
	var $_pagination = null;

	static public function &getFields()
	{
		$model = CFactory::getModel('profile');
		$filters = array('searchable' => '1');
		$fields =  $model->getAllFields($filters);

		JFactory::getLanguage()->load(COM_USER_NAME, JPATH_ROOT);
		// we need to get the user name / email seprately as these data did not
		// exists in custom profile fields.
		$config = CFactory::getConfig();
		$nameOptGroup = new stdClass();
		$nameOptGroup->type = "group";
		$nameOptGroup->published = 1;
		$nameOptGroup->name = JText::_('COM_COMMUNITY_ADVANCEDSEARCH_NAME');
		$nameOptGroup->visible = 1;

		$fields[JText::_('COM_COMMUNITY_ADVANCEDSEARCH_NAME')] = $nameOptGroup;

		$obj = new stdClass();
		$obj->type = "text";
		$obj->searchable = true;
		$obj->published = 1;
		$obj->name = JText::_('COM_COMMUNITY_ADVANCEDSEARCH_NAME');
		$obj->visible = 1;
		$obj->fieldcode = "username";
		$fields[$nameOptGroup->name]->fields[] = $obj;

		if ($config->get('privacy_search_email') != 2) {
			$obj = new stdClass();
			$obj->type = "email";
			$obj->searchable = true;
			$obj->published = 1;
			$obj->name = JText::_('COM_COMMUNITY_ADVANCEDSEARCH_EMAIL');
			$obj->visible = 1;
			$obj->fieldcode = "useremail";
			$fields[$nameOptGroup->name]->fields[] = $obj;
		}
		return $fields;
	}

	public function &getFieldList($fieldId)
	{
		$model = CFactory::getModel('search');
		$fieldList = $model->getFieldList($fieldId);
		return $fieldList;
	}

	static public function getResult($filter = array(), $join = 'and', $avatarOnly = '', $sorting = '')
	{
		$model = CFactory::getModel('search');
		$result = $model->getAdvanceSearch($filter, $join, $avatarOnly, $sorting);
		$pagination = $model->getPagination();

		$obj = new stdClass();
		$obj->result = $result;
		$obj->pagination = $pagination;
		$obj->operator = $join;

		return $obj;
	}

	public function setFilter()
	{
	}

	/**
	 * method used to return the required condition selection.
	 * param - field type - string
	 * return - assoc array
	 */
	public function &getFieldCondition($type)
	{
		$cond = array();

		switch ($type) {
			case 'date':
				$cond = array(
					'between' => JText::_('COM_COMMUNITY_CUSTOM_BETWEEN'),
					'equal' => JText::_('COM_COMMUNITY_CUSTOM_EQUAL'),
					'notequal' => JText::_('COM_COMMUNITY_CUSTOM_NOT_EQUAL'),
					'lessthanorequal' => JText::_('COM_COMMUNITY_CUSTOM_LESS_THAN_OR_EQUAL'),
					'greaterthanorequal' => JText::_('COM_COMMUNITY_CUSTOM_GREATER_THAN_OR_EQUAL')
				);
				break;
			case 'birthdate':
				$cond = array(
					'between' => JText::_('COM_COMMUNITY_CUSTOM_BETWEEN'),
					'equal' => JText::_('COM_COMMUNITY_CUSTOM_EQUAL'),
					'lessthanorequal' => JText::_('COM_COMMUNITY_CUSTOM_LESS_THAN_OR_EQUAL'),
					'greaterthanorequal' => JText::_('COM_COMMUNITY_CUSTOM_GREATER_THAN_OR_EQUAL')
				);
				break;
			case 'checkbox':
			case 'radio':
			case 'select':
			case 'singleselect':
			case 'list':
				$cond = array(
					'equal' => JText::_('COM_COMMUNITY_CUSTOM_EQUAL'),
					'notequal' => JText::_('COM_COMMUNITY_CUSTOM_NOT_EQUAL')
				);
				break;
			case 'email':
				$cond = array(
					'equal' => JText::_('COM_COMMUNITY_CUSTOM_EQUAL')
				);
				break;
			case 'textarea':
			case 'text':
			default:
				$cond = array(
					'contain' => JText::_('COM_COMMUNITY_CUSTOM_CONTAIN'),
					'equal' => JText::_('COM_COMMUNITY_CUSTOM_EQUAL'),
					'notequal' => JText::_('COM_COMMUNITY_CUSTOM_NOT_EQUAL')
				);
				break;
		}

		return $cond;
	}

	/**
	 * Method used to return the current MySQL version that running
	 * return - float
	 */
	static public function getMySQLVersion()
	{
		$db = JFactory::getDBO();

		$query = 'SELECT VERSION()';
		$db->setQuery($query);
		$result = $db->loadResult();

		preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $result, $version);

		if (function_exists('floatval')) {
			return floatval($version[0]);
		} else {
			return doubleval($version[0]);
		}
	}
}
