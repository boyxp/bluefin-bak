<?php
namespace library;
interface redis
{
	public function connect();
	public function close();
	public function __call($command, array $args);
	public function begin();
	public function commit();
	public function rollback();
}
