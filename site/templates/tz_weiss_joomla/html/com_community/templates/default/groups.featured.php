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
<?php if ($groups && $showFeatured) { ?>

	<!-- Slider Kit compatibility -->
		<!--[if IE 6]><?php CAssets::attach('assets/featuredslider/sliderkit-ie6.css', 'css'); ?><![endif]-->
		<!--[if IE 7]><?php CAssets::attach('assets/featuredslider/sliderkit-ie7.css', 'css'); ?><![endif]-->
		<!--[if IE 8]><?php CAssets::attach('assets/featuredslider/sliderkit-ie8.css', 'css'); ?><![endif]-->

		<!-- Slider Kit scripts -->
		<?php CAssets::attach('assets/featuredslider/sliderkit/jquery.sliderkit.1.8.js', 'js'); ?>

		<!-- Slider Kit launch -->
		<script type="text/javascript">
			joms.jQuery(window).load(function(){

				<?php if(JRequest::getVar('limitstart')!="" || JRequest::getVar('sort')!="" || JRequest::getVar('categoryid')!=""){?>
					var target_offset = joms.jQuery("#lists").offset();
					var target_top = target_offset.top;
					joms.jQuery('html, body').animate({scrollTop:target_top}, 200);
				<?php } ?>

				jax.call('community' , 'groups,ajaxShowGroupFeatured' , <?php echo $groups[0]->id; ?> );

				joms.jQuery(".featured-group").sliderkit({
					shownavitems:3,
					scroll:<?php echo $config->get('featuredgroupscroll'); ?>,
					// set auto to true to autoscroll
					auto:false,
					mousewheel:true,
					circular:true,
					scrollspeed:500,
					autospeed:10000,
					start:0
				});
				joms.jQuery('.cBoxPad').click(function(){
					var group_id = joms.jQuery(this).parent().attr('id');
					group_id = group_id.split("cPhoto");
					group_id = group_id[1];
					jax.call('community' , 'groups,ajaxShowGroupFeatured' , group_id );
				});



			});

			function updateGroup(groupId, title, categoryName, likes, avatar, groupDate, groupLink,  groupDesc, membercount, discussion, wallposts, memberCountLink, groupUnfeature )
			{
				joms.jQuery('#like-container').html(likes);
				joms.jQuery('#group-title').html(title);
				joms.jQuery('.group-date').html(groupDate);
				joms.jQuery('#community-group-data-category').html(categoryName);
				joms.jQuery('.cFeaturedTop .cFeaturedThumb img').attr('src',avatar);
				groupLink = groupLink.replace(/\&amp;/g,'&');
				joms.jQuery(joms.jQuery('.cFeaturedTop .cFeaturedThumb a')[0]).attr('href',groupLink);
				joms.jQuery('.group-link').attr('href', groupLink);
				joms.jQuery('.group-desc').html(groupDesc);
				joms.jQuery('.album-actions').html(groupUnfeature);
				joms.jQuery('#group-membercount').html(membercount);
				joms.jQuery('#group-discussion').html(discussion);
				joms.jQuery('#group-wallposts').html(wallposts);
				memberCountLink = memberCountLink.replace(/\&amp;/g,'&');
				joms.jQuery('#group-membercount-link').attr('href',memberCountLink);
				joms.jQuery('.cSlider-selected').removeClass('cSlider-selected');
				joms.jQuery('#cPhoto'+groupId).addClass('cSlider-selected');

				if(joms.jQuery('.cFeatured-PageCover.cFeaturedThumb b a').length > 0)
				{
					joms.jQuery('.cFeatured-PageCover.cFeaturedThumb b a').attr('onClick','joms.featured.remove('+groupId+',\'groups\');')
				}
			}

		</script>

<div id="cFeatured" class="cFeatured-Group">

	<div class="cFeaturedTop">
	<div class="clearfull">
		<div class="cFeatured-PageCover cFeaturedThumb cFloat-L">
			<a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groups[0]->id );?>">
				<img src="<?php echo $groups[0]->getAvatar( 'avatar' ); ?>" alt="<?php echo $this->escape($groups[0]->name);?>" />
			</a>

			<?php if( $isCommunityAdmin ){?>
			<b>
				<a title="<?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?>" onclick="joms.featured.remove('<?php echo $groups[0]->id;?>','groups');" href="javascript:void(0);"><?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?></a>
			</b>
			<?php } ?>

			<div id="like-container" class="cFeaturedLike"></div>
		</div>




		<!-- group Information -->
		<div class="cFeaturedInfo Page">
			<!-- Title -->
			<div class="cFeaturedTitle">
				<a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groups[0]->id );?>" class="group-link"><span id="group-title"><?php echo $groups[0]->name; ?></span></a>
			</div>

			<ul class="cFeaturedMeta cFloatedList cResetList clearfull">
				<li>
					<i class="com-icon-groups"></i>
					<a id="group-membercount-link" href="<?php echo CRoute::_( 'index.php?option=com_community&view=groups&task=viewmembers&groupid=' . $groups[0]->id ); ?>">
						<span id="group-membercount"><?php echo JText::sprintf((CStringHelper::isPlural($groups[0]->membercount)) ? 'COM_COMMUNITY_GROUPS_MEMBER_COUNT_MANY':'COM_COMMUNITY_GROUPS_MEMBER_COUNT', $groups[0]->membercount);?></span>
					</a>
				</li>
				<li>
					<i class="com-icon-comment"></i>
					<span id="group-discussion">
						<?php echo JText::sprintf((CStringHelper::isPlural($groups[0]->discusscount)) ? 'COM_COMMUNITY_GROUPS_DISCUSSION_COUNT_MANY' :'COM_COMMUNITY_GROUPS_DISCUSSION_COUNT', $groups[0]->discusscount);?>
					</span>
				</li>
				<li>
					<i class="com-icon-wall"></i>
					<span id="group-wallposts">
						<?php echo JText::sprintf((CStringHelper::isPlural($groups[0]->wallcount)) ? 'COM_COMMUNITY_GROUPS_WALL_COUNT_MANY' : 'COM_COMMUNITY_GROUPS_WALL_COUNT', $groups[0]->wallcount);?>
					</span>
				</li>
			</ul>


			<div class="group-desc">
				<?php echo JHTML::_('string.truncate', strip_tags($groups[0]->description ), 300); ?>
			</div>

			<div class="cFeaturedExtra">
				<ul class="cFeaturedMeta cFloatedList cResetList clearfull">
					<li>
						<?php echo JText::_('COM_COMMUNITY_EVENTS_CATEGORY'); ?>:
						<b id="community-group-data-category"><?php echo JText::_( $groups[0]->getCategoryName() ); ?></b>
					</li>
					<li>
						<span><?php echo JText::_('COM_COMMUNITY_GROUPS_CREATE_TIME');?></span> :
						<b class="group-date"> <?php echo JHTML::_('date', $groups[0]->created, JText::_('DATE_FORMAT_LC')); ?></b>
					</li>
				</ul>
			</div>

		</div><!--.cFeaturedTop -->
	</div>
	</div><!--.group-main-->

	<!-- navigation container -->
	<div class="cFeaturedBottom">
		<div class="cSlider featured-group">
			<div class="cSlider-Wrap cSlider-nav">
				<div class="cSlider-Clip cSlider-nav-clip">
					<ul class="cSlider-List Groups cFloatedList cResetList clearfix">

						<?php foreach($groups as $group) { ?>
						<li id="cPhoto<?php echo $group->id; ?>" class="<?php echo $group->id;?>">
							 <div id="<?php echo $group->id; ?>" class="cBoxPad">

							 	<div class="cFeaturedThumb">
									<a href="javascript:void(0);" class="cFeaturedAvatar">
										<img src="<?php echo $group->getThumbAvatar(); ?>" alt="<?php echo $this->escape($group->name);?>" />
									</a>
								</div>

								<div class="cFeaturedTitle"><b><?php echo CStringHelper::truncate(strip_tags($group->name),25);?></b></div>
								<div class="cFeaturedMeta"><?php echo JText::sprintf((CStringHelper::isPlural($group->membercount)) ? 'COM_COMMUNITY_GROUPS_MEMBER_COUNT_MANY':'COM_COMMUNITY_GROUPS_MEMBER_COUNT', $group->membercount);?></div>
							</div>
						</li>
						<?php
							} // end foreach
						?>
					</ul>
				</div>
				<div class="cSlider-btn cSlider-nav-btn cSlider-nav-prev"><a href="javascript:void(0);" title="<?php echo JText::_('COM_COMMUNITY_PREVIOUS_BUTTON');?>"><span>Previous</span></a></div>
				<div class="cSlider-btn cSlider-nav-btn cSlider-nav-next"><a href="javascript:void(0);" title="<?php echo JText::_('COM_COMMUNITY_NEXT_BUTTON');?>"><span>Next</span></a></div>
			</div>
		</div><!--.cSlider-->
	</div>

</div><!--#cFeatured-->
<?php
}
