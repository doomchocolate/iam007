<?php
class CAutoUpdate{
	
	//static private $downloadurl = 'http://localhost:8888/jomsocial/build/package/com_community_pro_3.0.zip';
	static private $error = array();
	static private $packagesize = 0;
	static private $ajaxresponse = NULL;
	static private $lastpct = '';
	static private $doprogress = true;
	static private $noprogressbrowser = array('opera','msie');
	
	/**
	* Get download progress from CURL
	*/
	static public function progressCallback($download_size, $downloaded, $upload_size, $uploaded){
		//error_log(self::$packagesize.", $downloaded, $upload_size, $uploaded");
		//error_log("$download_size, $downloaded, $upload_size, $uploaded");
		
		if(self::$packagesize > 0){
			$pct = round(($downloaded / self::$packagesize) * 100, 0) ;
			if(self::$lastpct != $pct) {
				self::$lastpct = $pct;
				//error_log($pct);
				
				//no progress info
				if(!self::$doprogress) return ;
				
				//echo "azcommunity.reportAutoUpdateProgress('".$pct."');";
				if(count(self::$ajaxresponse->_response) > 0){
					array_pop(self::$ajaxresponse->_response);
				}
				self::$ajaxresponse->addScriptCall("azcommunity.reportAutoUpdateProgress('".$pct."');");
				
				//copied from plugins/system/.../ajax.php
				$json = new Services_JSON();				
				echo $json->encode(self::$ajaxresponse->_response);
				//echo "<body onload=\"parent.jax_iresponse();\">" .htmlentities($json->encode(self::$ajaxresponse->_response)). "</body>";
				@ob_flush();
				flush();
			}
		}
	}
	
	
	/**
	* Get/Download the update
	*
	* @param
	* @return string downloaded package path OR FALSE on failure
	*/
	static public function getUpdate(){
		//$url = self::$downloadurl;
		
		//unfortunately for download progress, we now only support FF, Safari and Chrome. Opera and IE is not too friendly
		jimport('joomla.environment.browser');
		$browser = JBrowser::getInstance();
        $browserType = $browser->getBrowser();	//error_log($browserType);
		
		$filename = 'com_community.zip';
		$clientinfo = self::prepareInfo();
		$requestready = self::requestDownload($clientinfo);
		if(!$requestready){
			return false;
		}
		$url = $requestready;
		$config = JFactory::getConfig();
		$tmp_path = $config->get('tmp_path');
		self::$ajaxresponse	= new JAXResponse();
		self::$lastpct = '';
		
		//some browser are not playing nice on ajax readystate 3, for now, lets only report progress to the friendly ones
		if(in_array($browserType, self::$noprogressbrowser)){
			self::$doprogress = false;
			$downloaded = self::download($url, $tmp_path.'/'.$filename); 
		}else{
			self::$doprogress = true;
			@ob_end_clean();
			header('Content-type: application/x-javascript'); //header('Content-type: text/plain');		
			ob_start();
			$downloaded = self::download($url, $tmp_path.'/'.$filename); //error_log($tmp_path.'/'.$filename);	
			ob_flush();
			flush();
			@ob_end_flush();		
			self::$ajaxresponse	= NULL;
		}
		
		if(!$downloaded) return false;
		return $tmp_path.'/'.$filename;
	}
	
	/**
	* Request Download on our server
	*
	* @param array client info
	* @return boolean FALSE or Download URL
	*/
	static public function requestDownload($clientinfo){
				
		//test URL
		//$url = 'http://localhost:8888/myblog25/index.php?option=com_community&task=verifydownload';
		//$url = 'http://localhost:8888/jomwebsite25/index.php?option=com_account&task=verifydownload';
		$url = 'http://staging:t8wM2yCv6im@staging.jomsocial.com/index.php?option=com_account&task=verifydownload';
		
		//lets see if we can use CURL first
		if(self::hasCURL()){
			$res = self::getCURL($url, NULL, TRUE, $clientinfo);
		}elseif(self::hasFOPEN()){
			$res = self::getFOPEN($url, NULL, $clientinfo);
		}else{
			self::setError('Download request unsuccessful.');
			return false;
		}
		
		if(empty($res) || filter_var($res, FILTER_VALIDATE_URL) === FALSE){
			$msg = (empty($res)) ? 'Download request unsuccessful.' : $res;
			self::setError($msg);
			return false;
		}
		
		//set the download size here
		$urlpath = parse_url($res);
		parse_str($urlpath['query'], $qstr);
		self::$packagesize = $qstr['s'];
		
		return $res;
	}
	
	
	/**
	* Compare local version with latest available version
	*
	* @return boolean
	*/
	static public function checkUpdate(){
		$currlocal = self::getLocalVersionString();
		$latest = self::getCurrentVersionData();
		if(!$latest){
			self::setError(JText::_('COM_COMMUNITY_CONFIGURATION_AUTOUPDATE_CANNOT_CONTACT_SERVER'));
			return false;
		}
		if( trim($currlocal) < trim($latest->version.'.'.$latest->build)){
			self::setError(JText::_('COM_COMMUNITY_CONFIGURATION_AUTOUPDATE_GOT_UPDATE'));	
			return false;
		}
		return true;
	}
	
	/**
	* Get latest version - taken from controller/about.php
	*
	* @return stdobject
	*/
	static public function getCurrentVersionData()
	{
		$data	= new stdClass();
		$xml	= 'http://www.jomsocial.com/jomsocial.xml';
		try {
			$parser	= @new SimpleXMLElement( $xml , NULL , true );
		}catch(Exception $e){
			
			//try with cURL
			if(function_exists('curl_version')){
				$ch = curl_init();
				curl_setopt ($ch, CURLOPT_URL, $xml);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				$contents = curl_exec($ch);
				curl_close($ch);
				//echo $contents;
				$parser	= new SimpleXMLElement( $contents , NULL);
			}else{
				return false;
			}
		}
		
		/** Get version **/
		$data->version	= $parser->version;
		/** Get build number **/
		$data->build	= $parser->buid;
		/** Get updated date **/
		$data->updated	= $parser->updated;
		/** Get changelog url **/
		$data->changelogURL	= $parser->changelog;
		/** Get upgrade instructions url **/
		$data->instructionURL	= $parser->instruction;
		return $data;
	}

	/**
	* Get local version - taken from controller/about.php
	*
	* @return string
	*/
	static public function getLocalVersionString()
	{
		static $version		= '';
		if( empty( $version ) )
		{
			$xml		= JPATH_COMPONENT . '/community.xml';
			$parser		= new SimpleXMLElement( $xml , NULL , true );
			$version	= $parser->version;
		}
		return $version;
	}
	
	
	/**
	* Gather info about the current installation
	*/
	static public function prepareInfo(){
		$info = array();
		$info['domain'] = self::getHost();
		$info['code'] = self::getOrderCode();
		$info['email'] = self::getOrderMail();
		$info['joomla'] = JVERSION;
		return $info;
	}
	
	/**
	* Get Purchase Code / Order Code
	*/
	static public function getOrderCode(){
		$config = CFactory::getConfig();
		return $config->get('autoupdateordercode');
	}
	
	/**
	* Get the registered Order Email
	*/
	static public function getOrderMail(){
		$config = CFactory::getConfig();
		return $config->get('autoupdateemail');
	}
	
	
	/**
	* Referred from Akeeba's get_host() - Grab Domain Name
	*/
	static public function getHost(){
		if(!array_key_exists('REQUEST_METHOD', $_SERVER)) {
			// Running under CLI
			if(!class_exists('JURI', true)) {
				$filename = JPATH_ROOT.'/libraries/joomla/environment/uri.php';
				if(file_exists($filename)) {
					// Joomla! 2.5
					require_once $filename;
				} else {
					// Joomla! 3.x (and later?)
					require_once JPATH_ROOT.'/libraries/joomla/uri/uri.php';
				}
			}
			$url = '';
			$config = self::loadConfig();
			if(array_key_exists('siteurl', $config)){
				$url = $config[$key];
			}		
			$oURI = new JURI($url);
		} else {
			// Running under the web server
			$oURI = JURI::getInstance();
		}
		return $oURI->getHost();
	}

	//===========================================================
	// TAKEN FROM AKEEBA's classes/download.php
	//===========================================================
	
	
	/**
	 * Downloads from a URL and saves the result as a local file
	 * @param <type> $url
	 * @param <type> $target
	 * @return bool True on success
	 */
	public static function download($url, $target)
	{
		// Import Joomla! libraries
		jimport('joomla.filesystem.file');

		/** @var bool Did we try to force permissions? */
		$hackPermissions = false;

		// Make sure the target does not exist
		if(JFile::exists($target)) {
			if(!@unlink($target)) {
				JFile::delete($target);
			}
		}

		// Try to open the output file for writing
		$fp = @fopen($target, 'wb');
		if($fp === false) {
			// The file can not be opened for writing. Let's try a hack.
			$empty = '';
			if( JFile::write($target, $empty) ) {
				if( self::chmod($target, 511) ) {
					$fp = @fopen($target, 'wb');
					$hackPermissions = true;
				}
			}
		}
		
		$result = false;
		if($fp !== false)
		{
			// First try to download directly to file if $fp !== false
			$adapters = self::getAdapters();
			$result = false;
			while(!empty($adapters) && ($result === false)) {
				// Run the current download method
				$method = 'get' . strtoupper( array_shift($adapters) );
				$result = self::$method($url, $fp);
				
				// Check if we have a download
				if($result === true) {
					// The download is complete, close the file pointer
					@fclose($fp);
					// If the filesize is not at least 1 byte, we consider it failed.
					clearstatcache();
					$filesize = @filesize($target);
					if($filesize <= 0) {
						$result = false;
						$fp = @fopen($target, 'wb');
					}
				}
			}
			
			// If we have no download, close the file pointer
			if($result === false) {
				@fclose($fp);
			}
		}

		if($result === false)
		{
			// Delete the target file if it exists
			if(file_exists($target)) {
				if( !@unlink($target) ) {
					JFile::delete($target);
				}
			}
			// Download and write using JFile::write();
			$r = self::downloadAndReturn($url);
			$result = JFile::write($target, $r );
		}
		
		return $result;
	}

	/**
	 * Downloads from a URL and returns the result as a string
	 * @param <type> $url
	 * @return mixed Result string on success, false on failure
	 */
	public static function downloadAndReturn($url)
	{
		$adapters = self::getAdapters();
		$result = false;
		
		while(!empty($adapters) && ($result === false)) {
			// Run the current download method
			$method = 'get' . strtoupper( array_shift($adapters) );
			$result = self::$method($url, null);
		}
		
		return $result;
	}

	/**
	 * Does the server support PHP's cURL extension?
	 * @return bool True if it is supported
	 */
	private static function hasCURL()
	{
		static $result = null;

		if(is_null($result))
		{
			$result = function_exists('curl_init');
		}

		return $result;
	}
	
	/**
	 * Downloads the contents of a URL and writes them to disk (if $fp is not null)
	 * or returns them as a string (if $fp is null)
	 * @param string $url The URL to download from
	 * @param resource $fp The file pointer to download to. Omit to return the contents.
	 * @return bool|string False on failure, true on success ($fp not null) or the URL contents (if $fp is null)
	 */
	private static function getCURL($url, $fp = null, $nofollow = false, $data=NULL)
	{
		$result = false;
		
		$ch = curl_init($url);
				
		$cacert = '';
		if(!empty($cacert)) {
			if(file_exists($cacert)) {
				@curl_setopt($ch, CURLOPT_CAINFO, $cacert);
			}
		}
		
		curl_setopt( $ch, CURLOPT_NOPROGRESS, false );
		curl_setopt( $ch, CURLOPT_PROGRESSFUNCTION, array('CAutoUpdate', 'progressCallback' ));
		//curl_setopt( $ch, CURLOPT_PROGRESSFUNCTION, 'progressCallback' );

		
		if( !@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1) && !$nofollow ) {
			// Safe Mode is enabled. We have to fetch the headers and
			// parse any redirections present in there.
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);

			// Get the headers
			$data = curl_exec($ch);
			curl_close($ch);
			
			// Init
			$newURL = $url;
			
			// Parse the headers
			$lines = explode("\n", $data);
			foreach($lines as $line) {
				if(substr($line, 0, 9) == "Location:") {
					$newURL = trim(substr($line,9));
				}
			}

			// Download from the new URL
			if($url != $newURL) {
				return self::getCURL($newURL, $fp);
			} else {
				return self::getCURL($newURL, $fp, true);
			}
		} else {
			@curl_setopt($ch, CURLOPT_MAXREDIRS, 20);
		}
		
		//check if there's any data to be passed
		if(!empty($data)){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 75);
				
		// Pretend we are IE7, so that webservers play nice with us
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)');
		
		if(is_resource($fp)) {
			curl_setopt($ch, CURLOPT_FILE, $fp);
		}
		
		$result = curl_exec($ch);
		curl_close($ch);
		
		return $result;
	}

	/**
	 * Does the server support URL fopen() wrappers?
	 * @return bool
	 */
	private static function hasFOPEN()
	{
		static $result = null;

		if(is_null($result))
		{
			// If we are not allowed to use ini_get, we assume that URL fopen is
			// disabled.
			if(!function_exists('ini_get')) {
				$result = false;
			} else {
				$result = ini_get('allow_url_fopen');
			}
		}

		return $result;
	}
	
	/**
	* Use fopen 
	*
	* @param string URL
	* @param resource file socket
	* @return string
	*/
	private static function getFOPEN($url, $fp = null, $data=NULL)
	{
		$result = false;
		
		// Track errors
		if( function_exists('ini_set') ) {
			$track_errors = ini_set('track_errors',true);
		}
		
		// Open the URL for reading
		if(function_exists('stream_context_create')) {
			// PHP 5+ way (best)
			$httpopts = array('user_agent'=>'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)');
			
			if(!empty($data)){
				$httpopts['method'] = 'POST';
				$httpopts['header'] = 'Content-type: application/x-www-form-urlencoded';
				$httpopts['content'] = http_build_query($data);
			}
			
			$context = stream_context_create( array( 'http' => $httpopts ) );
			$ih = @fopen($url, 'r', false, $context);
		} else {
			// PHP 4 way (actually, it's just a fallback as we can't run Admin Tools in PHP4)
			if( function_exists('ini_set') ) {
				ini_set('user_agent', 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)');
			}
			$ih = @fopen($url, 'r');
		}
		
		// If fopen() fails, abort
		if( !is_resource($ih) ) {
			return $result;
		}
		
		// Try to download
		$bytes = 0;
		$result = true;
		$return = '';
		while (!feof($ih) && $result)
		{
			$contents = fread($ih, 4096);
			if ($contents === false) {
				@fclose($ih);
				$result = false;
				return $result;
			} else {
				$bytes += strlen($contents);
				if(is_resource($fp)) {
					$result = @fwrite($fp, $contents);
				} else {
					$return .= $contents;
					unset($contents);
				}
			}
		}
		
		@fclose($ih);
		
		if(is_resource($fp)) {
			return $result;
		} elseif( $result === true ) {
			return $return;
		} else {
			return $result;
		}
	}

	/**
	 * Detect and return available download adapters
	 *
	 * @return array
	 */
	private static function getAdapters()
	{
		// Detect available adapters
		$adapters = array();
		if(self::hasCURL()) $adapters[] = 'curl';
		if(self::hasFOPEN()) $adapters[] = 'fopen';
		return $adapters;
	}

	/**
	 * Change the permissions of a file, optionally using FTP
	 *
	 * @param string $file Absolute path to file
	 * @param int $mode Permissions, e.g. 0755
	 */
	private static function chmod($path, $mode)
	{
		if(is_string($mode))
		{
			$mode = octdec($mode);
			if( ($mode < 0600) || ($mode > 0777) ) $mode = 0755;
		}

		// Initialize variables
		jimport('joomla.client.helper');
		$ftpOptions = JClientHelper::getCredentials('ftp');

		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		if ($ftpOptions['enabled'] == 1) {
			// Connect the FTP client
			jimport('joomla.client.ftp');
			if(version_compare(JVERSION,'3.0','ge')) {
				$ftp = JClientFTP::getInstance(
					$ftpOptions['host'], $ftpOptions['port'], array(),
					$ftpOptions['user'], $ftpOptions['pass']
				);
			} else {
				if(version_compare(JVERSION,'3.0','ge')) {
					$ftp = JClientFTP::getInstance(
						$ftpOptions['host'], $ftpOptions['port'], array(),
						$ftpOptions['user'], $ftpOptions['pass']
					);
				} else {
					$ftp = JFTP::getInstance(
						$ftpOptions['host'], $ftpOptions['port'], array(),
						$ftpOptions['user'], $ftpOptions['pass']
					);
				}
			}
		}

		if(@chmod($path, $mode))
		{
			$ret = true;
		} elseif ($ftpOptions['enabled'] == 1) {
			// Translate path and delete
			jimport('joomla.client.ftp');
			$path = JPath::clean(str_replace(JPATH_ROOT, $ftpOptions['root'], $path), '/');
			// FTP connector throws an error
			$ret = $ftp->chmod($path, $mode);
		} else {
			return false;
		}
	}
	
	/**
	* Get Error Message
	*
	* @return array error list
	*/
	static public function getError(){
		return self::$error;
	}
	
	
	/**
	* Set Error Message
	*
	* @param string error message
	* @return array error list
	*/
	static private function setError($errmsg){
		$err = self::$error;
		$err[] = $errmsg;
		self::$error = $err;
		return self::$error;
	}
}
