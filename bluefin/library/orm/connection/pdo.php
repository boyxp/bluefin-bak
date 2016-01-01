<?php
namespace library\orm\connection;
use library\orm\connection;
class pdo extends \PDO implements connection
{
	public function __construct($dsn, $user='', $password='', array $options=null)
	{
		if(strpos(strtolower($dsn), 'charset=')!==false) {
			preg_match('/charset=([a-z0-9-]+)/i', $dsn, $match);
			$charset = isset($match[1]) ? $match[1] : 'utf8';
		} else {
			$charset = isset($options['charset']) ? $options['charset'] : 'utf8';
			$dsn    .= (substr($dsn, -1)===';' ? '' : ';')."charset={$charset}";
		}

		try {
			parent::__construct($dsn, $user, $password, array(\PDO::ATTR_PERSISTENT => true));
		} catch (exception $e) {
			throw new \exception('Connection failed: '.$e->getMessage(), $e->getCode());
		}

		$this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

		$timezone = isset($options['timezone']) ? $options['timezone'] : '+08:00';
		$this->exec("SET time_zone='{$timezone}'");
		$this->exec("SET NAMES '{$charset}'");
	}

	public function begin()
	{
		$this->beginTransaction();
	}
	//public function commit();
	//public function rollback();
}
