<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

$appName = explode('.', $act->app);
$appName = $appName[0];

/**
 * @todo Move $act into CActivity object from view
 */
$activity = new CActivity($act);
$permission = $activity->getPermission($my->id);

?>

<div class="joms-stream-options">
  <?php if($permission->showButton) {?>
  <button type="button" class="dropdown-toggle" data-value="" data-toggle="dropdown">
    <span class="dropdown-value"><i class="joms-icon-cog"></i></span>
    <span class="dropdown-caret joms-icon-caret-down"></span>
  </button>
  <?php }?>
  <ul class="dropdown-menu">
      <?php if($permission->hideStream){ ?>
      <li>
        <a href="#" data-action="hide" data-user-id="<?php echo $act->actor; ?>" data-stream-id="<?php echo $act->id; ?>" data-stream-type="<?php echo $appName; ?>">
          <i class="joms-icon-eye"></i><span><?php echo JText::_('COM_COMMUNITY_HIDE_ACTIVITY')?></span>
        </a>
      </li>
      <?php if($permission->ignoreStream){?>
      <li>
        <a href="#" data-action="ignore" data-user-id="<?php echo $act->actor; ?>" data-stream-id="<?php echo $act->id; ?>" data-stream-type="<?php echo $appName; ?>">
          <i class="joms-icon-minus-sign"></i><span><?php echo JText::_('COM_COMMUNITY_IGNORE_ACTIVITY')?></span>
        </a>
      </li>
      <?php } ?>
      <?php if($permission->editPost || $permission->deletePost){?>
      <li class="divider"></li>
      <?php }?>
      <?php }?>
    <!-- Edit Post -->
    <?php if($permission->editPost || $permission->deletePost){?>
    <?php if($permission->editPost) {?>
      <li>
        <a href="#" data-action="edit" data-stream-id="<?php echo $act->id; ?>" data-stream-type="<?php echo $appName; ?>">
          <i class="joms-icon-edit"></i><span><?php echo JText::_('COM_COMMUNITY_ACTIVITY_EDIT_POST')?></span>
        </a>
      </li>
    <?php }?>
    <!-- Delete Post -->
    <?php if( $permission->deletePost ) { ?>
      <li>
        <a href="#" data-action="delete" data-stream-id="<?php echo $act->id; ?>" data-stream-type="<?php echo $appName; ?>">
          <i class="joms-icon-remove"></i><span><?php echo JText::_('COM_COMMUNITY_ACTIVITY_DELETE_POST')?></span>
        </a>
      </li>
    <?php } ?>
    <?php }?>
    <!-- Edit Location -->
    <?php if($permission->deleteLocation) { ?>
    <li class="divider"></li>
    <li><a href="javascript:void(0);" onclick="joms.activities.editLocation(<?php echo $act->id?>)" data-action="edit location" data-stream-id="<?php echo $act->id ?>" >
      <i class="joms-icon-edit"></i><span><?php echo JText::_('COM_COMMUNITY_ACTIVITY_EDIT_LOCATION')?></span>
    </a></li>
    <?php } ?>
    <!-- Remove Location -->
    <?php if($permission->deleteLocation) { ?>
      <li>
        <a data-action="remove location" data-stream-id="<?php echo $act->id ?>" href="javascript:void(0);" onclick="joms.activities.removeLocation(<?php echo $act->id?>)" >
          <i class="joms-icon-remove"></i><span><?php echo JText::_('COM_COMMUNITY_ACTIVITY_DELETE_LOCATION')?></span>
        </a>
      </li>
    <?php } ?>
    <?php if($permission->deleteMood) {?>
      <!-- Remove Mood -->
      <li><a data-action="remove-mood" data-stream-id="<?php echo $act->id?>" href="#" >
        <i class="joms-icon-remove"></i><span><?php echo JText::_('COM_COMMUNITY_ACTIVITY_REMOVE_MOOD')?></span>
      </a></li>
    <?php }?>
    <?php if( CActivitiesHelper::hasTag($my->id, $act->title) ) { ?>
      <!-- Remove Tag -->
      <li><a data-action="remove-tag" data-stream-id="<?php echo $act->id ?>" href="javascript:" >
        <i class="joms-icon-remove"></i><span><?php echo JText::_('COM_COMMUNITY_ACTIVITY_REMOVE_TAG') ?></span>
      </a></li>
    <?php } ?>
  </ul>
</div>