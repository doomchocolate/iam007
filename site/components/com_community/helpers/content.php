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

class CContentHelper
{
	/**
	 * Inject data from paramter to content tags ({}) .
	 *
	 * @param	$content	Original content with content tags.
	 * @param	$params	Text contain all values need to be replaced.
	 * @param	$html	Auto detect url tag and insert inline.
	 * @return	$text	The content after replacing.
	 **/
	static public function injectTags( $content,$paramsTxt,$html=false )
	{
		$params = new CParameter( $paramsTxt );
		preg_match_all("/{(.*?)}/", $content, $matches, PREG_SET_ORDER);
		if(!empty( $matches ))
		{
			foreach ($matches as $val)
			{
				$replaceWith = JString::trim($params->get($val[1], null));
				if( !is_null( $replaceWith ) )
				{
					//if the replacement start with 'index.php', we can CRoute it
					if( JString::strpos($replaceWith, 'index.php') === 0){
						$replaceWith = CRoute::getExternalURL($replaceWith);
					}
					if($html) {
						$replaceUrl = $params->get($val[1].'_url', null);
						if( !is_null( $replaceUrl ) )
						{
							if($val[1] == 'stream') {
								$replaceUrl .= '#activity-stream-container';
							}

							//if the replacement start with 'index.php', we can CRoute it
							if( JString::strpos($replaceUrl, 'index.php') === 0){
								$replaceUrl = CRoute::getExternalURL($replaceUrl);
							}
							$replaceWith = '<a href="'.$replaceUrl.'">'.$replaceWith.'</a>';
						}
					}
					$content	= CString::str_ireplace($val[0], $replaceWith, $content);
				}
			}
		}
		return $content;
	}
}