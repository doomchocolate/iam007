<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<form action="index.php" id="adminForm" method="post" name="adminForm" enctype="multipart/form-data">
<div id="config-document">
<?php
switch ($this->cfgSection) {
	case 'site':
		?>
		<div class="row-fluid">
			<div class="span12">
				<?php require_once( dirname(__FILE__) . '/reportings.php' ); ?>
				<div class="space-12"></div>
				<?php require_once( dirname(__FILE__) . '/advance_search.php' ); ?>
				<div class="space-12"></div>
				<?php require_once( dirname(__FILE__) . '/cronprocess.php' ); ?>
				<div class="space-12"></div>
				<?php require_once( dirname(__FILE__) . '/registrations.php' ); ?>
				<div class="space-12"></div>
				<?php require_once( dirname(__FILE__) . '/activity.php' ); ?>
				<div class="space-12"></div>
				<?php require_once( dirname(__FILE__) . '/likes.php' ); ?>
			</div>
			<div class="span12">
				<?php require_once( dirname(__FILE__) . '/frontpage.php' ); ?>
				<div class="space-12"></div>
				<?php require_once( dirname(__FILE__) . '/bookmarkings.php' ); ?>
				<div class="space-12"></div>
				<?php require_once( dirname(__FILE__) . '/featured.php' ); ?>
				<div class="space-12"></div>
				<?php require_once( dirname(__FILE__) . '/messaging.php' ); ?>
				<div class="space-12"></div>
				<?php require_once( dirname(__FILE__) . '/walls.php' ); ?>
				<div class="space-12"></div>
				<?php require_once( dirname(__FILE__) . '/time_offset.php' ); ?>
				<div class="space-12"></div>
				<?php require_once( dirname(__FILE__) . '/email.php' ); ?>
				<div class="space-12"></div>
				<?php require_once( dirname(__FILE__) . '/status.php' ); ?>
				<div class="space-12"></div>
				<?php require_once( dirname(__FILE__) . '/profile.php' ); ?>
				<div class="space-12"></div>
				<?php require_once( dirname(__FILE__) . '/filtering.php' ); ?>
			</div>
		</div>
		<?php
		break;
	case 'anti-spam':
		?>
		<div class="row-fluid">
			<div class="span12">
				<?php require_once( dirname(__FILE__) . '/akismet.php' ); ?>
			</div>
			<div class="span12">
				<?php require_once( dirname(__FILE__) . '/limits.php' ); ?>
			</div>
		</div>
		<?php
		break;
	case 'layout':
		?>
		<div class="row-fluid">
			<div class="span12">
				<div class="space-12"></div>
				<?php require_once( dirname(__FILE__) . '/featured_listing.php' ); ?>
			</div>
			<div class="span12">
				<?php require_once( dirname(__FILE__) . '/display.php' ); ?>
				<div class="space-12"></div>
				<?php require_once( dirname(__FILE__) . '/frontpage_options.php' ); ?>
			</div>
		</div>
		<?php
		break;
	case 'privacy':
		?>
		<?php require_once( dirname(__FILE__) . '/privacy.php' ); ?>
		<?php
		break;
	case 'facebook-connect':
		?>
		<div class="row-fluid">
			<div class="span12">
				<?php require_once( dirname(__FILE__) . '/facebook_api.php' ); ?>
			</div>
			<div class="span12">
				<?php require_once( dirname(__FILE__) . '/facebook_imports.php' ); ?>
			</div>
		</div>

		<?php
		break;
	case 'remote-storage':
		?>
		<div class="row-fluid">
			<div class="span12">
				<?php require_once( dirname(__FILE__) . '/remote_storage_methods.php' ); ?>
			</div>
			<div class="span12">
				<?php require_once( dirname(__FILE__) . '/remote_storage_s3.php' ); ?>
			</div>
		</div>
		<?php
		break;
	case 'integrations':
		?>
		<div class="row-fluid">
			<div class="span12">
				<?php require_once( dirname(__FILE__) . '/google.php' ); ?>
			</div>
		</div>
		<?php
		break;
	case 'karma':
		?>
		<div class="row-fluid">
			<div class="span12">
				<?php require_once( dirname(__FILE__) . '/karma.php' ); ?>
			</div>
		</div>
		<?php
		break;
	case 'video':
		?>
		<div class="row-fluid">
			<div class="span24">
				<?php require_once( dirname(__FILE__) . '/videos.php' ); ?>
			</div>
		</div>
		<?php
		break;
	case 'group':
		?>
		<div class="row-fluid">
			<div class="span24">
				<?php require_once( dirname(__FILE__) . '/groups.php' ); ?>
			</div>
		</div>
		<?php
		break;
	case 'event':
		?>
		<div class="row-fluid">
			<div class="span24">
				<?php require_once( dirname(__FILE__) . '/events.php' ); ?>
			</div>
		</div>
		<?php
		break;
	case 'photo':
		?>
		<div class="row-fluid">
			<div class="span24">
				<?php require_once( dirname(__FILE__) . '/photos.php' ); ?>
			</div>
		</div>
		<?php
		break;
}
?>

</div>
<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="task" value="saveconfig" />
<input type="hidden" name="view" value="configuration" />
<input type="hidden" name="cfgSection" value="<?php echo $this->cfgSection; ?>" />
<input type="hidden" name="option" value="com_community" />
</form>