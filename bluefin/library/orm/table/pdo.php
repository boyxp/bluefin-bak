<?php
namespace library\orm\table;
class pdo implements \library\orm\table,\component\injector
{
	const DB    = 'test';
	const TABLE = 'test';
	const KEY   = 'id';

	protected static $_query  = array();
	protected static $_locator = null;

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

	protected static function _getQueryInstance()
	{
		$key = static::DB.'_'.static::TABLE;
		if(!isset(static::$_query[$key])) {
			static::$_query[$key]= static::$_locator->get('query\pdo', array(static::DB, static::TABLE));
		}

		return static::$_query[$key];
	}

	public static function inject(\component\locator $locator)
	{
		static::$_locator = $locator;
	}
}
