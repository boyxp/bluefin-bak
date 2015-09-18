<?php
namespace library\orm;
interface pool
{
	public function addConnection($db, \library\orm\connection $connection, $master=true);
	public function getConnection($db, $master=true);
	public function removeConnection($db, $master=true);
	public function flushConnection();
	public function __get($db);
	public function __set($db, \library\orm\connection $connection);
}
