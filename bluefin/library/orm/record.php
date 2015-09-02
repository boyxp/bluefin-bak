<?php
namespace library\orm;
interface record extends \Countable,\Iterator,\ArrayAccess
{
	public function __get($column);
	public function __set($column, $value);
	public function save();
	public function delete();
}
