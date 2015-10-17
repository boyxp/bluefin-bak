<?php
namespace library\orm;
interface table
{
	public static function insert(array $data=null);
	public static function select($columns='*');
	public static function update(array $data);
	public static function delete();
	public static function getConnection();
}
