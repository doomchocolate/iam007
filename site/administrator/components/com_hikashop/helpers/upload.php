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
class hikashopUploadHelper {

	protected $options;
	protected $imagesExt = array('jpg', 'jpeg', 'gif', 'png');

	public function __construct() {
		$this->setOptions();
	}

	public function setOptions($options = null) {
		$this->options = array(
			'upload_dir' => HIKASHOP_MEDIA.'upload'.DS,
			'upload_url' => JURI::base(true).'/media/'.HIKASHOP_COMPONENT.'/upload/',
			'param_name' => 'files',
			'delete_type' => 'DELETE',
			'max_file_size' => null,
			'min_file_size' => 1,
			'accept_file_types' => '/.+$/i',
			'max_number_of_files' => null,
			'max_width' => null,
			'max_height' => null,
			'min_width' => 1,
			'min_height' => 1,
			'discard_aborted_uploads' => true,
			'orient_image' => false,
			'image_versions' => array()
		);

		if(!empty($options)) {
			foreach($options as $k => $v) {
				if(!is_array($v) || empty($this->options[$k])) {
					$this->options[$k] = $v;
				} else {
					foreach($v as $kV => $vV) {
						$this->options[$k][$kV] = $vV;
					}
				}
			}
		}
	}

	public function process($options = null) {
		if(!empty($options)) {
			$this->setOptions($options);
		}

		header('Pragma: no-cache');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Content-Disposition: inline; filename="files.json"');
		header('X-Content-Type-Options: nosniff');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST');
		header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');

		switch($_SERVER['REQUEST_METHOD']) {
			case 'OPTIONS':
				break;
			case 'HEAD':
			case 'GET':
				return $this->get();
				break;
			case 'POST':
				return $this->post($options);
			default:
				header('HTTP/1.1 405 Method Not Allowed');
		}
		return false;
	}

	public function processFallback($options = null) {
		JRequest::checkToken() || die('Invalid Token');

		if(!empty($options)) {
			$this->setOptions($options);
		}
		$upload = isset($_FILES[$this->options['param_name']]) ? $_FILES[$this->options['param_name']] : reset($_FILES);
		$info = array();
		if($upload && is_array($upload['tmp_name'])) {
			foreach ($upload['tmp_name'] as $index => $value) {
				$info[] = $this->handle_file_upload(
					$upload['tmp_name'][$index],
					isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index],
					isset($_SERVER['HTTP_X_FILE_SIZE']) ? $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index],
					isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index],
					$upload['error'][$index],
					$options,
					$index
				);
			}
		} else if($upload || isset($_SERVER['HTTP_X_FILE_NAME'])) {
			$info[] = $this->handle_file_upload(
				isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
				isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : (isset($upload['name']) ? $upload['name'] : null),
				isset($_SERVER['HTTP_X_FILE_SIZE']) ? $_SERVER['HTTP_X_FILE_SIZE'] : (isset($upload['size']) ? $upload['size'] : null),
				isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : (isset($upload['type']) ? $upload['type'] : null),
				isset($upload['error']) ? $upload['error'] : null,
				$options
			);
		}
		return $info;
	}

	protected function get_file_object($file_name) {
		$file_path = $this->options['upload_dir'].$file_name;
		if (is_file($file_path) && $file_name[0] !== '.') {
			$file = new stdClass();
			$file->name = $file_name;
			$file->path = $file_path;
			$file->size = filesize($file_path);
			$file->url = $this->options['upload_url'].rawurlencode($file->name);

			return $file;
		}
		return null;
	}

	protected function get_file_objects() {
		if(!is_dir($this->options['upload_dir']))
			return array();
		return array_values( array_filter( array_map( array($this, 'get_file_object'), scandir($this->options['upload_dir']) ) ) );
	}

	protected function validate($uploaded_file, $file, $error, $index) {
		if($error) {
			$file->error = $error;
			return false;
		}
		if(!$file->name) {
			$file->error = 'missingFileName';
			return false;
		}
		if(!preg_match($this->options['accept_file_types'], $file->name)) {
			$file->error = 'acceptFileTypes';
			return false;
		}
		if($uploaded_file && is_uploaded_file($uploaded_file)) {
			$file_size = filesize($uploaded_file);
		} else {
			$file_size = $_SERVER['CONTENT_LENGTH'];
		}
		if($this->options['max_file_size'] && ( $file_size > $this->options['max_file_size'] || $file->size > $this->options['max_file_size']) ) {
			$file->error = 'maxFileSize';
			return false;
		}
		if($this->options['min_file_size'] && $file_size < $this->options['min_file_size']) {
			$file->error = 'minFileSize';
			return false;
		}
		if(is_int($this->options['max_number_of_files']) && ( count($this->get_file_objects()) >= $this->options['max_number_of_files']) ) {
			$file->error = 'maxNumberOfFiles';
			return false;
		}

		list($img_width, $img_height) = @getimagesize($uploaded_file);
		if(is_int($img_width)) {
			if($this->options['max_width'] && $img_width > $this->options['max_width'] || $this->options['max_height'] && $img_height > $this->options['max_height']) {
				$file->error = 'maxResolution';
				return false;
			}
			if($this->options['min_width'] && $img_width < $this->options['min_width'] || $this->options['min_height'] && $img_height < $this->options['min_height']) {
				$file->error = 'minResolution';
				return false;
			}
		}
		return true;
	}

	protected function upcount_name_callback($matches) {
		$index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
		$ext = isset($matches[2]) ? $matches[2] : '';
		return ' ('.$index.')'.$ext;
	}

	protected function upcount_name($name) {
		return preg_replace_callback(
			'/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
			array($this, 'upcount_name_callback'),
			$name,
			1
		);
	}

	protected function trim_file_name($name, $type, $index) {
		$file_name = trim(basename(stripslashes($name)), ".\x00..\x20");
		if(strpos($file_name, '.') === false && preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
			$file_name .= '.'.$matches[1];
		}
		if($this->options['discard_aborted_uploads']) {
			while(is_file($this->options['upload_dir'].$file_name)) {
				$file_name = $this->upcount_name($file_name);
			}
		}
		return $file_name;
	}

	protected function orient_image($file_path) {
		$exif = @exif_read_data($file_path);
		if($exif === false)
			return false;

		$orientation = intval(@$exif['Orientation']);
		if(!in_array($orientation, array(3, 6, 8)))
			return false;

		$image = @imagecreatefromjpeg($file_path);
		switch ($orientation) {
			case 3:
				$image = @imagerotate($image, 180, 0);
				break;
			case 6:
				$image = @imagerotate($image, 270, 0);
				break;
			case 8:
				$image = @imagerotate($image, 90, 0);
				break;
			default:
				return false;
		}
		$success = imagejpeg($image, $file_path);
		@imagedestroy($image);
		return $success;
	}

	protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $options, $index = null) {
		$file = new stdClass();
		$file->name = $this->trim_file_name($name, $type, $index);
		$file->size = intval($size);
		$file->type = $type;

		if(empty($this->options['sub_folder']))
			$this->options['sub_folder'] = '';

		if($this->validate($uploaded_file, $file, $error, $index)) {
			$shopConfig = hikashop_config();
			if($options['type'] == 'file') {
				$allowed = $shopConfig->get('allowedfiles');
			} else {
				$allowed = $shopConfig->get('allowedimages');
			}

			$file_path = strtolower(JFile::makeSafe($name));
			if(!preg_match('#\.('.str_replace(array(',','.'), array('|','\.'), $allowed).')$#Ui', $file_path,$extension) || preg_match('#\.(php.?|.?htm.?|pl|py|jsp|asp|sh|cgi)$#Ui', $file_path)) {
				$file->error = JText::sprintf('ACCEPTED_TYPE', substr($file_path,strrpos($file_path, '.') + 1), $allowed);
				return $file;
			}

			$file_path = str_replace(array('.',' '), '_', substr($file_path, 0, strpos($file_path,$extension[0]))) . $extension[0];

			if(JFile::exists($this->options['upload_dir'] . $this->options['sub_folder'] . $file_path)) {
				$pos = strrpos($file_path,'.');
				$file_path = substr($file_path,0,$pos).'_'.rand().'.'.substr($file_path,$pos+1);
			}
			if(!JFile::upload($uploaded_file, $this->options['upload_dir'] . $this->options['sub_folder'] . $file_path)) {
				if(!move_uploaded_file($uploaded_file, $this->options['upload_dir'] . $this->options['sub_folder'] . $file_path)) {
					$file->error = JText::sprintf('FAIL_UPLOAD',$uploaded_file,$this->options['upload_dir'] . $this->options['sub_folder'] . $file_path);
					return $file;
				}
			}
			$file_size = filesize($this->options['upload_dir'] . $this->options['sub_folder'] . $file_path);
			$file->name = $file_path;
			$file->path = $this->options['upload_dir'] . $this->options['sub_folder'] . $file_path;
			$file->url = $this->options['upload_url'].$this->options['sub_folder'].rawurlencode($file->name);
			if(strpos($file->name, '.') !== false) {
				$ext = strtolower(substr($file->name, strrpos($file->name, '.') + 1));
				if(!in_array($ext, $this->imagesExt)) {
					if ($this->options['orient_image']) {
						$this->orient_image($this->options['upload_dir'] .$this->options['sub_folder']. $file_path);
					}
				}
			}
		}
		return $file;
	}

	private function get() {
		$file_name = isset($_REQUEST['file']) ? basename(stripslashes($_REQUEST['file'])) : null;
		if($file_name) {
			$info = $this->get_file_object($file_name);
		} else {
			$info = $this->get_file_objects();
		}
		header('Content-type: application/json');
		return $info;
	}

	private function post($options) {
		$upload = isset($_FILES[$this->options['param_name']]) ? $_FILES[$this->options['param_name']] : reset($_FILES);
		$info = array();
		if($upload && is_array($upload['tmp_name'])) {
			foreach ($upload['tmp_name'] as $index => $value) {
				$info[] = $this->handle_file_upload(
					$upload['tmp_name'][$index],
					isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index],
					isset($_SERVER['HTTP_X_FILE_SIZE']) ? $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index],
					isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index],
					$upload['error'][$index],
					$options,
					$index
				);
			}
		} else if($upload || isset($_SERVER['HTTP_X_FILE_NAME'])) {
			$info[] = $this->handle_file_upload(
				isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
				isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : (isset($upload['name']) ? $upload['name'] : null),
				isset($_SERVER['HTTP_X_FILE_SIZE']) ? $_SERVER['HTTP_X_FILE_SIZE'] : (isset($upload['size']) ? $upload['size'] : null),
				isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : (isset($upload['type']) ? $upload['type'] : null),
				isset($upload['error']) ? $upload['error'] : null,
				$options
			);
		}
		header('Vary: Accept');
		$redirect = isset($_REQUEST['redirect']) ?
		stripslashes($_REQUEST['redirect']) : null;
		if($redirect) {
			$json = json_encode($info);
			header('Location: '.sprintf($redirect, rawurlencode($json)));
			return;
		}
		if(isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
			header('Content-type: application/json');
		} else {
			header('Content-type: text/plain');
		}
		return $info;
	}
}
