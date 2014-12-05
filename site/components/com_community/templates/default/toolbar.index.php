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
$viewName = JRequest::getCmd( 'view');
$taskName = JRequest::getCmd( 'task');
// call the auto refresh on specific page
?>
<?php if ($menuParams != '' && $menuParams->get('show_page_heading') != 0) : ?>
<div class="page-header">
	<h3><?php echo $this->escape($menuParams->get('page_title')); ?></h3>
</div>
<?php endif;?>
<?php if($showToolbar) : ?>

<style type="text/css">
	/*@TEMPORARY*/
	#community-wrap .row-fluid [class*="span"] {
		float: left;
	}
</style>

<div class="navbar js-toolbar">
  <div class="navbar-inner">
      <a class="btn btn-navbar js-bar-collapse-btn">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>
      	<ul class="nav hidden-desktop">
        	<li <?php echo $active == 0 ? ' class="active"' :'';?> ><a href="<?php echo CRoute::_( 'index.php?option=com_community&view=frontpage' );?>">
        		<i class="joms-icon-home"></i></a>
					</li>
					<li>
						<a href="javascript:joms.notifications.showWindow();" title="<?php echo JText::_( 'COM_COMMUNITY_NOTIFICATIONS_GLOBAL' );?>">
							<i class="joms-icon-globe"></i>
							<?php if( $newEventInviteCount ) { ?>
							<span class="js-counter"><?php echo $newEventInviteCount; ?></span>
							<?php } ?>
						</a>
					</li>

					<li>
						<a href="<?php echo CRoute::_( 'index.php?option=com_community&view=friends&task=pending' );?>" onclick="joms.notifications.showRequest();return false;" title="<?php echo JText::_( 'COM_COMMUNITY_NOTIFICATIONS_INVITE_FRIENDS' );?>">
							<i class="joms-icon-user"></i>
							<?php if( $newFriendInviteCount ){ ?><span class="js-counter"><?php echo $newFriendInviteCount; ?></span><?php } ?>
						</a>
					</li>
					<?php if($isMessageEnable) {?>
						<li>
							<a href="<?php echo CRoute::_( 'index.php?option=com_community&view=inbox' );?>"  onclick="joms.notifications.showInbox();return false;" title="<?php echo JText::_( 'COM_COMMUNITY_NOTIFICATIONS_INBOX' );?>">
								<i class="joms-icon-envelope-alt"></i>
								<?php if( $newMessageCount ){ ?><span class="js-counter"><?php echo $newMessageCount; ?></span><?php } ?>
							</a>
						</li>
					<?php }?>
					<li>
						<a href="javascript:void(0);" title="<?php echo JText::_('COM_COMMUNITY_LOGOUT'); ?>" onclick="document.getElementById('communitylogout2').submit();">
							<i class="joms-icon-exit"></i>
						</a>
					<form class="cForm" action="<?php echo JRoute::_('index.php');?>" method="post" name="communitylogout2" id="communitylogout2">
						<input type="hidden" name="option" value="<?php echo COM_USER_NAME ; ?>" />
						<input type="hidden" name="task" value="<?php echo COM_USER_TAKS_LOGOUT ; ?>" />
						<input type="hidden" name="return" value="<?php echo $logoutLink; ?>" />
						<?php echo JHtml::_('form.token'); ?>
					</form>
					</li>
				</ul>

      <div class="nav-collapse collapse js-bar-collapse">
        <ul class="nav">
        	<li class="<?php echo $active == 0 ? 'active' :'';?> visible-desktop" ><a href="<?php echo CRoute::_( 'index.php?option=com_community&view=frontpage' );?>">
        		<i class="joms-icon-home"></i></a>
			</li>

			<?php
			foreach( $menus as $menu ) {

				$dropdown	= !empty( $menu->childs ) ? 'dropdown' : '';
				$toggle = !empty( $menu->childs ) ? 'class="dropdown-toggle"' : '';
			?>

				<li class="<?php echo $active === $menu->item->id ? 'active' : '';?> <?php echo (isset($menu->item->css)) ? $menu->item->css : '' ; ?> <?php echo $dropdown; ?>" >
					<a href="<?php echo CRoute::_( $menu->item->link );?>" <?php echo $toggle; ?> ><?php echo JText::_( $menu->item->name );?></a>
					<?php if( !empty($menu->childs) ) { ?>
					<ul class="dropdown-menu">
						<?php foreach( $menu->childs as $child ) { ?>
		                    <li class="<?php echo (isset($child->css)) ? $child->css : ''; ?>">
								<?php if( $child->script ){ ?>
									<a href="javascript:void(0);" onclick="<?php echo $child->link;?>">
								<?php } else { ?>
									<a href="<?php echo CRoute::_( $child->link );?>">
								<?php } ?>
								<?php echo JText::_( $child->name );?></a>
							</li>
						<?php } ?>
					</ul>
					<?php } ?>
				</li>

			<?php } ?>


					<li class="visible-desktop" >
						<a class="menu-icon" href="javascript:joms.notifications.showWindow();" title="<?php echo JText::_( 'COM_COMMUNITY_NOTIFICATIONS_GLOBAL' );?>">
							<i class="joms-icon-globe"></i>
							<?php if( $newEventInviteCount ) { ?>
							<span class="js-counter"><?php echo $newEventInviteCount; ?></span>
							<?php } ?>
						</a>
					</li>


					<li class="visible-desktop" >
						<a class="menu-icon" href="<?php echo CRoute::_( 'index.php?option=com_community&view=friends&task=pending' );?>" onclick="joms.notifications.showRequest();return false;" title="<?php echo JText::_( 'COM_COMMUNITY_NOTIFICATIONS_INVITE_FRIENDS' );?>">
							<i class="joms-icon-user"></i>
							<?php if( $newFriendInviteCount ){ ?><span class="js-counter"><?php echo $newFriendInviteCount; ?></span><?php } ?>
						</a>
					</li>
					<?php if($isMessageEnable) {?>
						<li class="visible-desktop" >
							<a class="menu-icon" href="<?php echo CRoute::_( 'index.php?option=com_community&view=inbox' );?>"  onclick="joms.notifications.showInbox();return false;" title="<?php echo JText::_( 'COM_COMMUNITY_NOTIFICATIONS_INBOX' );?>">
								<i class="joms-icon-envelope-alt"></i>
								<?php if( $newMessageCount ){ ?><span class="js-counter"><?php echo $newMessageCount; ?></span><?php } ?>
							</a>
						</li>
					<?php }?>


        </ul>
        <ul class="nav pull-right">

			<li class="visible-desktop" >
				<a href="javascript:void(0);" title="<?php echo JText::_('COM_COMMUNITY_LOGOUT'); ?>" onclick="document.getElementById('communitylogout').submit();">
					<i class="joms-icon-exit"></i>
				</a>
			<form class="cForm" action="<?php echo JRoute::_('index.php');?>" method="post" name="communitylogout" id="communitylogout">
				<input type="hidden" name="option" value="<?php echo COM_USER_NAME ; ?>" />
				<input type="hidden" name="task" value="<?php echo COM_USER_TAKS_LOGOUT ; ?>" />
				<input type="hidden" name="return" value="<?php echo $logoutLink; ?>" />
				<?php echo JHtml::_('form.token'); ?>
			</form>
			</li>

        </ul>
      </div><!-- /.nav-collapse -->
  </div><!-- /navbar-inner -->
</div>

<?php endif; ?>

<?php if ( $miniheader ) : ?>
	<?php echo @$miniheader; ?>
<?php endif; ?>

<?php if ( !empty( $groupMiniHeader ) ) : ?>
	<?php echo $groupMiniHeader; ?>
<?php endif; ?>

<script>
	joms.jQuery(function() {
		var $collapsible = joms.jQuery('#js-collapse');

		$collapsible.collapse({
	        toggle: false
	    });

	    joms.jQuery('#js-collapse-btn').on('click',
	        function(){
	            $collapsible.collapse('toggle');
	        }
	    );
	});
</script>
