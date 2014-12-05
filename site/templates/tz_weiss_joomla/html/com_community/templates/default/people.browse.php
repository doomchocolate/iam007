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
//CFactory::load( 'libraries' , 'messaging' );

if( $featuredList && $showFeaturedList )
{

	$firstuser = CFactory::getUser( $featuredList[0] );

?>
	<!-- Slider Kit compatibility -->
		<!--[if IE 6]><?php CAssets::attach('assets/featuredslider/sliderkit-ie6.css', 'css'); ?><![endif]-->
		<!--[if IE 7]><?php CAssets::attach('assets/featuredslider/sliderkit-ie7.css', 'css'); ?><![endif]-->
		<!--[if IE 8]><?php CAssets::attach('assets/featuredslider/sliderkit-ie8.css', 'css'); ?><![endif]-->

		<!-- Slider Kit scripts -->
		<?php CAssets::attach('assets/featuredslider/sliderkit/jquery.sliderkit.1.8.js', 'js'); ?>

		<!-- Slider Kit launch -->
		<script type="text/javascript">
			joms.jQuery(window).load(function(){

				<?php if(JRequest::getVar('limitstart')!="" || JRequest::getVar('sort')!=""){?>
					var target_offset = joms.jQuery("#lists").offset();
					var target_top = target_offset.top;
					joms.jQuery('html, body').animate({scrollTop:target_top}, 200);
				<?php } ?>

				jax.call('community' , 'profile,ajaxShowProfileFeatured' , <?php echo $firstuser->id; ?> );

				joms.jQuery(".featured-user").sliderkit({
					shownavitems:3,
					scroll:<?php echo $config->get('featuredmemberscroll'); ?>,
					// set auto to true to autoscroll
					auto:false,
					mousewheel:true,
					circular:true,
					scrollspeed:500,
					autospeed:10000,
					start:0
				});
				joms.jQuery('.cBoxPad').click(function(){
					var user_id = joms.jQuery(this).parent().attr('id');
					user_id = user_id.split("cPhoto");
					user_id = user_id[1];

					 jax.call('community' , 'profile,ajaxShowProfileFeatured' , user_id );
				});
			});

			function updateFeaturedProfile(userId, username,  likes, avatar,  userLink, userUnfeature, userStatus, friendList ){
				joms.jQuery('#like-container').html(likes);
				joms.jQuery('#user-name').html(username);
				joms.jQuery('#user-status').html(userStatus);
				joms.jQuery('#user-avatar').attr('src',avatar);
				userLink = userLink.replace(/\&amp;/g,'&');
				joms.jQuery('.user-link').attr('href',userLink);
				joms.jQuery('.album-actions').html(userUnfeature);
				joms.jQuery('#friend-list').html(friendList);

			}
		</script>

		<div id="cFeatured">

			<!--.user-main-->
			<div class="cFeaturedTop cFeaturedFriends">
				<div class="cFeatured-PageCover cFeaturedThumb cFloat-L">
					<a class="user-link" href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $firstuser->id );?>" id="user-link">
						<img id="user-avatar" src="<?php echo $firstuser->getAvatar( 'avatar' ); ?>" />
					</a>

					<?php if( $isCommunityAdmin ){?>
					<b>
						<a title="<?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?>" onclick="joms.featured.remove('<?php echo $firstuser->id;?>','search');" href="javascript:void(0);"><?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?></a>
					</b>
					<?php } ?>

					<div id="like-container" class="cFeaturedLike"></div>
				</div>

				<!-- member information Information -->
				<div class="cFeaturedInfo Page">
					<!-- Title -->
					<div class="cFeaturedTitle">
						<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $firstuser->id );?>" class="user-link">
							<span id="user-name"><?php echo $firstuser->getDisplayName(); ?></span>
						</a>
					</div>
					<div id="user-status"><?php echo $firstuser->getStatus(); ?></div>
					<div id="friend-list"></div>
				</div><!--.group-info -->

				<!-- group Top: App Like -->
				<div class="jsApLike">

					<div class="clear"></div>
				</div>
				<!-- end: App Like -->
				<div style="clear: left;"></div>
			</div>

			<!-- navigation container -->
			<div class="cFeaturedBottom cFeaturedContent">
				<!--#####SLIDER#####-->
				<div class="cSlider featured-user">
					<div class="cSlider-Wrap cSlider-nav">
						<div class="cSlider-Clip cSlider-nav-clip">
							<ul class="cSlider-List Groups cFloatedList cResetList clearfix">

								<?php $x = 0; foreach($featuredList as $id) { $user = CFactory::getUser( $id ); ?>
							<li id="cPhoto<?php echo $user->id; ?>" class="<?php echo $user->id;?>">
									 <div id="<?php echo $x; ?>" class="cBoxPad cBoxBorder cBoxBorderLow">

									 	<div class="cFeaturedThumb">
											<a class="cFeaturedAvatar" href="javascript:void(0);">
												<img src="<?php echo $user->getThumbAvatar();?>" alt="<?php echo $user->getDisplayName(); ?>"/>
											</a>
											<?php if( $isCommunityAdmin ) { ?>
											<b>
												<a onclick="joms.featured.remove('<?php echo $user->id;?>','search');" href="javascript:void(0);" title="<?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?>" class="album-action remove-featured">
													<i class="com-icon-award-minus"></i>
												</a>
											</b>
											<?php } ?>
										</div>

										<div class="cFeaturedTitle">
											<b><?php echo JHTML::_('string.truncate', strip_tags($user->getDisplayName()),25);?></b>
										</div>

										<div class="cFeaturedMeta Action-Icons">
											<a class="cFeatured-icons" onclick="<?php echo CMessaging::getPopup($user->id); ?>" href="javascript:void(0);" title="<?php echo JText::_('COM_COMMUNITY_INBOX_SEND_MESSAGE'); ?>">
												<i class="com-icon-mail-go"></i>
											</a>
											<?php if( isset($alreadyfriend) && isset($alreadyfriend[$user->id]) ): ?>
											<a class="cFeatured-icons" onclick="joms.friends.connect('<?php echo $user->id; ?>')" href="javascript:void(0)" >
												<i class="com-icon-user-plus"></i>
											</a>
											<?php endif; ?>
										</div>
									</div>
							</li>
							<?php
									$x++;
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
	<div class="clear"></div>
<?php
}
?>
<?php echo $sortings; ?>
<?php if( !empty( $data ) ) { ?>
	<a id="lists" name="listing"></a>

	<ul class="cIndexList forFriendsList cResetList">
	<?php foreach( $data as $row ) : ?>
		<?php $displayname = $row->user->getDisplayName(); ?>
		<?php if(!empty($row->user->id) && !empty($displayname)) : ?>
		<li>
		<div class="cIndex-Box clearfix">
			<a href="<?php echo $row->profileLink; ?>" class="cIndex-Avatar cFloat-L">
				<img class="cAvatar" src="<?php echo $row->user->getThumbAvatar(); ?>" alt="<?php echo $row->user->getDisplayName(); ?>" />
				<?php if($row->user->isOnline()): ?>
				<b class="cStatus-Online">
					<?php echo JText::_('COM_COMMUNITY_ONLINE'); ?>
				</b>
				<?php endif; ?>
			</a>
			<div class="cIndex-Content">
				<h3 class="cIndex-Name cResetH">
					<a href="<?php echo $row->profileLink; ?>"><?php echo $row->user->getDisplayName(); ?></a>
				</h3>
				<div class="cIndex-Status"><?php echo $row->user->getStatus() ;?></div>
				<div class="cIndex-Actions">

					<?php if( $config->get('enablepm') && $my->id != $row->user->id && $my->id != 0): ?>
						<div>
							<i class="com-icon-mail-go"></i>
							<a onclick="<?php echo CMessaging::getPopup($row->user->id); ?>" href="javascript:void(0);">
								<?php echo JText::_('COM_COMMUNITY_INBOX_SEND_MESSAGE'); ?>
							</a>
						</div>
					<?php endif; ?>

					<div>
						<i class="com-icon-groups"></i>
						<span><?php echo JText::sprintf( (CStringHelper::isPlural($row->friendsCount)) ? 'COM_COMMUNITY_FRIENDS_COUNT_MANY' : 'COM_COMMUNITY_FRIENDS_COUNT', $row->friendsCount);?></span>
					</div>

					<?php
					if($row->addFriend)
					{
						$isWaitingApproval = CFriendsHelper::isWaitingApproval($my->id, $row->user->id);
					?>
						<div>
							<?php if(isset($row->isMyFriend) && $row->isMyFriend==1){ ?>
								<i class="com-icon-info"></i>
								<a href="javascript:void(0)" onclick="joms.friends.connect('<?php echo $row->user->id;?>')"><span><?php echo JText::_('COM_COMMUNITY_PROFILE_PENDING_FRIEND_REQUEST'); ?></span></a>
							<?php } else { ?>
								<?php if(!$isWaitingApproval){?>
									<i class="com-icon-user-plus"></i>
									<a href="javascript:void(0)" onclick="joms.friends.connect('<?php echo $row->user->id;?>')"><span><?php echo JText::_('COM_COMMUNITY_PROFILE_ADD_AS_FRIEND'); ?></span></a>
								<?php }else{ ?>
									<i class="com-icon-info"></i> <span><?php echo JText::_('COM_COMMUNITY_PROFILE_PENDING_FRIEND_REQUEST'); ?></span>
								<?php }?>
							<?php } ?>
						</div>
					<?php
					}
					else
					{
					?>
					<?php
						if(($my->id != $row->user->id) && ($my->id !== 0))
						{
					?>
						<div>
							<i class="com-icon-tick"></i> <span><?php echo JText::_('COM_COMMUNITY_PROFILE_ADDED_AS_FRIEND'); ?></span>
						</div>
					<?php
						}
					}
					?>

					<?php
					if( $isCommunityAdmin && isset($featuredList))
					{
						if( !in_array($row->user->id, $featuredList) )
						{
					?>
					<div id="featured-<?php echo $row->user->id;?>" class="cIndex-Feature">

							<a onclick="joms.featured.add('<?php echo $row->user->id;?>','search');"
							   href="javascript:void(0);"
							   class="btn Icon"
							   title="<?php echo JText::_('COM_COMMUNITY_MAKE_FEATURED'); ?>">
								<i class="com-icon-award-plus"></i>
							</a>
					</div>
					<?php
						}
					}
					?>
				</div>
			</div>

		</div>
		</li>
		<?php endif; ?>
	<?php endforeach; ?>
	</ul>

	<?php

	if ( !empty($pagination)  )
	{
	?>
	<div class="cPagination">
		<?php echo $pagination->getPagesLinks(); ?>
	</div>
	<?php
	}
	?>

<?php
	}
	else
	{
?>
		<div class="cAlert cNotFound-People"><?php echo JText::_('COM_COMMUNITY_SEARCH_NO_RESULT');?></div>
<?php
	}
?>
