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
require_once (COMMUNITY_COM_PATH.'/libraries/fields/profilefield.php');
class CFieldsUrl extends CProfileField
{
	/**
	 * Method to format the specified value for text type
	 **/
	public function getFieldData( $field )
	{
		$value = $field['value'];

		if( empty( $value ) )
			return $value;

		return '<a rel="nofollow" href="' . $value . '" target="_blank">' . $value . '</a>';
	}

	public function getFieldHTML( $field , $required )
	{
		// If maximum is not set, we define it to a default
		$field->max	= empty( $field->max ) ? 200 : $field->max;

		$class	= ($field->required == 1) ? ' required' : '';
		$class	.= !empty( $field->tips ) ? ' jomNameTips tipRight' : '';
		$scheme	= '';
		$host	= '';
		$style 				= $this->getStyle()?' style="' .$this->getStyle() . '" ':'';
		if(! empty($field->value))
		{
			//value passed could be something like http://,www.example.com due to processing done at com_community/views/register/view.html.php .
			//Let's correct the format bfore passing to parse_url()
			$field->value = implode('', explode(',', $field->value));
			if (strlen(str_replace(array('http://', 'https://'), '', $field->value)) != 0)
			{
				$url	= parse_url($field->value);
			}
			$scheme	= isset( $url[ 'scheme' ] ) ? $url['scheme'] : 'http://';
			$host	= isset( $url[ 'host' ] ) ? $url['host'] : '';
			$path	= isset( $url[ 'path'] ) ? $url['path'] : '';
			$query	= isset( $url[ 'query'] ) ? '?' . $url['query'] : '';
			$fragment = isset( $url['fragment'] ) ? '#' . $url['fragment'] : '' ;
			$field->value	= $host . $path . $query . $fragment;
		}

		ob_start();
?>
	<select name="field<?php echo $field->id;?>[]" class="input-small">
		<option value="http://"<?php echo ($scheme == 'http') ? ' selected="selected"' : '';?>><?php echo JText::_('http://');?></option>
		<option value="https://"<?php echo ($scheme == 'https') ? ' selected="selected"' : '';?>><?php echo JText::_('https://');?></option>
	</select>
	<input title="<?php echo CStringHelper::escape( $field->tips );?>" type="text" value="<?php echo $field->value;?>" id="field<?php echo $field->id;?>" name="field<?php echo $field->id;?>[]" maxlength="<?php echo $field->max;?>"  class="validate-profile-url<?php echo $class;?>"<?php echo $style; ?> />
	<span id="errfield<?php echo $field->id;?>msg" style="display:none;">&nbsp;</span>
<?php
		$html	= ob_get_contents();
		ob_end_clean();

		return $html;
	}

	public function isValid( $value , $required )
	{
		//CFactory::load( 'helpers' , 'validate' );

		$isValid	= CValidateHelper::url( $value );

		$url		= parse_url( $value );
		$host		= isset($url['host']) ? $url['host'] : '';		
		/* this field is required OR user entered something */
		if ( $required || $value != '' ) {
			/* it must be valid AND NOT empty host AND validLength*/
			return ($isValid) && (!empty($host)) && $this->validLength($value); 
		}		
		/* no required and user no entered anything than of course VALID */
		return true;
	}

	public function formatdata( $value )
	{
		if( empty( $value[0] ) || empty( $value[1] ) )
		{
			$value = '';
		}
		else
		{

			$scheme	= $value[ 0 ];
			$url	= $value[ 1 ];
			$value	= $scheme . $url;
		}
		return $value;
	}
}