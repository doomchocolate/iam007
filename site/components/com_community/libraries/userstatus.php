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


class CUserStatus {

	private $creators = null;
	public $target = '';
	private $type = '';

	/**
	 *
	 * @param type $target
	 * @param type $type
	 * @param type $creators
	 */
	public function __construct($target='', $type='profile' , $creators = null)
	{
		$my = CFactory::getUser();
		$this->type = $type;
		$this->target  = (empty($target)) ? $my->id : $target;

		if(is_array($creators))
		{
			foreach($creators as $row)
			{
				$this->addCreator($row);
			}
		}
	}

	public function addCreator($creator)
	{
		$this->creators[] =  $creator;

		return $creator;
	}

	public function render()
	{
		$my		= CFactory::getUser();
		$albumModel = CFactory::getModel('Photos');

		if($this->type == 'groups'){
			$album = $albumModel->getGroupAlbums($this->target);
		}
		else {
			$album = $albumModel->getProfileAlbums($my->id,true,true);
		}
		$this->permission = new stdClass();
		$this->permission->enablephotos			= CFactory::getConfig()->get("enablephotos");
		$this->permission->enablevideos			= CFactory::getConfig()->get("enablevideos");
		$this->permission->enablevideosupload	= CFactory::getConfig()->get("enablevideosupload");
		$this->permission->enableevents			= (CFactory::getConfig()->get("enableevents") && $my->canCreateEvents());

		if($this->type == 'profile' && $this->target != $my->id){

			$this->permission->enablephotos			= false;
			$this->permission->enablevideos			= false;
			$this->permission->enablevideosupload	= false;
			$this->permission->enableevents			= false;

		}

		if ($my->id && is_array($this->creators)) {

			$tmpl = new CTemplate();
			$html = $tmpl	->set('my', $my)
						->set('target', $this->target)
						->set('type', $this->type)
						->set('creators', $this->creators)
						->set('album',$album)
						->set('permission',$this->permission)
						->fetch('status.form');

			// Some of the creator might need custom url replacement
			// Take a look at status.photo.php template for example
			$group_url = ($this->type == 'groups') ? CRoute::_('index.php?option=com_community&view=photos&task=ajaxPreview&no_html=1&tmpl=component&groupid='.$this->target) : CRoute::_('index.php?option=com_community&view=photos&task=ajaxPreview&no_html=1&tmpl=component');
			$html = str_replace('{url}', $group_url, $html);
			echo $html;
		}
	}
}

class CUserStatusCreator {

	public $type='';
	public $class='';
	public $title='';
	public $html='';

	public function __construct($type=null)
	{
		$this->type = $type;
		$this->class = 'type-' . $type;
	}

	static function getPhotoInstance($groupid=null)
	{
		$template	=   new CTemplate();
		$creator        = new CUserStatusCreator('photo');
		$creator->title = JText::_('COM_COMMUNITY_PHOTOS');
		$template->set('groupid', $groupid);
		$creator->html  = $template->fetch('status.photo');
		return $creator;
	}

	static function getVideoInstance()
	{
		$template	=   new CTemplate();
		$creator        = new CUserStatusCreator('video');
			$creator->title = JText::_('COM_COMMUNITY_VIDEOS');
			$creator->html  = $template->fetch('status.video');
		return $creator;
	}


	static function getMessageInstance()
	{
		$template	=   new CTemplate();
		$creator        =   new CUserStatusCreator('message');
		$creator->title =   JText::_('COM_COMMUNITY_MESSAGE');
		$creator->html  =   $template->fetch('status.message');
		return $creator;
	}

        static function getEventInstance()
        {
            $template	=   new CTemplate();

            $my 	= CFactory::getUser();

            //CFactory::load( 'helpers' , 'event' );
            $dateSelection = CEventHelper::getDateSelection();

            $model		= CFactory::getModel( 'events' );
            $categories	= $model->getCategories();

            // Load category tree

            $cTree	= CCategoryHelper::getCategories($categories);
            $lists['categoryid']	=   CCategoryHelper::getSelectList( 'events', $cTree );

            $template->set( 'startDate'       , $dateSelection->startDate );
            $template->set( 'endDate'         , $dateSelection->endDate );
            $template->set( 'startHourSelect' , $dateSelection->startHour );
            $template->set( 'endHourSelect'   , $dateSelection->endHour );
            $template->set( 'startMinSelect'  , $dateSelection->startMin );
            $template->set( 'endMinSelect'    , $dateSelection->endMin );
            $template->set( 'startAmPmSelect' , $dateSelection->startAmPm );
            $template->set( 'endAmPmSelect'   , $dateSelection->endAmPm );
            $template->set( 'repeatEnd'       , $dateSelection->endDate );
            $template->set( 'enableRepeat'    , $my->authorise('community.view', 'events.repeat'));
            $template->set( 'lists'           , $lists );


            $creator  = new CUserStatusCreator('event');
            $creator->title = JText::_('COM_COMMUNITY_EVENTS');
            $creator->html  = $template->fetch('status.event');

            return $creator;
        }

}


?>