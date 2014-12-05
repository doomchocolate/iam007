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

/**
 *
 */
class CommunityGroupsController extends CommunityBaseController {

    /**
     * Call the View object to compose the resulting HTML display
     *
     * @param string View function to be called
     * @param mixed extra data to be passed to the View
     */
    public function renderView($viewfunc, $var = NULL) {

        $my = JFactory::getUser();
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = JRequest::getCmd('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);

        echo $view->get($viewfunc, $var);
    }

    /**
     * Responsible to return necessary contents to the Invitation library
     * so that it can add the mails into the queue
     * */
    public function inviteUsers($cid, $users, $emails, $message) {
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($cid);
        $content = '';
        $text = '';
        $title = JText::sprintf('COM_COMMUNITY_GROUPS_JOIN_INVITATION_MESSAGE', $group->name);
        $params = '';
        $my = CFactory::getUser();

        if (!$my->authorise('community.view', 'groups.invite.' . $cid, $group)) {
            return false;
        }

        $params = new CParameter('');
        $params->set('url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
        $params->set('groupname', $group->name);
        $params->set('group', $group->name);
        $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);

        if ($users) {
            foreach ($users as $id) {
                $groupInvite = JTable::getInstance('GroupInvite', 'CTable');
                $groupInvite->groupid = $group->id;
                $groupInvite->userid = $id;
                $groupInvite->creator = $my->id;

                $groupInvite->store();
            }
        }
        $htmlTemplate = new CTemplate();
        $htmlTemplate->set('groupname', $group->name);
        $htmlTemplate->set('url', CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id));
        $htmlTemplate->set('message', $message);

        $html = $htmlTemplate->fetch('email.groups.invite.html');

        $textTemplate = new CTemplate();
        $textTemplate->set('groupname', $group->name);
        $textTemplate->set('url', CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id));
        $textTemplate->set('message', $message);
        $text = $textTemplate->fetch('email.groups.invite.text');

        return new CInvitationMail('groups_invite', $html, $text, $title, $params);
    }

    public function editGroupWall($wallId) {
        $wall = JTable::getInstance('Wall', 'CTable');
        $wall->load($wallId);

        $my = CFactory::getUser();

        // @rule: We only allow editing of wall in 15 minutes
        $now = JFactory::getDate();
        $interval = CTimeHelper::timeIntervalDifference($wall->date, $now->toSql());
        $interval = abs($interval);

        if ($my->authorise('community.edit', 'groups.wall.' . $wall->contentid, $wall) && ( COMMUNITY_WALLS_EDIT_INTERVAL > $interval )) {
            return true;
        }
        return false;
    }

    public function editDiscussionWall($wallId) {
        $wall = JTable::getInstance('Wall', 'CTable');
        $wall->load($wallId);

        $discussion = JTable::getInstance('Discussion', 'CTable');
        $discussion->load($wall->contentid);

        $my = CFactory::getUser();

        // @rule: We only allow editing of wall in 15 minutes
        $now = JFactory::getDate();
        $interval = CTimeHelper::timeIntervalDifference($wall->date, $now->toSql());
        $interval = abs($interval);

        if ($my->authorise('community.edit', 'groups.discussion.' . $discussion->groupid, $wall) && ( COMMUNITY_WALLS_EDIT_INTERVAL > $interval )) {
            return true;
        }
        return false;
    }

    public function ajaxRemoveFeatured($groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');

        $objResponse = new JAXResponse();

        if (COwnerHelper::isCommunityAdmin()) {
            $model = CFactory::getModel('Featured');

            $featured = new CFeatured(FEATURED_GROUPS);
            $my = CFactory::getUser();

            if ($featured->delete($groupId)) {
                $html = JText::_('COM_COMMUNITY_GROUP_REMOVED_FROM_FEATURED');
            } else {
                $html = JText::_('COM_COMMUNITY_REMOVING_GROUP_FROM_FEATURED_ERROR');
            }
        } else {
            $html = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION');
        }

        $buttons = '<input type="button" class="btn" onclick="window.location.reload();" value="' . JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON') . '"/>';
        $objResponse->addScriptCall('cWindowAddContent', $html, $buttons);

        //ClearCache in Featured List
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_GROUPS));

        return $objResponse->sendResponse();
    }

    /**
     * Admin feature the given group
     */
    public function ajaxAddFeatured($groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');

        $objResponse = new JAXResponse();

        if (COwnerHelper::isCommunityAdmin()) {
            $model = CFactory::getModel('Featured');

            if (!$model->isExists(FEATURED_GROUPS, $groupId)) {

                $featured = new CFeatured(FEATURED_GROUPS);
                $table = JTable::getInstance('Group', 'CTable');
                $table->load($groupId);
                $my = CFactory::getUser();
                $config	= CFactory::getConfig();
                $limit			= $config->get( 'featured' . FEATURED_GROUPS . 'limit' , 10 );

                if($featured->add($groupId, $my->id)===true){
                    $html = JText::sprintf('COM_COMMUNITY_GROUP_IS_FEATURED', $table->name);
                }else{
                    $html = JText::sprintf('COM_COMMUNITY_GROUP_LIMIT_REACHED_FEATURED', $table->name, $limit);
                }
            } else {
                $html = JText::_('COM_COMMUNITY_GROUPS_ALREADY_FEATURED');
            }
        } else {
            $html = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION');
        }

        $buttons = '<input type="button" class="btn" onclick="window.location.reload();" value="' . JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON') . '"/>';

        $objResponse->addScriptCall('cWindowAddContent', $html, $buttons);

        //ClearCache in Featured List
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_GROUPS));

        return $objResponse->sendResponse();
    }

    /**
     * Method is called from the reporting library. Function calls should be
     * registered here.
     *
     * return	String	Message that will be displayed to user upon submission.
     * */
    public function reportDiscussion($link, $message, $discussionId) {
        $report = new CReportingLibrary();

        $report->createReport(JText::_('COM_COMMUNITY_INVALID_DISCUSSION'), $link, $message);

        $action = new stdClass();
        $action->label = 'Remove discussion';
        $action->method = 'groups,removeDiscussion';
        $action->parameters = $discussionId;
        $action->defaultAction = true;

        $report->addActions(array($action));

        return JText::_('COM_COMMUNITY_REPORT_SUBMITTED');
    }

    public function removeDiscussion($discussionId) {
        $model = CFactory::getModel('groups');
        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        //CFactory::load( 'models' , 'discussions' );
        $discussion = JTable::getInstance('Discussion', 'CTable');

        $discussion->load($discussionId);
        $discussion->delete();

        //Clear Cache for groups
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_GROUPS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));

        return JText::_('COM_COMMUNITY_DISCUSSION_REMOVED');
    }

    /**
     * Method is called from the reporting library. Function calls should be
     * registered here.
     *
     * return	String	Message that will be displayed to user upon submission.
     * */
    public function reportGroup($link, $message, $groupId) {
        //CFactory::load( 'libraries' , 'reporting' );
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $report = new CReportingLibrary();

        if (!$my->authorise('community.view', 'groups.report')) {
            return '';
        }

        $report->createReport(JText::_('Bad group'), $link, $message);

        $action = new stdClass();
        $action->label = 'COM_COMMUNITY_GROUPS_UNPUBLISH';
        $action->method = 'groups,unpublishGroup';
        $action->parameters = $groupId;
        $action->defaultAction = true;

        $report->addActions(array($action));

        return JText::_('COM_COMMUNITY_REPORT_SUBMITTED');
    }

    public function unpublishGroup($groupId) {
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);
        if($group->published == 1)
        {
            $group->published = '0';
            $msg = JText::_('COM_COMMUNITY_GROUPS_UNPUBLISH_SUCCESS');
        }
        else
        {
            $group->published = 1;
            $msg = JText::_('COM_COMMUNITY_GROUPS_PUBLISH_SUCCESS');
        }
        $group->store();

        return $msg;
    }

    /**
     * Displays the default groups view
     * */
    public function display($cacheable = false, $urlparams = false) {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();

        if (!$my->authorise('community.view', 'groups.list')) {
            echo JText::_('COM_COMMUNITY_GROUPS_DISABLE');
            return;
        }

        $this->renderView(__FUNCTION__);
    }

    /**
     * Full application view
     */
    public function app() {
        $view = $this->getView('groups');

        echo $view->get('appFullView');
    }

    /**
     * Full application view for discussion
     */
    public function discussApp() {
        $view = $this->getView('groups');

        echo $view->get('discussAppFullView');
    }

    public function ajaxAcceptInvitation($groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');

        $response = new JAXResponse();
        $my = CFactory::getUser();
        $table = JTable::getInstance('GroupInvite', 'CTable');
        $keys = array('groupid' => $groupId, 'userid' => $my->id);
        $table->load($keys);

        if (!$table->isOwner()) {
            $response->addScriptCall('COM_COMMUNITY_INVALID_ACCESS');
            return $response->sendResponse();
        }

        $this->_saveMember($groupId);
        // delete invitation after approve
        $table->delete();

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($table->groupid);
        $url = CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
        $response->addScriptCall("joms.jQuery('#groups-invite-" . $groupId . "').html('<span class=\"community-invitation-message\">" . JText::sprintf('COM_COMMUNITY_GROUPS_ACCEPTED_INVIT', $group->name, $url) . "</span>');location.reload(true)");

        return $response->sendResponse();
    }

    public function ajaxRejectInvitation($groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');

        $response = new JAXResponse();
        $my = CFactory::getUser();
        $table = JTable::getInstance('GroupInvite', 'CTable');
        $keys = array('groupid' => $groupId, 'userid' => $my->id);
        $table->load($keys);

        if (!$table->isOwner()) {
            // when the user is the owner group we need avoid the invitation
            $table->delete();

            $response->addScriptCall('COM_COMMUNITY_INVALID_ACCESS');
            return $response->sendResponse();
        }

        if ($table->delete()) {
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($table->groupid);
            $url = CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
            $response->addScriptCall("joms.jQuery('#groups-invite-" . $groupId . "').html('<span class=\"community-invitation-message\">" . JText::sprintf('COM_COMMUNITY_GROUPS_REJECTED_INVIT', $group->name, $url) . "</span>')");
        }

        return $response->sendResponse();
    }

    public function ajaxUnpublishGroup($groupId=null) {
		$mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $groupId = $jinput->post->get('groupid', '', 'INT');

        CError::assert($groupId, '', '!empty', __FILE__, __LINE__);

        if ( ! COwnerHelper::isCommunityAdmin()) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'), 'error');
            return false;
        } else {
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($groupId);

            if ($group->id == 0) {
                $response->addScriptCall('alert', JText::_('COM_COMMUNITY_GROUPS_ID_NOITEM'));
            } else {
                $group->published = 0;

                if ($group->store()) {
                    //trigger for onGroupDisable
                    $this->triggerGroupEvents('onGroupDisable', $group);
			        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups', false), JText::_('COM_COMMUNITY_GROUPS_UNPUBLISH_SUCCESS'));
                } else {
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_SAVE_ERROR'), 'error');
                    return false;
                }
            }
        }
    }

    /**
     *  Ajax function to delete a group
     *
     * @param	$groupId	The specific group id to unpublish
     * */
    public function ajaxDeleteGroup($groupId, $step = 1) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');
        $step = $filter->clean($step, 'int');

        $response = new JAXResponse();

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        $groupModel = CFactory::getModel('groups');
        $membersCount = $groupModel->getMembersCount($groupId);
        $my = CFactory::getUser();

        // @rule: Do not allow anyone that tries to be funky!
        if (!$my->authorise('community.delete', 'groups.' . $groupId, $group)) {
            $content = JText::_('COM_COMMUNITY_GROUPS_NOT_ALLOWED_DELETE');
            $buttons = '<input type="button" class="btn" onclick="cWindowHide();" value="' . JText::_('COM_COMMUNITY_CANCEL') . '"/>';
            $response->addScriptCall('cWindowAddContent', $content, $buttons);
            return $response->sendResponse();
        }

        $doneMessage = ' - <span class=\'success\'>' . JText::_('COM_COMMUNITY_DONE') . '</span><br />';
        $failedMessage = ' - <span class=\'failed\'>' . JText::_('COM_COMMUNITY_FAILED') . '</span><br />';
        $childId = 0;
        switch ($step) {
            case 1:
                // Nothing gets deleted yet. Just show a messge to the next step
                if (empty($groupId)) {
                    $content = JText::_('COM_COMMUNITY_GROUPS_ID_NOITEM');
                } else {
                    $content = '<strong>' . JText::sprintf('COM_COMMUNITY_GROUPS_DELETE_GROUP', $group->name) . '</strong><br/>';
                    $content .= JText::_('COM_COMMUNITY_GROUPS_DELETE_BULLETIN');

                    $response->addScriptCall('jax.call(\'community\', \'groups,ajaxDeleteGroup\', \'' . $groupId . '\', 2);');

                    //trigger for onBeforeGroupDelete
                    $this->triggerGroupEvents('onBeforeGroupDelete', $group);
                }
                $response->addScriptCall('cWindowAddContent', $content);
                break;
            case 2:
                CommunityModelGroups::getGroupChildId($groupId);
                // Delete all group bulletins
                if (CommunityModelGroups::deleteGroupBulletins($groupId)) {
                    $content = $doneMessage;
                } else {
                    $content = $failedMessage;
                }
                $content .= JText::_('COM_COMMUNITY_GROUPS_DELETE_GROUP_MEMBERS');
                $response->addScriptCall('joms.jQuery("#cWindowContent").append("' . $content . '");');
                $response->addScriptCall('cWindowResize(joms.jQuery("#cWindowContentWrap").height()+10);');
                $response->addScriptCall('jax.call(\'community\', \'groups,ajaxDeleteGroup\', \'' . $groupId . '\', 3);');
                break;
            case 3:
                // Delete all group members
                if (CommunityModelGroups::deleteGroupMembers($groupId)) {
                    $content = $doneMessage;
                } else {
                    $content = $failedMessage;
                }
                $content .= JText::_('COM_COMMUNITY_GROUPS_WALLS_DELETE');
                $response->addScriptCall('joms.jQuery("#cWindowContent").append("' . $content . '");');
                $response->addScriptCall('cWindowResize(joms.jQuery("#cWindowContentWrap").height()+10);');
                $response->addScriptCall('jax.call(\'community\', \'groups,ajaxDeleteGroup\', \'' . $groupId . '\', 4);');
                break;
            case 4:
                // Delete all group wall
                if (CommunityModelGroups::deleteGroupWall($groupId)) {
                    $content = $doneMessage;
                } else {
                    $content = $failedMessage;
                }
                $content .= JText::_('COM_COMMUNITY_GROUPS_DISCUSSIONS_DELETEL');
                $response->addScriptCall('joms.jQuery("#cWindowContent").append("' . $content . '");');
                $response->addScriptCall('cWindowResize(joms.jQuery("#cWindowContentWrap").height()+10);');
                $response->addScriptCall('jax.call(\'community\', \'groups,ajaxDeleteGroup\', \'' . $groupId . '\', 5);');
                break;
            case 5:
                // Delete all group discussions
                if (CommunityModelGroups::deleteGroupDiscussions($groupId)) {
                    $content = $doneMessage;
                } else {
                    $content = $failedMessage;
                }
                $content .= JText::_('COM_COMMUNITY_GROUPS_DELETE_MEDIA');
                $response->addScriptCall('joms.jQuery("#cWindowContent").append("' . $content . '");');
                $response->addScriptCall('cWindowResize(joms.jQuery("#cWindowContentWrap").height()+10);');
                $response->addScriptCall('jax.call(\'community\', \'groups,ajaxDeleteGroup\', \'' . $groupId . '\', 6);');
                break;
            case 6:
                // Delete all group's media files
                if (CommunityModelGroups::deleteGroupMedia($groupId)) {
                    $content = $doneMessage;
                } else {
                    $content = $failedMessage;
                }
                $response->addScriptCall('joms.jQuery("#cWindowContent").append("' . $content . '");');
                $response->addScriptCall('cWindowResize(joms.jQuery("#cWindowContentWrap").height()+10);');
                $response->addScriptCall('jax.call(\'community\', \'groups,ajaxDeleteGroup\', \'' . $groupId . '\', 7);');
                break;

            case 7:
                // Delete group
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($groupId);
                $groupData = $group;

                if ($group->delete($groupId)) {
                    //CFactory::load( 'libraries' , 'featured' );
                    $featured = new CFeatured(FEATURED_GROUPS);
                    $featured->delete($groupId);

                    jimport('joomla.filesystem.file');

                    //@rule: Delete only thumbnail and avatars that exists for the specific group
                    if ($groupData->avatar != "components/com_community/assets/group.jpg" && !empty($groupData->avatar)) {
                        $path = explode('/', $groupData->avatar);
                        $file = JPATH_ROOT . '/' . $path[0] . '/' . $path[1] . '/' . $path[2] . '/' . $path[3];
                        if (JFile::exists($file)) {
                            JFile::delete($file);
                        }
                    }

                    if ($groupData->thumb != "components/com_community/assets/group_thumb.jpg" && !empty($groupData->thumb)) {
                        $path = explode('/', $groupData->thumb);
                        $file = JPATH_ROOT . '/' . $path[0] . '/' . $path[1] . '/' . $path[2] . '/' . $path[3];
                        if (JFile::exists($file)) {
                            JFile::delete($file);
                        }
                    }

                    $html = '<div class=\"info\" style=\"display: none;\">' . JText::_('COM_COMMUNITY_GROUPS_DELETED') . '</div>';
                    $response->addScriptCall('joms.jQuery("' . $html . '").prependTo("#community-wrap").fadeIn();');
                    $response->addScriptCall('joms.jQuery("#community-groups-wrap").fadeOut();');

                    $content = JText::_('COM_COMMUNITY_GROUPS_DELETED');

                    //trigger for onGroupDelete
                    $this->triggerGroupEvents('onAfterGroupDelete', $groupData);
                } else {
                    $content = JText::_('COM_COMMUNITY_GROUPS_DELETE_ERROR');
                }
                $redirect = CRoute::_(JURI::root() . 'index.php?option=com_community&view=groups');
                $buttons = '<input type="button" class="btn" id="groupDeleteDone" onclick="cWindowHide(); window.location=\'' . $redirect . '\';" value="' . JText::_('COM_COMMUNITY_DONE_BUTTON') . '"/>';

                $response->addScriptCall('cWindowAddContent', $content, $buttons);
                break;
            default:
                break;
        }
        //Clear Cache for groups
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_GROUPS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));

        return $response->sendResponse();
    }

    /**
     *  Ajax function to prompt warning during group deletion
     *
     * @param	$groupId	The specific group id to unpublish
     * */
    public function ajaxWarnGroupDeletion($groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');

        $response = new JAXResponse();

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        $title = JText::sprintf('COM_COMMUNITY_GROUPS_DELETE_GROUP', $group->name);
        $content = JText::_('COM_COMMUNITY_GROUPS_DELETE_WARNING');
        $actions = '<input type="button" class="btn" onclick="cWindowHide();" value="' . JText::_('COM_COMMUNITY_CANCEL_BUTTON') . '"/>';
        $actions .= '<input type="button" class="btn btn-primary pull-right" onclick="jax.call(\'community\', \'groups,ajaxDeleteGroup\', \'' . $groupId . '\', 1);" value="' . JText::_('COM_COMMUNITY_DELETE') . '"/>';

        $response->addAssign('cwin_logo', 'innerHTML', $title);
        $response->addScriptCall('cWindowAddContent', $content, $actions);

        return $response->sendResponse();
    }

    /**
     * Ajax function to remove a reply from the discussions
     *
     * @params $discussId	An string that determines the discussion id
     * */
    public function ajaxRemoveReply($wallId) {
        require_once( JPATH_COMPONENT . '/libraries/activities.php' );

        $filter = JFilterInput::getInstance();
        $wallId = $filter->clean($wallId, 'int');

        CError::assert($wallId, '', '!empty', __FILE__, __LINE__);

        $response = new JAXResponse();

        //@rule: Check if user is really allowed to remove the current wall
        $my = CFactory::getUser();
        $model = $this->getModel('wall');
        $wall = $model->get($wallId);
        //CFactory::load( 'models' , 'discussions' );

        $discussion = JTable::getInstance('Discussion', 'CTable');
        $discussion->load($wall->contentid);

        //CFactory::load( 'helpers' , 'owner' );

        if (!$my->authorise('community.delete', 'groups.discussion.' . $discussion->groupid)) {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->ajaxBlockUnregister();
            } else {
                $response->addScriptCall('alert', $errorMsg);
            }
        } else {
            if (!$model->deletePost($wallId)) {
                $response->addAlert(JText::_('COM_COMMUNITY_GROUPS_REMOVE_WALL_ERROR'));
            } else {
                // Update activity.
                CActivities::removeWallActivities(array('app' => 'groups.discussion.reply', 'cid' => $wall->contentid, 'createdAfter' => $wall->date), $wallId);

                //add user points
                if ($wall->post_by != 0) {
                    //CFactory::load( 'libraries' , 'userpoints' );
                    CUserPoints::assignPoint('wall.remove', $wall->post_by);
                }
            }
        }
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS_DETAIL));
        return $response->sendResponse();
    }

    /**
     * Ajax function to display the remove bulletin information
     * */
    public function ajaxShowRemoveBulletin($groupid, $bulletinId) {
        $filter = JFilterInput::getInstance();
        $groupid = $filter->clean($groupid, 'int');
        $bulletinId = $filter->clean($bulletinId, 'int');

        $response = new JAXResponse();

        ob_start();
        ?>
        <div id="community-groups-join">
            <p>
                <?php echo JText::_('COM_COMMUNITY_GROUPS_BULLETIN_DELET_CONFIRMATION'); ?>
            </p>
        </div>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        $actions = '<form class="reset-gap" name="jsform-groups-ajaxshowremovebulletin" method="post" action="' . CRoute::_('index.php?option=com_community&view=groups&task=deleteBulletin') . '">';
        $actions .= '<input type="hidden" value="' . $groupid . '" name="groupid" />';
        $actions .= '<input type="hidden" value="' . $bulletinId . '" name="bulletinid" />';
        $actions .= '<input onclick="cWindowHide();return false" type="button" value="' . JText::_('COM_COMMUNITY_NO_BUTTON') . '" class="btn" name="Submit"/>';
        $actions .= '<input type="submit" value="' . JText::_('COM_COMMUNITY_YES_BUTTON') . '" class="btn btn-primary pull-right" name="Submit"/>';
        $actions .= '</form>';

        $response->addScriptCall('cWindowAddContent', $contents, $actions);

        return $response->sendResponse();
    }

    /**
     * Ajax function to display the remove discussion information
     * */
    public function ajaxShowRemoveDiscussion($groupid, $topicid) {
        $filter = JFilterInput::getInstance();
        $groupid = $filter->clean($groupid, 'int');
        $topicid = $filter->clean($topicid, 'int');

        $response = new JAXResponse();

        ob_start();
        ?>
        <div id="community-groups-join">
            <p>
                <?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_DELETE_CONFIRMATION'); ?>
            </p>
        </div>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        $actions = '<form class="reset-gap" name="jsform-groups-ajaxshowremovediscussion" method="post" action="' . CRoute::_('index.php?option=com_community&view=groups&task=deleteTopic') . '">';
        $actions .= '<input type="hidden" value="' . $groupid . '" name="groupid" />';
        $actions .= '<input type="hidden" value="' . $topicid . '" name="topicid" />';
        $actions .= '&nbsp;';
        $actions .= '<input onclick="cWindowHide();return false" type="button" value="' . JText::_('COM_COMMUNITY_NO_BUTTON') . '" class="btn" name="Submit"/>';
        $actions .= '<input type="submit" value="' . JText::_('COM_COMMUNITY_YES_BUTTON') . '" class="btn btn-primary pull-right" name="Submit"/>';
        $actions .= '</form>';

        $response->addScriptCall('cWindowAddContent', $contents, $actions);

        return $response->sendResponse();
    }

    public function ajaxShowLockDiscussion($groupid, $topicid) {
        $filter = JFilterInput::getInstance();
        $groupid = $filter->clean($groupid, 'int');
        $topicid = $filter->clean($topicid, 'int');

        $response = new JAXResponse();

        $discussion = JTable::getInstance('Discussion', 'CTable');
        $discussion->load($topicid);

        $questionLock = $discussion->lock ? JText::_('COM_COMMUNITY_DISCUSSION_UNLOCK_MESSAGE') : JText::_('COM_COMMUNITY_DISCUSSION_LOCK_MESSAGE');

        ob_start();
        ?>
        <div id="community-groups-join">
            <p>
                <?php echo $questionLock; ?>
            </p>
        </div>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        $actions = '<form class="reset-gap" name="jsform-groups-ajaxshowlockdiscussion" method="post" action="' . CRoute::_('index.php?option=com_community&view=groups&task=lockTopic') . '">';
        $actions .= '<input type="hidden" value="' . $groupid . '" name="groupid" />';
        $actions .= '<input type="hidden" value="' . $topicid . '" name="topicid" />';
        $actions .= '&nbsp;';
        $actions .= '<input onclick="cWindowHide();return false" type="button" value="' . JText::_('COM_COMMUNITY_NO_BUTTON') . '" class="btn" name="Submit"/>';
        $actions .= '<input type="submit" value="' . JText::_('COM_COMMUNITY_YES_BUTTON') . '" class="btn btn-primary pull-right" name="Submit"/>';
        $actions .= '</form>';

        $response->addScriptCall('cWindowAddContent', $contents, $actions);
        ;

        return $response->sendResponse();
    }

    /**
     * Ajax function to approve a specific member
     *
     * @params	string	id	The member's id that needs to be approved.
     * @params	string	groupid	The group id that the user is in.
     * */
    public function ajaxApproveMember($memberId, $groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');
        $memberId = $filter->clean($memberId, 'int');

        $response = new JAXResponse();

        $my = CFactory::getUser();
        $gMember = CFactory::getUser($memberId);
        //CFactory::load( 'helpers' , 'owner' );

        if (!$my->authorise('community.approve', 'groups.member.' . $groupId)) {
            $response->addScriptCall(JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION'));
        } else {
            // Load required tables
            $member = JTable::getInstance('GroupMembers', 'CTable');
            $group = JTable::getInstance('Group', 'CTable');

            // Load the group and the members table
            $group->load($groupId);
            $keys = array('groupId' => $groupId, 'memberId' => $memberId);
            $member->load($keys);

            //trigger for onGroupJoinApproved
            $this->triggerGroupEvents('onGroupJoin', $group, $memberId);

            // Only approve members that is really not approved yet.
            if ($member->approved) {
                $response->addScriptCall('joms.jQuery("#notice").html("' . JText::_('COM_COMMUNITY_MEMBER_ALREADY_APPROVED') . '");');
            } else {
                $member->approve();

                //Update member user table
                $gMember->updateGroupList(true);


                CGroups::joinApproved($group->id, $memberId);

                $response->addScriptCall('joms.jQuery("#member_' . $memberId . '").css("border","3px solid blue");');
                $response->addScriptCall('joms.jQuery("#notice").html("' . JText::_('COM_COMMUNITY_GROUPS_APPROVE_MEMBER') . '");');
                $response->addScriptCall('joms.jQuery("#notice").attr("class","info");');
                $response->addScriptCall('joms.jQuery("#groups-approve-' . $memberId . '").remove();');

                // email notification
                $params = new CParameter('');
                $params->set('url', CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id));
                $params->set('group', $group->name);
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);

                CNotificationLibrary::add('groups_member_approved', $group->ownerid, $memberId, JText::sprintf('COM_COMMUNITY_GROUP_MEMBER_APPROVED_EMAIL_SUBJECT'), '', 'groups.memberapproved', $params);
            }
        }
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_ACTIVITIES));
        return $response->sendResponse();
    }

    public function ajaxConfirmMemberRemoval($memberId, $groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');
        $memberId = $filter->clean($memberId, 'int');

        $objResponse = new JAXResponse();

        // Get html
        $member = CFactory::getUser($memberId);
        ob_start();
        ?>
        <p><?php echo JText::sprintf('COM_COMMUNITY_GROUPS_MEMBER_REMOVAL_WARNING', $member->getDisplayName()); ?></p>
        <br/>
        <input type="checkbox" name="block"/>&nbsp;<?php echo JText::_('COM_COMMUNITY_ALSO_BAN_MEMBER'); ?>
        <?php
        $html = ob_get_contents();
        ob_end_clean();

        // Get action
        ob_start();
        ?>
        <button class="btn" onclick="cWindowHide();" name="no">
            <?php echo JText::_('COM_COMMUNITY_NO_BUTTON'); ?>
        </button>
        <button class="btn btn-primary pull-right" onclick="joms.groups.removeMember(<?php echo $memberId; ?>, <?php echo $groupId; ?>)" name="yes">
            <?php echo JText::_('COM_COMMUNITY_YES_BUTTON'); ?>
        </button>
        <?php
        $actions = ob_get_contents();
        ob_end_clean();

        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_REMOVE_MEMBER'));

        $objResponse->addScriptCall('cWindowAddContent', $html, $actions);

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS));
        return $objResponse->sendResponse();
    }

    /**
     * Ajax method to remove specific member
     *
     * @params	string	id	The member's id that needs to be approved.
     * @params	string	groupid	The group id that the user is in.
     * */
    public function ajaxRemoveMember($memberId, $groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');
        $memberId = $filter->clean($memberId, 'int');

        $response = new JAXResponse();

        $model = $this->getModel('groups');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        $my = CFactory::getUser();

        if (!$my->authorise('community.remove', 'groups.member.' . $memberId, $group)) {

            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->ajaxBlockUnregister();
            } else {
                $response->addScriptCall('joms.jQuery("#notice").html("' . $errorMsg . '");');
                $response->addScriptCall('joms.jQuery("#notice").attr("class","error");');
            }
        } else {
            $groupMember = JTable::getInstance('GroupMembers', 'CTable');
            $keys = array('groupId' => $groupId, 'memberId' => $memberId);
            $groupMember->load($keys);

            $data = new stdClass();

            $data->groupid = $groupId;
            $data->memberid = $memberId;

            $model->removeMember($data);
            $user = CFactory::getUser($memberId);
            $user->updateGroupList(true);

            //add user points
            CUserPoints::assignPoint('group.member.remove', $memberId);

            //delete invitation
            $invitation = JTable::getInstance('Invitation', 'CTable');
            $invitation->deleteInvitation($groupId, $memberId, 'groups,inviteUsers');

            $response->addScriptCall('joms.jQuery("#member_' . $memberId . '").css("border","1px solid red");');
            $response->addScriptCall('joms.jQuery("#notice").html("' . JText::_('COM_COMMUNITY_GROUPS_MEMBERS_DELETE_SUCCESS') . '");');
            $response->addScriptCall('joms.jQuery("#notice").attr("class","info");');
        }

        $response->addScriptCall('cWindowHide();');

        // Store the group and update the data
        $group->updateStats();
        $group->store();
        return $response->sendResponse();
    }

    /**
     * Ajax method to display HTML codes to unpublish group
     *
     * @params	string	groupid	The group id that the user is in.
     * */
    public function ajaxShowUnpublishGroup($groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');

        $response = new JAXResponse();

        $model = $this->getModel('groups');
        $my = JFactory::getUser();

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        ob_start();
        ?>
        <div id="community-groups-join">
            <p><?php echo JText::_('COM_COMMUNITY_GROUPS_UNPUBLISH_CONFIRMATION'); ?> <strong><?php echo $group->name; ?></strong>?</p>
        </div>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        $actions = '<form class="reset-gap" name="jsform-groups-ajaxshowunpublishgroup" method="post" action="' . CRoute::_('index.php?option=com_community&view=groups&task=ajaxUnpublishGroup') . '">';
        $actions .= '<input onclick="cWindowHide();return false" type="button" value="' . JText::_('COM_COMMUNITY_NO_BUTTON') . '" class="btn" name="Submit"/>';
        $actions .= '<input type="submit" value="' . JText::_('COM_COMMUNITY_YES_BUTTON') . '" class="btn btn-primary pull-right" name="Submit"/>';
        $actions .= '<input type="hidden" value="' . $groupId . '" name="groupid" />';
        $actions .= '</form>';

        // Change cWindow title
        $response->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_GROUPS_UNPUBLISH'));
        $response->addScriptCall('cWindowAddContent', $contents, $actions);

        return $response->sendResponse();
    }

    /**
     * Ajax method to display HTML codes to leave group
     *
     * @params	string	id	The member's id that needs to be approved.
     * @params	string	groupid	The group id that the user is in.
     * */
    public function ajaxShowLeaveGroup($groupId) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');

        $response = new JAXResponse();

        $model = $this->getModel('groups');
        $my = JFactory::getUser();

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        ob_start();
        ?>
        <div id="community-groups-join">
            <p><?php echo JText::_('COM_COMMUNITY_GROUPS_MEMBERS_LEAVE_CONFIRMATION'); ?> <strong><?php echo $group->name; ?></strong>?</p>
        </div>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        $actions = '<form class="reset-gap" name="jsform-groups-ajaxshowleavegroup" method="post" action="' . CRoute::_('index.php?option=com_community&view=groups&task=leavegroup') . '">';
        $actions .= '<input onclick="cWindowHide();return false" type="button" value="' . JText::_('COM_COMMUNITY_NO_BUTTON') . '" class="btn" name="Submit"/>';
        $actions .= '<input type="submit" value="' . JText::_('COM_COMMUNITY_YES_BUTTON') . '" class="btn btn-primary pull-right" name="Submit"/>';
        $actions .= '<input type="hidden" value="' . $groupId . '" name="groupid" />';
        $actions .= '</form>';

        // Change cWindow title
        $response->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_GROUPS_LEAVE'));
        $response->addScriptCall('cWindowAddContent', $contents, $actions);

        return $response->sendResponse();
    }

    /**
     * Ajax function to display the join group
     *
     * @params $groupid	A string that determines the group id
     * */
    public function ajaxShowJoinGroup($groupId, $redirectUrl) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');
        $redirectUrl = $filter->clean($redirectUrl, 'string');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $response = new JAXResponse();

        $model = $this->getModel('groups');
        $my = CFactory::getUser();
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        $members = $model->getMembersId($groupId);

        ob_start();
        ?>
        <div id="community-groups-join">
            <?php if (in_array($my->id, $members)): ?>
                <?php
                $buttons = '<input onclick="cWindowHide();" type="submit" value="' . JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON') . '" class="btn" name="Submit"/>';
                ?>
                <p><?php echo JText::_('COM_COMMUNITY_GROUPS_ALREADY_MEMBER'); ?></p>
            <?php else: ?>
                <?php
                $buttons = '<form class="reset-gap" name="jsform-groups-ajaxshowjoingroup" method="post" action="' . CRoute::_('index.php?option=com_community&view=groups&task=joingroup') . '">';
                $buttons .= '<input type="hidden" value="' . $groupId . '" name="groupid" />';
                $buttons .= '<input onclick="cWindowHide();" type="button" value="' . JText::_('COM_COMMUNITY_NO_BUTTON') . '" class="btn" name="Submit" />';
                $buttons .= '<input type="submit" value="' . JText::_('COM_COMMUNITY_YES_BUTTON') . '" class="btn btn-primary pull-right" name="Submit"/>';
                $buttons .= '</form>';
                ?>
                <p>
                    <?php echo JText::sprintf('COM_COMMUNITY_GROUPS_JOIN_CONFIRMATION', $group->name); ?>
                </p>
            <?php endif; ?>
        </div>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        // Change cWindow title
        $response->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_GROUPS_JOIN'));
        $response->addScriptCall('cWindowAddContent', $contents, $buttons);

        return $response->sendResponse();
    }

    /**
     * Ajax Method to remove specific wall from the specific group
     *
     * @param wallId	The unique wall id that needs to be removed.
     * @todo: check for permission
     * */
    public function ajaxRemoveWall($wallId) {
        $filter = JFilterInput::getInstance();
        $wallId = $filter->clean($wallId, 'int');

        CError::assert($wallId, '', '!empty', __FILE__, __LINE__);

        $response = new JAXResponse();

        //@rule: Check if user is really allowed to remove the current wall
        $my = CFactory::getUser();
        $model = $this->getModel('wall');
        $wall = $model->get($wallId);

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($wall->contentid);

        //CFactory::load( 'helpers' , 'owner' );

        if (!$my->authorise('community.delete', 'groups.wall.' . $group->id)) {
            $errorMsg = $my->authoriseErrorMsg();

            if ($errorMsg == 'blockUnregister') {
                return $this->ajaxBlockUnregister();
            } else {
                $response->addScriptCall('alert', $errorMsg);
            }
        } else {
            if (!$model->deletePost($wallId)) {
                $response->addAlert(JText::_('COM_COMMUNITY_GROUPS_REMOVE_WALL_ERROR'));
            } else {
                if ($wall->post_by != 0) {
                    //add user points
                    //CFactory::load( 'libraries' , 'userpoints' );
                    CUserPoints::assignPoint('wall.remove', $wall->post_by);
                }
            }

            $group->updateStats();
            $group->store();
        }
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS));
        return $response->sendResponse();
    }

    /**
     * Ajax function to add new admin to the group
     *
     * @param memberid	Members id
     * @param groupid	Groupid
     *
     * */
    public function ajaxRemoveAdmin($memberId, $groupId) {
        return $this->updateAdmin($memberId, $groupId, false);
    }

    /**
     * Ajax function to add new admin to the group
     *
     * @param memberid	Members id
     * @param groupid	Groupid
     *
     * */
    public function ajaxAddAdmin($memberId, $groupId) {
        return $this->updateAdmin($memberId, $groupId, true);
    }

    public function updateAdmin($memberId, $groupId, $doAdd = true) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');
        $memberId = $filter->clean($memberId, 'int');

        $response = new JAXResponse();

        $my = CFactory::getUser();

        $model = $this->getModel('groups');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        //CFactory::load( 'helpers' , 'owner' );

        if (!$my->authorise('community.edit', 'groups.admin.' . $groupId, $group)) {
            $response->addScriptCall('joms.jQuery("#notice").html("' . JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING') . '");');
            $response->addScriptCall('joms.jQuery("#notice").attr("class","error");');
        } else {
            $member = JTable::getInstance('GroupMembers', 'CTable');

            $keys = array('groupId' => $group->id, 'memberId' => $memberId);
            $member->load($keys);
            $member->permissions = $doAdd ? 1 : 0;

            $member->store();
            $message = $doAdd ? JText::_('COM_COMMUNITY_GROUPS_NEW_ADMIN_MESSAGE') : JText::_('COM_COMMUNITY_GROUPS_NEW_USER_MESSAGE');
            $response->addScriptCall('joms.jQuery("#member_' . $memberId . '").css("border","3px solid blue");');
            $response->addScriptCall('joms.jQuery("#notice").html("' . $message . '");');
            $response->addScriptCall('joms.jQuery("#notice").attr("class","info");');

            if ($doAdd) {
                $response->addScriptCall('joms.jQuery("#member_' . $memberId . ' ul li.setAdmin")[0].addClass("hide");');
                $response->addScriptCall('joms.jQuery("#member_' . $memberId . ' ul li.setAdmin")[1].removeClass("hide");');
            } else {
                $response->addScriptCall('joms.jQuery("#member_' . $memberId . ' ul li.setAdmin")[1].addClass("hide");');
                $response->addScriptCall('joms.jQuery("#member_' . $memberId . ' ul li.setAdmin")[0].removeClass("hide");');
            }
        }

        return $response->sendResponse();
    }

    /**
     * Ajax function to save a new wall entry
     *
     * @param message	A message that is submitted by the user
     * @param uniqueId	The unique id for this group
     *
     * */
    public function ajaxSaveDiscussionWall($message, $uniqueId) {

        $filter = JFilterInput::getInstance();
        //$message = $filter->clean($message, 'string');
        $uniqueId = $filter->clean($uniqueId, 'int');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $response = new JAXResponse();

        $my = CFactory::getUser();

        // Load models
        $group = JTable::getInstance('Group', 'CTable');
        $discussionModel = CFactory::getModel('Discussions');
        $discussion = JTable::getInstance('Discussion', 'CTable');
        //$message		= strip_tags( $message );
        $discussion->load($uniqueId);
        $group->load($discussion->groupid);

        // If the content is false, the message might be empty.
        if (empty($message)) {
            $response->addAlert(JText::_('COM_COMMUNITY_EMPTY_MESSAGE'));
            return $response->sendResponse();
        }
        $config = CFactory::getConfig();

        // @rule: Spam checks
        if ($config->get('antispam_akismet_walls')) {
            //CFactory::load( 'libraries' , 'spamfilter' );

            $filter = CSpamFilter::getFilter();
            $filter->setAuthor($my->getDisplayName());
            $filter->setMessage($message);
            $filter->setEmail($my->email);
            $filter->setURL(CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $discussion->groupid . '&topicid=' . $discussion->id));
            $filter->setType('message');
            $filter->setIP($_SERVER['REMOTE_ADDR']);

            if ($filter->isSpam()) {
                $response->addAlert(JText::_('COM_COMMUNITY_WALLS_MARKED_SPAM'));
                return $response->sendResponse();
            }
        }
        // Save the wall content
        $wall = CWallLibrary::saveWall($uniqueId, $message, 'discussions', $my, ($my->id == $discussion->creator), 'groups,discussion');
        $date = JFactory::getDate();

        $discussion->lastreplied = $date->toSql();
        $discussion->store();

        // @rule: only add the activities of the wall if the group is not private.
        //if( $group->approvals == COMMUNITY_PUBLIC_GROUP ) {
        // Build the URL
        $discussURL = CUrl::build('groups', 'viewdiscussion', array('groupid' => $discussion->groupid, 'topicid' => $discussion->id), true);

        $act = new stdClass();
        $act->cmd = 'group.discussion.reply';
        $act->actor = $my->id;
        $act->target = 0;
        $act->title = '';
        $act->content = $message;
        $act->app = 'groups.discussion.reply';
        $act->cid = $discussion->id;
        $act->groupid = $group->id;
        $act->group_access = $group->approvals;

        $act->like_id = $wall->id;
        $act->like_type = 'groups.discussion.reply';

        $params = new CParameter('');
        $params->set('action', 'group.discussion.reply');
        $params->set('wallid', $wall->id);
        $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
        $params->set('group_name', $group->name);
        $params->set('discuss_url', 'index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $discussion->groupid . '&topicid=' . $discussion->id);

        // Add activity log
        CActivityStream::add($act, $params->toString());


        // Get repliers for this discussion and notify the discussion creator too
        $users = $discussionModel->getRepliers($discussion->id, $group->id);
        $users[] = $discussion->creator;

        // Make sure that each person gets only 1 email
        $users = array_unique($users);

        // The person who post this, should not be getting notification email
        $key = array_search($my->id, $users);

        if ($key !== false && isset($users[$key])) {
            unset($users[$key]);
        }

        // Add notification
        $params = new CParameter('');
        $params->set('url', 'index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $discussion->groupid . '&topicid=' . $discussion->id);
        $params->set('message', $message);
        $params->set('title', $discussion->title);
        $params->set('discussion', $discussion->title);
        $params->set('discussion_url', 'index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $discussion->groupid . '&topicid=' . $discussion->id);
        CNotificationLibrary::add('groups_discussion_reply', $my->id, $users, JText::_('COM_COMMUNITY_GROUP_NEW_DISCUSSION_REPLY_SUBJECT'), '', 'groups.discussion.reply', $params);

        //add user points
        //CFactory::load( 'libraries' , 'userpoints' );
        CUserPoints::assignPoint('group.discussion.reply');

        $config = CFactory::getConfig();
        $order = $config->get('group_discuss_order');
        $order = ($order == 'DESC') ? 'prepend' : 'append';

        $response->addScriptCall('joms.walls.insertOrder = "' . $order . '";');
        $response->addScriptCall('joms.walls.insert', $wall->content);

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_ACTIVITIES, COMMUNITY_CACHE_TAG_GROUPS_DETAIL));

        return $response->sendResponse();
    }

    /**
     * Ajax function to save a new wall entry
     *
     * @param message	A message that is submitted by the user
     * @param uniqueId	The unique id for this group
     * @deprecated since 2.4
     *
     * */
    public function ajaxSaveWall($message, $groupId) {
        $filter = JFilterInput::getInstance();
        $message = $filter->clean($message, 'string');
        $groupId = $filter->clean($groupId, 'int');

        $response = new JAXResponse();
        $my = CFactory::getUser();

        $groupModel = CFactory::getModel('groups');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);
        $config = CFactory::getConfig();

        // @rule: If configuration is set for walls in group to be restricted to memebers only,
        // we need to respect this.
        if (!$my->authorise('community.save', 'groups.wall.' . $groupId, $group)) {
            $response->addScriptCall('alert', JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'));
            return $response->sendResponse();
        }

        $message = strip_tags($message);
        // If the content is false, the message might be empty.
        if (empty($message)) {
            $response->addAlert(JText::_('COM_COMMUNITY_EMPTY_MESSAGE'));
        } else {
            $isAdmin = $groupModel->isAdmin($my->id, $group->id);

            // @rule: Spam checks
            if ($config->get('antispam_akismet_walls')) {
                //CFactory::load( 'libraries' , 'spamfilter' );

                $filter = CSpamFilter::getFilter();
                $filter->setAuthor($my->getDisplayName());
                $filter->setMessage($message);
                $filter->setEmail($my->email);
                $filter->setURL(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId));
                $filter->setType('message');
                $filter->setIP($_SERVER['REMOTE_ADDR']);

                if ($filter->isSpam()) {
                    $response->addAlert(JText::_('COM_COMMUNITY_WALLS_MARKED_SPAM'));
                    return $response->sendResponse();
                }
            }

            // Save the wall content
            $wall = CWallLibrary::saveWall($groupId, $message, 'groups', $my, $isAdmin, 'groups,group');

            // Store event will update all stats count data
            $group->updateStats();
            $group->store();

            // @rule: only add the activities of the wall if the group is not private.
            if ($group->approvals == COMMUNITY_PUBLIC_GROUP) {

                $params = new CParameter('');
                $params->set('action', 'group.wall.create');
                $params->set('wallid', $wall->id);
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId);

                $act = new stdClass();
                $act->cmd = 'group.wall.create';
                $act->actor = $my->id;
                $act->target = 0;
                $act->title = JText::sprintf('COM_COMMUNITY_GROUPS_WALL_POST_GROUP', '{group_url}', $group->name);
                $act->content = $message;
                $act->app = 'groups.wall';
                $act->cid = $wall->id;
                $act->groupid = $group->id;

                // Allow comments
                $act->comment_type = 'groups.wall';
                $act->comment_id = $wall->id;

                CActivityStream::add($act, $params->toString());
            }

            // @rule: Add user points
            //CFactory::load( 'libraries' , 'userpoints' );
            CUserPoints::assignPoint('group.wall.create');

            // @rule: Send email notification to members
            $groupParams = $group->getParams();

            if ($groupParams->get('wallnotification') == '1') {
                $model = $this->getModel('groups');
                $members = $model->getMembers($groupId, null);
                $admins = $model->getAdmins($groupId, null);

                $membersArray = array();

                foreach ($members as $row) {
                    if ($my->id != $row->id) {
                        $membersArray[] = $row->id;
                    }
                }

                foreach ($admins as $row) {
                    if ($my->id != $row->id) {
                        $membersArray[] = $row->id;
                    }
                }
                unset($members);
                unset($admins);

                // Add notification
                //CFactory::load( 'libraries' , 'notification' );

                $params = new CParameter('');
                $params->set('url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId);
                $params->set('group', $group->name);
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId);
                $params->set('message', $message);
                CNotificationLibrary::add('groups_wall_create', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_WALL_POST_NOTIFICATION_EMAIL_SUBJECT', $my->getDisplayName(), $group->name), '', 'groups.wall', $params);
            }
            $response->addScriptCall('joms.walls.insert', $wall->content);
        }

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_ACTIVITIES));

        return $response->sendResponse();
    }

    public function ajaxUpdateCount($type, $groupid) {
        $response = new JAXResponse();
        $my = CFactory::getUser();

        if ($my->id) {
            //CFactory::load( 'libraries' , 'groups' );
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($groupid);

            switch ($type) {
                case 'discussion':
                    $discussModel = CFactory::getModel('discussions');
                    $discussions = $discussModel->getDiscussionTopics($groupid, '10');
                    $totalDiscussion = $discussModel->total;

                    $my->setCount('group_discussion_' . $groupid, $totalDiscussion);

                    break;
                case 'bulletin':
                    $bulletinModel = CFactory::getModel('bulletins');
                    $bulletins = $bulletinModel->getBulletins($groupid);
                    $totalBulletin = $bulletinModel->total;

                    $my->setCount('group_bulletin_' . $groupid, $totalBulletin);
                    break;
            }
        }

        return $response->sendResponse();
    }

    public function ajaxUnbanMember($memberId, $groupId) {
        return $this->updateMemberBan($memberId, $groupId, FALSE);
    }

    /**
     * Ban the member from the group
     * @param type $memberId
     * @param type $groupId
     * @return type
     */
    public function ajaxBanMember($memberId, $groupId) {
        return $this->updateMemberBan($memberId, $groupId, TRUE);
    }

    /**
     * Refactored from AjaxUnBanMember and AjaxBanMember
     */
    public function updateMemberBan($memberId, $groupId, $doBan = true) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');
        $memberId = $filter->clean($memberId, 'int');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $response = new JAXResponse();
        $my = CFactory::getUser();

        $groupModel = CFactory::getModel('groups');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        if (!$my->authorise('community.update', 'groups.member.ban.' . $groupId, $group)) {
            $response->addScriptCall('joms.jQuery("#notice").html("' . JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING') . '");');
            $response->addScriptCall('joms.jQuery("#notice").attr("class","error");');
        } else {
            $member = JTable::getInstance('GroupMembers', 'CTable');
            $keys = array('groupId' => $group->id, 'memberId' => $memberId);
            $member->load($keys);

            $member->permissions = ($doBan) ? COMMUNITY_GROUP_BANNED : COMMUNITY_GROUP_MEMBER;

            $member->store();

            $group->updateStats();

            $group->store();

            if ($doBan) { //if user is banned, display the appropriate response and color code
                $response->addScriptCall('joms.jQuery("#member_' . $memberId . '").css("border","3px solid red");');
                $response->addScriptCall('joms.jQuery("#notice").html("' . JText::_('COM_COMMUNITY_GROUPS_MEMBER_BEEN_BANNED') . '");');
            } else {
                $response->addScriptCall('joms.jQuery("#member_' . $memberId . '").css("border","3px solid green");');
                $response->addScriptCall('joms.jQuery("#notice").html("' . JText::_('COM_COMMUNITY_GROUPS_MEMBER_BEEN_UNBANNED') . '");');
            }
            $response->addScriptCall('joms.jQuery("#notice").attr("class","info");');
        }

        $response->addScriptCall('cWindowHide();');

        return $response->sendResponse();
    }

    /**
     * Ajax retreive Featured Group Information
     * @since 2.6
     */
    public function ajaxShowGroupFeatured($groupId) {
        $my = CFactory::getUser();
        $objResponse = new JAXResponse();

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);
        $group->updateStats(); //ensure that stats are up-to-date
        // Get Avatar
        $avatar = $group->getAvatar('avatar');

        // group date
        $config = CFactory::getConfig();
        $groupDate = JHTML::_('date', $group->created, JText::_('DATE_FORMAT_LC'));

        // Get group link
        $groupLink = CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);

        // Get unfeature icon
        $groupUnfeature = '<a class="album-action remove-featured" title="' . JText::_('COM_COMMUNITY_REMOVE_FEATURED') . '" onclick="joms.featured.remove(\'' . $group->id . '\',\'groups\');" href="javascript:void(0);">' . JText::_('COM_COMMUNITY_REMOVE_FEATURED') . '</a>';

        // Get misc data
        $membercount = JText::sprintf((CStringHelper::isPlural($group->membercount)) ? 'COM_COMMUNITY_GROUPS_MEMBER_COUNT_MANY' : 'COM_COMMUNITY_GROUPS_MEMBER_COUNT', $group->membercount);
        $discussion = JText::sprintf((CStringHelper::isPlural($group->discusscount)) ? 'COM_COMMUNITY_GROUPS_DISCUSSION_COUNT_MANY' : 'COM_COMMUNITY_GROUPS_DISCUSSION_COUNT', $group->discusscount);
        $wallposts = JText::sprintf((CStringHelper::isPlural($group->wallcount)) ? 'COM_COMMUNITY_GROUPS_WALL_COUNT_MANY' : 'COM_COMMUNITY_GROUPS_WALL_COUNT', $group->wallcount);
        $memberCountLink = CRoute::_('index.php?option=com_community&view=groups&task=viewmembers&groupid=' . $group->id);

        // Get like
        $likes = new CLike();
        $likesHTML = $likes->getHTML('groups', $groupId, $my->id);

        $objResponse->addScriptCall('updateGroup', $groupId, $group->name, $group->getCategoryName(), $likesHTML, $avatar, $groupDate, $groupLink, JHTML::_('string.truncate', strip_tags($group->description), 300), $membercount, $discussion, $wallposts, $memberCountLink, $groupUnfeature);
        $objResponse->sendResponse();
    }

    public function edit() {
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = JRequest::getCmd('view', $this->getName());
        $config = CFactory::getConfig();

        $view = $this->getView($viewName, '', $viewType);
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $groupId = JRequest::getInt('groupid', '', 'REQUEST');
        $model = $this->getModel('groups');
        $my = CFactory::getUser();
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        if (empty($group->id)) {
            return JError::raiseWarning(404, JText::_('COM_COMMUNITY_GROUPS_NOT_FOUND_ERROR'));
        }

        if (!$my->authorise('community.edit', 'groups.' . $groupId, $group)) {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->blockUnregister();
            } else {
                echo $errorMsg;
            }
            return;
        }

        if ($jinput->getMethod() == 'POST') {
            JRequest::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));
            $data = JRequest::get('POST');

            $config = CFactory::getConfig();
            $inputFilter = CFactory::getInputFilter($config->get('allowhtml'));
            $description = JRequest::getVar('description', '', 'post', 'string', JREQUEST_ALLOWRAW);
            $data['description'] = $inputFilter->clean($description);

            if (!isset($data['approvals'])) {
                $data['approvals'] = 0;
            }

            $group->bind($data);

            //CFactory::load( 'libraries' , 'apps' );
            $appsLib = CAppPlugins::getInstance();
            $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-groups-forms'));

            if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
                $redirect = CRoute::_('index.php?option=com_community&view=groups&task=edit&groupid=' . $groupId, false);

                $removeActivity = $config->get('removeactivities'); //= $jinput->post->get('removeactivities' , FALSE, 'NONE'); //JRequest::getVar( 'removeactivities' , false , 'POST' );

                if ($removeActivity) {
                    $activityModel = CFactory::getModel('activities');

                    $activityModel->removeActivity('groups', $group->id);
                }

                // validate all fields
                if (empty($group->name)) {
                    $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_GROUPS_EMPTY_NAME_ERROR'));
                    return;
                }

                if ($model->groupExist($group->name, $group->id)) {
                    $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_GROUPS_NAME_TAKEN_ERROR'));
                    return;
                }

                if (empty($group->description)) {
                    $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_GROUPS_DESCRIPTION_EMPTY_ERROR'));
                    return;
                }

                // @rule: Retrieve params and store it back as raw string
                $params = $this->_bindParams();
                $group->params = $params->toString();



                $group->updateStats();
                $group->store();

                $act = new stdClass();
                $act->cmd = 'group.update';
                $act->actor = $my->id;
                $act->target = 0;
                $act->title = ''; //JText::sprintf('COM_COMMUNITY_GROUPS_GROUP_UPDATED' , '{group_url}' , $group->name );
                $act->content = '';
                $act->app = 'groups.update';
                $act->cid = $group->id;
                $act->groupid = $group->id;
                $act->group_access = $group->approvals;

                $params = new CParameter('');
                $params->set('action', 'group.update');
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId);


                // Add activity logging. Delete old ones
                CActivityStream::remove($act->app, $act->cid);
                CActivityStream::add($act, $params->toString());

                //add user points
                CUserPoints::assignPoint('group.updated');

                // Update photos privacy
                $photoPermission = $group->approvals ? PRIVACY_GROUP_PRIVATE_ITEM : 0;
                $photoModel = CFactory::getModel('photos');
                $photoModel->updatePermissionByGroup($group->id, $photoPermission);

                // Reupdate the display.
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id, false), JText::_('COM_COMMUNITY_GROUPS_UPDATED'));
                return;
            }
        }
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS_CAT, COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_ACTIVITIES));
        echo $view->get(__FUNCTION__);
    }

    /**
     * Method to display the create group form
     * */
    public function create() {
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $config = CFactory::getConfig();

        if ($my->authorise('community.add', 'groups')) {
            $model = CFactory::getModel('Groups');
            if (CLimitsLibrary::exceedDaily('groups')) {
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups', false), JText::_('COM_COMMUNITY_GROUPS_LIMIT_REACHED'), 'error');
            }

            $model = $this->getModel('groups');
            $data = new stdClass();
            $data->categories = $model->getCategories();

            if ($jinput->post->get('action', '', 'STRING') == 'save') {
                $appsLib = CAppPlugins::getInstance();
                $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-groups-forms'));

                if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
                    $gid = $this->save();

                    if ($gid !== FALSE) {
                        $mainframe = JFactory::getApplication();

                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($gid);

                        // Set the user as group member
                        $my->updateGroupList();

                        //trigger for onGroupCreate
                        $this->triggerGroupEvents('onGroupCreate', $group);

                        if ($config->get('moderategroupcreation')) {
                            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_GROUPS_MODERATION_MSG', $group->name), $group->name);
                            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups'));
                            return;
                        }

                        $url = CRoute::_('index.php?option=com_community&view=groups&task=created&groupid=' . $gid, false);
                        $mainframe->redirect($url);
                        return;
                    }
                }
            }
        } else {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->blockUnregister();
            } else {
                echo $errorMsg;
            }
            return;
        }
        //Clear Cache in front page
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_GROUPS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));
        $this->renderView(__FUNCTION__, $data);
    }

    /**
     * A new group has been created
     */
    public function created() {
        $this->renderView(__FUNCTION__);
    }

    private function _bindParams() {
        $params = new CParameter('');
        $groupId = JRequest::getInt('groupid', '', 'REQUEST');
        $mainframe = JFactory::getApplication();
        $redirect = CRoute::_('index.php?option=com_community&view=groups&task=edit&groupid=' . $groupId, false);

        $discussordering = JRequest::getInt('discussordering', 0, 'POST');
        $params->set('discussordering', $discussordering);

        // Set the group photo permission
        if (array_key_exists('photopermission-admin', $_POST)) {
            $params->set('photopermission', GROUP_PHOTO_PERMISSION_ADMINS);

            if (array_key_exists('photopermission-member', $_POST)) {
                $params->set('photopermission', GROUP_PHOTO_PERMISSION_ALL);
            }
        } else {
            $params->set('photopermission', GROUP_PHOTO_PERMISSION_DISABLE);
        }

        // Set the group video permission
        if (array_key_exists('videopermission-admin', $_POST)) {
            $params->set('videopermission', GROUP_VIDEO_PERMISSION_ADMINS);
            if (array_key_exists('videopermission-member', $_POST)) {
                $params->set('videopermission', GROUP_VIDEO_PERMISSION_ALL);
            }
        } else {
            $params->set('videopermission', GROUP_VIDEO_PERMISSION_DISABLE);
        }


        // Set the group event permission
        if (array_key_exists('eventpermission-admin', $_POST)) {
            $params->set('eventpermission', GROUP_EVENT_PERMISSION_ADMINS);

            if (array_key_exists('eventpermission-member', $_POST)) {
                $params->set('eventpermission', GROUP_EVENT_PERMISSION_ALL);
            }
        } else {
            $params->set('eventpermission', GROUP_EVENT_PERMISSION_DISABLE);
        }

        $config = CFactory::getConfig();
        $grouprecentphotos = JRequest::getInt('grouprecentphotos', GROUP_PHOTO_RECENT_LIMIT, 'REQUEST');
        if ($grouprecentphotos < 1 && $config->get('enablephotos')) {
            $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_GROUP_RECENT_ALBUM_SETTING_ERROR'));
            return;
        }
        $params->set('grouprecentphotos', $grouprecentphotos);

        $grouprecentvideos = JRequest::getInt('grouprecentvideos', GROUP_VIDEO_RECENT_LIMIT, 'REQUEST');
        if ($grouprecentvideos < 1 && $config->get('enablevideos')) {
            $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_GROUP_RECENT_VIDEOS_SETTING_ERROR'));
            return;
        }
        $params->set('grouprecentvideos', $grouprecentvideos);

        $grouprecentevent = JRequest::getInt('grouprecentevents', GROUP_EVENT_RECENT_LIMIT, 'REQUEST');
        if ($grouprecentevent < 1) {
            $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_GROUP_RECENT_EVENTS_SETTING_ERROR'));
            return;
        }
        $params->set('grouprecentevents', $grouprecentevent);

        $newmembernotification = JRequest::getInt('newmembernotification', 0, 'POST');
        $params->set('newmembernotification', $newmembernotification);

        $joinrequestnotification = JRequest::getInt('joinrequestnotification', 0, 'POST');
        $params->set('joinrequestnotification', $joinrequestnotification);

        $wallnotification = JRequest::getInt('wallnotification', 0, 'POST');
        $params->set('wallnotification', $wallnotification);

        $groupdiscussionfilesharing = JRequest::getInt('groupdiscussionfilesharing', 0, 'POST');
        $params->set('groupdiscussionfilesharing', $groupdiscussionfilesharing);

        $groupannouncementfilesharing = JRequest::getInt('groupannouncementfilesharing', 0, 'POST');
        $params->set('groupannouncementfilesharing', $groupannouncementfilesharing);

        return $params;
    }

    /**
     * Method to save the group
     * @return false if create fail, return the group id if create is successful
     * */
    public function save() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        if (JString::strtoupper($jinput->getMethod()) != 'POST') {
            $document = JFactory::getDocument();
            $viewType = $document->getType();
            $viewName = JRequest::getCmd('view', $this->getName());
            $view = $this->getView($viewName, '', $viewType);
            $view->addWarning(JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING'));
            return false;
        }

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        JRequest::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

        // Get my current data.
        $my = CFactory::getUser();
        $validated = true;

        $group = JTable::getInstance('Group', 'CTable');
        $model = $this->getModel('groups');

        $name = $jinput->post->get('name', '', 'STRING');

        $config = CFactory::getConfig();
        $inputFilter = CFactory::getInputFilter($config->get('allowhtml'));
        $description = JRequest::getVar('description', '', 'post', 'string', JREQUEST_ALLOWRAW);
        $description = $inputFilter->clean($description);

        $categoryId = $jinput->post->get('categoryid', '', 'INT');
        $website = $jinput->post->get('website', '', 'STRING');
        $grouprecentphotos = $jinput->post->get('grouprecentphotos', '', 'NONE');
        $grouprecentvideos = $jinput->post->get('grouprecentvideos', '', 'NONE');
        $grouprecentevents = $jinput->post->get('grouprecentevents', '', 'NONE');

        // @rule: Test for emptyness
        if (empty($name)) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_EMPTY_NAME_ERROR'), 'error');
        }

        // @rule: Test if group exists
        if ($model->groupExist($name)) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_NAME_TAKEN_ERROR'), 'error');
        }

        // @rule: Test for emptyness
        if (empty($description)) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_DESCRIPTION_EMPTY_ERROR'), 'error');
        }

        if (empty($categoryId)) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUP_CATEGORY_NOT_SELECTED'), 'error');
        }

        if ($grouprecentphotos < 1 && $config->get('enablephotos') && $config->get('groupphotos')) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUP_RECENT_ALBUM_SETTING_ERROR'), 'error');
        }

        if ($grouprecentvideos < 1 && $config->get('enablevideos') && $config->get('groupvideos')) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUP_RECENT_VIDEOS_SETTING_ERROR'), 'error');
        }

        if ($grouprecentevents < 1 && $config->get('enableevents') && $config->get('group_events')) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUP_RECENT_EVENTS_SETTING_ERROR'), 'error');
        }

        if ($validated) {
            // Assertions
            // Category Id must not be empty and will cause failure on this group if its empty.
            CError::assert($categoryId, '', '!empty', __FILE__, __LINE__);

            // @rule: Retrieve params and store it back as raw string
            $params = $this->_bindParams();

            $now = new JDate();

            // Bind the post with the table first
            $group->name = $name;
            $group->description = $description;
            $group->categoryid = $categoryId;
            $group->website = $website;
            $group->ownerid = $my->id;
            $group->created = $now->toSql();

            if (array_key_exists('approvals', $_POST)) {
                $group->approvals = $jinput->post->get('approvals', '0', 'INT');
            } else {
                $group->approvals = 0;
            }

            $group->params = $params->toString();

            // @rule: check if moderation is turned on.
            $group->published = ( $config->get('moderategroupcreation') ) ? 0 : 1;

            // we here save the group 1st. else the group->id will be missing and causing the member connection and activities broken.
            $group->store();

            // Since this is storing groups, we also need to store the creator / admin
            // into the groups members table
            $member = JTable::getInstance('GroupMembers', 'CTable');
            $member->groupid = $group->id;
            $member->memberid = $group->ownerid;

            // Creator should always be 1 as approved as they are the creator.
            $member->approved = 1;

            // @todo: Setup required permissions in the future
            $member->permissions = '1';
            $member->store();

            // @rule: Only add into activity once a group is created and it is published.
            if ($group->published) {

                $act = new stdClass();
                $act->cmd = 'group.create';
                $act->actor = $my->id;
                $act->target = 0;
                //$act->title	  	= JText::sprintf('COM_COMMUNITY_GROUPS_NEW_GROUP' , '{group_url}' , $group->name );
                $act->title = JText::sprintf('COM_COMMUNITY_GROUPS_NEW_GROUP_CATEGORY', '{group_url}', $group->name, '{category_url}', $group->getCategoryName());
                $act->content = ( $group->approvals == 0) ? $group->description : '';
                $act->app = 'groups';
                $act->cid = $group->id;
                $act->groupid = $group->id;
                $act->group_access = $group->approvals;

                // Allow comments
                $act->comment_type = 'groups.create';
                $act->comment_id = CActivities::COMMENT_SELF;
                ;

                // Store the group now.
                $group->updateStats();
                $group->store();

                $params = new CParameter('');
                $params->set('action', 'group.create');
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                $params->set('category_url', 'index.php?option=com_community&view=groups&task=display&categoryid=' . $group->categoryid);

                // Add activity logging
                CActivityStream::add($act, $params->toString());
            }

            // if need approval should send email notification to admin
            if ($group->approvals == 0) {
                $title_email = JText::_('COM_COMMUNITY_EMAIL_NEW_GROUP_NEED_APPROVAL_TITLE');
                $message_email = JText::sprintf('COM_COMMUNITY_EMAIL_NEW_GROUP_NEED_APPROVAL_MESSAGE', $my->getDisplayName(), $group->name);
                $from = $mainframe->getCfg('mailfrom'); //$jConfig->getValue( 'mailfrom' );
                $to = $config->get('notifyMaxReport');
                CNotificationLibrary::add('group', $from, $to, $title_email, $message_email, '', '');
            }

            //add user points
            CUserPoints::assignPoint('group.create');

            $validated = $group->id;
        }

        return $validated;
    }

    /**
     * Method to search for a group based on the parameter given
     * in a POST request
     * */
    public function search() {
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $config = CFactory::getConfig();

        if (!$my->authorise('community.view', 'groups.search')) {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_RESTRICTED_ACCESS'), 'notice');
                return $this->blockUnregister();
            } else {
                echo $errorMsg;
            }
            return;
        }

        $this->renderView(__FUNCTION__);
    }

    /**
     * Ajax function call that allows user to leave group
     *
     * @param groupId	The groupid that the user wants to leave from the group
     *
     * */
    public function leaveGroup() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $groupId = $jinput->post->get('groupid', '', 'INT');

        CError::assert($groupId, '', '!empty', __FILE__, __LINE__);

        $model = $this->getModel('groups');
        $my = CFactory::getUser();

        if (!$my->authorise('community.leave', 'groups.' . $groupId)) {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->blockUnregister();
            }
        }

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        $data = new stdClass();
        $data->groupid = $groupId;
        $data->memberid = $my->id;

        $model->removeMember($data);

        //add user points
        CUserPoints::assignPoint('group.leave');

        $mainframe = JFactory::getApplication();

        $my->updateGroupList();

        // STore the group and update the data
        $group->updateStats();
        $group->store();

        //delete invitation
        $invitation = JTable::getInstance('Invitation', 'CTable');
        $invitation->deleteInvitation($groupId, $my->id, 'groups,inviteUsers');

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS));

        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups', false), JText::_('COM_COMMUNITY_GROUPS_LEFT_MESSAGE'));
    }

    /**
     * @since 2.6
     * mygroupupdates page
     *
     */
    public function myGroupUpdate() {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();

        if (!$my->authorise('community.view', 'groups.list')) {
            echo JText::_('COM_COMMUNITY_GROUPS_DISABLE');
            return;
        }

        $this->renderView(__FUNCTION__);
    }

    /**
     * Method is used to receive POST requests from specific user
     * that wants to join a group
     *
     * @return	void
     * */
    public function joinGroup() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $groupId = $jinput->post->get('groupid', '', 'INT'); //JRequest::getVar('groupid' , '' , 'POST');
        // Add assertion to the group id since it must be specified in the post request
        CError::assert($groupId, '', '!empty', __FILE__, __LINE__);

        // Get the current user's object
        $my = CFactory::getUser();

        if (!$my->authorise('community.join', 'groups.' . $groupId)) {
            return $this->blockUnregister();
        }

        // Load necessary tables
        $groupModel = CFactory::getModel('groups');

        if ($groupModel->isMember($my->id, $groupId)) {

            $url = CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId, false);
            $mainframe->redirect($url, JText::_('COM_COMMUNITY_GROUPS_ALREADY_MEMBER'));
        } else {
            $url = CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId, false);

            $member = $this->_saveMember($groupId);
            $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_ACTIVITIES));
            if ($member->approved) {
                $mainframe->redirect($url, JText::_('COM_COMMUNITY_GROUPS_JOIN_SUCCESS'));
            }
            $mainframe->redirect($url, JText::_('COM_COMMUNITY_GROUPS_APPROVAL_NEED'));
        }
    }

    private function _saveMember($groupId) {

        $group = JTable::getInstance('Group', 'CTable');
        $member = JTable::getInstance('GroupMembers', 'CTable');

        $group->load($groupId);
        $params = $group->getParams();
        $my = CFactory::getUser();

        // Set the properties for the members table
        $member->groupid = $group->id;
        $member->memberid = $my->id;

        // @rule: If approvals is required, set the approved status accordingly.
        $member->approved = ( $group->approvals == COMMUNITY_PRIVATE_GROUP ) ? '0' : 1;

        // @rule: Special users should be able to join the group regardless if it requires approval or not
        $member->approved = COwnerHelper::isCommunityAdmin() ? 1 : $member->approved;

        // @rule: Invited users should be able to join the group immediately.
        $groupInvite = JTable::getInstance('GroupInvite', 'CTable');
        $keys = array('groupid' => $groupId, 'userid' => $my->id);
        if ($groupInvite->load($keys)) {
            $member->approved = 1;
        }

        //@todo: need to set the privileges
        $member->permissions = '0';

        $member->store();
        $owner = CFactory::getUser($group->ownerid);

        //trigger for onGroupJoin
        $this->triggerGroupEvents('onGroupJoin', $group, $my->id);

        // Update user group list
        $my->updateGroupList();

        // Test if member is approved, then we add logging to the activities.
        if ($member->approved) {

            CGroups::joinApproved($groupId, $my->id);
        }
        return $member;
    }

    public function uploadAvatar() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = JRequest::getCmd('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);
        $my = CFactory::getUser();
        $config = CFactory::getConfig();

        $groupid = $jinput->request->get('groupid', '', 'INT');
        $data = new stdClass();
        $data->id = $groupid;

        $groupsModel = $this->getModel('groups');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupid);

        if (!$my->authorise('community.upload', 'groups.avatar.' . $groupid, $group)) {
            $errorMsg = $my->authoriseErrorMsg();
            if (!$errorMsg) {
                return $this->blockUnregister();
            } else {
                echo $errorMsg;
            }
            return;
        }

        if ($jinput->getMethod() == 'POST') {
            //CFactory::load( 'helpers' , 'image' );
            $fileFilter = new JInput($_FILES);
            $file = $fileFilter->get('filedata', '', 'array');
            //$file		= JRequest::getVar('filedata' , '' , 'FILES' , 'array');

            if (!CImageHelper::isValidType($file['type'])) {
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_IMAGE_FILE_NOT_SUPPORTED'), 'error');
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id . '&task=uploadAvatar', false));
            }

            //CFactory::load( 'libraries' , 'apps' );
            $appsLib = CAppPlugins::getInstance();
            $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-groups-uploadavatar'));

            if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
                if (empty($file)) {
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_NO_POST_DATA'), 'error');
                } else {
                    $uploadLimit = (double) $config->get('maxuploadsize');
                    $uploadLimit = ( $uploadLimit * 1024 * 1024 );

                    // @rule: Limit image size based on the maximum upload allowed.
                    if (filesize($file['tmp_name']) > $uploadLimit && $uploadLimit != 0) {
                        $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_VIDEOS_IMAGE_FILE_SIZE_EXCEEDED'), 'error');
                        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=uploadavatar&groupid=' . $group->id, false));
                    }

                    if (!CImageHelper::isValid($file['tmp_name'])) {
                        $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_IMAGE_FILE_NOT_SUPPORTED'), 'error');
                    } else {
                        // @todo: configurable width?
                        $imageMaxWidth = 160;

                        // Get a hash for the file name.
                        $fileName = JApplication::getHash($file['tmp_name'] . time());
                        $hashFileName = JString::substr($fileName, 0, 24);

                        // @todo: configurable path for avatar storage?
                        $storage = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/avatar/groups';
                        $storageImage = $storage . '/' . $hashFileName . CImageHelper::getExtension($file['type']);
                        $storageThumbnail = $storage . '/thumb_' . $hashFileName . CImageHelper::getExtension($file['type']);
                        $image = $config->getString('imagefolder') . '/avatar/groups/' . $hashFileName . CImageHelper::getExtension($file['type']);
                        $thumbnail = $config->getString('imagefolder') . '/avatar/groups/' . 'thumb_' . $hashFileName . CImageHelper::getExtension($file['type']);

                        // Generate full image
                        if (!CImageHelper::resizeProportional($file['tmp_name'], $storageImage, $file['type'], $imageMaxWidth)) {
                            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageImage), 'error');
                        }

                        // Generate thumbnail
                        if (!CImageHelper::createThumb($file['tmp_name'], $storageThumbnail, $file['type'])) {
                            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageThumbnail), 'error');
                        }

                        // Autorotate avatar based on EXIF orientation value
                        if ($file['type'] == 'image/jpeg') {
                            $orientation = CImageHelper::getOrientation($file['tmp_name']);
                            CImageHelper::autoRotate($storageImage, $orientation);
                            CImageHelper::autoRotate($storageThumbnail, $orientation);
                        }

                        // Update the group with the new image
                        $groupsModel->setImage($groupid, $image, 'avatar');
                        $groupsModel->setImage($groupid, $thumbnail, 'thumb');

                        // @rule: only add the activities of the news if the group is not private.
                        if ($group->approvals == COMMUNITY_PUBLIC_GROUP) {
                            $url = CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupid);
                            $act = new stdClass();
                            $act->cmd = 'group.avatar.upload';
                            $act->actor = $my->id;
                            $act->target = 0;
                            $act->title = JText::sprintf('COM_COMMUNITY_GROUPS_NEW_GROUP_AVATAR', '{group_url}', $group->name);
                            $act->content = '<img src="' . rtrim(JURI::root(), '/') . '/' . $thumbnail . '" style="border: 1px solid #eee;margin-right: 3px;" />';
                            $act->app = 'groups';
                            $act->cid = $group->id;
                            $act->groupid = $group->id;

                            $params = new CParameter('');
                            $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);


                            CActivityStream::add($act, $params->toString());
                        }

                        //add user points
                        //CFactory::load( 'libraries' , 'userpoints' );
                        CUserPoints::assignPoint('group.avatar.upload');

                        $mainframe = JFactory::getApplication();
                        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupid, false), JText::_('COM_COMMUNITY_GROUPS_AVATAR_UPLOADED'));
                        exit;
                    }
                }
            }
        }
        //ClearCache in frontpage
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_ACTIVITIES));

        echo $view->get(__FUNCTION__, $data);
    }

    /**
     * Method that loads the viewing of a specific group
     * */
    public function viewGroup() {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        if (!$my->authorise('community.view', 'groups.list')) {
            echo JText::_('COM_COMMUNITY_GROUPS_DISABLE');
            return;
        }

        // Load the group table.
        $groupid = JRequest::getInt('groupid', '');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupid);

        if (empty($group->id)) {
            return JError::raiseWarning(404, JText::_('COM_COMMUNITY_GROUPS_NOT_FOUND_ERROR'));
        }

        $this->renderView(__FUNCTION__, $group);
    }

    /**
     * Show only current user group
     */
    public function mygroups() {

        $my = CFactory::getUser();

        if (!$my->authorise('community.view', 'groups.my')) {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->blockUnregister();
            } else {
                echo $errorMsg;
            }
            return;
        }

        $userid = JRequest::getInt('userid', null);
        $this->renderView(__FUNCTION__, $userid);
    }

    public function myinvites() {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();

        if (!$my->authorise('community.view', 'groups.invitelist')) {
            $errorMsg = $my->authoriseErrorMsg();
            echo $errorMsg;
            return;
        }
        $this->renderView(__FUNCTION__);
    }

    public function viewmembers() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $data = new stdClass();
        $data->id = $jinput->get->get('groupid', '', 'INT');

        if (!$my->authorise('community.view', 'groups.member.' . $data->id)) {
            $errorMsg = $my->authoriseErrorMsg();
            echo $errorMsg;
            return;
        }

        $this->renderView(__FUNCTION__, $data);
    }

    /**
     * Show full view of the news for the group
     * */
    public function viewbulletin() {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $id = $jinput->get('bulletinid', 0, 'INT'); //JRequest::getInt('bulletinid' , '' , 'GET');

        if (!$my->authorise('community.view', 'groups.bulletin.' . $id)) {
            $erroMsg = $my->authoriseErrorMsg();
            echo $erroMsg;
            return;
        }

        $this->renderView(__FUNCTION__);
    }

    /**
     * Show all news from specific groups
     * */
    public function viewbulletins() {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();

        if (!$my->authorise('community.view', 'groups.bulletins')) {
            $errorMsg = $my->authoriseErrorMsg();
            echo $errorMsg;
            return;
        }

        $this->renderView(__FUNCTION__);
    }

    /**
     * Show all discussions from specific groups
     * */
    public function viewdiscussions() {
        $this->renderView(__FUNCTION__);
    }

    /**
     * Save a new discussion
     * @param type $discussion
     * @return boolean
     *
     */
    private function _saveDiscussion(&$discussion) {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $topicId = $jinput->get('topicid', '', 'NONE'); //$jinput->get('topicid' , 'POST'); //JRequest::getVar( 'topicid' , 'POST' );
        $postData = JRequest::get('post');
        $inputFilter = CFactory::getInputFilter(true);
        $groupid = $jinput->request->get('groupid', '', 'INT'); //JRequest::getVar('groupid' , '' , 'REQUEST');
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $groupsModel = $this->getModel('groups');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupid);

        $discussion->bind($postData);

        //CFactory::load( 'helpers' , 'owner' );

        $creator = CFactory::getUser($discussion->creator);

        if ($my->id != $creator->id && !empty($discussion->creator) && !$groupsModel->isAdmin($my->id, $discussion->groupid) && !COwnerHelper::isCommunityAdmin()) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'), 'error');
            return false;
        }

        $isNew = is_null($discussion->id) || !$discussion->id ? true : false;

        if ($isNew) {
            $discussion->creator = $my->id;
        }

        $now = new JDate();

        $discussion->groupid = $groupid;
        $discussion->created = $now->toSql();
        $discussion->lastreplied = $discussion->created;
        $discussion->message = JRequest::getVar('message', '', 'post', 'string', JREQUEST_ALLOWRAW);
        $discussion->message = $inputFilter->clean($discussion->message);

        // @rule: do not allow html tags in the title
        $discussion->title = strip_tags($discussion->title);

        //CFactory::load( 'libraries' , 'apps' );
        $appsLib = CAppPlugins::getInstance();
        $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-groups-discussionform'));
        $validated = true;

        if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
            $config = CFactory::getConfig();

            // @rule: Spam checks
            if ($config->get('antispam_akismet_discussions')) {
                //CFactory::load( 'libraries' , 'spamfilter' );

                $filter = CSpamFilter::getFilter();
                $filter->setAuthor($my->getDisplayName());
                $filter->setMessage($discussion->title . ' ' . $discussion->message);
                $filter->setEmail($my->email);
                $filter->setURL(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id));
                $filter->setType('message');
                $filter->setIP($_SERVER['REMOTE_ADDR']);

                if ($filter->isSpam()) {
                    $validated = false;
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_DISCUSSIONS_MARKED_SPAM'), 'error');
                }
            }

            if (empty($discussion->title)) {
                $validated = false;
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_TITLE_EMPTY'), 'error');
            }

            if (empty($discussion->message)) {
                $validated = false;
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_BODY_EMPTY'), 'error');
            }

            if ($validated) {
                //CFactory::load( 'models' , 'discussions' );

                $params = new CParameter('');

                $params->set('filepermission-member', JRequest::getInt('filepermission-member', 0));

                $discussion->params = $params->toString();

                $discussion->store();

                if ($isNew) {
                    $group = JTable::getInstance('Group', 'CTable');
                    $group->load($groupid);

                    // Add logging.
                    $url = CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupid);


                    $act = new stdClass();
                    $act->cmd = 'group.discussion.create';
                    $act->actor = $my->id;
                    $act->target = 0;
                    $act->title = ''; //JText::sprintf('COM_COMMUNITY_GROUPS_NEW_GROUP_DISCUSSION' , '{group_url}' , $group->name );
                    $act->content = $discussion->message;
                    $act->app = 'groups.discussion';
                    $act->cid = $discussion->id;
                    $act->groupid = $group->id;
                    $act->group_access = $group->approvals;

                    $act->like_id = CActivities::LIKE_SELF;
                    $act->comment_id = CActivities::COMMENT_SELF;
                    $act->like_type = 'groups.discussion';
                    $act->comment_type = 'groups.discussion';

                    $params = new CParameter('');
                    $params->set('action', 'group.discussion.create');
                    $params->set('topic_id', $discussion->id);
                    $params->set('topic', $discussion->title);
                    $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                    $params->set('topic_url', 'index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $group->id . '&topicid=' . $discussion->id);

                    CActivityStream::add($act, $params->toString());

                    //@rule: Add notification for group members whenever a new discussion created.
                    $config = CFactory::getConfig();

                    if ($config->get('groupdiscussnotification') == 1) {
                        $model = $this->getModel('groups');
                        $members = $model->getMembers($groupid, null);
                        $admins = $model->getAdmins($groupid, null);

                        $membersArray = array();

                        foreach ($members as $row) {
                            $membersArray[] = $row->id;
                        }

                        foreach ($admins as $row) {
                            $membersArray[] = $row->id;
                        }
                        unset($members);
                        unset($admins);

                        // Add notification
                        //CFactory::load( 'libraries' , 'notification' );

                        $params = new CParameter('');
                        $params->set('url', 'index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $group->id . '&topicid=' . $discussion->id);
                        $params->set('group', $group->name);
                        $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                        $params->set('discussion', $discussion->title);
                        $params->set('discussion_url', 'index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $group->id . '&topicid=' . $discussion->id);
                        $params->set('user', $my->getDisplayName());
                        $params->set('subject', $discussion->title);
                        $params->set('message', $discussion->message);

                        CNotificationLibrary::addMultiple('groups_create_discussion', $discussion->creator, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_DISCUSSION_NOTIFICATION_EMAIL_SUBJECT', $group->name), '', 'groups.discussion', $params);
                    }
                }

                //add user points
                //CFactory::load( 'libraries' , 'userpoints' );
                CUserPoints::assignPoint('group.discussion.create');
            }
        } else {
            $validated = false;
        }

        return $validated;
    }

    public function adddiscussion() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = JRequest::getCmd('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);
        $my = CFactory::getUser();
        $groupid = $jinput->request->get('groupid', '', 'INT'); //JRequest::getVar('groupid' , '' , 'REQUEST');
        $groupsModel = $this->getModel('groups');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupid);

        $config = CFactory::getConfig();

        // Check if the user is banned
        $isBanned = $group->isBanned($my->id);

        if ($my->id == 0) {
            return $this->blockUnregister();
        }


        $config = CFactory::getConfig();

        if (!$config->get('creatediscussion') || (!$group->isMember($my->id) || $isBanned) && !COwnerHelper::isCommunityAdmin()) {
            echo $view->noAccess();
            return;
        }

        $discussion = JTable::getInstance('Discussion', 'CTable');

        if ($jinput->getMethod() == 'POST') {
            JRequest::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

            if ($this->_saveDiscussion($discussion) !== false) {
                $redirectUrl = CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&topicid=' . $discussion->id . '&groupid=' . $groupid, false);
                $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_ACTIVITIES, COMMUNITY_CACHE_TAG_GROUPS_DETAIL));
                $mainframe->redirect($redirectUrl, JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_CREATE_SUCCESS'));
                exit;
            }
        }

        echo $view->get(__FUNCTION__, $discussion);
    }

    /**
     * Show discussion
     */
    public function viewdiscussion() {
        $config = CFactory::getConfig();
        if (!$config->get('enablegroups')) {
            echo JText::_('COM_COMMUNITY_GROUPS_DISABLE');
            return;
        }

        $this->renderView(__FUNCTION__);
    }

    /**
     * Show Invite
     */
    public function invitefriends() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = JRequest::getCmd('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);

        $my = CFactory::getUser();
        $invited = $jinput->post->get('invite-list', '', 'NONE'); //JRequest::getVar( 'invite-list' , '' , 'POST' );
        $inviteMessage = $jinput->post->get('invite-message', '', 'STRING'); //JRequest::getVar( 'invite-message' , '' , 'POST' );
        $groupId = $jinput->request->get('groupid', '', 'INT'); //JRequest::getVar( 'groupid' , '' , 'REQUEST' );
        $groupsModel = $this->getModel('groups');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        // Check if the user is banned
        $isBanned = $group->isBanned($my->id);

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        if ((!$group->isMember($my->id) || $isBanned) && !COwnerHelper::isCommunityAdmin()) {
            echo JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN');
            return;
        }

        if ($jinput->getMethod() == 'POST') {
            JRequest::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));
            if (!empty($invited)) {
                $mainframe = JFactory::getApplication();
                $groupsModel = CFactory::getModel('Groups');
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($groupId);


                foreach ($invited as $invitedUserId) {
                    $groupInvite = JTable::getInstance('GroupInvite', 'CTable');
                    $groupInvite->groupid = $group->id;
                    $groupInvite->userid = $invitedUserId;
                    $groupInvite->creator = $my->id;

                    $groupInvite->store();
                }
                // Add notification
                //CFactory::load( 'libraries' , 'notification' );

                $params = new CParameter('');
                $params->set('url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                $params->set('groupname', $group->name);
                $params->set('message', $inviteMessage);
                $params->set('group', $group->name);
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);

                CNotificationLibrary::add('groups_invite', $my->id, $invited, JText::sprintf('COM_COMMUNITY_GROUPS_JOIN_INVITATION_MESSAGE'), '', 'groups.invite', $params);

                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id, false), JText::_('COM_COMMUNITY_GROUPS_INVITATION_SEND_MESSAGE'));
            } else {
                $view->addWarning(JText::_('COM_COMMUNITY_INVITE_NEED_AT_LEAST_1_FRIEND'));
            }
        }
        echo $view->get(__FUNCTION__);
    }

    public function editDiscussion() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $topicId = $jinput->get('topicid', '', 'INT');

        $discussion = JTable::getInstance('Discussion', 'CTable');
        $discussion->load($topicId);

        $groupId = $jinput->get('groupid', '', 'INT');
        $groupsModel = CFactory::getModel('Groups');
        $my = CFactory::getUser();
        $creator = CFactory::getUser($discussion->creator);
        $isGroupAdmin = $groupsModel->isAdmin($my->id, $discussion->groupid);

        if ($my->id == 0) {
            return $this->blockUserAccess();
        }

        // Make sure this user is a member of this group
        if ($my->id != $creator->id && !$isGroupAdmin && !COwnerHelper::isCommunityAdmin()) {

            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'), 'error');
        } else {
            if ($jinput->getMethod() == 'POST') {
                JRequest::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

                if ($this->_saveDiscussion($discussion) !== false) {
                    $redirectUrl = CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&topicid=' . $discussion->id . '&groupid=' . $groupId, false);

                    $mainframe = JFactory::getApplication();
                    $mainframe->redirect($redirectUrl, JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_UPDATED'));
                }
            }
            $this->renderView(__FUNCTION__, $discussion);
        }
    }

    public function editNews() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->blockUnregister();
        }


        // Load necessary models
        $groupsModel = CFactory::getModel('groups');
        //CFactory::load( 'models' , 'bulletins' );

        $groupId = JRequest::getInt('groupid', '', 'REQUEST');

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);
        //CFactory::load( 'helpers' , 'owner' );
        // Ensure user has really the privilege to view this page.
        if ($my->id != $group->ownerid && !COwnerHelper::isCommunityAdmin() && !$groupsModel->isAdmin($my->id, $groupId)) {
            echo JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING');
            return;
        }

        if ($jinput->getMethod() == 'POST') {
            JRequest::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));
            // Get variables from query
            $bulletin = JTable::getInstance('Bulletin', 'CTable');
            $bulletinId = $jinput->post->get('bulletinid', '', 'INT'); //JRequest::getVar( 'bulletinid' , '' , 'POST' );

            $bulletin->load($bulletinId);
            $bulletin->message = JRequest::getVar('message', '', 'post', 'string', JREQUEST_ALLOWRAW);
            $bulletin->title = $jinput->post->get('title', '', 'string'); //JRequest::getVar( 'title', '', 'post', 'string' );
            // Groupid should never be empty. Add some assert codes here
            CError::assert($groupId, '', '!empty', __FILE__, __LINE__);
            CError::assert($bulletinId, '', '!empty', __FILE__, __LINE__);

            if (empty($bulletin->message)) {
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewbulletin&bulletinid=' . $bulletinId . '&groupid=' . $groupId, false), JText::_('COM_COMMUNITY_GROUPS_BULLETIN_BODY_EMPTY'));
            }
            $params = new CParameter('');
            $params->set('filepermission-member', JRequest::getInt('filepermission-member', 0));
            $bulletin->params = $params->toString();

            $bulletin->store();
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewbulletin&bulletinid=' . $bulletinId . '&groupid=' . $groupId, false), JText::_('COM_COMMUNITY_BULLETIN_UPDATED'));
        }
    }

    /**
     * Method to add a new discussion
     * */
    public function addNews() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = JRequest::getCmd('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);

        // Load necessary models
        $groupsModel = CFactory::getModel('groups');
        $groupId = $jinput->request->get('groupid', '', 'INT');
        $config = CFactory::getConfig();
        if (!$config->get('createannouncement')) {
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId));
        }

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupId);

        // Ensure user has really the privilege to view this page.
        if ($my->id != $group->ownerid && !COwnerHelper::isCommunityAdmin() && !$groupsModel->isAdmin($my->id, $groupId)) {
            echo $view->noAccess();
            return;
        }

        $title = '';
        $message = '';

        if ($jinput->getMethod() == 'POST') {
            JRequest::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

            // Get variables from query
            $bulletin = JTable::getInstance('Bulletin', 'CTable');
            $bulletin->title = $jinput->post->get('title', '', 'STRING');
            $bulletin->message = JRequest::getVar('message', '', 'post', 'string', JREQUEST_ALLOWRAW);

            // Groupid should never be empty. Add some assert codes here
            CError::assert($groupId, '', '!empty', __FILE__, __LINE__);

            $validated = true;

            if (empty($bulletin->title)) {
                $validated = false;
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_BULLETIN_EMPTY'), 'notice');
            }

            if (empty($bulletin->message)) {
                $validated = false;
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_BULLETIN_BODY_EMPTY'), 'notice');
            }

            if ($validated) {
                $params = new CParameter('');
                $bulletin->groupid = $groupId;
                $bulletin->date = gmdate('Y-m-d H:i:s');
                $bulletin->created_by = $my->id;

                // @todo: Add moderators for the groups.
                // Since now is default to the admin, default to publish the news
                $bulletin->published = 1;
                $params->set('filepermission-member', JRequest::getInt('filepermission-member', 0));
                $bulletin->params = $params->toString();
                $bulletin->store();

                // Send notification to all user
                $model = $this->getModel('groups');
                $memberCount = $model->getMembersCount($groupId);
                $members = $model->getMembers($groupId, $memberCount, true, false, SHOW_GROUP_ADMIN);

                $membersArray = array();

                foreach ($members as $row) {
                    $membersArray[] = $row->id;
                }
                unset($members);

                // Add notification
                //CFactory::load( 'libraries' , 'notification' );


                $params->set('url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId);
                $params->set('group', $group->name);
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId);
                $params->set('subject', $bulletin->title);
                $params->set('announcement', $bulletin->title);
                $params->set('announcement_url', 'index.php?option=com_community&view=groups&task=viewbulletin&groupid=' . $group->id . '&bulletinid=' . $bulletin->id);

                CNotificationLibrary::add('groups_create_news', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_GROUPS_EMAIL_NEW_BULLETIN_SUBJECT'), '', 'groups.bulletin', $params);

                // Add logging to the bulletin
                $url = CRoute::_('index.php?option=com_community&view=groups&task=viewbulletin&groupid=' . $group->id . '&bulletinid=' . $bulletin->id);

                // Add activity logging

                $act = new stdClass();
                $act->cmd = 'group.news.create';
                $act->actor = $my->id;
                $act->target = 0;
                $act->title = ''; //JText::sprintf('COM_COMMUNITY_GROUPS_NEW_GROUP_NEWS' , '{group_url}' , $bulletin->title );
                $act->content = ( $group->approvals == 0 ) ? JString::substr(strip_tags($bulletin->message), 0, 100) : '';
                $act->app = 'groups.bulletin';
                $act->cid = $bulletin->id;
                $act->groupid = $group->id;
                $act->group_access = $group->approvals;

                $act->comment_id = CActivities::COMMENT_SELF;
                $act->comment_type = 'groups.bulletin';
                $act->like_id = CActivities::LIKE_SELF;
                $act->like_type = 'groups.bulletin';

                $params = new CParameter('');
//				$params->set( 'group_url' , 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id );
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewbulletin&groupid=' . $group->id . '&bulletinid=' . $bulletin->id);


                CActivityStream::add($act, $params->toString());

                //add user points
                CUserPoints::assignPoint('group.news.create');
                $this->cacheClean(array(COMMUNITY_CACHE_TAG_ACTIVITIES, COMMUNITY_CACHE_TAG_GROUPS_DETAIL));
                //$mainframe->redirect( CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId , false ), JText::_('COM_COMMUNITY_GROUPS_BULLETIN_CREATE_SUCCESS') );
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewbulletin&groupid=' . $groupId . '&bulletinid=' . $bulletin->id, false), JText::_('COM_COMMUNITY_GROUPS_BULLETIN_CREATE_SUCCESS'));
            } else {
                echo $view->get(__FUNCTION__, $bulletin);
                return;
            }
        }

        echo $view->get(__FUNCTION__, false);
    }

    public function deleteTopic() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        //CFactory::load( 'libraries' , 'activities' );
        $my = CFactory::getUser();
        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $topicid = $jinput->post->get('topicid', '', 'INT'); //JRequest::getVar( 'topicid' , '' , 'POST' );
        $groupid = $jinput->post->get('groupid', '', 'INT'); //JRequest::getVar( 'groupid' , '' , 'POST' );

        if (empty($topicid) || empty($groupid)) {
            echo JText::_('COM_COMMUNITY_INVALID_ID');
            return;
        }

        //CFactory::load( 'helpers' , 'owner' );
        //CFactory::load( 'models' , 'discussions' );

        $groupsModel = CFactory::getModel('groups');
        $wallModel = CFactory::getModel('wall');
        $activityModel = CFactory::getModel('activities');
        $fileModel = CFactory::getModel('files');
        $discussion = JTable::getInstance('Discussion', 'CTable');
        $discussion->load($topicid);

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupid);
        $isGroupAdmin = $groupsModel->isAdmin($my->id, $group->id);

        if ($my->id == $discussion->creator || $isGroupAdmin || COwnerHelper::isCommunityAdmin()) {


            if ($discussion->delete()) {
                // Remove the replies to this discussion as well since we no longer need them
                $wallModel->deleteAllChildPosts($topicid, 'discussions');
                // Remove from activity stream
                CActivityStream::remove('groups.discussion', $topicid);
                // Remove Discussion Files
                $fileModel->alldelete($topicid, 'discussion');

                $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_GROUPS_DETAIL, COMMUNITY_CACHE_TAG_ACTIVITIES));
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupid, false), JText::_('COM_COMMUNITY_DISCUSSION_REMOVED'));
            }
        } else {
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupid, false), JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_DELETE_WARNING'));
        }
    }

    public function lockTopic() {
        $mainframe = JFactory::getApplication();

        $my = CFactory::getUser();
        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $topicid = JRequest::getInt('topicid', '', 'POST');
        $groupid = JRequest::getInt('groupid', '', 'POST');

        if (empty($topicid) || empty($groupid)) {
            echo JText::_('COM_COMMUNITY_INVALID_ID');
            return;
        }

        //CFactory::load( 'helpers' , 'owner' );
        //CFactory::load( 'models' , 'discussions' );

        $groupsModel = CFactory::getModel('groups');
        $wallModel = CFactory::getModel('wall');
        $discussion = JTable::getInstance('Discussion', 'CTable');
        $discussion->load($topicid);

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupid);
        $isGroupAdmin = $groupsModel->isAdmin($my->id, $group->id);


        if ($my->id == $discussion->creator || $isGroupAdmin || COwnerHelper::isCommunityAdmin()) {
            $lockStatus = $discussion->lock ? false : true;
            $confirmMsg = $lockStatus ? JText::_('COM_COMMUNITY_DISCUSSION_LOCKED') : JText::_('COM_COMMUNITY_DISCUSSION_UNLOCKED');

            if ($discussion->lock($topicid, $lockStatus)) {
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $groupid . '&topicid=' . $topicid, false), $confirmMsg);
            }
        } else {
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $groupid . '&topicid=' . $topicid, false), JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_LOCK_GROUP_TOPIC'));
        }
    }

    public function deleteBulletin() {
        $mainframe = JFactory::getApplication();
        $my = CFactory::getUser();
        //CFactory::load( 'libraries' , 'activities' );
        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        $bulletinId = JRequest::getInt('bulletinid', '', 'POST');
        $groupid = JRequest::getInt('groupid', '', 'POST');

        if (empty($bulletinId) || empty($groupid)) {
            echo JText::_('COM_COMMUNITY_INVALID_ID');
            return;
        }

        //CFactory::load( 'helpers' , 'owner' );
        //CFactory::load( 'models' , 'bulletins' );

        $groupsModel = CFactory::getModel('groups');
        $bulletin = JTable::getInstance('Bulletin', 'CTable');
        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupid);

        $fileModel = CFactory::getModel('files');

        if ($groupsModel->isAdmin($my->id, $group->id) || COwnerHelper::isCommunityAdmin()) {
            $bulletin->load($bulletinId);

            if ($bulletin->delete()) {

                //add user points
                //CFactory::load( 'libraries' , 'userpoints' );
                CUserPoints::assignPoint('group.news.remove');
                CActivityStream::remove('groups.bulletin', $bulletinId);

                // Remove Bulletin Files
                $fileModel->alldelete($bulletinId, 'bulletin');

                $this->cacheClean(array(COMMUNITY_CACHE_TAG_ACTIVITIES, COMMUNITY_CACHE_TAG_GROUPS_DETAIL));
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupid, false), JText::_('COM_COMMUNITY_BULLETIN_REMOVED'));
            }
        } else {
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupid, false), JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_DELETE_WARNING'));
        }
    }

    /**
     * Displays send email form and processes the sendmail
     * */
    public function sendmail() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $id = JRequest::getInt('groupid', '');
        $message = JRequest::getVar('message', '', 'post', 'string', JREQUEST_ALLOWRAW);
        $title = $jinput->get('title', '', 'STRING'); //JRequest::getVar( 'title'	, '' );
        $my = CFactory::getUser();

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($id);

        //CFactory::load( 'helpers' , 'owner' );

        if (empty($id) || (!$group->isAdmin($my->id) && !COwnerHelper::isCommunityAdmin() )) {
            echo JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN');
            return;
        }

        if ($jinput->getMethod() == 'POST') {
            // Check for request forgeries
            JRequest::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

            $model = CFactory::getModel('Groups');
            $members = $model->getMembers($group->id, COMMUNITY_GROUPS_NO_LIMIT, COMMUNITY_GROUPS_ONLY_APPROVED, COMMUNITY_GROUPS_NO_RANDOM, COMMUNITY_GROUPS_SHOW_ADMINS);

            $errors = false;

            if (empty($message)) {
                $errors = true;
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_INBOX_MESSAGE_REQUIRED'), 'error');
            }

            if (empty($title)) {
                $errors = true;
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_TITLE_REQUIRED'), 'error');
            }

            if (!$errors) {
                // Add notification
                //CFactory::load( 'libraries' , 'notification' );
                $emails = array();
                $total = 0;
                foreach ($members as $member) {
                    $total += 1;
                    $user = CFactory::getUser($member->id);
                    $emails[] = $user->id;

                    // Exclude the actor
                    if ($user->id == $my->id) {
                        $total -= 1;
                    }
                }

                $params = new CParameter('');
                $params->set('url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                $params->set('group', $group->name);
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                $params->set('email', $title);
                $params->set('title', $title);
                $params->set('message', $message);
                CNotificationLibrary::add('groups_sendmail', $my->id, $emails, JText::sprintf('COM_COMMUNITY_GROUPS_SENDMAIL_SUBJECT'), '', 'groups.sendmail', $params);

                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id, false), JText::sprintf('COM_COMMUNITY_EMAIL_SENT_TO_GROUP_MEMBERS', $total));
            }
        }

        $this->renderView(__FUNCTION__);
    }

    /*
     * group event name
     * object array
     */

    public function triggerGroupEvents($eventName, &$args, $target = null) {
        CError::assert($args, 'object', 'istype', __FILE__, __LINE__);

        require_once( COMMUNITY_COM_PATH . '/libraries/apps.php' );
        $appsLib = CAppPlugins::getInstance();
        $appsLib->loadApplications();

        $params = array();
        $params[] = $args;

        if (!is_null($target))
            $params[] = $target;

        $appsLib->triggerEvent($eventName, $params);
        return true;
    }

    public function banlist() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $data = new stdClass();
        $data->id = $jinput->get->get('groupid', '', 'INT'); //JRequest::getVar('groupid' , '' , 'GET');
        $this->renderView(__FUNCTION__, $data);
    }

    /**
     * Method is used to receive POST requests from specific user
     * that wants to join a group
     * @param fastjoin : join from discussion page
     *
     * @return	void
     * */
    public function ajaxJoinGroup($groupId, $fastJoin = 'no') {
        $objResponse = new JAXResponse();

        $filter = JFilterInput::getInstance();

        $groupId = $filter->clean($groupId, 'int');
        if (empty($fastJoin)) {
            $fastJoin = 'no';
        }
        $fastJoin = $filter->clean($fastJoin, 'string');

        // Add assertion to the group id since it must be specified in the post request
        CError::assert($groupId, '', '!empty', __FILE__, __LINE__);

        // Get the current user's object
        $my = CFactory::getUser();

        if (!$my->authorise('community.join', 'groups.' . $groupId)) {
            return $this->ajaxBlockUnregister();
        }

        // Load necessary tables
        $groupModel = CFactory::getModel('groups');
        if ($fastJoin == 'yes') {
            $member = $this->_saveMember($groupId);
            $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_ACTIVITIES));

            if ($member->approved) {
                $objResponse->addScriptCall("joms.groups.joinComplete('" . JText::_('COM_COMMUNITY_GROUPS_JOIN_SUCCESS_BUTTON') . "'); location.reload(true);");
            } else {
                $objResponse->addScriptCall("joms.groups.joinComplete('" . JText::_('COM_COMMUNITY_GROUPS_JOIN_SUCCESS_BUTTON') . "'); location.reload(true);");
                //$objResponse->addScriptCall("joms.jQuery('.group-top').prepend('<div class=\"info\">".JText::_('COM_COMMUNITY_GROUPS_APPROVAL_NEED')."</div>');");
            }
        } else {
            if ($groupModel->isMember($my->id, $groupId)) {
                $objResponse->addScriptCall("joms.jQuery('.group-top').html('<div class=\"info\">" . JText::_('COM_COMMUNITY_GROUPS_ALREADY_MEMBER') . "</div>');");
            } else {
                $url = CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId, false);

                $member = $this->_saveMember($groupId);
                $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_ACTIVITIES));

                if ($member->approved) {
                    $objResponse->addScriptCall("joms.jQuery('.group-top').html('<div class=\"info\">" . JText::_('COM_COMMUNITY_GROUPS_JOIN_SUCCESS') . "</div>');");
                } else {
                    $objResponse->addScriptCall("joms.jQuery('.group-top').html('<div class=\"info\">" . JText::_('COM_COMMUNITY_GROUPS_APPROVAL_NEED') . "</div>');");
                }

                $objResponse->addScriptCall("joms.jQuery('.group-join').parent().remove();");

                $objResponse->addScriptCall("setTimeout(function(){window.location.reload()},1000);");
            }
        }

        return $objResponse->sendResponse();
    }

}
