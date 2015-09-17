<?php
namespace library\orm\resultset;
class _default implements \library\orm\resultset,\component\injector
{
	private $data    = null;
	private $positon = 0;

	private static $_locator = null;

	public function __construct(array $data=array())
	{
		$this->data = $data;
	}

	public function offset($row_num=0, $offset=0)
	{
		$row_num= (int)$row_num;
		$offset = (int)$offset;
		if(isset($this->data[$row_num]) and count($this->data[$row_num])>$offset) {
			$values = array_values($this->data[$row_num]);
			return $values[$offset];
		}
		return null;
	}

	public function column($column_key=null, $index_key=null)
	{
		if(function_exists('array_column')) {
			return array_column($this->data, $column_key, $index_key);
		}

		$temp = array();
		if(!is_null($column_key) and !is_null($index_key)) {
			foreach($this->data as $row) {
				$temp[$row[$index_key]] = $row[$column_key];
			}
		} elseif(is_null($column_key)) {
			foreach($this->data as $row) {
				$temp[$row[$index_key]] = $row;
			}
		} else {
			foreach($this->data as $row) {
				$temp[] = $row[$column_key];
			}
		}

		return $temp;
	}

	public function each(callable $callback)
	{
		array_map($callback, $this->data);
		return $this;
	}

	public function map(callable $callback)
	{
		$this->data = array_map($callback, $this->data);
		return $this;
	}

	public function join(\library\orm\resultset $result, $left_key=null, $right_key=null)
	{
		if(is_null($left_key) and is_null($right_key)) {
			$key = array_intersect(array_keys($this->data[0]), array_keys($result[0]));
			if(!isset($key[0])) { return null; }
			$left_key = $right_key = $key[0];
		} elseif(is_null($right_key)) {
			$right_key = $left_key;
		}

		$right = $result->column(null, $right_key);
		$temp  = array();
		foreach($this->data as $left_row) {
			$left_item = $left_row[$left_key];
			$right_row = isset($right[$left_item]) ? $right[$left_item] : array();
			$temp[]    = array_merge($left_row, $right_row);
		}

		$this->data = $temp;
		return $this;
	}


	//Countable
	public function count()
	{
		return count($this->data);
	}


	//Iterator
	public function current()
	{
		return $this->data[$this->positon];
	}

	public function key()
	{
		return $this->positon;
	}

	public function next()
	{
		++$this->positon;
	}

	public function rewind()
	{
		$this->positon = 0;
	}

	public function valid()
	{
		return isset($this->data[$this->positon]);
	}


	//ArrayAccess
	public function offsetSet($offset, $value)
	{
		if(is_null($offset)) {
			$this->data[] = $value;
		} else {
			$this->data[$offset] = $value;
		}
	}

	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
    	}

	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->data[$offset]) ? $this->data[$offset] : null;
	}

	public static function inject(\component\locator $locator)
	{
		static::$_locator = $locator;
	}
}
