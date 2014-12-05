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

<?php if($batchCount == 1) { ?>

<?php
$photo_info = $photos[0]->getInfo();
$photo_size = $photo_info['size'];
?>

	<p><?php echo $this->escape($this->acts[0]->title);?></p>

	<div class="joms-stream-single-photo <?php echo $photo_size; ?>">
		<a href="<?php echo $photos[0]->getPhotoLink();?>" >
			<img src="<?php echo $photos[0]->getImageURI();?>" alt="<?php echo $this->escape($photos[0]->caption);?>" />
		</a>
	</div>

<?php } else if( $batchCount <= 4 ) { ?>
	<p class="joms-stream-photo-caption"><?php echo $this->escape($this->acts[0]->title);?></p>
	<div class="row-fluid joms-stream-multi-photo">
	<?php
		$photos = array_slice($photos, 0, $batchCount);
		foreach($photos as $photo) { ?>
		<div class="span3">
			<div class="joms-stream-single-photo">
				<a href="<?php echo $photo->getPhotoLink();?>" >
					<img alt="<?php echo $this->escape($photo->caption);?>" src="<?php echo $photo->getThumbURI();?>" />
				</a>
			</div>
		</div>

	<?php } ?>
	</div>

<?php } else { ?>

	<?php if($batchCount >= 1) { ?>
		<?php if($batchCount >= 5 ) { ?>
			<p class="joms-stream-photo-caption"><?php echo $this->escape($this->acts[0]->title);?></p>
			<div class="joms-stream-single-photo">
				<div class="joms-stream-multi-photo-hero">
					<a href="<?php echo $photos[0]->getPhotoLink();?>" >
						<img src="<?php echo $photos[0]->getImageURI();?>" alt="<?php echo $this->escape($photos[0]->caption);?>" />
					</a>
				</div>
		<?php } ?>

		<?php if($batchCount != 1) { ?>
			<div class="joms-stream-multi-photo">
				<div class="row-fluid">
				<?php
					if($batchCount >= 5) {
						unset($photos[0]);
						$batchCountSlice = 5;
					} else {
						$batchCountSlice = $batchCount;
					}

					$photos = array_slice($photos, 0, $batchCountSlice);

					foreach($photos as $key=>$photo) {
                    //skip if more than 4
                    if($key > 3){continue;}
                ?>
						<div class="span3">
							<a href="<?php echo $photo->getPhotoLink();?>" class="cPhoto-Thumb"><img alt="<?php echo $this->escape($photo->caption);?>" src="<?php echo $photo->getThumbURI();?>" /></a>
						</div>
					<?php } ?>
				</div>
			</div>
		<?php }
			if($batchCount >= 5){
				?>
			</div>
		<?php
			}
		?>

	<?php } ?>
<?php } ?>