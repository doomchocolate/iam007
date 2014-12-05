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

/**
 * Class exists checking
 */
if (!class_exists('CCrawlerCurl')) {

    /**
     * Curl class
     * Provide method to get / post with cUrl
     */
    class CCrawlerCurl extends CCrawler {

        /**
         * Curl resource
         * @var resource
         */
        protected $_curl;

        /**
         *
         * @var array 
         */
        protected $_headers = array();

        /**
         *
         * @var object 
         */
        protected $_response = null;

        /**
         * Construct
         */
        public function __construct() {
            /* default */
            $this->_headers[] = "Accept: */*";
            $this->_headers[] = "Accept-Charset: utf-8;ISO-8859-1;iso-8859-2;q=0.7,*;q=0.7";
            $this->_headers[] = "Cache-Control: max-age=0";
            $this->_headers[] = "Connection: keep-alive";
            $this->_headers[] = "Keep-Alive: 300";
            $this->_headers[] = "Accept-Language: en-us,en;q=0.5";
            $this->_headers[] = "Pragma: no-cache"; /* browsers keep this blank. */
            $this->_headers[] = "User-Agent: " . self::COMMUNITY_USER_AGENT;
            $this->init();
        }

        /**
         * Cleanup everything
         */
        public function __destruct() {
            $this->close();
        }

        /**
         * Init class
         * @return boolean
         */
        public function init() {
            if (extension_loaded('curl') && is_callable('curl_init') && is_callable('curl_exec')) {
                $this->_curl = curl_init();
                /**
                 * Init default curl options
                 * @link https://php.net/manual/en/function.curl-setopt.php
                 */
                $this->setCurl(CURLOPT_USERAGENT, self::COMMUNITY_USER_AGENT);
                $this->setCurl(CURLOPT_HTTPHEADER, $this->_headers);
                $this->setCurl(CURLOPT_VERBOSE, false);
                $this->setCurl(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                $this->setCurl(CURLOPT_ENCODING, 'gzip,deflate');
                $this->setCurl(CURLOPT_AUTOREFERER, true);
                $this->setCurl(CURLOPT_FOLLOWLOCATION, true);
                $this->setCurl(CURLOPT_SSL_VERIFYPEER, false);
                $this->setCurl(CURLOPT_RETURNTRANSFER, 1);
                $this->setCurl(CURLOPT_HEADER, true); /* TRUE to include the header in the output. */
                $this->setCurl(CURLOPT_TIMEOUT, 30); /* Time out in 30 seconds */
                return true;
            }
            return false;
        }

        /**
         * Set cURL option
         * @link http://php.net/manual/en/function.curl-setopt.php
         * @param type $option
         * @param type $value
         * @return boolean
         */
        public function setCurl($option, $value) {

            $rtn = curl_setopt($this->_curl, $option, $value);
            if ($option === CURLOPT_FOLLOWLOCATION) {
                if ($value === true) {
                    $this->set('manual_follow', !$rtn);
                } else {
                    $this->set('manual_follow', false);
                }
            }
            return $rtn;
        }

        /**
         * Add header data
         * @param type $header
         */
        public function addHeader($header) {
            $this->_headers[] = $header;
        }

        /**
         * Alias method to quick set header with combined of options
         */

        /**
         * Request to get header only
         * @param boolean $flag
         * @return \CCrawlerCurl
         */
        public function setHeaderOnly($flag) {
            $this->setCurl(CURLOPT_HEADER, true);
            $this->setCurl(CURLOPT_NOBODY, true);
            return $this;
        }

        /**
         * 
         * @param string $remoteFile
         * @param int $range
         * @return \CCrawlerCurl
         */
        public function setFileHeader($remoteFile, $range = 32768) {
            $this->addHeader('Range: bytes=0-' . $range);
            $this->setCurl(CURLOPT_HEADER, false);
            $this->setCurl(CURLOPT_URL, $remoteFile);
            return $this;
        }

        /**
         * Set head to determine request is ajax or not
         * @param boolean $isAjax
         * @return \CCrawlerCurl
         */
        public function setDoAjax($isAjax = true) {
            if ($isAjax) {
                $this->_headers[] = "X-Requested-With: XMLHttpRequest";
            } else {
                foreach ($this->_headers as $key => $val) {
                    if ($val == "X-Requested-With: XMLHttpRequest") {
                        unset($this->_headers[$key]);
                    }
                }
            }
            return $this;
        }

        /**
         * 
         * @param string $username
         * @param string $password
         * @return \CCrawlerCurl
         */
        public function setSsl($username = null, $password = null) {
            $this->setCurl(CURLOPT_SSL_VERIFYPEER, false);
            $this->setCurl(CURLOPT_SSL_VERIFYHOST, false);
            $this->setCurl(CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            /* if username & password provided and setCurl */
            if ($username && $password) {
                $this->setCurl(CURLOPT_USERPWD, "$username:$password");
            }
            return $this;
        }

        /**
         * 
         * @param string $username
         * @param string $password
         * @return \CCrawlerCurl
         */
        public function setBasicAuth($username, $password) {
            $this->setCurl(CURLOPT_HEADER, false);
            $this->setCurl(CURLOPT_USERPWD, "$username:$password");
            return $this;
        }

        /**
         * 
         * @param string $file
         * @return \CCrawlerCurl
         */
        public function setCookieFile($file) {
            if (!file_exists($file)) {
                $handle = fopen($file, 'w+');
                fclose($handle);
            }
            $this->setCurl(CURLOPT_COOKIESESSION, true);
            $this->setCurl(CURLOPT_COOKIEJAR, $file);
            $this->setCurl(CURLOPT_COOKIEFILE, $file);
            $this->set('cookiefile', $file);
            return $this;
        }

        /**
         * 
         * @param type $connect
         * @param type $transfer
         * @return \CCrawlerCurl
         */
        public function setTimeout($connect, $transfer) {
            $this->setCurl(CURLOPT_CONNECTTIMEOUT, $connect);
            $this->setCurl(CURLOPT_TIMEOUT, $transfer);
            return $this;
        }

        /**
         * 
         * @param type $data
         * @return array
         */
        protected function _postFields($data) {
            if (is_array($data)) {
                if (is_array_multidim($data)) {
                    $data = http_build_multi_query($data);
                } else {
                    foreach ($data as $key => $value) {
                        if (is_array($value) && empty($value)) {
                            $data[$key] = '';
                        } elseif (is_string($value) && strpos($value, '@') === 0) {
                            if (class_exists('CURLFile')) {
                                $data[$key] = new CURLFile(substr($value, 1));
                            }
                        }
                    }
                }
            }

            return $data;
        }

        /**
         * Execute request
         * @return \CCrawlerResponse
         */
        public function exec() {
            /* Execute request and store as raw */
            $data['response']['raw'] = curl_exec($this->_curl);
            /* Curl information of request */
            $data['response']['info'] = curl_getinfo($this->_curl);
            $data['error_code'] = curl_errno($this->_curl);
            $data['error_message'] = curl_error($this->_curl);
            /* Declare new response object */
            $this->_response = new CCrawlerResponse($data);
            return $this->_response;
        }

        /**
         * 
         * @param type $type
         * @param type $url
         * @param type $data
         * @return \CCrawlerResponse
         */
        public function crawl($type, $url = null, $data = array()) {
            switch ($type) {
                case 'GET':
                    if (!is_null($url))
                        $this->setCurl(CURLOPT_URL, $this->_buildUrl($url, $data));
                    $this->setCurl(CURLOPT_CUSTOMREQUEST, 'GET');
                    $this->setCurl(CURLOPT_HTTPGET, true);
                    break;
                case 'POST':
                    if (!is_null($url))
                        $this->setCurl(CURLOPT_URL, $this->_buildUrl($url));
                    $this->setCurl(CURLOPT_CUSTOMREQUEST, 'POST');
                    $this->setCurl(CURLOPT_POST, true);
                    $this->setCurl(CURLOPT_POSTFIELDS, $this->_postFields($data));
                    break;
                case 'PUT':
                    if (!is_null($url))
                        $this->setCurl(CURLOPT_URL, $url);
                    $this->setCurl(CURLOPT_CUSTOMREQUEST, 'PUT');
                    $this->setCurl(CURLOPT_POSTFIELDS, http_build_query($data));
                    break;
                case 'PATCH':
                    if (!is_null($url))
                        $this->setCurl(CURLOPT_URL, $this->_buildUrl($url));
                    $this->setCurl(CURLOPT_CUSTOMREQUEST, 'PATCH');
                    $this->setCurl(CURLOPT_POSTFIELDS, $data);
                    break;
                case 'DELETE':
                    if (!is_null($url))
                        $this->setCurl(CURLOPT_URL, $this->_buildUrl($url, $data));
                    $this->setCurl(CURLOPT_CUSTOMREQUEST, 'DELETE');
                    break;
                default:
                    if (!is_null($url))
                        $this->setCurl(CURLOPT_URL, $this->_buildUrl($url, $data));
            }
            return $this->exec();
        }

        /**
         *
         * @return type
         */
        public function getCookies() {
            $contents = file_get_contents($this->get('cookiefile'));
            $cookies = array();
            if ($contents) {
                $lines = explode("\n", $contents);
                if (count($lines)) {
                    foreach ($lines as $key => $val) {
                        $tmp = explode("\t", $val);
                        if (count($tmp) > 3) {
                            $tmp[count($tmp) - 1] = str_replace("\n", "", $tmp[count($tmp) - 1]);
                            $tmp[count($tmp) - 1] = str_replace("\r", "", $tmp[count($tmp) - 1]);
                            $cookies[$tmp[count($tmp) - 2]] = $tmp[count($tmp) - 1];
                        }
                    }
                }
            }
            return $cookies;
        }

        public function reset() {
            curl_reset($this->_curl);
        }

        /**
         * 
         * @return \CCrawlerCurl
         */
        public function close() {
            if (is_resource($this->_curl)) {
                curl_close($this->_curl);
            }
            return $this;
        }

    }

}

/**
 * Class exists checking
 */
if (!class_exists('CCrawlerResponse')) {


    /**
     * Curl response object class
     */
    class CCrawlerResponse extends JObject {

        /**
         * Construct
         * @param type $properties
         */
        public function __construct($properties = null) {
            parent::__construct($properties);
            /* Extract header */
            $this->set('header', $this->_parseResponseHeader(substr($this->response['raw'], 0, $this->response['info']['header_size'])));
            /* Extract body */
            $this->set('body', substr($this->response['raw'], $this->response['info']['header_size']));
        }

        /**
         * Get header value or all array
         * @param string $name
         * @param mixed $default
         * @return mixed
         */
        public function getHeader($name = null, $default = null) {
            if ($name !== null) {
                return (isset($this->header[$name])) ? $this->header[$name] : $default;
            } else {
                return $this->header;
            }
        }

        /**
         * Get body
         * @return string
         */
        public function getBody() {
            return $this->body;
        }

        /**
         * Get cURL information
         * @param string $name
         * @param mixed $default
         * @return mixed
         */
        public function getInfo($name, $default = null) {
            return (isset($this->response['info'][$name])) ? $this->response['info'][$name] : $default;
        }

        /**
         * Parse body to get data
         * @return JRegistry
         */
        public function parse() {
            $contentType = $this->getInfo('content_type');
            if ($contentType) {
                if (strpos($contentType, ';') !== false) {
                    $contentType = explode(';', $contentType);
                    $contentType = trim($contentType[0]);
                }
                switch ($contentType) {
                    case 'text/html':
                        $parser = new CParserMetas();
                        /* extract meta data from body */
                        $data = $parser
                                ->init(array(
                                    'content' => $this->getBody(),
                                    'url' => $this->getInfo('url')))
                                ->extract();

                        /**
                         * Images process
                         * @todo need to improve
                         */
                        $images = $data->get('image');
                        $counter = 0;
                        $limit = $this->get('max_images', 5);
                        if (is_array($images)) {
                            $curl = new CCrawlerCurl();
                            $_images = array();
                            foreach ($images as $key => $imageUrl) {

                                //$imageUrl = strtolower($imageUrl);
                                /* This imageurl already have valid path */
                                if (strpos(strtolower($imageUrl), 'http://') !== false || strpos(strtolower($imageUrl), 'https://')) {
                                    
                                } else {
                                    /* Image have no URL than we need add it */
                                    $host = parse_url($this->getInfo('url'), PHP_URL_HOST);
                                    $url = JUri::getInstance($this->getInfo('url'));
                                    $url = $url->getScheme() . '://' . $host;
                                    $imageUrl = strtolower($url . $imageUrl);
                                }


                                /**
                                 * Read image and make sure image size is valid
                                 */
                                $imageContent = @file_get_contents($imageUrl);

                                if ($imageContent) {

                                    $im = imagecreatefromstring($imageContent);
                                    $width = imagesx($im);
                                    $height = imagesy($im);
                                   
                                    if ($width > 150 && $height > 150 & $counter <= $limit) {
                                        $_images[] = $imageUrl;
                                        $counter ++;
                                    }
                                }
                            }
                        }
                        $data->set('image', $_images);
                        
                        return $data;
                }
            }
        }

        /**
         *
         * @param type $header
         * @return array
         */
        private function _parseResponseHeader($header) {
            $parts = explode("\n", $header);
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part != '') {
                    $tmp = explode(':', $part, 2);
                    if (count($tmp) == 2) {
                        if ($tmp[1] !== '')
                            $headers[$tmp[0]] = trim($tmp[1]);
                    } else {
                        if ($tmp[0] !== '')
                            $headers['Status'] = trim($tmp[0]);
                    }
                }
            }
            if (isset($headers))
                return $headers;
        }

    }

}