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
<script type='text/javascript'>
joms.jQuery(document).ready(function(){

   joms.jQuery(':checkbox').click(function(){
        var checked = joms.jQuery('input:checked').length;

        if(checked==3){
            joms.jQuery(':submit').removeAttr("disabled");
        }else{
            joms.jQuery(':submit').attr("disabled",true);
        }
    })
});
</script>
<p>
    This regen feature will regenerate photo thumbnails for JomSocial Photos. This feature is helpful when:
    <ul>
        <li>old thumbs (generated pre-JomSocial 2.0) does not suit your taste due to its small size (64px)</li>
        <li>a mashap caused thumbs to be missing from storage</li>
    </ul>
</p>
<p>
    Regen will require <strong>/images/originalphotos</strong> directory to be intact. Without this directory. JomSocial will not ab be
    able to regenrate new thumbnails. JomSocial will attempt to regenerate thumbs from <strong>/images/resized</strong> photos. If no images
    are available in /resized directory, the script will use photos from <strong>/images/originalphotos</strong>.
</p>
<p>
    Since photos in <strong>/images/originalphotos</strong> are generally of higher resolution, it will be more performance intensive and may
    bring servers to halt. Please be cautious and avoid from setting a number that too hight. A good number to start is 5.
</p>
<br />
<br />
<form method="post" action="<?php echo CRoute::getURI(); ?>" id="createGroup" name="jsform-groups-create" class="community-form-validate">
<p>
    <span>
            I would like to regenerate
    </span>
    <input type="text" name="no" class="input text">
    <span>
        thumbnails from
    </span>
    <input type="text" name="start" class="input text">
    <span>
        for
    </span>
    <select name="type" class="input select">
        <option value="1">UserId</option>
        <option value="2">AlbumID</option>
        <option value="3">All Photos</option>
    </select>
    <span>
         with the ID
    </span>
    <input type="text" name="id" class="input text">
</p>
<p>
    <input type="checkbox" class="input checkbox"><span> I have made a backup of the entire <b>/images/</b> directory</span>
</p>
<p>
    <input type="checkbox" class="input checkbox"><span> I have made sure that all directories and files in <b>/images/</b> directory are writable and readable</span>
</p>
<p>
    <input type="checkbox" class="input checkbox"><span> I am ware that this feature will not work for Amazon S3 photos</span>
</p>
<input name="action" type="hidden" value="save" />
<input type="submit" value="Proceed" disabled="disabled">
<?php echo JHTML::_( 'form.token' ); ?> 
</form>