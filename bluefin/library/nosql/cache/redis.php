<?php
namespace library\nosql\cache;
use library\nosql\cache;
use library\orm\connection;
class redis implements cache
{
	private $_redis  = null;

	public function __construct(connection $redis)
	{
		$this->_redis = $redis;
	}

	public function get($key)
	{
		return $this->_redis->get($key);
	}

	public function __get($key)
	{
		return $this->get($key);
	}

	public function set($key, $value)
	{
		return $this->_redis->set($key, $value);
	}

	public function __set($key, $value)
	{
		return $this->set($key, $value);
	}

	public function expire($key, $ttl=60)
	{
		return $this->_redis->expire($key, intval($ttl));
	}

	public function ttl($key)
	{
		return $this->_redis->ttl($key);
	}

	public function exists($key)
	{
		return $this->_redis->exists($key);
	}

	public function delete($key)
	{
		return $this->_redis->del($key);
	}

	public function flush()
	{
	}
}
