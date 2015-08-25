<?php
namespace component\request;
class _default implements \component\request
{
	private $_pathinfo;

	public function __construct()
	{
		$this->_pathinfo = pathinfo($_SERVER['REQUEST_URI']);
	}

	public function getMethod()
	{
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ? 'AJAX' : $_SERVER['REQUEST_METHOD'];
	}

	public function getUri()
	{
		return $_SERVER['REQUEST_URI'];
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
		return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
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
