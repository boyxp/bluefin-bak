<?php
namespace library\orm;
interface query
{
	public function insert(array $data);
	public function update(array $data);
	public function delete();
	public function select($columns='*');
	public function from();
	public function where($condition, array $bind=null);
	public function group($fields);
	public function having($condition, array $bind=null);
	public function order($field, $direction='ASC');
	public function limit($offset=20, $count=null);
	public function fetch($record=true);
	public function fetchAll($resultset=true);
	public function execute();
}
