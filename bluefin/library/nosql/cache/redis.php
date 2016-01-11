<?php
namespace library\nosql\cache;
use library\nosql\cache;
use library\orm\connection;
class redis implements cache
{
	private $_redis  = null;
	private $_prefix = null;

	public function __construct(connection $redis, $prefix=null)
	{
		$this->_redis = $redis;
		if($prefix) {
			$this->_prefix = "{$prefix}:";
		} else {
			$this->_prefix = "CACHE:{$_SERVER['SERVER_NAME']}:";
		}
	}

	public function get($key)
	{
		return $this->_redis->get($this->_prefix.$key);
	}

	public function __get($key)
	{
		return $this->get($this->_prefix.$key);
	}

	public function set($key, $value, $ttl=0)
	{
		$this->_redis->set($this->_prefix.$key, $value);
		if($ttl>0) {
			$this->expire($this->_prefix.$key, $ttl);
		}

		return $this;
	}

	public function __set($key, $value)
	{
		return $this->set($this->_prefix.$key, $value);
	}

	public function expire($key, $ttl=60)
	{
		$this->_redis->expire($this->_prefix.$key, intval($ttl));
		return $this;
	}

	public function ttl($key)
	{
		$ttl = $this->_redis->ttl($this->_prefix.$key);
		return $ttl>0 ? $ttl : 0;
	}

	public function exists($key)
	{
		return $this->_redis->exists($this->_prefix.$key);
	}

	public function delete($key)
	{
		$this->_redis->del($this->_prefix.$key);
		return $this;
	}

	public function flush()
	{
	}
}
