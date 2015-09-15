<?php
namespace library\orm\connection;
class pdo extends \PDO implements \library\orm\connection
{
	public function __construct($dsn, $user, $password, array $options=null)
	{
		try {
			parent::__construct($dsn, $user, $password, array(PDO::ATTR_PERSISTENT => true));
		} catch (exception $e) {
			throw new \exception('Connection failed: '.$e->getMessage(), $e->getCode());
		}

		//attribute
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

		//charset
		if(isset($options['charset'])) {
			$charset = $options['charset'];
		} elseif(strpos($dsn, 'charset=')!==false) {
			preg_match('/charset=([a-z0-9-]+)/i', $dsn, $match);
			$charset = isset($match[1]) ? $match[1] : 'utf8';
		} else {
			$charset = 'utf8';
		}
		$this->exec("SET NAMES '{$charset}'");

		//timezone
		$timezone = isset($options['timezone']) ? $options['timezone'] : '+08:00';
		$this->exec("SET time_zone='{$timezone}'");
	}

	public function begin()
	{
		$this->beginTransaction();
	}
	//public function commit();
	//public function rollback();
}
