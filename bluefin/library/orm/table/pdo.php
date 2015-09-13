<?php
namespace library\orm\table;
class pdo implements \library\orm\table,\component\injector
{
	const DB    = 'test';
	const TABLE = 'test';
	const KEY   = 'id';

	public static function insert(array $data=null, $multi=false)
	{
	}

	public static function select($columns='*', $condition=null, array $bind=null)
	{
	}

	public static function update(array $data, $condition=null, array $bind=null)
	{
	}

	public static function delete($condition=null, array $bind=null)
	{
	}

	public static function getConnection()
	{
	}

	public static function inject(\component\locator $locator)
	{
		static::$_locator = $locator;
	}
}
