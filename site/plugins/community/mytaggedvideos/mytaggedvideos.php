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

if(!class_exists('plgCommunityMyTaggedVideos'))
{
	class plgCommunityMyTaggedVideos extends CApplications
	{
		var $name		= 'MyTaggedVideos';
		var $_name		= 'myTaggedVideos';
		var $_user		= null;
	
	    function plgCommunityMyTaggedVideos(& $subject, $config)
	    {
                        parent::__construct($subject, $config);
                        $this->db = JFactory::getDbo();
			$this->_my		= CFactory::getUser();						
	    }
	
		/**
		 * Ajax function to save a new wall entry
		 * 	 
		 * @param message	A message that is submitted by the user
		 * @param uniqueId	The unique id for this group
		 * 
		 **/	 	 	 	 	 		
		function onProfileDisplay()
		{	
			JPlugin::loadLanguage( 'plg_community_mytaggedvideos', JPATH_ADMINISTRATOR );
			$mainframe = JFactory::getApplication();
		
			// Attach CSS
			$document	= JFactory::getDocument();
			// $css		= JURI::base() . 'plugins/community/myvideos/style.css';
			// $document->addStyleSheet($css);
			$user     = CFactory::getRequestUser();
			$userid	= $user->id;
			$this->loadUserParams();
			
			$def_limit = $this->params->get('count', 10);
			$limit = JRequest::getVar('limit', $def_limit, 'REQUEST');
			$limitstart = JRequest::getVar('limitstart', 0, 'REQUEST');	
			$row = $this->getVideos($userid);
			$total = count($row);		
			
			$caching = $this->params->get('cache', 1);		
			if($caching)
			{
				$caching = $mainframe->getCfg('caching');
			}
			
			$cache = JFactory::getCache('plgCommunityMyTaggedVideos');
			$cache->setCaching($caching);
			$callback = array('plgCommunityMyTaggedVideos', '_getLatestVideosHTML');		
			$content = $cache->call($callback, $userid, $this->userparams->get('count', 5 ), $limitstart, $row, $total);
			
			return $content;
		}
		
		static public function _getLatestVideosHTML($userid, $limit, $limitstart, $row, $total)
		{
			//
			//CFactory::load( 'models' , 'videos' );
			$video = JTable::getInstance( 'Video' , 'CTable' );
							
			ob_start();				
			if(!empty($row))
			{
				?>
				<div id="application-photo">
					<ul class="cThumbsList cResetList clearfix">
				<?php
				$i = 1;
				foreach($row as $data)
				{
					if($i > $limit){
						break;
					}
					$i++;
					$video->load( $data->id );
					$link = plgCommunityMyTaggedVideos::buildLink($data->id);
					$thumbnail	= $video->getThumbnail();
					?>					
						<li>
							<a href="<?php echo $link; ?>" class="cVideo-Thumb">
								<img class="cAvatar Video cMediaAvatar jomNameTips" title="<?php echo CTemplate::escape($video->getTitle());?>" src="<?php echo $thumbnail; ?>"/>
								<b><?php echo $video->getDurationInHMS()?></b>
							</a>
						</li>
					<?php
				}			
				?>
					</ul>
					<div class="app-box-footer">
						<a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=myvideos&userid='.$userid); ?>">
							<span><?php echo JText::_('PLG_MYTAGGEDVIDEOS_VIEWALL_VIDEOS');?></span>
							<span>(<?php echo $total;?>)</span>
						</a>
					</div>
				</div>
				<?php
			}
			else
			{
				?>
				<div><?php echo JText::_('PLG_MYTAGGEDVIDEOS_NO_VIDEOS')?></div>
				<?php
			}	
			?>
			<div style='clear:both;'></div>
			<?php
			$contents  = ob_get_contents();
			@ob_end_clean();
			$html = $contents;
			
			return $html;
		}
		
		static public function getVideos($userid)
		{
			//get videos from the user
			//CFactory::load('models', 'videos');
			$model	= CFactory::getModel( 'VideoTagging' );
			$videos = $model->getTaggedVideosByUser($userid);
			
			return $videos;
		}
		
		static public function buildLink($videoId)
		{
			$video	= JTable::getInstance( 'Video' , 'CTable' );
			$video->load( $videoId );

			return $video->getURL();
		}
	
	}	
}
