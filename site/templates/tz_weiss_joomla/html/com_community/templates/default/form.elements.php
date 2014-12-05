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

if( $formElements )
{
?>

<?php

	foreach( $formElements as $element )
	{
		if( $element instanceof CFormElement && $element->position == $position )
		{
?>
			<tr>
				<td class="key"><label><?php echo $element->label;?></label></td>
				<td class="value">
					<div style="display: inline-block;"><?php echo $element->html;?></div>
				</td> 
			</tr>
<?php
		}
	}
?>

<?php
}