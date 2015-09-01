<?php
namespace library\orm;
interface query
{
	public function select($columns='*');
	public function from();
	public function where($condition, array $bind=null);
	public function group($fields);
	public function having($condition, array $bind=null);
	public function order($field, $direction='ASC');
	public function limit($count=20, $offset=0);
	public function fetch($all=true);
}
