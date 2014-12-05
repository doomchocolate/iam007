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

require_once( JPATH_ROOT .'/components/com_community/libraries/core.php' );
class CFeatured
{
	var $stack	= null;
	var $type	= null;

	public function __construct( $type )
	{
		// Initialize stack type so that it can be used.
		$this->type				= $type;
		$this->_load();
	}

	public function _load()
	{
		$config	= CFactory::getConfig();
		$limit	= $config->get( 'featured' . $this->type . 'limit' , 10 );
		$db		= JFactory::getDBO();
		$query	= 'SELECT * FROM ' . $db->quoteName('#__community_featured')
				. ' WHERE ' . $db->quoteName('type') .'=' . $db->Quote($this->type)
				. ' ORDER BY ' . $db->quoteName('id') .' DESC'
				. ' LIMIT 0,' . $limit;

		$db->setQuery($query);

		$this->stack	= $db->loadObjectList();
	}

	public function delete($cid)
	{
		$db		= JFactory::getDBO();

		$query	= 'DELETE FROM ' . $db->quoteName('#__community_activities')
				. ' WHERE ' . $db->quoteName('cid') . '=' . $db->Quote($cid)
				. ' AND ' . $db->quoteName('app') . '=' . $db->Quote($this->type.'.featured');

		$db->setQuery($query);
		$db->query();

		// Remove from DB
		$query	= 'DELETE FROM ' . $db->quoteName('#__community_featured')
				. ' WHERE ' . $db->quoteName('cid') .'=' . $db->Quote( $cid )
				. ' AND ' . $db->quoteName('type') .'=' . $db->Quote($this->type);

		$db->setQuery($query);
		return $db->query();
	}


	/**
	 * [add description]
	 * @param [type] $cid       [description]
	 * @param [type] $createdBy [description]
	 */
	public function add( $cid , $createdBy )
	{
		$config			= CFactory::getConfig();
		$limit			= $config->get( 'featured' . $this->type . 'limit' , 10 );
		$count			= count($this->stack);

		// CFactory::load( 'models' , 'featured' );
		// Once limit is reached, shift first element off the stack.
		if( $count >= $limit )
		{
 			//$removed	= array_pop( $this->stack );

			// We need to remove it from the database.
			//$table		= JTable::getInstance('Featured','CTable');
			//$table->load( $removed->id );
			//$table->delete();
                        return false;
		}

		// Add the latest featured into the stack.
		$table				= JTable::getInstance( 'Featured' , 'CTable' );
		$table->cid			= $cid;
		$table->type		= $this->type;
		$table->created_by	= $createdBy;
		$table->created		= JFactory::getDate()->toSql();
		$table->store();

		$data				= new stdClass();
		$data->id			= $table->id;
		$data->cid			= $cid;
		$data->created_by	= $createdBy;
		$data->type			= $this->type;
		$data->created		= JFactory::getDate()->toSql();

		array_unshift($this->stack, $data );

		// Log into Activity Stream
		$this->_addToActivityStream($cid);

		return true;
	}

	public function getItems()
	{
		return $this->stack;
	}

	/**
	 *	Check if an item is featured or not.
	 *
	 **/
	public function isFeatured( $cid )
	{
		$ids	= $this->getItemIds();

		return in_array( $cid , $ids );
	}

	/**
	 *	Returns a list of unique ids from item
	 *
	 **/
	public function getItemIds()
	{
		$id	= array();

		if($this->stack )
		{
			foreach( $this->stack as $item )
			{
				$id[]	= $item->cid;
			}
		}
		return $id;
	}

	/*
	 *	Add featured content into activity streams
	 */
	public function _addToActivityStream($cid = 0)
	{
		// $cid shouldn't be 0
		if ($cid == 0) return;

		// Construct activity stream
		$act			= new stdClass();
		$act->cid		= $cid;
		$act->target	= 0;
		$act->app		= $this->type . '.featured';
		$act->cmd		= $this->type . '.featured';

		$params = new JRegistry('');


		// Process each type of featured content
		switch ($this->type)
		{
            case FEATURED_EVENTS:
//
                $table			= JTable::getInstance('Event', 'CTable');
                $table->load($cid);

                $act->actor		= $table->creator;
                $eventUrl		= 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $table->id;
                $act->title		= JText::sprintf('COM_COMMUNITY_ACTIVITIES_FEATURED_EVENT', $eventUrl, $table->title);
                $act->content	= '<img src=\"' . $table->getAvatar() . '\" style=\"border: 1px solid #eee;margin-right: 3px;\" />';
                $act->comment_type 	= $act->app;
                $act->like_id = CActivities::LIKE_SELF;
                $act->like_type = $act->app;

                break;
			case FEATURED_GROUPS:
//
 				$table			= JTable::getInstance('Group', 'CTable');
 				$table->load($cid);

 				$act->actor		= $table->ownerid;
 				$groupUrl		= 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $table->id;
 				$act->title		= JText::sprintf('COM_COMMUNITY_ACTIVITIES_FEATURED_GROUP', $groupUrl, $table->name);
 				$act->content	= '<img src=\"' . $table->getAvatar() . '\" style=\"border: 1px solid #eee;margin-right: 3px;\" />';
 				$act->comment_type 	= $act->app;
                $act->like_id = CActivities::LIKE_SELF;
                $act->like_type = $act->app;

				break;
			case FEATURED_USERS:
				$user				= CFactory::getUser($cid);
				$ownerUrl			= 'index.php?option=com_community&view=profile&userid=' . $user->id;
				$act->actor			= $user->id;
				$act->title			= '';//JText::sprintf('COM_COMMUNITY_ACTIVITIES_FEATURED_USER', '{owner_url}', $user->getDisplayName());
				$act->content		= '';
				$act->comment_type 	= $act->app;
                $act->like_id = CActivities::LIKE_SELF;
                $act->like_type = $act->app;

				$params->set('userid', $user->id);
				$params->set('owner_url'	, $ownerUrl );
				break;
			case FEATURED_VIDEOS:

				$table			= JTable::getInstance('Video', 'CTable');
				$table->load($cid);
				$videoUrl		= $table->getViewURI();
				$ownerUrl		= 'index.php?option=com_community&view=profile&userid=' . $table->creator;
				$user			= CFactory::getUser($table->creator);
				$ownerName		= $user->getDisplayName();
				$act->actor		= $table->creator;
				$act->title		= '';//JText::sprintf('COM_COMMUNITY_ACTIVITIES_FEATURED_VIDEO', '{owner_url}', $ownerName, '{video_url}');
				$config			= CFactory::getConfig();
				$act->comment_type 	= $act->app;
                $act->like_id = CActivities::LIKE_SELF;
                $act->like_type = $act->app;


				$params->set('owner_url'	, $ownerUrl );
				$params->set('video_url'	, $videoUrl );

				// embed the video when click show more
				// now only applies to external video provider

				break;
			case FEATURED_ALBUMS:
				$table			= JTable::getInstance('Album', 'CTable');
				$table->load($cid);
				$albumUrl		= $table->getURI();
				$ownerUrl		= 'index.php?option=com_community&view=profile&userid=' . $table->creator;
				$user			= CFactory::getUser($table->creator);
				$ownerName		= $user->getDisplayName();
				$act->actor		= $table->creator;
				$act->title		= '';//JText::sprintf('COM_COMMUNITY_ACTIVITIES_FEATURED_ALBUM', '{owner_url}', $ownerName, '{album_url}');
				//$table->thumbnail= $table->getCoverThumbPath();
				//$table->thumbnail= ($table->thumbnail) ? JURI::root() . $table->thumbnail : JURI::root() . 'components/com_community/assets/album_thumb.jpg';
				$act->content	=  '';//<img src="' . $table->thumbnail . '" style="border: 1px solid #eee;margin-right: 3px;" />';

				$params->set('owner_url'	, $ownerUrl );
				$params->set('album_url'		, $albumUrl );
				$act->comment_type 	= $act->app;
                $act->like_id = CActivities::LIKE_SELF;
                $act->like_type = $act->app;


				break;
			default:
				// If featured type is unknown, we'll skip it
				return;
		}

		// Add activity logging with 0 points

		CActivityStream::add($act, $params->toString(),0);
	}
}