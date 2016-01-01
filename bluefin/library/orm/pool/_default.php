<?php
namespace library\orm\pool;
use library\orm\connection;
use library\orm\pool;
use component\injector;
use component\locator;
use component\registry;
class _default implements pool,injector
{
	private static $_registry   = null;
	private static $_connection = array();
	private static $_singleton  = array();
	private static $_locator    = null;

	public function __construct(registry $registry=null)
	{
		static::$_registry = $registry ? $registry : static::$_locator->get('registry', array('database'));
	}

	public function addConnection($db, connection $connection, $master=true)
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

		if(!static::$_registry) {
			return null;
		}

		$config = static::$_registry->get("{$db}:{$type}");
		if(!is_array($config) or count($config)===0) {
			$config = static::$_registry->get($db);
			if(!is_array($config) or count($config)===0) {
				return null;
			}
		}

		if(isset($config['connection']) and isset($config['param'])) {
			static::$_singleton[$db][$type] = static::$_locator->get($config['connection'], $config['param']);
			return static::$_singleton[$db][$type];
		}

		$index = array_rand($config);
		if(!is_null($index)) {
			$config = $config[$index];
			if(isset($config['connection']) and isset($config['param'])) {
				static::$_singleton[$db][$type] = static::$_locator->get($config['connection'], $config['param']);
				return static::$_singleton[$db][$type];
			}
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

	public function __set($db, connection $connection)
	{
		$this->addConnection($db, $connection);
	}

	public static function inject(locator $locator)
	{
		static::$_locator = $locator;
	}

	public function __destruct()
	{
		static::$_connection = null;
		static::$_singleton  = null;
		static::$_registry   = null;
	}
}
