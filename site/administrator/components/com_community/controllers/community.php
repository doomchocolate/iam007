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

jimport( 'joomla.application.component.controller' );

/**
 * JomSocial Component Controller
 */
class CommunityControllerCommunity extends CommunityController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function display( $cachable = false, $urlparams = array() )
	{
		$viewName	= JRequest::getCmd( 'view' , 'community' );

		// Set the default layout and view name
		$layout		= JRequest::getCmd( 'layout' , 'default' );

		// Get the document object
		$document	= JFactory::getDocument();

		// Get the view type
		$viewType	= $document->getType();

		// Get the view
		$view		= $this->getView( $viewName , $viewType );
		// Get Model
		$model		= $this->getModel( $viewName );

		if( $model )
		{
			$view->setModel( $model , $viewName );

			//Set Users Model
			$Users	= $this->getModel( 'Users' );
			$view->setModel( $Users  , false );

			//Set MailQueue
			$Mail	= $this->getModel( 'Mailqueue' );
			$view->setModel( $Mail  , false );

			//Set Activities
			$act	= $this->getModel( 'Activities' );
			$view->setModel( $act  , false );

			//Set Groups
			$groups	= $this->getModel( 'Groups' ,'CommunityAdminModel');
			$view->setModel( $groups  , false );

			//Set Events
			$events	= $this->getModel( 'Events' );
			$view->setModel( $events  , false );

			//Set Photos
			$photos	= $this->getModel( 'Photos', 'CommunityAdminModel');
			$view->setModel( $photos  , false );

			//Set Videos
			$videos	= $this->getModel( 'Videos','CommunityAdminModel' );
			$view->setModel( $videos  , false );
		}

		// Set the layout
		$view->setLayout( $layout );

		// Display the view
		$view->display();
	}

	public function getRssFeed($url,$id)
	{
		$response	= new JAXResponse();

		$version = new JVersion();
		$joomla_ver = $version->getHelpVersion();
		$rss = array();

		if($joomla_ver <='0.30')
		{
				$rssData = $this->getRSS($url,$id);

				foreach($rssData->items as $item)
				{
					$data = new stdClass();
					preg_match_all('#(<[/]?img.*>)#U',  $item->get_content(),$matches);

					$imgSrc = '';
					if(isset($matches[0][0]))
					{
						$imgSrc = explode('src="', $matches[0][0]);
						$imgSrc = explode('" ',$imgSrc[1]);

						$imgSrc = $imgSrc[0];
					}

					$data->title = $item->get_title();
					$data->url = $item->get_link();
					$data->img = $imgSrc;
					$data->published = strtolower($item->get_date('l , d F Y'));
					$data->content = strip_tags(JFilterOutput::stripImages($item->get_description()));
					$rss[] = $data;
				}
		}
		else
		{
			try
			{
				$feed = new JFeedFactory;
				$rssDoc = $feed->getFeed($url);
			}
			catch (InvalidArgumentException $e)
			{
				return JText::_('MOD_FEED_ERR_FEED_NOT_RETRIEVED');
			}
			catch (RunTimeException $e)
			{
				return JText::_('MOD_FEED_ERR_FEED_NOT_RETRIEVED');
			}
			catch (LogicException $e)
			{
				return JText::_('MOD_FEED_ERR_FEED_NOT_RETRIEVED');
			}

			if (empty($rssDoc))
			{
				return JText::_('MOD_FEED_ERR_FEED_NOT_RETRIEVED');
			}

			if ($rssDoc)
			{

				for($i = 0; $i < 5; $i++)
				{
					$date = $rssDoc[$i]->publishedDate;
					$data = new stdClass();
					preg_match_all('#(<[/]?img.*>)#U',  $rssDoc[$i]->content,$matches);

					$imgSrc = '';
					if(isset($matches[0][0]))
					{
						$imgSrc = explode('src="', $matches[0][0]);
						$imgSrc = explode('" ',$imgSrc[1]);

						$imgSrc = $imgSrc[0];
					}

					$data->title = $rssDoc[$i]->title;
					$data->url = $rssDoc[$i]->uri;
					$data->img = $imgSrc;
					$data->published = strtolower($date->format('l , d F Y'));
					$data->content = strip_tags(JFilterOutput::stripImages($rssDoc[$i]->content));

					$rss[] = $data;
				}
			}
		}

		$html = '';

		foreach($rss as $data)
		{
			//var_dump($data->img);
			$html .= '<div class="media clearfix">';
			$html .= '<div class="media-body">';
			$html .= '<h4 class="media-heading reset-gap"><a href="'.$data->url.'" target="_blank">'.$data->title.'</a></h4>';
			$html .= '<p class="orange">'.$data->published.'</p>';

			if($data->img) {
				$html .= '<a class="pull-left thumbnail" href="'.$data->url.'" target="_blank">';
				$html .= '<img class="media-object" src="'.$data->img.'" width="100px"  />';
				$html .= '</a>';
			}

			$html .= JHTML::_('string.truncate',$data->content,200);
			$html .= '</div>';
			$html .= '</div>';
		}

		$response->addScriptCall('joms.jQuery("#'.$id.'").html',$html);
		return $response->sendResponse();
	}

	public function getEngagementGraph($type, $time)
	{
		$response = new JAXResponse();

		$communityView = $this->getView('community' , 'html');

		$js = $communityView->getEngagementJs($type, $time);

		$response->addScriptCall($js);

		return $response->sendResponse();
	}

	public function getStatisticGraph($time)
	{
		$response = new JAXResponse();

		$groupModel = $this->getModel('Groups','CommunityAdminModel');
		$eventModel = $this->getModel('Events');
		$photoModel = $this->getModel('Photos','CommunityAdminModel');

		$communityView = $this->getView('community' , 'html');
		$communityView->setModel($groupModel);
		$communityView->setModel($eventModel);
		$communityView->setModel($photoModel);

		$js = $communityView->getStatisticJs($time);

		$response->addScriptCall($js);

		return $response->sendResponse();
	}

	public function getRSS($url)
	{
		$rssDoc = JFactory::getFeedParser($url);

		$feed = new stdclass();

		if ($rssDoc != false)
		{
			// channel header and link
			$feed->title = $rssDoc->get_title();
			$feed->link = $rssDoc->get_link();
			$feed->description = $rssDoc->get_description();

			// channel image if exists
			$feed->image->url = $rssDoc->get_image_url();
			$feed->image->title = $rssDoc->get_image_title();

			// items
			$items = $rssDoc->get_items();

			// feed elements
			$feed->items = array_slice($items, 0, 5);
		} else {
			$feed = false;
		}

		return $feed;
	}
}