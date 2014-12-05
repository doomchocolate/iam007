<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') OR DIE();
$moduleCount = count(JModuleHelper::getModules('js_side_frontpage')) + count(JModuleHelper::getModules('js_side_top'));
?>

<style type="text/css">
	/*@TEMPORARY*/
	#community-wrap .row-fluid [class*="span"] {
		float: left;
	}
</style>

<script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/ajaxfileupload.pack.js"></script>
<script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/imgareaselect/scripts/jquery.imgareaselect.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo JURI::root();?>components/com_community/assets/imgareaselect/css/imgareaselect-avatar.css" />

<script type="text/javascript"> joms.filters.bind();</script>
<script type="text/javascript">
    jQuery(document).ready(function(){
        if(jQuery('#community-wrap').find('> .js-toolbar').length){
            jQuery('#cProfileWrapper .cMain').prepend(jQuery('#community-wrap').find('> .js-toolbar'));
        }
    });
</script>

<!-- begin: .cLayout -->
<div id="cProfileWrapper" class="row-fluid cPage cProfile <?php if(!$isMine) { echo 'cProfileOther';} ?>">

	<!-- begin: .cFocus -->
	<?php $this->view('profile')->modProfileUserinfo(); ?>
	<!-- end: .cFocus -->

	<div id="editLayout-stop" class="page-action" style="display: none;">
		<a onclick="joms.editLayout.stop()" href="javascript: void(0)"><?php echo JText::sprintf('COM_COMMUNITY_STOP_EDIT_PROFILE_APPS_LAYOUT') ?></a>
	</div>

	<?php $this->renderModules( 'js_profile_top' ); ?>
	<?php if($isMine) $this->renderModules( 'js_profile_mine_top' ); ?>

    <div class="cContent">
        <div class="row-fluid">
            <div class="span9">
                <!-- begin: .cMain -->
                <div class="cMain">

                    <?php $this->renderModules( 'js_profile_feed_top' ); ?>
                    <div class="activity-stream-front">

                        <?php
                            $this->view('profile')->modProfileUserstatus();
                                                    if ( JFactory::getUser()->guest != 1 ) {
                                            ?>
                        <div class="joms-latest-activities-container" data-actid="<?php echo JFactory::getApplication()->input->get('actid', -1, 'INT'); ?>" style="display:none;">
                            <a id="activity-update-click" class="btn btn-block" href="javascript:void(0);"></a>
                        </div>
                        <?php } ?>
                        <div class="activity-stream-profile">
                            <div id="activity-stream-container">
                            <?php echo $newsfeed; ?>
                            </div>
                        </div>

                        <?php $this->renderModules( 'js_profile_feed_bottom' ); ?>
                        <div id="apps-sortable" class="connectedSortable" >
                        <?php
                            $this->view('profile')->modProfileActivities();
                        ?>
                        <?php echo $content; ?>
                        </div>
                    </div>
                </div>
                <!-- end: .cMain -->
            </div>
            <div class="span3">
                <!-- begin: .cSidebar -->
                <div class="cSidebar">
                    <?php

                        $this->renderModules( 'js_side_top' );
                        $this->renderModules( 'js_profile_side_top' );
                        echo $sidebarTop;

                        if($isMine) $this->renderModules( 'js_profile_mine_side_top' );

                        echo $this->view('profile')->modProfileUserVideo();
                        echo $this->view('profile')->modGetFriendsHTML();

                        if( $config->get('enablegroups')){
                            echo $this->view('profile')->modGetGroupsHTML();
                        }

                        if($isMine) $this->renderModules( 'js_profile_mine_side_bottom' );

                        echo $sidebarBottom;
                        $this->renderModules( 'js_profile_side_bottom' );
                        $this->renderModules( 'js_side_bottom' );
                    ?>

                    
                </div>
                <!-- end: .cSidebar -->
            </div>
        </div>
    </div>

	<?php if($isMine) $this->renderModules( 'js_profile_mine_bottom' ); ?>
	<?php $this->renderModules( 'js_profile_bottom' ); ?>

</div>
<!-- end: .cLayout -->