<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_ROOT .'/components/com_community/libraries/core.php';
require_once JPATH_ROOT .'/components/com_community/libraries/template.php';

class CWall
{
	static public function _processWallContent($comment)
	{
		//Generate Link for comment
		$comment = CStringHelper::autoLink($comment);
		// Convert video link to embedded video
		$comment = CVideosHelper::getVideoLink($comment);

		return $comment;
	}

	/**
	 * Method to get the walls HTML form
	 *
	 * @param    userId
	 * @param    uniqueId
	 * @param    appType
	 * @param    $ajaxFunction    Optional ajax function
	 **/
	static public function getWallInputForm($uniqueId, $ajaxAddFunction, $ajaxRemoveFunc, $viewAllLink = '')
	{
		$my = CFactory::getUser();

		// Hide the input form completely from visitors
		if ($my->id == 0)
			return '';

		$tmpl = new CTemplate();

		return $tmpl->set('uniqueId', $uniqueId)
			->set('viewAllLink', $viewAllLink)
			->set('ajaxAddFunction', $ajaxAddFunction)
			->set('ajaxRemoveFunc', $ajaxRemoveFunc)
			->fetch('wall.form');
	}

	static public  function saveWall($uniqueId, $message, $appType, &$creator, $isOwner, $processFunc = '', $templateFile = 'wall.content', $wallId = 0)
	{
		$my = CFactory::getUser();

		// Add some required parameters, otherwise assert here
		CError::assert($uniqueId, '', '!empty', __FILE__, __LINE__);
		CError::assert($appType, '', '!empty', __FILE__, __LINE__);
		CError::assert($message, '', '!empty', __FILE__, __LINE__);
		CError::assert($my->id, '', '!empty', __FILE__, __LINE__);

		// Load the models

		$wall = JTable::getInstance('Wall', 'CTable');
		$wall->load($wallId);

		if ($wallId == 0) {
			// Get current date
			$now = JFactory::getDate();
			$now = $now->toSql();

			// Set the wall properties
			$wall->type = $appType;
			$wall->contentid = $uniqueId;
			$wall->post_by = $creator->id;

			$wall->date = $now;
			$wall->published = 1;

			// @todo: set the ip address
			$wall->ip = $_SERVER['REMOTE_ADDR'];
		}
		$wall->comment = $message;

		// Store the wall message
		$wall->store();

		// Convert it to array so that the walls can be processed by plugins
		$args = array();
		$args[0] =  $wall;

		//Process wall comments
		$comment = new CComment();
		$wallComments = $wall->comment;
		$wall->comment = $comment->stripCommentData($wall->comment);

		// Trigger the wall comments
		CWall::triggerWallComments($args);

		$wallData = new stdClass();

		$wallData->id = $wall->id;
		$wallData->content = CWallLibrary::_getWallHTML($wall, $wallComments, $appType, $isOwner, $processFunc, $templateFile);


		$wallData->content = CStringHelper::replaceThumbnails($wallData->content);
		CTags::add($wall);
		return $wallData;
	}

	/**
	 * @param <type> $act
	 */
	static public  function getActivityContentHTML($act)
	{

		CFactory::getModel('wall');
		$config = CFactory::getConfig();

		$wall = JTable::getInstance('Wall', 'CTable');
		$wall->load($act->cid);

		$comment = new CComment();
		//$wall->comment = $comment->stripCommentData($wall->comment);

		// Trigger the wall applications / plugins
		$walls = array();
		$walls[] = $wall;
		CWall::triggerWallComments($walls);

		$wall->comment = CWallLibrary::_getWallHTML($wall, null, 'profile', true, null, 'wall.content');

		$tmpl = new CTemplate();
		return $tmpl->set('comment', $wall->comment)
			->fetch('activity.wall.post');
	}

	/**
	 * Return html-free summary of the wall content
	 */
	public static function getWallContentSummary($wallId)
	{

		CFactory::getModel('wall');
		$config = CFactory::getConfig();

		$wall = JTable::getInstance('Wall', 'CTable');
		$wall->load($wallId);

		$comment = new CComment();
		$wall->comment = JHTML::_('string.truncate',$comment->stripCommentData($wall->comment), $config->getInt('streamcontentlength'));

		$tmpl = new CTemplate();
		return $tmpl->set('comment', CStringHelper::escape($wall->comment))
			->fetch('activity.wall.post');
	}

	public function canComment($appType, $uniqueId)
	{
		$my = CFactory::getUser();
		$allowed = false;

		switch ($appType) {
			case 'groups':
				$group = JTable::getInstance('Group', 'CTable');
				$group->load($uniqueId);

				$allowed = $group->isMember($my->id);
				break;
			default:
				$allowed = true;
				break;
		}
		return $allowed;
	}

	/**
	 * Fetches the wall content template and returns the wall data in HTML format
	 *
	 * @param    appType            The application type to load the walls from
	 * @param    uniqueId        The unique id for the specific application
	 * @param    isOwner            Boolean value if the current browser is owner of the specific app or profile
	 * @param    limit            The limit to display the walls
	 * @param    templateFile    The template file to use.
	 **/
	static public function getWallContents($appType, $uniqueId, $isOwner, $limit = 0, $limitstart = 0, $templateFile = 'wall.content', $processFunc = '', $param = null, $banned = 0)
	{
		CError::assert($appType, '', '!empty', __FILE__, __LINE__);
		//CError::assert($uniqueId, '', '!empty', __FILE__, __LINE__);

		$config = CFactory::getConfig();

		$html = '<div id="wall-containter" class="cComments">';
		$model = CFactory::getModel('wall');

		//@rule: If limit is not set, then we need to use Joomla's limit
		if ($limit == 0) {
			$app = JFactory::getApplication();
			$limit = $app->getCfg('list_limit');
		}

		// Special 'discussions'
		$order = 'DESC';
		$walls = $model->getPost($appType, $uniqueId, $limit, $limitstart, $order);

		// Special 'discussions'
		$discussionsTrigger = false;
		$order = $config->get('group_discuss_order');
		if (($appType == 'discussions') && ($order == 'ASC')) {
			$walls = array_reverse($walls);
			$discussionsTrigger = true;
		}

		if ($walls) {
			//Process wall comments
			$wallComments = array();
			$comment = new CComment();

			for ($i = 0; $i < count($walls); $i++) {
				// Set comments
				$wall = $walls[$i];
				$wallComments[] = $wall->comment;

				if(CFactory::getUser($wall->post_by)->block) {
					$wall->comment = JText::_('COM_COMMUNITY_CENSORED');
				} else {
					$wall->comment = $comment->stripCommentData($wall->comment);
				}

				// Change '->created to lapse format if stream uses lapse format'
				if ($config->get('activitydateformat') == 'lapse') {
					//$wall->date = CTimeHelper::timeLapse($wall->date);
				}
			}

			// Trigger the wall applications / plugins
			CWall::triggerWallComments($walls);

			for ($i = 0; $i < count($walls); $i++) {
				if ($banned == 1) {
					$html .= CWallLibrary::_getWallHTML($walls[$i], $wallComments[$i], $appType, $isOwner, $processFunc, $templateFile, $banned);
				} else {
					$html .= CWallLibrary::_getWallHTML($walls[$i], $wallComments[$i], $appType, $isOwner, $processFunc, $templateFile);
				}
			}

			if ($appType == 'discussions') {
				$wallCount = CWallLibrary::getWallCount('discussions', $uniqueId);
				$limitStart = $limitstart + $limit;

				if ($wallCount > $limitStart) {
					$groupId = JRequest::getInt('groupid');
					$groupId = empty($groupId) ? $param : $groupId;

					if ($discussionsTrigger) {
						$html = $this->_getOlderWallsHTML($groupId, $uniqueId, $limitStart) . $html;
					} else {
						$html .= $this->_getOlderWallsHTML($groupId, $uniqueId, $limitStart);
					}
				}
			}
		}

		$html .= '</div>';

		return $html;
	}

	public function _getOlderWallsHTML($groupId, $discussionId, $limitStart)
	{
		$config = CFactory::getConfig();
		$order = $config->get('group_discuss_order');
		$buttonText = '';

		$buttonText = ($order == 'ASC') ? JText::_('COM_COMMUNITY_GROUPS_OLDER_WALL') : JText::_('COM_COMMUNITY_MORE');

		ob_start();
		?>
	<div class="joms-newsfeed-more" id="wall-more">
		<a class="more-wall-text" href="javascript:void(0);" onclick="joms.walls.more();"><?php echo $buttonText;?></a>

		<div class="loading"></div>
	</div>
	<input type="hidden" id="wall-groupId" value="<?php echo $groupId;?>"/>
	<input type="hidden" id="wall-discussionId" value="<?php echo $discussionId;?>"/>
	<input type="hidden" id="wall-limitStart" value="<?php echo $limitStart;?>"/>
	<?php
		$moreWalls = ob_get_contents();
		ob_end_clean();

		return $moreWalls;
	}

	static public function _getWallHTML($wall, $wallComments, $appType, $isOwner, $processFunc, $templateFile, $banned = 0)
	{
		$user = CFactory::getUser($wall->post_by);
		$date = CTimeHelper::getDate($wall->date);

		$config = CFactory::getConfig();

		// @rule: for site super administrators we want to allow them to view the remove link
		$isOwner = COwnerHelper::isCommunityAdmin() ? true : $isOwner;
		$isEditable = CWall::isEditable($processFunc, $wall->id);

		$commentsHTML = '';

		$comment = new CComment();
		// If the wall post is a user wall post (in profile pages), we
		// add wall comment feature
		if ($appType == 'user' || $appType == 'groups' || $appType == 'events') {
			if ($banned == 1) {
				$commentsHTML = $comment->getHTML($wallComments, 'wall-cmt-' . $wall->id, false);
			} else {
				$commentsHTML = $comment->getHTML($wallComments, 'wall-cmt-' . $wall->id, CWall::canComment($wall->type, $wall->contentid));
			}
		}

		$avatarHTML = CUserHelper::getThumb($wall->post_by, 'avatar');

		// @rule: We only allow editing of wall in 15 minutes
		$now = JFactory::getDate();
		$interval = CTimeHelper::timeIntervalDifference($wall->date, $now->toSql());
		$interval = COMMUNITY_WALLS_EDIT_INTERVAL - abs($interval);
		$editInterval = round($interval / 60);

		// Change '->created to lapse format if stream uses lapse format'
		if ($config->get('activitydateformat') == 'lapse') {
			$wall->created = CTimeHelper::timeLapse($date);
		} else {
			$wall->created = $date->Format(JText::_('DATE_FORMAT_LC2'));
		}

		// Create new instance of the template
		$tmpl = new CTemplate();
		return $tmpl->set('id', $wall->id)
			->set('author', $user->getDisplayName())
			->set('avatarHTML', $avatarHTML)
			->set('authorLink', CUrlHelper::userLink($user->id))
			->set('created', $wall->created)
			->set('content', $wall->comment)
			->set('commentsHTML', $commentsHTML)
			->set('avatar', $user->getThumbAvatar())
			->set('isMine', $isOwner)
			->set('isEditable', $isEditable)
			->set('editInterval', $editInterval)
			->set('processFunc', $processFunc)
			->set('config', $config)
			->fetch($templateFile);
	}

	static public function getViewAllLinkHTML($link, $count = null)
	{
		if (!$link) return '';

		$tmpl = new CTemplate();
		return $tmpl->set('viewAllLink', $link)
			->set('count', $count)
			->fetch('wall.misc');
	}

	static public function getWallCount($appType, $uniqueId)
	{
		$model = CFactory::getModel('wall');
		$count = $model->getCount($uniqueId, $appType);
		return $count;
	}

	/**
	 * @todo: change this to a simple $my->authorise
	 * @param type $processFunc
	 * @param type $wallId
	 * @return type
	 */
	static public function isEditable($processFunc, $wallId)
	{
		$func = explode(',', $processFunc);

		if (count($func) < 2) {
			return false;
		}

		$controller = $func[0];
		$method = 'edit' . $func[1] . 'Wall';

		if (count($func) > 2) {
			//@todo: plugins
		}

		return CWall::_callFunction($controller, $method, array($wallId));
	}

	public function _checkWallFunc($processFunc)
	{
	}

	static public function _callFunction($controller, $method, $arguments)
	{
		require_once(JPATH_ROOT .'/components/com_community/controllers/controller.php');
		require_once(JPATH_ROOT .'/components/com_community/controllers' .'/'. JString::strtolower($controller) . '.php');

		$controller = JString::ucfirst($controller);
		$controller = 'Community' . $controller . 'Controller';
		$controller = new $controller();

		// @rule: If method not exists, we need to do some assertion here.
		if (!method_exists($controller, $method)) {
			JError::raiseError(500, JText::_('Method not found'));
		}

		return call_user_func_array(array($controller, $method), $arguments);
	}

	public function addWallComment($type, $cid, $comment)
	{
		$my = CFactory::getUser();
		$table = JTable::getInstance('CTable', 'Wall');

		$table->contentid = $cid;
		$table->type = $type;
		$table->comment = $comment;
		$table->post_by = $my->id;

		$table->store();
		return $table->id;
	}

	/**
	 * Formats the comment in the rows
	 *
	 * @param Array    An array of wall objects
	 **/
	static public function triggerWallComments(&$rows)
	{
		CError::assert($rows, 'array', 'istype', __FILE__, __LINE__);

		require_once(COMMUNITY_COM_PATH .'/libraries/apps.php');
		$appsLib = CAppPlugins::getInstance();
		$appsLib->loadApplications();

		for ($i = 0; $i < count($rows); $i++) {
			if (isset($rows[$i]->comment) && (!empty($rows[$i]->comment))) {
				$args = array();
				$args[] =  $rows[$i];

				$appsLib->triggerEvent('onWallDisplay', $args);
			}
		}
		return true;
	}

	/**
	 * Return formatted comment given the wall item
	 */
	public static function formatComment($wall)
	{
		$config		= CFactory::getConfig();
		$my			= CFactory::getUser();
		$actModel	= CFactory::getModel('activities');
		$like 		= new CLike();

		$likeCount = $like->getLikeCount('comment',$wall->id);
		$ifUserLike = $like->userLiked('comment',$wall->id,$my->id);

		// Save processing time
		if (!$wall->comment) return '';

		$user = CFactory::getUser($wall->post_by);

		// Censor if the user is banned
		if($user->block) {
			$wall->comment = JText::_('COM_COMMUNITY_CENSORED');
		} else {
			// strip out the comment data
			$CComment = new CComment();
			$wall->comment = $CComment->stripCommentData($wall->comment);

			// Need to perform basic formatting here
			// 1. support nl to br,
			// 2. auto-link text
			$CTemplate = new CTemplate();
			$wall->comment = $origComment = $CTemplate->escape($wall->comment);
			$wall->comment = CStringHelper::autoLink($wall->comment);
		}

		$commentsHTML = '';
		$commentsHTML .= '<div class="cComment wall-coc-item" id="wall-' . $wall->id . '"><a href="' . CUrlHelper::userLink($user->id) . '"><img src="' . $user->getThumbAvatar() . '" alt="" class="wall-coc-avatar" /></a>';
		$date         = new JDate($wall->date);
		$commentsHTML .= '<a class="wall-coc-author" href="' . CUrlHelper::userLink($user->id) . '">' . $user->getDisplayName() . '</a> ';
		$commentsHTML .= $wall->comment;
		$commentsHTML .= '<span class="wall-coc-time">' . CTimeHelper::timeLapse($date);

		// Only site admin, or wall autho50350r can remove it
		$cid        = isset($wall->contentid) ? $wall->contentid : null;
		$activity   = $actModel->getActivity($cid);

		$ownPost    = ($my->id == $wall->post_by);
		$targetPost = ($activity->target == $my->id);
		$allowRemove = COwnerHelper::isCommunityAdmin() || ( ( $ownPost || $targetPost ) && !empty($my->id) );

		if ($allowRemove)
		{
			$commentsHTML .= ' <span class="wall-coc-remove-link">&#x2022; <a href="#removeComment">' . JText::_('COM_COMMUNITY_WALL_REMOVE') . '</a></span>';
		}

		$commentsHTML .= '</span>';
		$commentsHTML .= '</div>';

		$removeHTML = '';

		if($allowRemove){
			$removeHTML = '<span><a data-action="remove" data-id="'.$wall->id.'" href="javascript:">'. JText::_('COM_COMMUNITY_WALL_REMOVE') .'</a></span>';
		}

		$editHTML='';

		if( $config->get('wallediting') && $ownPost || COwnerHelper::isCommunityAdmin() )
		{
			$editHTML= '<span class="cStream-EditComment"><a data-action="edit" data-id="'.$wall->id.'" href="javascript:">'. JText::_('COM_COMMUNITY_EDIT') .'</a></span>';
		}

		$removeTagHTML = '';

		if(CActivitiesHelper::hasTag($my->id, $wall->comment)){
			$removeTagHTML = '<span><a data-action="remove-tag" data-id="'.$wall->id.'" href="javascript:">'. JText::_('COM_COMMUNITY_WALL_REMOVE_TAG') .'</a></span>';
		}

        /* user deleted */
        if ( $user->guest == 1 ) {
            $userLink = '<span class="cStream-Author">'.   $user->getDisplayName()  . '</span> ';
        }else {
            $userLink = '<a class="cStream-Avatar cStream-Author cFloat-L" href="' . CUrlHelper::userLink($user->id) . '"> <img class="cAvatar" src="' . $user->getThumbAvatar() . '"> </a> ';
        }

        if($ifUserLike != COMMUNITY_LIKE){
        	$likeHTML = '<a data-action="like" data-stream-type="comment" data-stream-id='.$wall->id.' href="#" class="joms-icon-thumbs-up">'.JText::_('COM_COMMUNITY_LIKE').'</a>';
        } else {
        	$likeHTML = '<a data-action="unlike" data-stream-type="comment" data-stream-id='.$wall->id.' href="#" class="joms-icon-thumbs-down">'.JText::_('COM_COMMUNITY_UNLIKE').'</a>';
        }

        //disable likes for guest
        (!$my->id) ? $likeHTML = '' : 1;

        if($likeCount > 0){
        	$likeCountHTML = '<a href="#" data-stream-id="'.$wall->id.'" data-action="showlike"><i class="joms-icon-thumbs-up"></i><span>'.$likeCount.'</span></a>';
        } else {
        	$likeCountHTML='';
        }
        $params = $wall->params;
        $paramsHTML = '';
        $image = (array)$params->get('image');

        if($params->get('title')){

		    $paramsHTML .= '<div class="joms-stream-box joms-fetch-wrapper no-box clearfix">';
		    $paramsHTML .= '<div class="row-fluid">';
		    if( $params->get('image') ) {
		    		$paramsHTML .='<div class="span3">';
		        $paramsHTML .='<a href="'.$params->get('link') ? $params->get('link') : '#'.'">';
		        $paramsHTML .='<img class="joms-stream-thumb" src="'. array_shift($image).'" />';
		        $paramsHTML .='</a>';
		        $paramsHTML .= '</div>';
		    }
            $url = $params->get('url') ? $params->get('url') : '#';
		    $paramsHTML .='<div class="span9"><div class="joms-stream-fetch-content"' . ( $params->get('image') ? '' : ' style="margin-left:0"' ) . '>';
		    $paramsHTML .='<a href="'.$url.'">';
		    $paramsHTML .='<span class="joms-stream-fetch-title">'.$params->get('title').'</span>';
		    $paramsHTML .= '<span class="joms-stream-fetch-desc">'.CStringHelper::trim_words( $params->get('description') ).'</span>';

		    if( $params->get('link') ) {
		        $paramsHTML .='<cite>'.preg_replace('#^https?://#', '', $params->get('link')).'</cite>';
		    }

		    $paramsHTML .='</a></div></div></div></div>';
  		}

  		if(!$params->get('title') && $params->get('url')) {
  			 $paramsHTML .= '<div class="joms-stream-box joms-fetch-wrapper no-box clearfix">';
  			 $paramsHTML .='<a href="'. $params->get('url') .'">';
		     $paramsHTML .='<img class="joms-stream-thumb" src="'.$params->get('url').'" />';
		     $paramsHTML .='</a>';
  			 $paramsHTML .= '</div>';
  		}

		$wall->comment = nl2br($wall->comment);
        $wall->comment = CLinkGeneratorHelper::replaceAliasURL($wall->comment);
        $wall->comment = CStringHelper::getEmoticon($wall->comment);

		$commentsHTML = '
		<div class="cStream-Comment stream-comment clearfix" data-type="stream-comment" data-commentid="'.$wall->id.'">
			' . $userLink . '
			<div class="cStream-Content" style="position:relative">
				<div data-type="stream-comment-content">
					<a class="cStream-Author" href="' . CUrlHelper::userLink($user->id) . '">' . $user->getDisplayName() . '</a>
					<span class="comment">'.$wall->comment.'</span>
					'.$paramsHTML.'
					<div class="cStream-Meta">
						'.CTimeHelper::timeLapse($date).'
						'.$likeHTML.'
						'.$likeCountHTML.'
						<span class="js-meta-actions">
						'.$editHTML.'
						'.$removeHTML.'
						'.$removeTagHTML.'
						</span>
					</div>
				</div>
				<div data-type="stream-comment-editor" class="cStream-Respond" style="display:none">
					<div class="cStream-Form" style="display:block;margin:0;padding:0;">
						<div class="cStream-FormInput"><textarea>'.$origComment.'</textarea></div>
						<div class="cStream-FormSubmit">
							<a data-action="cancel" href="javascript:" class="cStream-FormCancel">Cancel</a>
							<button data-action="save" class="btn btn-primary btn-small">Edit Comment</button>
						</div>
					</div>
				</div>
			</div>
		</div>';

		return $commentsHTML;
	}

	public static function getWallUser($contentid){
		$wallModel = CFactory::getModel('wall');
		return $wallModel->getPostUserslist($contentid);
	}
}

/**
 * Maintain classname compatibility with JomSocial 1.6 below
 */
class CWallLibrary extends CWall
{
}