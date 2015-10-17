<?php
namespace library\orm\record;
class _default implements \library\orm\record
{
	private $_data    = null;
	private $_query   = null;
	private $_key     = null;
	private $_created = false;

	public function __construct(array $data=null, $query=null)
	{
		$this->_data    = $data===null ? array() : $data;
		$this->_query   = $query;
		$this->_created = $data===null;
	}

	public function __get($column)
	{
		return isset($this->_data[$column]) ? $this->_data[$column] : null;
	}

	public function __set($column, $value)
	{
		$this->_data[$column] = $value;
	}

	public function save()
	{
		if($this->_created) {
			$this->_key     = $this->_query->insert($this->_data)->execute();
			$this->_created = false;
		} elseif($this->_key) {
			$this->_query->update($this->_data)->where($this->_key)->execute();
		} else {
			$this->_query->update($this->_data)->execute();
		}
	}

	public function delete()
	{
		if($this->_created===false) {
			$this->_query->delete($this->_data)->execute();
			$this->_key     = null;
			$this->_data    = array();
			$this->_created = true;
		}
	}

	//Countable
	public function count()
	{
		return count($this->_data);
	}


	//Iterator
	public function current()
	{
		return current($this->_data);
	}

	public function key()
	{
		return key($this->_data);
	}

	public function next()
	{
		next($this->_data);
	}

	public function rewind()
	{
		reset($this->_data);
	}

	public function valid()
	{
		return current($this->_data)!==false;
	}


	//ArrayAccess
	public function offsetSet($key, $value)
	{
		if(!is_null($key)) {
			$this->_data[$key] = $value;
		}
	}

	public function offsetExists($key)
	{
		return isset($this->_data[$key]);
	}

	public function offsetUnset($key)
	{
		unset($this->_data[$key]);
	}

	public function offsetGet($key)
	{
		return isset($this->_data[$key]) ? $this->_data[$key] : null;
	}
}
