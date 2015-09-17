<?php
namespace library\orm;
interface pool
{
	public static function addConnection($db, \library\orm\connection $connection, $master=true);
	public static function getConnection($db, $master=true);
	public static function removeConnection($db, $master=true);
	public static function flushConnection();
	public function __get($db);
	public function __set($db, \library\orm\connection $connection);
}
