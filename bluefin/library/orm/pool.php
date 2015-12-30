<?php
namespace library\orm;
interface pool
{
	public function addConnection($db, connection $connection, $master=true);
	public function getConnection($db, $master=true);
	public function removeConnection($db, $master=true);
	public function flushConnection();
	public function __get($db);
	public function __set($db, connection $connection);
}
