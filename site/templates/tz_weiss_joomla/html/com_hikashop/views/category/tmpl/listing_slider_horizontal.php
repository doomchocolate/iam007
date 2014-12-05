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
?>
<?php
$height=$this->newSizes->height;
$width=$this->newSizes->width;
$duration=$this->params->get('product_effect_duration');
if(empty($duration)){ $duration=400; }

$productTransitionEffect=$this->params->get('product_transition_effect');
if($productTransitionEffect=='bounce') $productTransition='Bounce.easeOut';
if($productTransitionEffect=='linear') $productTransition='linear';
if($productTransitionEffect=='elastic') $productTransition='Elastic.easeOut';
if($productTransitionEffect=='sin') $productTransition='Sine.easeInOut';
if($productTransitionEffect=='quad') $productTransition='Quad.easeInOut';
if($productTransitionEffect=='expo') $productTransition='Expo.easeOut';
if($productTransitionEffect=='back') $productTransition='Back.easeOut';

if(!HIKASHOP_J30)
	JHTML::_('behavior.mootools');
else
	JHTML::_('behavior.framework');

$function="
	try{
		var myEffect_".$this->params->get('main_div_name')."_".$this->row->category_id." = new Fx.Morph('product_".$this->params->get('main_div_name')."_".$this->row->category_id."', {
			duration: ".$duration.",
			link: 'cancel',
			transition: Fx.Transitions.".$productTransition.",
			wait: true
		});
	}catch(err){
		var fx_".$this->params->get('main_div_name')."_".$this->row->category_id." = new Fx.Style('product_".$this->params->get('main_div_name')."_".$this->row->category_id."', 'margin-left', {
			duration: ".$duration.",
			transition: Fx.Transitions.".$productTransition.",
			wait: true
		});
	}";

$start="
	try{
		myEffect_".$this->params->get('main_div_name')."_".$this->row->category_id.".start({
				 'margin-left': totIncrement
		});
	}catch(err){
		fx_".$this->params->get('main_div_name')."_".$this->row->category_id.".stop()
		fx_".$this->params->get('main_div_name')."_".$this->row->category_id.".start(totIncrement);
	}";



$js="window.hikashop.ready( function(){

	var totIncrement = 0;
	var increment = ".$width."+1;
	var maxRightIncrement = increment*(-2);
	".$function."


	$('window_".$this->params->get('main_div_name')."_".$this->row->category_id."').addEvents({
		mouseenter: function(){
				if(totIncrement>maxRightIncrement){
		totIncrement = totIncrement - increment;
		".$start."
		}
		},
		mouseleave: function(){
		 if(totIncrement<0){
		totIncrement = totIncrement + increment;
		".$start."
			};
			}
		});

});";

$doc = JFactory::getDocument();
$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
$link = $this->getLink($this->row->category_id,$this->row->alias);
$pane_percent_height=$this->params->get('pane_height');
$htmlLink="";
$cursor="";

if($this->params->get('link_to_product_page',1)){
	$htmlLink='onclick = "window.location.href = \''.$link.'\'';
	$cursor="cursor:pointer;";
}
?>
 <div class="hikashop_horizontal_slider" id="window_<?php echo $this->params->get('main_div_name'); ?>_<?php echo $this->row->category_id;  ?>" style="margin: auto; <?php echo $cursor; ?> height:<?php echo $height; ?>px; width:<?php echo $width; ?>px; overflow:hidden; position:relative" <?php echo $htmlLink; ?>" >
 	<div id="product_<?php echo $this->params->get('main_div_name'); ?>_<?php echo $this->row->category_id;  ?>" style="height:<?php echo $height; ?>px; width:<?php echo $width*2; ?>px; " >
		<table cellspacing="0" cellpadding="0" style="height:<?php echo $height; ?>px;">
			<tr>
			<th valign="top" style="padding:0px" height="<?php echo $height; ?>" width="<?php echo $width; ?>">

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
			$css='';
			if(!empty($pane_percent_height)){
				 $paneHeight=$height*($pane_percent_height/100);
				 $css='height:'.$paneHeight.'px';
			}
		?>
		<div class="hikashop_img_pane_panel" style="width:<?php echo $width; ?>px; <?php echo $css; ?>;">

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

				</th>
				<th valign="top" height="<?php echo $height; ?>" width="<?php echo $width; ?>" >

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


				</th>
			</tr>
		</table>
		</div>
</div>
