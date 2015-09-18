<?php
namespace library\orm\pool;
class _default implements \library\orm\pool,\component\injector
{
	private static $_connection = array();
	private static $_singleton  = array();
	private static $_locator    = null;

	public function addConnection($db, \library\orm\connection $connection, $master=true)
	{
		$type = $master ? 'master' : 'slave';
		static::$_connection[$db][$type][] = $connection;
	}

	public function getConnection($db, $master=true)
	{
		$type = $master ? 'master' : 'slave';

		if(isset(static::$_singleton[$db][$type])) {
			return static::$_singleton[$db][$type];
		}

		if(isset(static::$_connection[$db][$type])) {
			$index = array_rand(static::$_connection[$db][$type]);
			static::$_singleton[$db][$type] = static::$_connection[$db][$type][$index];
			return static::$_singleton[$db][$type];
		}

		return null;
	}

	public function removeConnection($db, $master=true)
	{
		$type = $master ? 'master' : 'slave';

		if(isset(static::$_connection[$db][$type])) {
			unset(static::$_connection[$db][$type]);
		}
	}

	public function flushConnection()
	{
		static::$_connection = array();
	}

	public function __get($db)
	{
		return $this->getConnection($db);
	}

	public function __set($db, \library\orm\connection $connection)
	{
		$this->addConnection($db, $connection);
	}

	public static function inject(\component\locator $locator)
	{
		static::$_locator = $locator;
	}

	public function __destruct()
	{
		static::$_connection = null;
		static::$_singleton  = null;
	}
}
