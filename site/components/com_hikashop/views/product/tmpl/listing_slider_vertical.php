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
$enableCarousel=$this->params->get('enable_carousel');
if(empty($duration)){ $duration=400; }

$transitions = array(
	'bounce' => 'Bounce.easeOut',
	'linear' => 'linear',
	'elastic' => 'Elastic.easeOut',
	'sin' => 'Sine.easeInOut',
	'quad' => 'Quad.easeInOut',
	'expo' => 'Expo.easeOut',
	'expo' => 'Back.easeOut'
);
if(!empty($transitions[$this->params->get('product_transition_effect')])) {
	$productTransition = $transitions[$this->params->get('product_transition_effect')];
} else {
	$productTransition = current($transitions);
}

if(!HIKASHOP_J30)
	JHTML::_('behavior.mootools');
else
	JHTML::_('behavior.framework');

$function="
try{
	var myEffect_".$this->params->get('main_div_name')."_".$this->row->product_id." = new Fx.Morph('product_".$this->params->get('main_div_name')."_".$this->row->product_id."', {
		duration: ".$duration.",
		link: 'cancel',
		transition: Fx.Transitions.".$productTransition.",
		wait: true
	});
}catch(err){
	var fx_".$this->params->get('main_div_name')."_".$this->row->product_id." = new Fx.Style('product_".$this->params->get('main_div_name')."_".$this->row->product_id."', 'margin-top', {
		duration: ".$duration.",
		transition: Fx.Transitions.".$productTransition.",
		wait: true
	});
}";

$start="
try{
	myEffect_".$this->params->get('main_div_name')."_".$this->row->product_id.".start({
		'margin-top': totIncrement
	});
}catch(err){
	fx_".$this->params->get('main_div_name')."_".$this->row->product_id.".stop()
	fx_".$this->params->get('main_div_name')."_".$this->row->product_id.".start(totIncrement);
}";



$js="window.hikashop.ready( function(){
	var totIncrement = 0;
	var increment = ".$height."+1;
	var maxRightIncrement = increment*(-2);
	".$function."

	$('window_".$this->params->get('main_div_name')."_".$this->row->product_id."').addEvents({
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
			}
		}
	});
});";

if(!HIKASHOP_PHP5)
	$doc =& JFactory::getDocument();
else
	$doc = JFactory::getDocument();
$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
$link = hikashop_completeLink('product&task=show&cid='.$this->row->product_id.'&name='.$this->row->alias.$this->itemid.$this->category_pathway);
$pane_height=$this->params->get('pane_height');
$htmlLink = '';
$cursor = '';
if($this->params->get('link_to_product_page',1)){
	if(!$this->params->get('add_to_cart') && !$this->params->get('add_to_wishlist')){
		$htmlLink = 'onclick="window.location.href = \''.$link.'\'';
		$cursor = 'cursor:pointer;';
	}
}

if(!empty($this->row->extraData->top)) { echo implode("\r\n",$this->row->extraData->top); }
?>
<div class="hikashop_vertical_slider" id="window_<?php echo $this->params->get('main_div_name'); ?>_<?php echo $this->row->product_id;  ?>" style=" margin: auto; <?php echo $cursor; ?> height:<?php echo $height; ?>px; width:<?php echo $width; ?>px; overflow:hidden; position:relative" <?php echo $htmlLink; ?>" >
 	<div id="product_<?php echo $this->params->get('main_div_name'); ?>_<?php echo $this->row->product_id;  ?>" style=" height:<?php echo $height*2; ?>px; width:<?php echo $width; ?>px; " >
		<div style="height:<?php echo $height*2; ?>">
			<div style="padding:0px; height:<?php echo $height; ?>px; width:<?php echo $width; ?>px; position:relative">

				<!-- PRODUCT IMG -->
				<div style="height:<?php echo $this->image->main_thumbnail_y;?>px;text-align:center;clear:both;" class="hikashop_product_image">
					<div style="position:relative;text-align:center;clear:both;width:<?php echo $this->image->main_thumbnail_x;?>px;margin: auto;" class="hikashop_product_image_subdiv">
					<?php if($this->params->get('link_to_product_page',1)){ ?>
						<a href="<?php echo $link;?>" title="<?php echo $this->escape($this->row->product_name); ?>">
					<?php } ?><?php
					$image_options = array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'));
					$img = $this->image->getThumbnail(@$this->row->file_path, array('width' => $this->image->main_thumbnail_x, 'height' => $this->image->main_thumbnail_y), $image_options);
					if($img->success) {
						echo '<img class="hikashop_product_listing_image" title="'.$this->escape(@$this->row->file_description).'" alt="'.$this->escape(@$this->row->file_name).'" src="'.$img->url.'"/>';
					}
					$main_thumb_x = $this->image->main_thumbnail_x;
					$main_thumb_y = $this->image->main_thumbnail_y;
					if($this->params->get('display_badges',1)){
						$this->classbadge->placeBadges($this->image, $this->row->badges, -10, 0);
					}
					$this->image->main_thumbnail_x = $main_thumb_x;
					$this->image->main_thumbnail_y = $main_thumb_y;

					if($this->params->get('link_to_product_page',1)){ ?>
						</a>
					<?php } ?>
					</div>
				</div>
				<!-- EO PRODUCT IMG -->

				<?php
					$css = '';
					if(!empty($pane_height)){
						 $css = 'height:'.$pane_height.'px';
					} else{
						$pane_height = 30;
					}
				?>
				<div class="hikashop_img_pane_panel" style="width:<?php echo $width; ?>px; <?php echo $css; ?>; ">

				<!-- PRODUCT NAME -->
				<span class="hikashop_product_name">
					<?php
					if($this->params->get('link_to_product_page',1)){
					?>
					<a href="<?php echo $link;?>"><?php
					}
					echo $this->row->product_name;
					if($this->params->get('link_to_product_page',1)){
						?></a><?php
					}
					?>
				</span>
				<!-- EO PRODUCT NAME -->

				<!-- PRODUCT CODE -->
					<span class='hikashop_product_code_list'>
						<?php if ($this->config->get('show_code')) { ?>
							<?php if($this->params->get('link_to_product_page',1)){ ?>
								<a href="<?php echo $link;?>">
							<?php }
							echo $this->row->product_code;
							if($this->params->get('link_to_product_page',1)){ ?>
								</a>
							<?php } ?>
						<?php } ?>
					</span>
				<!-- EO PRODUCT CODE -->

				<!-- PRODUCT PRICE -->
				<?php
					if($this->params->get('show_price','-1')=='-1'){
						$config =& hikashop_config();
						$this->params->set('show_price',$config->get('show_price'));
					}
					if($this->params->get('show_price')){
						$this->setLayout('listing_price');
						echo $this->loadTemplate();
					}
				?>
				<!-- EO PRODUCT PRICE -->
				</div>
			</div>
			<div class="hikashop_slide_vertical_description" style="padding:0px; height:<?php echo $height; ?>px; width:<?php echo $width; ?>px;">
				<!-- PRODUCT NAME -->
				<span class="hikashop_product_name">
					<?php if($this->params->get('link_to_product_page',1)){ ?>
						<a href="<?php echo $link;?>">
					<?php }
						echo $this->row->product_name;
					if($this->params->get('link_to_product_page',1)){ ?>
						</a>
					<?php } ?>
				</span>
				<!-- EO PRODUCT NAME -->
				<?php if(!empty($this->row->extraData->afterProductName)) { echo implode("\r\n",$this->row->extraData->afterProductName); } ?>

				<!-- PRODUCT DESCRIPTION -->
				<div style="text-align:<?php echo $this->align; ?>; overflow:hidden">
				<?php
					echo preg_replace('#<hr *id="system-readmore" */>.*#is','',$this->row->product_description);
				?>
				</div>
				<!-- EO PRODUCT DESCRIPTION -->

				<!-- ADD TO CART BUTTON AREA -->
				<?php
				if($this->params->get('add_to_cart') || $this->params->get('add_to_wishlist')){
					$this->setLayout('add_to_cart_listing');
					echo $this->loadTemplate();
				}?>
				<!-- EO ADD TO CART BUTTON AREA -->
			</div>
		</div>
	</div>
</div>
<?php if(!empty($this->row->extraData->bottom)) { echo implode("\r\n",$this->row->extraData->bottom); } ?>
