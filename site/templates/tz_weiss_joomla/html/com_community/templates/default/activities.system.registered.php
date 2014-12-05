<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

$usersModel			= CFactory::getModel( 'user' );
$now				= new JDate();
$date				= CTimeHelper::getDate();
$users				= $usersModel->getLatestMember(10);
$totalRegistered	= count($users);
$title				= JText::sprintf('COM_COMMUNITY_TOTAL_USERS_REGISTERED_THIS_MONTH_ACTIVITY_TITLE', $totalRegistered , $date->monthToString($now->format('%m')));

?>

<div class="joms-stream-box joms-responsive clearfix" style="margin-bottom:0px;">

  <aside>
      <i class="joms-icon-bullhorn joms-icon-thumbnail"></i>
  </aside>

	<article>
		<a href="javascript:void(0);">
			<i class="joms-icon-bullhorn portrait-phone-only"></i> <?php echo JText::_('COM_COMMUNITY_LAST_10_USERS_REGISTERED'); ?>
		</a>

		<div class="separator"></div>

		<?php if($totalRegistered > 0) { ?>

			<ul class="unstyled">
			<?php foreach($users  as $user ) { ?>
			<?php
				$registerDate = $user->registerDate;
			?>
				<li class="clearfix space-12">
					<img class="pull-left joms-stream-avatar" style="margin-right:8px;width:36px;" src="<?php echo $user->getThumbAvatar(); ?>" >
					<div>
						<a href="<?php echo CUrlHelper::userLink($user->id); ?>">
							<h5 class="reset-gap"><?php echo $user->getDisplayName(); ?></h5>
						</a>
							<p class="reset-gap">
								<?php echo JText::_('COM_COMMUNITY_MEMBER_SINCE'); ?>: <?php echo JHTML::_('date', $registerDate , JText::_('DATE_FORMAT_LC1')); ?>
							</p>
					</div>
				</li>
			<?php } ?>
			</ul>
		<?php } ?>

	</article>

</div>
