<?php
namespace library\orm\connection;
class mongo extends \MongoDB implements \library\orm\connection
{
	protected static $_connection = array();

	public function __construct($dsn, $user='', $password='', array $options=null)
	{
		$options = $options===null ? array('connect'=>true) : $options;
		if($user) {
			$options['username'] = $user;
		}

		if($password) {
			$options['password'] = $password;
		}

		$options['connectTimeoutMS'] = isset($options['connectTimeoutMS']) ? $options['connectTimeoutMS'] : 2000;
		$options['db']               = isset($options['db']) ? $options['db'] : 'test';

		try {
			if(!isset(static::$_connection[$dsn])) {
				static::$_connection[$dsn] = new \MongoClient($dsn, $options);
			}

			parent::__construct(static::$_connection[$dsn], $options['db']);
		} catch (exception $e) {
			throw new \exception('Connection failed: '.$e->getMessage(), $e->getCode());
		}
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
