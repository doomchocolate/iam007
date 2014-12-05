<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

$user = CFactory::getUser($this->act->actor);

// Setup event table
$group = JTable::getInstance('Group', 'CTable');
$group->load($act->groupid);
$this->set('group', $group);

if(!empty($this->act->params)){
     if (!is_object($this->act->params)) {
        $act->params = new JRegistry($this->act->params);
    }
	// Load params
	$action = $this->act->params->get('action');
	$actors = $this->act->params->get('actors');
}
else {
	$action='';
	$actors='';
}
$this->set('actors', $actors);
?>

	<?php if( $this->act->app == 'groups.wall') { ?>
		<?php $this->load('activities.profile'); ?>
	<?php } else if( $this->act->app == 'groups.featured') { ?>
		<?php $this->load('activities.groups.featured'); ?>
	<?php } else if( $action == 'group.create') { ?>
		<?php $this->load('activities.groups.create'); ?>
	<?php } else if( $action == 'group.update') { ?>
		<?php $this->load('activities.groups.update'); ?>
	<?php } else if( $action == 'group.join') { ?>
		<?php $this->load('activities.groups.join'); ?>
	<?php } else if( $action == 'group.discussion.create') { ?>
		<?php $this->load('activities.groups.discussion.create'); ?>
	<?php } else if( $action == 'group.discussion.reply') { ?>
		<?php $this->load('activities.groups.discussion.reply'); ?>
	<?php } else if( $this->act->app == 'groups.bulletin') { ?>
		<?php $this->load('activities.groups.bulletin'); ?>
	<?php } else { ?>
	<?php
		$table = JTable::getInstance('Activity','CTable');
		$table->load($this->act->id);
		if(!$table->delete()){
	?>
<a class="cStream-Avatar cFloat-L" href="<?php echo CUrlHelper::userLink($user->id); ?>">
	<img class="cAvatar" data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>">
</a>

<div class="cStream-Content">
	<div class="cStream-Attachment">
		<?php
		$html = CGroups::getActivityContentHTML($act);
		echo $html;
		?>
	</div>
<?php $this->load('activities.actions'); ?>
</div>
<?php }} ?>