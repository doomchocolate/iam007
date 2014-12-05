<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die();

class CommunityFilesController extends CommunityBaseController {

    public function ajaxFileUploadForm($type = NULL, $id = NULL) {
        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $params      = JComponentHelper::getParams('com_media');
        $fileType    = $params->get('upload_extensions');

        $config      = CFactory::getConfig();
        $maxFileSize = $config->get('filemaxuploadsize');
        $url         = CRoute::_('index.php?option=com_community&view=files&task=multiUpload&type=' . $type . '&id=' . $id);
        $objResponse = new JAXResponse();
        $tmpl        = new CTemplate();

        $tmpl->set('fileType', $fileType)
                ->set('maxFileSize', $maxFileSize)
                ->set('url', $url);

        $html = $tmpl->fetch('files.multiupload');

        $objResponse->addScriptCall('cWindowAddContent', $html);
        return $objResponse->sendResponse();
    }

    public function multiUpload() {
        $mainframe = JFactory::getApplication();
        $jinput    = $mainframe->input;

        $my        = CFactory::getUser();
        $config    = CFactory::getConfig();
        $type      = $jinput->get('type', NULL, 'NONE');
        $id        = $jinput->get('id', '0', 'Int');

        if ($my->id == 0) {
            $tokenId = $jinput->request->get('token', '', 'STRING');
            $userId  = $jinput->request->get('uploaderid', '', 'INT');
            $my      = CFactory::getUserFromTokenId($tokenId, $userId);
            $session = JFactory::getSession();

            $session->set('user', $my);
        }

        $parentTable = JTable::getInstance(ucfirst($type), 'CTable');
        $parentTable->load($id);

        $table = JTable::getInstance('File', 'CTable');

        $_file = $jinput->files->get('file');

        $fileLib = new CFilesLibrary();

        if (CLimitsLibrary::exceedDaily('files', $my->id)) {
            $json = array('msg' => JText::_('COM_COMMUNITY_FILES_LIMIT_REACHED'));
            echo json_encode($json);
            exit;
        }

        if($type == 'discussion' && !CLimitsHelper::exceededGroupFileUpload($parentTable->groupid))
        {
            $json = array('msg' => JText::_('COM_COMMUNITY_FILES_LIMIT_REACHED'));
            echo json_encode($json);
            exit;
        }

        $now = new JDate();


        $ext = pathinfo($_file['name']);

        $file = new stdClass();
        $file->creator = $my->id;
        $file->filesize = sprintf("%u", $_file['size']);
        $file->name = JString::substr($_file['name'], 0, JString::strlen($_file['name']) - (JString::strlen($ext['extension']) + 1));
        $file->created = $now->toSql();
        $file->type = CFileHelper::getExtensionIcon(CFileHelper::getFileExtension($_file['name']));
        $fileName = JApplication::getHash($_file['name'] . time()) . JString::substr($_file['name'], JString::strlen($_file['name']) - (JString::strlen($ext['extension']) + 1));

        if ($_file['error'] > 0 && $_file['error'] !== 'UPLOAD_ERR_OK') {
            $json = array('msg' => JText::sprintf('COM_COMMUNITY_PHOTOS_UPLOAD_ERROR', $_file['error']));
            echo json_encode($json);
            exit;
        }

        if (!$fileLib->checkType($_file['name'])) {
            $json = array('msg' => JText::_('COM_COMMUNITY_IMAGE_FILE_NOT_SUPPORTED'));
            echo json_encode($json);
            exit;
        }

        switch ($type) {
            case 'discussion': {
                    $file->discussionid = $parentTable->id;
                    $file->groupid = $parentTable->groupid;
                    $file->filepath = 'images/files' . '/' . $type . '/' . $file->discussionid . '/' . $fileName;
                    break;
                }
            case 'bulletin': {
                    $file->bulletinid = $parentTable->id;
                    $file->groupid = $parentTable->groupid;
                    $file->filepath = 'images/files' . '/' . $type . '/' . $file->bulletinid . '/' . $fileName;
                    break;
                }
        }

        if (!JFolder::exists(JPATH_ROOT . '/images/files' . '/' . $type . '/' . $parentTable->id)) {
            JFolder::create(JPATH_ROOT . '/images/files' . '/' . $type . '/' . $parentTable->id, (int) octdec($config->get('folderpermissionsphoto')));
            JFile::copy(JPATH_ROOT . '/components/com_community/index.html', JPATH_ROOT . '/files' . '/' . $type . '/' . $parentTable->id . '/index.html');
        }

        JFile::copy($_file['tmp_name'], JPATH_ROOT . '/images/files' . '/' . $type . '/' . $parentTable->id . '/' . $fileName);

        $table->bind($file);

        $table->store();

        $params = new CParameter('');
        switch ($type) {
            case 'discussion': {
                    // Get repliers for this discussion and notify the discussion creator too
                    $discussionModel = CFactory::getModel('Discussions');
                    $discussion = JTable::getInstance('Discussion', 'CTable');
                    $discussion->load($parentTable->id);
                    $users = $discussionModel->getRepliers($discussion->id, $discussion->groupid);
                    $users[] = $discussion->creator;

                    // The person who post this, should not be getting notification email
                    $key = array_search($my->id, $users);

                    if ($key !== false && isset($users[$key])) {
                        unset($users[$key]);
                    }
                    $params->set('url', 'index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $discussion->groupid . '&topicid=' . $discussion->id);

                    $params->set('filename', $_file['name']);
                    $params->set('discussion', $discussion->title);
                    $params->set('discussion_url', 'index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $discussion->groupid . '&topicid=' . $discussion->id);
                    CNotificationLibrary::add('groups_discussion_newfile', $my->id, $users, JText::sprintf('COM_COMMUNITY_GROUP_DISCUSSION_NEW_FILE_SUBJECT'), '', 'groups.discussion.newfile', $params);
                    break;
                }
            case 'bulletin': {
                    break;
                }
        }
        $json = array('id' => $table->id);
        echo json_encode($json);

        exit;
    }

    public function ajaxSaveName($id = null, $name = null) {
        $objResponse = new JAXResponse();
        $filter = JFilterInput::getInstance();
        $id = $filter->clean($id, 'int');
        $name = $filter->clean($name, 'string');

        $my = CFactory::getUser();
        $file = JTable::getInstance('File', 'CTable');

        $file->load($id);
        $file->name = $name;

        $file->store();

        return $objResponse->sendResponse();
    }

    public function downloadFile() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $type = $jinput->get('type', '', 'NONE');
        $id = $jinput->get('id', 0, 'Int');

        $fileLib = new CFilesLibrary();

        $my = CFactory::getUser();
        $cTable = JTable::getInstance('File', 'CTable');
        $cTable->load($id);

        $parentId = $fileLib->getParentId($type, $cTable);

        if (!$my->authorise('community.download', 'files.' . $type, $parentId)) {
            echo JText::_('COM_COMMUNITY_FILE_DOWNLOAD_WARNING');
            exit;
        }

        if ($cTable->storage == 'file') {
            $fileLib->downloadFile($cTable->filepath, $cTable->name);
        } else {
            $fileLib->S3DownloadFile($cTable);
        }

        $cTable->hits += 1;

        $cTable->store();
        exit;
    }

    public function ajaxDeleteFile($type = null, $id = null) {
        $objResponse = new JAXResponse();

        $filter = JFilterInput::getInstance();
        $id = $filter->clean($id, 'int');

        $file = JTable::getInstance('File', 'CTable');
        $file->load($id);

        $my = CFactory::getUser();

        if (!$my->authorise('community.delete', 'files.' . $type, $file)) {
            die('Invalid Permission');
        }

        if ($file->delete($id)) {
            $objResponse->addScriptCall('joms.jQuery(\'li.file_' . $id . '\').remove();');
            $objResponse->addScriptCall('if(joms.jQuery(\'ul.cFilesList li\').length == 0){joms.jQuery(\'.app-box-content\').html(\'' . JText::_('COM_COMMUNITY_FILES_NO_FILE') . '\')}');
        }

        return $objResponse->sendResponse();
    }

    public function ajaxviewFiles($type, $parentId) {
        $objResponse = new JAXResponse();

        $table = JTable::getInstance(ucfirst($type), 'CTable');
        $table->load($parentId);

        $limit = 5;

        $tmpl = new CTemplate();
        $tmpl->set('type', $type);
        $tmpl->set('gid', $parentId);

        $html = $tmpl->fetch('files.allfiles');

        $objResponse->addScriptCall('cWindowAddContent', $html);
        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::sprintf('COM_COMMUNITY_FILES_VIEW_TITLE', $table->name));

        $objResponse->addScriptCall('joms.file.getFileList', 'mostdownload', $parentId, 0, $limit, $type);

        return $objResponse->sendResponse();
    }

    public function ajaxFileDownload($type, $id) {
        $objResponse = new JAXResponse();

        $filter = JFilterInput::getInstance();
        $id = $filter->clean($id, 'int');
        $type = $filter->clean($type, 'string');

        //CFactory::load( 'libraries' , 'files' );
        $fileLib = new CFilesLibrary();

        $my = CFactory::getUser();
        $cTable = JTable::getInstance('File', 'CTable');
        $cTable->load($id);

        $parentId = $fileLib->getParentId($type, $cTable);

        if ($my->id == 0) {
            $this->ajaxBlockUnregister();
        }

        if (!$my->authorise('community.download', 'files.' . $type, $parentId)) {
            $objResponse->addScriptCall('cWindowAddContent', JText::_('COM_COMMUNITY_FILE_DOWNLOAD_WARNING'));
            return $objResponse->sendResponse();
        }
        /* Notice message to let' user know that file in downloading */
        $objResponse->addScriptCall('cWindowAddContent', JText::_('COM_COMMUNITY_FILE_DOWNLOADING'));
        $url = CRoute::_('index.php?option=com_community&view=files&task=downloadFile&type=' . $type . '&id=' . $id);
        $objResponse->addScriptCall("joms.file.startdownload", $url);

        return $objResponse->sendResponse();
    }

    public function ajaxgetFileList($extension, $groupId, $limitstart = 0, $limit = 8, $type) {
        $objResponse = new JAXResponse();

        //CFactory::load( 'libraries' , 'files' );

        $fileLib = new CFilesLibrary();
        $model = CFactory::getModel('files');
        $field = $type . 'id';

        switch ($extension) {
            case 'mostdownload': {
                    $data = $model->getTopDownload($groupId, $limitstart, $limit, $field);

                    foreach ($data as $key => $_data) {
                        $data[$key] = $fileLib->convertToMb($_data);
                        $data[$key] = $fileLib->getParentType($_data);
                        $data[$key] = $fileLib->getParentName($_data);
                        $data[$key]->user = CFactory::getUser($_data->creator);
                    }

                    $tmpl = new CTemplate();
                    $tmpl->set('data', $data);

                    $html = $tmpl->fetch('files.listing');

                    if ($limitstart == 0) {
                        $objResponse->addAssign('most-download-list', 'innerHTML', $html);
                    } else {
                        $objResponse->addScriptCall('joms.file.updateFileList', $html, 'most-download-list');
                    }

                    break;
                }
            default: {
                    $defaultextension = array('document', 'archive', 'images', 'multimedia', 'miscellaneous');
                    $data = $model->getFileList($type, $groupId, $limitstart, $limit, $extension);

                    foreach ($data as $key => $_data) {
                        $data[$key] = $fileLib->convertToMb($_data);
                        $data[$key] = $fileLib->getParentType($_data);
                        $data[$key] = $fileLib->getParentName($_data);
                        $data[$key]->user = CFactory::getUser($_data->creator);

                        if ($_data->type !== $extension && in_array($extension, $defaultextension, true)) {
                            unset($data[$key]);
                        }
                    }

                    $tmpl = new CTemplate();
                    $tmpl->set('data', $data);

                    $html = $tmpl->fetch('files.listing');

                    if (in_array($extension, $defaultextension, true)) {
                        if ($limitstart == 0) {
                            $objResponse->addAssign('files-' . $extension, 'innerHTML', $html);
                        } else {
                            $objResponse->addScriptCall('joms.file.updateFileList', $html, 'files-' . $extension);
                        }
                    } else {
                        $objResponse->addScriptCall('joms.file.searchFileUpdate', $html, 'most-download');
                    }

                    break;
                }
        }

        //calculate pending files list
        $loadedFiles = $limitstart + count($data);
        $totalFiles = $model->getGroupFileCount($groupId, $extension, $field);
        if ($totalFiles > $loadedFiles) {
            //update limitstart
            $limitstart = $limitstart + count($data);
            $moreCount = $totalFiles - $loadedFiles;
            //load more option
            $loadMore = '<a onClick="joms.file.getFileList(\'' . $extension . '\',\'' . $groupId . '\',\'' . $limitstart . '\',\'' . $limit . '\',\'' . $type . '\');" href="javascript:void(0)">' . JText::_('COM_COMMUNITY_INVITE_LOAD_MORE') . '(' . $moreCount . ') </a>';
        } else {
            //nothing to load
            $loadMore = '';
        }
        $objResponse->addAssign('load-more-btn', 'innerHTML', $loadMore);

        return $objResponse->sendResponse();
    }

    public function ajaxviewMore($type, $id) {
        $objResponse = new JAXResponse();

        $table = JTable::getInstance(ucfirst($type), 'CTable');
        $table->load($id);

        $limit = 5;

        $tmpl = new CTemplate();
        $tmpl->set('type', $type);
        $tmpl->set('gid', $id);

        $html = $tmpl->fetch('files.allfiles');

        $objResponse->addScriptCall('cWindowAddContent', $html);
        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::sprintf('COM_COMMUNITY_FILES_VIEW_TITLE', $table->title));

        $objResponse->addScriptCall('joms.file.getFileList', 'mostdownload', $id, 0, $limit, $type);

        return $objResponse->sendResponse();
    }

}

?>
