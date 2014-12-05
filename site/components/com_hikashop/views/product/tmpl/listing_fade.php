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
if(empty($duration)){
	$duration=1000;
}
if($this->params->get('background_color','')==''){
	$config =& hikashop_config();
	$this->params->set('background_color',$config->get('background_color'));
}
$backgroundColor="";
$backgroundColor=$this->params->get('background_color');

if(!HIKASHOP_J30)
	JHTML::_('behavior.mootools');
else
	JHTML::_('behavior.framework');
$Fadefunction="
	try{
		myFx_".$this->params->get('main_div_name')."_".$this->row->product_id." = new Fx.Style('myElement_".$this->params->get('main_div_name')."_".$this->row->product_id."', 'opacity', {
			wait: false,
 			duration: ".$duration."
 		});
	}catch(err){
		$('myElement_".$this->params->get('main_div_name')."_".$this->row->product_id."').set('morph', {
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
		myFx_".$this->params->get('main_div_name')."_".$this->row->product_id.".stop();
				myFx_".$this->params->get('main_div_name')."_".$this->row->product_id.".start(0);
	}";

$FadeStartTwo="
	try{
		this.morph({
				'opacity': 1.0,
				duration: ".$duration."
			});
	}catch(err){
		myFx_".$this->params->get('main_div_name')."_".$this->row->product_id.".stop();
				myFx_".$this->params->get('main_div_name')."_".$this->row->product_id.".start(1);
	}";


$js="var myFx_".$this->params->get('main_div_name')."_".$this->row->product_id." = null;
		window.hikashop.ready( function(){
	".$Fadefunction."

	$('myElement_".$this->params->get('main_div_name')."_".$this->row->product_id."').addEvents({
		mouseenter: function(){
		 ".$FadeStartOne."
		},
		mouseleave: function(){
			".$FadeStartTwo."
		}
	});

});";

$doc = JFactory::getDocument();
$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
$pane_percent_height=$this->params->get('pane_height');
$link = hikashop_completeLink('product&task=show&cid='.$this->row->product_id.'&name='.$this->row->alias.$this->itemid.$this->category_pathway);
$htmlLink="";
$cursor="";
if($this->params->get('link_to_product_page',1)){
	$htmlLink='onclick = "window.location.href = \''.$link.'\'';
	$cursor="cursor:pointer;";
}
?>
<div class="hikashop_fade_effect" id="window_<?php echo $this->row->product_id;  ?>" style="margin: auto; text-align:<?php echo $this->align; ?>; <?php echo $cursor; ?> height:<?php echo $height; ?>px; width:<?php echo $width; ?>px; overflow:hidden; position:relative" <?php echo $htmlLink; ?>" >
 	<div class="hikashop_fade_effect_picture" id="myElement_<?php echo $this->params->get('main_div_name'); ?>_<?php echo $this->row->product_id;  ?>" style="background-color:<?php echo $backgroundColor; ?>; position:absolute; height:<?php echo $height; ?>px; width:<?php echo $width; ?>px; ">

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
				$horizontal = '0';
				$vertical = -'10';
				if($this->params->get('display_badges',1)){
					$this->classbadge->placeBadges($this->image, $this->row->badges, $vertical, $horizontal);
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
			$paneHeight='';
			if(!empty($pane_percent_height)){
				 $paneHeight='height:'.$height+$pane_percent_height.'px;';
			}
		?>
		<div class="hikashop_img_pane_panel" style="width:<?php echo $width; ?>px;<?php echo $paneHeight; ?>;">
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


	<!-- PRODUCT DESCRIPTION -->
	<div style="height=<?php echo $height; ?>px; text-align:<?php echo $this->align; ?>; overflow:hidden">
		<?php
		echo preg_replace('#<hr *id="system-readmore" */>.*#is','',$this->row->product_description);
		?>
	</div>
	<!-- EO PRODUCT DESCRIPTION -->

	<!-- PRODUCT VOTE -->
	<?php
	if($this->params->get('show_vote')){
		$this->setLayout('listing_vote');
		echo $this->loadTemplate();
	}
	?>
	<!-- EO PRODUCT VOTE -->

	<!-- ADD TO CART BUTTON AREA -->
	<?php
	if($this->params->get('add_to_cart') || $this->params->get('add_to_wishlist')){
		$this->setLayout('add_to_cart_listing');
		echo $this->loadTemplate();
	}?>
	<!-- EO ADD TO CART BUTTON AREA -->

	<!-- COMPARISON AREA -->
	<?php
	if(JRequest::getVar('hikashop_front_end_main',0) && JRequest::getVar('task')=='listing' && $this->params->get('show_compare')) { ?>
		<br/><?php
		if( $this->params->get('show_compare') == 1 ) {
			$js = 'setToCompareList('.$this->row->product_id.',\''.$this->escape($this->row->product_name).'\',this); return false;';
			echo $this->cart->displayButton(JText::_('ADD_TO_COMPARE_LIST'),'compare',$this->params,$link,$js,'',0,1,'hikashop_compare_button');
		 } else { ?>
		<input type="checkbox" class="hikashop_compare_checkbox" id="hikashop_listing_chk_<?php echo $this->row->product_id;?>" onchange="setToCompareList(<?php echo $this->row->product_id;?>,'<?php echo $this->escape($this->row->product_name); ?>',this);"><label for="hikashop_listing_chk_<?php echo $this->row->product_id;?>"><?php echo JText::_('ADD_TO_COMPARE_LIST'); ?></label>
	<?php }
	} ?>
	<!-- EO COMPARISON AREA -->
</div>

