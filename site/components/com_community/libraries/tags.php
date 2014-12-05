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


class CTags{

	public static function add($obj){

		$data = self::prepareData($obj);
		$tags = self::gethashtags($data->title);
		if(count($tags)>0){
			foreach($tags as $tag){
				$table = JTable::getInstance('Tag', 'CTable');
				$table->bind($data);
				$table->tag = $tag;
				$table->store();
			}
		}

		return;
	}

	public static function gethashtags($str){

		preg_match_all('/(^|[^a-z0-9_])#([a-z0-9_]+)/i', $str, $matchedHashtags);

		$hashtag = array();
	  	// For each hashtag, strip all characters but alpha numeric
	  	if(!empty($matchedHashtags[0])) {
      		foreach($matchedHashtags[0] as $match) {
          		$hashtag[]= preg_replace("/[^a-z0-9]+/i", "", $match);
	      	}
	  	}

	  	return $hashtag;
	}

	public static function prepareData($obj){
		$data = new stdClass();
		//Format Comment data
		if($obj instanceof CTableWall){
			$obj->title = $obj->comment;
			$obj->actor = $obj->post_by;
			$obj->id 	= $obj->contentid;
			$data->element 	= 'comment.'.$obj->type;
		}

		$data->title = $obj->title;
		$data->userid = $obj->actor;
		$data->cid = $obj->id;

		if(!isset($data->element)){
			switch ($obj->app) {
				case 'profile':
						$data->element = 'profile.status';
				break;
				case 'groups.wall':
						$data->element = 'groups.post';
				break;
				case 'events.wall':
						$data->element = 'events.post';
				break;
			}
		}

		return $data;
	}
}


