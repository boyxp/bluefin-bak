<?php
namespace library\nosql;
interface cache
{
	public function get($key);
	public function __get($key);
	public function set($key, $value);
	public function __set($key, $value);
	public function expire($key, $ttl=0);
	public function ttl($key);
	public function exists($key);
	public function delete($key);
	public function flush();
}
