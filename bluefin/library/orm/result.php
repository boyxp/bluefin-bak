<?php
namespace library\orm;
interface result extends \Countable,\Iterator,\ArrayAccess
{
	public function offset($row=0, $offset=0);
	public function column($column_key=null, $index_key=null);
	public function each(callable $callback);
	public function map(callable $callback);
	public function join(\library\orm\result $result, $left_key=null, $right_key=null);
}
