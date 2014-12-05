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
CAssets::attach('assets/easytabs/jquery.easytabs.min.js', 'js');
CAssets::attach('assets/ajaxfileupload.pack.js', 'js');
CAssets::attach('assets/imgareaselect/scripts/jquery.imgareaselect.min.js', 'js');
CAssets::attach('assets/imgareaselect/css/imgareaselect-avatar.css', 'css');
CAssets::attach('assets/jqueryui/drag/jquery-ui-drag.js', 'js');
?>

<style type="text/css">
  /*@TEMPORARY*/
  #community-wrap .row-fluid [class*="span"] {
    float: left;
  }
</style>

<div class="js-focus">
  <div class="js-focus-cover">
    <img id='<?php echo $group->id; ?>'  data-cover-context="group" class="focusbox-image cover-image" src="<?php echo $group->getCover(); ?>" alt="cover photo" style="top:<?php echo $group->coverPostion; ?>">
    <div class="js-focus-gradient" data-cover-context="group" data-cover-type="cover"></div>

      <?php if($isAdmin || $isSuperAdmin || $isMine) { ?>
        <!-- Change cover button -->
        <div class="js-focus-change-cover" data-cover-type="cover">
          <a href="javascript:void(0)" class="btn <?php echo ($group->defaultCover) ? 'hidden' : '' ?>" data-cover-context="group" onclick="joms.cover.reposition(this, <?php echo $group->id; ?>,'<?php echo JText::_("COM_COMMUNITY_CANCEL_BUTTON")?>','<?php echo JText::_("COM_COMMUNITY_SAVE_BUTTON")?>');"><i class="joms-icon-cog"></i><?php echo JText::_('COM_COMMUNITY_REPOSITION_COVER')?></a>
          <a href="javascript:void(0)" class="btn ml-6" data-cover-context="group" onclick="joms.cover.select(this, <?php echo $group->id; ?>);" ><i class="joms-icon-picture"></i><?php echo JText::_('COM_COMMUNITY_CHANGE_COVER'); ?></a>
        </div>
      <?php }?>

        <!-- Focus Title , Add as friend button -->
        <div class="js-focus-header">
          <div class="row-fluid">
            <div class="span5 offset3">
              <h3><?php echo $group->name; ?></h3>
            </div>
            <div class="span4 text-right">
              <!-- invite friend button -->
              <?php if($isMember) { ?>
                <div class="btn btn-primary" onclick="joms.invitation.showForm(null,'groups,inviteUsers','<?php echo $group->id;?>',1,1)"><i class="js-icon-user-add"></i><?php echo JText::_('COM_COMMUNITY_INVITE_FRIENDS'); ?></div>
              <?php } else { ?>
                <div class="btn btn-primary" onclick="joms.groups.join(<?php echo $group->id;?>)"><i class="js-icon-user-add"></i><?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN'); ?></div>
              <?php }?>
            </div>
          </div>
        </div>


  </div>
  <div class="js-focus-content">
    <div class="row-fluid">

      <div class="span3">
        <div class="thumbnail js-focus-avatar">
            <img src="<?php echo $group->getAvatar( 'avatar' ) . '?_=' . time(); ?>" border="0" alt="<?php echo $this->escape($group->name);?>" />
            <?php if ($isAdmin || $isSuperAdmin || $isMine) { ?>
            <b class="js-focus-avatar-option">
              <a href="javascript:void(0)" onclick="joms.photos.uploadAvatar('group','<?php echo $group->id?>')"><?php echo JText::_('COM_COMMUNITY_CHANGE_AVATAR')?></a>
            </b>
          <?php } ?>
        </div>
      </div>

      <div class="span9">
        <!-- Focus Menu -->
        <div class="js-focus-menu">
          <div class="row-fluid">
            <div class="span12">
              <ul class="inline unstyled">
                <li><a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewmembers&groupid='.$group->id)?>"><?php echo ( $membersCount == 1) ?  JText::sprintf('COM_COMMUNITY_GROUPS_MEMBER_COUNT',$membersCount) : JText::sprintf('COM_COMMUNITY_GROUPS_MEMBER_COUNT_MANY',$membersCount) ; ?></a></li>
                <?php if($config->get('creatediscussion')) {?>
                  <li><a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussions&groupid='.$group->id)?>"><?php echo ( $totalDiscussion <= 1) ?  JText::sprintf('COM_COMMUNITY_GROUPS_DISCUSSION_COUNT',count($discussions)) : JText::sprintf('COM_COMMUNITY_GROUPS_DISCUSSION_COUNT_MANY',$totalDiscussion); ?></a></li>
                <?php }?>
                <?php if($config->get('createannouncement')){?>
                  <li><a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewbulletins&groupid='.$group->id)?>"><?php echo ( count($bulletins) == 1 ) ?  JText::sprintf('COM_COMMUNITY_GROUPS_ANNOUNCEMENT_COUNT',count($bulletins)) : JText::sprintf('COM_COMMUNITY_GROUPS_ANNOUNCEMENT_COUNT_MANY',count($bulletins)); ?></a></li>
                <?php }?>
                <?php if($showPhotos){ ?>
                  <li><a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=display&groupid='.$group->id)?>"><?php echo ( $totalPhotos == 1 ) ?  JText::sprintf('COM_COMMUNITY_PHOTOS_COUNT_SINGULAR',$totalPhotos) : JText::sprintf('COM_COMMUNITY_PHOTOS_COUNT',$totalPhotos); ?></a></li>
                <?php }?>
                <?php if($showVideos){ ?>
                  <li><a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&groupid='.$group->id);?>"><?php echo ( $totalVideos == 1 ) ?  JText::sprintf('COM_COMMUNITY_VIDEOS_COUNT',$totalVideos) : JText::sprintf('COM_COMMUNITY_VIDEOS_COUNT_MANY',$totalVideos) ; ?></a></li>
                <?php }?>
                <?php if($showEvents){?>
                  <li><a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=dispaly&groupid='.$group->id)?>"><?php echo ( $totalEvents == 1) ?  JText::sprintf('COM_COMMUNITY_EVENTS_COUNT',$totalEvents) : JText::sprintf('COM_COMMUNITY_EVENTS_COUNT_MANY',$totalEvents) ; ?></a></li>
                <?php }?>
                <?php if( (($isAdmin) || ($isMine) || ($isMember && !$isBanned)) && $isFile) { ?>
				  <li><a href="javascript:void(0)" onClick="joms.file.viewFile('group',<?php echo $group->id?>)"><?php echo JText::_('COM_COMMUNITY_FILES_VIEW_GROUP')?></a></li>
				<?php } ?>
              </ul>
            </div>
          </div>
        </div>
        <!-- Focus Details expand/collapse -->
        <div class="js-focus-details collapse">
          <div class="row-fluid">
            <div class="span12">
              <p><?php //echo $group->description; ?></p>
              <dl class="dl-horizontal">
                <dt><?php echo JText::_('COM_COMMUNITY_GROUPS_CATEGORY'); ?></dt>
                <dd><a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=display&categoryid=' . $group->categoryid);?>"><?php echo JText::_( $group->getCategoryName() ); ?></a></dd>
                <dt><?php echo JText::_('COM_COMMUNITY_GROUPS_CREATE_TIME');?></dt>
                <dd><?php echo JHTML::_('date', $group->created, JText::_('DATE_FORMAT_LC')); ?></dd>
                <dt><?php echo JText::_('COM_COMMUNITY_GROUPS_ADMINS');?></dt>
                <dd><?php echo $adminsList;?></dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div> <!-- end js-focus-content -->
  <div class="js-focus-actions">

    <div class="navbar">
      <div class="navbar-inner">
          <a class="btn btn-navbar js-collapse-btn">
            <span class="caret"></span>
          </a>
          <div class="nav-collapse collapse js-collapse">
            <ul class="nav">
          <?php if ($config->get('enablesharethis')  == 1 ) { ?>
            <li><a href="javascript:void(0);" onClick="joms.bookmarks.show('<?php echo CRoute::getExternalURL( 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id ); ?>')"><i class="joms-icon-share"></i><?php echo JText::_('COM_COMMUNITY_SHARE')?></a></li>
          <?php } ?>
          <?php if($isLikeEnabled){?>
            <li id='like-groups-<?php echo $group->id; ?>'><a href="javascript:void(0);" onclick="<?php echo ($isUserLiked) == 1 ? 'joms.like.newDislike(this)' : 'joms.like.newLike(this)' ?>" class="<?php if($isUserLiked == 1){ ?> js-focus-like <?php }?>"><i class="joms-icon-thumbs-up"></i><span><?php echo $totalLikes; ?></span> <?php echo JText::_('COM_COMMUNITY_LIKE')?></a></li>
          <?php }?>
          <li><span><i class="joms-icon-eye"></i><?php echo ($group->hits > 1) ? JText::sprintf('COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY',$group->hits) : JText::sprintf('COM_COMMUNITY_VIDEOS_HITS_COUNT',$group->hits); ?></span></li>
          <?php if(($config->get('enablereporting') == 1 && $my->id != 0) || ( $config->get('enablereporting') == 1 && $config->get('enableguestreporting') == 1 && $my->id == 0)){?>
            <li><a href="javascript:void(0);" onclick="joms.report.emptyMessage = '<?php echo JText::_('COM_COMMUNITY_REPORT_MESSAGE_CANNOT_BE_EMPTY'); ?>';joms.report.showWindow('groups,reportGroup','[<?php echo $group->id ?>]');" ><i class="js-icon-warning-sign"></i><?php echo JText::_('COM_COMMUNITY_REPORT_GROUP'); ?></a></li>
              <?php }?>
            </ul>
            <?php if($isMember && !$isBanned || $isSuperAdmin){?>
            <ul class="nav pull-right">
              <li class="dropup">
                <a href="#" class="js-navbar-options"><?php echo JText::_('COM_COMMUNITY_GROUP_OPTIONS')?></a>
                <ul class="dropdown-menu pull-right">
                <li><a tabindex="-1" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=adddiscussion&groupid=' . $group->id );?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_CREATE'); ?></a></li>
            <!-- Show events -->
            <?php if($showEvents){?>
              <?php if ( ($eventPermission ==  1 && $isAdmin) || ($isMember == 2 && $isAdmin) ) { ?>
                <li><a tabindex="-1" href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=create&groupid=' . $group->id);?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_CREATE_EVENT'); ?></a></li>
                <li><a tabindex="-1" href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=import&groupid=' . $group->id);?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_IMPORT_EVENT'); ?></a></li>
              <?php } ?>
            <?php } ?>
                  <?php if( ($isMember) && (!$isMine) && !($waitingApproval) && (COwnerHelper::isRegisteredUser()) ) { ?>
                <li><a tabindex="-1" href="javascript:void(0);" onclick="joms.groups.leave('<?php echo $group->id;?>');"><?php echo JText::_('COM_COMMUNITY_GROUPS_LEAVE');?></a></li>
                  <?php } ?>

                <?php if($isAdmin || $isSuperAdmin || $isMine) { ?>
                  <!-- Change cover button -->
                  <li class="hidden-desktop">
                      <a href="javascript:void(0)" class="<?php echo ($group->defaultCover) ? 'hidden' : '' ?>" data-cover-context="group" onclick="joms.cover.reposition(this, <?php echo $group->id; ?>,'<?php echo JText::_("COM_COMMUNITY_CANCEL_BUTTON")?>','<?php echo JText::_("COM_COMMUNITY_SAVE_BUTTON")?>');"><?php echo JText::_('COM_COMMUNITY_REPOSITION_COVER')?></a>
                  </li>
                  <li class="hidden-desktop">
                    <a href="javascript:void(0)" data-cover-context="group" onclick="joms.cover.select(this, <?php echo $group->id; ?>);" > <?php echo JText::_('COM_COMMUNITY_CHANGE_COVER'); ?></a>
                  </li>
                    <li class="divider"></li>
                <?php }?>

            <!-- Show photos -->
            <?php if($showPhotos){?>
              <?php if ( ($photoPermission ==  1 && $isAdmin) || ($isMember == 2 && $isAdmin) ) { ?>
                <li class="visible-desktop"><a tabindex="-1" href="javascript:void(0);" onclick="joms.notifications.showUploadPhoto('','<?php echo $group->id; ?>'); return false;" ><?php echo JText::_('COM_COMMUNITY_PHOTOS_UPLOAD_PHOTOS'); ?></a></li>
                <li><a tabindex="-1" href="<?php echo CRoute::_('index.php?option=com_community&view=photos&groupid=' . $group->id . '&task=newalbum');?>"><?php echo JText::_('COM_COMMUNITY_PHOTOS_CREATE_ALBUM_BUTTON'); ?></a></li>
              <?php } ?>
            <?php } ?>
            <!-- Show videos -->
                  <?php if($showVideos){?>
              <?php if ( ($videoPermission ==  1 && $isAdmin) || ($isMember == 2 && $isAdmin) ) { ?>
                    <li><a tabindex="-1" href="javascript:void(0);" onclick="joms.videos.addVideo('<?php echo VIDEO_GROUP_TYPE; ?>', '<?php echo $group->id; ?>')"><?php echo JText::_('COM_COMMUNITY_VIDEOS_ADD'); ?></a></li>
              <?php } ?>
                  <?php }?>
                  <li><a tabindex="-1" href="javascript:void(0);" onclick="joms.invitation.showForm(null,'groups,inviteUsers','<?php echo $group->id;?>',1,1)"><?php echo JText::_('COM_COMMUNITY_INVITE_FRIENDS'); ?></a></li>
                  <?php if( $isMine || $isSuperAdmin || $isAdmin ){?>
                    <li class="divider"></li>
                    <li><a tabindex="-1" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=edit&groupid=' . $group->id );?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_EDIT'); ?></a></li>
                    <li><a tabindex="-1" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=sendmail&groupid=' . $group->id );?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_SENDMAIL'); ?></a></li>
                    <li><a tabindex="-1" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=addnews&groupid=' . $group->id );?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_BULLETIN_CREATE'); ?></a></li>
                    <?php if($isSuperAdmin){?>
                      <li><a tabindex="-1" href="javascript:void(0);" onclick="javascript:joms.groups.unpublish('<?php echo $group->id;?>');"><?php echo JText::_('COM_COMMUNITY_GROUPS_UNPUBLISH'); ?></a></li>
                    <?php }?>
                    <li class="divider"></li>
                    <li><a tabindex="-1" href="javascript:void(0)" onclick="javascript:joms.groups.deleteGroup('<?php echo $group->id;?>');"><?php echo JText::_('COM_COMMUNITY_GROUPS_DELETE_GROUP_BUTTON'); ?></a></li>
                  <?php }?>
              </ul>
              </li>
            </ul>
            <?php }?>
          </div><!-- /.nav-collapse -->
      </div><!-- /navbar-inner -->
    </div>

  </div>
</div>

  <!-- Waiting Approval -->
  <?php if( $waitingApproval ) { ?>
  <div class="cAlert alert-info">
    <i class="com-icon-waiting"></i>
    <span><?php echo JText::_('COM_COMMUNITY_GROUPS_APPROVAL_PENDING'); ?></span>
  </div>
  <?php }?>

  <?php if($isInvited){ ?>
  <div id="groups-invite-<?php echo $group->id; ?>" class="cInvite cAlert alert-info">

    <div class="cInvite-Message">
      <?php echo JText::sprintf( 'COM_COMMUNITY_GROUPS_YOU_INVITED', $join); ?>
    </div>
    <div class="cInvite-Relations">
      <p><?php echo JText::sprintf( (CStringHelper::isPlural($friendsCount)) ? 'COM_COMMUNITY_GROUPS_FRIEND' : 'COM_COMMUNITY_GROUPS_FRIEND_MANY', $friendsCount ); ?></p>
    </div>

    <div class="cInvite-Actions">
      <a href="javascript:void(0);" onclick="jax.call('community','groups,ajaxRejectInvitation','<?php echo $group->id; ?>');" class="btn">
        <?php echo JText::_('COM_COMMUNITY_EVENTS_REJECT'); ?>
      </a>
      <a href="javascript:void(0);" onclick="jax.call('community','groups,ajaxAcceptInvitation','<?php echo $group->id; ?>');" class="btn btn-primary">
        <?php echo JText::_('COM_COMMUNITY_EVENTS_ACCEPT'); ?>
      </a>
    </div>
  </div>
  <?php } ?>

<div class="row-fluid">

  <div class="span8">
    <!-- begin: .cMain -->
    <div class="cMain">
      <!-- Application Tabs' Toolbar -->
      <div class="cTabsBar clearfull">
        <ul class="cPageTabs cResetList cFloatedList clearfix">
          <li <?php if($isMember || $isSuperAdmin || !$config->get('lockgroupwalls')) {echo 'class="cTabCurrent"';} else if($isPrivate){echo 'class="cTabDisabled"';} ?>>
            <a href="javascript:void(0)"><?php echo JText::_('COM_COMMUNITY_FRONTPAGE_RECENT_ACTIVITIES');?><?php if($alertNewStream){ echo '<span></span>'; } ?></a>
          </li>
          <li <?php if(!$isMember && !$isSuperAdmin && $config->get('lockgroupwalls')) {echo 'class="cTabCurrent"';} ?> >
            <a href="javascript:void(0)"><?php echo JText::_('COM_COMMUNITY_GROUPS_DESCRIPTION');?></a>
          </li>
          <?php if($config->get('createannouncement') && ($isMember || $isSuperAdmin)) { ?>
          <li <?php if($isPrivate && !$isMember && !$isSuperAdmin) {echo 'class="cTabDisabled"';} ?> >
            <a href="javascript:void(0)"><?php echo JText::_('COM_COMMUNITY_GROUPS_BULLETIN');?><?php if($alertNewBulletin){ echo '<span></span>'; } ?></a>
          </li>
          <?php } ?>
          <?php if($config->get('creatediscussion') ){?>
          <li <?php if(!$isMember && !$isSuperAdmin && $isPrivate) { echo 'class="cTabDisabled"'; } ?> >
            <a href="javascript:void(0)"><?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION');?><?php if($alertNewDiscussion){ echo '<span></span>'; } ?></a>
          </li>
        </ul> <?php }?>
      </div>
      <!-- Application Tabs' Toolbar End -->

      <!-- Application Tabs's Content -->
      <div class="cTabsContentWrap">
        <!-- Tab Content : Activity Stream -->
        <?php
          if( $group->approvals=='0' || $isMine || ($isMember && !$isBanned) || $isSuperAdmin )
          {
        ?>
        <div class="cTabsContent  <?php if($isMember || $isSuperAdmin || !$config->get('lockgroupwalls')) {echo 'cTabsContentCurrent';} ?>">
          <?php if($isMember || $isSuperAdmin || !$config->get('lockgroupwalls') ) { $status->render(); } ?>
          <div class="cActivity cGroup-Activity" id="activity-stream-container">
            <div class="cActivity-LoadLatest joms-latest-activities-container">
              <a id="activity-update-click" class="btn btn-block" href="javascript:void(0);"></a>
            </div>

            <?php echo $streamHTML; ?>
          </div>
        </div>
        <?php
          }
        ?>
        <!-- Tab Content : Activity Stream -->

        <!-- Tab Content : Description -->
        <div class="cTabsContent <?php if(!$isMember && !$isSuperAdmin && $config->get('lockgroupwalls')) {echo 'cTabsContentCurrent';} ?>">
          <div class="cGroup-Description">
            <?php echo $group->description; ?>
            <dl>
                <dt><?php echo JText::_('COM_COMMUNITY_GROUPS_CATEGORY'); ?></dt>
                <dd><a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=display&categoryid=' . $group->categoryid);?>"><?php echo JText::_( $group->getCategoryName() ); ?></a></dd>
                <dt><?php echo JText::_('COM_COMMUNITY_GROUPS_CREATE_TIME');?></dt>
                <dd><?php echo JHTML::_('date', $group->created, JText::_('DATE_FORMAT_LC')); ?></dd>
                <dt><?php echo JText::_('COM_COMMUNITY_GROUPS_ADMINS');?></dt>
                <dd><?php echo $adminsList;?></dd>
            </dl>
          </div>
        </div>
        <!-- Tab Content : Description -->

        <!-- Tab Content : Announcements -->
        <?php
          if( $group->approvals=='0' || $isMine || ($isMember && !$isBanned) || $isSuperAdmin )
          {
            if( $config->get('createannouncement') && $isMember)
            {
        ?>
        <div class="cTabsContent">

          <div class="cGroup-Announcments">
            <?php echo $bulletinsHTML; ?>

            <div class="cUpdatesHelper small clearfull">
              <span class="updates-options cFloat-R">
                <?php if( $isAdmin || $isSuperAdmin ): ?>
                <a class="app-box-action" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=addnews&groupid=' . $group->id );?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_BULLETIN_CREATE');?></a>
                <?php endif; ?>

                <?php if( $bulletins ): ?>
                <a class="app-box-action" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewbulletins&groupid=' . $group->id);?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_BULLETIN_VIEW_ALL');?></a>
                <?php endif; ?>
              </span>
              <span class="updates-pagination"><?php if (count($bulletins)>1) {echo JText::sprintf( 'COM_COMMUNITY_GROUPS_BULLETIN_COUNT_OF' , count($bulletins) , $totalBulletin );} ?></span>
            </div>
          </div>

        </div>
        <?php
            }
          }
        ?>
        <!-- Tab Content : Announcements -->

        <!-- Tab Content : Discussions -->
        <?php
          if( $group->approvals=='0' || $isMine || ($isMember && !$isBanned) || $isSuperAdmin )
          {
            if( $config->get('creatediscussion') )
            {
        ?>
        <div class="cTabsContent">
          <div class="cGroup-Discussions">

            <?php echo $discussionsHTML; ?>

            <div class="cUpdatesHelper small clearfull">
              <span class="updates-options cFloat-R">
                <?php if( ($isMember && !$isBanned) && !($waitingApproval) || $isSuperAdmin): ?>
                <a class="app-box-action" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&groupid=' . $group->id . '&task=adddiscussion');?>">
                  <?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_CREATE');?>
                </a>
                <?php endif; ?>
                <?php if ( $discussions ): ?>
                <a class="app-box-action" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussions&groupid=' . $group->id );?>">
                  <?php echo JText::_('COM_COMMUNITY_GROUPS_VIEW_ALL_DISCUSSIONS');?>
                </a>
                <?php endif; ?>
              </span>
              <span class="updates-pagination"><?php if (count($discussions)>1) { echo JText::sprintf( 'COM_COMMUNITY_GROUPS_DISCUSSION_COUNT_OF' , count($discussions) , $totalDiscussion );} ?></span>
            </div>
          </div>
        </div>
        <?php
            }
          }
        ?>
        <!-- Tab Content : Discussions -->
      </div>
      <!-- Application Tabs's Content -->
    </div>
    <!-- end: .cMain -->
  </div>

  <div class="span4">
    <!-- begin: .cSidebar -->
    <div class="cSidebar">

    <?php $this->renderModules( 'js_side_top' ); ?>
    <?php $this->renderModules( 'js_groups_side_top' ); ?>

      <!-- Group's Approval -->
      <?php if( ( $isMine || $isAdmin || $isSuperAdmin) && ( $unapproved > 0 ) ) { ?>
      <div class="cModule cPage-Approval app-box control-approval">
        <ul class="app-box-list for-menu cResetList">
          <li>
            <i class="com-icon-user-plus"></i>
            <a class="friend" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewmembers&approve=1&groupid=' . $group->id);?>">
              <?php echo JText::sprintf((CStringHelper::isPlural($unapproved)) ? 'COM_COMMUNITY_GROUPS_APPROVAL_NOTIFICATION_MANY'  :'COM_COMMUNITY_GROUPS_APPROVAL_NOTIFICATION' , $unapproved ); ?>
            </a>
          </li>
        </ul>
      </div>
      <?php } ?>
      <!-- Group's Approval -->

      <!-- Group's Module' -->
      <?php if( $group->approvals=='0' || $isMine || ($isMember && !$isBanned) || $isSuperAdmin ) { ?>

      <!-- Group's Members @ Sidebar -->
      <?php if($members){ ?>
      <div class="cModule cGroups-Members app-box">
        <h3 class="app-box-header"><?php echo JText::sprintf('COM_COMMUNITY_GROUPS_MEMBERS'); ?></h3>

        <div class="app-box-content">
          <ul class="cThumbsList cResetList clearfix">
          <?php foreach($members as $member) { ?>
            <li>
              <a href="<?php echo CUrlHelper::userLink($member->id); ?>">
                <img border="0" class="cAvatar jomNameTips" src="<?php echo $member->getThumbAvatar(); ?>" title="<?php echo CTooltip::cAvatarTooltip($member);?>" alt="<?php echo CTooltip::cAvatarTooltip($member);?>" />
              </a>
            </li>
          <?php if(--$limit < 1) break; } ?>
          </ul>
        </div>

        <div class="app-box-footer">
          <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewmembers&groupid=' . $group->id);?>">
            <?php echo JText::_('COM_COMMUNITY_VIEW_ALL');?> (<?php echo $membersCount; ?>)
          </a>
        </div>
      </div>
      <?php } ?>
      <!-- Group's Members @ Sidebar -->

      <!-- Group Events @ Sidebar -->
      <?php if( $showEvents ){ ?>
      <div class="cModule app-box">
        <h3 class="app-box-header"><?php echo JText::_('COM_COMMUNITY_EVENTS');?></h3>

        <div class="app-box-content">
          <?php if( $events ){ ?>
          <ul class="cThumbDetails cResetList">
          <?php
          foreach( $events as $event )
          {
            $creator      = CFactory::getUser($event->creator);
          ?>
            <li>
              <b class="cThumb-Calendar cFloat-L">
                <b><?php echo CEventHelper::formatStartDate($event, JText::_('M') ); ?></b>
                <b><?php echo CEventHelper::formatStartDate($event, JText::_('d') ); ?></b>
              </b>
              <div class="cThumb-Detail">
                <div class="event-detail">
                  <a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id.'&groupid=' . $group->id);?>" class="cThumb-Title">
                    <?php echo $event->title;?>
                  </a>
                  <div class="cThumb-Location">
                    <?php // echo $event->getCategoryName();?>
                    <?php echo $event->location;?>
                  </div>
                  <!-- <div class="eventTime"><?php echo JText::sprintf('COM_COMMUNITY_EVENTS_DURATION', JHTML::_('date', $event->startdate, JText::_('DATE_FORMAT_LC2') ), JHTML::_('date', $event->enddate, JText::_('DATE_FORMAT_LC2') )); ?></div> -->
                  <div class="cThumb-Members small">
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewguest&groupid=' . $group->id . '&eventid=' . $event->id . '&type='.COMMUNITY_EVENT_STATUS_ATTEND);?>"><?php echo JText::sprintf((!CStringHelper::isSingular($event->confirmedcount)) ? 'COM_COMMUNITY_EVENTS_MANY_GUEST_COUNT':'COM_COMMUNITY_EVENTS_GUEST_COUNT', $event->confirmedcount);?></a>
                  </div>
                </div>
              </div>
            </li>
          <?php } ?>
          </ul>
          <?php } else { ?>
          <div class="cEmpty"><?php echo JText::_('COM_COMMUNITY_EVENTS_NOT_CREATED');?></div>
          <?php } ?>
        </div>
        <div class="app-box-footer">
          <a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=display&groupid=' . $group->id );?>">
            <?php echo JText::_('COM_COMMUNITY_EVENTS_ALL_EVENTS');?>
          </a>
        </div>
      </div>
      <?php } ?>
      <!-- Group Events @ Sidebar -->

      <!-- Group Photo @ Sidebar -->
      <?php if( $showPhotos ){ ?>
      <?php // if($this->params->get('groupsPhotosPosition') == 'js_groups_side_bottom'){ ?>
      <?php if( $albums ) { ?>
      <div class="cModule cGroupPhotos app-box">
        <h3 class="app-box-header"><?php echo JText::_('COM_COMMUNITY_PHOTOS_PHOTO_ALBUMS');?></h3>

        <div class="app-box-content">
          <ul class="unstyled clearfix js-col-layout">
            <?php foreach($albums as $album ) { ?>
            <li class="js-col2">
              <a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=album&albumid=' . $album->id . '&groupid=' . $group->id);?>">
                <img class="cAvatar cMediaAvatar jomNameTips" title="<?php echo $this->escape( $album->name );?>" src="<?php echo $album->getCoverThumbURI();?>" alt="<?php echo $album->getCoverThumbURI();?>" />
              </a>
            </li>
            <?php } ?>
          </ul>
        </div>

        <div class="app-box-footer">
          <a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=display&groupid=' . $group->id );?>">
            <?php echo JText::_('COM_COMMUNITY_VIEW_ALL_ALBUMS').' ('.$totalAlbums.')';?>
          </a>
        </div>
      </div>
      <?php } ?>
      <?php // } ?>
      <?php } ?>
      <!-- Group Photo @ Sidebar -->

      <!-- Group Video @ Sidebar -->
      <?php if( $showVideos ){ ?>
      <?php // if($this->params->get('groupsVideosPosition') == 'js_groups_side_bottom'){ ?>
      <?php if($videos) { ?>
      <div class="cModule app-box">
        <h3 class="app-box-header cResetH"><?php echo JText::_('COM_COMMUNITY_VIDEOS');?></h3>

        <div class="app-box-content">
          <div class="row-fluid js-mod-video">
            <?php foreach( $videos as $video ) { ?>
            <div class="span6 bottom-gap">
              <a class="cVideo-Thumb jomNameTips" href="<?php echo $video->getURL(); ?>" title="<?php echo $video->title; ?>">
                <img src="<?php echo $video->getThumbnail(); ?>" class="cAvatar cMediaAvatar" />
                <b class="cVideo-Duration"><?php echo $video->getDurationInHMS(); ?></b>
              </a>
            </div>
            <?php } ?>
          </div>
        </div>

        <div class="app-box-footer">
          <a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&groupid='.$group->id); ?>">
            <?php echo JText::_('COM_COMMUNITY_VIDEOS_ALL').' ('.$totalVideos.')'; ?>
          </a>
        </div>
      </div>
      <?php } ?>
      <?php } ?>
    <?php } ?>

    <?php $this->renderModules( 'js_groups_side_bottom' ); ?>
    <?php $this->renderModules( 'js_side_bottom' ); ?>

    </div>
    <!-- end: .cSidebar -->
  </div>

</div>



<?php if($editGroup) {?>
<script type="text/javascript">
  joms.groups.edit();
</script>
<?php } ?>

<script>

  // When people are viewing the 'discussion' page longer than 5 seconds,'
  jomsQuery('#community-group-dicussion').parent().bind('onAfterShow', function() {
    jax.call('community', 'groups,ajaxUpdateCount', 'discussion', <?php echo $group->id; ?> );
  });

  // When people are viewing the 'discussion' page longer than 5 seconds,'
  jomsQuery('#community-group-news').parent().bind('onAfterShow', function() {
    jax.call('community', 'groups,ajaxUpdateCount', 'bulletin', <?php echo $group->id; ?> );
  });

  // expand / collapse about section
  jomsQuery(".btn-about").click(function () {
    jomsQuery(".cFocus-definition").slideToggle();
  });

  // override config setting
  joms || (joms = {});
  joms.constants || (joms.constants = {});
  joms.constants.conf || (joms.constants.conf = {});

  joms.constants.groupid                  = <?php echo $group->id; ?>;
  joms.constants.conf.enablephotos        = <?php echo (isset($showPhotos) && $showPhotos == 1 && (( $isAdmin && $photoPermission == 1 ) || ($isMember && $photoPermission == 2) ) ) ? 1 : 0 ; ?>;
  joms.constants.conf.enablevideos        = <?php echo (isset($showVideos) && $showVideos == 1 && (( $isAdmin && $videoPermission == 1 ) || ($isMember && $videoPermission == 2) ) ) ? 1 : 0 ; ?>;
  joms.constants.conf.enablevideosupload  = 0;
  joms.constants.conf.enableevents        = <?php echo (isset($showEvents) && $showEvents == 1 && (( $isAdmin && $eventPermission == 1 ) || ($isMember && $eventPermission == 2) ) ) ? 1 : 0 ; ?>;

</script>
