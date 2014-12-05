<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$height=$this->newSizes->height;
$width=$this->newSizes->width;
$duration=$this->params->get('product_effect_duration');
if(empty($duration)){
	$duration=1000;
}
$backgroundColor="";
$backgroundColor=$this->params->get('background_color');

if(!HIKASHOP_J30)
	JHTML::_('behavior.mootools');
else
	JHTML::_('behavior.framework');
$Fadefunction="
	try{
		myFx_".$this->params->get('main_div_name')."_".$this->row->category_id." = new Fx.Style('myElement_".$this->params->get('main_div_name')."_".$this->row->category_id."', 'opacity', {
			wait: false,
 			duration: ".$duration."
 		});
	}catch(err){
		$('myElement_".$this->params->get('main_div_name')."_".$this->params->get('main_div_name')."_".$this->row->category_id."').set('morph', {
					duration: ".$duration.",
					transition: 'linear',
			 });
	}";

$FadeStartOne="
	try{
		this.morph({
				'opacity': 0.0,
				duration: ".$duration."
			});
	}catch(err){
		myFx_".$this->params->get('main_div_name')."_".$this->row->category_id.".stop();
				myFx_".$this->params->get('main_div_name')."_".$this->row->category_id.".start(0);
	}";

$FadeStartTwo="
	try{
		this.morph({
				'opacity': 1.0,
				duration: ".$duration."
			});
	}catch(err){
		myFx_".$this->params->get('main_div_name')."_".$this->row->category_id.".stop();
				myFx_".$this->params->get('main_div_name')."_".$this->row->category_id.".start(1);
	}";


$js="var myFx_".$this->params->get('main_div_name')."_".$this->row->category_id." = null;
		window.hikashop.ready( function(){
	".$Fadefunction."

	$('myElement_".$this->params->get('main_div_name')."_".$this->row->category_id."').addEvents({
		mouseenter: function(){
		 ".$FadeStartOne."
		},
		mouseleave: function(){
			".$FadeStartTwo."
		}
	});

});";

$doc =& JFactory::getDocument();
$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
$pane_percent_height=$this->params->get('pane_height');
$link = $this->getLink($this->row->category_id,$this->row->alias);
$htmlLink="";
$cursor="";




if($this->params->get('link_to_product_page',1)){
	$htmlLink='onclick = "window.location.href = \''.$link.'\'';
	$cursor="cursor:pointer;";
}
?>
<div class="hikashop_fade_effect" id="window_<?php echo $this->row->category_id;  ?>" style="margin: auto; text-align:center; <?php echo $cursor; ?> height:<?php echo $height; ?>px; width:<?php echo $width; ?>px; overflow:hidden; position:relative" <?php echo $htmlLink; ?>" >
 	<div class="hikashop_fade_effect_picture" id="myElement_<?php echo $this->params->get('main_div_name'); ?>_<?php echo $this->row->category_id;  ?>" style="background-color:<?php echo $backgroundColor; ?>; position:absolute; height:<?php echo $height; ?>px; width:<?php echo $width; ?>px; ">

		<!-- CATEGORY IMG -->
			<div style="height:<?php echo $this->image->main_thumbnail_y;?>px;width:<?php echo $this->image->main_thumbnail_x;?>px;text-align:center;margin:auto" class="hikashop_product_image">
				<a href="<?php echo $link;?>" title="<?php echo $this->escape($this->row->category_name); ?>">
					<?php
					$image_options = array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'));
					$img = $this->image->getThumbnail(@$this->row->file_path, array('width' => $this->image->main_thumbnail_x, 'height' => $this->image->main_thumbnail_y), $image_options);
					if($img->success) {
						echo '<img class="hikashop_product_listing_image" title="'.$this->escape(@$this->row->file_description).'" alt="'.$this->escape(@$this->row->file_name).'" src="'.$img->url.'"/>';
					}
					?>
				</a>
			</div>
		<!-- EO CATEGORY IMG -->

		<?php
			$paneHeight='';
			if(!empty($pane_percent_height)){
				 $paneHeight='height:'.$height+$pane_percent_height.'px;';
			}
		?>
		<div class="hikashop_img_pane_panel" style="width:<?php echo $width; ?>px;<?php echo $paneHeight; ?>;">
			<!-- CATEGORY NAME -->
				<span class="hikashop_category_name">
					<a href="<?php echo $link;?>">
						<?php
						echo $this->row->category_name;

						if($this->params->get('number_of_products',0)){
							echo ' ('.$this->row->number_of_products.')';
						}
						?>
					</a>
				</span>
			<!-- EO CATEGORY NAME -->

		</div>
	</div>



	<!-- CATEGORY NAME -->
	<span class="hikashop_category_name">
		<a href="<?php echo $link;?>">
			<?php
			echo $this->row->category_name;
			?>
		</a>
	</span>
	<!-- EO CATEGORY NAME -->


	<!-- CATEGORY DESCRIPTION -->
	<div class="hikashop_category_desc" style="height=<?php echo $height; ?>px; text-align:<?php echo $this->align; ?>; overflow:hidden">
		<?php
		echo preg_replace('#<hr *id="system-readmore" */>.*#is','',$this->row->category_description);
		?>
	</div>
	<!-- EO CATEGORY DESCRIPTION -->

</div>

