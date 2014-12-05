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
 if(!$browser->isMobile()){
?>
<script type="text/javascript">
	joms.jQuery(document).ready(function() {
		joms.jQuery("img.cAvatar").bind("mouseover", function(event) {
			var minitipAlbumId = joms.jQuery(this).attr('data').split('_')[3];
			joms.tooltips.setDelay(joms.jQuery(this), 'jax.cacheCall("community","photos,ajaxShowThumbnail","'+minitipAlbumId+'");', "load-thumbnail", 240, 60, event);
		})
	});
</script>
<?php }?>
<a id="lists" name="listing"></a>

<?php
if( $albums )
{
?>
<ul class="cMedia-ThumbList Albums cResetList cFloatedList clearfix">
<?php
	$i = 0;
	foreach($albums as $album)
	{

            /* Filter normal user to view cover album */
            if($album->type =='profile.Cover' && !$album->isOwner && !$isSuperAdmin && !$isCommunityAdmin )
                continue;
			CHeadHelper::addOpengraph('og:image', $album->getCoverThumbURI(), true);
?>
	<li class="album-permission-<?php echo $album->permissions ?>">
		<div class="cMedia-Box">
			<div class="cMedia-AlbumCover">
				<a class="cPhotoAvatar" href="<?php echo $album->getURI(); ?>">
					<img class="cAvatar" src="<?php echo $album->getCoverThumbURI(); ?>" alt="<?php echo $this->escape($album->name);?>" data="album_prop_<?php echo rand(0,200).'_'.$album->id;?>"/>
				</a>

				<?php
					if( $album->isOwner  ||   $isSuperAdmin  ||  ( $isCommunityAdmin && ($type == PHOTOS_USER_TYPE || $type == PHOTOS_GROUP_TYPE) )  )
					{
				?>
				<div class="cMedia-Actions<?php if( in_array($album->id, $featuredList) ){ ?> featured<?php } ?><?php if( !$album->isOwner ) { ?> not-owner<?php } ?>">
					<div>
					<?php
						if( $album->isOwner  ||   $isSuperAdmin  )
						{
					?>
						<a class="album-action edit" title="<?php echo JText::_('COM_COMMUNITY_PHOTOS_EDIT');?>" href="<?php echo $album->editLink; ?>">
							<i class="com-icon-form"></i>
						</a>
						<?php if ( $album->isOwner ) { ?>
							<a class="album-action upload visible-desktop" title="<?php echo JText::_('COM_COMMUNITY_PHOTOS_UPLOAD');?>" href="<?php echo $album->uploadLink; ?>">
								<i class="com-icon-add"></i>
							</a>
						<?php } ?>
						<a class="album-action delete" title="<?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_DELETE');?>" href="javascript:void(0);" onclick="cWindowShow('jax.call(\'community\',\'photos,ajaxRemoveAlbum\',\'<?php echo $album->id;?>\',\'<?php echo $currentTask; ?>\');' , '<?php echo JText::_('COM_COMMUNITY_REMOVE');?>' , 450 , 150 );">
							<i class="com-icon-block"></i>
						</a>
					<?php
						}
					?>

					<?php
					if($showFeatured){
						if( $isCommunityAdmin && ($type == PHOTOS_USER_TYPE || $type == PHOTOS_GROUP_TYPE) )
						{
							if( !in_array($album->id, $featuredList)  && $album->permissions == 0 )
							{
					?>
						<a class="album-action featured" title="<?php echo JText::_('COM_COMMUNITY_MAKE_FEATURED'); ?>" onclick="joms.featured.add('<?php echo $album->id;?>','photos');" href="javascript:void(0);">
							<i class="com-icon-award-plus"></i>
						</a>
					<?php
							}
							else
							{
					?>
					<?php if($album->permissions == 0 ): //check if album is public ?>
						<a class="album-action remove-featured" title="<?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?>" onclick="joms.featured.remove('<?php echo $album->id;?>','photos');" href="javascript:void(0);">
							<i class="com-icon-award-minus"></i>
						</a>
					<?php endif; ?>
						<?php	}
						}
					}
					?>
					</div>
				</div>
				<?php } ?>

				<?php
				if($showFeatured)
				{
					if( $isCommunityAdmin && ($type == PHOTOS_USER_TYPE || $type == PHOTOS_GROUP_TYPE) )
					{
						if( in_array($album->id, $featuredList) )
						{
				?>
				<span class="cMedia-Featured"></span>
				<?php
						}
					}
				}
				?>
			</div>
			<div class="cMedia-Summary">
				<div class="cMedia-Title"><a href="<?php echo $album->getURI(); ?>"><?php echo $this->escape($album->name); ?></a></div>
				<div class="cMedia-Count">
					<?php if(CStringHelper::isPlural($album->count)) {
						echo JText::sprintf('COM_COMMUNITY_PHOTOS_COUNT', $album->count );
					} else {
						echo JText::sprintf('COM_COMMUNITY_PHOTOS_COUNT_SINGULAR', $album->count );
					} ?>
					<?php
					// Show access class for "friends (30)" or "me only (40)"
					$accessClass = 'public'; // NO need to display this
					$accessClass = ($album->permissions == PRIVACY_FRIENDS) ? 'site' 		: $accessClass ;
					$accessClass = ($album->permissions == PRIVACY_FRIENDS) ? 'friends' 	: $accessClass ;
					$accessClass = ($album->permissions == PRIVACY_PRIVATE) ? 'me' 		: $accessClass ;

					$accessTitle = "";
					$accessTitle = ($accessClass == 'site') ? JText::_('COM_COMMUNITY_PRIVACY_TITLE_SITE_MEMBERS') : $accessTitle;
					$accessTitle = ($accessClass == 'friends') ? JText::_('COM_COMMUNITY_PRIVACY_TITLE_FRIENDS') : $accessTitle;
					$accessTitle = ($accessClass == 'me') ? JText::_('COM_COMMUNITY_PRIVACY_TITLE_ME') : $accessTitle;

					if($accessClass != 'public') {
					?>
					<span>
						<i class="com-glyph-lock-<?php echo $accessClass; ?>" title="<?php echo $accessTitle;?>"></i>
					</span>
					<?php
					}
					?>
				</div>
				<div class="cMedia-Details small">
					<?php echo $album->lastupdated; ?>
					<?php
					if(isset($album->location) && $album->location != "")
					{
						echo JText::sprintf('COM_COMMUNITY_EVENTS_TIME_SHORT', $album->location );
					}
					?>
					<?php
					if ($currentTask != 'myphotos')
					{
					?>
						<?php echo JText::_('COM_COMMUNITY_PHOTOS_BY'); ?>
						<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$album->creator); ?>"><?php echo $album->user->getDisplayName(); ?></a>
					<?php
					}
					?>
				</div>
			</div>
		</div>
	</li>

	<?php
		// count every 4 albums, and insert clearing div so there's no wrapping bug
		$i++;
		if( $i % 4 == 0 )
		{
			$i = 0;
	?>
	<div class="clear"></div>
	<?php }
		} // end: foreach($albums as $album)
	?>
</ul>
	<?php
}
else
{
?>
<div class="cEmpty cAlert">
	<?php echo JText::_('COM_COMMUNITY_PHOTOS_NO_ALBUM_CREATED'); ?>
</div>
<?php
} // end: if( $albums )
?>




<?php
if ( $pagination->getPagesLinks() )
{
?>
<div class="cPagination">
	<?php echo $pagination->getPagesLinks(); ?>
</div>
<?php
}
?>