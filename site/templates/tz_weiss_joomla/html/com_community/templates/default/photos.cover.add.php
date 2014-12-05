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

$browser = JBrowser::getInstance();

$onClickJS ="joms.jQuery('#uploadCover').click();";

if($browser->getBrowser() == 'msie')
{
    $onClickJS ='';
}
?>
<p id="error_msg" style='color:#B94A48'></p>

<ul class="nav nav-tabs" id="coverphotostab">
    <li id="photostab" class="active"><a href="#coverphotos" data-toggle="tab"><?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUMS_LABEL') ?> </a></li>
    <li id="coveruploadtab"><a href="#coverupload" data-toggle="tab"><?php echo JText::_('COM_COMMUNITY_PHOTOS_UPLOAD_PHOTOS') ?></a></li>
</ul>

<div class="tab-content clearfix">
    <div class="tab-pane active" id="coverphotos">
        <div id="coverphotolist"></div>
        <div id="coveralbumlist">
        <?php if (count($albums) == 0) { ?>
            <div class="alert alert-info" align="center"><?php echo JText::_('COM_COMMUNITY_PHOTOS_NO_ALBUM_CREATED') ?></div>
		<?php } else { ?>

        <div class="row-fluid">
        <?php $i = 1; // split foreach every 4 data
        foreach( $albums as $album ) { ?>
        <div class="span3 album">
            <a class="thumbnail" onclick="joms.cover.openalbum(<?php echo $album->id; ?>,<?php echo $album->total_photo; ?>)" href="javascript:void(0)">
                <img alt="" src="<?php echo (!empty($album->thumbnail)) ? $album->thumbnail : 'http://dribbble.com/system/assets/2839/13307/screenshots/477345/flower.jpg?1332182802' ?>">
            </a>
            <div class="album-details">
                <a class="album-name" onclick="joms.cover.openalbum(<?php echo $album->id; ?>,<?php echo $album->total_photo; ?>)" href="javascript:void(0)"><?php echo $this->escape($album->name); ?></a>
                <div class="album-count"><strong> <?php echo $album->total_photo . " " . JText::_('COM_COMMUNITY_NOTIFICATIONGROUP_PHOTOS'); ?></strong></div>
            </div>
        </div>
        <?php if($i % 4 == 0) { ?>
        </div><div class="row-fluid">
        <?php }
        $i++; //increment i
        } ?>
        </div>

        <?php } ?>
        </div>
    </div>

    <div class="tab-pane" id="coverupload">
    	<div id="uploadbox">
            <div class="filelist"></div>
            <label class="label-filetype">
                <a class="btn btn-primary btn-block" href="javascript:<?php echo $onClickJS; ?>void(0);" ><?php echo JText::_('COM_COMMUNITY_PHOTOS_UPLOAD_PHOTOS');?></a>
                <input id="uploadCover" type="file" name="uploadCover" size="35" onchange="joms.cover.uploader('<?php echo CRoute::_("index.php?option=com_community&view=photos&task=ajaxCoverUpload&type=".$type."&parentId=".$parentId)?>')" style="display:none;" >
            </label>
        </div>
    </div>
</div>

<script>
  joms.jQuery(function () {
    joms.jQuery('#coverphotostab').tab('show');
    joms.jQuery('#photostab').addClass('active');
  })
</script>