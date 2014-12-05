<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

	$model		= CFactory::getModel( 'photos');
	$photos		= $model->getPopularPhotos( 8 , 0 );

	$tmpl   =	new CTemplate();
?>

<div class="joms-stream-box joms-responsive clearfix" style="margin-bottom:0px;">

  <aside>
      <i class="joms-icon-bullhorn joms-icon-thumbnail"></i>
  </aside>

	<article>
		<a href="javascript:void(0);"><i class="joms-icon-bullhorn portrait-phone-only"></i> <?php echo JText::_('COM_COMMUNITY_ACTIVITIES_TOP_PHOTOS'); ?></a>
		<div class="separator"></div>
		<div class="cStream-Photo top-photos js-col-layout">
			<?php
			foreach( $photos as $photo )
			{
			?>
				<div class="js-col4">
					<a href="<?php echo $photo->getPhotoLink();?>" class="cSnip-Photo" title="<?php echo $this->escape($photo->caption);?>">
						<?php
						$user = CFactory::getUser($photo->creator);
						?>
						<img class="cPhoto-Thumb" alt="<?php echo $this->escape($photo->caption);?>" src="<?php echo $photo->getThumbURI();?>" />
						<span>
							<i><?php echo JText::sprintf('COM_COMMUNITY_PHOTOS_UPLOADED_BY' , $user->getDisplayName() );?></i>
						</span>
					</a>
				</div>
			<?php
			}
			?>
		</div>

	</article>

</div>