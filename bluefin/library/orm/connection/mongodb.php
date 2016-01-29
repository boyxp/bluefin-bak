<?php
namespace library\orm\connection;
use library\orm\connection;
class mongodb implements connection
{
	protected static $_connection = array();
	protected $_manager  = null;
	protected $_database = null;

	public function __construct($dsn, array $options=null)
	{
		$options = $options===null ? array('w'=>1) : $options;

		if(preg_match('/\/([a-z0-9_]+)(?:\?|$)/i', $dsn, $matches)) {
			$this->_database = $matches[1];
		} else {
			$this->_database = 'development';
		}

		try {
			if(!isset(static::$_connection[$dsn])) {
				static::$_connection[$dsn] = new \MongoDB\Driver\Manager($dsn, $options);
			}

			$this->_manager = static::$_connection[$dsn];

		} catch (exception $e) {
			throw new \InvalidArgumentException('Connection failed: '.$e->getMessage(), $e->getCode());
		}
	}

	public function getManager()
	{
		return $this->_manager;
	}

	public function getDatabase()
	{
		return $this->_database;
	}

	public function begin()
	{
	}

	public function commit()
	{
	}

	public function rollback()
	{
	}
}
