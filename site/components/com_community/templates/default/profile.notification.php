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

<div class="cLayout cProfile-Notifications">

	<div class="cAlert">
		<?php echo JText::sprintf('COM_COMMUNITY_PROFILE_NOTIFICATION_LIST_LIMIT_MSG',$config->get('maxnotification'));?>
	</div>

	<?php if($notifications) : ?>
	<ul class="cStreamList cResetList">
		<?php
			foreach( $notifications as $row ) {
			$user	= CFactory::getUser( $row->actor );
		?>
		<li>
			<a href="<?php echo CContentHelper::injectTags('{url}',$row->params,true); ?>" class="cStream-Avatar cFloat-L">
				<img class="cAvatar" src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>" />
			</a>
			<div class="joms-stream-content">
				<div class="cStream-Headline"><?php echo CContentHelper::injectTags($row->content,$row->params,true); ?></div>
				<div class="cStream-Actions small"><?php echo CTimeHelper::timeLapse(CTimeHelper::getDate($row->created)); ?></div>
			</div>
		</li>
		<?php } ?>
	</ul>
	<?php endif; ?>



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


<script>
joms.jQuery(".cProfile-DataStream p a").each(function(key,val){
	joms.jQuery(val).attr("target","_blank");
	joms.jQuery(val).click(function(e){
		if (!e) var e = window.event;
		e.cancelBubble = true;
		if (e.stopPropagation) e.stopPropagation();
	});
});
joms.jQuery(".cProfile-DataStream li").each(function(key,val){
	joms.jQuery(val).click(function(e){
		var link = joms.jQuery(this).find("a").attr("href");
		if (link.length > 0){
			window.open(link,null);
		}
	});
});
</script>
</div>