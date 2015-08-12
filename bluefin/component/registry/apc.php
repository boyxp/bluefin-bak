<?php
namespace component\registry;
class apc implements \component\registry
{
	private $_prefix = '';

	public function __construct($prefix=null)
	{
		if(!is_null($prefix) and is_string($prefix)) {
			$this->_prefix = $prefix.':';
		}
	}

	public function get($key)
	{
		return apc_fetch($this->_prefix.$key);
	}

	public function __get($key)
	{
		return $this->get($key);
	}

	public function set($key, $value, $ttl=0)
	{
		apc_store($this->_prefix.$key, $value, $ttl);
		return $this;
	}

	public function __set($key, $value)
	{
		return $this->set($key, $value);
	}

	public function exists($key)
	{
		return apc_exists($this->_prefix.$key); 
	}

	public function delete($key)
	{
		return apc_delete($this->_prefix.$key);
	}

	public function flush()
	{
		$iterator = new \APCIterator('user');
		foreach($iterator as $key=>$value) {
			if($this->_prefix==='' or strpos($key, $this->_prefix)===0) {
				apc_delete($key);
			}
		}
		$iterator = null;
	}
}
