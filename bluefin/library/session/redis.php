<?php
namespace library\session;
class redis implements \library\session
{
	private $_life   = 1200;
	private $_path   = '/';
	private $_domain = '';
	private $_secure = false;
	private $_http   = true;
	private $_redis  = null;
	private $_prefix = 'SESSION:';
	private $_cache  = null;

	public function __construct(\library\redis $redis)
	{
		$this->_redis  = $redis;
		$this->_secure = isset($_SERVER['HTTPS']);
		session_set_save_handler($this, true);
		//if(!isset($_COOKIE['PHPSESSID'])) {
			//session_set_cookie_params($lifetime=1800, $path='/', $domain=$_SERVER['HTTP_HOST'], $secure=, $httponly=true);
		//}
	}

	public function setLifeTime($life_time)
	{
		$life_time = intval($life_time);
		if($life_time > 0) {
			$this->_life = $life_time;
		}

		return $this;
	}

	public function setPath($path)
	{
		$this->_path = $path;
		return $this;
	}

	public function setDomain($domain)
	{
		if(strpos($_SERVER['HTTP_HOST'], $domain)!==false) {
			$this->_domain = $domain;
		}
		return $this;
	}

	public function setSecure($secure)
	{
		$this->_secure = $secure ? true : false;
		return $this;
	}

	public function setHttpOnly($http_only)
	{
		$this->_http = $http_only ? true : false;
		return $this;
	}

	public function __set($option, $value)
	{
		$method = 'set'.$option;
		if(method_exists(__CLASS__, $method)) {
			call_user_func(array($this, $method), $value);
		}
	}

	public function open($path=null, $name=null)
	{
		return true;
	}

	public function read($session_id)
	{
		$this->_cache = $this->_redis->get($this->_prefix.$session_id);
		$this->_redis->expire($this->_prefix.$session_id, $this->_life);
		return $this->_cache===null ? '' : $this->_cache;
	}

	public function write($session_id, $session_data)
	{
		if($this->_cache!==$session_data) {
			$this->_redis->set($this->_prefix.$session_id, $session_data);
			$this->_cache = $session_data;
		}

		return true;
	}

	public function destroy($session_id)
	{
		$this->_cache = null;
		$this->_redis->del($this->_prefix.$session_id);
		return true;
	}

	public function gc($max_life_time)
	{
		return true;
	}

	public function close()
	{
		return true;
	}
}
