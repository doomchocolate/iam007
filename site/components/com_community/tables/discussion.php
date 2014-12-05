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

// Include interface definition
//CFactory::load( 'models' , 'tags' );

class CTableDiscussion extends JTable
	implements CTaggable_Item
{

	var $id			= null;
	var $groupid	= null;
	var $creator 	= null;
	var $created 	= null;
	var $title 		= null;
	var $message 	= null;
	var $lock	 	= null;
	var $params		= null;

	/**
	 * Constructor
	 */
	public function __construct( &$db )
	{
		parent::__construct( '#__community_groups_discuss', 'id', $db );
	}

	public function check()
	{
		// Filter the discussion
		$config = CFactory::getConfig();
		//$clean = ('none' != $config->get('htmleditor'));

		$safeHtmlFilter	= CFactory::getInputFilter();
		$this->title	= $safeHtmlFilter->clean($this->title);

		$safeHtmlFilter	= CFactory::getInputFilter($config->getBool('allowhtml'));
                $this->message 	= $safeHtmlFilter->clean($this->message);

		return true;
	}

	public function store($updateNulls = false)
	{
		if (!$this->check()) {
			return false;
		}

		$result = parent::store();

		if($result)
		{
			$this->_updateGroupStats();
		}
		return $result;
	}

	/**
	 * Delete the discussion
	 * @param  [type] $oid [description]
	 * @return [type]
	 */
	public function delete($oid=null)
	{
		// Delete the stream related to the discussion replies
		CActivities::remove('groups.discussion.reply', $this->id);

		$result = parent::delete($oid);

		if($result)
		{
			$this->_updateGroupStats();
		}


		return $result;
	}

	private function _updateGroupStats()
	{
		//CFactory::load( 'models' , 'groups' );
		$group	= JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $this->groupid );
		$group->updateStats();
		$group->store();
	}

	public function lock( $id=null, $lockStatus=false )
	{
		$db		= JFactory::getDBO();

		$obj		= new stdClass();
		$obj->id	= $id;
		$obj->lock	= $lockStatus;

		return $db->updateObject('#__community_groups_discuss',$obj,'id',false);

	}

	/**
	 * Return the title of the object
	 */
	public function tagGetTitle()
	{
		return $this->title;
	}

	/**
	 * Return the HTML summary of the object
	 */
    public function tagGetHtml()
	{
		return '';
	}

	/**
	 * Return the internal link of the object
	 *
	 */
	public function tagGetLink()
	{
		return $this->getViewURI();
	}

	/**
	 * Return true if the user is allow to modify the tag
	 *
	 */
	public function tagAllow($userid)
	{
		// @todo: neec to check with group admin
		return true;
	}

	public function getParams()
	{
		$params	= new CParameter( $this->params );

		return $params;
	}
}