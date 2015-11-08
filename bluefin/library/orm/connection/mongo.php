<?php
namespace library\orm\connection;
class mongo extends \MongoClient implements \library\orm\connection
{
	public function __construct($dsn, $user='', $password='', array $options=null)
	{
		$options = $options===null ? array('connect'=>true) : $options;
		if($user) {
			$options['username'] = $user;
		}

		if($password) {
			$options['password'] = $password;
		}

		$options['connectTimeoutMS'] = isset($options['connectTimeoutMS']) ? $options['connectTimeoutMS'] : 50;

		try {
			parent::__construct($dsn, $options);
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
