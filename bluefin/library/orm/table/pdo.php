<?php
namespace library\orm\table;
class pdo implements \library\orm\table,\component\injector
{
		const DB    = 'test';
		const TABLE = 'test';
		const KEY   = 'id';

		protected static $_query  = array();
		protected static $_locator = null;

		public static function insert(array $data=null)
		{
			if($data===null) {
				$query = static::_getQueryInstance();
				return static::$_locator->get('record', array(null, $query));
			} else {
				return static::_getQueryInstance()->insert($data);
			}
		}

		public static function select($columns='*')
		{
			return static::_getQueryInstance()->select($columns);
		}

		public static function update(array $data)
		{
			return static::_getQueryInstance()->update($data);
		}

	public static function delete($condition=null, array $bind=null)
	{
		$where      = static::_condition($condition, $bind);
		$connection = static::$_locator->pool->getConnection(static::DB, $master=true);
		$statement  = $connection->prepare('DELETE FROM '.static::TABLE.' WHERE '.$where['condition']);
		$statement->execute($where['bind']);
		return $statement->rowCount();
	}

	public static function getConnection()
	{
		return static::$_locator->pool->getConnection(static::DB, $master=true);
	}

		protected static function _getQueryInstance()
		{
			$key = static::DB.':'.static::TABLE;
			if(!isset(static::$_query[$key])) {
				static::$_query[$key] = static::$_locator->get('query\pdo', array(static::DB, static::TABLE, static::KEY));
			}

			return static::$_query[$key];
		}

	public static function inject(\component\locator $locator)
	{
		static::$_locator = $locator;
	}
}
