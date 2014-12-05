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
<div class="cPageActions clearfix">
	<div class="cPageAction cFloat-R">
		<?php echo $bookmarksHTML;?>
	</div>
	<div class="cPageMeta cFloat-L">
		<i class="update-icon com-icon-bell"></i>
		<span><?php echo JHTML::_('date' , $bulletin->date, JText::_('DATE_FORMAT_LC')); ?></span>
	</div>
</div>

<div class="row-fluid">
	<div class="span8">

		<div class="cMain">
			<div class="cPageStory clearfull">
				<!--cPageStory-Author-->
				<a class="cPageStory-Author cFloat-L" href="<?php echo CUrlHelper::userLink($creator->id); ?>"><img class="cAvatar" src="<?php echo $creator->getThumbAvatar(); ?>" border="0" alt="" /></a>

				<!--cPageStory-Content-->
				<div class="cPageStory-Content"><?php echo $bulletin->message;?></div>

				<!--cPageStory-Editor-->
				<div class="cPageStory-Editor" id="bulletin-edit-data" style="display: none;">
					<form class="cForm" name="addnews" method="post" action="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=editnews'); ?>">
						<ul class="cFormList cFormVertical cResetList">
							<li>
								<label class="form-label" for="title"><?php echo JText::_('COM_COMMUNITY_GROUPS_BULETIN_TITLE');?></label>
								<div class="form-field">
									<input type="text" value="<?php echo $bulletin->title;?>" id="title" name="title" class="input text" style="width: 94%;" />
								</div>
							</li>
							<li>
								<label class="form-label" for="description"><?php echo JText::_('COM_COMMUNITY_GROUPS_BULLETIN_DESCRIPTION');?></label>
								<div class="form-field">
									<?php if( $config->get( 'htmleditor' ) == 'none' && $config->getBool('allowhtml') ) { ?>
										<div class="htmlTag"><?php echo JText::_('COM_COMMUNITY_HTML_TAGS_ALLOWED'); ?></div>
									<?php } ?>

									<?php

										if( !CStringHelper::isHTML($editorMessage)
											&& $config->get('htmleditor') != 'none'
											&& $config->getBool('allowhtml') )
										{
											$editorMessage = CStringHelper::nl2br($editorMessage);
										}

										if( $config->get( 'htmleditor' ) )
										{
									?>
									<script type="text/javascript">
									function saveContent()
									{
										<?php echo $editor->saveText( 'message' ); ?>
										return true;
									}
									</script>
									<?php echo $editor->displayEditor( 'message',  $editorMessage , '98%', '450', '10', '20' , false ); ?>
									<?php
										}
										else
										{
									?>
										<textarea class="input textarea" style="width: 94%; margin: 10px 0;" name="message"><?php echo $editorMessage;?></textarea>
									<?php
										}
									?>
								</div>
							</li>
							<?php if($gparams->get('groupannouncementfilesharing') > 0){ ?>
							<li class="has-seperator">
								<div class="form-field">
									<label for="filepermission-member" class="label-checkbox">
										<input type="checkbox" class="input checkbox" name="filepermission-member" value="1" <?php echo ($params->get('filepermission-member') > 0) ? 'checked="checked"' : '' ?> />
										<?php echo JText::_('COM_COMMUNITY_FILES_ALLOW_MEMBERS')?>
									</label>
								</div>
							</li>
							<?php }?>
							<li class="has-seperator">
								<div class="form-field">
									<input type="hidden" value="<?php echo $bulletin->groupid;?>" name="groupid" />
									<input type="hidden" value="<?php echo $bulletin->id;?>" name="bulletinid" />
									<?php echo JHTML::_( 'form.token' ); ?>

									<input type="button" class="btn" onclick="joms.groups.editBulletin();return false;" value="<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON'); ?>" />
									<input type="submit" class="btn btn-primary" value="<?php echo JText::_('COM_COMMUNITY_SAVE_BUTTON'); ?>" class="button" onclick="saveContent();" />
								</div>
							</li>
						</ul>
					</form>
				</div>
				<!--Buletin : Edit Entry-->
			</div>

		</div>

	</div>
	<div class="span4">
		<div class="cSidebar">
			<?php echo $filesharingHTML;?>
		</div>
	</div>
</div>




