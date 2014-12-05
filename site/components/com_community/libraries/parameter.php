<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

defined('_JEXEC') or die('Restricted access');
jimport('joomla.registry.registry');

class CParameter extends JRegistry
{
	/**
	 * [$_xml description]
	 * @var [type]
	 */
	protected $_xml = null;

	/**
	 * __construct description
	 * @param [object] $data    [description]
	 * @param [string] $xmlPath [description]
	 */
	public function __construct($data = NULL, $xmlPath = NULL)
	{
		parent::__construct($data);

		if(!is_null($xmlPath))
		{
			$this->_xml = new SimpleXMLElement($xmlPath,NULL,true);
		}
	}

	/**
	* @param string name
	* @param string group [currently not used, being put there to imitate JParameter render()]
	* @return string html
	*/
	public function render()
	{
		$html	= array();
		$html[]	= '<table width="100%" class="cFormTable" cellspacing="0" cellspacing="0">';
		$params	= $this->_xml->params;
		$data	= $this->data;

		foreach($params as $param)
		{
			foreach($param as $_param)
			{
				//var_dump($_param);
				$html[] = '<tr>';

				if($_param['type'] == 'spacer')
				{
					$html[] = '<td class="key"><span class="editlinktip"> </span></td>';
					$html[] = '<td class="field" valign="top">'.JText::_($_param['default']).'</td>';
				}
				else
				{
					$html[] = '<td class="key"><span class="editlinktip hasTip" title="'.(empty($_param['description']) ? '' : JText::_($_param['description'] )).'">'. JText::_($_param['label']) .'</span></td>';
					$html[] = '<td class="field" valign="top">'. $this->_generateHTML($_param,$data) .'</td>';
				}


				$thml[] = '</tr>';
			}
		}
		//$html[] = '</table>';

		return implode("\n", $html);

	}


	/**
	* new render table function, for rendering new table structure
	* @param string name
	* @param string group [currently not used, being put there to imitate JParameter render()]
	* @return string html
	*/
	public function render_table()
	{
		$html	= array();
		$html[]	= '<table width="100%">';
		$params	= $this->_xml->params;
		$data	= $this->data;

		foreach($params as $param)
		{
			foreach($param as $_param)
			{
				//var_dump($_param);
				$html[] = '<tr>';

				if($_param['type'] == 'spacer')
				{
					$html[] = '<td width="200"><span></span></td>';
					$html[] = '<td class="field">'.JText::_($_param['default']).'</td>';
				}
				else
				{
					$html[] = '<td width="200"><span class="js-tooltip" title="'.(empty($_param['description']) ? '' : JText::_($_param['description'] )).'">'. JText::_($_param['label']) .'</span></td>';
					$html[] = '<td class="field">'. $this->_generateHTML($_param,$data) .'</td>';
				}


				$thml[] = '</tr>';
			}
		}
		//$html[] = '</table>';

		return implode("\n", $html);

	}


	/**
	 * [bind description]
	 * @param  [type] $data  [description]
	 * @param  string $group [description]
	 * @return [type]        [description]
	 */
	public function bind($data, $group = '_default')
	{
		if (is_array($data))
		{
			return $this->loadArray($data, $group);

		} elseif (is_object($data))
		{
			return $this->loadObject($data, $group);

		} else
		{
			// Return JSON
			return $this->loadString($data);
		}

	}
	/**
	 * [generateHTML description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	private function _generateHTML($data,$value)
	{
		if(!is_object($data))
		{
			return false;
		}

		$html = array();

		// Setup empty value if tehre is none
		// update leave it, if value 0
		if(empty($value->$data['name'])){
			$value->$data['name'] = '';
        }

		switch ($data['type'])
		{
			case 'list':
				$html[] = '<select title="'.(empty($data['description']) ? '' : JText::_($data['description'] )).'" id="params'.$data['name'].'" name="params['.$data['name'].']">';
				$html[] = $this->_getOption($data,$value->$data['name']);
				$html[] = '</select>';
				break;
			case 'radio':
				$html[] = $this->_getRadio($data,$value->$data['name']);
				break;
			case 'twitter':
				$html[] = CTwitter::getOAuthRequest();
				break;
			case 'text':
				$Tvalue = (count((array)$value) == 0 ) ? '' : $value->$data['name'];
				$html[] = '<input title="'.(empty($data['description']) ? '' : JText::_($data['description'] )).'" id=params'.$data['name'].' class="text_area" type="text" value="'.$Tvalue.'" name=params['.$data['name'].']>';
				break;
			case 'textarea':
				$html[] = '<textarea title="'.(empty($data['description']) ? '' : JText::_($data['description'] )).'" id="params'.$data['name'].' class="fullwidth" rows="" cols="" name="'.$data['name'].'">'.JText::_($value->$data['name']).'</textarea>';
				break;
		}

		return implode("\n",$html);
	}

	/**
	 * [getOption description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	private function _getOption($data,$value)
	{
		$html = array();
		foreach($data->children() as $_data)
		{
			$selected = ($_data['value'] == $value) ? ' selected="selected"' : '';
			$html[] = '<option value='.$_data['value'].$selected.'>'.JText::_($_data['name']).'</option>';
		}

		return implode("\n", $html);
	}
	private function _getRadio($data,$value)
	{
		$html = array();
		$name = $data['name'];
		if($value == '')
		{
			$value=0;
		}
		foreach($data->children() as $_data)
		{
			$selected = (isset($value) && $_data['value'] == $value) ? ' checked="checked"' : '';
			$html[] = '<label><input title="'.(empty($data['description']) ? '' : JText::_($data['description'] )).'" id="params'.$name.$_data['value'].'" type="radio" name="params['.$name.']" value='.$_data['value'].$selected.' /><span class="lbl"> '.JText::_($_data['name']).'</span></label>';
			// $html[] = '<label for="params'.$name.$_data['value'].'">'.JText::_($_data['name']).'</label>';
		}

		return implode("\n", $html);
	}
}
