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
if (!class_exists('CCurl')) {

    /**
     * Curl class
     * Provide method to get / post with cUrl
     */
    class CCurl {

        /**
         * Curl resource
         * @var resource
         */
        private $_curl;

        /**
         *
         * @var type 
         */
        private $_cookiefile = null;

        /**
         *
         * @var array 
         */
        private $_headers = array();

        /**
         *
         * @var type 
         */
        private $_manual_follow;

        /**
         *
         * @var type 
         */
        private $_redirect_url;

        /**
         *
         * @var type 
         */
        private $_response = null;

        /**
         * Construct
         * @todo cUrl installed checking
         */
        public function __construct() {
            /* default */
            $this->_headers[] = "Accept: */*";
            $this->_headers[] = "Cache-Control: max-age=0";
            $this->_headers[] = "Connection: keep-alive";
            $this->_headers[] = "Keep-Alive: 300";
            $this->_headers[] = "Accept-Charset: utf-8;ISO-8859-1;iso-8859-2;q=0.7,*;q=0.7";
            $this->_headers[] = "Accept-Language: en-us,en;q=0.5";
            $this->_headers[] = "Pragma: "; /* browsers keep this blank. */
            $this->_init();
        }

        /**
         * Cleanup everything
         */
        public function __destruct() {
            $this->close();
        }

        /**
         * Class init
         */
        private function _init() {
            $this->_curl = curl_init();
            $this->setCurl(CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            $this->setCurl(CURLOPT_HTTPHEADER, $this->_headers);
            $this->setCurl(CURLOPT_VERBOSE, false);
            $this->setCurl(CURLOPT_RETURNTRANSFER, 1);
            $this->setCurl(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            $this->setCurl(CURLOPT_ENCODING, 'gzip,deflate');
            $this->setCurl(CURLOPT_AUTOREFERER, true);
            $this->setCurl(CURLOPT_FOLLOWLOCATION, true);
            $this->setCurl(CURLOPT_SSL_VERIFYPEER, false);
            /**
             * @todo will need on this later, for now we use true as default

              if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
              $this->setCurl(CURLOPT_FOLLOWLOCATION, true);
              } else {
              $this->_manual_follow = true;
              }
             *
             */
            $this->setCurl(CURLOPT_RETURNTRANSFER, 1);
            $this->setCurl(CURLOPT_HEADER, true); /* TRUE to include the header in the output. */
            $this->setCurl(CURLOPT_TIMEOUT, 0);
        }

        /**
         * @link http://php.net/manual/en/function.curl-setopt.php
         * @param type $option
         * @param type $value
         * @return boolean
         */
        public function setCurl($option, $value) {
            $rtn = curl_setopt($this->_curl, $option, $value);
            if ($option === CURLOPT_FOLLOWLOCATION) {
                if ($value === true) {
                    $this->_manual_follow = !$rtn;
                } else {
                    $this->_manual_follow = false;
                }
            }
            return $rtn;
        }

        /**
         * Add Header data
         * @param type $header
         */
        public function addHeader($header) {
            $this->_headers[] = $header;
        }

        /**
         * Execute to get header only
         * @param string $url
         * @param type $data
         * @return type
         */
        public function getHeaderOnly($url, $data = null) {
            $this->setCurl(CURLOPT_FRESH_CONNECT, true);
            if (!is_null($data)) {
                $fields = $this->makeQuery($data);
                $url .= '?' . $fields;
            }
            $this->setCurl(CURLOPT_URL, $url);
            $this->setCurl(CURLOPT_HEADER, true);
            $this->setCurl(CURLOPT_FILETIME, true);
            $this->setCurl(CURLOPT_NOBODY, true);

            $this->_response = curl_exec($this->_curl);

            if ($this->_response === false) {
                return false;
            }

            /* clean up */
            $this->setCurl(CURLOPT_NOBODY, false);
            $response = new CCurlResponse($this->_response, $this->getInfo());
            return $response->getHeader();
        }

        /**
         * Execute Curl and get full response
         * @param type $url
         * @param type $data
         * @return boolean
         */
        public function post($url, $data = array()) {
            $fields = $this->makeQuery($data);
            $this->setCurl(CURLOPT_URL, $url);
            $this->setCurl(CURLOPT_POST, true);
            $this->setCurl(CURLOPT_POSTFIELDS, $fields);

            $this->_response = curl_exec($this->_curl);

            if ($this->_response === false) {
                return false;
            }

            /* clean up */
            $this->setCurl(CURLOPT_POST, false);
            $this->setCurl(CURLOPT_POSTFIELDS, '');

            return new CCurlResponse($this->_response, $this->getInfo());
        }

        /**
         * 
         * @param string $url
         * @param type $data
         * @return \CCurlResponse|boolean
         */
        public function get($url, $data = null) {
            $this->setCurl(CURLOPT_FRESH_CONNECT, true);
            if (!is_null($data)) {
                $fields = $this->makeQuery($data);
                $url .= '?' . $fields;
            }
            $this->setCurl(CURLOPT_URL, $url);

            $this->_response = curl_exec($this->_curl);

            $error = curl_errno($this->_curl);

            if ($error != CURLE_OK || empty($this->_response)) {
                return false;
            }
            return new CCurlResponse($this->_response, $this->getInfo());
        }

        public function getFileHeader($remoteFile, $range = 32768) {
            $this->addHeader('Range: bytes=0-' . $range);
            $this->setCurl(CURLOPT_HEADER, false);
            $this->setCurl(CURLOPT_URL, $remoteFile);

            $this->_response = curl_exec($this->_curl);

            $error = curl_errno($this->_curl);

            if ($error != CURLE_OK || empty($this->_response)) {
                return false;
            }
            return $this->_response;
        }

        /**
         *
         * @param type $data
         * @return string
         */
        public function makeQuery($data) {
            if (is_array($data)) {
                $fields = array();
                foreach ($data as $key => $value) {
                    $fields[] = $key . '=' . urlencode($value);
                }
                $fields = implode('&', $fields);
            } else {
                $fields = $data;
            }

            return $fields;
        }

        /**
         *
         */
        public function noAjax() {
            foreach ($this->_headers as $key => $val) {
                if ($val == "X-Requested-With: XMLHttpRequest") {
                    unset($this->_headers[$key]);
                }
            }
        }

        /**
         * 
         */
        public function setAjax() {
            $this->_headers[] = "X-Requested-With: XMLHttpRequest";
        }

        /**
         *
         * @param type $username
         * @param type $password
         */
        public function setSsl($username = null, $password = null) {
            $this->setCurl(CURLOPT_SSL_VERIFYPEER, false);
            $this->setCurl(CURLOPT_SSL_VERIFYHOST, false);
            $this->setCurl(CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            /* if username & password provided and setCurl */
            if ($username && $password) {
                $this->setCurl(CURLOPT_USERPWD, "$username:$password");
            }
        }

        /**
         * 
         * @param type $username
         * @param type $password
         */
        public function setBasicAuth($username, $password) {
            $this->setCurl(CURLOPT_HEADER, false);
            $this->setCurl(CURLOPT_USERPWD, "$username:$password");
        }

        /**
         *
         * @param type $file
         */
        public function setCookieFile($file) {
            if (!file_exists($file)) {
                $handle = fopen($file, 'w+');
                fclose($handle);
            }
            $this->setCurl(CURLOPT_COOKIESESSION, true);
            $this->setCurl(CURLOPT_COOKIEJAR, $file);
            $this->setCurl(CURLOPT_COOKIEFILE, $file);
            $this->_cookiefile = $file;
        }

        /**
         *
         * @return type
         */
        public function getCookies() {
            $contents = file_get_contents($this->_cookiefile);
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

        /**
         *
         * @param type $val
         * @return boolean
         */
        public function setDataMode($val) {
            return $this->setCurl(CURLOPT_BINARYTRANSFER, $val);
        }

        /**
         *
         * @param type $connect
         * @param type $transfer
         */
        public function setTimeout($connect, $transfer) {
            $this->setCurl(CURLOPT_CONNECTTIMEOUT, $connect);
            $this->setCurl(CURLOPT_TIMEOUT, $transfer);
        }

        /**
         *
         * @return type
         */
        public function getInfo() {
            return curl_getinfo($this->_curl);
        }

        /**
         *
         * @return type
         */
        public function getError() {
            return curl_errno($this->_curl) ? curl_error($this->_curl) : false;
        }

        public function getHttpCode() {
            return curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);
        }

        /**
         * FOLLOWLOCATION manually if we need to
         * @param type $page
         * @return string
         */
        public function maybeFollow($page) {
            if (strpos($page, "\r\n\r\n") !== false) {
                list($headers, $page) = explode("\r\n\r\n", $page, 2);
            }

            $code = $this->getHttpCode();

            if ($code > 300 && $code < 310) {
                $info = $this->getInfo();
                preg_match("#Location: ?(.*)#i", $headers, $match);
                $this->_redirect_url = trim($match[1]);
                if (substr_count($this->_redirect_url, "http://") == 0 && isset($info['url']) && substr_count($info['url'], "http://")) {
                    $url_parts = parse_url($info['url']);
                    if (isset($url_parts['host']) && $url_parts['host']) {
                        $this->_redirect_url = "http://" . $url_parts['host'] . $this->_redirect_url;
                    }
                }
                if ($this->_manual_follow) {
                    return $this->get($this->_redirect_url);
                }
            } else {
                $this->_redirect_url = '';
            }

            return $page;
        }

        /**
         * 
         */
        public function close() {
            if (is_resource($this->_curl))
                curl_close($this->_curl);
        }

    }

}

/**
 * Class exists checking
 */
if (!class_exists('CCurlResponse')) {

    /**
     * Curl response object class
     */
    class CCurlResponse {

        /**
         *
         * @var type 
         */
        private $_response = null;

        /**
         *
         * @var type 
         */
        private $_curlInfo = array();

        public function __construct($response, $curlInfo) {
            $this->_response = $response;
            $this->_curlInfo = $curlInfo;
        }

        /**
         *
         * @return type
         */
        public function getHeader() {
            static $header;
            if (!isset($header))
                $header = $this->_parseResponseHeader(substr($this->_response, 0, $this->_curlInfo['header_size']));
            return $header;
        }

        /**
         *
         * @staticvar type $body
         * @return type
         */
        public function getBody() {
            static $body;
            if (!isset($body))
                $body = substr($this->_response, $this->_curlInfo['header_size']);
            return $body;
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
            return $headers;
        }

    }

}