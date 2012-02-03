﻿<?php
/*
 * title: fun.php
 * author: kevyu
 * version: v1.5.0
 * updated: 2011/1/12 
 */
include 'Fb.php';
ob_start();	//or FirePHP will failed
session_start();
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
if (!function_exists('curl_init')) {
	throw new Exception('FUN needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
	throw new Exception('FUN needs the JSON PHP extension.');
}

class FUN
{
	const API_VERSION = '1.5.0';
	/**
	 * API_URL
	 */
	const API_URL_PRODUCTION = 'http://api.fun.wayi.com.tw/';
	const API_URL_TESTING = 'http://apitest.fun.wayi.com.tw/';
	private $API_URL;
	protected $testing = false;

	protected $appId;
	protected $apiSecret;
 	protected $redirectUri;
	protected $keepCookie = true;

	protected $session;		//user login status(access_token)
	protected $fileUploadSupport; 	//support file upload
	protected $config;

	private $logger;
	public function __construct($config) {
		$this->logger = FirePHP::getInstance(true);
		$this->logger->info(sprintf('%s start fun php-sdk(%s) ...',date('Y-m-d H:i:s '), self::API_VERSION));

		//parameters
		$this->config = $config;
		$this->setAppId($config['appId']);
		$this->setApiSecret($config['secret']);
		if(!$this->setRedirectUri($config['redirect_uri']))
			die('redirect uri is invalid');	

	
		if(isset($config['keepCookie']) && ($config['keepCookie'] == false)){
			$this->logger->info('do not keep cookie information');
			$this->keepCookie = false;
		}

		if(isset($config['testing']) && $config['testing']){
			$this->logger->info('it is testing');
			$this->testing = true;
			$this->API_URL = self::API_URL_TESTING;
		}else{
			$this->API_URL = self::API_URL_PRODUCTION;
		}

		//afeter testing parameter is read
		if(isset($_GET['logout']))
			$this->logout();

	}

	/*
	 *	getter & setter
	 */ 
	public function getApiUrl(){
		return $this->API_URL;
	}

	public function setAppId($appId) {
		$this->appId = $appId;
		return $this;
	}

	public function getAppId() {
		return $this->appId;
	}

	public function setApiSecret($apiSecret) {
		$this->apiSecret = $apiSecret;
		return $this;
	}

	public function getApiSecret() {
		return $this->apiSecret;
	}

	public function setRedirectUri($uri = '') {
		//need http:// 
		if(!$this->isValidURL($uri))
			return false;
		$this->redirectUri = $uri;
		return $this;
	}
	function isValidURL($url)
	{
		return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
	}

	public function getRedirectUri() {
		return $this->redirectUri;
	}

	public function getSession() {
		$this->logger->info('[getSession] start ...');
		if ($this->session)
			return $this->session;

		$session = array();

		/*
		 *	session fetching seq
		 *	1.get session from request first, if it's sns iframe it will get f8d 
		 *	2.then session from sdk
		 */ 
		if (isset($_REQUEST['session'])){
			$this->logger->info('[getSession] get session form fun session');
			$session = json_decode(
					get_magic_quotes_gpc()
					? stripslashes(urldecode($_REQUEST['session']))
					: urldecode($_REQUEST['session']),
					true
					);
		}else if (isset($_SESSION[$this->getCookieName()])){
			$this->logger->info('[getSession] get session form sdk session' . $this->getCookieName());
			$session = $_SESSION[$this->getCookieName()];
		}else if ( $this->keepCookie && isset($_COOKIE[$this->getCookieName()])){
			$this->logger->info('[getSession] get session form sdk cookie ' . $this->getCookieName());
			$session = json_decode( stripslashes($_COOKIE[$this->getCookieName()]), true);
		}else if (isset($_GET['code'])){
			$this->logger->info('[getSession] get code');
			//auth_code exchange token
			$params = array(
					'code' 		=> $_GET['code'],
					'grant_type' 	=> 'authorization_code',
					'redirect_uri'	=> $this->redirectUri,
					'client_id' 	=> $this->appId,
					'client_secret' => $this->apiSecret
				       );
			$result = json_decode($this->makeRequest($this->API_URL.'oauth/token', $params, $method="GET"));
			
			if (is_array($result) && isset($result['error'])) {
				$e = new ApiException($result);
				throw $e;
				return false;
			}

			$session = $result;
		}

		//if session
		if($session){
			//vlidate access token
			$this->setSession($session);
		}

		return ($this->session)?$this->session:false;
	}

	function verify_token($access_token){
		$path = '/oauth/verify_access_token';
		$method ='GET';
		$params = array('access_token'=> $access_token);

		try{
			$result = json_decode($this->makeRequest($this->getUrl($path), $params, $method),true);
		}catch(ApiException $ex){
			$this->logger->info(sprintf('[verify token] token invalid(%s)', print_r($ex,true)));
			$this->clearCookie($this->getCookieName());
			return false;
		}
		return true;
	}


	function isCurrencyMode(){
		return isset($this->config['currency']);
	}

	function getCurrencyUrl(){
		$this->logger->info('[getCurrencyUrl]get currency url');

		//0.precondition
		if(!$this->getCurrencySkey())
			return $this->getLoginUrl();

		$result = $this->Api('/v1/me/currency','GET',array('serial' => $this->getCurrencySkey()));

		return $result;
	}

	/**
	 * setup user status
	 *
	 * @param object $session		
	 * @return void
	 */
	public function setSession($session=null) {
		$this->session = (array) $session;	//casting type will preventing avoiding object or array type
		if(!$this->verify_token(urldecode($this->session['access_token']))){
			$this->session=null;
			$this->clearCookie($this->getCookieName());
			return;
		}

		$_SESSION[$this->getCookieName()] = $this->session;
		$this->setCookie($this->getCookieName(), json_encode($this->session));
	}

	public function getAccessToken() {
		//$session = $this->getSession();
		if ($this->session) {
			return $this->session['access_token'];
		}else{
			return false;
		}
	}

	/*
  	 * if user not login, provide login url
	 * @return string
	 */
	public function getLoginUrl($return_uri) {
		//0.validate
		$clean['redirect_uri'] = (isset($this->config['redirect_uri']))?$this->config['redirect_uri']:'';
		$clean['scope'] =  (empty($this->config['scope']))?'':$this->config['scope'];
		$clean['game_type'] = (isset($this->config['currency']) && isset($this->config['currency']['game_type']))?$this->config['currency']['game_type']:'';

		//without currency, it will login by session or cookies
		$params = array(
				'response_type' => 'code',
				'redirect_uri' => urlencode($clean['redirect_uri']),
				'client_id' => urlencode($this->appId),
				'scope' => urlencode($clean['scope']),
				'currency' => urlencode($clean['game_type'])
			       );
		return $this->API_URL . "oauth/authorize?" .  http_build_query($params);
	}

	public function getLogoutUrl(){
		return '?logout='. md5(time());
	}

	/*
	 * plus api_url to recongnize testing or production cookies
	 */
	function getCookieName(){
		$env = ($this->testing)?"testing":"production";
		return sprintf('F8D_%s', $env);
	}
	public function logout(){
		$this->logger->info(sprintf('[logout] unset cookie(%s)', $this->getCookieName()));
		$this->clearCookie($this->getCookieName());
	}

	public function getGameMallUrl(){
		return 'http://gamemall.wayi.com.tw/shopping/default.asp?action=wgs_list'; 
	}	

	/**
	 * 設定上傳檔案狀態
	 *
	 * @param bool $$fileUploadSupport		設定狀態
	 * @return void
	 */
	public function setFileUploadSupport($fileUploadSupport) {
		$this->fileUploadSupport = $fileUploadSupport;
	}

	/**
	 * use file 
	 * @return bool
	 */
	public function useFileUploadSupport() {
		return $this->fileUploadSupport;
	}

	
	/**
	 * 蕞API
	 * @return string
	 */
	public function api($path, $method = 'GET', $params = array()) {
		$this->logger->info(sprintf(sprintf('[api] %s %s ', $path, $method)));

		$params['method'] = $method;

		if (!isset($params['access_token'])) {
			$this->session = (array)$this->session;		//sometimes it is stdClass, and it will cause error
			$params['access_token'] = $this->session['access_token'];
		}

		foreach ($params as $key => $value) {
			if (!is_string($value)) {
				$params[$key] = json_encode($value);
			}
		}
		$result = json_decode($this->makeRequest($this->getUrl($path), $params, $method),true);

		if (is_array($result) && isset($result['error'])) {
			$e = new ApiException($result);
			throw $e;
		}

		return $result;
	}

	protected function makeRequest($url, $params, $method="GET") {
		$ch = curl_init();
		$opts = array(
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT        => 60,
				CURLOPT_USERAGENT      => 'funapi'
			     );

		switch ($method) {
			case 'POST':
				curl_setopt($ch, CURLOPT_POST, TRUE);
				if ($this->useFileUploadSupport()) 
					$opts[CURLOPT_POSTFIELDS] = $params;
				else
					$opts[CURLOPT_POSTFIELDS] = http_build_query($params, null, '&');
				break;
			case 'DELETE':
				$opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
				break;
			case 'PUT':
				$opts[CURLOPT_PUT] = TRUE;
				break;
		}

		$pureUrl = $url;
		if($method!="POST")
		{
			$params = http_build_query($params, null, '&');
			$url.="?".$params;
		}
		$this->logger->info(sprintf('connect to: %s - url: %s - param: %s', $url, $pureUrl, $params));

		$opts[CURLOPT_URL] = $url;

		if (isset($opts[CURLOPT_HTTPHEADER])) {
			$existing_headers = $opts[CURLOPT_HTTPHEADER];
			$existing_headers[] = 'Expect:';
			$opts[CURLOPT_HTTPHEADER] = $existing_headers;
		} else {
			$opts[CURLOPT_HTTPHEADER] = array('Expect:');
		}

		curl_setopt_array($ch, $opts);
		$result = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($code != 200) {
			$this->logger->warn(sprintf('done :%s result:%s ',  $code , $result));
			if($code == 401)
				$this->logout();
			$e = new ApiException(array(
						'error_code' => $code,
						'error_description'=> $result)
					);
			curl_close($ch);
			throw $e;
		}
		$this->logger->info(sprintf('done :%s result:%s ',  $code , $result));
		curl_close($ch);
		return $result;
	}


	/**
	 * setup cookie
	 *
	 * @param string $name	
	 * @param string $value
	 * @return void
	 */
	private function setCookie($name, $value) {
		$this->logger->info(sprintf('[setCookie] name: %s value: %s',$name,$value));
//		$mtime = explode(' ', microtime());
//		setcookie($name, $value, $mtime[1]+intval(30*60*1000));
		setcookie($name, $value, time() + 3600);
	}

	/**
	 * clear cookie
	 *
	 * @param string $name	
	 * @param string $value
	 * @return void
	 */
	private function clearCookie($name) {
		$this->logger->info(sprintf('[clear session] session(%s)' , $name));
		session_unset();
		//session_destroy();

		$this->logger->info(sprintf('[clear cookie] cookie(%s)' , $name));
		setcookie($name, '');
		setcookie($name, '', time() - 3600*24*365);
		unset($_COOKIE[$name]);
	}

	/**
	 * generater URL
	 *
	 * @param string $path	
	 * @param string $params
	 * @return void
	 */
	protected function getUrl($path='', $params=array()) {
		$url = $this->API_URL;
		if ($path) {
			if ($path[0] === '/') {
				$path = substr($path, 1);
			}
			$url .= $path;
		}
		if ($params) {
			$url .= '?' . http_build_query($params, null, '&');
		}
		return $url;
	}
}

class ApiException extends Exception
{
	protected $result;
	public function __construct($result) {
		$this->result = $result;

		$code = isset($result['error_code']) ? $result['error_code'] : 0;

		if (isset($result['error_description'])) {
			$msg = $result['error_description'];
		} else {
			$msg = 'Unknown Error. Check getResult()';
		}
		parent::__construct($msg, $code);
	}

	public function getResult() {
		return $this->result;
	}

	public function errorMessage(){
	}
	public function printMessage(){
		echo 'Error Code:' .  $this->result['error_code'] . '<br/>';
		echo 'Message:' . $this->getMessage() . '<br/>';
		echo 'Description:' . $this->result['error_description'] . '<br/>';
		echo 'Stack trace:<br/>';
		$traces = $this->getTrace();		

		$result = '';
		foreach($traces as $trace){
			if($trace['class'] != '') {
				$result .= $trace['class'];
				$result .= '->';
			}
			$result .= $trace['function'];
			$result .= '();<br />';
		}
		echo $result;

	}
	public function getType() {
		if (isset($this->result['error'])) {
			$error = $this->result['error'];
			return $error;
		}
		return 'Exception';
	}
}
?>

