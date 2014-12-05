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

/**
 * Configuration view for JomSocial
 */
class CommunityViewCommunity extends JViewLegacy
{
	/**
	 * The default method that will display the output of this view which is called by
	 * Joomla
	 *
	 * @param	string template	Template file name
	 **/
	public function display( $tpl = null )
	{

		$lang	= JFactory::getLanguage();
		$lang->load( 'com_community.country',JPATH_ROOT);

		JToolBarHelper::title( JText::_( 'COM_COMMUNITY_DASHBOARD' ), 'configuration');
		$user			= $this->getModel('Users');
		$messages		= $this->getModel('Mailqueue');
		$post			= $this->getModel('Activities');
		$photos			= $this->getModel('Photos');
		$videos			= $this->getModel('Videos');
		$groups			= $this->getModel('Groups');
		$events			= $this->getModel('Events');
		$discussions	= CFactory::getModel('Discussions');

		$this->set('userCount',$user->getMembersCount('jomsocial'));
		$this->set('messageCount',$messages->getTotal());
		$this->set('postCount',$post->getActCount());
		$this->set('photoCount',$photos->getTotal());
		$this->set('videosCount',$videos->getTotal());
		$this->set('groupsCount',$groups->getTotal());
		$this->set('eventsCount',$events->getTotal());
		$this->set('discussionCount',$discussions->getSiteDiscussionCount());
		$this->set('statusData',$this->getActivities());
		$this->set('gender',$user->getGenderInfo());
		$this->set('comments',$this->getUserCommment());
		$this->set('latestMembers',$user->getLatestMembers());
		$this->set('userCountry', $user->getUserCountry());

		// Event calendar
		$this->set('eventcal',$this->getActiveEventData());
		$this->set('userCity',$user->getUserCity());

		$this->set('age',$this->_getUserAverage());

		parent::display( $tpl );
	}

	public function loadLayout()
	{
		parent::display('layout');
	}

	/**
	 * Private method to set the toolbar for this view
	 *
	 * @access private
	 *
	 * @return null
	 **/
	public function setToolBar()
	{
		// Set the titlebar text
		JToolBarHelper::title( JText::_( 'COM_COMMUNITY_JOMSOCIAL' ), 'community' );
	}

	public function addIcon( $image , $url , $text , $newWindow = false )
	{
		$lang		= JFactory::getLanguage();
		$newWindow	= ( $newWindow ) ? ' target="_blank"' : '';
?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a href="<?php echo $url; ?>"<?php echo $newWindow; ?>>
					<?php echo JHTML::_('image', 'administrator/components/com_community/assets/icons/' . $image , NULL, NULL ); ?>
					<span><?php echo $text; ?></span></a>
			</div>
		</div>
<?php
	}

	public function getSideMenuHTML()
	{
		$menus = Array(
			Array(
				'title' => JText::_('COM_COMMUNITY_DASHBOARD'),
				'url' => 'index.php?option=com_community',
				'class' => 'js-icon-dashboard',
				'children' => Array()
			),
			Array(
				'title' => JText::_('COM_COMMUNITY_TITLE_MONITOR'),
				'url' => '#',
				'class' => 'js-icon-desktop',
				'children' => Array(
					Array(
						'title' => JText::_('COM_COMMUNITY_ACTIVITIES'),
						'url' => 'index.php?option=com_community&view=activities'
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_MEMBERS'),
						'url' => 'index.php?option=com_community&view=users',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_REPORTS'),
						'url' => 'index.php?option=com_community&view=reports',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_MAIL_QUEUE'),
						'url' => 'index.php?option=com_community&view=mailqueue',
					)
				)
			),
			Array(
				'title' => JText::_('COM_COMMUNITY_TOOLBAR_CONFIGURATION'),
				'url' => '#',
				'class' => 'js-icon-cogs',
				'children' => Array(
					Array(
						'title' => JText::_('COM_COMMUNITY_CONFIGURATION_SITE_TOOLBAR'),
						'url' => 'index.php?option=com_community&view=configuration&cfgSection=site',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_TITLE_ANTISPAM'),
						'url' => 'index.php?option=com_community&view=configuration&cfgSection=anti-spam',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_CONFIGURATION_LAYOUT_TOOLBAR'),
						'url' => 'index.php?option=com_community&view=configuration&cfgSection=layout',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_CONFIGURATION_PRIVACY'),
						'url' => 'index.php?option=com_community&view=configuration&cfgSection=privacy',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_USERPOINTS'),
						'url' => 'index.php?option=com_community&view=userpoints',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_CONFIGURATION_FACEBOOK_TOOLBAR'),
						'url' => 'index.php?option=com_community&view=configuration&cfgSection=facebook-connect',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_CONFIGURATION_REMOTE_TOOLBAR'),
						'url' => 'index.php?option=com_community&view=configuration&cfgSection=remote-storage',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_CONFIGURATION_INTEGRATIONS_TOOLBAR'),
						'url' => 'index.php?option=com_community&view=configuration&cfgSection=integrations',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_TEMPLATES'),
						'url' => 'index.php?option=com_community&view=templates',
					)
				)
			),
			Array(
				'title' => JText::_('COM_COMMUNITY_TITLE_PROFILES'),
				'url' => '#',
				'class' => 'js-icon-edit',
				'children' => Array(
					Array(
						'title' => JText::_('COM_COMMUNITY_MULTIPLE_PROFILES'),
						'url' => 'index.php?option=com_community&view=multiprofile',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_CUSTOM_PROFILES'),
						'url' => 'index.php?option=com_community&view=profiles',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_CONFIGURATION_KARMA'),
						'url' => 'index.php?option=com_community&view=configuration&cfgSection=karma',
					)
				)
			),
			Array(
				'title' => JText::_('COM_COMMUNITY_GROUPS'),
				'url' => '#',
				'class' => 'js-icon-group',
				'children' => Array(
					Array(
						'title' => JText::_('COM_COMMUNITY_TITLE_GROUP_VIEW'),
						'url' => 'index.php?option=com_community&view=groups',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_GROUP_CATEGORIES'),
						'url' => 'index.php?option=com_community&view=groupcategories',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_TITLE_GROUP_SETTING'),
						'url' => 'index.php?option=com_community&view=configuration&cfgSection=group',
					)
				)
			),
			Array(
				'title' => JText::_('COM_COMMUNITY_EVENTS'),
				'url' => '#',
				'class' => 'js-icon-calendar',
				'children' => Array(
					Array(
						'title' => JText::_('COM_COMMUNITY_TITLE_EVENT_VIEW'),
						'url' => 'index.php?option=com_community&view=events',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_EVENT_CATEGORIES'),
						'url' => 'index.php?option=com_community&view=eventcategories',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_TITLE_EVENT_SETTING'),
						'url' => 'index.php?option=com_community&view=configuration&cfgSection=event',
					)
				)
			),
			Array(
				'title' => JText::_('COM_COMMUNITY_PHOTOS'),
				'url' => '#',
				'class' => 'js-icon-camera',
				'children' => Array(
					Array(
						'title' => JText::_('COM_COMMUNITY_TITLE_PHOTO_VIEW'),
						'url' => 'index.php?option=com_community&view=photos',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_TITLE_PHOTO_SETTING'),
						'url' => 'index.php?option=com_community&view=configuration&cfgSection=photo',
					)
				)
			),
			Array(
				'title' => JText::_('COM_COMMUNITY_VIDEOS'),
				'url' => '#',
				'class' => 'js-icon-film',
				'children' => Array(
					Array(
						'title' => JText::_('COM_COMMUNITY_TITLE_VIDEO_VIEW'),
						'url' => 'index.php?option=com_community&view=videos',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_TOOLBAR_VIDEO_CATEGORIES'),
						'url' => 'index.php?option=com_community&view=videoscategories',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_TITLE_VIDEO_SETTING'),
						'url' => 'index.php?option=com_community&view=configuration&cfgSection=video',
					)
				)
			),
			Array(
				'title' => JText::_('COM_COMMUNITY_APPLICATIONS'),
				'url' => 'index.php?option=com_community&view=applications',
				'class' => 'js-icon-briefcase',
				'children' => Array()
			),
			Array(
				'title' => JText::_('COM_COMMUNITY_TITLE_TOOL'),
				'url' => '#',
				'class' => 'js-icon-bolt',
				'children' => Array(
					Array(
						'title' => JText::_('COM_COMMUNITY_MESSAGING_MASS'),
						'url' => 'index.php?option=com_community&view=messaging',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_UPDATE'),
						'url' => 'index.php?option=com_community&view=update',
					)
				)
			),
			Array(
				'title' => JText::_('COM_COMMUNITY_HELP'),
				'url' => '#',
				'class' => 'js-icon-info-sign',
				'children' => Array(
					Array(
						'title' => JText::_('COM_COMMUNITY_DOC'),
						'url' => 'http://documentation.jomsocial.com',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_TITLE_ADDONS'),
						'url' => 'http://www.jomsocial.com/addons',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_ABOUT'),
						'url' => 'index.php?option=com_community&view=about',
					),
					Array(
						'title' => JText::_('COM_COMMUNITY_TITLE_SUPPORT'),
						'url' => 'http://www.jomsocial.com/support',
					)
				)
			)
		);

		$view = JRequest::getcmd('view');
		$cfgSection = JRequest::getcmd('cfgSection','');
		$cfgSection = (!empty($cfgSection)) ? '&cfgSection='.$cfgSection : '';

		$html = '<ul class="nav nav-list">' . PHP_EOL;
		$helpArray = array(JText::_('COM_COMMUNITY_DOC')=>1,JText::_('COM_COMMUNITY_TITLE_ADDONS')=>1,JText::_('COM_COMMUNITY_TITLE_SUPPORT')=>1,JText::_('COM_COMMUNITY_ABOUT')=>0);

		foreach ($menus as $menu)
		{
			$hasChildren = ! empty($menu['children']);
			$dropdownToggleClass = ($hasChildren) ? 'dropdown-toggle' : '';
			$isOpen = false;
			$current = '';

			if($hasChildren) {
				foreach ($menu['children'] as $child) {
					if($child['url'] == 'index.php?option=com_community&view=' . $view.$cfgSection ) {
						$isOpen = true;
						$current = $child['url'];
						break;
					}
				}
			} else if($menu['url'] == 'index.php?option=com_community&view=' . $view ) {
				$isOpen = true;
			}

			$openClass = ($isOpen) ? 'open' : '';
			$openSubStyle = ($isOpen) ? 'display: block;' : '';

			$html .= '<li class="' . $openClass . '"><a href="' . JRoute::_($menu['url']) . '" class="' . $dropdownToggleClass . '">';
			$html .= '<i class="' . $menu['class'] . '"></i> <span class="menu-text"> ' . $menu['title'] .' </span>';

			if($hasChildren) {
				$html .= '<b class="arrow js-icon-angle-down"></b>';
			}

			$html .= '</a>';

			if ($hasChildren) {
				$html .= PHP_EOL . '<ul class="submenu" style="' . $openSubStyle . '">' . PHP_EOL;
				foreach ($menu['children'] as $child) {
					$target = '';
					if($menu['title'] == 'Help' && $helpArray[$child['title']])
					{
						$target='target="_blank"';
					}
					$submenuStyle = ($current == $child['url']) ? 'class="active"' : '';
					$html .= '<li '.$submenuStyle.'><a '.$target.' href="' . JRoute::_($child['url']) . '">';
					$html .= '<i class="js-icon-double-angle-right"></i> <span class="menu-text"> ' . $child['title'] .' </span>';
					$html .= '</a></li>';
				}
				$html .= '</ul>' . PHP_EOL;
			}
			$html .= '</li>'. PHP_EOL;
		}
		$html .= '</ul>' . PHP_EOL;

		return $html;
	}

	public function getActivities()
	{
		$Activities = $this->getModel('Activities');

		$data = $Activities->getActivities();

		$data = array_slice($data,0,5);
		$data = CAdminactivity::getTitle($data);

		foreach($data as $key=>$_data)
		{
			$data[$key]->user = CFactory::getUser($_data->actor);
		}

		return $data;
	}

	public function getUserCommment()
	{
		$wall	= CFactory::getModel('Wall');
		$datas = $wall->getPostList();

		foreach($datas as $key=>$data )
		{
			$datas[$key]->user = CFactory::getUser($data->post_by);
		}
		return $datas;
	}

	public function getLabelCss($status)
	{
		$css = array('approved'=>'label-success arrowed-in','blocked'=>'label-important','pending'=>'label-warning');

		return $css[$status];
	}

	public function jsonFormat($object, $time = 'week', $label = '')
	{
		$count = 0;
		$now = new JDate();
		$arrayData = array();
		$dateFormat = 'd/m';

		switch ($time) {
			case 'week':
				$startDate = new JDate(strtotime('this week', time()));
				$endDate = new JDate(strtotime('+1 week',strtotime($startDate->format('Y-m-d'))));
				break;
			case 'lastweek':
				$startDate = $now->modify('-1 week');
				$endDate = new JDate(strtotime('+1 week',strtotime($startDate->format('Y-m-d'))));
				break;
			case 'month':
				$startDate = $now->modify('first day of this month');
				$endDate = new JDate(strtotime('+1 month',strtotime($startDate->format('Y-m-d'))));
				$dateFormat = 'd';
				break;
			case 'lastmonth':
				$startDate = $now->modify('first day of last month');
				$endDate = new JDate(strtotime('+1 month',strtotime($startDate->format('Y-m-d'))));
				$dateFormat = 'd';
				break;
		}

		$interval = $startDate->diff($endDate);
		$intervalDays = $interval->days + 1;

		for($i=0; $i<=$intervalDays; $i ++)
		{
			if(count($object)) {
				foreach($object as $key => $data)
				{
					$date = new JDate($data->created);

					if($startDate->format('Y-m-d') === $date->format('Y-m-d') && $data->count > 0)
					{
						$arrayData[$i] = '[\'' . $date->format($dateFormat) . '\',' . $data->count . ']';
						$count += $data->count;
					}
					else if(empty($arrayData[$i]))
					{
						$arrayData[$i] = '[\''.$startDate->format($dateFormat).'\',0]';
					}
				}
			} else {
				$arrayData[$i] = '[\''.$startDate->format($dateFormat).'\',0]';
			}

			$startDate = $startDate->modify('+1 day');
		}

		$string = '['.implode(',', $arrayData).']';

		$obj = new stdClass();
		$obj->json = $string;
		$obj->count = $count;
		$obj->label = $label;

		return $obj;
	}

	public function getActiveEventData()
	{
		$events	= $this->getModel('Events');

		$result = $events->getActiveEvent();

		$arrayData = array();
		foreach($result as $data)
		{
			$date = new JDate($data->startdate);
			$endDate = new JDate($data->enddate);
			$string = '{';
			$string .= 'title:\''.JHtml::_('string.truncate',$this->escape(addslashes($data->title),300)).'\'';
			$string .= ',start:\''.$date->format('c').'\'';
			$string .= ($data->allday) ? ',allDay:true' : ',end:\''.$endDate->format('c').'\'';
			$string .= '}';
			$arrayData[] = $string;
		}
		$jsonString = '['.implode(',',$arrayData).']';
		return $jsonString;


	}

	private function _getUserAverage()
	{
		$user			= $this->getModel('Users');

		$users = $user->getUserGenderList();
		$users2 = $user->getUserBirthDateList();

		$data = array();

		foreach($users as $user)
		{
			$data[$user->user_id]['Gender'] = JText::_($user->value);
		}

		foreach($users2 as $_user)
		{
			$dateCheck = date_parse($_user->value);

			if($dateCheck['error_count'] == 0)
			{
				$date = new JDate($_user->value);
				$data[$_user->user_id]['Age'] = floor((time()-$date->toUnix())/31556926);
			}
		}

		$avg = 0;
		$mAvg = 0;
		$fAvg = 0;
		$count = 0;
		$Mcount = 0;
		$Fcount = 0;

		foreach($data as $_data)
		{
			if(isset($_data['Gender']) && ucfirst ($_data['Gender'])==JText::_('COM_COMMUNITY_MALE') && isset($_data['Age']) && $_data['Age'] != 0)
			{
				$mAvg +=$_data['Age'];
				$Mcount +=1;
			}

			if(isset($_data['Gender']) && ucfirst ($_data['Gender'])==JText::_('COM_COMMUNITY_FEMALE') && isset($_data['Age'] ) && $_data['Age'] != 0)
			{
				$fAvg +=$_data['Age'];
				$Fcount +=1;
			}

			if(isset($_data['Age']) && $_data['Age'] != 0)
			{
				$avg +=$_data['Age'];

				$count +=1;

			}
		}

		$result = new stdClass();

		$result->average = ($count == 0) ? 0 : floor($avg/$count);
		$result->MaleAverage = ($Mcount == 0 ) ? 0 : floor($mAvg/$Mcount);
		$result->FemaleAverage = ($Fcount == 0) ? 0 : floor($fAvg/$Fcount);

		return $result;
	}

	public function getEngagementJs($type, $time)
	{
		$actionsArr = array(
				'message' => array('like' => 'profile.status.like', 'comment' => 'profile.comment', 'share' => 'message.share'),
				'photo' => array('like' => 'photo.like', 'comment' => 'photo.comment', 'share' => 'photo.share'),
				'video' => array('like' => 'videos.like', 'comment' => 'video.comment', 'share' => 'video.share'),
				'event' => array('like' => 'events.like', 'comment' => 'event.comment', 'share' => 'event.share'),
				'group' => array('like' => 'groups.like', 'comment' => 'group.comment', 'share' => 'group.share')
			);

		$like = $this->jsonFormat(CEngagement::getData(array($actionsArr[$type]['like']), $time), $time, JText::_('COM_COMMUNITY_ENGAGEMENT_LIKES'));
		$comment = $this->jsonFormat(CEngagement::getData(array($actionsArr[$type]['comment']), $time), $time, JText::_('COM_COMMUNITY_ENGAGEMENT_COMMENTS'));
		$share = $this->jsonFormat(CEngagement::getData(array($actionsArr[$type]['share']), $time), $time, ($type == 'message') ? JText::_('COM_COMMUNITY_ENGAGEMENT_STATUSES') : JText::_('COM_COMMUNITY_ENGAGEMENT_SHARES'));

		$js = <<<ENDSCRIPT
var engDatasets = {
	"{$like->label}": {
	  label: "{$like->count} {$like->label}",
	  data: {$like->json}
	},
	"{$comment->label}": {
	  label: "{$comment->count} {$comment->label}",
	  data: {$comment->json}
	},
	"{$share->label}": {
	  label: "{$share->count} {$share->label}",
	  data: {$share->json}
	},
};

var i = 0;
$.each(engDatasets, function(key, val) {
  val.color = i;
  ++i;
});

var engChoiceContainer = jQuery("#eng-{$type}-choices");
$.each(engDatasets, function(key, val) {
  engChoiceContainer.append("<label><input type='checkbox' name='" + key +
	"' checked='checked' id='id" + key + "'></input>" +
	"<span class='lbl' for='id" + key + "'>"
	+ key + "</span></label>");
});

engPlotAccordingToChoices = function() {
	var checkedChoices = engChoiceContainer.find("input:checked");
	if(checkedChoices.length == 1) {
		checkedChoices.attr("disabled", true);
	} else {
		checkedChoices.removeAttr("disabled");
	}

	var data = [];

	engChoiceContainer.find("input:checked").each(function () {
		var key = jQuery(this).attr("name");
		if (key && engDatasets[key]) {
		  data.push(engDatasets[key]);
		}
		});

		if (data.length > 0) {
		$.plot("#eng-{$type}-plot", data, {
		  yaxis: {
			min: 0
		  },
		  xaxis: {
			tickDecimals: 0,
			mode: 'categories'
		  }
		});
	}
};

engChoiceContainer.find("input").click(window.engPlotAccordingToChoices);

engPlotAccordingToChoices();
ENDSCRIPT;

		return $js;
	}

	public function getStatisticJs($time)
	{
		// Data statistic
		$groupModel = $this->getModel('Groups');
		$eventModel = $this->getModel('Events');
		$photoModel = $this->getModel('Photos');
		$videoModel = JModelLegacy::getInstance('Videos', 'CommunityAdminModel'); // @TODO: Make this work with getModel()

		$group = $this->jsonFormat($groupModel->getGroupsbyInterval(), $time,JText::_('COM_COMMUNITY_GROUPS'));
		$event = $this->jsonFormat($eventModel->getEventsbyInterval(), $time,JText::_('COM_COMMUNITY_EVENTS'));
		$photo = $this->jsonFormat($photoModel->getPhotosbyInterval(), $time,JText::_('COM_COMMUNITY_PHOTOS'));
		$video = $this->jsonFormat($videoModel->getVideosbyInterval(), $time,JText::_('COM_COMMUNITY_VIDEOS'));

		$js = <<<ENDSCRIPT
var datasets2 = {
  "{$group->label}": {
	label: "{$group->count} {$group->label}",
	data: {$group->json}
  },
  "{$photo->label}": {
	label: "{$photo->count} {$photo->label}",
	data: {$photo->json}
  },
  "{$video->label}": {
	label: "{$video->count} {$video->label}",
	data: {$video->json}
  },
  "{$event->label}": {
	label: "{$event->count} {$event->label}",
	data: {$event->json}
  }
};

var i = 0;
$.each(datasets2, function(key, val) {
  val.color = i;
  ++i;
});

var choiceContainer = jQuery("#choices-2");
$.each(datasets2, function(key, val) {
  choiceContainer.append("<label><input type='checkbox' name='" + key +
	"' checked='checked' id='id" + key + "'></input>" +
	"<span class='lbl' for='id" + key + "'>"
	+ key + "</span></label>");
});

choiceContainer.find("input").click(plotAccordingToChoices);

function plotAccordingToChoices() {

var checkedChoices = choiceContainer.find("input:checked");
if(checkedChoices.length == 1) {
	checkedChoices.attr("disabled", true);
} else {
	checkedChoices.removeAttr("disabled");
}

  var data = [];

  choiceContainer.find("input:checked").each(function () {
	var key = jQuery(this).attr("name");
	if (key && datasets2[key]) {
	  data.push(datasets2[key]);
	}
  });

  if (data.length > 0) {
	$.plot("#placeholder-2", data, {
	  yaxis: {
		min: 0
	  },
	  xaxis: {
		tickDecimals: 0,
		mode: 'categories'
	  }
	});
  }
}

plotAccordingToChoices();
ENDSCRIPT;

		return $js;
	}
}