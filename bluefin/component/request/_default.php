<?php
namespace component\request;
use component\request as request;
class _default implements request
{
	private $_pathinfo;
	private $_uri   = null;
	private $_query = null;

	public function __construct()
	{
		if(strpos($_SERVER['REQUEST_URI'], '?')!==false) {
			list($uri, $query) = explode('?', $_SERVER['REQUEST_URI']);
			parse_str($query, $this->_query);
		} else {
			$uri = $_SERVER['REQUEST_URI'];
		}

		$this->_pathinfo = pathinfo($uri);
		$this->_uri      = $uri;
	}

	public function getMethod()
	{
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ? 'AJAX' : $_SERVER['REQUEST_METHOD'];
	}

	public function getUri()
	{
		return $this->_uri;
	}

	public function getScheme()
	{
		return isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : 'http';
	}

	public function getHost()
	{
		return $_SERVER['HTTP_HOST'];
	}

	public function getPort()
	{
		return $_SERVER['SERVER_PORT'];
	}

	public function getPath()
	{
		return $this->_pathinfo['dirname'];
	}

	public function getBasename()
	{
		return $this->_pathinfo['basename'];
	}

	public function getFilename()
	{
		return $this->_pathinfo['filename'];
	}

	public function getExtension()
	{
		return isset($this->_pathinfo['extension']) ? $this->_pathinfo['extension'] : null;
	}

	public function getQueryString()
	{
		return $_SERVER['QUERY_STRING'];
	}

	public function getQuery($name=null, $default=null)
	{
		return isset($this->_query[$name]) ? $this->_query[$name] : $default;
	}

	public function getClientIp()
	{
		if(isset($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif(isset($_SERVER['REMOTE_ADDR'])) {
			return $_SERVER['REMOTE_ADDR'];
		} else{
			return null;
		}
	}

	public function getUser()
	{
		return isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
	}

	public function getPassword()
	{
		return isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null;
	}

	public function getReferer()
	{
		return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
	}

	public function getUserAgent()
	{
		return $_SERVER['HTTP_USER_AGENT'];
	}

	public function __get($name)
	{
		$method = 'get'.$name;
		return method_exists(__CLASS__, $method) ? $this->$method() : null;
	}
}
