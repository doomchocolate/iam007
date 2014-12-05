<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

class CFormElement
{
	public $label = '';
	public $html = '';
	public $position = '';


	/**
	 * Renders the provided elements into their respective HTML formats.
	 *
	 * @param	Array	formElements	An array of CFormElement objects.
	 * @param	string	position		The position of the field 'before' will be loaded before the rest of the form and 'after' will be loaded after the rest of the form loaded.
	 *
	 * returns	string	html			Contents of generated CFormElements.
	 **/
	static public function renderElements( $formElements , $position )
	{
		$tmpl	= new CTemplate();
		$tmpl->set( 'formElements'	, $formElements );
		$tmpl->set( 'position'		, $position );
		$html	= $tmpl->fetch( 'form.elements' );
		return trim($html);
	}
}
