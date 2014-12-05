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

global $jaxFuncNames;

if ( ! isset($jaxFuncNames) or ! is_array($jaxFuncNames))
{
	$jaxFuncNames = array();
}

$jaxFuncNames['community_entry'] = "";

$jaxFuncNames[] = 'system,ajaxReport';
$jaxFuncNames[] = 'system,ajaxSendReport';
$jaxFuncNames[]	= 'system,ajaxEditWall';
$jaxFuncNames[]	= 'system,ajaxUpdateWall';
$jaxFuncNames[]	= 'system,ajaxShowInvitationForm';
$jaxFuncNames[]	= 'system,ajaxShowFriendsForm';
$jaxFuncNames[]	= 'system,ajaxSubmitInvitation';
$jaxFuncNames[]	= 'system,ajaxLike';
$jaxFuncNames[]	= 'system,ajaxDislike';
$jaxFuncNames[]	= 'system,ajaxUnlike';
$jaxFuncNames[]	= 'system,ajaxGetLikeHTML';
$jaxFuncNames[]	= 'system,ajaxGetOlderWalls';
$jaxFuncNames[] = 'system,ajaxStreamAdd';
$jaxFuncNames[] = 'system,ajaxLoadFriendsList';

$jaxFuncNames[] = "status,ajaxUpdate";
$jaxFuncNames[] = "status,ajaxUpdateStatus";
$jaxFuncNames[] = "status,test";
$jaxFuncNames[] = "frontpage,ajaxTest";
$jaxFuncNames[] = "status,ajaxPreview";
$jaxFuncNames[] = "status,ajaxSubmit";

/** apps **/
$jaxFuncNames[] = 'apps,ajaxShowAbout';
$jaxFuncNames[] = 'apps,ajaxShowPrivacy';
$jaxFuncNames[] = 'apps,ajaxRemove';
$jaxFuncNames[] = 'apps,ajaxAdd';
$jaxFuncNames[] = 'apps,ajaxShowSettings';
$jaxFuncNames[] = 'apps,ajaxSaveSettings';
$jaxFuncNames[] = 'apps,ajaxSavePrivacy';
$jaxFuncNames[] = 'apps,ajaxSaveOrder';
$jaxFuncNames[] = 'apps,ajaxSavePosition';
$jaxFuncNames[] = 'apps,ajaxRefreshLayout';
$jaxFuncNames[] = 'apps,ajaxBrowse';
$jaxFuncNames[] = 'apps,ajaxAddApp';

/** inbox **/
$jaxFuncNames[] = 'inbox,ajaxAddReply';
$jaxFuncNames[] = 'inbox,ajaxCompose';
$jaxFuncNames[] = 'inbox,ajaxRemoveMessage';
$jaxFuncNames[] = 'inbox,ajaxRemoveFullMessages';
$jaxFuncNames[] = 'inbox,ajaxRemoveSentMessages';
$jaxFuncNames[] = 'inbox,ajaxMarkMessageAsRead';
$jaxFuncNames[] = 'inbox,ajaxMarkMessageAsUnread';
$jaxFuncNames[]	= 'inbox,ajaxIphoneInbox';
$jaxFuncNames[] = 'inbox,ajaxSend';
$jaxFuncNames[] = 'inbox,ajaxDeleteMessages';

/** friends**/
$jaxFuncNames[] = 'friends,ajaxFriendTagSave';
$jaxFuncNames[] = 'friends,ajaxAssignTag';
$jaxFuncNames[]	= 'friends,ajaxIphoneFriends';
$jaxFuncNames[] = 'friends,ajaxAdd';
$jaxFuncNames[] = 'friends,ajaxSaveFriend';
$jaxFuncNames[] = 'friends,ajaxConnect';
$jaxFuncNames[]	= 'friends,ajaxCancelRequest';
$jaxFuncNames[]	= 'friends,ajaxApproveRequest';
$jaxFuncNames[]	= 'friends,ajaxRejectRequest';
$jaxFuncNames[] = 'friends,ajaxConfirmFriendRemoval';
$jaxFuncNames[] = 'friends,ajaxRemoveFriend';
$jaxFuncNames[] = 'friends,ajaxBlockFriend';

/** groups **/
$jaxFuncNames[]	= 'groups,ajaxSaveWall';
$jaxFuncNames[]	= 'groups,ajaxSaveDiscussionWall';
$jaxFuncNames[]	= 'groups,ajaxRemoveWall';
$jaxFuncNames[]	= 'groups,ajaxAddNews';
$jaxFuncNames[]	= 'groups,ajaxSaveNews';
$jaxFuncNames[]	= 'groups,ajaxSaveJoinGroup';
$jaxFuncNames[]	= 'groups,ajaxShowLeaveGroup';
$jaxFuncNames[]	= 'groups,ajaxShowUnpublishGroup';
$jaxFuncNames[]	= 'groups,ajaxLeaveGroup';
$jaxFuncNames[]	= 'groups,ajaxRemoveMember';
$jaxFuncNames[]	= 'groups,ajaxRemoveReply';
$jaxFuncNames[]	= 'groups,ajaxApproveMember';
$jaxFuncNames[]	= 'groups,ajaxShowRemoveDiscussion';
$jaxFuncNames[]	= 'groups,ajaxShowRemoveBulletin';
$jaxFuncNames[]	= 'groups,ajaxAddAdmin';
$jaxFuncNames[]	= 'groups,ajaxRemoveAdmin';
$jaxFuncNames[]	= 'groups,ajaxDeleteGroup';
$jaxFuncNames[]	= 'groups,ajaxWarnGroupDeletion';
$jaxFuncNames[]	= 'groups,ajaxAddFeatured';
$jaxFuncNames[]	= 'groups,ajaxRemoveFeatured';
$jaxFuncNames[]	= 'groups,ajaxRejectInvitation';
$jaxFuncNames[]	= 'groups,ajaxAcceptInvitation';
$jaxFuncNames[]	= 'groups,ajaxShowLockDiscussion';
$jaxFuncNames[]	= 'groups,ajaxBanMember';
$jaxFuncNames[]	= 'groups,ajaxUnbanMember';
$jaxFuncNames[] = 'groups,ajaxConfirmMemberRemoval';
$jaxFuncNames[]	= 'groups,ajaxJoinGroup';
$jaxFuncNames[]	= 'groups,ajaxUpdateCount';
$jaxFuncNames[] = 'groups,ajaxShowGroupFeatured';

/** photos **/
$jaxFuncNames[]	= 'photos,ajaxShowWall';
$jaxFuncNames[]	= 'photos,ajaxSaveWall';
$jaxFuncNames[]	= 'photos,ajaxRemoveWall';
$jaxFuncNames[]	= 'photos,ajaxShowWallContents';
$jaxFuncNames[]	= 'photos,ajaxSaveCaption';
$jaxFuncNames[]	= 'photos,ajaxRemovePhoto';
$jaxFuncNames[]	= 'photos,ajaxSetDefaultPhoto';
$jaxFuncNames[]	= 'photos,ajaxPagination';
$jaxFuncNames[] = 'photos,ajaxRemoveAlbum';
$jaxFuncNames[]	= 'photos,ajaxSwitchPhotoTrigger';
$jaxFuncNames[]	= 'photos,ajaxAddFeatured';
$jaxFuncNames[]	= 'photos,ajaxRemoveFeatured';
$jaxFuncNames[]	= 'photos,ajaxAddPhotoTag';
$jaxFuncNames[]	= 'photos,ajaxRemovePhotoTag';
$jaxFuncNames[]	= 'photos,ajaxDisplayCreator';
$jaxFuncNames[]	= 'photos,ajaxLinkToProfile';
$jaxFuncNames[]	= 'photos,ajaxSaveOrdering';
$jaxFuncNames[]	= 'photos,ajaxConfirmRemovePhoto';
$jaxFuncNames[] = 'photos,ajaxAddPhotoHits';
$jaxFuncNames[] = 'photos,ajaxRotatePhoto';
$jaxFuncNames[] = 'photos,ajaxUpdateCounter';
$jaxFuncNames[] = 'photos,ajaxAlbumSaveWall';
$jaxFuncNames[] = 'photos,ajaxAlbumRemoveWall';
$jaxFuncNames[] = 'photos,ajaxUploadPhoto';
$jaxFuncNames[] = 'photos,ajaxGetAlbumURL';
$jaxFuncNames[] = 'photos,ajaxCreateAlbum';
$jaxFuncNames[] = 'photos,ajaxUploadAvatar';
$jaxFuncNames[] = 'photos,ajaxShowThumbnail';
$jaxFuncNames[] = 'photos,ajaxGotoOldUpload';
$jaxFuncNames[] = 'photos,ajaxUpdateThumbnail';
$jaxFuncNames[] = 'photos,ajaxSaveAlbumDesc';
$jaxFuncNames[] = 'photos,ajaxShowPhotoFeatured';
$jaxFuncNames[] = 'photos,ajaxConfirmDefaultPhoto';
$jaxFuncNames[] = 'photos,ajaxChangeCover';
$jaxFuncNames[] = 'photos,ajaxGetPhotoList';
$jaxFuncNames[] = 'photos,ajaxSetPhotoCover';
$jaxFuncNames[] = 'photos,ajaxSetPhotoPhosition';
$jaxFuncNames[] = 'photos,ajaxCheckDefaultAlbum';
/**
 * @since 3.2
 */
$jaxFuncNames[] = 'photos,ajaxShowAlbumFeatured';

/** register **/
$jaxFuncNames[] = 'register,ajaxShowTnc';
$jaxFuncNames[] = 'register,ajaxSetMessage';
$jaxFuncNames[] = 'register,ajaxCheckUserName';
$jaxFuncNames[] = 'register,ajaxCheckEmail';
$jaxFuncNames[] = 'register,ajaxGenerateAuthKey';
$jaxFuncNames[] = 'register,ajaxAssignAuthKey';

/** comment **/
$jaxFuncNames[]	= 'comment,ajaxAdd';

/** profile **/
$jaxFuncNames[] = 'profile,ajaxPlayProfileVideo';
$jaxFuncNames[] = 'profile,ajaxConfirmLinkProfileVideo';
$jaxFuncNames[] = 'profile,ajaxLinkProfileVideo';
$jaxFuncNames[] = 'profile,ajaxRemoveConfirmLinkProfileVideo';
$jaxFuncNames[] = 'profile,ajaxRemoveLinkProfileVideo';
$jaxFuncNames[] = 'profile,ajaxErrorFileUpload';
$jaxFuncNames[]	= 'profile,ajaxBanUser';
$jaxFuncNames[]	= 'profile,ajaxRemovePicture';
$jaxFuncNames[]	= 'profile,ajaxIphoneProfile';
$jaxFuncNames[]	= 'profile,ajaxUploadNewPicture';
$jaxFuncNames[]	= 'profile,ajaxUpdateThumbnail';
$jaxFuncNames[]	= 'profile,ajaxUpdateURL';
$jaxFuncNames[]	= 'profile,ajaxConfirmRemoveAvatar';
$jaxFuncNames[] = 'profile,ajaxShowProfileFeatured';
$jaxFuncNames[]	= 'profile,ajaxRemoveCover';
/**
 * @since 3.2
 */
$jaxFuncNames[] = 'profile,ajaxConfirmIgnoreUser';
$jaxFuncNames[]	= 'profile,ajaxIgnoreUser';

/** activities **/
$jaxFuncNames[]	= 'activities,ajaxConfirmDeleteActivity';
$jaxFuncNames[]	= 'activities,ajaxUpdatePrivacyActivity';
$jaxFuncNames[]	= 'activities,ajaxDeleteActivity';
$jaxFuncNames[]	= 'activities,ajaxHideActivity';
$jaxFuncNames[]	= 'activities,ajaxAddComment';
$jaxFuncNames[]	= 'activities,ajaxGetContent';
$jaxFuncNames[]	= 'activities,ajaxAddPredefined';
$jaxFuncNames[]	= 'activities,ajaxAddText';
$jaxFuncNames[]	= 'activities,ajaxGetActivities';
$jaxFuncNames[] = 'activities,ajaxGetRecentActivities';
$jaxFuncNames[] = 'activities,ajaxGetRecentActivitiesCount';
$jaxFuncNames[] = 'activities,ajaxGetOlderActivities';
$jaxFuncNames[]	= 'activities,ajaxSharePopup';
$jaxFuncNames[]	= 'activities,ajaxShowMap';
$jaxFuncNames[]	= 'activities,ajaxAddShare';
$jaxFuncNames[]	= 'activities,ajaxeditLocation';
$jaxFuncNames[]	= 'activities,ajaxSaveLocation';
$jaxFuncNames[]	= 'activities,ajaxRemoveLocation';
$jaxFuncNames[]	= 'activities,deleteLocation';
$jaxFuncNames[]	= 'activities,ajaxEditStatus';
$jaxFuncNames[]	= 'activities,ajaxHideStatus';
$jaxFuncNames[]	= 'activities,ajaxSaveStatus';
$jaxFuncNames[]	= 'activities,ajaxAddMood';
$jaxFuncNames[]	= 'activities,ajaxSaveMood';
$jaxFuncNames[]	= 'activities,ajaxConfirmRemoveMood';
$jaxFuncNames[]	= 'activities,ajaxRemoveMood';
$jaxFuncNames[]	= 'activities,ajaxShowOthers';
$jaxFuncNames[]	= 'activities,ajaxshowLikedUser';
$jaxFuncNames[]	= 'activities,ajaxGetStreamTitle';
$jaxFuncNames[]	= 'activities,ajaxRemoveUserTag';
/**
 * @since 3.2
 */
$jaxFuncNames[]	= 'activities,ajaxConfirmIgnoreUser';

/** frontpage **/
$jaxFuncNames[]	= 'frontpage,ajaxGetNewestMember';
$jaxFuncNames[]	= 'frontpage,ajaxGetActiveMember';
$jaxFuncNames[]	= 'frontpage,ajaxGetPopularMember';
$jaxFuncNames[]	= 'frontpage,ajaxGetActivities';
$jaxFuncNames[]	= 'frontpage,ajaxGetFeaturedMember';
$jaxFuncNames[]	= 'frontpage,ajaxIphoneFrontpage';
$jaxFuncNames[]	= 'frontpage,ajaxGetNewestVideos';
$jaxFuncNames[]	= 'frontpage,ajaxGetFeaturedVideos';
$jaxFuncNames[]	= 'frontpage,ajaxGetPopularVideos';

/** notification **/
$jaxFuncNames[]	= 'notification,ajaxGetNotification';
$jaxFuncNames[]	= 'notification,ajaxRejectRequest';
$jaxFuncNames[]	= 'notification,ajaxApproveRequest';
$jaxFuncNames[]	= 'notification,ajaxJoinInvitation';
$jaxFuncNames[]	= 'notification,ajaxRejectInvitation';
$jaxFuncNames[] = 'notification,ajaxGroupJoinInvitation';
$jaxFuncNames[] = 'notification,ajaxGroupRejectInvitation';
$jaxFuncNames[] = 'notification,ajaxGroupJoinRequest';
$jaxFuncNames[] = 'notification,ajaxGroupRejectRequest';
$jaxFuncNames[] = 'notification,ajaxGetRequest';
$jaxFuncNames[] = 'notification,ajaxGetInbox';

/** connect **/
$jaxFuncNames[]	= 'connect,ajaxImportData';
$jaxFuncNames[]	= 'connect,ajaxUpdateEmail';
$jaxFuncNames[]	= 'connect,ajaxUpdate';
$jaxFuncNames[]	= 'connect,ajaxMergeNotice';
$jaxFuncNames[]	= 'connect,ajaxMerge';
$jaxFuncNames[]	= 'connect,ajaxCreateNewAccount';
$jaxFuncNames[]	= 'connect,ajaxCheckEmail';
$jaxFuncNames[]	= 'connect,ajaxCheckUsername';
$jaxFuncNames[]	= 'connect,ajaxCheckName';
$jaxFuncNames[]	= 'connect,ajaxValidateLogin';
$jaxFuncNames[] = 'connect,ajaxShowNewUserForm';
$jaxFuncNames[] = 'connect,ajaxShowExistingUserForm';
$jaxFuncNames[] = 'connect,ajaxInvite';

/** Bookmarks **/
$jaxFuncNames[]	= 'bookmarks,ajaxShowBookmarks';
$jaxFuncNames[]	= 'bookmarks,ajaxEmailPage';

/** events **/
$jaxFuncNames[]	= 'events,ajaxSaveWall';
$jaxFuncNames[]	= 'events,ajaxRemoveWall';
$jaxFuncNames[]	= 'events,ajaxWarnEventDeletion';
$jaxFuncNames[]	= 'events,ajaxDeleteEvent';
$jaxFuncNames[]	= 'events,ajaxRequestInvite';
$jaxFuncNames[]	= 'events,ajaxIgnoreEvent';
$jaxFuncNames[]	= 'events,ajaxApproveInvite';
$jaxFuncNames[]	= 'events,ajaxConfirmUnblockGuest';
$jaxFuncNames[]	= 'events,ajaxUnblockGuest';
$jaxFuncNames[]	= 'events,ajaxConfirmBlockGuest';
$jaxFuncNames[]	= 'events,ajaxBlockGuest';
$jaxFuncNames[]	= 'events,ajaxConfirmRemoveGuest';
$jaxFuncNames[]	= 'events,ajaxRemoveGuest';
$jaxFuncNames[]	= 'events,ajaxManageAdmin';
$jaxFuncNames[]	= 'events,ajaxJoinInvitation';
$jaxFuncNames[]	= 'events,ajaxRejectInvitation';
$jaxFuncNames[]	= 'events,ajaxDisplayNearbyEvents';
$jaxFuncNames[] = 'events,ajaxCreate';
$jaxFuncNames[] = 'events,ajaxUpdateStatus';
$jaxFuncNames[] = 'events,ajaxShowMap';
$jaxFuncNames[] = 'events,ajaxGetCalendar';
$jaxFuncNames[] = 'events,ajaxGetEvents';
$jaxFuncNames[] = 'events,ajaxAddFeatured';
$jaxFuncNames[] = 'events,ajaxRemoveFeatured';
$jaxFuncNames[] = 'events,ajaxShowRepeatOption';
$jaxFuncNames[] = 'events,ajaxShowEventFeatured';

/** Backend **/
$jaxFuncNames[]	= 'admin,profiles,ajaxEditField';
$jaxFuncNames[]	= 'admin,profiles,ajaxTogglePublish';
$jaxFuncNames[]	= 'admin,profiles,ajaxSaveField';
$jaxFuncNames[]	= 'admin,groups,ajaxTogglePublish';
$jaxFuncNames[]	= 'admin,groupcategories,ajaxEditCategory';
$jaxFuncNames[]	= 'admin,groupcategories,ajaxSaveCategory';
$jaxFuncNames[]	= 'admin,videoscategories,ajaxTogglePublish';
$jaxFuncNames[]	= 'admin,videoscategories,ajaxEditCategory';
$jaxFuncNames[]	= 'admin,videoscategories,ajaxSaveCategory';
$jaxFuncNames[]	= 'admin,templates,ajaxChangeTemplate';
$jaxFuncNames[]	= 'admin,templates,ajaxLoadTemplateFile';
$jaxFuncNames[]	= 'admin,templates,ajaxSaveTemplateFile';
$jaxFuncNames[]	= 'admin,update,ajaxCheckVersion';
$jaxFuncNames[]	= 'admin,groups,ajaxEditGroup';
$jaxFuncNames[]	= 'admin,groups,ajaxChangeGroupOwner';
$jaxFuncNames[]	= 'admin,maintenance,ajaxPatch';
$jaxFuncNames[]	= 'admin,maintenance,ajaxPatchGroup';
$jaxFuncNames[]	= 'admin,maintenance,ajaxPatchTable';
$jaxFuncNames[]	= 'admin,maintenance,ajaxPatchFriendTable';
$jaxFuncNames[]	= 'admin,maintenance,ajaxPatchFriend';
$jaxFuncNames[]	= 'admin,maintenance,ajaxPatchPrivacy';
$jaxFuncNames[]	= 'admin,maintenance,ajaxCheckHash';
$jaxFuncNames[]	= 'admin,reports,ajaxPerformAction';
$jaxFuncNames[]	= 'admin,userpoints,ajaxRuleScan';
$jaxFuncNames[]	= 'admin,userpoints,ajaxTogglePublish';
$jaxFuncNames[]	= 'admin,userpoints,ajaxEditRule';
$jaxFuncNames[]	= 'admin,userpoints,ajaxSaveRule';
$jaxFuncNames[]	= 'admin,users,ajaxTogglePublish';
$jaxFuncNames[]	= 'admin,messaging,ajaxSendMessage';
$jaxFuncNames[]	= 'admin,users,ajaxRemoveAvatar';
$jaxFuncNames[]	= 'admin,profiles,ajaxEditGroup';
$jaxFuncNames[]	= 'admin,profiles,ajaxSaveGroup';
$jaxFuncNames[]	= 'admin,groups,ajaxAssignGroup';
$jaxFuncNames[]	= 'admin,profiles,ajaxGroupTogglePublish';
$jaxFuncNames[]	= 'admin,events,ajaxTogglePublish';
$jaxFuncNames[]	= 'admin,events,ajaxEditEvent';
$jaxFuncNames[]	= 'admin,eventcategories,ajaxEditCategory';
$jaxFuncNames[]	= 'admin,eventcategories,ajaxSaveCategory';
$jaxFuncNames[]	= 'admin,multiprofile,ajaxTogglePublish';
$jaxFuncNames[]	= 'admin,configuration,ajaxResetPrivacy';
$jaxFuncNames[]	= 'admin,configuration,ajaxResetNotification';
$jaxFuncNames[]	= 'admin,profiles,ajaxGetFieldParams';
$jaxFuncNames[]	= 'admin,system,ajaxAutoupdate';
$jaxFuncNames[] = 'admin,community,getRssFeed';
$jaxFuncNames[] = 'admin,community,getEngagementGraph';
$jaxFuncNames[] = 'admin,community,getStatisticGraph';
$jaxFuncNames[] = 'admin,videos,ajaxTogglePublish';
$jaxFuncNames[] = 'admin,videos,ajaxEditVideo';
$jaxFuncNames[] = 'admin,videos,ajaxViewVideo';
$jaxFuncNames[] = 'admin,users,ajaxToggleStatus';
$jaxFuncNames[] = 'admin,photos,ajaxViewPhoto';
$jaxFuncNames[] = 'admin,photos,ajaxEditPhoto';
$jaxFuncNames[] = 'admin,photos,ajaxTogglePublish';

/** search **/
$jaxFuncNames[]	= 'search,ajaxGetFieldCondition';
$jaxFuncNames[]	= 'search,ajaxAddFeatured';
$jaxFuncNames[]	= 'search,ajaxRemoveFeatured';

/** video **/
$jaxFuncNames[]	= 'videos,ajaxShowWallContents';
$jaxFuncNames[]	= 'videos,ajaxSaveWall';
$jaxFuncNames[]	= 'videos,ajaxRemoveWall';
$jaxFuncNames[]	= 'videos,ajaxRemoveVideo';
$jaxFuncNames[]	= 'videos,ajaxEditVideo';
$jaxFuncNames[]	= 'videos,ajaxAddVideo';
$jaxFuncNames[] = 'videos,ajaxLinkVideo';
$jaxFuncNames[] = 'videos,ajaxLinkVideoPreview';
$jaxFuncNames[] = 'videos,ajaxUploadVideo';
$jaxFuncNames[]	= 'videos,ajaxAddFeatured';
$jaxFuncNames[]	= 'videos,ajaxRemoveFeatured';
$jaxFuncNames[]	= 'videos,ajaxFetchThumbnail';
$jaxFuncNames[]	= 'videos,ajaxSetVideoCategory';
$jaxFuncNames[] = 'videos,ajaxShowVideoWindow';
$jaxFuncNames[] = 'videos,ajaxShowStreamVideoWindow';
$jaxFuncNames[] = 'videos,ajaxShowVideoFeatured';
$jaxFuncNames[] = 'videos,ajaxAddVideoTag';
$jaxFuncNames[] = 'videos,ajaxRemoveVideoTag';
$jaxFuncNames[] = 'videos,ajaxCheckFileSize';

$jaxFuncNames[]	= 'autousersuggest,ajaxAutoUserSuggest';

/** ban **/
$jaxFuncNames[]	= 'profile,ajaxConfirmBlockUser';
$jaxFuncNames[]	= 'profile,ajaxBlockUser';
$jaxFuncNames[]	= 'profile,ajaxConfirmUnBlockUser';
$jaxFuncNames[]	= 'profile,ajaxUnblockUser';
$jaxFuncNames[]	= 'profile,ajaxConfirmIgnoreUser';
$jaxFuncNames[]	= 'profile,ajaxIgnoreUser';
$jaxFuncNames[]	= 'profile,ajaxConfirmUnIgnoreUser';
$jaxFuncNames[]	= 'profile,ajaxUnIgnoreUser';

/** Comment **/
$jaxFuncNames[]	= 'system,ajaxStreamAddComment';
$jaxFuncNames[]	= 'system,ajaxStreamShowComments';
$jaxFuncNames[]	= 'system,ajaxStreamRemoveComment';

$jaxFuncNames[]	= 'system,ajaxStreamAddLike';
$jaxFuncNames[]	= 'system,ajaxStreamShowLikes';
$jaxFuncNames[]	= 'system,ajaxStreamUnlike';
$jaxFuncNames[]	= 'system,ajaxeditComment';

/**
 * @since 3.2
 */
$jaxFuncNames[]	= 'system,ajaxGetFetchUrl';
$jaxFuncNames[]	= 'system,ajaxDeleteTempImage';

/**
 * Memberlist
 * @since 2.0
 **/
$jaxFuncNames[]	= 'memberlist,ajaxShowSaveForm';

/** Like/Dislike **/
$jaxFuncNames[]	= 'system,ajaxLike';
$jaxFuncNames[]	= 'system,ajaxDislike';

/** Zencoder **/
$jaxFuncNames[]	= 'admin,zencoder,ajaxShowForm';
$jaxFuncNames[]	= 'admin,zencoder,ajaxSubmitForm';

/**File upload**/
$jaxFuncNames[]	= 'files,ajaxFileUploadForm';
$jaxFuncNames[]	= 'files,ajaxSaveName';
$jaxFuncNames[]	= 'files,ajaxDeleteFile';
$jaxFuncNames[]	= 'files,ajaxviewFiles';
$jaxFuncNames[]	= 'files,ajaxFileDownload';
$jaxFuncNames[]	= 'files,ajaxgetFileList';
$jaxFuncNames[]	= 'files,ajaxviewMore';

/**
 * @since 3.2
 */
$jaxFuncNames[] = 'location,ajaxGetCoordsByIp';
$jaxFuncNames[]	= 'location,ajaxGetAddressFromCoords';
$jaxFuncNames[]	= 'location,ajaxGetCoordsByAddress';

// Dont process other plugin ajax definitions for back end
if ( ! JString::stristr(JPATH_COMPONENT, 'administrator/components/com_community')
	&& ! JString::stristr(JPATH_COMPONENT, 'administrator\components\com_community'))
{

	// Include CAppPlugins library
	require_once JPATH_ROOT.'/components/com_community/libraries/apps.php';

	// Load Ajax plugins jax file.
	CAppPlugins::loadAjaxPlugins();
}