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

class plgCommunityEvents extends CApplications
{
	var $name 		= "Events";
	var $_name		= 'events';
	
	function plgCommunityEvents(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}
	
	function onProfileDisplay()
	{
		JPlugin::loadLanguage( 'plg_community_events', JPATH_ADMINISTRATOR );

		$config	= CFactory::getConfig();
				
		if( !$config->get('enableevents') )
		{
			return JText::_('PLG_EVENTS_EVENT_DISABLED');	
		}
		
		$document	= JFactory::getDocument();
		// $document->addStyleSheet( JURI::root() . 'plugins/community/events/style.css' );

		$mainframe	= JFactory::getApplication();
		$user		= CFactory::getRequestUser();
		$caching 	= $this->params->get('cache', 1);
		$model		= CFactory::getModel( 'Events' );
		$my			= CFactory::getUser();
		$this->loadUserParams();

		//CFactory::load( 'helpers' , 'event' );
		$event		= JTable::getInstance( 'Event' , 'CTable' );
		$handler	= CEventHelper::getHandler( $event );
		
		$events		= $model->getEvents( null , $user->id , $this->params->get( 'sorting' , 'latest' ) , null , true , false , null , null ,$handler->getContentTypes() , $handler->getContentId() , $this->userparams->get('count', 5 ) );
		
		if($caching)
		{
			$caching = $mainframe->getCfg('caching');
		}
		
		$creatable	= false;
		
		if( $my-> id == $user->id )
		{
			$creatable	= true;
		}
		
		$cache		= JFactory::getCache('plgCommunityEvents');
		$cache->setCaching($caching);
		$callback	= array( $this , '_getEventsHTML');		
		$content	= $cache->call($callback, true , $events , $user , $config , $model->getEventsCount( $user->id ) , $creatable );
		return $content; 
	}
	
	function _getEventsHTML( $createEvents , $rows , $user , $config , $totalEvents , $creatable )
	{
		//CFactory::load( 'helpers' , 'string' );
		
		ob_start();
		?>
		<div class="jsProfileEvents">
		<?php
		if( $rows )
		{
		?>
		<ul class="cThumbDetails cResetList">
		<?php
		foreach( $rows as $row ) {
			$event		    = JTable::getInstance( 'Event', 'CTable' );
			$event->load( $row->id );
			
			$creator	    =   CFactory::getUser($event->creator);
			
			// Get the formated date & time
			$format		    =   ($config->get('eventshowampm')) ?  JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_12H') : JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_24H');
			$startdatehtml   =	CTimeHelper::getFormattedTime($event->startdate, $format);
			$enddatehtml	    =	CTimeHelper::getFormattedTime($event->enddate, $format);
		?>

			<li class="jomNameTips" title="<?php echo CStringHelper::escape( $event->summary );?>">
				<b class="cThumb-Calendar cFloat-L">
					<b><?php echo CEventHelper::formatStartDate($event, JText::_('M') ); ?></b>
					<b><?php echo CEventHelper::formatStartDate($event, JText::_('d') ); ?></b>
				</b>
				<div class="cThumb-Detail">
					<a class="cThumb-Title" href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id );?>"><?php echo $event->title;?></a>
					<div class="cThumb-Location"><?php echo $event->location;?></div>
					<div class="cThumb-Members small">
						<a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewguest&eventid=' . $event->id . '&type='.COMMUNITY_EVENT_STATUS_ATTEND);?>"><?php echo JText::sprintf((!CStringHelper::isSingular($event->confirmedcount)) ? 'COM_COMMUNITY_EVENTS_ATTANDEE_COUNT_MANY':'COM_COMMUNITY_EVENTS_ATTANDEE_COUNT', $event->confirmedcount);?></a>
					</div>
				</div>
			</li>
			<?php } ?>
		</ul>
		<?php
		}
		else
		{
		?>
			<div><?php echo JText::_('PLG_EVENTS_NO_EVENTS_CREATED_BY_THE_USER_YET');?></div>
		<?php
		}
		?>
						<div class="app-box-footer">
								<a class="app-box-action" href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=create' );?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_CREATE');?></a>
								<a class="app-box-action" href="<?php echo CRoute::_('index.php?option=com_community&view=events');?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_ALL_EVENTS').' ('.$totalEvents.')';?></a>
						</div>	
		</div>
		<?php
		$content	= ob_get_contents();
		ob_end_clean();
		
		return $content;
	}
}