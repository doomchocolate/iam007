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
/**
 * JomSocial Table Model
 */
class CommunityTableConfiguration extends JTable
{
	var $name		= null;
	var $params		= null;

	public function __construct(&$db)
	{
		parent::__construct( '#__community_config' , 'name' , $db );
	}

	public function store($updateNulls = false){

		/*
		The reason why we need a custom store() function is because the key in the table is not auto increment
		Joomla's store() is doing UPDATE if key value is passed, and INSERT when not passed.
		But in case of config, key name is always passed, so we need to check first if the record is there.
		If not, just insert an empty record first before executing the Parent::store()
		*/
		$k = $this->_tbl_key;

		//check if key
		if(!empty($this->$k)){
			$query = "SELECT COUNT(*) FROM ".$this->_tbl." WHERE name=".$this->_db->quote($this->$k);
			$this->_db->setQuery( $query );
			$exist = $this->_db->loadResult();
			if(!$exist){
				$ins = new StdClass;
				$ins->$k = $this->$k;
				$this->_db->insertObject($this->_tbl, $ins, $this->_tbl_key);
			}
		}
		return $this->__store($updateNulls);
		//return parent::store($updateNulls);
	}

	/**
	 * This is copied from JTable Library to adapt with Joomla 3 environment
	 * Modified to remove a check that would block non-numeric keys to be saved properly
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/store
	 * @since   11.1
	 */
	private function __store($updateNulls = false)
	{
		$k = $this->_tbl_key;
		if (!empty($this->asset_id))
		{
			$currentAssetId = $this->asset_id;
		}

		// The asset id field is managed privately by this class.
		if ($this->_trackAssets)
		{
			unset($this->asset_id);
		}

		// If a primary key exists update the object, otherwise insert it.
		if ($this->$k)
		{
			$this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
		}
		else
		{
			$this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
		}

		// If the table is not set to track assets return true.
		if (!$this->_trackAssets)
		{
			return true;
		}

		if ($this->_locked)
		{
			$this->_unlock();
		}

		/*
		 * Asset Tracking
		 */

		$parentId = $this->_getAssetParentId();
		$name = $this->_getAssetName();
		$title = $this->_getAssetTitle();

		$asset = self::getInstance('Asset', 'JTable', array('dbo' => $this->getDbo()));
		$asset->loadByName($name);

		// Re-inject the asset id.
		$this->asset_id = $asset->id;

		// Check for an error.
		$error = $asset->getError();
		if ($error)
		{
			$this->setError($error);
			return false;
		}

		// Specify how a new or moved node asset is inserted into the tree.
		if (empty($this->asset_id) || $asset->parent_id != $parentId)
		{
			$asset->setLocation($parentId, 'last-child');
		}

		// Prepare the asset to be stored.
		$asset->parent_id = $parentId;
		$asset->name = $name;
		$asset->title = $title;

		if ($this->_rules instanceof JAccessRules)
		{
			$asset->rules = (string) $this->_rules;
		}

		if (!$asset->check() || !$asset->store($updateNulls))
		{
			$this->setError($asset->getError());
			return false;
		}

		// Create an asset_id or heal one that is corrupted.
		if (empty($this->asset_id) || ($currentAssetId != $this->asset_id && !empty($this->asset_id)))
		{
			// Update the asset_id field in this table.
			$this->asset_id = (int) $asset->id;

			$query = $this->_db->getQuery(true);
			$query->update($this->_db->quoteName($this->_tbl));
			$query->set('asset_id = ' . (int) $this->asset_id);
			$query->where($this->_db->quoteName($k) . ' = ' . (int) $this->$k);
			$this->_db->setQuery($query);

			$this->_db->execute();
		}

		return true;
	}
}
