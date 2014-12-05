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

require_once( JPATH_ROOT .'/components/com_community/libraries/core.php' );

class CFilterBar
{
	static public function getHTML( $url , $sortItems = array() , $defaultSort = '' , $filterItems = array() , $defaultFilter = '' )
	{

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$cleanURL	= $url;
		$uri		= JFactory::getURI();
		$queries	= $_REQUEST;

		$allow = array('option','view','browse', 'task');

		foreach($queries as $key=>$value)
		{
			if(!in_array($key,$allow))
			{
				unset($queries[$key]);
			}
		}

		$selectedSort	= $jinput->get->get('sort', $defaultSort, 'STRING');
		$selectedFilter = $jinput->get->get('filter', $defaultFilter, 'STRING');
		$tmpl		= new CTemplate();
		$tmpl->set( 'queries'			, $queries );
		$tmpl->set( 'selectedSort' 		,  $selectedSort );
		$tmpl->set( 'selectedFilter' 	, $selectedFilter );
		$tmpl->set( 'sortItems' 		, $sortItems );
		$tmpl->set( 'uri'				, $uri );
		$tmpl->set( 'filterItems'		, $filterItems );
		$tmpl->set( 'jinput'			, $jinput );

		return $tmpl->fetch( 'filterbar.html' );
	}
}