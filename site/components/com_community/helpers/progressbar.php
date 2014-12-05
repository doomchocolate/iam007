<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

class CProgressbarHelper{

	/**
	* Generate the HTML for a progress bar
	*
	* @param int max progress value
	* @param int current progress value
	* @param string [optional] style class for custom styling on the bar container
	* @param string [optional] style class for custom styling for the bar filling
	* @return string HTML
	*/
	static public function getHTML( $max=100, $currval=0, $barclass='outerpgbar', $barfillclass='innerpgbar'){

		if(!is_numeric($max) || !is_numeric($currval) || $max < 0 || $currval < 0)
		return '<div>Progress bar cannot be generated properly</div>';

		$width = intval(($currval/$max) * 100) ;
		$barclass = (!empty($barclass))
					? 'class="'.$barclass.'"'
					: 'style="background-color:white; height:.8em; border :1px solid #D0D0D0; margin-bottom:10px; padding:1px; "' ;

		$barfillclass = (!empty($barfillclass))
					? 'class="'.$barfillclass.'" style="width:'.$width.'%;"'
					: 'style="width:'.$width.'%; height: 100%; background-color:green; position:relative;"' ;

		//class progressbarfill : inside of the progress bar
		$html = '<div '.$barclass.' >'.
					'<div '.$barfillclass.'></div>'.
				'</div>';

		return $html;
	}
}
