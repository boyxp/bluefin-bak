<?php
namespace library\orm;
interface table
{
	public static function insert(array $data=null, $multi=false);
	public static function select($columns='*');
	public static function update(array $data, $condition=null, array $bind=null);
	public static function delete($condition=null, array $bind=null);
	public static function getConnection();
}
