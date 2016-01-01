<?php
namespace component\registry;
use component\registry;
class apc implements registry
{
	private $_prefix = '';
	private $_cache  = array();

	public function __construct($prefix=null)
	{
		if(isset($_SERVER['HTTP_HOST'])) {
			$this->_prefix .= $_SERVER['HTTP_HOST'].':';
		}

		if($prefix!==null and is_string($prefix)) {
			$this->_prefix .= $prefix.':';
		}
	}

	public function get($key)
	{
		return apc_fetch($this->_prefix.$key);
	}

	public function __get($key)
	{
		if(!isset($this->_cache[$key])) {
			$this->_cache[$key] = $this->get($key);
		}

		return $this->_cache[$key];
	}

	public function set($key, $value, $ttl=0)
	{
		apc_store($this->_prefix.$key, $value, $ttl);
		return $this;
	}

	public function __set($key, $value)
	{
		$this->_cache[$key] = $value;
		return $this->set($key, $value);
	}

	public function exists($key)
	{
		return apc_exists($this->_prefix.$key); 
	}

	public function delete($key)
	{
		if(isset($this->_cache[$key])) {
			unset($this->_cache[$key]);
		}

		return apc_delete($this->_prefix.$key);
	}

	public function flush()
	{
		$this->_cache = array();

		$iterator = new \APCIterator('user');
		foreach($iterator as $key=>$value) {
			if($this->_prefix==='' or strpos($key, $this->_prefix)===0) {
				apc_delete($key);
			}
		}
		$iterator = null;
		return $this;
	}
}
