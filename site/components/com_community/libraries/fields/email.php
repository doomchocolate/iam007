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
class CFieldsEmail extends CProfileField
{
	/**
	 * Method to format the specified value for text type
	 **/
	public function getFieldData( $field )
	{
		$value = $field['value'];

		if( empty( $value ) )
			return $value;

		return CLinkGeneratorHelper::getEmailURL($value);
	}

	public function getFieldHTML( $field , $required )
	{
		// If maximum is not set, we define it to a default
		$field->max	= empty( $field->max ) ? 200 : $field->max;

		//get the value in param
		$params	= new CParameter($field->params);
		$style 				= $this->getStyle()?' style="' .$this->getStyle() . '" ':'';

		$class	= ($field->required == 1) ? ' required' : '';
		$class	.= $params->get('min_char') != '' && $params->get('max_char') != '' ? ' minmax_'.$params->get('min_char').'_'.$params->get('max_char') : '';
		$class	.= !empty( $field->tips ) ? ' jomNameTips tipRight' : '';
		ob_start();
?>
	<input class="validate-profile-email<?php echo $class;?>" title="<?php echo CStringHelper::escape( JText::_( $field->tips ) );?>" type="text" value="<?php echo $field->value;?>" id="field<?php echo $field->id;?>" name="field<?php echo $field->id;?>" maxlength="<?php echo $field->max;?>" size="40" <?php echo $style;?>	 />
	<span id="errfield<?php echo $field->id;?>msg" style="display:none;">&nbsp;</span>
<?php
		$html	= ob_get_contents();
		ob_end_clean();

		return $html;
	}

	public function isValid( $value , $required )
	{

		$isValid	= CValidateHelper::email( $value );

		if( !empty($value) && !$isValid )
		{
			return false;
		}
		//validate string length
		if(!$this->validLength($value)){
			return false;
		}
		//validate allowed domain
		if(isset($this->params)){
			$allowed = $this->params->get('allowed');
			if($allowed){
				$delimiter = ';';
				$allowed_list = explode($delimiter,$allowed);
				$valid = false;
				if(count($allowed_list) > 0 ){
					foreach($allowed_list as $domain){
						if(CValidateHelper::domain( $value, $domain))
						{
							$valid = true;
						}
					}
				}
				if(!$valid){
					return false;
				}
			}
		}
		//validate backlist domain
		if(isset($this->params)){
			$blacklist = $this->params->get('blacklist');
			if($blacklist){
				$delimiter = ';';
				$blacklists = explode($delimiter,$blacklist);
				if(count($blacklists) > 0 ){
					foreach($blacklists as $domain){
						if(CValidateHelper::domain( $value, $domain))
						{
							return false;
						}
					}
				}
			}
		}
		return true;
	}
}