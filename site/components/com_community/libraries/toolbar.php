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

require_once JPATH_ROOT.'/components/com_community/libraries/core.php';



/**
 * The following are deprecated since 2.2.x
 **/
if ( ! defined('TOOLBAR_HOME'))
{
	define('TOOLBAR_HOME', 'HOME');
}

if ( ! defined('TOOLBAR_PROFILE'))
{
	define('TOOLBAR_PROFILE', 'PROFILE');
	define('TOOBLAR_PROFILE_LINK' , 'index.php?option=com_community&view=profile' );
}

if ( ! defined('TOOLBAR_FRIEND'))
{
	define('TOOLBAR_FRIEND', 'FRIEND');
	define('TOOLBAR_FRIEND_LINK' , 'index.php?option=com_community&view=friends' );
}

if ( ! defined('TOOLBAR_APP'))
{
	define('TOOLBAR_APP', 'APP');
}

if ( ! defined('TOOLBAR_INBOX'))
{
	define('TOOLBAR_INBOX', 'INBOX');
}

/**
 * Deprecated since 2.2.x
 **/
class CToolbar
{
	var $_toolbar = array();

	public function CToolbar()
	{
		$this->_toolbar	= array(
			TOOLBAR_HOME    => null,
			TOOLBAR_PROFILE => null,
			TOOLBAR_FRIEND  => null,
			TOOLBAR_APP     => null,
			TOOLBAR_INBOX   => null
		);
	}

	/**
	 * Function to add new toolbar group.
	 * param - key : string - the key of the group
	 *       - caption : string - the label of the group name
	 *       - link	: string - the url that link to the page
	 */
	public function addGroup($key, $caption = '', $link = '')
	{
		if ( ! array_key_exists($key, $this->_toolbar))
		{
			$newGroup          = new stdClass();
			$newGroup->caption = $caption;
			$newGroup->link    = $link;
			$newGroup->view    = array();
			$newGroup->child   = array(
				'prepend' => array(),
				'append'  => array()
			);

			$this->_toolbar[strtoupper($key)] = $newGroup;
		}
	}

	/**
	 * Function used to remove toolbar group and its associated menu items.
	 * param - key : string - the key of the group
	 */
	public function removeGroup($key)
	{
		if (array_key_exists($key, $this->_toolbar))
		{
			unset($this->_toolbar[strtoupper($key)]);
		}
		else
		{
			//this is for new toolbar system
			$toolbar = CToolbarLibrary::getInstance();
			$toolbar->removeGroup($key);
		}
	}


	/**
	 * Function to add new toolbar menu items.
	 * param - groupKey : string - the key of the group
	 *       - itemKey : string - the unique key of the menu item
	 *       - caption : string - the label of the menu item name
	 *       - link	: string - the url that link to the page
	 *       - order : string - display sequence : append | prepend
	 *       - isScriptCall : boolean - to indicate whether this is a javascript function or is a anchor link.
	 *       - hasSeparator : boolean - to indicate whether this item should use the class 'seperator' from JomSocial.
	 */

	public function addItem($groupKey, $itemKey, $caption = '', $link = '', $order = 'append', $isScriptCall = false, $hasSeparator = false)
	{
		$sorting = $order;

		if (array_key_exists($groupKey, $this->_toolbar))
		{
			$tbGroup	=  $this->_toolbar[strtoupper($groupKey)];
			$childItem	=  $tbGroup->child;

			$child               = new stdClass();
			$child->caption      = $caption;
			$child->link         = $link;
			$child->isScriptCall = $isScriptCall;
			$child->hasSeparator = $hasSeparator;

			if ($sorting != 'append' && $sorting != 'prepend')
			{
				$sorting = 'append';
			}

			$tbGroup->child[$sorting][$itemKey] = $child;
		}
	}

	/**
	 * Function used to remove toolbar menu item
	 * param - groupKey : string - the key of the group
	 *       - itemKey : string - the unique key of the menu item
	 */
	public function removeItem($groupKey, $itemKey)
	{
		if (array_key_exists($groupKey, $this->_toolbar))
		{
			$tbGroup   =  $this->_toolbar[strtoupper($groupKey)];
			$childItem =  $tbGroup->child;

			if (is_array($itemKey))
			{
				if (array_key_exists($itemKey, $childItem['prepend']))
				{
					unset($childItem['prepend'][$itemKey]);
				}

				if (array_key_exists($itemKey, $childItem['append']))
				{
					unset($childItem['append'][$itemKey]);
				}
			}
		}
		else
		{
			//this is for new toolbar system
			$toolbar = CToolbarLibrary::getInstance();
			$toolbar->removeItem($groupKey,$itemKey);
		}
	}

	/**
	 * Function used to return html anchor link
	 * param  - string - toolbar group key
	 *        - string - order of the items
	 * return - string - html anchor links
	 */
	public function getMenuItemObjects( $groupKey , $order )
	{
		$sorting    = array();
		$itemString = '';
		$result     = array();

		if ($order != 'append' && $order != 'prepend' && $order != 'all')
		{
			$sorting[] = 'append';
		}
		elseif ($order == 'all')
		{
			$sorting[] = 'prepend';
			$sorting[] = 'append';
		}
		else
		{
			$sorting[] = $order;
		}

		if (isset($this->_toolbar) && !empty($this->_toolbar[$groupKey]))
		{
			$toolbarItems = $this->_toolbar[$groupKey]->child;

			foreach ($sorting as $row)
			{
				$menuItems = $toolbarItems[$row];

				if ( ! empty($menuItems))
				{
					foreach ($menuItems as $row)
					{
						$caption      = $row->caption;
						$link         = $row->link;
						$isScriptCall = $row->isScriptCall;
						$separator    = (isset($row->hasSeparator) && $row->hasSeparator) ? 'class="has-separator"' : '';

						if (isset($link) && !empty($link))
						{
							$data            = new stdClass();
							$data->type      = $isScriptCall ? 'script' : 'link';
							$data->separator = $separator;
							$data->link      = $link;
							$data->caption   = $caption;

							$result[]        = $data;
						}
					}
				}
			}
		}

		return $result;
	}


	/**
	 * Deprecated since 1.8.x
	 */
	public function getMenuItems($groupKey, $order)
	{
		$data       = $this->getMenuItemObjects( $groupKey , $order );
		$sorting    = array();
		$itemString = '';

		foreach ($data as $item)
		{
			if ($item->type == 'script')
			{
				$itemString .= '<a href="javascript:void(0)" onclick="'.$item->link.';" '.$item->separator.'>'.$item->caption.'</a>';
			}
			else
			{
				$itemString .= '<a href="'.$item->link.'" '.$item->separator. '>'.$item->caption.'</a>';
			}
		}

		return $itemString;
	}

	/**
	 *	Function to retrieve those toolbar that user custom add.
	 *	return - an array of object.
	 */
	public function getExtraToolbars()
	{
		$tbExtra = array();

		if (isset($this->_toolbar) && !empty($this->_toolbar))
		{
			// @TODO Joomla 2.5.6 should have higher PHP version requirement. To be confirm and replaced
			//
			// we cant use array_diff_assoc bcos only php version > 4.3.0 support.
			//so no choice but we have to use looping.

			$tbCore = array(
				TOOLBAR_HOME    => '1',
				TOOLBAR_PROFILE => '1',
				TOOLBAR_FRIEND  => '1',
				TOOLBAR_APP     => '1',
				TOOLBAR_INBOX   => '1',
			);

			foreach ($this->_toolbar as $key => $val)
			{
				if ( ! array_key_exists($key, $tbCore))
				{
					$tbExtra[$key] = $val;
				}
			}
		}

		return $tbExtra;
	}


	/**
	 * Function to retrieve custom toolbar menu items to caller
	 * param - groupKey : string - the key of the group
	 * return array of object
	 */
	public function getToolbarItems($groupKey)
	{
		if (array_key_exists($groupKey, $this->_toolbar))
		{
			$tbGroup = $this->_toolbar[strtoupper($groupKey)];

			return $tbGroup;
		}
		else
		{
			return '';
		}
	}

	/**
	 * Function used to determined whether a core menu group was set.
	 * param  - string - toolbar group key
	 * return - boolean
	 */
	public function hasToolBarGroup($groupKey)
	{
		if (array_key_exists($groupKey, $this->_toolbar))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Function to add views that associated with the toolbar group.
	 * param  - string - group key
	 * param  - string - view name
	 */
	public function addGroupActiveView($groupkey, $viewName)
	{
		if ( ! empty($groupkey) && ! empty($viewName))
		{
			if (array_key_exists($groupkey, $this->_toolbar))
			{
				$tbGroup =  $this->_toolbar[strtoupper($groupkey)];
				$tbView  =  $tbGroup->view;

				if ( ! in_array($viewName, $tbView))
				{
					array_push($tbView, $viewName);
				}
			}
		}
	}

	/**
	 * Function to get the toolbar group key based on what view being associated.
	 * param  - string - view name
	 * return - string
	 */
	public function getGroupActiveView($viewName)
	{
		$groupKey = '';

		if ( ! empty($viewName))
		{
			foreach ($this->_toolbar as $key => $tbGroup)
			{
				$tbView	= $tbGroup->view;

				if (in_array($viewName, $tbView))
				{
					$groupKey	= $key;
					break;
				}
			}
		}

		return $groupKey;
	}

	/**
	 * Function used to return all the toolbar group keys.
	 * return - array
	 */
	public function getToolBarGroupKey()
	{
		return array_keys($this->_toolbar);
	}

	/**
	 * Function to get the current viewing page, the toolbar group key.
	 * param  - string - uri of the current view page
	 * return - string
	 */
	public function getActiveToolBarGroup($uri)
	{
		$activeGroup = '';
		$sorting     = array('prepend', 'append');

		foreach ($this->_toolbar as $key => $group)
		{
			//check the parent link
			if (htmlspecialchars_decode($uri) == htmlspecialchars_decode($group->link))
			{
				$activeGroup = $key;
				break;
			}

			//check the child links
			$toolbarItems = $group->child;

			foreach ($sorting as $row)
			{
				$menuItems =  $toolbarItems[$row];
				if ( ! empty($menuItems))
				{
					foreach ($menuItems as $item)
					{
						if ( ! $item->isScriptCall)
						{
							if (htmlspecialchars_decode($uri) == htmlspecialchars_decode($item->link))
							{
								$activeGroup = $key;
								break;
							}
						}
					}
				}
			}
		}

		return $activeGroup;
	}
}

class CToolbarLibrary
{
	var $items = array();

	private function __construct()
	{
		$model       = CFactory::getModel( 'Toolbar' );
		$this->items = $model->getItems();

	}

	public static function getInstance()
	{
		static $instance = null;

		if (is_null($instance))
		{
			$instance = new CToolbarLibrary();
		}

		return $instance;
	}

	public function getItems()
	{
		return $this->items;
	}

	private function findGroup($key)
	{
		foreach ($this->items as $index => $item)
		{
			$menu = $item->item;

			if (isset($menu->id) && $menu->id == $key)
			{
				return $index;
			}
		}
		return null;
	}

	public function addItem($groupKey, $itemKey, $caption = '', $link = '', $order = 'append', $isScriptCall = false, $hasSeparator = false)
	{
		$groupIndex = $this->findGroup($groupKey);

		if ( ! is_null($groupIndex))
		{
			$childObj         = new stdClass();
			$childObj->id     = $itemKey;
			$childObj->link   = $link;
			$childObj->name   = $caption;
			$childObj->script = $isScriptCall;

			if ($order == 'append')
			{
				array_push($this->items[$groupIndex]->childs, $childObj);
			}
			else
			{
				array_unshift($this->items[$groupIndex]->childs, $childObj);
			}
		}
	}

	public function removeItem($groupKey, $itemKey)
	{
		if ($groupKey)
		{
			foreach ($this->items as $index1 => $item)
			{
				$menu = $item->item;

				if (isset($menu->id) && $menu->id == $groupKey)
				{
					if (isset($item->childs) && is_array($item->childs))
					{
						foreach ($item->childs as $index2 => $child)
						{
							if (isset($child->id) && $child->id == $itemKey)
							{
								// the item is found, unset it
								unset($item->childs[$index2]);
							}
						}
					}
				}

				$this->items[$index1] = $item;
			}
		}
	}

	public function addGroup($key, $caption = '', $link = '')
	{
		$groupIndex = $this->findGroup($key);

		if (is_null($groupIndex))
		{
			$obj             = new stdClass();
			$obj->item       = new stdClass();
			$obj->item->id   = $key;
			$obj->item->name = $caption;
			$obj->item->link = $link;
			$obj->childs     = array();
			$this->items[]   = $obj;
		}
	}

	public function removeGroup($key)
	{
		if ($key)
		{
			foreach ($this->items as $index1 => $item)
			{
				$menu = $item->item;

				if (isset($menu->id) && $menu->id == $key)
				{
					unset($this->items[$index1]);
				}
			}
		}
	}


	public function getHTML($userId = '')
	{
		static $html = false;

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		if ( ! $html)
		{
			$my = CFactory::getUser();

			// @rule: Do not display toolbar for non logged in users.
			if (empty($my->id))
			{
				$task    = $jinput->get->get('task', '');
				$groupId = $jinput->get->get('groupid', '', 'INT');

				if ( ! empty($groupId) && $task != 'viewgroup')
				{
					return CMiniHeader::showGroupMiniHeader($groupId);
				}
				return CMiniHeader::showMiniHeader($userId);
			}

			$format	= $jinput->get->get('format', 'html', 'STRING');

			// @rule: For json formatted output, we do not want to display the output as well.
			if ($format == 'json')
			{
				return;
			}

			// Compatibility with other pages, we need to include necessary javascripts and css libraries.
			$this->attachHeaders();

			CWindow::load();
			$config     = CFactory::getConfig();
			$logoutLink = base64_encode(CRoute::_('index.php?option=com_community&view='.$config->get('redirect_logout'), false));
			$tmpl       = new CTemplate();
			$miniheader = CMiniHeader::showMiniHeader($userId);

			$groupMiniHeader = '';
			$task            = $jinput->get('task', '');
			$groupId         = $jinput->get('groupid', '', 'INT');

			// Show miniheader
			if ($task != 'viewgroup')
			{
				$groupMiniHeader = CMiniHeader::showGroupMiniHeader($groupId);
			}

			$menus = $this->getItems();
			$this->addLegacyToolbars($menus);

			$model      = CFactory::getModel( 'Toolbar' );
			$notifModel = CFactory::getModel('notification');

			$newMessageCount      = $this->getTotalNotifications( 'inbox' );
			$newEventInviteCount  = $this->getTotalNotifications( 'events' );
			$newFriendInviteCount = $this->getTotalNotifications( 'friends' );
			$newGroupInviteCount  = $this->getTotalNotifications( 'groups' );

			$myParams             = $my->getParams();
			$newNotificationCount = $notifModel->getNotificationCount($my->id,'0',$myParams->get('lastnotificationlist',''));

			$menuParams = '';
			if(isset($menus[$model->getActiveId(CToolbarLibrary::getActiveLink())]))
			{
				$menuParams = new CParameter($menus[$model->getActiveId(CToolbarLibrary::getActiveLink())]->item->params);
			}
			//add Event notification count with group notification count
			//$newEventInviteCount += $newGroupInviteCount;
			$totalNotifications = $newMessageCount + $newEventInviteCount + $newFriendInviteCount;

			$html = $tmpl->set('miniheader', $miniheader)
						->set('groupMiniHeader', $groupMiniHeader)
						->set('menus', $menus)
						->set('showToolbar', $config->get('show_toolbar'))
						->set('newMessageCount', $newMessageCount )
						->set('newFriendInviteCount', $newFriendInviteCount )
						->set('newEventInviteCount', ($newEventInviteCount + $newGroupInviteCount + $newNotificationCount))
						->set('logoutLink', $logoutLink)
						->set('active', $model->getActiveId(CToolbarLibrary::getActiveLink()))
						->set('notiAlert', ($newMessageCount + $newEventInviteCount + $newFriendInviteCount + $newNotificationCount))// @rule: Backward compatibility prior to 2.2
						->set('menuParams',$menuParams)
						->set('isMessageEnable',$config->get('enablepm'))
						->fetch('toolbar.index');
		}

		return $html;
	}

	public function addLegacyToolbars(&$menus)
	{
		// Retrieve legacy toolbars
		$CFactory = new CFactory();
		$legacyToolbar = $CFactory->getToolbar();

		$defaultMenus  = array('HOME', 'APP', 'INBOX', 'PROFILE', 'FRIEND');

		if ( ! is_array($legacyToolbar->_toolbar))
		{
			return;
		}

		foreach ($legacyToolbar->_toolbar as $toolbar => $item)
		{
			if (is_object($item))
			{
				if (in_array($toolbar, $defaultMenus))
				{
					foreach ($item->child as $position => $items)
					{
						if ( ! empty($items))
						{
							foreach ($items as $item_id => $child )
							{
								$obj               = new stdClass();
								$obj->item         = new stdClass();
								$obj->item->id     = $item_id;
								$obj->item->link   = $child->link;
								$obj->item->name   = $child->caption;
								$obj->item->script = $child->isScriptCall;
								$menus[]           = $obj;
							}
						}
					}
				}
				else
				{
					$obj             = new stdClass();
					$obj->item       = new stdClass();
					$obj->item->id   = $toolbar;
					$obj->item->name = $item->caption;
					$obj->item->link = $item->link;
					$obj->childs     = array();

					if (isset($item->child))
					{
						foreach ($item->child as $position => $items)
						{
							if ( ! empty($items))
							{
								foreach ($items as $item_id => $child)
								{
									$childObj         = new stdClass();
									$childObj->id     = $item_id;
									$childObj->link   = $child->link;
									$childObj->name   = $child->caption;
									$childObj->script = $child->isScriptCall;

									$obj->childs[]    = $childObj;
								}
							}
						}
					}

					$menus[] = $obj;
				}
			}
		}
	}

	private function getActiveLink()
	{
		$jinput = JFactory::getApplication()->input;
                /* Get link for special toolbar items */
                if($jinput->get('view') === 'memberlist')
                    return 'index.php?option=' . $jinput->get('option') . '&view=' . $jinput->get('view') . '&listid=' . $jinput->get('listid');
		return 'index.php?option=' . $jinput->get('option') . '&view=' . $jinput->get('view');
	}

	/**
	 * Get total number of unread or awaiting notifications
	 *
	 * @access	public
	 * @param	string	$app	The unique application or view name.
	 * @return	int		The number of unread notifications.
	 **/
	public function getTotalNotifications($app)
	{
		$model      = CFactory::getModel($app);
		$modelClass = 'CommunityModel'.ucfirst($app);

		$reflection	= new ReflectionClass($modelClass);

		if ( ! $reflection->implementsInterface('CNotificationsInterface'))
		{
			return 0;
		}

		return (int) $model->getTotalNotifications(CFactory::getUser());
	}

	/**
	 * Attach necessary scripts and stylesheets for the toolbar to operate correctly on 3rd party
	 * environments.
	 **/
	private function attachHeaders()
	{
		$document = JFactory::getDocument();
		$config   = CFactory::getConfig();

		if ($document->getType() != 'html')
		{
			return;
		}

		$js = 'assets/window-1.0.min.js';
		CAssets::attach($js, 'js');

		$js	= 'assets/script-1.2.min.js';
		CAssets::attach($js, 'js');



		CTemplate::addStylesheet('style');

		$templateParams = CTemplate::getTemplateParams();
		CTemplate::addStylesheet('style.'.$templateParams->get('colorTheme','green'));

		// Load rtl stylesheet
		if ($document->direction == 'rtl')
		{
			CTemplate::addStylesheet('style.rtl');
		}

		// This need to be loaded so the popups will work correctly in notification window

		CWindow::load();

		$template = new CTemplateHelper;
		$styleIE7 = $template->getTemplateAsset('styleIE7', 'css');
		$styleIE6 = $template->getTemplateAsset('styleIE6', 'css');

		$css = '<!-- JomSocial -->
				<!--[if IE 7.0]>
				<link rel="stylesheet" href="'.$styleIE7->url.'" type="text/css" />
				<![endif]-->
				<!--[if lte IE 6]>
				<link rel="stylesheet" href="'.$styleIE6->url.'" type="text/css" />
				<![endif]-->';

		$document->addCustomTag( $css );

		$css = 'assets/autocomplete.css';
		CAssets::attach($css, 'css');

		// Load joms.ajax
		CTemplate::addScript('joms.ajax');
	}
}