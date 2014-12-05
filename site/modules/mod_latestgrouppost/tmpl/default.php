<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die('Restricted access');
?>

<?php
if (count($groups) > 0)
{
?>

<style type="text/css">
ul.mod_latestgroupwalls {
	padding: 0;
	margin: 0;
	list-style: none;
}

ul.mod_latestgroupwalls li {
	background: none;
	padding: 5px 0 !important; 
	border: none;
}
ul.mod_latestgroupwalls li + li {
	border-top: 1px solid #CCC;
}
</style>


<div>
<?php
	$charactersCount	= $params->get('charcount' , 100 );

	if(is_array($groups) && !empty($groups)){
	
	//$key indicates the group id
	foreach( $groups as $key=>$groupPost )
	{
		$groupId        = $key;
		$groupname      = CStringHelper::escape($groupPost[0]->groupname);
		$grouplink 		= CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId );
		
		/*
		$user			= CFactory::getUser( $wall->actor );
		$wall->comment	= CComment::stripCommentData( $group->title );
		$comment		= JString::substr( $wall->comment , 0 , $charactersCount);
		$comment		.= ( $charactersCount > JString::strlen( $wall->comment ) ) ? '' : '...';

		$groupId        = $wall->groupid;
		$groupname      = CStringHelper::escape($wall->groupname);
		$grouplink 		= CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId );

		$table	= JTable::getInstance( 'Group' , 'CTable' );
		$table->load( $groupId );
		$groupavatar = $table->getThumbAvatar();
		*/
?>
	<div>
	<!-- Group name -->
	<div>
		<strong><?php echo $groupname; ?></strong>
	</div>
	<ul class="mod_latestgroupwalls <?php echo $params->get('moduleclass_sfx'); ?>">
	<?php 
		foreach($groupPost as $post_info): 
		$user			= CFactory::getUser( $post_info->actor );
		$comment	= CComment::stripCommentData( $post_info->title );
		$comment		= JString::substr( $comment , 0 , $charactersCount);
		$comment		.= ( $charactersCount > JString::strlen( $comment ) ) ? '' : '...';
	?>
	<li>
		<div style="float: left;">
                    <a title="<?php echo $user->getDisplayName();?>" href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$user->id); ?>">
				<img style="width: 32px; padding: 2px; border: 1px solid rgb(204, 204, 204);" src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $groupname; ?>" />
			</a>
		</div>
		
		<div style="margin-left: 42px; line-height: normal;">
			<span style="display: block;margin-top: 3px;"><a href="<?php echo $grouplink; ?>"><?php echo $comment;?></a></span>
			<span style="width: 100%;"><?php echo JText::sprintf('MOD_LATESTGROUPPOST_BY', '<a href="'.CRoute::_('index.php?option=com_community&view=profile&userid='.$user->id).'">'.$user->getDisplayName().'</a>'); ?></span>
			
		</div>
		<div style="clear: both;"></div>
	</li>
	<?php endforeach; ?>
	<ul class="mod_latestgroupwalls<?php echo $params->get('moduleclass_sfx'); ?>">
	</div>
<?php
	}
	}
?>
</div>
<?php
}
?>