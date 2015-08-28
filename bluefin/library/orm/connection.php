<?php
namespace library\orm;
interface connection
{
	public function begin();
	public function commit();
	public function rollback();
}
