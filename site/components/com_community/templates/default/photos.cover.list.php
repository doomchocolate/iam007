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
<div class="photoalbumlist">
<?php if(count($photos) > 0): ?>
    <ul class="row-fluid unstyled">
		<?php foreach($photos as $p): ?>
        <li class="span3">
        	<div class="thumbnail">
	    		<a id="<?php echo $p->id; ?>" href="javascript:void(0)" class="cover-thumbnail-link" onclick="joms.cover.setCover(this);">
                	<img src="<?php echo $p->getThumbURI(); ?>" alt="<?php echo $p->caption; ?>">
                </a>
    		</div>
    	</li>
		<?php endforeach; ?>
    </ul>
<?php endif; ?>
</div>
<!-- back to album button -->
<a class="btn clearfix" href="javascript:void(0)" onclick="joms.jQuery('#coverphotolist').hide();joms.jQuery('#coveralbumlist').show(function(){cWindowAutoResize()});">&laquo; <?php echo JText::_('COM_COMMUNITY_PHOTOS_BACK_TO_ALBUM'); ?></a>
