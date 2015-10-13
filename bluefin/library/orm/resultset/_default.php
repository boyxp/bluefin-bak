<?php
namespace library\orm\resultset;
class _default implements \library\orm\resultset,\component\injector
{
	private $_query   = null;
	private $_data    = null;
	private $_positon = 0;

	private static $_locator = null;

	public function __construct(array $data=array(), $query=null)
	{
		$this->_data   = $data;
		$this->_query  = $query;
	}

	public function offset($row_num=0, $offset=0)
	{
		$row_num= (int)$row_num;
		$offset = (int)$offset;
		if(isset($this->_data[$row_num]) and count($this->_data[$row_num])>$offset) {
			$values = array_values($this->_data[$row_num]);
			return $values[$offset];
		}
		return null;
	}

	public function column($column_key=null, $index_key=null)
	{
		if(function_exists('array_column')) {
			return array_column($this->_data, $column_key, $index_key);
		}

		$temp = array();
		if(!is_null($column_key) and !is_null($index_key)) {
			foreach($this->_data as $row) {
				$temp[$row[$index_key]] = $row[$column_key];
			}
		} elseif(is_null($column_key)) {
			foreach($this->_data as $row) {
				$temp[$row[$index_key]] = $row;
			}
		} else {
			foreach($this->_data as $row) {
				$temp[] = $row[$column_key];
			}
		}

		return $temp;
	}

	public function each(callable $callback)
	{
		array_map($callback, $this->_data);
		return $this;
	}

	public function map(callable $callback)
	{
		$this->_data = array_map($callback, $this->_data);
		return $this;
	}

	public function join(\library\orm\resultset $result, $left_key=null, $right_key=null)
	{
		if(is_null($left_key) and is_null($right_key)) {
			$key = array_intersect(array_keys($this->_data[0]), array_keys($result[0]));
			if(!isset($key[0])) { return null; }
			$left_key = $right_key = $key[0];
		} elseif(is_null($right_key)) {
			$right_key = $left_key;
		}

		$right = $result->column(null, $right_key);
		$temp  = array();
		foreach($this->_data as $left_row) {
			$left_item = $left_row[$left_key];
			$right_row = isset($right[$left_item]) ? $right[$left_item] : array();
			$temp[]    = array_merge($left_row, $right_row);
		}

		$this->_data = $temp;
		return $this;
	}


	//Countable
	public function count()
	{
		return count($this->_data);
	}


	//Iterator
	public function current()
	{
		return static::$_locator->get('record', array($this->_data[$this->_positon], $this->_query));
	}

	public function key()
	{
		return $this->_positon;
	}

	public function next()
	{
		++$this->_positon;
	}

	public function rewind()
	{
		$this->_positon = 0;
	}

	public function valid()
	{
		return isset($this->_data[$this->_positon]);
	}


	//ArrayAccess
	public function offsetSet($offset, $value)
	{
		if(is_null($offset)) {
			$this->_data[] = $value;
		} else {
			$this->_data[$offset] = $value;
		}
	}

	public function offsetExists($offset)
	{
		return isset($this->_data[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this->_data[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->_data[$offset]) ? static::$_locator->get('record', array($this->_data[$offset], $this->_query)) : null;
	}

	public static function inject(\component\locator $locator)
	{
		static::$_locator = $locator;
	}
}
