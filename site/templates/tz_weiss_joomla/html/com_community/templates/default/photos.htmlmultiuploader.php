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

?>

<?php if ($disableUpload) { ?>
    <div style="width:100%;text-align:center;height:2em;"><?php echo $preMessage;?></div>
<?php } else { ?>
    <link rel="stylesheet" href="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/jquery.plupload.queue/css/jquery.plupload.queue.css" type="text/css" media="screen" />
    <script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/browserplus-min.js"></script>
    <script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/plupload.js"></script>
    <script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/plupload.gears.js"></script>
    <script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/plupload.silverlight.js"></script>
    <script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/plupload.flash.js"></script>
    <script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/plupload.browserplus.js"></script>
    <script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/plupload.html4.js"></script>
    <script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/plupload.html5.js"></script>
    <script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/jquery.plupload.queue/jquery.plupload.queue.js"></script>

    <script>
        joms.jQuery(document).ready(function($){
            $(document).on("click", ".joms-album-privacy ul.dropdown-menu a", function(e) {
                e.preventDefault();
                var val = $(this).data('option-value');
                var icon = $(this).find('i').attr('class');
                //var html = $(this).html() + '<span class="dropdown-caret joms-icon-caret-down"></span>';
                $(this).parents('ul.dropdown-menu').siblings('input[name="joms-postbox-privacy"]').val(val);
                $(this).parents('ul.dropdown-menu').siblings('button').find('span.dropdown-value i').attr('class', icon);
                //$(this).parents('ul.dropdown-menu').siblings('button').html(html);
            });
        });
    </script>

    <script type="text/javascript">
        joms.jQuery('#clear').click(function(e) {
            e.preventDefault();
            joms.jQuery("#uploader").pluploadQueue().splice();
        });

        joms.jQuery(document).ready(function() {
            // Hide Create/Select Album, Add Files and Start Upload if Needs disable
            // e.g. when limit is reached
            var uploadMsg = {
                defaultMsg: '<?php echo addslashes(JText::_('COM_COMMUNITY_PHOTOS_DEFAULT_UPLOAD_NOTICE')); ?>',
                groupEmptyValidateMsg : '<?php echo addslashes(JText::_('COM_COMMUNITY_PHOTOS_ENTER_ALBUM_NAME')); ?>',
                uploadingCreateMsg: '<?php echo addslashes(JText::_('COM_COMMUNITY_PHOTOS_UPLOADING_TO_CREATED_ALBUM')); ?>',
                uploadingSelectMsg: '<?php echo addslashes(JText::_('COM_COMMUNITY_PHOTOS_UPLOADING_TO_SELECTED_ALBUM')); ?>',
                uploadedCompleteMsg: '<?php echo addslashes(JText::_('COM_COMMUNITY_PHOTOS_UPLOADED_COMPLETE_TO_ALBUM'));?>',
                maxFileSize:'<?php echo $maxFileSize;?>'
            };
            joms.photos.multiUpload._init('<?php echo $groupId; ?>', uploadMsg);

            joms.photos.multiUpload.defaultUploadUrl = '<?php echo CRoute::_('index.php?option=com_community&view=photos&task=multiUpload', false);?>';

            // Remove all tooptips
            joms.jQuery('#multi_uploader [title]').removeAttr('title');
            joms.jQuery('.plupload_header').remove();

            // Switch class for customized manipulation
            joms.jQuery('.plupload_buttons').addClass('custom_plupload_buttons');
            joms.jQuery('.custom_plupload_buttons').removeClass('plupload_buttons');
            joms.jQuery(".plupload_upload_status").addClass('custom_plupload_status');
            joms.jQuery(".custom_plupload_status").removeClass('plupload_upload_status');

            joms.photos.multiUpload.assignUploadUrl( joms.photos.multiUpload.getSelectedAlbumId() );

            <?php if(count($allAlbums)<1){ ?>
            joms.jQuery("#optional-album").hide();
            <?php } ?>

            <?php if(intval($selectedAlbum)!=0){ ?>
            joms.jQuery("#new-album").hide();
            joms.jQuery("#newalbum").hide();
            joms.jQuery("#select-album").css('display','inline');
            joms.jQuery("#albumid").val('<?php echo $selectedAlbum; ?>');
            <?php } ?>

            joms.jQuery('#albumid').change(function() {
                joms.photos.multiUpload.assignUploadUrl(joms.jQuery(this).val());
            });

            joms.jQuery('#album-name').keydown(function (e){

                if(pluploader.pluploadQueue().files.length>0){
                    joms.jQuery('.plupload_start').removeClass('plupload_disabled');
                }

            });

            // joms.jQuery('a.add-more').click(function() {
            // 	joms.jQuery("#multi_uploader").pluploadQueue().splice();
            // 	joms.jQuery(".custom_plupload_buttons").show();
            // 	joms.jQuery(".custom_plupload_status").hide();
            // 	joms.jQuery('div#upload-footer').hide();
            // 	joms.jQuery("#optional-album").css('display','inline');
            // 	joms.photos.multiUpload.displayNotice(joms.photos.multiUpload.defaultMsg);
            // 	joms.photos.multiUpload.hideShowInput();
            // });

            joms.jQuery('a#album_link').click(function() {
                jax.call('community' , 'photos,ajaxGetAlbumURL' , joms.photos.multiUpload.getSelectedAlbumId(), '<?php echo $groupId; ?>' );
                return false;
            });
            joms.jQuery('.plupload_file_name').first().html('<?php echo JText::_("COM_COMMUNITY_PHOTOS_MULTIUPLOAD_FILENAME"); ?>'); //filename
            joms.jQuery('.plupload_file_size').first().html('<?php echo JText::_("COM_COMMUNITY_PHOTOS_MULTIUPLOAD_SIZE"); ?>'); //filesize
            joms.jQuery('.plupload_file_status').first().children().html('<?php echo JText::_("COM_COMMUNITY_PHOTOS_MULTIUPLOAD_STATUS"); ?>'); //status
            joms.jQuery('.plupload_droptext').first().html('<?php echo JText::_("COM_COMMUNITY_PHOTOS_MULTIUPLOAD_DRAG_FILES"); ?>'); //drag files here
            joms.jQuery('#multi_uploader_browse').first().html('<?php echo JText::_("COM_COMMUNITY_PHOTOS_MULTIUPLOAD_ADD_FILES"); ?>'); //add files button

        });
    </script>


    <div id="photo-uploader" class="joms-share-popup" style="clearfix">

        <div id="upload-header" class="clearfix">

            <div id="new-album" style="margin-top:10px">
                <input type="text" id="album-name" style="margin-bottom:0px;" placeholder="<?php echo JText::_('COM_COMMUNITY_PHOTOS_NEW'); ?> <?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_NAME'); ?>" />
                <div id="optional-album" style="display:inline">
                    <span style="padding:0px 12px;"><?php echo JText::_('COM_COMMUNITY_OR'); ?></span>
                    <a href="javascript:joms.photos.multiUpload.showExistingAlbum();" class="btn btn-warning"><?php echo JText::_('COM_COMMUNITY_PHOTOS_ADD_TO_EXISTING_ALBUM'); ?></a>
                </div>
            </div>

            <div id="select-album" style="display:none;margin-top:10px;">
                <select id="albumid" name="albumid" style="margin-bottom:0px;">
                    <?php foreach ($allAlbums as $index => $objAlbumProp) { ?>
                        <option value="<?php echo $objAlbumProp->id;?>"><?php echo $objAlbumProp->name;?></option>
                    <?php } ?>
                </select>
                <span style="padding:0px 12px;"><?php echo JText::_('COM_COMMUNITY_OR'); ?></span>
                <a class="btn btn-primary" href="javascript:joms.photos.multiUpload.createNewAlbum();"><?php echo strtolower(JText::_('COM_COMMUNITY_PHOTOS_CREATE_NEW_ALBUM_TITLE')); ?></a>
            </div>

            <div class="joms-share-status-action" style="float:right;">
                <div data-stream-id="2" class="joms-privacy-dropdown joms-album-privacy">
                    <input type="hidden" name="joms-postbox-privacy" value="<?php echo PRIVACY_PUBLIC?>">
                    <button data-toggle="dropdown" class="dropdown-toggle" data-selected-privacy="" type="button"><span class="dropdown-value"><i class="joms-icon-globe"></i></span><span class="dropdown-caret joms-icon-caret-down"></span></button>
                    <ul class="dropdown-menu" style="right:0;left:auto;min-width:135px">
                        <li><a data-option-value="<?php echo PRIVACY_PUBLIC ?>" href="#"><i class="joms-icon-globe"></i><span><?php echo JText::_('COM_COMMUNITY_PRIVACY_PUBLIC');?></span></a></li>
                        <li><a data-option-value="<?php echo PRIVACY_MEMBERS; ?>" href="#"><i class="joms-icon-users"></i><span><?php echo JText::_('COM_COMMUNITY_PRIVACY_SITE_MEMBERS');?></span></a></li>
                        <li><a data-option-value="<?php echo PRIVACY_FRIENDS; ?>" href="#"><i class="joms-icon-user"></i><span><?php echo JText::_('COM_COMMUNITY_PRIVACY_FRIENDS');?></span></a></li>
                        <li><a data-option-value="<?php echo PRIVACY_PRIVATE; ?>" href="#"><i class="joms-icon-lock"></i><span><?php echo JText::_('COM_COMMUNITY_PRIVACY_ME');?></span></a></li>
                    </ul>
                </div>
            </div>


            <?php if (intval($groupId) === 0): ?>
                <div style="margin-top:12px;">
                    <small id="photoUploaderNotice"><?php echo $preMessage;?></small>
                </div>
            <?php endif; ?>


        </div>




        <div id="upload-content" class="clrfix">
            <div id="multi_uploader" style="overflow: hidden;"></div>
        </div><!--#upload-content-->

        <div id="upload-footer" style="display: none; padding: 10px 0">
            <!-- <a class="btn add-more" href="javascript: void(0); "><?php echo JText::_('COM_COMMUNITY_PHOTOS_ADD_MORE_PHOTOS'); ?></a> -->
            <a class="btn btn-primary" href="javascript: void(0);" id="album_link"><?php echo JText::_('COM_COMMUNITY_UPLOAD_VIEW_ALBUM'); ?></a>
            <!-- 		<a href="javascript: void(0);" class="btn" onclick="cWindowHide();" >
			<?php echo JText::_('COM_COMMUNITY_CLOSE_BUTTON'); ?></a> -->
            <!-- <div id="photoUploadedCounter"></div> -->
        </div><!--#upload-footer-->
    </div><!--#photo-uploader-->
<?php } ?>