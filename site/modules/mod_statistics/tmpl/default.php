<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div id="cModule-Statistic" class="cMods cMods-Statistic<?php echo $params->get( 'moduleclass_sfx' ) ?>">
	<b>
		<?php echo JText::_('MOD_LATESTDISC_STATISTICS');?>
	</b>
	<?php
		foreach($stats as $stat)
		{
			switch($stat)
			{
				case 't_members':
					$name = JText::_("MOD_LATESTDISC_MEMBERS");
					$total = $params->get('t_members');
					break;
				case 't_groups':
					$name = JText::_("MOD_LATESTDISC_GROUPS");
					$total = $params->get('t_groups');
					break;
				case 't_discussions':
					$name = JText::_("MOD_LATESTDISC_DISCUSSION");
					$total = $params->get('t_discussions');
					break;
				case 't_albums':
					$name = JText::_("MOD_LATESTDISC_ALBUMS");
					$total = $params->get('t_albums');
					break;
				case 't_photos':
					$name = JText::_("MOD_LATESTDISC_PHOTOS");
					$total = $params->get('t_photos');
					break;
				case 't_videos':
					$name = JText::_("MOD_LATESTDISC_VIDEOS");
					$total = $params->get('t_videos');
					break;
				case 't_bulletins':
					$name = JText::_("MOD_LATESTDISC_BULLETINS");
					$total = $params->get('t_bulletins');
					break;
				case 't_activities':
					$name = JText::_("MOD_LATESTDISC_ACTIVITIES");
					$total = $params->get('t_activities');
					break;
				case 't_walls':
					$name = JText::_("MOD_LATESTDISC_WALL_POSTS");
					$total = $params->get('t_walls');
					break;
				case "t_events":
					$name	= JText::_("MOD_LATESTDISC_EVENTS");
					$total	= $params->get('t_events');
					break;
				case 'genders':
					$male = JText::_("MOD_LATESTDISC_MALES");
					$female = JText::_("MOD_LATESTDISC_FEMALES");
					$unspecified = JText::_("MOD_LATESTDISC_UNSPECIFIED");						
					$total_males = $params->get('t_gender_males');
					$total_females = $params->get('t_gender_females');
					$total_unspecified = $params->get('t_gender_unspecified');
					break;
			}
			
			if($stat == "genders")
			{
				if($params->get('genders_male', 0))
				{
	?>
		        <div title="<?php echo JText::_("MOD_LATESTDISC_TOTAL") . " " . $male; ?> : <?php echo $total_males; ?>">
		            <span><?php echo JText::_("MOD_LATESTDISC_TOTAL") . " " . $male; ?>:</span>
		            <?php echo $total_males; ?>
		        </div>
	<?php 
				}
				if($params->get('genders_female', 0))
				{
	?>
		        <div title="<?php echo JText::_("MOD_LATESTDISC_TOTAL") . " " . $female; ?> : <?php echo $total_females; ?>">
		            <span><?php echo JText::_("MOD_LATESTDISC_TOTAL") . " " . $female; ?>:</span>
		            <?php echo $total_females; ?>
		        </div>
	<?php 
				}
				if($params->get('genders_unspecified', 0)){
	?>
		        <div title="<?php echo JText::_("MOD_LATESTDISC_TOTAL") . " " . $unspecified; ?> : <?php echo $total_unspecified; ?>">
		            <span><?php echo JText::_("MOD_LATESTDISC_TOTAL") . " " . $unspecified; ?>:</span>
		            <?php echo $total_unspecified; ?>
		        </div>
	<?php 
				}
			}
			else
			{
	?>
	        <div title="<?php echo JText::_("MOD_LATESTDISC_TOTAL") . " " . $name; ?> : <?php echo $total; ?>">
	            <span><?php echo JText::_("MOD_LATESTDISC_TOTAL") . " " . $name; ?>:</span>
	            <?php echo $total; ?>
	        </div>
	<?php
			}
		}
	?>
</div>