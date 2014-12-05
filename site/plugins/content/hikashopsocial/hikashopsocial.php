<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
jimport( 'joomla.plugin.plugin' );

class plgContentHikashopsocial extends JPlugin
{
	var $meta=array();
	function plgContentHikashopsocial( &$subject, $params )
	{
		parent::__construct( $subject, $params );
		if ( (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ||
				 (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') ) {
			$this->https = 's';
		} else {
			$this->https = '';
		}
	}

	function onAfterRender(){
		$app = Jfactory::getApplication();
		if(!$app->isAdmin() && (JRequest::getVar('option')=='com_hikashop' || JRequest::getVar('option')=='') && (JRequest::getVar('ctrl')=='product' || JRequest::getVar('ctrl')=='category') && (JRequest::getVar('task')=='show' || JRequest::getVar('task')=='listing')){
			$body = JResponse::getBody();
			if(strpos($body,'{hikashop_social}')){
				$pluginsClass = hikashop_get('class.plugins');
				$plugin = $pluginsClass->getByName('content','hikashopsocial');
				if(!isset($plugin->params['position'])){
					$plugin->params['position'] = 0;
					$plugin->params['display_twitter'] = 1;
					$plugin->params['display_fb'] = 1;
					$plugin->params['display_google'] = 1;
					$plugin->params['fb_style'] = 0;
					$plugin->params['fb_faces'] = 1;
					$plugin->params['fb_verb'] = 0;
					$plugin->params['fb_theme'] = 0;
					$plugin->params['fb_font'] = 0;
					$plugin->params['fb_type'] = 0;
					$plugin->params['twitter_count'] = 0;
					$plugin->params['google_size']=2;
					$plugin->params['google_count']=1;
				}
				if(!isset($plugin->params['fb_send'])){ $plugin->params['fb_send']=0; }
				if(!isset($plugin->params['fb_tag'])){ $plugin->params['fb_tag']="iframe"; }
				if($plugin->params['position']==0) $html='<div id="hikashop_social" style="text-align:left;">';
				else if($plugin->params['position']==1 && $plugin->params['width']!=0) $html='<div id="hikashop_social" style="text-align:right; width:'.$plugin->params['width'].'px">';
				else{ $html='<div id="hikashop_social" style="text-align:right; width:100%">'; }

				if($plugin->params['display_twitter']) $html.=$this->_addTwitterButton( $plugin);
				if(@$plugin->params['display_pinterest']) $html.=$this->_addPinterestButton( $plugin);
				if(@$plugin->params['display_google']) $html.=$this->_addGoogleButton( $plugin);
				if(@$plugin->params['display_addThis']) $html.=$this->_addAddThisButton( $plugin);
				if($plugin->params['display_fb']) $html.=$this->_addFacebookButton( $plugin);

				$html.='</div>';
				$body = str_replace('{hikashop_social}',$html,$body);
				if(@$plugin->params['display_google']){
					$mainLang = JFactory::getLanguage();
					$tag = $mainLang->get('tag');
					if(!in_array($tag,array('zh-CN','zh-TW','en-GB','en-US','pt-BR','pt-PT'))) $tag=strtolower(substr($tag,0,2));
					$lang = '{"lang": "'.$tag.'"}';
					$body=str_replace('</head>', '<script type="text/javascript" src="https://apis.google.com/js/plusone.js">'.$lang.'</script></head>', $body);
				}
				if($plugin->params['display_fb']){
					$body=str_replace('<html ', '<html xmlns:fb="https://www.facebook.com/2008/fbml" xmlns:og="http://ogp.me/ns# " xmlns:fb="http://ogp.me/ns/fb#"	', $body);
					if($plugin->params['fb_tag']=="xfbml"){
						$mainLang = JFactory::getLanguage();
						$tag = str_replace('-','_',$mainLang->get('tag'));
						$fb='<div id="fb-root"></div>
								<script>(function(d, s, id) {
									var js, fjs = d.getElementsByTagName(s)[0];
									if (d.getElementById(id)) return;
									js = d.createElement(s); js.id = id;
									js.src = "//connect.facebook.net/'.$tag.'/all.js#xfbml=1";
									fjs.parentNode.insertBefore(js, fjs);
									}(document, \'script\', \'facebook-jssdk\'));
								</script>';
						$body = preg_replace('#<body.*>#Us','$0'.$fb,$body);
					}
				}
				if(@$plugin->params['display_pinterest']){
					$body=str_replace('</head>', '<script type="text/javascript" src="http'.$this->https.'://assets.pinterest.com/js/pinit.js"></script></head>', $body);
				}
				if(@$plugin->params['display_addThis']){
					$var=array(); $vars='';
					if(!empty($plugin->params['services_exclude'])){ $var[]='services_exclude: "'.$plugin->params['services_exclude'].'"';}
					if(!empty($var)){
						$vars='<script type="text/javascript">var addthis_config =	{ '.implode(';',$var).' }</script>';
					}
					$body=str_replace('</head>', '<script type="text/javascript" src="http'.$this->https.'://s7.addthis.com/js/250/addthis_widget.js"></script>'.$vars.'</head>', $body);
				}

				if(!empty($this->meta)){
					foreach($this->meta as $k => $v){
						if(!strpos($body,$k)){
							$body=str_replace('</head>', $v.'</head>', $body);
						}
					}
				}
				JResponse::setBody($body);
			}
		}
	}

	function _addAddThisButton(&$plugin){
		$atClass=''; $class=''; $divClass=''; $endDiv='';
		if($plugin->params['addThis_display']==0){ $atClass='addthis_button_compact';	}
		if($plugin->params['addThis_display']==1){ $atClass='addthis_button_compact'; $divClass='<div class="addthis_default_style addthis_toolbox addthis_32x32_style">'; $endDiv='</div>';}
		if($plugin->params['addThis_display']==2){ $atClass='addthis_counter';}

		if($plugin->params['position']==0){ $class='hikashop_social_addThis'; }
		else{ $class='hikashop_social_addThis_right'; }

		$html='<span class="'.$class.'" >'.$divClass.'<a class="'.$atClass.'"></a>'.$endDiv.'</span>';
		return $html;
	}

	function _addGoogleButton(&$plugin){
		if($plugin->params['google_count']==1){ $count='count="true"'; }
		else{ $count='count="false"'; }

		$div='<span>';
		if($plugin->params['position']==0){
			if($plugin->params['google_size']==0){ $size='size="standard"'; $div="<span class='hikashop_social_google'>";	}
			if($plugin->params['google_size']==1){ $size='size="small"'; $div="<span class='hikashop_social_google'>";}
			if($plugin->params['google_size']==2){ $size='size="medium"'; $div="<span class='hikashop_social_google'>";}
			if($plugin->params['google_size']==3){ $size='size="tall"'; $div="<span class='hikashop_social_google'>";}
		}else{
			if($plugin->params['google_size']==0){ $size='size="standard"'; $div="<span class='hikashop_social_google_right'>";	}
			if($plugin->params['google_size']==1){ $size='size="small"'; $div="<span class='hikashop_social_google_right'>";}
			if($plugin->params['google_size']==2){ $size='size="medium"'; $div="<span class='hikashop_social_google_right'>";}
			if($plugin->params['google_size']==3){ $size='size="tall"'; $div="<span class='hikashop_social_google_right'>";}
		}
		$html=$div.'<g:plusone '.$size.' '.$count.'></g:plusone></span>';
		return $html;
	}

	function _addPinterestButton(&$plugin){
		$product = $this->_getProductInfo();
		$imageUrl = $this->_getImageURL($product->product_id);
		if($plugin->params['position']==0){
			if($plugin->params['pinterest_display']==0){ $count='horizontal'; $div="<span class='hikashop_social_pinterest'>";}
			if($plugin->params['pinterest_display']==1){ $count='vertical'; $div="<span class='hikashop_social_pinterest'>";}
			if($plugin->params['pinterest_display']==2){ $count='none'; $div="<span class='hikashop_social_pinterest'>";}
		}else{
			if($plugin->params['pinterest_display']==0){ $count='horizontal'; $div="<span class='hikashop_social_pinterest_right'>";}
			if($plugin->params['pinterest_display']==1){ $count='vertical'; $div="<span class='hikashop_social_pinterest_right'>";}
			if($plugin->params['pinterest_display']==2){ $count='none'; $div="<span class='hikashop_social_pinterest_right'>";}
		}
		if(isset($product->product_canonical) && !empty($product->product_canonical)){
			$url = hikashop_cleanURL($product->product_canonical);
		}else{
			$url=hikashop_currentURL('',false);
		}
		$html=$div.'<a href="http'.$this->https.'://pinterest.com/pin/create/button/?url='.urlencode($url).'&media='.urlencode($imageUrl).'&description='.htmlspecialchars(strip_tags($product->product_description), ENT_COMPAT,'UTF-8').'" class="pin-it-button" count-layout="'.$count.'"><img border="0" src="http://assets.pinterest.com/images/PinExt.png" title="Pin It" /></a></span>';
		return $html;
	}

	function _addTwitterButton(&$plugin){
		$product = $this->_getProductInfo();
		if($plugin->params['position']==0){
			if($plugin->params['twitter_count']==0){ $count='horizontal'; $div="<span class='hikashop_social_tw_horizontal'>"; }
			if($plugin->params['twitter_count']==1){ $count='vertical'; $div="<span class='hikashop_social_tw'>"; }
			if($plugin->params['twitter_count']==2){ $count='none'; $div="<span class='hikashop_social_tw'>"; }
		}else{
			if($plugin->params['twitter_count']==0){ $count='horizontal'; $div="<span class='hikashop_social_tw_horizontal_right'>"; }
			if($plugin->params['twitter_count']==1){ $count='vertical'; $div="<span class='hikashop_social_tw_right'>"; }
			if($plugin->params['twitter_count']==2){ $count='none'; $div="<span class='hikashop_social_tw_right'>"; }
		}

		$message='';
		if(!empty($plugin->params['twitter_text'])){
			$message='data-text="'.$plugin->params['twitter_text'].'"';
		}

		$mention='';
		if(!empty($plugin->params['twitter_mention'])){
			$mention='data-via="'.$plugin->params['twitter_mention'].'"';
		}

		$mainLang = JFactory::getLanguage();
		$locale=strtolower(substr($mainLang->get('tag'),0,2));

		if($locale=='en') $lang='';
		else if($locale=='fr') $lang='data-lang="fr"';
		else if($locale=='de') $lang='data-lang="de"';
		else if($locale=='es') $lang='data-lang="es"';
		else if($locale=='it') $lang='data-lang="it"';
		else if($locale=='ja') $lang='data-lang="ja"';
		else if($locale=='ru') $lang='data-lang="ru"';
		else if($locale=='tr') $lang='data-lang="tr"';
		else if($locale=='ko') $lang='data-lang="ko"';
		else $lang='';

		if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) {
			$this->meta['hikashop_twitter_js_code']='<script type="text/javascript">
				function twitterPop(str) {
					mywindow = window.open(\'http://twitter.com/share?url=\'+str,"Tweet_widow","channelmode=no,directories=no,location=no,menubar=no,scrollbars=no,toolbar=no,status=no,width=500,height=375,left=300,top=200");
					mywindow.focus();
				}
				</script>';
	 		$html=$div;
			if(isset($product->product_canonical) && !empty($product->product_canonical)){
				$url = hikashop_cleanURL($product->product_canonical);
			}else{
				$url=hikashop_currentURL('',false);
			}
			$html.='<a href="javascript:twitterPop(\''.$url.'\')"><img src="'.HIKASHOP_IMAGES.'icons/tweet_button.jpg"></a></span>';
			return $html;
		}

		if(!isset($div)) $div='<span>';
		$html=$div;
		$html.='<a href="http'.$this->https.'://twitter.com/share" class="twitter-share-button" '.$message.' data-count="'.$count.'" '.$mention.' '.$lang.'>Tweet</a>
				<script type="text/javascript" src="http'.$this->https.'://platform.twitter.com/widgets.js"></script></span>';
		return $html;
	}

	function _addFacebookButton( &$plugin){
		$product=$this->_getProductInfo();

		$options='';
		$xfbml_options= '';
		if($plugin->params['fb_style']==0){ $options='layout=standard&amp;'; $options.='width=400&amp;';}
		if($plugin->params['fb_style']==1){ $options='layout=button_count&amp;'; $options.='width=115&amp;'; $xfbml_options.='data-layout="button_count" ';}
		if($plugin->params['fb_style']==2){ $options='layout=box_count&amp;'; $options.='width=115&amp;'; $xfbml_options.='data-layout="box_count" ';}

		if($plugin->params['fb_faces']==0){ $options.='show_faces=false&amp;'; $xfbml_options.='data-show-faces="false" ';}
		else{ $options.='show_faces=true&amp;'; $xfbml_options.='data-show-faces="false" '; }
		if($plugin->params['fb_verb']==0){ $options.='action=like&amp;'; }
		else{ $options.='action=recommend&amp;'; $xfbml_options.='data-action="recommend" ';}
		if($plugin->params['fb_theme']==0){ $options.='colorscheme=light&amp;'; }
		else{ $options.='colorscheme=dark&amp;'; $xfbml_options.='data-colorscheme="dark" ';}
		if($plugin->params['fb_font']==0){ $options.='font=arial&amp;'; $xfbml_options.='data-font="arial" '; }
		if($plugin->params['fb_font']==1){ $options.='font=lucida%20grande&amp;'; $xfbml_options.='data-font="lucida%20grande" '; }
		if($plugin->params['fb_font']==2){ $options.='font=segoe%20ui&amp;'; $xfbml_options.='data-font="segoe%20ui" ';}
		if($plugin->params['fb_font']==3){ $options.='font=tahoma&amp;'; $xfbml_options.='data-font="tahoma" '; }
		if($plugin->params['fb_font']==4){ $options.='font=trebuchet%2Bms&amp;'; $xfbml_options.='data-font="trebuchet%20ms" '; }
		if($plugin->params['fb_font']==5){ $options.='font=verdana&amp;'; $xfbml_options.='data-font="verdana" '; }

		if($plugin->params['fb_send']==1){ $xfbml_options.='data-send="true"" ';}

		if(isset($product->product_canonical) && !empty($product->product_canonical)){
			$url = hikashop_cleanURL($product->product_canonical);
		}else{
			$url=hikashop_currentURL('',false);
		}

		if($plugin->params['position']==0){
			if($plugin->params['fb_style']==0){	$sizes='class="hikashop_social_fb_standard"';	$div='<span class="hikashop_social_fb" >';}
			if($plugin->params['fb_style']==1){	$sizes='class="hikashop_social_fb_button_count"'; $div='<span class="hikashop_social_fb" >';}
			if($plugin->params['fb_style']==2){	$sizes='class="hikashop_social_fb_box_count"';	$div='<span class="hikashop_social_fb" >';}
		}else{
			if($plugin->params['fb_style']==0){	$sizes='class="hikashop_social_fb_standard"';	$div='<span class="hikashop_social_fb_right" >';}
			if($plugin->params['fb_style']==1){	$sizes='class="hikashop_social_fb_button_count"'; $div='<span class="hikashop_social_fb_right" >';}
			if($plugin->params['fb_style']==2){	$sizes='class="hikashop_social_fb_box_count"';	$div='<span class="hikashop_social_fb_right" >';}
		}

		if(isset($div)){ $html=$div; }
		else{ $html='';}
		if($plugin->params['fb_tag']=="iframe"){
			$html.='<iframe
						src="http'.$this->https.'://www.facebook.com/plugins/like.php?href='.urlencode($url).'&amp;send=false&amp;'.$options.'height=30"
						scrolling="no"
						frameborder="0"
						style="border:none; overflow:hidden;" '.$sizes.'
						allowTransparency="true">
					</iframe>';
		}else{
			$html.='<div class="fb-like" data-href="'.$url.'" '.$xfbml_options.'></div>';
		}
		if(isset($div)) $html.='</span>';



		$this->meta['property="og:title"']='<meta property="og:title" content="'.htmlspecialchars($product->product_name, ENT_COMPAT,'UTF-8').'"/> ';

		if($plugin->params['fb_type']==0){ $this->meta['property="og:type"']='<meta property="og:type" content="product"/> '; }
		if($plugin->params['fb_type']==1){ $this->meta['property="og:type"']='<meta property="og:type" content="album"/> '; }
		if($plugin->params['fb_type']==2){ $this->meta['property="og:type"']='<meta property="og:type" content="book"/> '; }
		if($plugin->params['fb_type']==3){ $this->meta['property="og:type"']='<meta property="og:type" content="company"/> '; }
		if($plugin->params['fb_type']==4){ $this->meta['property="og:type"']='<meta property="og:type" content="drink"/> '; }
		if($plugin->params['fb_type']==5){ $this->meta['property="og:type"']='<meta property="og:type" content="game"/> '; }
		if($plugin->params['fb_type']==6){ $this->meta['property="og:type"']='<meta property="og:type" content="movie"/> '; }
		if($plugin->params['fb_type']==7){ $this->meta['property="og:type"']='<meta property="og:type" content="song"/> '; }

		$config =& hikashop_config();
		$uploadFolder = ltrim(JPath::clean(html_entity_decode($config->get('uploadfolder','media/com_hikashop/upload/'))),DS);
		$uploadFolder = rtrim($uploadFolder,DS).DS;
		$this->uploadFolder_url = str_replace(DS,'/',$uploadFolder);
		$this->uploadFolder = JPATH_ROOT.DS.$uploadFolder;
		$this->thumbnail = $config->get('thumbnail',1);
		$this->thumbnail_y = $config->get('product_image_y',$config->get('thumbnail_y'));
		$this->thumbnail_x = $config->get('product_image_x',$config->get('thumbnail_x'));
		$this->main_thumbnail_x=$this->thumbnail_x;
		$this->main_thumbnail_y=$this->thumbnail_y;
		$this->main_uploadFolder_url = $this->uploadFolder_url;
		$this->main_uploadFolder = $this->uploadFolder;

		$imageUrl = $this->_getImageURL($product->product_id);
		if(!empty($imageUrl)){
			$this->meta['property="og:image"']='<meta property="og:image" content="'.$imageUrl.'" /> ';
		}

		$this->meta['property="og:url"']='<meta property="og:url" content="'.$url.'" />';
		$conf = JFactory::getConfig();
		if(HIKASHOP_J30){
			$siteName=$conf->get('sitename');
		}else{
			$siteName=$conf->getValue('config.sitename');
		}
		$this->meta['property="og:description"']='<meta property="og:description" content="'.htmlspecialchars(strip_tags($product->product_description), ENT_COMPAT,'UTF-8').'"/> ';
		$this->meta['property="og:site_name"']='<meta property="og:site_name" content="'.htmlspecialchars($siteName, ENT_COMPAT,'UTF-8').'"/> ';
		if(!empty($plugin->params['admin'])){
			$this->meta['property="fb:admins"']='<meta property="fb:admins" content="'.htmlspecialchars($plugin->params['admin'], ENT_COMPAT,'UTF-8').'" />';
		}

		return $html;
	}

	function _getProductInfo(){
		static $product = null;
		if(empty($product)){
			$app = Jfactory::getApplication();
			$product_id = (int)hikashop_getCID('product_id');
			$menus	= $app->getMenu();
			$menu	= $menus->getActive();
			if(empty($menu)){
				if(!empty($Itemid)){
					$menus->setActive($Itemid);
					$menu	= $menus->getItem($Itemid);
				}
			}
			if(empty($product_id)){
				if (is_object( $menu )) {
					jimport('joomla.html.parameter');
					$category_params = new JParameter( $menu->params );
					$product_id = $category_params->get('product_id');
				}
			}
			if(!empty($product_id)){
				$productClass = hikashop_get('class.product');
				$product = $productClass->get($product_id);
				if($product->product_type=='variant'){
					$product = $productClass->get($product->product_parent_id);
				}
			}
		}
		return $product;
	}

	function _getImageURL($product_id){
		$config =& hikashop_config();
		$uploadFolder = ltrim(JPath::clean(html_entity_decode($config->get('uploadfolder','media/com_hikashop/upload/'))),DS);
		$uploadFolder = rtrim($uploadFolder,DS).DS;
		$this->uploadFolder_url = str_replace(DS,'/',$uploadFolder);
		$this->main_uploadFolder_url = $this->uploadFolder_url;
		$db = JFactory::getDBO();
		$queryImage = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id='.$product_id.'  AND file_type=\'product\' ORDER BY file_ordering ASC, file_id ASC';
		$db->setQuery($queryImage);
		$image = $db->loadObject();
		$imageUrl = '';
		if(empty($image)){
			$queryImage = 'SELECT * FROM '.hikashop_table('file').' as a LEFT JOIN '.hikashop_table('product').' as b ON a.file_ref_id=b.product_id  WHERE product_parent_id='.$product_id.'  AND file_type=\'product\' ORDER BY file_ordering ASC, file_id ASC';
			$db->setQuery($queryImage);
			$image = $db->loadObject();
		}
		if(!empty($image)){
				$imageUrl=JURI::base().$this->main_uploadFolder_url.$image->file_path;
		}
		return $imageUrl;
	}
}
