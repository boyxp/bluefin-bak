<?php
namespace library\orm;
interface table
{
	public static function insert($data, $multi=false);
	public static function select($columns='*', $condition=null, array $bind=null);
	public static function update($data, $condition=null, array $bind=null);
	public static function delete($condition=null, array $bind=null);
}
