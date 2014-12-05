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

require_once( JPATH_ROOT .'/components/com_community/libraries/core.php');
if(JFile::exists( JPATH_ADMINISTRATOR .'/components/com_kunena/libraries/api.php' ))
{
	require_once(JPATH_ADMINISTRATOR .'/components/com_kunena/libraries/api.php');
}
if(!class_exists('plgCommunityKunena'))
{
	class plgCommunityKunena extends CApplications
	{
			var $name 		= "My on Kunena";
			var $_name		= 'Kunena';
			var $_path		= '';

			var $db_prefix = "fb";

			function plgCommunityKunena(& $subject, $config)
		{
					//from Kunena v1.6.3, table prefix has been changed
					$db = JFactory::getDBO();
					$sql = 'show tables like ' . $db->Quote('%_kunena_users');

					$db->setQuery($sql);
					$userTable = $db->loadObject();

					if($userTable){
							$this->db_prefix = "kunena";
					}

					parent::__construct($subject, $config);
		}

			function onProfileDisplay()
			{
					JPlugin::loadLanguage( 'plg_community_kunena', JPATH_ADMINISTRATOR );

					$files	= JPATH_ROOT .'/components/com_kunena/class.kunena.php';

					// for the newest kunena version
					$files2  = JPATH_ROOT .'/components/com_kunena/kunena.php';

					if(JFile::exists( $files ) || JFile::exists( $files2 ))
					{

						$config	= CFactory::getConfig();

						if( !$config->get('enablegroups') )
						{
								return JText::_('PLG_KUNENA_GROUP_DISABLED');
						}


						$uri	= JURI::base();

							$document	= JFactory::getDocument();
							// Attach CSS
							//$css		= JURI::base() . 'plugins/community/kunena/style.css';
							//$document->addStyleSheet($css);
							$css	= 'plugins/community/kunena/kunena/';
							CAssets::attach( 'style.css' , 'css' , $css );
							//CFactory::load('helpers', 'time');

							$groupsModel		= CFactory::getModel( 'groups' );
							$avatarModel		= CFactory::getModel( 'avatar' );

							$user		= CFactory::getRequestUser();
							$userName	= $user->getDisplayName();

							$groups		= $groupsModel->getGroups( $user->id );

							$my	= JFactory::getUser();

							$username = $this->params->get('username');
							$password = $this->params->get('password');

							$db = JFactory::getDBO();
							// Get forum user info:
                                                        
							$sql = 'SELECT a.*, b.* FROM '.$db->quoteName('#__'.$this->db_prefix.'_users').' as a '
							 . 'LEFT JOIN '.$db->quoteName('#__users').' AS b on b.'.$db->quoteName('id').'=a.'.$db->quoteName('userid')
							 . 'WHERE a.'.$db->quoteName('userid').'='.$db->Quote($user->id);

							$db->setQuery($sql);

							$userinfo = $db->loadObject();

							if( $userinfo )
							{
								$usr_info = 1;
								//print_r($userinfo);
								$maxPost = intval($userinfo->posts);

								// Get latest forum topics
								// Search only within allowed group
								$query = 'SELECT b.' . $db->quoteName( 'group_id' ) . ' as gid'
										 . ' FROM ' . $db->quoteName('#__users') . ' as a, '
										 . $db->quoteName('#__user_usergroup_map') . ' as b'
										 . ' WHERE a.' . $db->quoteName('id') . '= b.' . $db->quoteName('user_id')
										 . ' AND a.' . $db->quoteName( 'id' ) . '=' .$db->Quote($my->id);

								$db->setQuery($query);
								$db->query();

								$dse_groupid = $db->loadObjectList();

								if (count($dse_groupid))
								{
									$group_id = $dse_groupid[0]->gid;
								}
								else
								{
									$group_id = 0;
								}

								$maxCount = $this->params->get('count', 5);
								$query = 'SELECT a.* , b.'.$db->quoteName('id').' as category, b.'.$db->quoteName('name').' as catname, c.'.$db->quoteName('hits').' AS threadhits'
									  .' FROM '.$db->quoteName('#__'.$this->db_prefix.'_messages').' AS a, '.$db->quoteName('#__'.$this->db_prefix.'_categories').' AS b, '.$db->quoteName('#__'.$this->db_prefix.'_messages').' AS c, '.$db->quoteName('#__'.$this->db_prefix.'_messages_text').' AS d'
									  .' WHERE a.'.$db->quoteName('catid').'     = b.'.$db->quoteName('id')
									  .' AND a.'.$db->quoteName('thread').'      = c.'.$db->quoteName('id')
									  .' AND a.'.$db->quoteName('id').'          = d.'.$db->quoteName('mesid')
									  .' AND a.'.$db->quoteName('hold').'        = '.$db->Quote('0')
									  .' AND b.'.$db->quoteName('published').'   = '.$db->Quote('1')
									  .' AND a.'.$db->quoteName('userid').'      ='.$db->Quote($user->id)
									  .' AND (b.'.$db->quoteName('pub_access').' <='.$db->Quote($group_id).')'
									  .' ORDER BY '.$db->quoteName('time').' DESC'
									  .' LIMIT 0, '.$maxCount;

								$db->setQuery($query);

								$items = $db->loadObjectList();
							}
							else
							{
								$usr_info = 0;
								$userId   = "";
								$userName = "";
								$items    = "";
							}

							$fbItemid = '&amp;Itemid='.$this->getItemid();

							$mainframe = JFactory::getApplication();
							$caching = $this->params->get('cache', 1);

							if($caching)
							{
									$caching = $mainframe->getCfg('caching');
							}

							$cache = JFactory::getCache('plgCommunityKunena');
							$cache->setCaching($caching);

							$callback = array('plgCommunityKunena', '_getKunenaHTML');
							$content = $cache->call($callback, $usr_info, $user->id, $userName, $items, $fbItemid);
					}
					else
					{
							//$content = "<div class=\"icon-nopost\"><img src='".JURI::base()."components/com_community/assets/error.gif' alt=\"\" /></div>";
							//$content .= "<div class=\"content-nopost\" style=\"height:100%;\">".JText::_('PLG_KUNENA_NOT_INSTALLED')."</div>";

							$content = "<div>" .JText::_('PLG_KUNENA_NOT_INSTALLED') . "</div>";
					}

					return $content;
			}

			static public function _getKunenaHTML($usr_info, $userId, $userName, $items, $fbItemid){
					ob_start();

					if($usr_info){
							if( !empty($items) ) {
									?>
									<div id="applications-kunena">
									<?php
									foreach ($items as $item ){
											$fbURL 		= JRoute::_("index.php?option=com_kunena&amp;func=view".$fbItemid."&amp;catid=" . $item->catid . "&amp;id=" . $item->id . "#" . $item->id);
											$fbCatURL 	= JRoute::_("index.php?option=com_kunena".$fbItemid."&amp;func=showcat&amp;catid=" . $item->catid);
											$postDate	= new JDate($item->time);
										?>
												<div class="apps-item">
													<b>
														<a href="<?php echo $fbURL;?>" class="apps-headline"><?php echo stripslashes ($item->subject); ?></a>
													</b>
													in
													<a href="<?php echo $fbCatURL; ?>"><?php echo $item->catname; ?></a>
													on
													<?php echo CTimeHelper::timeLapse($postDate,false); ?>
												</div>
											<?php
											}
									?>
									</div>
									<?php
							} else {
									?>
									<div class="icon-nopost">
										<img src="<?php echo JURI::base(); ?>plugins/community/kunena/kunena/no-post.gif" alt="" />
									</div>
							<div class="content-nopost">
								<?php echo $userName . ' ' . JText::_('PLG_KUNENA_NO_DISCUSSION_JOIN'); ?>
							</div>
									<?php
							}
					}else{
							?>
					<div class="icon-nopost">
						<img src="<?php echo JURI::base(); ?>plugins/community/kunena/kunena/no-post.gif" alt="" />
					</div>
					<div class="content-nopost">
						<?php echo JText::_('PLG_KUNENA_NO_FORUM_POST'); ?>
					</div>
							<?php
					 }

					$contents	= ob_get_contents();
					ob_end_clean();
					return $contents;
			}

			/**
			 * Return itemid for Kunena
			 */
			static public function getItemid(){
					$db = JFactory::getDBO();
					$Itemid = 0;
					if (!defined("FB_FB_ITEMID")) {
					if ($Itemid < 1) {
							$db->setQuery('SELECT '.$db->quoteName('id')
													.' FROM '.$db->quoteName('#__menu')
													.' WHERE '.$db->quoteName('link').' = '.$db->Quote('index.php?option=com_kunena')
													.' AND '.$db->quoteName('published').' = '.$db->Quote('1'));
							$Itemid = $db->loadResult();

							if ($Itemid < 1) {
							   $Itemid = 0;
							}
					}
				} else {
					$Itemid = FB_FB_ITEMID;
					}

				return $Itemid;
			}

			/**
			 * Return path to topic emoticons
			 * Sadly, for now, we will only return default, emoticons
			 */
			static public function getTopicImoticon(&$item) {
				$version = 1.5;
				if(class_exists('Kunena'))
				{
					$version = Kunena::version();
				}

				$emoticonPath = '';
				if( !defined('JB_URLEMOTIONSPATH' )) {
						if($version <'1.6.0'){
							$emoticonPath = JURI::base() . 'components/com_kunena/template/default/images/english/emoticons/';
						} else {
							$emoticonPath = JURI::base() . 'components/com_kunena/template/default/images/icons/';
						}
				} else {
						$emoticonPath = JB_URLEMOTIONSPATH;
				}

				 // Emotions
				$topic_emoticons = array ();
				if($version <'1.6.0'){

					$topic_emoticons[0] = $emoticonPath . 'default.gif';
					$topic_emoticons[1] = $emoticonPath . 'exclam.gif';
					$topic_emoticons[2] = $emoticonPath . 'question.gif';
					$topic_emoticons[3] = $emoticonPath . 'arrow.gif';
					$topic_emoticons[4] = $emoticonPath . 'love.gif';
					$topic_emoticons[5] = $emoticonPath . 'grin.gif';
					$topic_emoticons[6] = $emoticonPath . 'shock.gif';
					$topic_emoticons[7] = $emoticonPath . 'smile.gif';

				}else{
					$topic_emoticons[0] = $emoticonPath . 'topic-default.png';
					$topic_emoticons[1] = $emoticonPath . 'topic-exclam.png';
					$topic_emoticons[2] = $emoticonPath . 'topic-question.png';
					$topic_emoticons[3] = $emoticonPath . 'topic-arrow.png';
					$topic_emoticons[4] = $emoticonPath . 'topic-love.png';
					$topic_emoticons[5] = $emoticonPath . 'topic-grin.png';
					$topic_emoticons[6] = $emoticonPath . 'topic-shock.png';
					$topic_emoticons[7] = $emoticonPath . 'topic-smile.png';
				}
				return $topic_emoticons[$item->topic_emoticon];
			}

			public function onCommunityStreamRender($act)
			{
				$user	= CFactory::getUser($act->actor);
				$config	= CFactory::getConfig();

				// Load params
				$param = new JRegistry($act->params);
				$action = $param->get('action');
				$actors = $param->get('actors');
				$this->set('actors', $actors);

				// Handle 'single' view exclusively
				$act->title = preg_replace('/\{multiple\}(.*)\{\/multiple\}/i', '', $act->title);
				$search = array('{single}', '{/single}');
				$act->title = CString::str_ireplace($search, '', $act->title);
				$actorLink = '<a class="cStream-Author" href="' .CUrlHelper::userLink($user->id).'">'.$user->getDisplayName().'</a>';
				$title = CString::str_ireplace('{actor}', $actorLink, $act->title);

				$stream = new stdClass();
				$stream->actor = $user;
				$stream->target = null;
				$stream->headline = $title;
				$stream->message = $act->content;
				$stream->group = "";
				$stream->attachments = array();

				$attachment = new stdClass();
				$attachment->type = 'quote';
				$attachment->message = '';
				$stream->attachments[] = $attachment;

				return $stream;
			}
	}
}