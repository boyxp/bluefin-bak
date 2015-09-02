<?php
namespace library\orm;
interface resultset extends \Countable,\Iterator,\ArrayAccess
{
	public function offset($row_num=0, $offset=0);
	public function column($column_key=null, $index_key=null);
	public function each(callable $callback);
	public function map(callable $callback);
	public function join(\library\orm\resultset $result, $left_key=null, $right_key=null);
}
