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
JHTML::_('behavior.tooltip');
$row =& $this->rows;
$vote_enabled = $row->vote_enabled;
if ($vote_enabled == 1) {
	$hikashop_vote_average_score = $row->hikashop_vote_average_score;
	$hikashop_vote_average_score_rounded = $row->hikashop_vote_average_score_rounded;
	JRequest::setVar("rate_rounded",$hikashop_vote_average_score_rounded);
	$hikashop_vote_total_vote = $row->hikashop_vote_total_vote;
	$hikashop_vote_nb_star = $row->hikashop_vote_nb_star;
	JRequest::setVar("nb_max_star",$hikashop_vote_nb_star);

	$type_item = $row->type_item;
	$hikashop_vote_ref_id = $row->vote_ref_id;

	$main_div_name = $row->main_div_name;
	$hikashop_vote_user_id = hikashop_loadUser();
	$listing_true = $row->listing_true;
	$select_id = "select_id_".$hikashop_vote_ref_id;
	if($main_div_name != '' ){
		$select_id .= "_".$main_div_name;
	}else{
		$select_id .= "_hikashop_main_div_name";
	}
	if(empty($main_div_name)){?>
		<input 	type="hidden" id="hikashop_vote_ref_id" value="<?php echo $hikashop_vote_ref_id;?>"/>
<?php } ?>
	<input 	type="hidden" id="hikashop_vote_ok_<?php echo $hikashop_vote_ref_id;?>" value="0"/>
	<input 	type="hidden" id="vote_type_<?php echo $hikashop_vote_ref_id;?>" value="<?php echo $type_item; ?>"/>
	<input 	type="hidden" id="hikashop_vote_user_id_<?php echo $hikashop_vote_ref_id;?>" value="<?php echo $hikashop_vote_user_id;?>"/>

	<div class="hikashop_vote_stars">
		<select name="hikashop_vote_rating" style="display: none;" class="chzn-done" id="<?php echo $select_id;?>">
			<?php
			for ($i = 1; $i <= $hikashop_vote_nb_star; $i++) {
				echo '<option value="' . $i . '">' . $i . '</option>';
			}
			?>
		</select>
		<script type='text/javascript'>
			window.hikashop.ready( function() {
				var rating = new hikashop_ratings(document.getElementById('<?php echo $select_id;?>'), {
					id : 'hikashop_vote_rating_<?php echo $type_item; ?>_<?php echo $hikashop_vote_ref_id;?>_',
					showSelectBox : false,
					container : null,
					defaultRating :  <?php echo $hikashop_vote_average_score_rounded; ?>
				});
			});
		</script>
		<span class="hikashop_total_vote" >(<?php echo JHTML::tooltip($hikashop_vote_average_score.'/'.$hikashop_vote_nb_star, JText::_('VOTE_AVERAGE'), '', ' '.$hikashop_vote_total_vote.' '); ?>) </span>
		<span id="hikashop_vote_status_<?php echo $hikashop_vote_ref_id;?>" class="hikashop_vote_notification_mini"></span>
	</div>
<?php
}
?>
