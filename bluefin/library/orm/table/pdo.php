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
		$fields     = $multi ? array_keys(current($data)) : array_keys($data);
		$connection = static::$_locator->pool->getConnection(static::DB, $master=true);
		$statement  = $connection->prepare('INSERT INTO '.static::TABLE.'(`'.join($fields, '`,`').'`)VALUES(?'.str_repeat(',?', count($fields)-1).')');

		if($multi) {
			$connection->begin();
			try {
				foreach($data as $row) {
					$statement->execute(array_values($row));
				}
				$connection->commit();
				return count($data);
			} catch(exception $e) {
				$connection->rollback();
				return 0;
			}
		} else {
			$statement->execute(array_values($data));
			return $connection->lastInsertId();
		}
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
