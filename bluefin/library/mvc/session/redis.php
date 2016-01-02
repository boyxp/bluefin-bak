<?php
namespace library\mvc\session;
use library\mvc\session;
use library\orm\connection\redis as connection;
class redis implements session
{
	private $_life   = 1200;
	private $_path   = '/';
	private $_domain = '';
	private $_secure = false;
	private $_http   = true;
	private $_redis  = null;
	private $_prefix = 'SESSION:';
	private $_cache  = null;

	public function __construct(connection $redis)
	{
		$this->_redis  = $redis;
		$this->_secure = isset($_SERVER['HTTPS']);
		$this->_domain = $_SERVER['SERVER_NAME'];
		$this->_prefix = "SESSION:{$_SERVER['SERVER_NAME']}:";
		session_set_save_handler($this, true);
	}

	public function start()
	{
		session_name('SECUREID');
		session_set_cookie_params(0, $this->_path, $this->_domain, $this->_secure, $this->_http);
		session_start();
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
		if($session_data and $this->_cache!==$session_data) {
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
			$this->_prefix = "SESSION:{$domain}:";
		}
		return $this;
	}

	public function setSecure($secure)
	{
		$this->_secure = ($secure and isset($_SERVER['HTTPS']));
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
}
